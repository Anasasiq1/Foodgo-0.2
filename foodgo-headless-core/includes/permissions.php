<?php
/**
 * Permissions & Capabilities for Headless Endpoints
 */

if (!defined('ABSPATH')) {
    exit;
}

function foodgo_is_admin_user() {
    return current_user_can('manage_options') || current_user_can('manage_foodgo_settings');
}

function foodgo_is_merchant_user() {
    return current_user_can('manage_foodgo_kitchen') || current_user_can('manage_options');
}

function foodgo_is_delivery_user() {
    return current_user_can('manage_foodgo_deliveries') || current_user_can('manage_options');
}

function foodgo_check_public_access() {
    return true;
}

function foodgo_check_authenticated_customer() {
    return is_user_logged_in() || !empty(foodgo_get_authenticated_user());
}

function foodgo_check_merchant_permission() {
    $user = foodgo_get_authenticated_user();
    if (!$user) {
        return current_user_can('manage_foodgo_kitchen') || current_user_can('manage_options');
    }
    return user_can($user, 'manage_foodgo_kitchen') || user_can($user, 'manage_options');
}

function foodgo_check_delivery_permission() {
    $user = foodgo_get_authenticated_user();
    if (!$user) {
        return current_user_can('manage_foodgo_deliveries') || current_user_can('manage_options');
    }
    return user_can($user, 'manage_foodgo_deliveries') || user_can($user, 'manage_options');
}
