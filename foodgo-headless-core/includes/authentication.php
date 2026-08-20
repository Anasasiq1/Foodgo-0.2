<?php
/**
 * Authentication Engine for Decoupled Frontend
 */

if (!defined('ABSPATH')) {
    exit;
}

function foodgo_generate_token($user_id) {
    $payload = array(
        'user_id' => $user_id,
        'time' => time(),
        'rand' => wp_generate_password(12, false),
    );
    $json = wp_json_encode($payload);
    $sig = hash_hmac('sha256', $json, wp_salt('auth'));
    return base64_encode($json . '|' . $sig);
}

function foodgo_verify_token($token) {
    if (empty($token)) return false;
    $decoded = base64_decode($token);
    $parts = explode('|', $decoded);
    if (count($parts) !== 2) return false;

    $json = $parts[0];
    $sig = $parts[1];
    $expected_sig = hash_hmac('sha256', $json, wp_salt('auth'));

    if (!hash_equals($expected_sig, $sig)) {
        return false;
    }

    $payload = json_decode($json, true);
    if (!$payload || !isset($payload['user_id'])) {
        return false;
    }

    // Check expiration (30 days)
    if (isset($payload['time']) && (time() - $payload['time']) > (30 * DAY_IN_SECONDS)) {
        return false;
    }

    return get_user_by('id', $payload['user_id']);
}

function foodgo_get_authenticated_user() {
    if (is_user_logged_in()) {
        return wp_get_current_user();
    }

    $auth_header = '';
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    if (preg_match('/Bearer\s(\S+)/i', $auth_header, $matches)) {
        return foodgo_verify_token($matches[1]);
    }

    return false;
}
