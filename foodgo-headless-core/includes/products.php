<?php
/**
 * WooCommerce Product Extensions for Foodgo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add Foodgo Custom fields to WooCommerce Product Admin
add_action('woocommerce_product_options_general_product_data', 'foodgo_add_product_admin_fields');
add_action('woocommerce_process_product_meta', 'foodgo_save_product_admin_fields');

function foodgo_add_product_admin_fields() {
    echo '<div class="options_group">';
    
    woocommerce_wp_text_input(array(
        'id' => '_foodgo_prep_time',
        'label' => __('Kitchen Prep Time', 'foodgo-headless-core'),
        'placeholder' => '15 - 20 mins',
        'desc_tip' => 'true',
        'description' => __('Estimated preparation time shown on the React frontend.', 'foodgo-headless-core'),
    ));

    woocommerce_wp_text_input(array(
        'id' => '_foodgo_default_spice',
        'label' => __('Default Spice Level (%)', 'foodgo-headless-core'),
        'placeholder' => '50',
        'type' => 'number',
        'custom_attributes' => array('min' => '0', 'max' => '100', 'step' => '5'),
    ));

    echo '</div>';
}

function foodgo_save_product_admin_fields($post_id) {
    if (isset($_POST['_foodgo_prep_time'])) {
        update_post_meta($post_id, '_foodgo_prep_time', sanitize_text_field($_POST['_foodgo_prep_time']));
    }
    if (isset($_POST['_foodgo_default_spice'])) {
        update_post_meta($post_id, '_foodgo_default_spice', intval($_POST['_foodgo_default_spice']));
    }
}
