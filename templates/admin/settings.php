<?php
/**
 * Settings Template
 *
 * @package WooCommerce_DataLens
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wc-datalens-settings">
    <h1><?php _e('DataLens Settings', 'woocommerce-datalens'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wc_datalens_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="tracking_enabled"><?php _e('Enable Tracking', 'woocommerce-datalens'); ?></label>
                </th>
                <td>
                    <select name="tracking_enabled" id="tracking_enabled">
                        <option value="yes" <?php selected($tracking_enabled, 'yes'); ?>>
                            <?php _e('Yes', 'woocommerce-datalens'); ?>
                        </option>
                        <option value="no" <?php selected($tracking_enabled, 'no'); ?>>
                            <?php _e('No', 'woocommerce-datalens'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('Enable or disable data tracking for analytics.', 'woocommerce-datalens'); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="forecasting_enabled"><?php _e('Enable Forecasting', 'woocommerce-datalens'); ?></label>
                </th>
                <td>
                    <select name="forecasting_enabled" id="forecasting_enabled">
                        <option value="yes" <?php selected($forecasting_enabled, 'yes'); ?>>
                            <?php _e('Yes', 'woocommerce-datalens'); ?>
                        </option>
                        <option value="no" <?php selected($forecasting_enabled, 'no'); ?>>
                            <?php _e('No', 'woocommerce-datalens'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('Enable or disable sales forecasting features.', 'woocommerce-datalens'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'woocommerce-datalens'); ?>">
        </p>
    </form>
    
    <!-- Notification Container -->
    <div id="wc-datalens-notification" class="wc-datalens-notification" style="display: none;">
        <div class="notification-content">
            <span class="notification-message"></span>
            <button type="button" class="notification-close">&times;</button>
        </div>
    </div>
    
    <div class="wc-datalens-order-sync">
        <h2><?php _e('Order Synchronization', 'woocommerce-datalens'); ?></h2>
        
        <p><?php _e('DataLens automatically tracks orders placed through the frontend. However, orders created by other plugins or imported from external sources may not be automatically tracked. Use the options below to sync these orders.', 'woocommerce-datalens'); ?></p>
        
        <div class="sync-actions">
            <button type="button" id="sync-all-orders" class="button button-primary">
                <?php _e('Sync All Orders', 'woocommerce-datalens'); ?>
            </button>
            <button type="button" id="auto-sync-orders" class="button button-secondary">
                <?php _e('Sync New Orders Only', 'woocommerce-datalens'); ?>
            </button>
        </div>
        
        <div id="sync-status" class="sync-status" style="display: none;">
            <div class="sync-message"></div>
            <div class="sync-progress" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>
        </div>
        
        <div class="sync-info">
            <h3><?php _e('Sync Information', 'woocommerce-datalens'); ?></h3>
            <ul>
                <li><strong><?php _e('Sync All Orders:', 'woocommerce-datalens'); ?></strong> <?php _e('Synchronizes all existing orders from WooCommerce to DataLens. This may take some time for stores with many orders.', 'woocommerce-datalens'); ?></li>
                <li><strong><?php _e('Sync New Orders Only:', 'woocommerce-datalens'); ?></strong> <?php _e('Synchronizes only orders that were created since the last sync. This is faster and recommended for regular use.', 'woocommerce-datalens'); ?></li>
                <li><strong><?php _e('Auto-Sync:', 'woocommerce-datalens'); ?></strong> <?php _e('The system automatically syncs new orders every hour in the background.', 'woocommerce-datalens'); ?></li>
            </ul>
        </div>
    </div>
    
    <div class="wc-datalens-info">
        <h2><?php _e('Plugin Information', 'woocommerce-datalens'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Version', 'woocommerce-datalens'); ?></th>
                <td><?php echo WC_DATALENS_VERSION; ?></td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Database Tables', 'woocommerce-datalens'); ?></th>
                <td>
                    <?php
                    global $wpdb;
                    $tables = array(
                        $wpdb->prefix . 'wc_datalens_events',
                        $wpdb->prefix . 'wc_datalens_orders',
                        $wpdb->prefix . 'wc_datalens_product_views'
                    );
                    
                    $existing_tables = array();
                    foreach ($tables as $table) {
                        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
                            $existing_tables[] = $table;
                        }
                    }
                    
                    if (count($existing_tables) === count($tables)) {
                        echo '<span style="color: green;">✓ ' . __('All tables created successfully', 'woocommerce-datalens') . '</span>';
                    } else {
                        echo '<span style="color: red;">✗ ' . __('Some tables are missing', 'woocommerce-datalens') . '</span>';
                    }
                    ?>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Data Retention', 'woocommerce-datalens'); ?></th>
                <td>
                    <?php _e('Analytics data is stored indefinitely. You can manually clean up old data from the database if needed.', 'woocommerce-datalens'); ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="wc-datalens-help">
        <h2><?php _e('Help & Support', 'woocommerce-datalens'); ?></h2>
        
        <div class="help-content">
            <h3><?php _e('What Data is Tracked?', 'woocommerce-datalens'); ?></h3>
            <ul>
                <li><?php _e('WooCommerce order statuses and totals', 'woocommerce-datalens'); ?></li>
                <li><?php _e('Product page views and interactions', 'woocommerce-datalens'); ?></li>
                <li><?php _e('Add to cart and cart view events', 'woocommerce-datalens'); ?></li>
                <li><?php _e('User login and registration activity', 'woocommerce-datalens'); ?></li>
            </ul>
            
            <h3><?php _e('Forecasting Features', 'woocommerce-datalens'); ?></h3>
            <ul>
                <li><?php _e('Weekly sales predictions based on historical data', 'woocommerce-datalens'); ?></li>
                <li><?php _e('Product-specific sales forecasts', 'woocommerce-datalens'); ?></li>
                <li><?php _e('Trend analysis and seasonal patterns', 'woocommerce-datalens'); ?></li>
                <li><?php _e('Confidence levels for predictions', 'woocommerce-datalens'); ?></li>
            </ul>
            
            <h3><?php _e('Privacy & GDPR', 'woocommerce-datalens'); ?></h3>
            <p><?php _e('This plugin tracks user interactions for analytics purposes. Ensure your privacy policy reflects this data collection.', 'woocommerce-datalens'); ?></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.wc-datalens-settings form');
    const notification = document.getElementById('wc-datalens-notification');
    const notificationMessage = notification.querySelector('.notification-message');
    const closeButton = notification.querySelector('.notification-close');
    
    // Store initial form values
    const initialValues = {};
    const formElements = form.querySelectorAll('select, input[type="text"], input[type="number"], textarea');
    formElements.forEach(element => {
        if (element.type === 'checkbox' || element.type === 'radio') {
            initialValues[element.name] = element.checked;
        } else {
            initialValues[element.name] = element.value;
        }
    });
    
    // Check for form changes
    function checkFormChanges() {
        let hasChanges = false;
        formElements.forEach(element => {
            let currentValue;
            if (element.type === 'checkbox' || element.type === 'radio') {
                currentValue = element.checked;
            } else {
                currentValue = element.value;
            }
            
            if (initialValues[element.name] !== currentValue) {
                hasChanges = true;
            }
        });
        
        if (hasChanges) {
            showNotification('Settings have been modified. Click "Save Settings" to apply changes.', 'warning');
        } else {
            hideNotification();
        }
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
        notificationMessage.textContent = message;
        notification.className = `wc-datalens-notification ${type}`;
        notification.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            hideNotification();
        }, 5000);
    }
    
    // Hide notification
    function hideNotification() {
        notification.style.display = 'none';
    }
    
    // Event listeners
    formElements.forEach(element => {
        element.addEventListener('change', checkFormChanges);
        element.addEventListener('input', checkFormChanges);
    });
    
    // Close button
    closeButton.addEventListener('click', hideNotification);
    
    // Form submission
    form.addEventListener('submit', function(e) {
        showNotification('Settings saved successfully!', 'success');
        
        // Update initial values after successful save
        setTimeout(() => {
            formElements.forEach(element => {
                if (element.type === 'checkbox' || element.type === 'radio') {
                    initialValues[element.name] = element.checked;
                } else {
                    initialValues[element.name] = element.value;
                }
            });
        }, 1000);
    });
    
    // Sync button notifications
    const syncAllButton = document.getElementById('sync-all-orders');
    const syncNewButton = document.getElementById('auto-sync-orders');
    
    if (syncAllButton) {
        syncAllButton.addEventListener('click', function() {
            showNotification('Starting full order synchronization...', 'info');
        });
    }
    
    if (syncNewButton) {
        syncNewButton.addEventListener('click', function() {
            showNotification('Starting new order synchronization...', 'info');
        });
    }
});
</script>
