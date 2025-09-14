<?php
/**
 * Plugin Name: WooCommerce DataLens
 * Plugin URI: https://example.com/woocommerce-datalens
 * Description: A comprehensive business analytics dashboard for WooCommerce store owners with forecasting capabilities.
 * Version: 1.0.0
 * Author: A. Owadud Bhuiyan
 * Author URI: https://example.com
 * Text Domain: wc-datalens
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Woo: 12345:342928dfsfhsf2349842374wdf4234sfd
 * HPOS Compatible: true
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_DATALENS_VERSION', '1.0.0');
define('WC_DATALENS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_DATALENS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WC_DATALENS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Check if WooCommerce is active
function wc_datalens_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . 
                 __('WooCommerce DataLens requires WooCommerce to be installed and activated.', 'wc-datalens') . 
                 '</p></div>';
        });
        return false;
    }
    return true;
}

// Check HPOS compatibility
function wc_datalens_check_hpos_compatibility() {
    if (!class_exists('WooCommerce')) {
        return false;
    }
    
    // Check if HPOS is available (WooCommerce 6.9+)
    if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
        return true;
    }
    
    return false;
}

// Check if HPOS is enabled and active
function wc_datalens_is_hpos_enabled() {
    if (!wc_datalens_check_hpos_compatibility()) {
        return false;
    }
    
    return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
}

// Initialize the plugin
function wc_datalens_init() {
    if (!wc_datalens_check_woocommerce()) {
        return;
    }
    
    // Load text domain for translations
    load_plugin_textdomain('wc-datalens', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Load plugin classes
    require_once WC_DATALENS_PLUGIN_PATH . 'includes/class-wc-datalens-hpos-compatibility.php';
    require_once WC_DATALENS_PLUGIN_PATH . 'includes/class-wc-datalens.php';
    require_once WC_DATALENS_PLUGIN_PATH . 'includes/class-wc-datalens-tracker.php';
    require_once WC_DATALENS_PLUGIN_PATH . 'includes/class-wc-datalens-dashboard.php';
    require_once WC_DATALENS_PLUGIN_PATH . 'includes/class-wc-datalens-forecasting.php';
    
    // Initialize the main plugin class
    WC_DataLens::get_instance();
}

// Hook into WordPress init
add_action('init', 'wc_datalens_init');

// Activation hook
register_activation_hook(__FILE__, 'wc_datalens_activate');
function wc_datalens_activate() {
    if (!wc_datalens_check_woocommerce()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('WooCommerce DataLens requires WooCommerce to be installed and activated.', 'wc-datalens'));
    }
    
    // Create database tables
    require_once WC_DATALENS_PLUGIN_PATH . 'includes/class-wc-datalens.php';
    WC_DataLens::create_tables();
    
    // Set default options
    add_option('wc_datalens_version', WC_DATALENS_VERSION);
    add_option('wc_datalens_tracking_enabled', 'yes');
    add_option('wc_datalens_forecasting_enabled', 'yes');
    
    // Check HPOS compatibility and show notice if needed
    if (!wc_datalens_check_hpos_compatibility()) {
        add_option('wc_datalens_hpos_notice_shown', false);
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wc_datalens_deactivate');
function wc_datalens_deactivate() {
    // Clean up if needed
    wp_clear_scheduled_hook('wc_datalens_daily_cleanup');
}

// Add HPOS compatibility notice
add_action('admin_notices', 'wc_datalens_hpos_notice');
function wc_datalens_hpos_notice() {
    if (get_option('wc_datalens_hpos_notice_shown', false)) {
        return;
    }
    
    if (!wc_datalens_check_hpos_compatibility()) {
        echo '<div class="notice notice-warning is-dismissible"><p>' . 
             __('<strong>WooCommerce DataLens:</strong> For optimal performance, consider upgrading to WooCommerce 6.9+ to enable High-Performance Order Storage (HPOS) compatibility.', 'wc-datalens') . 
             '</p></div>';
    }
}

// Add HPOS compatibility check for WooCommerce
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
