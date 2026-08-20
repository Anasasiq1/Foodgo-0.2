<?php
/**
 * Merchant / Kitchen Management for Foodgo
 */

if (!defined('ABSPATH')) {
    exit;
}

function foodgo_get_merchant_orders_data() {
    if (!class_exists('WooCommerce')) return array();

    $orders = wc_get_orders(array(
        'limit' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
        'status' => array('wc-processing', 'wc-preparing', 'wc-ready-pickup', 'wc-pending'),
    ));

    $result = array();
    foreach ($orders as $order) {
        $items = array();
        foreach ($order->get_items() as $item) {
            $items[] = array(
                'id' => $item->get_id(),
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'notes' => $item->get_meta(__('Special Kitchen Note', 'foodgo-headless-core')),
            );
        }

        $result[] = array(
            'id' => $order->get_id(),
            'orderNumber' => '#' . $order->get_order_number(),
            'customerName' => $order->get_formatted_billing_full_name() ?: 'Customer',
            'items' => $items,
            'total' => floatval($order->get_total()),
            'status' => ucfirst(str_replace('wc-', '', $order->get_status())),
            'date' => $order->get_date_created()->date('Y-m-d H:i:s'),
        );
    }

    return $result;
}
