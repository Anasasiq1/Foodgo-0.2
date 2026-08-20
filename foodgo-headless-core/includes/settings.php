<?php
/**
 * WordPress Admin Settings Page for Foodgo Headless Core
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'foodgo_register_admin_menu');

function foodgo_register_admin_menu() {
    add_menu_page(
        __('Foodgo Headless', 'foodgo-headless-core'),
        __('Foodgo', 'foodgo-headless-core'),
        'manage_options',
        'foodgo-settings',
        'foodgo_render_settings_page',
        'dashicons-food',
        56
    );
}

add_action('admin_init', 'foodgo_register_settings');

function foodgo_register_settings() {
    register_setting('foodgo_settings_group', 'foodgo_frontend_url');
    register_setting('foodgo_settings_group', 'foodgo_allowed_origins');
    register_setting('foodgo_settings_group', 'foodgo_enable_merchant');
    register_setting('foodgo_settings_group', 'foodgo_enable_delivery');
    register_setting('foodgo_settings_group', 'foodgo_enable_support');
    register_setting('foodgo_settings_group', 'foodgo_disable_emojis');
    register_setting('foodgo_settings_group', 'foodgo_disable_xmlrpc');
}

function foodgo_render_settings_page() {
    $frontend_url = get_option('foodgo_frontend_url', '');
    $allowed_origins = get_option('foodgo_allowed_origins', '');
    $enable_merchant = get_option('foodgo_enable_merchant', '1');
    $enable_delivery = get_option('foodgo_enable_delivery', '1');
    $enable_support = get_option('foodgo_enable_support', '1');
    ?>
    <div class="wrap" style="max-width: 900px;">
        <div style="display:flex; align-items:center; gap: 12px; margin-bottom: 20px;">
            <div style="background:#EF2A39; color:#fff; width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:bold;">FG</div>
            <div>
                <h1 style="margin:0; font-size:24px; font-weight:800;">Foodgo Headless Core</h1>
                <p style="margin:0; color:#666;">Decoupled WooCommerce Frontend Management for React / Vite</p>
            </div>
        </div>

        <div style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:20px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <h2 style="margin-top:0; font-size:16px; border-bottom:1px solid #eee; padding-bottom:10px;">🔌 Connection & Diagnostic Status</h2>
            <table class="widefat striped" style="margin-top:12px;">
                <tbody>
                    <tr>
                        <td><strong>WordPress REST API</strong></td>
                        <td><span style="color:#00a32a; font-weight:bold;">● Active</span> (<code><?php echo esc_url(rest_url()); ?></code>)</td>
                    </tr>
                    <tr>
                        <td><strong>WooCommerce Store API</strong></td>
                        <td>
                            <?php if (class_exists('WooCommerce')): ?>
                                <span style="color:#00a32a; font-weight:bold;">● Ready</span> (<code><?php echo esc_url(rest_url('wc/store/v1/')); ?></code>)
                            <?php else: ?>
                                <span style="color:#d63638; font-weight:bold;">✕ WooCommerce Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Foodgo Headless Config Endpoint</strong></td>
                        <td><a href="<?php echo esc_url(rest_url('foodgo/v1/config')); ?>" target="_blank" style="font-weight:bold; color:#EF2A39;"><code><?php echo esc_url(rest_url('foodgo/v1/config')); ?></code> ↗</a></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('foodgo_settings_group'); ?>
            <?php do_settings_sections('foodgo_settings_group'); ?>

            <div style="background:#fff; border:1px solid #ccd0d4; border-radius:8px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <h2 style="margin-top:0; font-size:16px; border-bottom:1px solid #eee; padding-bottom:10px;">⚙️ Frontend & CORS Configuration</h2>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><strong>Frontend Website URL</strong></th>
                        <td>
                            <input type="url" name="foodgo_frontend_url" value="<?php echo esc_attr($frontend_url); ?>" class="regular-text" placeholder="https://foodgo.yourdomain.com" style="width:100%; max-width:480px;" />
                            <p class="description">The primary production domain where your React/Vite frontend is deployed.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><strong>Additional Allowed CORS Origins</strong></th>
                        <td>
                            <textarea name="foodgo_allowed_origins" rows="3" class="large-text" placeholder="http://localhost:3000&#10;https://staging.foodgo.app"><?php echo esc_textarea($allowed_origins); ?></textarea>
                            <p class="description">One origin per line (e.g. <code>http://localhost:3000</code> or <code>https://app.foodgo.com</code>).</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><strong>Foodgo Modules</strong></th>
                        <td>
                            <label><input type="checkbox" name="foodgo_enable_merchant" value="1" <?php checked('1', $enable_merchant); ?> /> Enable Kitchen / Merchant Module</label><br />
                            <label><input type="checkbox" name="foodgo_enable_delivery" value="1" <?php checked('1', $enable_delivery); ?> /> Enable Delivery Partner Management</label><br />
                            <label><input type="checkbox" name="foodgo_enable_support" value="1" <?php checked('1', $enable_support); ?> /> Enable In-App Customer Live Chat</label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Foodgo Settings', 'foodgo-headless-core'), 'primary', 'submit', true, array('style' => 'background:#EF2A39; border-color:#d41f2d;')); ?>
            </div>
        </form>
    </div>
    <?php
}
