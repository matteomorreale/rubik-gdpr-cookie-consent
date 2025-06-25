// JavaScript per l'area di amministrazione del plugin

(function($) {
    'use strict';

    // Namespace per evitare conflitti
    window.ManusGDPRAdmin = window.ManusGDPRAdmin || {};

    $(document).ready(function() {
        
        // Remove any existing handlers first to prevent duplicates
        $(document).off('click.manus-gdpr', '.consent-toggle');
        
        // Enhanced consent details toggle with debugging e namespace
        $(document).on('click.manus-gdpr', '.consent-toggle', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var consentId = $(this).data('consent-id');
            var details = $('#consent-details-' + consentId);
            var button = $(this);
            
            console.log('Manus GDPR: Consent toggle clicked:', {
                consentId: consentId,
                detailsExists: details.length > 0,
                isVisible: details.is(':visible'),
                buttonText: button.text().trim()
            });
            
            if (details.length === 0) {
                console.error('Manus GDPR: Details element not found for consent ID:', consentId);
                return;
            }
            
            // Forza lo stato attuale per evitare conflitti
            if (details.hasClass('gdpr-showing') || details.is(':visible')) {
                details.removeClass('gdpr-showing').addClass('gdpr-hiding');
                details.slideUp(200, function() {
                    $(this).removeClass('gdpr-hiding');
                });
                button.text(button.data('show-text') || 'Mostra dettagli');
                button.attr('aria-expanded', 'false');
                console.log('Manus GDPR: Details hidden for consent ID:', consentId);
            } else {
                details.removeClass('gdpr-hiding').addClass('gdpr-showing');
                details.slideDown(200, function() {
                    $(this).removeClass('gdpr-showing');
                });
                button.text(button.data('hide-text') || 'Nascondi dettagli');
                button.attr('aria-expanded', 'true');
                console.log('Manus GDPR: Details shown for consent ID:', consentId);
            }
        });
        
        // Inizializzazione completata
        console.log('Manus GDPR Admin: JavaScript initialized successfully');
        
        // Debug: verifica presenza elementi
        setTimeout(function() {
            var toggles = $('.consent-toggle');
            console.log('Manus GDPR: Found ' + toggles.length + ' consent toggles on page');
            if (toggles.length > 0) {
                console.log('Manus GDPR: Sample toggle data:', {
                    consentId: toggles.first().data('consent-id'),
                    showText: toggles.first().data('show-text'),
                    hideText: toggles.first().data('hide-text')
                });
            }
        }, 500);
        
        // Auto-submit filter form on change
        $('#filter-status').on('change', function() {
            $(this).closest('form').submit();
        });
        
        // Clear search functionality
        $('#consent-search-input').on('input', function() {
            var searchTerm = $(this).val();
            var clearBtn = $('#search-clear');
            
            if (searchTerm.length > 0) {
                if (clearBtn.length === 0) {
                    $(this).after('<button type="button" id="search-clear" class="button" style="margin-left: 5px;">×</button>');
                }
            } else {
                clearBtn.remove();
            }
        });
        
        // Handle clear search button
        $(document).on('click', '#search-clear', function() {
            $('#consent-search-input').val('').focus();
            $(this).remove();
        });
        
        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 75) {
                e.preventDefault();
                $('#consent-search-input').focus();
            }
            
            // Escape to clear search
            if (e.keyCode === 27 && $('#consent-search-input').is(':focus')) {
                $('#consent-search-input').val('');
                $('#search-clear').remove();
            }
        });
        
        // Bulk actions (preparazione per future implementazioni)
        $('.consent-log-bulk-action').on('change', function() {
            var action = $(this).val();
            var $form = $(this).closest('form');
            
            if (action === 'export') {
                // Implementazione futura per esportazione
                console.log('Export selected consents');
            } else if (action === 'delete') {
                // Implementazione futura per eliminazione
                if (confirm('Sei sicuro di voler eliminare i consensi selezionati?')) {
                    console.log('Delete selected consents');
                }
            }
        });
        
        // Statistics cards animation
        $('.consent-stat-card').hover(
            function() {
                $(this).css('transform', 'translateY(-2px)');
                $(this).css('transition', 'transform 0.2s ease');
            },
            function() {
                $(this).css('transform', 'translateY(0)');
            }
        );
        
        // Auto-refresh functionality (optional)
        var autoRefresh = localStorage.getItem('manus-gdpr-auto-refresh');
        if (autoRefresh === 'enabled') {
            setInterval(function() {
                // Solo se l'utente non ha interazioni attive
                if (!$(':focus').length && !$('.consent-details:visible').length) {
                    location.reload();
                }
            }, 300000); // 5 minuti
        }
        
        // Add tooltips to status badges
        $('.consent-badge').each(function() {
            var $badge = $(this);
            var status = $badge.text().toLowerCase();
            var tooltipText = '';
            
            switch(status) {
                case 'accettato':
                    tooltipText = 'L\'utente ha accettato tutti i cookie';
                    break;
                case 'rifiutato':
                    tooltipText = 'L\'utente ha rifiutato tutti i cookie non essenziali';
                    break;
                case 'parziale':
                    tooltipText = 'L\'utente ha accettato solo alcune categorie di cookie';
                    break;
            }
            
            if (tooltipText) {
                $badge.attr('title', tooltipText);
            }
        });
        
        // Cookie Scanner Enhanced Functionality
        if (typeof ManusGDPRCookieScanner === 'undefined') {
            window.ManusGDPRCookieScanner = {
                
                // Advanced cookie detection using various methods
                detectCookies: function() {
                    const cookies = [];
                    
                    // Method 1: Document.cookie
                    this.addDocumentCookies(cookies);
                    
                    // Method 2: Storage APIs
                    this.addStorageData(cookies);
                    
                    // Method 3: Script tag analysis
                    this.addScriptBasedCookies(cookies);
                    
                    return cookies;
                },
                
                addDocumentCookies: function(cookies) {
                    if (!document.cookie) return;
                    
                    document.cookie.split(';').forEach(cookie => {
                        const [name, value] = cookie.trim().split('=');
                        if (name && value) {
                            cookies.push({
                                name: name.trim(),
                                value: value.trim(),
                                domain: window.location.hostname,
                                path: '/',
                                source: 'document.cookie',
                                secure: window.location.protocol === 'https:',
                                httpOnly: false,
                                sameSite: 'Lax'
                            });
                        }
                    });
                },
                
                addStorageData: function(cookies) {
                    // LocalStorage
                    try {
                        for (let i = 0; i < localStorage.length; i++) {
                            const key = localStorage.key(i);
                            const value = localStorage.getItem(key);
                            cookies.push({
                                name: key,
                                value: value.substring(0, 100), // Limit value length
                                domain: window.location.hostname,
                                path: '/',
                                source: 'localStorage',
                                type: 'localStorage',
                                persistent: true
                            });
                        }
                    } catch (e) {
                        console.warn('Cannot access localStorage:', e);
                    }
                    
                    // SessionStorage
                    try {
                        for (let i = 0; i < sessionStorage.length; i++) {
                            const key = sessionStorage.key(i);
                            const value = sessionStorage.getItem(key);
                            cookies.push({
                                name: key,
                                value: value.substring(0, 100), // Limit value length
                                domain: window.location.hostname,
                                path: '/',
                                source: 'sessionStorage',
                                type: 'sessionStorage',
                                persistent: false
                            });
                        }
                    } catch (e) {
                        console.warn('Cannot access sessionStorage:', e);
                    }
                },
                
                addScriptBasedCookies: function(cookies) {
                    // Analyze script tags for common tracking scripts
                    const scripts = document.querySelectorAll('script[src]');
                    
                    scripts.forEach(script => {
                        const src = script.src.toLowerCase();
                        
                        // Google Analytics
                        if (src.includes('googletagmanager') || src.includes('google-analytics')) {
                            this.addPredictedCookies(cookies, 'analytics', [
                                '_ga', '_gid', '_gat', '__utma', '__utmb', '__utmc', '__utmt', '__utmz'
                            ]);
                        }
                        
                        // Google AdSense
                        if (src.includes('googlesyndication') || src.includes('googleadservices')) {
                            this.addPredictedCookies(cookies, 'advertising', [
                                'IDE', 'DSID', '__gads', '__gpi', 'NID'
                            ]);
                        }
                        
                        // Facebook Pixel
                        if (src.includes('facebook.net') || src.includes('fbcdn.net')) {
                            this.addPredictedCookies(cookies, 'advertising', [
                                '_fbp', 'fr', 'datr', 'sb', 'wd'
                            ]);
                        }
                        
                        // Other common tracking scripts
                        if (src.includes('hotjar') || src.includes('crazyegg') || src.includes('mixpanel')) {
                            this.addPredictedCookies(cookies, 'analytics', [
                                '_hjid', '_hjSession', '_ce.s', 'mp_', '__mp_opt_in_out'
                            ]);
                        }
                    });
                },
                
                addPredictedCookies: function(cookies, category, cookieNames) {
                    cookieNames.forEach(name => {
                        // Check if cookie already exists in our list
                        const exists = cookies.some(cookie => cookie.name === name);
                        if (!exists) {
                            cookies.push({
                                name: name,
                                value: 'predicted_value_' + Math.random().toString(36).substr(2, 9),
                                domain: window.location.hostname,
                                path: '/',
                                source: 'script_analysis',
                                predicted: true,
                                category: category,
                                expires: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString()
                            });
                        }
                    });
                },
                
                // Enhanced scanning with multiple page loads and interactions
                performDeepScan: function(url, options = {}) {
                    return new Promise((resolve, reject) => {
                        const iframe = document.createElement('iframe');
                        iframe.style.cssText = 'position: absolute; top: -9999px; left: -9999px; width: 1px; height: 1px;';
                        iframe.setAttribute('sandbox', 'allow-same-origin allow-scripts allow-forms');
                        
                        let scanPhase = 0;
                        const phases = [
                            { name: 'Initial Load', action: () => this.loadPage(iframe, url) },
                            { name: 'User Interaction', action: () => this.simulateUserInteraction(iframe) },
                            { name: 'Delayed Scripts', action: () => this.waitForDelayedScripts(iframe) },
                            { name: 'Final Analysis', action: () => this.extractFinalCookies(iframe) }
                        ];
                        
                        const nextPhase = () => {
                            if (scanPhase >= phases.length) {
                                const finalCookies = this.detectCookies();
                                document.body.removeChild(iframe);
                                resolve(finalCookies);
                                return;
                            }
                            
                            const phase = phases[scanPhase++];
                            this.updateScanProgress(phase.name, (scanPhase / phases.length) * 100);
                            
                            phase.action().then(() => {
                                setTimeout(nextPhase, 1000); // Wait between phases
                            }).catch(reject);
                        };
                        
                        document.body.appendChild(iframe);
                        nextPhase();
                    });
                },
                
                loadPage: function(iframe, url) {
                    return new Promise((resolve) => {
                        iframe.onload = resolve;
                        iframe.src = url;
                    });
                },
                
                simulateUserInteraction: function(iframe) {
                    return new Promise((resolve) => {
                        try {
                            const doc = iframe.contentDocument || iframe.contentWindow.document;
                            
                            // Simulate clicks on common interactive elements
                            const interactiveElements = doc.querySelectorAll('button, a, input[type="submit"], .btn, [role="button"]');
                            
                            if (interactiveElements.length > 0) {
                                // Click first few elements
                                for (let i = 0; i < Math.min(3, interactiveElements.length); i++) {
                                    const element = interactiveElements[i];
                                    if (element.click && !element.href) { // Avoid navigation
                                        try {
                                            element.click();
                                        } catch (e) {
                                            console.warn('Could not click element:', e);
                                        }
                                    }
                                }
                            }
                            
                            // Simulate scroll
                            try {
                                iframe.contentWindow.scrollTo(0, 100);
                                setTimeout(() => iframe.contentWindow.scrollTo(0, 0), 500);
                            } catch (e) {
                                console.warn('Could not simulate scroll:', e);
                            }
                            
                        } catch (e) {
                            console.warn('Could not simulate user interaction:', e);
                        }
                        
                        resolve();
                    });
                },
                
                waitForDelayedScripts: function(iframe) {
                    return new Promise((resolve) => {
                        // Wait for scripts that might load after user interaction
                        setTimeout(resolve, 2000);
                    });
                },
                
                extractFinalCookies: function(iframe) {
                    return new Promise((resolve) => {
                        // Final cookie extraction from iframe context
                        try {
                            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            // Additional cookie detection logic here if needed
                        } catch (e) {
                            console.warn('Could not access iframe document:', e);
                        }
                        resolve();
                    });
                },
                
                updateScanProgress: function(phaseName, percentage) {
                    const progressBar = document.querySelector('.progress-fill');
                    const progressText = document.querySelector('.progress-text');
                    
                    if (progressBar) {
                        progressBar.style.width = percentage + '%';
                    }
                    
                    if (progressText) {
                        progressText.textContent = `${phaseName} - ${Math.round(percentage)}%`;
                    }
                },
                
                // Cookie categorization with machine learning-like patterns
                categorizeCookie: function(cookieName, cookieData = {}) {
                    const name = cookieName.toLowerCase();
                    const value = (cookieData.value || '').toLowerCase();
                    const domain = (cookieData.domain || '').toLowerCase();
                    
                    // Advanced pattern matching
                    const patterns = {
                        necessary: [
                            /^(php)?sess/i, /csrf/i, /xsrf/i, /nonce/i, /wp[-_]/i, /wordpress/i,
                            /^(auth|login|user|admin)/i, /security/i, /token/i
                        ],
                        analytics: [
                            /^_ga/i, /^_gi/i, /^__utm/i, /^_dc_gtm/i, /gtm/i, /analytics/i,
                            /^_hj/i, /hotjar/i, /^mp_/i, /mixpanel/i, /^_ce\./i, /crazyegg/i
                        ],
                        advertising: [
                            /^(ide|dsid|nid)/i, /doubleclick/i, /googlesyndication/i, /googleads/i,
                            /^_fb/i, /facebook/i, /^fr$/i, /^datr/i, /ads/i, /marketing/i
                        ],
                        functional: [
                            /lang/i, /language/i, /currency/i, /theme/i, /preference/i,
                            /settings/i, /config/i, /locale/i
                        ]
                    };
                    
                    // Check each category
                    for (const [category, categoryPatterns] of Object.entries(patterns)) {
                        for (const pattern of categoryPatterns) {
                            if (pattern.test(name) || pattern.test(value) || pattern.test(domain)) {
                                return category;
                            }
                        }
                    }
                    
                    // Domain-based categorization
                    if (domain.includes('google') || domain.includes('gstatic')) {
                        if (name.includes('ad') || name.includes('doubleclick')) {
                            return 'advertising';
                        }
                        return 'analytics';
                    }
                    
                    if (domain.includes('facebook') || domain.includes('fbcdn')) {
                        return 'advertising';
                    }
                    
                    // Default to functional
                    return 'functional';
                },
                
                // Generate comprehensive scan report
                generateScanReport: function(cookies) {
                    const report = {
                        timestamp: new Date().toISOString(),
                        url: window.location.href,
                        totalCookies: cookies.length,
                        categories: {},
                        sources: {},
                        privacy: {
                            secure: 0,
                            httpOnly: 0,
                            sameSite: 0,
                            persistent: 0
                        },
                        recommendations: []
                    };
                    
                    cookies.forEach(cookie => {
                        const category = this.categorizeCookie(cookie.name, cookie);
                        report.categories[category] = (report.categories[category] || 0) + 1;
                        
                        const source = cookie.source || 'unknown';
                        report.sources[source] = (report.sources[source] || 0) + 1;
                        
                        if (cookie.secure) report.privacy.secure++;
                        if (cookie.httpOnly) report.privacy.httpOnly++;
                        if (cookie.sameSite) report.privacy.sameSite++;
                        if (cookie.expires || cookie.persistent) report.privacy.persistent++;
                    });
                    
                    // Generate recommendations
                    if (report.privacy.secure < cookies.length * 0.8) {
                        report.recommendations.push('Considera l\'uso di cookie sicuri (Secure flag) per migliorare la sicurezza.');
                    }
                    
                    if (report.categories.advertising > 0) {
                        report.recommendations.push('Sono stati rilevati cookie pubblicitari. Assicurati di ottenere il consenso dell\'utente.');
                    }
                    
                    if (report.categories.analytics > 5) {
                        report.recommendations.push('Alto numero di cookie analitici rilevati. Considera di consolidare i servizi di analisi.');
                    }
                    
                    return report;
                }
            };
        }
        
        // TCF API test function for admin (tests frontend via iframe)
        window.ManusGDPRAdmin.testTCFAPI = function() {
            console.group('Manus GDPR Admin: TCF API Test');
            console.log('Testing TCF API on frontend...');
            
            // Create hidden iframe to test frontend
            var iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.style.width = '1px';
            iframe.style.height = '1px';
            iframe.style.position = 'absolute';
            iframe.style.top = '-9999px';
            iframe.style.left = '-9999px';
            
            // Set iframe source to site homepage
            var siteUrl = window.location.protocol + '//' + window.location.host;
            iframe.src = siteUrl;
            
            var testCompleted = false;
            var testTimeout;
            
            iframe.onload = function() {
                try {
                    // Wait a bit for the page to fully load
                    setTimeout(function() {
                        try {
                            var iframeWindow = iframe.contentWindow;
                            
                            if (typeof iframeWindow.__tcfapi === 'function') {
                                console.log('✅ TCF API found on frontend');
                                
                                // Test ping
                                iframeWindow.__tcfapi('ping', 2, function(pingReturn, success) {
                                    console.log('📡 Ping result:', success ? '✅ Success' : '❌ Failed');
                                    if (pingReturn) {
                                        console.log('   CMP ID:', pingReturn.cmpId);
                                        console.log('   CMP Version:', pingReturn.cmpVersion);
                                        console.log('   TCF Policy Version:', pingReturn.tcfPolicyVersion);
                                        console.log('   GDPR Applies:', pingReturn.gdprApplies);
                                        console.log('   CMP Status:', pingReturn.cmpStatus);
                                    }
                                });
                                
                                // Test getTCData
                                iframeWindow.__tcfapi('getTCData', 2, function(tcData, success) {
                                    console.log('📊 TC Data result:', success ? '✅ Success' : '❌ Failed');
                                    if (tcData) {
                                        console.log('   TC String:', tcData.tcString ? '✅ Present (' + tcData.tcString.length + ' chars)' : '❌ Missing');
                                        console.log('   GDPR Applies:', tcData.gdprApplies);
                                        console.log('   Event Status:', tcData.eventStatus);
                                        console.log('   CMP Status:', tcData.cmpStatus);
                                        console.log('   Purpose Consents:', tcData.purpose.consents);
                                        console.log('   Vendor Consents:', Object.keys(tcData.vendor.consents || {}).length);
                                    }
                                });
                                
                                // Test addEventListener
                                iframeWindow.__tcfapi('addEventListener', 2, function(tcData, success) {
                                    console.log('👂 Event Listener result:', success ? '✅ Success' : '❌ Failed');
                                    if (tcData && tcData.listenerId !== undefined) {
                                        console.log('   Event Listener ID:', tcData.listenerId);
                                        console.log('   Event Status:', tcData.eventStatus);
                                    }
                                });
                                
                                console.log('🎉 TCF API test completed successfully!');
                                console.log('💡 You can also test manually by running: testTCFAPI() in the browser console on any frontend page');
                                
                            } else {
                                console.error('❌ TCF API not found on frontend');
                                console.error('🔧 Troubleshooting steps:');
                                console.error('   1. Check if IAB TCF v2.2 is enabled in Manus GDPR settings');
                                console.error('   2. Verify the frontend page is loading correctly');
                                console.error('   3. Check browser console for JavaScript errors');
                                console.error('   4. Try testing directly on a frontend page');
                            }
                            
                        } catch (crossOriginError) {
                            console.warn('⚠️  Cross-origin restriction detected');
                            console.log('🌐 Cannot access iframe due to browser security policy');
                            console.log('✅ This is normal behavior for cross-origin iframes');
                            console.log('💡 To test TCF API manually:');
                            console.log('   1. Open any frontend page of your site');
                            console.log('   2. Open browser developer console');
                            console.log('   3. Run: testTCFAPI()');
                            console.log('   4. Or check: typeof window.__tcfapi');
                        }
                        
                        testCompleted = true;
                        clearTimeout(testTimeout);
                        console.groupEnd();
                        
                        // Clean up
                        setTimeout(function() {
                            if (iframe && iframe.parentNode) {
                                iframe.parentNode.removeChild(iframe);
                            }
                        }, 1000);
                        
                    }, 2000);
                    
                } catch (error) {
                    console.error('❌ Error testing TCF API:', error);
                    console.groupEnd();
                }
            };
            
            iframe.onerror = function() {
                console.error('❌ Failed to load frontend page for testing');
                console.error('🔧 Check if the site is accessible and loading correctly');
                console.groupEnd();
            };
            
            // Timeout fallback
            testTimeout = setTimeout(function() {
                if (!testCompleted) {
                    console.warn('⏱️  TCF API test timed out');
                    console.log('💡 Try testing manually on a frontend page:');
                    console.log('   1. Visit any page of your site (not admin)');
                    console.log('   2. Open developer console');
                    console.log('   3. Run: testTCFAPI()');
                    console.groupEnd();
                }
            }, 10000);
            
            // Add iframe to page
            document.body.appendChild(iframe);
            
            return true;
        };
        
        // TCF v2.2 info message for admin
        console.log('ℹ️  Manus GDPR Admin loaded');
        console.log('🔧 To test TCF API, run: ManusGDPRAdmin.testTCFAPI()');
        console.log('💡 Or test directly on frontend with: testTCFAPI()');
        
        // Add handler for TCF test button
        $(document).on('click', '#test-tcf-api', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var originalText = button.text();
            
            // Visual feedback
            button.prop('disabled', true).text('Testing...');
            
            console.log('🚀 Starting TCF API test from admin...');
            
            // Run the test
            ManusGDPRAdmin.testTCFAPI();
            
            // Reset button after test
            setTimeout(function() {
                button.prop('disabled', false).text(originalText);
            }, 2000);
        });
        
    });

})(jQuery);
