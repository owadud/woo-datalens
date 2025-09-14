<?php
/**
 * WooCommerce DataLens Tracker Class
 *
 * @package WooCommerce_DataLens
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_DataLens_Tracker {
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // WooCommerce order hooks
        add_action('woocommerce_thankyou', array($this, 'track_order_completed'));
        add_action('woocommerce_order_status_changed', array($this, 'track_order_status_change'), 10, 4);
        
        // Hook for orders created by other plugins or imports
        add_action('woocommerce_new_order', array($this, 'track_order_created'), 10, 1);
        add_action('woocommerce_order_created', array($this, 'track_order_created'), 10, 1);
        
        // Product interaction hooks
        add_action('template_redirect', array($this, 'track_product_view'));
        add_action('woocommerce_add_to_cart', array($this, 'track_add_to_cart'), 10, 6);
        add_action('woocommerce_cart_updated', array($this, 'track_cart_view'));
        
        // User activity hooks
        add_action('wp_login', array($this, 'track_user_login'), 10, 2);
        add_action('user_register', array($this, 'track_user_registration'));
        
        // AJAX handlers
        add_action('wp_ajax_wc_datalens_track_event', array($this, 'ajax_track_event'));
        add_action('wp_ajax_nopriv_wc_datalens_track_event', array($this, 'ajax_track_event'));
    }
    
    /**
     * Track order completion
     */
    public function track_order_completed($order_id) {
        if (!$this->is_tracking_enabled()) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $this->log_event('order_completed', array(
            'order_id' => $order_id,
            'order_total' => $order->get_total(),
            'order_status' => $order->get_status(),
            'customer_id' => $order->get_customer_id(),
            'payment_method' => $order->get_payment_method(),
            'shipping_method' => $order->get_shipping_method()
        ));
        
        $this->track_order_in_db($order);
    }
    
    /**
     * Track order status changes
     */
    public function track_order_status_change($order_id, $old_status, $new_status, $order) {
        if (!$this->is_tracking_enabled()) {
            return;
        }
        
        $this->log_event('order_status_changed', array(
            'order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'customer_id' => $order->get_customer_id()
        ));
        
        $this->update_order_in_db($order);
    }
    
    /**
     * Track order creation (for orders created by other plugins or imports)
     */
    public function track_order_created($order_id) {
        if (!$this->is_tracking_enabled()) {
            return;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Check if order already exists in DataLens
        global $wpdb;
        $table = $wpdb->prefix . 'wc_datalens_orders';
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT order_id FROM $table WHERE order_id = %d",
            $order_id
        ));
        
        if (!$existing) {
            $this->log_event('order_created', array(
                'order_id' => $order_id,
                'order_total' => $order->get_total(),
                'order_status' => $order->get_status(),
                'customer_id' => $order->get_customer_id(),
                'payment_method' => $order->get_payment_method(),
                'shipping_method' => $order->get_shipping_method(),
                'created_by_plugin' => true
            ));
            
            $this->track_order_in_db($order);
        }
    }
    
    /**
     * Track product view
     */
    public function track_product_view() {
        if (!$this->is_tracking_enabled() || !is_product()) {
            return;
        }
        
        $product_id = get_the_ID();
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return;
        }
        
        // Track in events table
        $this->log_event('product_view', array(
            'product_id' => $product_id,
            'product_name' => $product->get_name(),
            'product_price' => $product->get_price(),
            'product_type' => $product->get_type()
        ));
        
        // Track in product views table
        $this->track_product_view_in_db($product_id);
    }
    
    /**
     * Track add to cart
     */
    public function track_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        if (!$this->is_tracking_enabled()) {
            return;
        }
        
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }
        
        $this->log_event('add_to_cart', array(
            'product_id' => $product_id,
            'variation_id' => $variation_id,
            'quantity' => $quantity,
            'product_name' => $product->get_name(),
            'product_price' => $product->get_price(),
            'cart_item_key' => $cart_item_key
        ));
    }
    
    /**
     * Track cart view
     */
    public function track_cart_view() {
        if (!$this->is_tracking_enabled() || !is_cart()) {
            return;
        }
        
        $cart_items = WC()->cart->get_cart();
        $cart_total = WC()->cart->get_total('raw');
        
        $this->log_event('cart_view', array(
            'cart_items_count' => count($cart_items),
            'cart_total' => $cart_total,
            'cart_items' => array_keys($cart_items)
        ));
    }
    
    /**
     * Track user login
     */
    public function track_user_login($user_login, $user) {
        if (!$this->is_tracking_enabled()) {
            return;
        }
        
        $this->log_event('user_login', array(
            'user_id' => $user->ID,
            'user_login' => $user_login,
            'user_email' => $user->user_email,
            'user_role' => $this->get_user_role($user)
        ));
    }
    
    /**
     * Track user registration
     */
    public function track_user_registration($user_id) {
        if (!$this->is_tracking_enabled()) {
            return;
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        
        $this->log_event('user_registration', array(
            'user_id' => $user_id,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'user_role' => $this->get_user_role($user)
        ));
    }
    
    /**
     * AJAX handler for tracking events
     */
    public function ajax_track_event() {
        if (!wp_verify_nonce($_POST['nonce'], 'wc_datalens_nonce')) {
            wp_die(__('Security check failed', 'woocommerce-datalens'));
        }
        
        $event_type = sanitize_text_field($_POST['event_type']);
        $event_data = isset($_POST['event_data']) ? $_POST['event_data'] : array();
        
        $this->log_event($event_type, $event_data);
        
        wp_send_json_success();
    }
    
    /**
     * Log event to database
     */
    private function log_event($event_type, $event_data = array()) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wc_datalens_events';
        
        $data = array(
            'event_type' => $event_type,
            'event_data' => json_encode($event_data),
            'user_id' => get_current_user_id(),
            'session_id' => $this->get_session_id(),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => current_time('mysql')
        );
        
        $wpdb->insert($table, $data);
    }
    
    /**
     * Track order in database
     */
    private function track_order_in_db($order) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wc_datalens_orders';
        
        // Skip refund orders as they don't have customer_id method
        if ($order instanceof WC_Order_Refund) {
            return;
        }
        
        $data = array(
            'order_id' => $order->get_id(),
            'order_status' => $order->get_status(),
            'order_total' => $order->get_total(),
            'order_date' => $order->get_date_created()->format('Y-m-d H:i:s'),
            'customer_id' => $order->get_customer_id(),
            'created_at' => current_time('mysql')
        );
        
        $wpdb->replace($table, $data);
    }
    
    /**
     * Update order in database
     */
    private function update_order_in_db($order) {
        global $wpdb;
        
        // Skip refund orders
        if ($order instanceof WC_Order_Refund) {
            return;
        }
        
        $table = $wpdb->prefix . 'wc_datalens_orders';
        
        $data = array(
            'order_status' => $order->get_status(),
            'order_total' => $order->get_total()
        );
        
        $wpdb->update($table, $data, array('order_id' => $order->get_id()));
    }
    
    /**
     * Track product view in database
     */
    private function track_product_view_in_db($product_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'wc_datalens_product_views';
        
        $data = array(
            'product_id' => $product_id,
            'user_id' => get_current_user_id(),
            'session_id' => $this->get_session_id(),
            'ip_address' => $this->get_client_ip(),
            'viewed_at' => current_time('mysql')
        );
        
        $wpdb->insert($table, $data);
    }
    
    /**
     * Get session ID
     */
    private function get_session_id() {
        // Use WordPress cookies or generate a unique session ID without PHP sessions
        if (isset($_COOKIE['wc_datalens_session'])) {
            return $_COOKIE['wc_datalens_session'];
        }
        
        // Generate a unique session ID
        $session_id = 'datalens_' . uniqid() . '_' . time();
        
        // Set cookie for future requests (if headers haven't been sent)
        if (!headers_sent()) {
            setcookie('wc_datalens_session', $session_id, time() + (86400 * 30), '/'); // 30 days
        }
        
        return $session_id;
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * Get user role
     */
    private function get_user_role($user) {
        $roles = $user->roles;
        return !empty($roles) ? $roles[0] : '';
    }
    
    /**
     * Check if tracking is enabled
     */
    private function is_tracking_enabled() {
        return get_option('wc_datalens_tracking_enabled', 'yes') === 'yes';
    }
    
    /**
     * Sync all existing orders from WooCommerce
     * This method ensures all orders in WooCommerce are tracked in DataLens
     */
    public function sync_all_orders() {
        global $wpdb;
        
        // Get all orders from WooCommerce (excluding refunds) using HPOS-compatible method
        $order_args = array(
            'limit' => -1,
            'status' => array('pending', 'processing', 'completed', 'cancelled', 'refunded', 'failed'),
            'orderby' => 'date',
            'order' => 'ASC',
            'type' => 'shop_order' // Only get regular orders, not refunds
        );
        
        // Use HPOS-compatible order query
        $orders = WC_DataLens_HPOS_Compatibility::get_orders($order_args);
        
        $synced_count = 0;
        $table = $wpdb->prefix . 'wc_datalens_orders';
        
        foreach ($orders as $order) {
            // Skip refund orders
            if ($order instanceof WC_Order_Refund) {
                continue;
            }
            
            $order_id = $order->get_id();
            
            // Check if order already exists in DataLens
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT order_id FROM $table WHERE order_id = %d",
                $order_id
            ));
            
            if (!$existing) {
                // Insert order into DataLens
                $data = array(
                    'order_id' => $order_id,
                    'order_status' => $order->get_status(),
                    'order_total' => $order->get_total(),
                    'order_date' => $order->get_date_created()->format('Y-m-d H:i:s'),
                    'customer_id' => $order->get_customer_id(),
                    'created_at' => current_time('mysql')
                );
                
                $wpdb->insert($table, $data);
                $synced_count++;
                
                // Also log the order completion event
                $this->log_event('order_completed', array(
                    'order_id' => $order_id,
                    'order_total' => $order->get_total(),
                    'order_status' => $order->get_status(),
                    'customer_id' => $order->get_customer_id(),
                    'payment_method' => $order->get_payment_method(),
                    'shipping_method' => $order->get_shipping_method(),
                    'synced' => true
                ));
            }
        }
        
        return $synced_count;
    }
    
    /**
     * Sync orders created after a specific date
     */
    public function sync_orders_since($date) {
        global $wpdb;
        
        $order_args = array(
            'limit' => -1,
            'status' => array('pending', 'processing', 'completed', 'cancelled', 'refunded', 'failed'),
            'date_created' => '>=' . $date,
            'orderby' => 'date',
            'order' => 'ASC',
            'type' => 'shop_order' // Only get regular orders, not refunds
        );
        
        // Use HPOS-compatible order query
        $orders = WC_DataLens_HPOS_Compatibility::get_orders($order_args);
        
        $synced_count = 0;
        $table = $wpdb->prefix . 'wc_datalens_orders';
        
        foreach ($orders as $order) {
            // Skip refund orders
            if ($order instanceof WC_Order_Refund) {
                continue;
            }
            
            $order_id = $order->get_id();
            
            // Check if order already exists in DataLens
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT order_id FROM $table WHERE order_id = %d",
                $order_id
            ));
            
            if (!$existing) {
                // Insert order into DataLens
                $data = array(
                    'order_id' => $order_id,
                    'order_status' => $order->get_status(),
                    'order_total' => $order->get_total(),
                    'order_date' => $order->get_date_created()->format('Y-m-d H:i:s'),
                    'customer_id' => $order->get_customer_id(),
                    'created_at' => current_time('mysql')
                );
                
                $wpdb->insert($table, $data);
                $synced_count++;
                
                // Also log the order completion event
                $this->log_event('order_completed', array(
                    'order_id' => $order_id,
                    'order_total' => $order->get_total(),
                    'order_status' => $order->get_status(),
                    'customer_id' => $order->get_customer_id(),
                    'payment_method' => $order->get_payment_method(),
                    'shipping_method' => $order->get_shipping_method(),
                    'synced' => true
                ));
            }
        }
        
        return $synced_count;
    }
    
    /**
     * Hook to automatically sync new orders that might be created by other plugins
     */
    public function auto_sync_new_orders() {
        // Get the last sync time
        $last_sync = get_option('wc_datalens_last_order_sync', '');
        
        if (empty($last_sync)) {
            // First time sync - sync all orders
            $synced_count = $this->sync_all_orders();
            update_option('wc_datalens_last_order_sync', current_time('mysql'));
            return $synced_count;
        } else {
            // Sync orders since last sync
            $synced_count = $this->sync_orders_since($last_sync);
            update_option('wc_datalens_last_order_sync', current_time('mysql'));
            return $synced_count;
        }
    }

    /**
     * Populate sample data for testing (only if no real data exists)
     */
    public function populate_sample_data() {
        global $wpdb;
        
        // Check if we already have data
        $orders_table = $wpdb->prefix . 'wc_datalens_orders';
        $views_table = $wpdb->prefix . 'wc_datalens_product_views';
        $events_table = $wpdb->prefix . 'wc_datalens_events';
        
        $existing_orders = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table");
        $existing_views = $wpdb->get_var("SELECT COUNT(*) FROM $views_table");
        $existing_events = $wpdb->get_var("SELECT COUNT(*) FROM $events_table");
        
        if ($existing_orders > 0 || $existing_views > 0 || $existing_events > 0) {
            return; // Don't populate if data already exists
        }
        
        // Get some products
        $products = get_posts(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'numberposts' => 10
        ));
        
        if (empty($products)) {
            return; // No products to work with
        }
        
        // Generate sample orders for the last 30 days with realistic patterns
        $order_id_counter = 1000;
        for ($i = 30; $i >= 0; $i--) {
            $date = date('Y-m-d H:i:s', strtotime("-$i days"));
            
            // More orders on weekends and fewer on weekdays
            $day_of_week = date('N', strtotime("-$i days"));
            $base_orders = ($day_of_week >= 6) ? rand(2, 8) : rand(0, 4); // More on weekends
            
            // Add some seasonal variation
            $month = date('n', strtotime("-$i days"));
            if ($month == 12) { // December - holiday season
                $base_orders = round($base_orders * 1.5);
            }
            
            for ($j = 0; $j < $base_orders; $j++) {
                $order_total = rand(2500, 50000) / 100; // $25-$500
                $status = rand(0, 3) ? 'completed' : 'processing'; // Mostly completed
                
                $wpdb->insert($orders_table, array(
                    'order_id' => $order_id_counter++,
                    'order_status' => $status,
                    'order_total' => $order_total,
                    'order_date' => $date,
                    'customer_id' => rand(1, 50),
                    'created_at' => current_time('mysql')
                ));
            }
        }
        
        // Generate sample product views with realistic patterns
        foreach ($products as $index => $product) {
            // Popular products get more views
            $popularity_factor = 1;
            if ($index < 3) {
                $popularity_factor = rand(3, 5); // Top 3 products are more popular
            } elseif ($index < 6) {
                $popularity_factor = rand(2, 3); // Next 3 are moderately popular
            }
            
            $view_count = rand(10, 100) * $popularity_factor;
            
            for ($i = 0; $i < $view_count; $i++) {
                $view_date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days'));
                
                $wpdb->insert($views_table, array(
                    'product_id' => $product->ID,
                    'user_id' => rand(0, 30),
                    'session_id' => 'sample_' . rand(1000, 9999),
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'viewed_at' => $view_date
                ));
            }
        }
        
        // Generate sample events with realistic distribution
        $event_types = array(
            'user_login' => 30,
            'user_registration' => 10,
            'add_to_cart' => 40,
            'product_view' => 20
        );
        
        $total_events = 200;
        foreach ($event_types as $event_type => $percentage) {
            $event_count = round(($percentage / 100) * $total_events);
            
            for ($i = 0; $i < $event_count; $i++) {
                $event_date = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days'));
                
                $event_data = array('sample' => true);
                if ($event_type === 'add_to_cart') {
                    $event_data['product_id'] = $products[array_rand($products)]->ID;
                    $event_data['quantity'] = rand(1, 3);
                } elseif ($event_type === 'product_view') {
                    $event_data['product_id'] = $products[array_rand($products)]->ID;
                }
                
                $wpdb->insert($events_table, array(
                    'event_type' => $event_type,
                    'event_data' => json_encode($event_data),
                    'user_id' => rand(0, 30),
                    'session_id' => 'sample_' . rand(1000, 9999),
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'user_agent' => 'Sample User Agent',
                    'created_at' => $event_date
                ));
            }
        }
    }
}
