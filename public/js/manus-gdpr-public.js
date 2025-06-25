jQuery(document).ready(function($) {
    // Show overlay when banner is present
    function showOverlay() {
        var $overlay = $('.manus-gdpr-overlay');
        if ($overlay.length) {
            $overlay.addClass('show');
        }
    }
    
    // Hide overlay
    function hideOverlay() {
        var $overlay = $('.manus-gdpr-overlay');
        if ($overlay.length) {
            $overlay.removeClass('show');
        }
    }
    
    // Initialize overlay when banner is visible
    $(document).ready(function() {
        var $banner = $('#manus-gdpr-cookie-banner');
        if ($banner.length && $banner.is(':visible')) {
            setTimeout(showOverlay, 100); // Small delay for smooth transition
        }
    });

    // Handle consent actions
    $(document).on(
        'click',
        '#manus-gdpr-accept-button, #manus-gdpr-reject-button',
        function(e) {
            e.preventDefault();

            var consentStatus = $(this).data('consent-status');
            var consentData = {};

            // Set all categories based on action
            if (consentStatus === 'accepted') {
                consentData = {
                    necessary: true,
                    analytics: true,
                    advertising: true,
                    functional: true
                };
            } else if (consentStatus === 'rejected') {
                consentData = {
                    necessary: true,
                    analytics: false,
                    advertising: false,
                    functional: false
                };
            }

            recordConsent(consentStatus, consentData);
        }
    );

    // Handle manage preferences button
    $(document).on('click', '#manus-gdpr-manage-button', function(e) {
        e.preventDefault();
        var $modal = $('#manus-gdpr-preferences-modal');
        $modal.css('display', 'block').addClass('show');
    });

    // Handle modal close
    $(document).on('click', '.manus-gdpr-close', function(e) {
        e.preventDefault();
        var $modal = $('#manus-gdpr-preferences-modal');
        $modal.removeClass('show');
        setTimeout(function() {
            $modal.css('display', 'none');
        }, 300); // Matches CSS transition duration
    });

    // Close modal when clicking outside
    $(document).on('click', '#manus-gdpr-preferences-modal', function(e) {
        if (e.target === this) {
            var $modal = $(this);
            $modal.removeClass('show');
            setTimeout(function() {
                $modal.css('display', 'none');
            }, 300); // Matches CSS transition duration
        }
    });

    // Handle save preferences
    $(document).on('click', '#manus-gdpr-save-preferences', function(e) {
        e.preventDefault();

        var consentData = {
            necessary: true // Always true
        };

        // Get preferences from checkboxes
        $('#manus-gdpr-preferences-modal input[type="checkbox"]').each(function() {
            var category = $(this).data('category');
            if (category && category !== 'necessary') {
                consentData[category] = $(this).is(':checked');
            }
        });

        var consentStatus = 'partial';
        // Check if all are accepted or all are rejected
        var nonNecessary = Object.keys(consentData).filter(key => key !== 'necessary');
        var allAccepted = nonNecessary.every(key => consentData[key]);
        var allRejected = nonNecessary.every(key => !consentData[key]);

        if (allAccepted) {
            consentStatus = 'accepted';
        } else if (allRejected) {
            consentStatus = 'rejected';
        }

        recordConsent(consentStatus, consentData);
        var $modal = $('#manus-gdpr-preferences-modal');
        $modal.removeClass('show');
        setTimeout(function() {
            $modal.css('display', 'none');
        }, 300); // Matches CSS transition duration
    });

    // Function to record consent
    function recordConsent(consentStatus, consentData) {
        $.ajax({
            url: manus_gdpr_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'manus_gdpr_consent_action',
                manus_gdpr_consent_nonce: manus_gdpr_ajax.nonce,
                consent_status: consentStatus,
                consent_data: JSON.stringify(consentData)
            },
            success: function(response) {
                if (response.success) {
                    // Hide overlay first, then banner
                    hideOverlay();
                    
                    $('#manus-gdpr-cookie-banner').fadeOut(function() {
                        // Show floating icon after banner is hidden
                        showFloatingIcon();
                    });
                    
                    // Emit TCF event for IAB compatibility
                    var tcfEvent = new CustomEvent('manus-gdpr-consent-updated', {
                        detail: {
                            consentStatus: consentStatus,
                            consentData: consentData
                        }
                    });
                    document.dispatchEvent(tcfEvent);
                    
                    // Update TCF API if available
                    if (typeof window.__tcfapi === 'function') {
                        console.log('Manus GDPR: Updating TCF API with new consent');
                        
                        // Trigger TCF update
                        setTimeout(function() {
                            if (window.__tcfapi.eventListeners && window.__tcfapi.eventListeners.length > 0) {
                                window.__tcfapi.eventListeners.forEach(function(listener) {
                                    window.__tcfapi('getTCData', 2, listener.callback, listener.id);
                                });
                            }
                        }, 100);
                    }
                    
                    // Don't reload page, just update the UI
                    console.log('Manus GDPR: Consent recorded successfully', consentData);
                    
                    // Set a cookie to remember consent for floating icon
                    setCookie('manus_gdpr_consent', JSON.stringify({
                        status: consentStatus,
                        data: consentData,
                        timestamp: Date.now()
                    }), 365);
                } else {
                    console.error('Error:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    // TCF API test function for debugging
    window.testTCFAPI = function() {
        if (typeof window.__tcfapi === 'function') {
            console.group('TCF API Test');
            
            // Test ping
            window.__tcfapi('ping', 2, function(pingReturn, success) {
                console.log('Ping result:', pingReturn, 'Success:', success);
            });
            
            // Test getTCData
            window.__tcfapi('getTCData', 2, function(tcData, success) {
                console.log('TC Data:', tcData, 'Success:', success);
                if (tcData) {
                    console.log('TC String:', tcData.tcString);
                    console.log('GDPR Applies:', tcData.gdprApplies);
                    console.log('Purpose Consents:', tcData.purpose.consents);
                }
            });
            
            console.groupEnd();
        } else {
            console.error('TCF API not available. Make sure IAB TCF v2.2 is enabled in settings.');
        }
    };

    // Function to show floating icon
    function showFloatingIcon() {
        var $floatingIcon = $('#manus-gdpr-floating-icon');
        if ($floatingIcon.length) {
            $floatingIcon.css('display', 'flex'); // Force display
            $floatingIcon.addClass('show').fadeIn(500);
        }
    }

    // Function to hide floating icon
    function hideFloatingIcon() {
        var $floatingIcon = $('#manus-gdpr-floating-icon');
        if ($floatingIcon.length) {
            $floatingIcon.removeClass('show').fadeOut(300);
        }
    }

    // Handle floating icon click
    $(document).on('click', '#manus-gdpr-floating-icon', function(e) {
        e.preventDefault();
        var $modal = $('#manus-gdpr-preferences-modal');
        $modal.css('display', 'block').addClass('show');
    });

    // Check if consent has already been given and show floating icon
    function checkConsentStatus() {
        // First check if banner is present and visible
        var $banner = $('#manus-gdpr-cookie-banner');
        
        if ($banner.length === 0 || $banner.is(':hidden') || $banner.css('display') === 'none') {
            // Banner is not present or hidden, check for consent cookie
            if (getCookie('manus_gdpr_consent') !== null) {
                showFloatingIcon();
            }
        } else {
            // Banner is visible, don't show floating icon
            hideFloatingIcon();
        }
    }

    // Helper function to get cookie value
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) {
            return parts.pop().split(";").shift();
        }
        return null;
    }
    
    // Helper function to set cookie
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    // Check if consent has already been given and show floating icon
    function checkConsentStatus() {
        console.log('Manus GDPR: Checking consent status...');
        
        // Check if banner is present and visible
        var $banner = $('#manus-gdpr-cookie-banner');
        
        // If banner is visible, don't show floating icon
        if ($banner.length > 0 && $banner.is(':visible') && $banner.css('display') !== 'none') {
            console.log('Manus GDPR: Banner is visible, hiding floating icon');
            hideFloatingIcon();
            return;
        }
        
        // Check for consent cookie
        var consentCookie = getCookie('manus_gdpr_consent');
        if (consentCookie) {
            try {
                var consentData = JSON.parse(decodeURIComponent(consentCookie));
                console.log('Manus GDPR: Found consent cookie, showing floating icon', consentData);
                showFloatingIcon();
            } catch (e) {
                console.log('Manus GDPR: Invalid consent cookie format, checking simple cookie');
                // Fallback for simple cookie format
                showFloatingIcon();
            }
        } else {
            console.log('Manus GDPR: No consent cookie found, hiding floating icon');
            hideFloatingIcon();
        }
    }

    // Initialize on page load
    $(document).ready(function() {
        // Delay to ensure all CSS is loaded
        setTimeout(checkConsentStatus, 1000);
    });

    // Make floating icon keyboard accessible
    $(document).on('keydown', '#manus-gdpr-floating-icon', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
        }
    });

    // Add tabindex for accessibility
    $(document).ready(function() {
        $('#manus-gdpr-floating-icon').attr('tabindex', '0');
    });
});


