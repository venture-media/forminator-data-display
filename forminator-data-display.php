<?php
/**
 * Plugin Name:       Forminator Data Display
 * Plugin URI:        https://github.com/venture-media/forminator-data-display
 * Description:       Displays ONLY selection fields from Forminator forms + total submissions shortcode.
 * Version:           0.9.3
 * Author:            Leon de Klerk
 * License:           MIT license
 * Text Domain:       forminator-data-display
 * Requires PHP:      7.4
 * Requires Plugins:  forminator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Forminator_Data_Display {

    public function __construct() {
        add_shortcode( 'ffd', array( $this, 'render_form_data' ) );
        add_shortcode( 'ffd-short', array( $this, 'render_form_total' ) );
    }

    /**
     * Shortcode [ffd-short id="123" class="my-custom-class"]
     */
    public function render_form_total( $atts ) {
        $atts = shortcode_atts( array(
            'id'    => 0,
            'class' => 'ffd-total-submissions',
        ), $atts, 'ffd-short' );

        $form_id = absint( $atts['id'] );
        if ( ! $form_id ) {
            return '<span style="color:red;">Error: Invalid form ID</span>';
        }

        if ( ! class_exists( 'Forminator_API' ) ) {
            return '<span style="color:red;">Forminator not active</span>';
        }

        $entries = Forminator_API::get_entries( $form_id, 0 );
        $total   = ! empty( $entries ) ? count( $entries ) : 0;

        $class = sanitize_html_class( $atts['class'] );
        return '<span class="' . esc_attr( $class ) . '">' . esc_html( $total ) . '</span>';
    }

    // render_form_data()
    public function render_form_data( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'ffd' );

        $form_id = absint( $atts['id'] );
        if ( ! $form_id ) {
            return '<p style="color:red;">Error: Please provide a valid form ID, e.g. [ffd id="2724"]</p>';
        }

        if ( ! class_exists( 'Forminator_API' ) ) {
            return '<p style="color:red;">Forminator plugin is not active.</p>';
        }

        $form = Forminator_API::get_form( $form_id );
        if ( is_wp_error( $form ) || ! $form ) {
            return '<p style="color:red;">Form with ID ' . $form_id . ' not found.</p>';
        }

        $form_name = $form->settings['formName'] ?? get_the_title( $form_id ) ?? 'Form #' . $form_id;

        $output = '<h2>' . esc_html( $form_name ) . '</h2>';

        $fields = $form->get_fields();
        if ( empty( $fields ) ) {
            return $output . '<p>No fields found in this form.</p>';
        }

        $entries = Forminator_API::get_entries( $form_id, 0 );
        if ( empty( $entries ) ) {
            $output .= '<p>No submissions yet.</p>';
            return $output;
        }

        $selection_types = array( 'radio', 'checkbox', 'select' );

        foreach ( $fields as $field ) {
            $field_type = $field->type ?? '';
            if ( ! in_array( $field_type, $selection_types, true ) ) {
                continue;
            }

            $field_label   = $field->field_label ?? $field->label ?? 'Untitled Field';
            $element_id    = $field->element_id ?? $field->id ?? '';
            $options       = $field->options ?? array();

            if ( empty( $options ) || ! $element_id ) {
                continue;
            }

            $option_map = array();
            $counts     = array();
            foreach ( $options as $opt ) {
                $value = $opt['value'] ?? $opt['label'] ?? '';
                $label = $opt['label'] ?? $value;
                if ( $value !== '' ) {
                    $option_map[ $value ] = $label;
                    $counts[ $label ]     = 0;
                }
            }

            foreach ( $entries as $entry ) {
                if ( ! isset( $entry->meta_data[ $element_id ] ) ) {
                    continue;
                }

                $submitted = $entry->meta_data[ $element_id ]['value'] ?? null;

                if ( is_array( $submitted ) ) {
                    foreach ( $submitted as $val ) {
                        if ( isset( $option_map[ $val ] ) ) {
                            $counts[ $option_map[ $val ] ]++;
                        } elseif ( $val !== '' ) {
                            $counts[ $val ] = ( $counts[ $val ] ?? 0 ) + 1;
                        }
                    }
                } elseif ( $submitted !== null && $submitted !== '' ) {
                    if ( isset( $option_map[ $submitted ] ) ) {
                        $counts[ $option_map[ $submitted ] ]++;
                    } else {
                        $counts[ $submitted ] = ( $counts[ $submitted ] ?? 0 ) + 1;
                    }
                }
            }

            $grand_total = array_sum( $counts );

            $output .= '<h3>' . esc_html( $field_label ) . '</h3>';
            $output .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:1200px;">';
            $output .= '<thead><tr><th class="ffd-col1" style="text-align:left;">Option</th><th class="ffd-col2" style="text-align:center;">Total Submissions</th></tr></thead>';
            $output .= '<tbody>';
            foreach ( $counts as $label => $total ) {
                $output .= '<tr><td>' . esc_html( $label ) . '</td><td style="text-align:center;">' . esc_html( $total ) . '</td></tr>';
            }
            $output .= '<tr style="font-weight:bold; background:#f0f0f0;"><td>Total</td><td style="text-align:center;">' . esc_html( $grand_total ) . '</td></tr>';
            $output .= '</tbody></table><br>';
        }

        if ( strpos( $output, '<h3>' ) === false ) {
            $output .= '<p>No selection fields found in this form.</p>';
        }

        return $output;
    }
}

new Forminator_Data_Display();
