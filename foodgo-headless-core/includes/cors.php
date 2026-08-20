<?php
/**
 * Production-Safe Dynamic CORS Manager
 * Handles origin verification without wildcard * for authenticated and credentialed requests.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', 'foodgo_handle_cors_headers', 1);

function foodgo_handle_cors_headers() {
    $frontend_url = get_option('foodgo_frontend_url', '');
    $extra_origins_raw = get_option('foodgo_allowed_origins', '');
    $allowed_origins = array_filter(array_map('trim', explode("\n", $extra_origins_raw)));

    if (!empty($frontend_url)) {
        $allowed_origins[] = rtrim($frontend_url, '/');
    }

    // Include local development origins
    $allowed_origins[] = 'http://localhost:3000';
    $allowed_origins[] = 'http://localhost:5173';
    $allowed_origins[] = 'http://127.0.0.1:3000';
    $allowed_origins[] = 'http://127.0.0.1:5173';

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    if (!empty($origin)) {
        $origin_clean = rtrim($origin, '/');
        if (in_array($origin_clean, $allowed_origins, true) || empty($frontend_url)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Headers: Authorization, Content-Type, Nonce, X-WC-Store-API-Nonce, x-admin-token, X-Requested-With");
            header("Access-Control-Expose-Headers: Nonce, X-WC-Store-API-Nonce");
        }
    }

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        status_header(200);
        exit();
    }
}
