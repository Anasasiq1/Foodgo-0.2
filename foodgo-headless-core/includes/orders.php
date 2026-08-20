<?php
/**
 * WooCommerce Order Metadata & Status Management for Foodgo
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add Custom Order Statuses (e.g. Preparing, Ready for Pickup, Out for Delivery)
add_filter('wc_order_statuses', 'foodgo_register_custom_order_statuses');

function foodgo_register_custom_order_statuses($order_statuses) {
    $new_statuses = array();

    foreach ($order_statuses as $key => $status) {
        $new_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_statuses['wc-preparing'] = _x('Food Preparing', 'Order status', 'foodgo-headless-core');
            $new_statuses['wc-ready-pickup'] = _x('Ready for Pickup', 'Order status', 'foodgo-headless-core');
            $new_statuses['wc-out-delivery'] = _x('Out for Delivery', 'Order status', 'foodgo-headless-core');
        }
    }

    return $new_statuses;
}

add_action('init', 'foodgo_register_order_status_post_status');

function foodgo_register_order_status_post_status() {
    register_post_status('wc-preparing', array(
        'label' => _x('Food Preparing', 'Order status', 'foodgo-headless-core'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Food Preparing <span class="count">(%s)</span>', 'Food Preparing <span class="count">(%s)</span>', 'foodgo-headless-core')
    ));

    register_post_status('wc-ready-pickup', array(
        'label' => _x('Ready for Pickup', 'Order status', 'foodgo-headless-core'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Ready for Pickup <span class="count">(%s)</span>', 'Ready for Pickup <span class="count">(%s)</span>', 'foodgo-headless-core')
    ));

    register_post_status('wc-out-delivery', array(
        'label' => _x('Out for Delivery', 'Order status', 'foodgo-headless-core'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Out for Delivery <span class="count">(%s)</span>', 'Out for Delivery <span class="count">(%s)</span>', 'foodgo-headless-core')
    ));
}
