jQuery(document).ready(function($) {
    // Make utility functions globally available
    window.getCookie = function(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) {
            return parts.pop().split(";").shift();
        }
        return null;
    };

    // Helper function to set cookie value
    window.setCookie = function(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    };

    // Local aliases for internal use
    var getCookie = window.getCookie;
    var setCookie = window.setCookie;

    function hasStoredConsent() {
        var consentCookie = getCookie('manus_gdpr_consent');
        return !!(consentCookie && consentCookie !== 'null' && consentCookie !== 'undefined');
    }

    function enforceConsentUI(attempt) {
        if (!hasStoredConsent()) {
            return;
        }

        hideOverlay();

        var $banner = $('#matteomorreale-gdpr-cookie-banner');
        if ($banner.length) {
            $banner.stop(true, true).hide();
        } else if (attempt < 20) {
            setTimeout(function() {
                enforceConsentUI(attempt + 1);
            }, 50);
            return;
        }

        showFloatingIcon();
    }
    // Show overlay when banner is present
    function showOverlay() {
        var $overlay = $('.matteomorreale-gdpr-overlay');
        if ($overlay.length) {
            $overlay.addClass('show');
        }
    }
    
    // Hide overlay
    function hideOverlay() {
        var $overlay = $('.matteomorreale-gdpr-overlay');
        if ($overlay.length) {
            $overlay.removeClass('show');
        }
    }
    
    // Initialize overlay when banner is visible
    $(document).ready(function() {
        enforceConsentUI(0);

        var $banner = $('#matteomorreale-gdpr-cookie-banner');
        if ($banner.length && $banner.is(':visible') && !hasStoredConsent()) {
            setTimeout(showOverlay, 100); // Small delay for smooth transition
        }
        
        // Initialize modal preferences with current consent state
        // This ensures the modal shows correct state even if opened on first load
        if ($('#matteomorreale-gdpr-preferences-modal').length) {
            setTimeout(function() {
                applyCurrentConsentToModal();
            }, 200); // Small delay to ensure DOM is fully ready
        }
    });

    // Handle consent actions
    $(document).on(
        'click',
        '#matteomorreale-gdpr-accept-button, #matteomorreale-gdpr-reject-button',
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
    $(document).on('click', '#matteomorreale-gdpr-manage-button', function(e) {
        e.preventDefault();
        var $modal = $('#matteomorreale-gdpr-preferences-modal');
        $modal.css('display', 'block').addClass('show');
        
        // Apply current consent state to modal when opening
        setTimeout(function() {
            applyCurrentConsentToModal();
        }, 50); // Small delay to ensure modal is visible
    });

    // Handle modal close
    $(document).on('click', '.matteomorreale-gdpr-close', function(e) {
        e.preventDefault();
        var $modal = $('#matteomorreale-gdpr-preferences-modal');
        $modal.removeClass('show');
        setTimeout(function() {
            $modal.css('display', 'none');
        }, 300); // Matches CSS transition duration
    });

    // Close modal when clicking outside
    $(document).on('click', '#matteomorreale-gdpr-preferences-modal', function(e) {
        if (e.target === this) {
            var $modal = $(this);
            $modal.removeClass('show');
            setTimeout(function() {
                $modal.css('display', 'none');
            }, 300); // Matches CSS transition duration
        }
    });

    // Handle save preferences
    $(document).on('click', '#matteomorreale-gdpr-save-preferences', function(e) {
        e.preventDefault();

        // Get current consent before changes
        var previousConsent = getCurrentConsentData();

        var consentData = {
            necessary: true // Always true
        };

        // Get preferences from checkboxes
        $('#matteomorreale-gdpr-preferences-modal input[type="checkbox"]').each(function() {
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

        // Check if advertising consent was just enabled
        if (!previousConsent.advertising && consentData.advertising) {
            console.log('GDPR: Advertising enabled in preferences, will reload after save');
            window.gdprShouldReload = true;
        }

        recordConsent(consentStatus, consentData);
        var $modal = $('#matteomorreale-gdpr-preferences-modal');
        $modal.removeClass('show');
        setTimeout(function() {
            $modal.css('display', 'none');
        }, 300); // Matches CSS transition duration
    });

    // Function to record consent
    function recordConsent(consentStatus, consentData) {
        var previousConsent = getCurrentConsentData();

        setCookie('manus_gdpr_consent', consentStatus, 365);
        setCookie('manus_gdpr_consent_data', encodeURIComponent(JSON.stringify({
            status: consentStatus,
            data: consentData,
            timestamp: Date.now()
        })), 365);

        hideOverlay();

        var $banner = $('#matteomorreale-gdpr-cookie-banner');
        if ($banner.length) {
            $banner.stop(true, true).fadeOut(function() {
                showFloatingIcon();
            });
        } else {
            showFloatingIcon();
        }

        if (window.matteomorrealeGDPRCookieBlocker) {
            if (consentStatus === 'rejected' || 
                (consentStatus === 'partial' && !consentData.advertising)) {
                console.log('GDPR: Clearing blocked cookies and content');
                window.matteomorrealeGDPRCookieBlocker.clearBlockedCookies();
                
                var adsenseElements = document.querySelectorAll('.adsbygoogle, ins.adsbygoogle');
                adsenseElements.forEach(function(element) {
                    if (element.parentNode) {
                        element.style.display = 'none';
                        element.innerHTML = '<div style="padding: 20px; text-align: center; border: 1px solid #ddd; background: #f9f9f9; color: #666;">Pubblicità bloccata - Consenso pubblicitario rifiutato</div>';
                    }
                });
                
                if (window.googletag && window.googletag.cmd) {
                    window.googletag.cmd.push(function() {
                        if (window.googletag.pubads) {
                            console.log('GDPR: Clearing Google Ad Manager slots');
                            window.googletag.pubads().clear();
                        }
                    });
                }
            } else if (consentData.advertising && !previousConsent.advertising) {
                console.log('GDPR: Advertising consent enabled, reloading page to load ads');
                window.gdprShouldReload = true;
            }
        }

        $.ajax({
            url: matteomorreale_gdpr_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'matteomorreale_gdpr_consent_action',
                matteomorreale_gdpr_consent_nonce: matteomorreale_gdpr_ajax.nonce,
                consent_status: consentStatus,
                consent_data: JSON.stringify(consentData)
            },
            success: function(response) {
                if (response.success) {
                    console.log('GDPR Cookie Consent: Consent recorded successfully', consentData);

                    if (typeof window.__tcfapi === 'function') {
                        console.log('GDPR Cookie Consent: Updating TCF API with new consent');
                        
                        setTimeout(function() {
                            var tcfEvent = new CustomEvent('matteomorreale-gdpr-consent-updated', {
                                detail: {
                                    consentStatus: consentStatus,
                                    consentData: consentData
                                }
                            });
                            document.dispatchEvent(tcfEvent);
                            
                            if (window.__tcfapi.eventListeners && window.__tcfapi.eventListeners.length > 0) {
                                window.__tcfapi.eventListeners.forEach(function(listener) {
                                    try {
                                        window.__tcfapi('getTCData', 2, listener.callback, listener.id);
                                    } catch (e) {
                                        console.error('GDPR: Error updating TCF listener:', e);
                                    }
                                });
                            }
                            
                            if (window.gdprShouldReload) {
                                window.gdprShouldReload = false;
                                
                                var notification = document.createElement('div');
                                notification.style.cssText = 'position:fixed;top:20px;right:20px;background:#28a745;color:white;padding:15px 20px;border-radius:8px;z-index:999999;font-family:sans-serif;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
                                notification.innerHTML = '✓ Consenso salvato! Ricaricamento per abilitare le pubblicità...';
                                document.body.appendChild(notification);
                                
                                setTimeout(function() {
                                    console.log('GDPR: Reloading page to load advertisements');
                                    window.location.reload();
                                }, 1500);
                            }
                        }, 100);
                    } else if (window.gdprShouldReload) {
                        window.gdprShouldReload = false;
                        
                        var notification = document.createElement('div');
                        notification.style.cssText = 'position:fixed;top:20px;right:20px;background:#28a745;color:white;padding:15px 20px;border-radius:8px;z-index:999999;font-family:sans-serif;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
                        notification.innerHTML = '✓ Consenso salvato! Ricaricamento per abilitare le pubblicità...';
                        document.body.appendChild(notification);
                        
                        setTimeout(function() {
                            console.log('GDPR: Reloading page to load advertisements (no TCF)');
                            window.location.reload();
                        }, 1500);
                    }
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
        var $floatingIcon = $('#matteomorreale-gdpr-floating-icon');
        if ($floatingIcon.length) {
            $floatingIcon.css('display', 'flex'); // Force display
            $floatingIcon.addClass('show').fadeIn(500);
        }
    }

    // Function to hide floating icon
    function hideFloatingIcon() {
        var $floatingIcon = $('#matteomorreale-gdpr-floating-icon');
        if ($floatingIcon.length) {
            $floatingIcon.removeClass('show').fadeOut(300);
        }
    }

    // Handle floating icon click
    $(document).on('click', '#matteomorreale-gdpr-floating-icon', function(e) {
        e.preventDefault();
        var $modal = $('#matteomorreale-gdpr-preferences-modal');
        $modal.css('display', 'block').addClass('show');
        
        // Apply current consent state to modal when opening
        setTimeout(function() {
            applyCurrentConsentToModal();
        }, 50); // Small delay to ensure modal is visible
    });

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
        $('#matteomorreale-gdpr-preferences-modal input[type="checkbox"]').each(function() {
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
        var $banner = $('#matteomorreale-gdpr-cookie-banner');

        if (hasStoredConsent()) {
            if ($banner.length > 0) {
                $banner.stop(true, true).hide();
            }
            hideOverlay();
            showFloatingIcon();
            return;
        }
        
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

    window.addEventListener('pageshow', function() {
        enforceConsentUI(0);
        checkConsentStatus();
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
    $(document).on('keydown', '#matteomorreale-gdpr-floating-icon', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
        }
    });

    // Add tabindex for accessibility
    $(document).ready(function() {
        $('#matteomorreale-gdpr-floating-icon').attr('tabindex', '0');
    });
});
