/**
 * WooCommerce DataLens Admin - Simple PHP-based version
 */
jQuery(document).ready(function($) {
    
    console.log('✅ DataLens Admin Simple Version');
    
    // Show/hide custom date range
    $('#period-filter').on('change', function() {
        const customDateRange = $('.custom-date-range');
        if ($(this).val() === 'custom') {
            customDateRange.show();
        } else {
            customDateRange.hide();
        }
    });
    
    // Handle filter form submission
    $('.wc-datalens-filter form').on('submit', function(e) {
        const submitButton = $(this).find('.button-primary');
        
        // Add loading state
        submitButton.prop('disabled', true).text('Loading...');
        
        // Let the form submit normally (PHP will handle everything)
        // Don't prevent default - let the page reload with new data
        console.log('📋 Form submitted - reloading with new data...');
    });
    
    // Order sync functionality (simplified)
    $('#sync-all-orders, #auto-sync-orders').on('click', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const originalText = button.text();
        const action = button.attr('id') === 'sync-all-orders' ? 'wc_datalens_sync_orders' : 'wc_datalens_auto_sync_orders';
        
        // Show loading state
        button.addClass('updating-message').text('Syncing...').prop('disabled', true);
        
        $.ajax({
            url: wcDataLens.ajaxUrl,
            type: 'POST',
            data: {
                action: action,
                nonce: wcDataLens.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('<div class="notice notice-success"><p>' + response.data.message + '</p></div>')
                        .insertAfter('.wp-header-end').delay(3000).fadeOut();
                    
                    // Reload page to show updated data
                    setTimeout(() => location.reload(), 2000);
                } else {
                    alert('Error: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error: ' + error);
            },
            complete: function() {
                button.removeClass('updating-message').text(originalText).prop('disabled', false);
            }
        });
    });
    
});
