<?php
/**
 * DataLens Sales Forecasting Template
 *
 * @package WC_DataLens
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get forecast data
$forecast_data = WC_DataLens_Forecasting::get_forecast_data();
?>

<div class="wrap wc-datalens-forecasting">
    <style>
        .wc-datalens-forecasting {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        
        .forecast-summary {
            margin-bottom: 30px;
        }
        
        .forecast-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .forecast-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-header .dashicons {
            font-size: 24px;
            margin-right: 10px;
            color: #0073aa;
        }
        
        .card-header h3 {
            margin: 0;
            color: #23282d;
        }
        
        .forecast-number {
            font-size: 32px;
            font-weight: bold;
            color: #0073aa;
            margin-bottom: 10px;
        }
        
        .confidence {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
        
        .trend-analysis {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .trend-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .trend-item {
            text-align: center;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
        }
        
        .trend-item h4 {
            margin: 0 0 15px 0;
            color: #23282d;
            font-size: 16px;
            font-weight: 600;
        }
        
        .trend-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .trend-value.positive {
            color: #46b450;
        }
        
        .trend-value.negative {
            color: #dc3232;
        }
        
        .trend-item p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .product-forecast {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .forecast-insights {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .insights-content {
            margin-top: 20px;
        }
        
        .insight {
            display: flex;
            align-items: flex-start;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            border-left: 4px solid;
        }
        
        .insight.positive {
            background: #f0f8f0;
            border-left-color: #46b450;
        }
        
        .insight.warning {
            background: #fffbf0;
            border-left-color: #ffb900;
        }
        
        .insight.negative {
            background: #fef7f1;
            border-left-color: #dc3232;
        }
        
        .insight .dashicons {
            font-size: 20px;
            margin-right: 10px;
            margin-top: 2px;
        }
        
        .insight.positive .dashicons {
            color: #46b450;
        }
        
        .insight.warning .dashicons {
            color: #ffb900;
        }
        
        .insight.negative .dashicons {
            color: #dc3232;
        }
        
        .insight p {
            margin: 0;
            color: #23282d;
        }
        
        .confidence-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .confidence-badge.high {
            background: #46b450;
            color: #fff;
        }
        
        .confidence-badge.medium {
            background: #ffb900;
            color: #fff;
        }
        
        .confidence-badge.low {
            background: #dc3232;
            color: #fff;
        }
        
        @media (max-width: 768px) {
            .trend-grid {
                grid-template-columns: 1fr;
            }
            
            .forecast-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <h1><?php _e('DataLens Sales Forecasting', 'wc-datalens'); ?></h1>
    
    <!-- Weekly Forecast Summary -->
    <div class="forecast-summary">
        <h2><?php _e('Next Week Forecast', 'wc-datalens'); ?></h2>
        
        <div class="forecast-cards">
            <div class="forecast-card">
                <div class="card-header">
                    <span class="dashicons dashicons-cart"></span>
                    <h3><?php _e('Orders', 'wc-datalens'); ?></h3>
                </div>
                <div class="card-content">
                                    <div class="forecast-number"><?php echo isset($forecast_data['weekly_forecast']['predicted_orders']) ? number_format($forecast_data['weekly_forecast']['predicted_orders']) : '0'; ?></div>
                <p><?php _e('Predicted Orders', 'wc-datalens'); ?></p>
                <div class="confidence">
                    <?php printf(__('Confidence: %s', 'wc-datalens'), ucfirst(isset($forecast_data['weekly_forecast']['confidence_level']) ? $forecast_data['weekly_forecast']['confidence_level'] : 'medium')); ?>
                </div>
                </div>
            </div>
            
            <div class="forecast-card">
                <div class="card-header">
                    <span class="dashicons dashicons-money-alt"></span>
                    <h3><?php _e('Revenue', 'wc-datalens'); ?></h3>
                </div>
                <div class="card-content">
                                    <div class="forecast-number"><?php echo isset($forecast_data['weekly_forecast']['predicted_revenue']) ? wc_price($forecast_data['weekly_forecast']['predicted_revenue']) : wc_price(0); ?></div>
                <p><?php _e('Predicted Revenue', 'wc-datalens'); ?></p>
                <div class="confidence">
                    <?php printf(__('Confidence: %s', 'wc-datalens'), ucfirst(isset($forecast_data['weekly_forecast']['confidence_level']) ? $forecast_data['weekly_forecast']['confidence_level'] : 'medium')); ?>
                </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Trend Analysis -->
    <div class="trend-analysis">
        <h2><?php _e('Trend Analysis', 'wc-datalens'); ?></h2>
        
        <div class="trend-grid">
            <div class="trend-item">
                <h4><?php _e('Orders Trend', 'wc-datalens'); ?></h4>
                <div class="trend-value <?php echo isset($forecast_data['trend_analysis']['orders_trend']) && $forecast_data['trend_analysis']['orders_trend'] > 0 ? 'positive' : 'negative'; ?>">
                    <?php echo isset($forecast_data['trend_analysis']['orders_trend']) && $forecast_data['trend_analysis']['orders_trend'] > 0 ? '+' : ''; ?><?php echo isset($forecast_data['trend_analysis']['orders_trend']) ? number_format($forecast_data['trend_analysis']['orders_trend'], 1) : '0.0'; ?>%
                </div>
                <p><?php _e('vs previous period', 'wc-datalens'); ?></p>
            </div>
            
            <div class="trend-item">
                <h4><?php _e('Revenue Trend', 'wc-datalens'); ?></h4>
                <div class="trend-value <?php echo isset($forecast_data['trend_analysis']['revenue_trend']) && $forecast_data['trend_analysis']['revenue_trend'] > 0 ? 'positive' : 'negative'; ?>">
                    <?php echo isset($forecast_data['trend_analysis']['revenue_trend']) && $forecast_data['trend_analysis']['revenue_trend'] > 0 ? '+' : ''; ?><?php echo isset($forecast_data['trend_analysis']['revenue_trend']) ? number_format($forecast_data['trend_analysis']['revenue_trend'], 1) : '0.0'; ?>%
                </div>
                <p><?php _e('vs previous period', 'wc-datalens'); ?></p>
            </div>
            
            <div class="trend-item">
                <h4><?php _e('Average Order Value', 'wc-datalens'); ?></h4>
                <div class="trend-value <?php echo isset($forecast_data['trend_analysis']['avg_order_trend']) && $forecast_data['trend_analysis']['avg_order_trend'] > 0 ? 'positive' : 'negative'; ?>">
                    <?php echo isset($forecast_data['trend_analysis']['avg_order_trend']) && $forecast_data['trend_analysis']['avg_order_trend'] > 0 ? '+' : ''; ?><?php echo isset($forecast_data['trend_analysis']['avg_order_trend']) ? number_format($forecast_data['trend_analysis']['avg_order_trend'], 1) : '0.0'; ?>%
                </div>
                <p><?php _e('vs previous period', 'wc-datalens'); ?></p>
            </div>
            
            <div class="trend-item">
                <h4><?php _e('Product Views', 'wc-datalens'); ?></h4>
                <div class="trend-value <?php echo isset($forecast_data['trend_analysis']['views_trend']) && $forecast_data['trend_analysis']['views_trend'] > 0 ? 'positive' : 'negative'; ?>">
                    <?php echo isset($forecast_data['trend_analysis']['views_trend']) && $forecast_data['trend_analysis']['views_trend'] > 0 ? '+' : ''; ?><?php echo isset($forecast_data['trend_analysis']['views_trend']) ? number_format($forecast_data['trend_analysis']['views_trend'], 1) : '0.0'; ?>%
                </div>
                <p><?php _e('vs previous period', 'wc-datalens'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Product Sales Forecast -->
    <div class="product-forecast">
        <h2><?php _e('Product Sales Forecast - Next Week', 'wc-datalens'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Product', 'wc-datalens'); ?></th>
                    <th><?php _e('Recent Views', 'wc-datalens'); ?></th>
                    <th><?php _e('Recent Orders', 'wc-datalens'); ?></th>
                    <th><?php _e('Conversion Rate', 'wc-datalens'); ?></th>
                    <th><?php _e('Predicted Views', 'wc-datalens'); ?></th>
                    <th><?php _e('Predicted Sales', 'wc-datalens'); ?></th>
                    <th><?php _e('Confidence', 'wc-datalens'); ?></th>
                    <th><?php _e('Actions', 'wc-datalens'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($forecast_data['product_forecast'])): ?>
                    <?php foreach ($forecast_data['product_forecast'] as $product): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html(isset($product['product_name']) ? $product['product_name'] : 'Unknown Product'); ?></strong>
                            </td>
                            <td><?php echo number_format(isset($product['recent_views']) ? $product['recent_views'] : 0); ?></td>
                            <td><?php echo number_format(isset($product['recent_orders']) ? $product['recent_orders'] : 0); ?></td>
                            <td><?php echo number_format((isset($product['conversion_rate']) ? $product['conversion_rate'] : 0) * 100, 2); ?>%</td>
                            <td><?php echo number_format(isset($product['predicted_views']) ? $product['predicted_views'] : 0); ?></td>
                            <td>
                                <strong><?php echo number_format(isset($product['predicted_sales']) ? $product['predicted_sales'] : 0); ?></strong>
                            </td>
                            <td>
                                <span class="confidence-badge <?php echo esc_attr(isset($product['confidence']) ? $product['confidence'] : 'medium'); ?>">
                                    <?php echo ucfirst(isset($product['confidence']) ? $product['confidence'] : 'medium'); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo get_edit_post_link(isset($product['product_id']) ? $product['product_id'] : 0); ?>" class="button button-small">
                                    <?php _e('Edit', 'wc-datalens'); ?>
                                </a>
                                <a href="<?php echo get_permalink(isset($product['product_id']) ? $product['product_id'] : 0); ?>" class="button button-small" target="_blank">
                                    <?php _e('View', 'wc-datalens'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8"><?php _e('No product forecast data available.', 'wc-datalens'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Forecast Insights -->
    <div class="forecast-insights">
        <h2><?php _e('Forecast Insights', 'wc-datalens'); ?></h2>
        
        <div class="insights-content">
            <?php 
            $confidence_level = isset($forecast_data['weekly_forecast']['confidence_level']) ? $forecast_data['weekly_forecast']['confidence_level'] : 'medium';
            if ($confidence_level === 'high'): ?>
                <div class="insight positive">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <p><?php _e('High confidence in forecast based on consistent historical data patterns.', 'wc-datalens'); ?></p>
                </div>
            <?php elseif ($confidence_level === 'medium'): ?>
                <div class="insight warning">
                    <span class="dashicons dashicons-warning"></span>
                    <p><?php _e('Medium confidence in forecast. Consider seasonal factors and recent trends.', 'wc-datalens'); ?></p>
                </div>
            <?php else: ?>
                <div class="insight negative">
                    <span class="dashicons dashicons-no-alt"></span>
                    <p><?php _e('Low confidence in forecast due to insufficient or inconsistent data.', 'wc-datalens'); ?></p>
                </div>
            <?php endif; ?>
            
            <?php 
            $orders_trend = isset($forecast_data['trend_analysis']['orders_trend']) ? $forecast_data['trend_analysis']['orders_trend'] : 0;
            if ($orders_trend > 10): ?>
                <div class="insight positive">
                    <span class="dashicons dashicons-trending-up"></span>
                    <p><?php _e('Strong upward trend in orders. Consider increasing inventory for high-performing products.', 'wc-datalens'); ?></p>
                </div>
            <?php elseif ($orders_trend < -10): ?>
                <div class="insight negative">
                    <span class="dashicons dashicons-trending-down"></span>
                    <p><?php _e('Declining order trend. Review marketing strategies and product offerings.', 'wc-datalens'); ?></p>
                </div>
            <?php endif; ?>
            
            <?php 
            if (!empty($forecast_data['product_forecast'])):
                $top_product = reset($forecast_data['product_forecast']);
                if ($top_product && isset($top_product['predicted_sales']) && $top_product['predicted_sales'] > 5): 
                ?>
                    <div class="insight positive">
                        <span class="dashicons dashicons-star-filled"></span>
                        <p><?php 
                        /* translators: %1$s: product name, %2$d: expected sales */
                        printf(__('"%1$s" is predicted to be your top seller next week with %2$d expected sales.', 'wc-datalens'), 
                            esc_html($top_product['product_name']), 
                            $top_product['predicted_sales']); ?></p>
                    </div>
                <?php endif; 
            endif; ?>
        </div>
    </div>
</div>
