/**
 * Analytics Tracker
 * Lightweight JavaScript snippet for tracking page views
 * Sends data to track.php endpoint asynchronously
 */

(function() {
    'use strict';
    
    // Only track if not a bot and not in admin area
    if (navigator.webdriver || /bot|crawler|spider|crawling/i.test(navigator.userAgent)) {
        return;
    }
    
    // Don't track admin pages
    if (window.location.pathname.indexOf('/admin/') !== -1) {
        return;
    }
    
    // Collect page data
    const pageData = {
        page_url: window.location.pathname + window.location.search,
        page_title: document.title || '',
        referrer: document.referrer || ''
    };
    
    // Send tracking data asynchronously (non-blocking)
    const sendTracking = function() {
        // Use sendBeacon if available (better for page unload)
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(pageData)], { type: 'application/json' });
            navigator.sendBeacon('api/track.php', blob);
        } else {
            // Fallback to fetch API
            fetch('api/track.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(pageData),
                keepalive: true // Ensures request completes even if page unloads
            }).catch(function(error) {
                // Silently fail - analytics should not break user experience
                console.debug('Analytics tracking failed:', error);
            });
        }
    };
    
    // Track immediately on page load
    if (document.readyState === 'complete') {
        sendTracking();
    } else {
        // Wait for page to be fully loaded
        window.addEventListener('load', sendTracking, { once: true });
    }
    
    // Also track on visibility change (for SPA-like behavior)
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            // Small delay to avoid duplicate tracking
            setTimeout(sendTracking, 100);
        }
    });
})();
