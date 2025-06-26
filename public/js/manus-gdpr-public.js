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
        
        // Initialize modal preferences with current consent state
        // This ensures the modal shows correct state even if opened on first load
        if ($('#manus-gdpr-preferences-modal').length) {
            setTimeout(function() {
                applyCurrentConsentToModal();
            }, 200); // Small delay to ensure DOM is fully ready
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
        
        // Apply current consent state to modal when opening
        setTimeout(function() {
            applyCurrentConsentToModal();
        }, 50); // Small delay to ensure modal is visible
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
                    
                        // Update cookie blocking system
                        if (window.ManusGDPRCookieBlocker) {
                            if (consentStatus === 'rejected' || 
                                (consentStatus === 'partial' && !consentData.advertising)) {
                                console.log('GDPR: Clearing blocked cookies and content');
                                window.ManusGDPRCookieBlocker.clearBlockedCookies();
                                
                                // Remove AdSense elements if advertising consent is denied
                                var adsenseElements = document.querySelectorAll('.adsbygoogle, ins.adsbygoogle');
                                adsenseElements.forEach(function(element) {
                                    if (element.parentNode) {
                                        element.style.display = 'none';
                                        element.innerHTML = '<div style="padding: 20px; text-align: center; border: 1px solid #ddd; background: #f9f9f9; color: #666;">Pubblicità bloccata - Consenso pubblicitario rifiutato</div>';
                                    }
                                });
                                
                                // Block Google Ad Manager slots
                                if (window.googletag && window.googletag.cmd) {
                                    window.googletag.cmd.push(function() {
                                        if (window.googletag.pubads) {
                                            console.log('GDPR: Clearing Google Ad Manager slots');
                                            window.googletag.pubads().clear();
                                        }
                                    });
                                }
                            }
                        }
                        
                        // Update TCF API if available
                        if (typeof window.__tcfapi === 'function') {
                            console.log('GDPR Cookie Consent: Updating TCF API with new consent');
                            
                            // Trigger TCF update with a small delay to ensure cookie is set
                            setTimeout(function() {
                                // Dispatch custom event for TCF update
                                var tcfEvent = new CustomEvent('manus-gdpr-consent-updated', {
                                    detail: {
                                        consentStatus: consentStatus,
                                        consentData: consentData
                                    }
                                });
                                document.dispatchEvent(tcfEvent);
                                
                                // Force TCF API update
                                if (window.__tcfapi.eventListeners && window.__tcfapi.eventListeners.length > 0) {
                                    window.__tcfapi.eventListeners.forEach(function(listener) {
                                        try {
                                            window.__tcfapi('getTCData', 2, listener.callback, listener.id);
                                        } catch (e) {
                                            console.error('GDPR: Error updating TCF listener:', e);
                                        }
                                    });
                                }
                            }, 100);
                        }
                    
                    // Don't reload page, just update the UI
                    console.log('GDPR Cookie Consent: Consent recorded successfully', consentData);
                    
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
        
        // Apply current consent state to modal when opening
        setTimeout(function() {
            applyCurrentConsentToModal();
        }, 50); // Small delay to ensure modal is visible
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

    // Get current consent data from cookie
    function getCurrentConsentData() {
        var consentCookie = getCookie('manus_gdpr_consent');
        var consentDataCookie = getCookie('manus_gdpr_consent_data');
        
        // Default values (all false except necessary)
        var defaultData = {
            necessary: true,
            analytics: false,
            advertising: false,
            functional: false
        };
        
        if (!consentCookie) {
            return defaultData;
        }
        
        // Handle detailed consent data cookie first
        if (consentDataCookie) {
            try {
                // Try to decode URL-encoded cookie data
                var decodedData = decodeURIComponent(consentDataCookie);
                var parsedData = JSON.parse(decodedData);
                if (parsedData && parsedData.data) {
                    return Object.assign(defaultData, parsedData.data);
                }
            } catch (e) {
                console.warn('GDPR: Error parsing consent data cookie (trying fallback):', e);
                // Try without decoding
                try {
                    var parsedData = JSON.parse(consentDataCookie);
                    if (parsedData && parsedData.data) {
                        return Object.assign(defaultData, parsedData.data);
                    }
                } catch (e2) {
                    console.warn('GDPR: Could not parse consent data cookie at all:', e2);
                }
            }
        }
        
        // Handle simple accept/reject values
        if (consentCookie === 'accepted') {
            return {
                necessary: true,
                analytics: true,
                advertising: true,
                functional: true
            };
        } else if (consentCookie === 'rejected') {
            return defaultData; // All false except necessary
        }
        
        // Try to parse consent cookie directly if it's JSON
        try {
            var decodedConsent = decodeURIComponent(consentCookie);
            var parsed = JSON.parse(decodedConsent);
            return Object.assign(defaultData, parsed);
        } catch (e) {
            // Try without decoding
            try {
                var parsed = JSON.parse(consentCookie);
                return Object.assign(defaultData, parsed);
            } catch (e2) {
                // Not JSON, return default
                return defaultData;
            }
        }
    }

    // Apply current consent state to modal checkboxes
    function applyCurrentConsentToModal() {
        var currentConsent = getCurrentConsentData();
        
        console.log('GDPR: Applying current consent state:', currentConsent);
        
        // Update each checkbox based on current consent
        $('#manus-gdpr-preferences-modal input[type="checkbox"]').each(function() {
            var $checkbox = $(this);
            var category = $checkbox.data('category');
            
            if (category && currentConsent.hasOwnProperty(category)) {
                var shouldBeChecked = currentConsent[category];
                var isCurrentlyChecked = $checkbox.prop('checked');
                
                $checkbox.prop('checked', shouldBeChecked);
                
                // Log for debugging
                console.log('GDPR: Category ' + category + ': ' + isCurrentlyChecked + ' → ' + shouldBeChecked);
            }
        });
        
        console.log('GDPR: Applied current consent state to modal successfully');
    }

    // Check if consent has already been given and show floating icon
    function checkConsentStatus() {
        console.log('GDPR Cookie Consent: Checking consent status...');
        
        // Check if banner is present and visible
        var $banner = $('#manus-gdpr-cookie-banner');
        
        // If banner is visible, don't show floating icon
        if ($banner.length > 0 && $banner.is(':visible') && $banner.css('display') !== 'none') {
            console.log('GDPR Cookie Consent: Banner is visible, hiding floating icon');
            hideFloatingIcon();
            return;
        }
        
        // Check for consent cookie
        var consentCookie = getCookie('manus_gdpr_consent');
        if (consentCookie) {
            try {
                var consentData = JSON.parse(decodeURIComponent(consentCookie));
                console.log('GDPR Cookie Consent: Found consent cookie, showing floating icon', consentData);
                showFloatingIcon();
            } catch (e) {
                console.log('GDPR Cookie Consent: Invalid consent cookie format, checking simple cookie');
                // Fallback for simple cookie format
                showFloatingIcon();
            }
        } else {
            console.log('GDPR Cookie Consent: No consent cookie found, hiding floating icon');
            hideFloatingIcon();
        }
    }

    // Initialize on page load
    $(document).ready(function() {
        // Delay to ensure all CSS is loaded
        setTimeout(checkConsentStatus, 1000);
        
        // Initialize AdSense monitoring if needed
        initAdBlockingMonitor();
    });

    // Function to monitor and block AdSense elements dynamically
    function initAdBlockingMonitor() {
        // Only run if MutationObserver is available
        if (typeof MutationObserver === 'undefined') {
            return;
        }
        
        // Check for AdSense elements every time the DOM changes
        var observer = new MutationObserver(function(mutations) {
            var shouldBlock = shouldBlockAds();
            
            if (shouldBlock) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check for AdSense elements
                            if (node.classList && (node.classList.contains('adsbygoogle') || 
                                node.tagName === 'INS' && node.getAttribute('class') && 
                                node.getAttribute('class').includes('adsbygoogle'))) {
                                blockAdElement(node);
                            }
                            
                            // Check for AdSense elements in children
                            var adsenseElements = node.querySelectorAll && node.querySelectorAll('.adsbygoogle, ins[class*="adsbygoogle"]');
                            if (adsenseElements) {
                                adsenseElements.forEach(blockAdElement);
                            }
                        }
                    });
                });
            }
        });
        
        // Start observing
        observer.observe(document.body, { 
            childList: true, 
            subtree: true 
        });
        
        // Initial check for existing ads
        setTimeout(function() {
            if (shouldBlockAds()) {
                var existingAds = document.querySelectorAll('.adsbygoogle, ins[class*="adsbygoogle"]');
                existingAds.forEach(blockAdElement);
            }
        }, 500);
    }
    
    // Function to check if ads should be blocked
    function shouldBlockAds() {
        var currentConsent = getCurrentConsentData();
        return !currentConsent.advertising;
    }
    
    // Function to block a single ad element
    function blockAdElement(element) {
        if (element && element.style) {
            element.style.display = 'none';
            element.innerHTML = '<div style="padding: 20px; text-align: center; border: 1px solid #ddd; background: #f9f9f9; color: #666; font-family: Arial, sans-serif; font-size: 14px;">📢 Pubblicità bloccata<br><small>Consenso pubblicitario necessario</small></div>';
            console.log('GDPR: Blocked AdSense element', element);
        }
    }

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


