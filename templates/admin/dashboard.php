<?php
/**
 * Dashboard Template
 *
 * @package WooCommerce_DataLens
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wc-datalens-dashboard">
    <h1><?php _e('DataLens Analytics Dashboard', 'wc-datalens'); ?></h1>
    
    <?php if (defined('WP_DEBUG') && WP_DEBUG): ?>
        <div class="notice notice-info">
            <p><strong>Debug Mode:</strong> Check browser console for React charts loading status and any errors.</p>
        </div>
    <?php endif; ?>
    
    <!-- Period Filter -->
    <div class="wc-datalens-filter">
        <form method="get" action="">
            <input type="hidden" name="page" value="wc-datalens">
            
            <select name="period" id="period-filter">
                <option value="1d" <?php selected($period, '1d'); ?>><?php _e('Last 24 Hours', 'wc-datalens'); ?></option>
                <option value="7d" <?php selected($period, '7d'); ?>><?php _e('Last 7 Days', 'wc-datalens'); ?></option>
                <option value="30d" <?php selected($period, '30d'); ?>><?php _e('Last 30 Days', 'wc-datalens'); ?></option>
                <option value="90d" <?php selected($period, '90d'); ?>><?php _e('Last 90 Days', 'wc-datalens'); ?></option>
                <option value="custom" <?php selected($period, 'custom'); ?>><?php _e('Custom Range', 'wc-datalens'); ?></option>
            </select>
            
            <div class="custom-date-range" style="display: <?php echo $period === 'custom' ? 'inline-block' : 'none'; ?>;">
                <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>" placeholder="<?php _e('Start Date', 'woocommerce-datalens'); ?>">
                <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>" placeholder="<?php _e('End Date', 'woocommerce-datalens'); ?>">
            </div>
            
            <button type="submit" class="button button-primary"><?php _e('Apply Filter', 'woocommerce-datalens'); ?></button>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="wc-datalens-summary">
        <div class="summary-card">
            <div class="card-icon orders">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <div class="card-content">
                <h3><?php echo number_format($analytics_data['summary']['total_orders']); ?></h3>
                <p><?php _e('Total Orders', 'woocommerce-datalens'); ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="card-icon revenue">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="card-content">
                <h3><?php echo wc_price($analytics_data['summary']['total_revenue']); ?></h3>
                <p><?php _e('Total Revenue', 'woocommerce-datalens'); ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="card-icon views">
                <span class="dashicons dashicons-visibility"></span>
            </div>
            <div class="card-content">
                <h3><?php echo number_format($analytics_data['summary']['total_views']); ?></h3>
                <p><?php _e('Product Views', 'woocommerce-datalens'); ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="card-icon users">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="card-content">
                <h3><?php echo number_format($analytics_data['summary']['total_users']); ?></h3>
                <p><?php _e('Active Users', 'woocommerce-datalens'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- PHP-Generated Charts -->
    <div class="wc-datalens-charts-php">
        <h2><?php _e('Analytics Charts', 'woocommerce-datalens'); ?></h2>
        
        <div class="charts-grid">
            <!-- Orders Chart -->
            <div class="chart-container">
                <h3><?php _e('Orders Over Time', 'woocommerce-datalens'); ?></h3>
                <div class="chart-php">
                    <?php echo $this->render_orders_chart($analytics_data); ?>
                </div>
            </div>
            
            <!-- Revenue Chart -->
            <div class="chart-container">
                <h3><?php _e('Revenue Over Time', 'woocommerce-datalens'); ?></h3>
                <div class="chart-php">
                    <?php echo $this->render_revenue_chart($analytics_data); ?>
                </div>
            </div>
            
            <!-- Product Views Chart -->
            <div class="chart-container">
                <h3><?php _e('Product Views', 'woocommerce-datalens'); ?></h3>
                <div class="chart-php">
                    <?php echo $this->render_views_chart($analytics_data); ?>
                </div>
            </div>
            
            <!-- User Activity Chart -->
            <div class="chart-container">
                <h3><?php _e('User Activity', 'woocommerce-datalens'); ?></h3>
                <div class="chart-php">
                    <?php echo $this->render_activity_chart($analytics_data); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Tables -->
    <div class="wc-datalens-tables">
        <div class="table-row">
            <div class="table-container">
                <h3><?php _e('Top Products by Views', 'woocommerce-datalens'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Product', 'woocommerce-datalens'); ?></th>
                            <th><?php _e('Views', 'woocommerce-datalens'); ?></th>
                            <th><?php _e('Actions', 'woocommerce-datalens'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics_data['top_products'])): ?>
                            <?php foreach ($analytics_data['top_products'] as $product): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($product->post_title); ?></strong>
                                    </td>
                                    <td><?php echo number_format($product->view_count); ?></td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($product->ID); ?>" class="button button-small">
                                            <?php _e('Edit', 'woocommerce-datalens'); ?>
                                        </a>
                                        <a href="<?php echo get_permalink($product->ID); ?>" class="button button-small" target="_blank">
                                            <?php _e('View', 'woocommerce-datalens'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3"><?php _e('No product views data available.', 'woocommerce-datalens'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="table-container">
                <h3><?php _e('Recent Orders', 'woocommerce-datalens'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Order', 'woocommerce-datalens'); ?></th>
                            <th><?php _e('Status', 'woocommerce-datalens'); ?></th>
                            <th><?php _e('Total', 'woocommerce-datalens'); ?></th>
                            <th><?php _e('Date', 'woocommerce-datalens'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics_data['recent_orders'])): ?>
                            <?php foreach ($analytics_data['recent_orders'] as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo admin_url('post.php?post=' . $order->order_id . '&action=edit'); ?>">
                                            #<?php echo $order->order_id; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="order-status status-<?php echo esc_attr($order->order_status); ?>">
                                            <?php echo wc_get_order_status_name($order->order_status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo wc_price($order->order_total); ?></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($order->order_date)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4"><?php _e('No recent orders available.', 'woocommerce-datalens'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// React charts will initialize automatically
console.log('Dashboard template loaded, waiting for React charts to initialize...');
</script>
