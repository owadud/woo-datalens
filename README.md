# WooCommerce DataLens - Business Analytics Dashboard

A comprehensive business analytics dashboard for WooCommerce store owners with forecasting capabilities.

## Features

- **Real-time Analytics Dashboard**: Track orders, revenue, product views, and user activity
- **Sales Forecasting**: Predict future sales with confidence levels
- **Trend Analysis**: Monitor performance trends over time
- **Product Performance**: Identify top-performing products
- **Order Synchronization**: Sync existing WooCommerce orders
- **Responsive Design**: Works on desktop and mobile devices
- **HPOS Compatible**: Full support for WooCommerce High-Performance Order Storage

## HPOS Compatibility

WooCommerce DataLens is fully compatible with WooCommerce High-Performance Order Storage (HPOS), providing:

- **Automatic Detection**: Seamlessly detects and adapts to HPOS status
- **Performance Optimization**: Enhanced performance when HPOS is enabled
- **Backward Compatibility**: Works with traditional WooCommerce storage
- **Future-Proof**: Aligns with WooCommerce's long-term architecture

### HPOS Benefits

- **Faster Order Queries**: Dedicated tables with proper indexing
- **Better Scalability**: Improved performance with large numbers of orders
- **Reduced Database Load**: No complex JOINs with posts table
- **Enhanced Caching**: HPOS-specific caching mechanisms

For detailed HPOS compatibility information, see [HPOS_COMPATIBILITY.md](HPOS_COMPATIBILITY.md).

## Installation

1. Upload the plugin files to `/wp-content/plugins/woocommerce-datalens/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure WooCommerce is installed and activated
4. Navigate to **WordPress Admin → DataLens** to access the dashboard

## Quick Start

### 1. Initial Setup
After activation, the plugin will:
- Create necessary database tables
- Populate sample data (if no existing data is found)
- Set up automatic order tracking

### 2. Access the Dashboard
- Go to **WordPress Admin → DataLens**
- View your analytics data in the dashboard
- Use the period filter to analyze different time ranges

### 3. Sync Existing Orders
If you have existing WooCommerce orders:
- Go to **WordPress Admin → DataLens → Settings**
- Click "Sync All Orders" to import existing orders
- Or use "Sync New Orders Only" for incremental sync

### 4. View Forecasting
- Go to **WordPress Admin → DataLens → Forecasting**
- View sales predictions and trend analysis
- Check confidence levels for forecasts

## Troubleshooting

### Charts Not Displaying Data

**Issue**: Charts show empty or no data
**Solution**: 
1. Run the test script: `https://yoursite.com/test-datalens.php`
2. Check if sample data was populated
3. Ensure you have WooCommerce orders or products
4. Try syncing orders manually in Settings

### Forecasting Shows Low Confidence

**Issue**: Always shows "Low confidence in forecast"
**Solution**:
1. Ensure you have at least 2 weeks of order data
2. Check that orders have "completed" or "processing" status
3. More data will improve confidence levels
4. The confidence calculation has been improved in this version

### Security Check Failed

**Issue**: "Error: Security check failed" when syncing orders
**Solution**:
1. This has been fixed in the current version
2. Ensure you're using the latest plugin files
3. Clear browser cache and try again
4. Check that you're logged in as an administrator

### No Data Available

**Issue**: Dashboard shows "No data available"
**Solution**:
1. The plugin automatically populates sample data if no real data exists
2. Check if you have WooCommerce products
3. Create some test orders in WooCommerce
4. Use the sync feature to import existing orders

## Testing the Plugin

Use the included test script to verify everything is working:

1. Upload `test-datalens.php` to your WordPress root directory
2. Visit `https://yoursite.com/test-datalens.php`
3. Follow the test results and recommendations

## Data Collection

The plugin tracks the following data:

### Orders Data
- Order ID, status, total, date
- Customer information
- Payment and shipping methods

### Product Views
- Product page visits
- User session information
- IP addresses (for analytics)

### User Activity
- Login events
- Registration events
- Add to cart actions
- Cart views

### Events
- All user interactions
- Session tracking
- User agent information

## Privacy & GDPR

This plugin collects user interaction data for analytics purposes. Ensure your privacy policy reflects this data collection. The plugin stores:

- IP addresses (for analytics)
- User session data
- Order and product interaction data
- User agent strings

## Database Tables

The plugin creates three main tables:

1. `wp_wc_datalens_events` - User interaction events
2. `wp_wc_datalens_orders` - Order tracking data
3. `wp_wc_datalens_product_views` - Product view tracking

## API Endpoints

The plugin provides AJAX endpoints for:

- `wc_datalens_get_analytics` - Retrieve analytics data
- `wc_datalens_get_forecast` - Retrieve forecasting data
- `wc_datalens_sync_orders` - Manual order sync
- `wc_datalens_auto_sync_orders` - Auto order sync
- `wc_datalens_track_event` - Track user events

## Customization

### Adding Custom Events
```php
// Track a custom event
do_action('wc_datalens_track_event', 'custom_event', array(
    'custom_data' => 'value'
));
```

### Modifying Chart Colors
Edit `assets/css/admin.css` to customize chart colors and styling.

### Extending Analytics
The plugin uses a modular structure. You can extend the `WC_DataLens_Dashboard` class to add custom analytics.

## Support

For issues and questions:

1. Run the test script first: `test-datalens.php`
2. Check the troubleshooting section above
3. Ensure WooCommerce is properly configured
4. Verify database tables exist and have data

## Changelog

### Version 1.0.0 (Fixed)
- Fixed chart data display issues
- Improved forecasting confidence calculation
- Fixed security check failures
- Added sample data population
- Enhanced error handling
- Improved chart responsiveness
- Added comprehensive testing script

## Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## License

GPL v2 or later

## Credits

Developed for WooCommerce store owners who need comprehensive business analytics and forecasting capabilities.
# woo-datalens
