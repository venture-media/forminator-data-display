# Forminator Data Display

**Plugin Name:** Forminator Data Display  
**Author:** [Leon de Klerk](https://github.com/Leon2332)  
**License:** MIT

**Tested with:**
 - php 8.3
 - WordPress 7.0
 - Forminator 1.53  

A lightweight WordPress plugin that displays summarized data from **Forminator** forms. It extracts and visualizes only **selection fields** (radio, checkbox, select, etc.) with submission counts in clean, easy-to-read tables.

---

## Description

Forminator Data Display turns your Forminator form submissions into beautiful summary statistics. Perfect for surveys, polls, quizzes, and feedback forms where you want to show results publicly or internally.

**Key Features:**

- Uses a simple shortcode: `[ffd id="2724"]`
- Automatically matches Forminator forms (`[forminator_form id="2724"]`)
- **Only displays selection fields** (radio buttons, checkboxes, dropdowns, etc.)

---

## Installation

1. Upload the plugin through the 'Plugins' screen in WordPress
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Make sure **Forminator** plugin is installed and active
4. Use the shortcode on any page or post

---

## Usage

### Basic Shortcodes

#### Form table:
```shortcode
[ffd id="YOUR_FORM_ID"]
```
#### Form total submissions:
```shortcode
[ffd-short id="YOUR_FORM_ID"]
```
