<?php
/**
 * Security & Hardening for Headless WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

// Disable unnecessary public endpoints if configured
add_action('init', 'foodgo_security_hardening');

function foodgo_security_hardening() {
    $disable_emojis = get_option('foodgo_disable_emojis', '1');
    if ($disable_emojis === '1') {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
    }

    $disable_xmlrpc = get_option('foodgo_disable_xmlrpc', '1');
    if ($disable_xmlrpc === '1') {
        add_filter('xmlrpc_enabled', '__return_false');
    }
}

// Sanitize user inputs helper
function foodgo_sanitize_array($data) {
    if (is_array($data)) {
        return array_map('foodgo_sanitize_array', $data);
    }
    return sanitize_text_field($data);
}
