<?php
/**
 * Main WooCommerce DataLens Class
 *
 * @package WC_DataLens
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_DataLens {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Tracker instance
     */
    public $tracker;
    
    /**
     * Dashboard instance
     */
    public $dashboard;
    
    /**
     * Forecasting instance
     */
    public $forecasting;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->init_components();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('init', array($this, 'init_ajax_handlers'));
        
        // Add order sync hooks
        add_action('wp_ajax_wc_datalens_sync_orders', array($this, 'ajax_sync_orders'));
        add_action('wp_ajax_wc_datalens_auto_sync_orders', array($this, 'ajax_auto_sync_orders'));
        
        // Auto-sync orders periodically
        add_action('wp_loaded', array($this, 'maybe_auto_sync_orders'));
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        $this->tracker = new WC_DataLens_Tracker();
        $this->dashboard = new WC_DataLens_Dashboard();
        $this->forecasting = new WC_DataLens_Forecasting();
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('DataLens Analytics', 'wc-datalens'),
            __('DataLens', 'wc-datalens'),
            'manage_woocommerce',
            'wc-datalens',
            array($this->dashboard, 'render_dashboard_page'),
            'dashicons-chart-area',
            56
        );
        
        add_submenu_page(
            'wc-datalens',
            __('Dashboard', 'wc-datalens'),
            __('Dashboard', 'wc-datalens'),
            'manage_woocommerce',
            'wc-datalens',
            array($this->dashboard, 'render_dashboard_page')
        );
        
        add_submenu_page(
            'wc-datalens',
            __('Forecasting', 'wc-datalens'),
            __('Forecasting', 'wc-datalens'),
            'manage_woocommerce',
            'wc-datalens-forecasting',
            array($this->forecasting, 'render_forecasting_page')
        );
        
        add_submenu_page(
            'wc-datalens',
            __('Settings', 'wc-datalens'),
            __('Settings', 'wc-datalens'),
            'manage_woocommerce',
            'wc-datalens-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wc-datalens') === false) {
            return;
        }
        
        // Only enqueue simple admin script for form handling
        wp_enqueue_script(
            'wc-datalens-admin-simple',
            WC_DATALENS_PLUGIN_URL . 'assets/js/admin-simple.js',
            array('jquery'),
            WC_DATALENS_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wc-datalens-admin',
            WC_DATALENS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WC_DATALENS_VERSION
        );
        
        // Localize script for both admin and react scripts
        $script_data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc_datalens_nonce'),
            'strings' => array(
                            'loading' => __('Loading...', 'wc-datalens'),
            'error' => __('Error occurred', 'wc-datalens'),
            'noData' => __('No data available', 'wc-datalens')
            )
        );
        
        wp_localize_script('wc-datalens-admin-simple', 'wcDataLens', $script_data);
    }
    
    /**
     * Track product views automatically
     */
    public function track_product_views() {
        if (is_product() && !is_admin()) {
            global $product;
            if ($product && $product->get_id()) {
                $user_id = get_current_user_id();
                $this->dashboard->track_product_view($product->get_id(), $user_id);
            }
        }
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        if (!is_woocommerce() && !is_cart() && !is_checkout()) {
            return;
        }
        
        wp_enqueue_script(
            'wc-datalens-frontend',
            WC_DATALENS_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            WC_DATALENS_VERSION,
            true
        );
        
        wp_localize_script('wc-datalens-frontend', 'wcDataLens', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc_datalens_nonce'),
            'productId' => get_the_ID(),
            'userId' => get_current_user_id()
        ));
    }
    
    /**
     * Initialize AJAX handlers
     */
    public function init_ajax_handlers() {
        // Dashboard AJAX
        add_action('wp_ajax_wc_datalens_get_analytics', array($this->dashboard, 'ajax_get_analytics'));
        add_action('wp_ajax_nopriv_wc_datalens_get_analytics', array($this->dashboard, 'ajax_get_analytics'));
        
        // Forecasting AJAX
        add_action('wp_ajax_wc_datalens_get_forecast', array($this->forecasting, 'ajax_get_forecast'));
        add_action('wp_ajax_nopriv_wc_datalens_get_forecast', array($this->forecasting, 'ajax_get_forecast'));
        
        // Track product views
        add_action('wp_head', array($this, 'track_product_views'));
        
        // Tracking AJAX
        add_action('wp_ajax_wc_datalens_track_event', array($this->tracker, 'ajax_track_event'));
        add_action('wp_ajax_nopriv_wc_datalens_track_event', array($this->tracker, 'ajax_track_event'));
        
        // Debug AJAX endpoint
        add_action('wp_ajax_wc_datalens_debug', array($this, 'ajax_debug'));
        add_action('wp_ajax_nopriv_wc_datalens_debug', array($this, 'ajax_debug'));
    }
    
    /**
     * Debug AJAX endpoint
     */
    public function ajax_debug() {
        wp_send_json_success([
            'message' => 'DataLens AJAX is working',
            'timestamp' => current_time('mysql'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'actions_registered' => [
                'wc_datalens_get_analytics',
                'wc_datalens_get_forecast',
                'wc_datalens_track_event',
                'wc_datalens_debug'
            ]
        ]);
    }

    /**
     * AJAX handler for manual order sync
     */
    public function ajax_sync_orders() {
        if (!wp_verify_nonce($_POST['nonce'], 'wc_datalens_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $synced_count = $this->tracker->sync_all_orders();
        
        wp_send_json_success(array(
            'message' => sprintf(__('Successfully synced %d orders', 'wc-datalens'), $synced_count),
            'synced_count' => $synced_count
        ));
    }
    
    /**
     * AJAX handler for auto order sync
     */
    public function ajax_auto_sync_orders() {
        if (!wp_verify_nonce($_POST['nonce'], 'wc_datalens_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $synced_count = $this->tracker->auto_sync_new_orders();
        
        wp_send_json_success(array(
            'message' => sprintf(__('Successfully synced %d new orders', 'wc-datalens'), $synced_count),
            'synced_count' => $synced_count
        ));
    }
    
    /**
     * Auto-sync orders periodically (every hour)
     */
    public function maybe_auto_sync_orders() {
        $last_auto_sync = get_option('wc_datalens_last_auto_sync', 0);
        $current_time = time();
        
        // Auto-sync every hour
        if ($current_time - $last_auto_sync > 3600) {
            $this->tracker->auto_sync_new_orders();
            update_option('wc_datalens_last_auto_sync', $current_time);
        }
        
        // Populate sample data if no data exists (only once)
        $sample_data_populated = get_option('wc_datalens_sample_data_populated', false);
        if (!$sample_data_populated) {
            $this->tracker->populate_sample_data();
            update_option('wc_datalens_sample_data_populated', true);
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $tracking_enabled = get_option('wc_datalens_tracking_enabled', 'yes');
        $forecasting_enabled = get_option('wc_datalens_forecasting_enabled', 'yes');
        
        include WC_DATALENS_PLUGIN_PATH . 'templates/admin/settings.php';
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'wc_datalens_settings')) {
            wp_die(__('Security check failed', 'wc-datalens'));
        }
        
        update_option('wc_datalens_tracking_enabled', sanitize_text_field($_POST['tracking_enabled']));
        update_option('wc_datalens_forecasting_enabled', sanitize_text_field($_POST['forecasting_enabled']));
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>' . 
                 __('Settings saved successfully!', 'wc-datalens') . 
                 '</p></div>';
        });
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Events table
        $events_table = $wpdb->prefix . 'wc_datalens_events';
        $sql_events = "CREATE TABLE $events_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_data longtext NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Orders tracking table
        $orders_table = $wpdb->prefix . 'wc_datalens_orders';
        $sql_orders = "CREATE TABLE $orders_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            order_status varchar(50) NOT NULL,
            order_total decimal(10,2) NOT NULL,
            order_date datetime NOT NULL,
            customer_id bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_id (order_id),
            KEY order_status (order_status),
            KEY order_date (order_date),
            KEY customer_id (customer_id)
        ) $charset_collate;";
        
        // Product views table
        $product_views_table = $wpdb->prefix . 'wc_datalens_product_views';
        $sql_product_views = "CREATE TABLE $product_views_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            viewed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY viewed_at (viewed_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_events);
        dbDelta($sql_orders);
        dbDelta($sql_product_views);
    }
}
