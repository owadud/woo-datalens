<?php
/**
 * HPOS Compatibility Class for WooCommerce DataLens
 *
 * This class provides unified methods for order queries that work with both
 * traditional WooCommerce order storage and High-Performance Order Storage (HPOS).
 *
 * @package WC_DataLens
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_DataLens_HPOS_Compatibility {
    
    /**
     * Initialize HPOS compatibility
     */
    public static function init() {
        // Add HPOS-specific hooks if available
        if (self::is_hpos_available()) {
            add_action('woocommerce_order_status_changed', array(__CLASS__, 'on_order_status_changed'), 10, 4);
            add_action('woocommerce_new_order', array(__CLASS__, 'on_new_order'), 10, 1);
            add_action('woocommerce_order_refunded', array(__CLASS__, 'on_order_refunded'), 10, 2);
        }
    }
    
    /**
     * Check if HPOS is available (WooCommerce 6.9+)
     *
     * @return bool
     */
    public static function is_hpos_available() {
        return class_exists('Automattic\WooCommerce\Utilities\OrderUtil');
    }
    
    /**
     * Check if HPOS is enabled and active
     *
     * @return bool
     */
    public static function is_hpos_enabled() {
        if (!self::is_hpos_available()) {
            return false;
        }
        
        return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }
    
    /**
     * Get orders with HPOS compatibility
     *
     * @param array $args Query arguments
     * @return WC_Order[]|array
     */
    public static function get_orders($args = array()) {
        // Use HPOS-aware wc_get_orders if available
        if (function_exists('wc_get_orders')) {
            // Optimize args for HPOS if enabled
            if (self::is_hpos_enabled()) {
                $args = self::optimize_order_args($args);
            }
            return wc_get_orders($args);
        }
        
        // If wc_get_orders is not available, return empty array
        // This ensures compatibility without falling back to deprecated methods
        return array();
    }
    
    /**
     * Get order by ID with HPOS compatibility
     *
     * @param int $order_id Order ID
     * @return WC_Order|false
     */
    public static function get_order($order_id) {
        if (function_exists('wc_get_order')) {
            return wc_get_order($order_id);
        }
        
        // If wc_get_order is not available, return false
        return false;
    }
    
    /**
     * Get order count with HPOS compatibility
     *
     * @param array $args Query arguments
     * @return int
     */
    public static function get_order_count($args = array()) {
        if (function_exists('wc_get_orders')) {
            $args['limit'] = -1;
            $args['return'] = 'ids';
            
            // Optimize args for HPOS if enabled
            if (self::is_hpos_enabled()) {
                $args = self::optimize_order_args($args);
            }
            
            $orders = wc_get_orders($args);
            return count($orders);
        }
        
        // If wc_get_orders is not available, return 0
        return 0;
    }
    
    /**
     * Check if an ID is a valid order with HPOS compatibility
     *
     * @param int $order_id Order ID
     * @return bool
     */
    public static function is_order($order_id) {
        if (self::is_hpos_available()) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::is_order($order_id);
        }
        
        // Fallback: try to get the order and check if it's valid
        $order = self::get_order($order_id);
        return $order && $order->get_id();
    }
    
    /**
     * Get order type with HPOS compatibility
     *
     * @param int $order_id Order ID
     * @return string
     */
    public static function get_order_type($order_id) {
        if (self::is_hpos_available()) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::get_order_type($order_id);
        }
        
        // Fallback: get order and check its type
        $order = self::get_order($order_id);
        if ($order) {
            return $order->get_type();
        }
        
        return '';
    }
    
    /**
     * Get order admin edit URL with HPOS compatibility
     *
     * @param int $order_id Order ID
     * @return string
     */
    public static function get_order_admin_edit_url($order_id) {
        if (self::is_hpos_available()) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::get_order_admin_edit_url($order_id);
        }
        
        // Fallback: construct admin URL
        return admin_url('post.php?post=' . $order_id . '&action=edit');
    }
    
    /**
     * HPOS Event Handlers
     */
    
    /**
     * Handle order status changes
     */
    public static function on_order_status_changed($order_id, $old_status, $new_status, $order) {
        // Trigger DataLens order sync
        do_action('wc_datalens_order_status_changed', $order_id, $old_status, $new_status, $order);
    }
    
    /**
     * Handle new orders
     */
    public static function on_new_order($order_id) {
        // Trigger DataLens order sync
        do_action('wc_datalens_new_order', $order_id);
    }
    
    /**
     * Handle order refunds
     */
    public static function on_order_refunded($order_id, $refund_id) {
        // Trigger DataLens order sync
        do_action('wc_datalens_order_refunded', $order_id, $refund_id);
    }
    
    /**
     * Get optimized order query arguments for HPOS
     *
     * @param array $args Original arguments
     * @return array Optimized arguments
     */
    public static function optimize_order_args($args = array()) {
        if (!self::is_hpos_enabled()) {
            return $args;
        }
        
        // HPOS-specific optimizations
        $optimized_args = $args;
        
        // Use return => 'objects' for better performance with HPOS
        if (!isset($optimized_args['return'])) {
            $optimized_args['return'] = 'objects';
        }
        
        // Use specific orderby for HPOS performance
        if (isset($optimized_args['orderby']) && $optimized_args['orderby'] === 'date') {
            $optimized_args['orderby'] = 'date_created';
        }
        
        return $optimized_args;
    }
    
    /**
     * Check if we should use HPOS-specific features
     *
     * @return bool
     */
    public static function should_use_hpos_features() {
        return self::is_hpos_enabled() && self::is_hpos_available();
    }
    
    /**
     * Get HPOS status information
     *
     * @return array
     */
    public static function get_hpos_status() {
        return array(
            'available' => self::is_hpos_available(),
            'enabled' => self::is_hpos_enabled(),
            'woocommerce_version' => defined('WC_VERSION') ? WC_VERSION : 'Unknown',
            'using_hpos' => self::should_use_hpos_features()
        );
    }
}

// Initialize HPOS compatibility
add_action('plugins_loaded', array('WC_DataLens_HPOS_Compatibility', 'init'));
