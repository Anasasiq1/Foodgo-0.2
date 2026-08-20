<?php
/**
 * Plugin Name: Foodgo Headless Core
 * Plugin URI: https://github.com/Anasasiq1/Foodgo-0.2
 * Description: The official Headless WooCommerce & WordPress integration engine for the Foodgo React Frontend. Powers Store API customization, dynamic config, CORS, merchant kitchen routing, delivery logistics, and live support.
 * Version: 2.0.0
 * Author: Foodgo Gourmet Kitchen Team
 * Author URI: https://foodgo.app
 * Text Domain: foodgo-headless-core
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('FOODGO_VERSION', '2.0.0');
define('FOODGO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FOODGO_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Foodgo Headless Core Class
 */
final class Foodgo_Headless_Core {

    private static $instance = null;

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    private function includes() {
        require_once FOODGO_PLUGIN_DIR . 'includes/security.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/cors.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/permissions.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/authentication.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/product-customization.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/products.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/orders.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/merchants.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/delivery.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/support.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/settings.php';
        require_once FOODGO_PLUGIN_DIR . 'includes/rest-api.php';
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('init', array($this, 'init'));
        add_action('admin_notices', array($this, 'check_woocommerce_dependency'));
    }

    public function init() {
        load_plugin_textdomain('foodgo-headless-core', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function activate() {
        // Register Foodgo custom roles (Merchant & Delivery Partner)
        add_role('foodgo_merchant', __('Foodgo Merchant / Kitchen Manager', 'foodgo-headless-core'), array(
            'read' => true,
            'manage_foodgo_kitchen' => true,
            'edit_shop_orders' => true,
        ));

        add_role('foodgo_delivery', __('Foodgo Delivery Partner', 'foodgo-headless-core'), array(
            'read' => true,
            'manage_foodgo_deliveries' => true,
        ));

        // Add capabilities to Administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('manage_foodgo_settings');
            $admin_role->add_cap('manage_foodgo_kitchen');
            $admin_role->add_cap('manage_foodgo_deliveries');
        }

        // Set default options if not exists
        if (!get_option('foodgo_frontend_url')) {
            update_option('foodgo_frontend_url', home_url());
        }

        // Flush rewrite rules for custom endpoints
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function check_woocommerce_dependency() {
        if (!class_exists('WooCommerce')) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong><?php _e('Foodgo Headless Core requires WooCommerce to be installed and active.', 'foodgo-headless-core'); ?></strong></p>
            </div>
            <?php
        }
    }
}

// Instantiate plugin
function foodgo_headless_core() {
    return Foodgo_Headless_Core::get_instance();
}

foodgo_headless_core();
