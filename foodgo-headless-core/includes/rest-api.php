<?php
/**
 * REST API Routes Registration for Foodgo Headless Core
 * Namespace: /foodgo/v1/
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', 'foodgo_register_rest_routes');

function foodgo_register_rest_routes() {
    $namespace = 'foodgo/v1';

    // 1. Automatic Public Frontend Configuration
    register_rest_route($namespace, '/config', array(
        'methods' => 'GET',
        'callback' => 'foodgo_rest_get_public_config',
        'permission_callback' => '__return_true',
    ));

    // 2. Payment Methods List
    register_rest_route($namespace, '/payment-methods', array(
        'methods' => 'GET',
        'callback' => 'foodgo_rest_get_payment_methods',
        'permission_callback' => '__return_true',
    ));

    // 3. Authentication Routes
    register_rest_route($namespace, '/auth/login', array(
        'methods' => 'POST',
        'callback' => 'foodgo_rest_auth_login',
        'permission_callback' => '__return_true',
    ));

    register_rest_route($namespace, '/auth/register', array(
        'methods' => 'POST',
        'callback' => 'foodgo_rest_auth_register',
        'permission_callback' => '__return_true',
    ));

    register_rest_route($namespace, '/auth/me', array(
        'methods' => 'GET',
        'callback' => 'foodgo_rest_auth_me',
        'permission_callback' => 'foodgo_check_authenticated_customer',
    ));

    register_rest_route($namespace, '/auth/logout', array(
        'methods' => 'POST',
        'callback' => 'foodgo_rest_auth_logout',
        'permission_callback' => '__return_true',
    ));

    // 4. Customer Orders
    register_rest_route($namespace, '/customer/orders', array(
        'methods' => 'GET',
        'callback' => 'foodgo_rest_get_customer_orders',
        'permission_callback' => 'foodgo_check_authenticated_customer',
    ));

    register_rest_route($namespace, '/orders/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'foodgo_rest_get_order_detail',
        'permission_callback' => 'foodgo_check_authenticated_customer',
    ));

    // 5. Merchant Kitchen Orders
    register_rest_route($namespace, '/merchant/orders', array(
        'methods' => 'GET',
        'callback' => 'foodgo_rest_get_merchant_orders',
        'permission_callback' => 'foodgo_check_merchant_permission',
    ));

    register_rest_route($namespace, '/merchant/orders/(?P<id>\d+)/status', array(
        'methods' => 'POST',
        'callback' => 'foodgo_rest_update_merchant_order_status',
        'permission_callback' => 'foodgo_check_merchant_permission',
    ));

    // 6. Delivery Partner Logistics
    register_rest_route($namespace, '/delivery/tasks', array(
        'methods' => 'GET',
        'callback' => 'foodgo_rest_get_delivery_tasks',
        'permission_callback' => 'foodgo_check_delivery_permission',
    ));

    register_rest_route($namespace, '/delivery/tasks/(?P<id>\d+)/status', array(
        'methods' => 'POST',
        'callback' => 'foodgo_rest_update_delivery_status',
        'permission_callback' => 'foodgo_check_delivery_permission',
    ));

    // 7. Customer Support Chat
    register_rest_route($namespace, '/support/messages', array(
        'methods' => 'GET',
        'callback' => 'foodgo_rest_get_support_messages',
        'permission_callback' => '__return_true',
    ));

    register_rest_route($namespace, '/support/send', array(
        'methods' => 'POST',
        'callback' => 'foodgo_rest_send_support_message',
        'permission_callback' => '__return_true',
    ));
}

/**
 * Public Configuration Callback - ZERO secrets exposed
 */
function foodgo_rest_get_public_config() {
    $currency = function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : 'INR';
    $currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '₹';

    return rest_ensure_response(array(
        'siteName' => get_bloginfo('name') ?: 'Foodgo Gourmet Kitchen',
        'siteUrl' => home_url(),
        'apiBaseUrl' => rest_url('wp/v2'),
        'storeApiUrl' => rest_url('wc/store/v1'),
        'currency' => $currency,
        'currencySymbol' => $currency_symbol,
        'timezone' => wp_timezone_string(),
        'version' => FOODGO_VERSION,
        'features' => array(
            'cart' => true,
            'checkout' => true,
            'coupons' => true,
            'guestCheckout' => true,
            'variations' => true,
            'merchant' => get_option('foodgo_enable_merchant', '1') === '1',
            'delivery' => get_option('foodgo_enable_delivery', '1') === '1',
            'support' => get_option('foodgo_enable_support', '1') === '1',
            'customization' => true,
        ),
        'modules' => array(
            array(
                'id' => 'food',
                'name' => 'Food & Meals',
                'title' => 'Gourmet Kitchen',
                'subtitle' => 'Burgers, Curries & More',
                'tagline' => 'Fresh & Handcrafted Daily',
                'icon' => 'burger',
                'order' => 1,
                'active' => true,
            ),
        ),
        'deliverySettings' => array(
            'slots' => array(
                array('id' => 'slot-1', 'timeLabel' => '1:00 PM', 'fee' => 0, 'active' => true, 'order' => 1),
                array('id' => 'slot-2', 'timeLabel' => '3:00 PM', 'fee' => 0, 'active' => true, 'order' => 2),
                array('id' => 'slot-3', 'timeLabel' => '5:00 PM', 'fee' => 0, 'active' => true, 'order' => 3),
            ),
            'urgentDelivery' => array(
                'enabled' => true,
                'fee' => 30,
                'label' => 'Urgent Delivery (15-25 mins)',
            ),
        ),
    ));
}

