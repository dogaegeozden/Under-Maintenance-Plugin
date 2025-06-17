<?php
/*
Plugin Name: Under Maintenance
Description: Displays an under maintenance page for non-admin users/visitors.
Author: Doga Ege Ozden
Author URI: https://dogaegeozden.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Version: 1.0
*/



##############################

# HELPER FUNCTIONS

##############################

function under_maintenance_input_field($name, $value, $type = 'text', $checked = false) {
    if ($type === 'checkbox') {
        echo '<input type="checkbox" name="under_maintenance_settings[' . esc_attr($name) . ']" value="1" ' 
            . ($checked ? 'checked' : '') . ' />';
    } else {
        echo '<input type="' . esc_attr($type) . '" name="under_maintenance_settings[' . esc_attr($name) . ']" value="'
            . esc_attr($value) . '" class="regular-text" />';
    }
}

function under_maintenance_textarea_field($name, $value, $rows = 5) {
    echo '<textarea name="under_maintenance_settings['
        . esc_attr($name) . ']" class="regular-text" rows="'
        . intval($rows) . '">'
        . esc_textarea($value) . '</textarea>';
}



##############################

# TAKE IT UNDER MAINTENANCE

##############################

function take_it_under_maintenance() {
    $options = get_option('under_maintenance_settings');
    if (empty($options['enable_under_maintenance'])) return;

    $title = isset($options['under_maintenance_page_title']) ? esc_html($options['under_maintenance_page_title']) : '';
    $message = isset($options['under_maintenance_page_message']) ? esc_html($options['under_maintenance_page_message']) : '';

    if (!current_user_can('edit_themes') || !current_user_can('manage_options')) {
        wp_die(
            "<h1 style='text-align:center;margin-top:50px;font-size:30px;'>{$title}</h1>
            <p style='text-align:center;'>{$message}</p>",
            'Maintenance Mode'
        );
    }
}
add_action('template_redirect', 'take_it_under_maintenance');



##############################

# ADMIN PAGE REGISTRATION

##############################

function under_maintenance_admin_menu() {
    add_menu_page(
        'Under Maintenance Settings',    // Page title (browser tab)
        'Under Maintenance',             // Menu title (sidebar label)
        'manage_options',                // Capability
        'under-maintenance-settings',    // Menu slug
        'under_maintenance_plugin_page', // Callback function to display the page
        'dashicons-hammer',              // Icon (WordPress Dashicon)
        1                                // Position in sidebar (optional)
    );
}
add_action('admin_menu', 'under_maintenance_admin_menu');



##############################

# SETTINGS PAGE MARKUP

##############################

function under_maintenance_plugin_page() {
    echo '<div class="wrap">';
        echo '<h1>Under Maintenance</h1>';
        echo '<form method="post" action="options.php">';
            settings_fields('under_maintenance_settings_group');
            do_settings_sections('under-maintenance-settings');
            submit_button();
        echo '</form>';
    echo '</div>';
}



##############################

# SETTINGS PAGE FIELDS

##############################

function under_maintenance_register_settings() {
    register_setting('under_maintenance_settings_group', 'under_maintenance_settings', [
        'sanitize_callback' => 'under_maintenance_sanitize_callback'
    ]);
    add_settings_section(
        'under_maintenance_main_section',
        'Under Maintenance Settings',
        null,
        'under-maintenance-settings'
    );
    add_settings_field(
        'enable_under_maintenance',
        'Enable Under Maintenance:',
        'under_maintenance_enable_callback',
        'under-maintenance-settings',
        'under_maintenance_main_section'
    );
    add_settings_field(
        'under_maintenance_page_title',
        'Title:',
        'under_maintenance_page_title_callback',
        'under-maintenance-settings',
        'under_maintenance_main_section'
    );
    add_settings_field(
        'under_maintenance_page_message',
        'Message:',
        'under_maintenance_page_message_callback',
        'under-maintenance-settings',
        'under_maintenance_main_section'
    );
}
add_action('admin_init', 'under_maintenance_register_settings');



##############################

# SANITIZATION

##############################

function under_maintenance_sanitize_callback($input) {
    $sanitized = [];
    // Checkbox fields - boolean (true/false)
    $sanitized['enable_under_maintenance'] = !empty($input['enable_under_maintenance']);
    // Text fields - sanitize_text_field
    $sanitized['under_maintenance_page_title'] =
    isset($input['under_maintenance_page_title'])
        ? sanitize_text_field($input['under_maintenance_page_title'])
        : '';
    $sanitized['under_maintenance_page_message'] =
        isset($input['under_maintenance_page_message'])
            ? sanitize_textarea_field($input['under_maintenance_page_message'])
            : '';
    return $sanitized;
}



##############################

# CALLBACKS

##############################

function under_maintenance_enable_callback() {
    $options = get_option('under_maintenance_settings');
    $checked = !empty($options['enable_under_maintenance']);
    under_maintenance_input_field('enable_under_maintenance', '1', 'checkbox', $checked);
}

function under_maintenance_page_title_callback() {
    $options = get_option('under_maintenance_settings');
    under_maintenance_input_field(
        $name='under_maintenance_page_title',
        $value=$options['under_maintenance_page_title'] ?? 'Under Maintenance'
    );
}

function under_maintenance_page_message_callback() {
    $options = get_option('under_maintenance_settings');
    under_maintenance_textarea_field(
        $name='under_maintenance_page_message',
        $value=$options['under_maintenance_page_message'] ?? 'We\'re currently performing scheduled maintenance. Please check back later.'
    );
}