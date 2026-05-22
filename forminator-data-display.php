<?php
/**
 * Plugin Name:       Forminator Data Display
 * Plugin URI:        https://github.com/venture-media/forminator-data-display
 * Description:       Displays ONLY selection fields (radio, checkbox, select) data from Forminator forms.
 * Version:           0.9.2
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
    }


     // Shortcode handler

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

        // Get the form object
        $form = Forminator_API::get_form( $form_id );
        if ( is_wp_error( $form ) || ! $form ) {
            return '<p style="color:red;">Form with ID ' . $form_id . ' not found.</p>';
        }

        // Form name (h2)
        $form_name = $form->settings['formName'] ?? get_the_title( $form_id ) ?? 'Form #' . $form_id;

        $output = '<h2>' . esc_html( $form_name ) . '</h2>';

        // Get all fields
        $fields = $form->get_fields();
        if ( empty( $fields ) ) {
            return $output . '<p>No fields found in this form.</p>';
        }

        // Get ALL entries (per_page = 0 returns everything)
        $entries = Forminator_API::get_entries( $form_id, 0 );
        if ( empty( $entries ) ) {
            $output .= '<p>No submissions yet.</p>';
            return $output;
        }

        // Filter only selection fields: radio, checkbox, select
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

            // Build option map: value => label (for display) and initialize counts
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

            // Count submissions for this field
            foreach ( $entries as $entry ) {
                if ( ! isset( $entry->meta_data[ $element_id ] ) ) {
                    continue;
                }

                $submitted = $entry->meta_data[ $element_id ]['value'] ?? null;

                if ( is_array( $submitted ) ) {
                    // Multi-select / checkbox
                    foreach ( $submitted as $val ) {
                        if ( isset( $option_map[ $val ] ) ) {
                            $counts[ $option_map[ $val ] ]++;
                        } elseif ( $val !== '' ) {
                            $counts[ $val ] = ( $counts[ $val ] ?? 0 ) + 1;
                        }
                    }
                } elseif ( $submitted !== null && $submitted !== '' ) {
                    // Single select / radio
                    if ( isset( $option_map[ $submitted ] ) ) {
                        $counts[ $option_map[ $submitted ] ]++;
                    } else {
                        $counts[ $submitted ] = ( $counts[ $submitted ] ?? 0 ) + 1;
                    }
                }
            }

            // Calculate grand total for this field
            $grand_total = array_sum( $counts );

            // Output: <h3> + separate table for this field (with extra Total row)
            $output .= '<h3>' . esc_html( $field_label ) . '</h3>';
            $output .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:1200px;">';
            $output .= '<thead><tr><th class="ffd-col1" style="text-align:left;">Option</th><th class="ffd-col2" style="text-align:center;">Total Submissions</th></tr></thead>';
            $output .= '<tbody>';
            foreach ( $counts as $label => $total ) {
                $output .= '<tr>';
                $output .= '<td>' . esc_html( $label ) . '</td>';
                $output .= '<td style="text-align:center;">' . esc_html( $total ) . '</td>';
                $output .= '</tr>';
            }
            // Extra row: total of the totals
            $output .= '<tr class="ffd-total" style="font-weight:bold;">';
            $output .= '<td>Total</td>';
            $output .= '<td style="text-align:center;">' . esc_html( $grand_total ) . '</td>';
            $output .= '</tr>';
            $output .= '</tbody></table><br>';
        }

        if ( strpos( $output, '<h3>' ) === false ) {
            $output .= '<p>No selection fields (radio, checkbox, or select) found in this form.</p>';
        }

        return $output;
    }
}

// Initialize the plugin
new Forminator_Data_Display();
