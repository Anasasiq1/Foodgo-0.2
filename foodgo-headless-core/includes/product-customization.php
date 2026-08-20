<?php
/**
 * Product Customization Engine (Spiciness, Portion, Curries/Salna, Add-ons)
 * Integrates directly with WooCommerce Store API and WooCommerce Order Items.
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Extend WooCommerce Store API Cart Item data with Foodgo customization
add_filter('woocommerce_store_api_add_to_cart_data', 'foodgo_store_api_capture_cart_customization', 10, 2);

function foodgo_store_api_capture_cart_customization($cart_item_data, $request) {
    $extensions = $request->get_param('extensions');
    if (!empty($extensions['foodgo'])) {
        $cart_item_data['foodgo_customization'] = $extensions['foodgo'];
    }
    return $cart_item_data;
}

// 2. Transfer custom food data from Cart Item to WooCommerce Order Item Meta
add_action('woocommerce_checkout_create_order_line_item', 'foodgo_add_order_item_customization_meta', 10, 4);

function foodgo_add_order_item_customization_meta($item, $cart_item_key, $values, $order) {
    if (isset($values['foodgo_customization'])) {
        $custom = $values['foodgo_customization'];

        if (isset($custom['spiceLevel'])) {
            $item->add_meta_data(__('Spiciness Level', 'foodgo-headless-core'), $custom['spiceLevel'] . '%', true);
            $item->add_meta_data('_foodgo_spice_level', $custom['spiceLevel'], true);
        }

        if (isset($custom['portion'])) {
            $item->add_meta_data('_foodgo_portion', $custom['portion'], true);
        }

        if (!empty($custom['curry']) && !empty($custom['curry']['curryName'])) {
            $curry = $custom['curry'];
            $label = sprintf('%s (%d %s)', $curry['curryName'], $curry['totalUnits'], $curry['unitLabel']);
            $item->add_meta_data(__('Selected Curry / Salna', 'foodgo-headless-core'), $label, true);
            $item->add_meta_data('_foodgo_curry', $curry, true);
        }

        if (!empty($custom['toppings']) && is_array($custom['toppings'])) {
            $topping_names = wp_list_pluck($custom['toppings'], 'name');
            $item->add_meta_data(__('Extra Toppings', 'foodgo-headless-core'), implode(', ', $topping_names), true);
        }

        if (!empty($custom['sides']) && is_array($custom['sides'])) {
            $side_names = wp_list_pluck($custom['sides'], 'name');
            $item->add_meta_data(__('Selected Sides', 'foodgo-headless-core'), implode(', ', $side_names), true);
        }

        if (!empty($custom['specialInstructions'])) {
            $item->add_meta_data(__('Special Kitchen Note', 'foodgo-headless-core'), sanitize_text_field($custom['specialInstructions']), true);
        }
    }
}

// 3. Extend Store API Product Schema with foodgo_meta
add_action('woocommerce_blocks_loaded', 'foodgo_register_store_api_product_schema_extension');

function foodgo_register_store_api_product_schema_extension() {
    if (function_exists('woocommerce_store_api_register_endpoint_data')) {
        woocommerce_store_api_register_endpoint_data(array(
            'endpoint' => 'product',
            'namespace' => 'foodgo',
            'data_callback' => 'foodgo_store_api_product_data_callback',
            'schema_callback' => '__return_empty_array',
            'schema_type' => ARRAY_A,
        ));
    }
}

function foodgo_store_api_product_data_callback($product) {
    $product_id = $product->get_id();
    $prep_time = get_post_meta($product_id, '_foodgo_prep_time', true) ?: '15 - 20 mins';
    $default_spice = get_post_meta($product_id, '_foodgo_default_spice', true);
    $curry_config = get_post_meta($product_id, '_foodgo_curry_config', true);

    return array(
        'prepTime' => $prep_time,
        'defaultSpice' => ($default_spice !== '') ? intval($default_spice) : 50,
        'defaultPortion' => 1,
        'curryConfig' => $curry_config ? json_decode($curry_config, true) : array('enabled' => true),
    );
}