function foodgo_rest_get_payment_methods() {
    $gateways = array();
    if (class_exists('WooCommerce') && WC()->payment_gateways()) {
        foreach (WC()->payment_gateways()->get_available_payment_gateways() as $id => $gateway) {
            $gateways[] = array(
                'id' => $id,
                'title' => $gateway->get_title(),
                'description' => $gateway->get_description(),
                'order' => $gateway->get_method_title() ? 1 : 2,
                'enabled' => true,
                'icon' => $gateway->get_icon(),
            );
        }
    }

    if (empty($gateways)) {
        $gateways = array(
            array('id' => 'cod', 'title' => 'Cash on Delivery', 'description' => 'Pay with cash upon arrival', 'order' => 1, 'enabled' => true),
        );
    }

    return rest_ensure_response($gateways);
}

function foodgo_rest_auth_login($request) {
    $params = $request->get_json_params();
    $username = sanitize_user($params['username'] ?? '');
    $password = $params['password'] ?? '';

    $user = wp_authenticate($username, $password);
    if (is_wp_error($user)) {
        return new WP_Error('invalid_credentials', __('Invalid username or password.', 'foodgo-headless-core'), array('status' => 401));
    }

    $token = foodgo_generate_token($user->ID);

    return rest_ensure_response(array(
        'success' => true,
        'token' => $token,
        'user' => array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'displayName' => $user->display_name,
            'role' => reset($user->roles) ?: 'customer',
        ),
    ));
}

function foodgo_rest_auth_register($request) {
    $params = $request->get_json_params();
    $username = sanitize_user($params['username'] ?? '');
    $email = sanitize_email($params['email'] ?? '');
    $password = $params['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        return new WP_Error('missing_fields', __('Username, email and password are required.', 'foodgo-headless-core'), array('status' => 400));
    }

    if (username_exists($username) || email_exists($email)) {
        return new WP_Error('user_exists', __('User or email already registered.', 'foodgo-headless-core'), array('status' => 409));
    }

    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        return $user_id;
    }

    $user = get_user_by('id', $user_id);
    $token = foodgo_generate_token($user_id);

    return rest_ensure_response(array(
        'success' => true,
        'token' => $token,
        'user' => array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'displayName' => $user->display_name,
            'role' => 'customer',
        ),
    ));
}

function foodgo_rest_auth_me() {
    $user = foodgo_get_authenticated_user();
    if (!$user) {
        return new WP_Error('unauthorized', __('Unauthorized', 'foodgo-headless-core'), array('status' => 401));
    }

    return rest_ensure_response(array(
        'success' => true,
        'user' => array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'displayName' => $user->display_name,
            'role' => reset($user->roles) ?: 'customer',
        ),
    ));
}

function foodgo_rest_auth_logout() {
    wp_logout();
    return rest_ensure_response(array('success' => true));
}

function foodgo_rest_get_customer_orders() {
    $user = foodgo_get_authenticated_user();
    if (!$user || !class_exists('WooCommerce')) {
        return rest_ensure_response(array());
    }

    $orders = wc_get_orders(array(
        'customer' => $user->ID,
        'limit' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    $data = array();
    foreach ($orders as $order) {
        $data[] = $order->get_data();
    }

    return rest_ensure_response($data);
}

function foodgo_rest_get_order_detail($request) {
    $order_id = intval($request['id']);
    if (!class_exists('WooCommerce')) {
        return new WP_Error('not_found', 'WooCommerce not found', array('status' => 404));
    }

    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_Error('order_not_found', 'Order not found', array('status' => 404));
    }

    return rest_ensure_response($order->get_data());
}

function foodgo_rest_get_merchant_orders() {
    return rest_ensure_response(foodgo_get_merchant_orders_data());
}

function foodgo_rest_update_merchant_order_status($request) {
    $order_id = intval($request['id']);
    $params = $request->get_json_params();
    $status = sanitize_text_field($params['status'] ?? '');

    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_Error('not_found', 'Order not found', array('status' => 404));
    }

    if ($status === 'Preparing') $order->update_status('wc-preparing');
    elseif ($status === 'Ready for Pickup') $order->update_status('wc-ready-pickup');
    elseif ($status === 'Completed') $order->update_status('wc-completed');

    return rest_ensure_response(array('success' => true));
}

function foodgo_rest_get_delivery_tasks() {
    return rest_ensure_response(foodgo_get_delivery_tasks_data());
}

function foodgo_rest_update_delivery_status($request) {
    $order_id = intval($request['id']);
    $params = $request->get_json_params();
    $status = sanitize_text_field($params['status'] ?? '');

    $order = wc_get_order($order_id);
    if (!$order) {
        return new WP_Error('not_found', 'Order not found', array('status' => 404));
    }

    if ($status === 'Out for Delivery') $order->update_status('wc-out-delivery');
    elseif ($status === 'Delivered') $order->update_status('wc-completed');

    return rest_ensure_response(array('success' => true));
}

function foodgo_rest_get_support_messages($request) {
    $email = sanitize_email($request->get_param('email') ?: 'guest@foodgo.com');
    return rest_ensure_response(array(
        'success' => true,
        'messages' => foodgo_get_support_messages_data($email),
        'unreadCountCustomer' => 0,
    ));
}

function foodgo_rest_send_support_message($request) {
    $params = $request->get_json_params();
    $email = sanitize_email($params['email'] ?? 'guest@foodgo.com');
    $name = sanitize_text_field($params['name'] ?? 'Foodie Customer');
    $text = sanitize_text_field($params['text'] ?? '');
    $audio_url = esc_url_raw($params['audioUrl'] ?? '');
    $audio_duration = intval($params['audioDuration'] ?? 0);

    $msg = foodgo_save_support_message_data($email, $name, $text, $audio_url, $audio_duration);
    return rest_ensure_response(array(
        'success' => true,
        'message' => $msg,
    ));
}
