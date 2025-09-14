/**
 * WooCommerce DataLens Frontend JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initFrontendTracking();
    });
    
    function initFrontendTracking() {
        // Track product views
        trackProductView();
        
        // Track add to cart events
        trackAddToCart();
        
        // Track cart view events
        trackCartView();
        
        // Track user interactions
        trackUserInteractions();
    }
    
    function trackProductView() {
        // Only track if we're on a product page and have a product ID
        if (typeof wcDataLens !== 'undefined' && wcDataLens.productId) {
            // Track initial page view
            trackEvent('product_view', {
                product_id: wcDataLens.productId,
                page_type: 'product',
                timestamp: new Date().toISOString()
            });
            
            // Track time spent on product page
            let startTime = Date.now();
            $(window).on('beforeunload', function() {
                const timeSpent = Math.round((Date.now() - startTime) / 1000);
                if (timeSpent > 5) { // Only track if spent more than 5 seconds
                    trackEvent('product_time_spent', {
                        product_id: wcDataLens.productId,
                        time_spent_seconds: timeSpent
                    });
                }
            });
        }
    }
    
    function trackAddToCart() {
        // Track WooCommerce add to cart events
        $(document.body).on('added_to_cart', function(event, fragments, cart_hash, button) {
            const $button = $(button);
            const productId = $button.data('product_id') || $button.closest('form').find('input[name="add-to-cart"]').val();
            
            if (productId) {
                trackEvent('add_to_cart', {
                    product_id: productId,
                    button_text: $button.text().trim(),
                    timestamp: new Date().toISOString()
                });
            }
        });
        
        // Track custom add to cart buttons
        $('.add_to_cart_button, .single_add_to_cart_button').on('click', function() {
            const $button = $(this);
            const productId = $button.data('product_id') || $button.closest('form').find('input[name="add-to-cart"]').val();
            
            if (productId) {
                trackEvent('add_to_cart_click', {
                    product_id: productId,
                    button_text: $button.text().trim(),
                    timestamp: new Date().toISOString()
                });
            }
        });
    }
    
    function trackCartView() {
        // Track when user views cart
        if (window.location.pathname.includes('/cart/') || window.location.pathname.includes('/basket/')) {
            trackEvent('cart_view', {
                page_type: 'cart',
                timestamp: new Date().toISOString()
            });
            
            // Track cart items
            const cartItems = [];
            $('.cart_item').each(function() {
                const $item = $(this);
                const productId = $item.data('product_id');
                const quantity = $item.find('.quantity input').val() || $item.find('.qty').text();
                const price = $item.find('.product-price .amount').text();
                
                if (productId) {
                    cartItems.push({
                        product_id: productId,
                        quantity: quantity,
                        price: price
                    });
                }
            });
            
            if (cartItems.length > 0) {
                trackEvent('cart_items_view', {
                    items: cartItems,
                    item_count: cartItems.length,
                    timestamp: new Date().toISOString()
                });
            }
        }
    }
    
    function trackUserInteractions() {
        // Track scroll depth
        let maxScroll = 0;
        $(window).on('scroll', function() {
            const scrollPercent = Math.round(($(window).scrollTop() / ($(document).height() - $(window).height())) * 100);
            if (scrollPercent > maxScroll) {
                maxScroll = scrollPercent;
                
                // Track at certain scroll milestones
                if (maxScroll >= 25 && maxScroll < 50) {
                    trackEvent('scroll_25_percent', {
                        scroll_percent: maxScroll,
                        page_url: window.location.href
                    });
                } else if (maxScroll >= 50 && maxScroll < 75) {
                    trackEvent('scroll_50_percent', {
                        scroll_percent: maxScroll,
                        page_url: window.location.href
                    });
                } else if (maxScroll >= 75 && maxScroll < 100) {
                    trackEvent('scroll_75_percent', {
                        scroll_percent: maxScroll,
                        page_url: window.location.href
                    });
                } else if (maxScroll >= 100) {
                    trackEvent('scroll_100_percent', {
                        scroll_percent: maxScroll,
                        page_url: window.location.href
                    });
                }
            }
        });
        
        // Track form interactions
        $('form').on('submit', function() {
            const $form = $(this);
            const formId = $form.attr('id') || $form.attr('class') || 'unknown_form';
            
            trackEvent('form_submit', {
                form_id: formId,
                form_action: $form.attr('action'),
                timestamp: new Date().toISOString()
            });
        });
        
        // Track external link clicks
        $('a[href^="http"]').on('click', function() {
            const $link = $(this);
            const href = $link.attr('href');
            
            // Don't track if it's the same domain
            if (href.indexOf(window.location.hostname) === -1) {
                trackEvent('external_link_click', {
                    link_url: href,
                    link_text: $link.text().trim(),
                    timestamp: new Date().toISOString()
                });
            }
        });
        
        // Track search queries
        $('form[role="search"], .search-form').on('submit', function() {
            const $form = $(this);
            const $input = $form.find('input[name="s"], input[type="search"]');
            const query = $input.val();
            
            if (query && query.trim().length > 0) {
                trackEvent('search_query', {
                    search_term: query.trim(),
                    timestamp: new Date().toISOString()
                });
            }
        });
    }
    
    function trackEvent(eventType, eventData) {
        // Check if tracking is enabled
        if (typeof wcDataLens === 'undefined') {
            return;
        }
        
        // Add common data
        const data = {
            event_type: eventType,
            event_data: eventData,
            page_url: window.location.href,
            page_title: document.title,
            user_agent: navigator.userAgent,
            screen_resolution: screen.width + 'x' + screen.height,
            timestamp: new Date().toISOString()
        };
        
        // Add user ID if available
        if (wcDataLens.userId) {
            data.user_id = wcDataLens.userId;
        }
        
        // Send tracking data via AJAX
        $.ajax({
            url: wcDataLens.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wc_datalens_track_event',
                nonce: wcDataLens.nonce,
                event_type: eventType,
                event_data: JSON.stringify(eventData)
            },
            success: function(response) {
                // Optional: Log successful tracking
                if (window.console && console.debug) {
                    console.debug('DataLens: Event tracked successfully', eventType, eventData);
                }
            },
            error: function(xhr, status, error) {
                // Optional: Log tracking errors
                if (window.console && console.warn) {
                    console.warn('DataLens: Failed to track event', eventType, error);
                }
            }
        });
        
        // Also send to Google Analytics if available
        if (typeof gtag !== 'undefined') {
            gtag('event', eventType, {
                event_category: 'DataLens',
                event_label: JSON.stringify(eventData),
                value: 1
            });
        }
        
        // Send to Facebook Pixel if available
        if (typeof fbq !== 'undefined') {
            fbq('track', 'CustomEvent', {
                event_type: eventType,
                event_data: eventData
            });
        }
    }
    
    // Track page load time
    $(window).on('load', function() {
        const loadTime = performance.now();
        trackEvent('page_load', {
            load_time_ms: Math.round(loadTime),
            page_url: window.location.href
        });
    });
    
    // Track user engagement
    let engagementStartTime = Date.now();
    let isEngaged = false;
    
    // Mark as engaged after 10 seconds
    setTimeout(function() {
        isEngaged = true;
        trackEvent('user_engaged', {
            engagement_time_ms: Date.now() - engagementStartTime,
            page_url: window.location.href
        });
    }, 10000);
    
    // Track when user leaves the page
    $(window).on('beforeunload', function() {
        if (isEngaged) {
            const totalTime = Date.now() - engagementStartTime;
            trackEvent('user_exit', {
                total_time_ms: totalTime,
                page_url: window.location.href
            });
        }
    });
    
    // Track mobile vs desktop usage
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    trackEvent('device_type', {
        device_type: isMobile ? 'mobile' : 'desktop',
        user_agent: navigator.userAgent
    });
    
    // Track browser information
    trackEvent('browser_info', {
        browser: getBrowserInfo(),
        language: navigator.language,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
    });
    
    function getBrowserInfo() {
        const userAgent = navigator.userAgent;
        let browser = 'Unknown';
        
        if (userAgent.indexOf('Chrome') > -1) {
            browser = 'Chrome';
        } else if (userAgent.indexOf('Safari') > -1) {
            browser = 'Safari';
        } else if (userAgent.indexOf('Firefox') > -1) {
            browser = 'Firefox';
        } else if (userAgent.indexOf('Edge') > -1) {
            browser = 'Edge';
        } else if (userAgent.indexOf('MSIE') > -1 || userAgent.indexOf('Trident/') > -1) {
            browser = 'Internet Explorer';
        }
        
        return browser;
    }
    
    // Export tracking function for external use
    window.DataLensTrack = trackEvent;
    
})(jQuery);
