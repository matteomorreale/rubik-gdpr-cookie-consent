<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for enqueueing the public-facing stylesheet and JavaScript.
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Manus AI
 */
class Manus_GDPR_Frontend {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Add cookie blocking functionality
        add_action( 'wp_head', array( $this, 'add_cookie_blocking_script' ), 1 );
        add_action( 'wp_head', array( $this, 'add_tcf_v2_script' ), 2 );
        add_action( 'wp_head', array( $this, 'add_adsense_blocking_script' ), 3 );
        add_action( 'wp_footer', array( $this, 'add_cookie_scanner_integration' ), 999 );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../public/css/manus-gdpr-public.css', array(), MANUS_GDPR_VERSION, 'all' );

        // Add custom CSS if set
        $this->add_custom_css();

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../public/js/manus-gdpr-public.js', array( 'jquery' ), MANUS_GDPR_VERSION, false );

        // Get options for JavaScript
        $options = get_option( 'manus_gdpr_settings', array() );

        // Localize script for AJAX
        wp_localize_script( $this->plugin_name, 'manus_gdpr_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'manus_gdpr_consent_action' ),
            'settings' => array(
                'theme_mode' => isset( $options['theme_mode'] ) ? $options['theme_mode'] : 'light',
                'layout_mode' => isset( $options['layout_mode'] ) ? $options['layout_mode'] : 'card',
                'show_overlay' => isset( $options['show_banner_overlay'] ) ? $options['show_banner_overlay'] : true
            )
        ) );

    }

    /**
     * Display the cookie consent banner.
     *
     * @since    1.0.0
     */
    public function display_cookie_banner() {
        $options = get_option( 'manus_gdpr_settings' );
        $banner_message = isset( $options['banner_message'] ) ? $options['banner_message'] : __( 'Utilizziamo i cookie per migliorare la tua esperienza di navigazione. Cliccando su "Accetta", acconsenti all\"uso di tutti i cookie.', 'manus-gdpr' );
        $banner_position = isset( $options['banner_position'] ) ? $options['banner_position'] : 'bottom';
        $privacy_page_id = isset( $options['privacy_page_id'] ) ? $options['privacy_page_id'] : '';
        $privacy_page_link = '';

        if ( ! empty( $privacy_page_id ) ) {
            $privacy_page_link = get_permalink( $privacy_page_id );
        } else {
            // Create a default privacy page if none is selected
            $privacy_page_title = __( 'Informativa sulla Privacy', 'manus-gdpr' );
            $privacy_page_content = __( 'Questa è la nostra informativa sulla privacy. Qui puoi trovare informazioni su come raccogliamo, utilizziamo e proteggiamo i tuoi dati personali e le tue preferenze sui cookie.', 'manus-gdpr' );
            $privacy_page_args = array(
                'post_title'    => $privacy_page_title,
                'post_content'  => $privacy_page_content,
                'post_status'   => 'publish',
                'post_type'     => 'page',
            );
            // Use WP_Query instead of deprecated get_page_by_title
            $query = new WP_Query( array(
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'title'          => $privacy_page_title,
                'posts_per_page' => 1,
                'no_found_rows'  => true
            ) );
            
            $existing_privacy_page = $query->have_posts() ? $query->posts[0] : null;
            wp_reset_postdata();
            
            if ( ! $existing_privacy_page ) {
                $privacy_page_id = wp_insert_post( $privacy_page_args );
                update_option( 'manus_gdpr_settings', array_merge( $options, array( 'privacy_page_id' => $privacy_page_id ) ) );
                $privacy_page_link = get_permalink( $privacy_page_id );
            } else {
                $privacy_page_link = get_permalink( $existing_privacy_page->ID );
            }
        }

        // Check if consent has been given
        if ( ! isset( $_COOKIE['manus_gdpr_consent'] ) ) {
            // Show full banner if no consent
            include_once MANUS_GDPR_PATH . 'public/partials/manus-gdpr-public-display.php';
        } else {
            // Show only floating icon and preferences modal if consent already given
            include_once MANUS_GDPR_PATH . 'public/partials/manus-gdpr-floating-icon.php';
        }
    }

    /**
     * Block scripts based on user consent.
     *
     * @since    1.0.0
     */
    public function block_scripts() {
        $options = get_option( 'manus_gdpr_settings' );
        $block_scripts_by_default = isset( $options['block_scripts_by_default'] ) ? $options['block_scripts_by_default'] : false;
        $scripts_to_block = isset( $options['scripts_to_block'] ) ? explode( "\n", $options['scripts_to_block'] ) : array();

        if ( $block_scripts_by_default && ! isset( $_COOKIE['manus_gdpr_consent'] ) ) {
            foreach ( $scripts_to_block as $script ) {
                // This is a simplified example. Real blocking requires more sophisticated methods.
                // For example, using output buffering and replacing script tags.
                echo '<!-- Script blocked by GDPR Cookie Consent: ' . esc_html( $script ) . ' -->';
            }
        }
    }

    /**
     * Handle consent actions via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_consent_action() {
        if ( ! isset( $_POST['manus_gdpr_consent_nonce'] ) || ! wp_verify_nonce( $_POST['manus_gdpr_consent_nonce'], 'manus_gdpr_consent_action' ) ) {
            wp_send_json_error( 'Nonce verification failed.' );
        }

        $consent_status = sanitize_text_field( $_POST['consent_status'] );
        $consent_data = isset( $_POST['consent_data'] ) ? json_decode( stripslashes( $_POST['consent_data'] ), true ) : array();

        // Record consent in the database
        $user_id = get_current_user_id();
        $ip_address = $_SERVER['REMOTE_ADDR'];
        Manus_GDPR_Database::record_consent( $user_id, $ip_address, $consent_status, $consent_data );

        // Set cookie for consent status
        setcookie( 'manus_gdpr_consent', $consent_status, time() + (365 * 24 * 60 * 60), '/' );
        
        // Set cookie with detailed consent data for floating icon detection
        $consent_cookie_data = array(
            'status' => $consent_status,
            'data' => $consent_data,
            'timestamp' => time()
        );
        setcookie( 'manus_gdpr_consent_data', json_encode( $consent_cookie_data ), time() + (365 * 24 * 60 * 60), '/' );

        wp_send_json_success( 'Consent recorded successfully.' );
    }

    /**
     * Add cookie blocking script to head
     */
    public function add_cookie_blocking_script() {
        $blocked_cookies = Manus_GDPR_Cookie_Scanner::get_blocked_cookies();
        
        if ( empty( $blocked_cookies ) ) {
            return;
        }

        ?>
        <script type="text/javascript">
        (function() {
            'use strict';
            
            // Blocked cookies configuration
            const blockedCookies = <?php echo json_encode( $blocked_cookies ); ?>;
            const blockedPatterns = blockedCookies.map(cookie => ({
                name: cookie.name,
                domain: cookie.domain,
                category: cookie.category,
                pattern: new RegExp(cookie.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i')
            }));

            // Override document.cookie setter
            const originalCookie = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') || 
                                   Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');
            
            if (originalCookie && originalCookie.set) {
                Object.defineProperty(document, 'cookie', {
                    get: function() {
                        return originalCookie.get.call(this);
                    },
                    set: function(value) {
                        const cookieName = value.split('=')[0].trim();
                        
                        // Check if cookie should be blocked
                        const shouldBlock = blockedPatterns.some(pattern => {
                            return pattern.pattern.test(cookieName) || 
                                   (pattern.domain && window.location.hostname.includes(pattern.domain));
                        });
                        
                        if (shouldBlock) {
                            console.log('Blocked cookie:', cookieName);
                            return; // Don't set the cookie
                        }
                        
                        // Allow the cookie
                        return originalCookie.set.call(this, value);
                    },
                    configurable: true
                });
            }

            // Block localStorage for certain patterns
            const originalLocalStorageSetItem = localStorage.setItem;
            localStorage.setItem = function(key, value) {
                const shouldBlock = blockedPatterns.some(pattern => 
                    pattern.pattern.test(key) && pattern.category !== 'necessary'
                );
                
                if (shouldBlock) {
                    console.log('Blocked localStorage:', key);
                    return;
                }
                
                return originalLocalStorageSetItem.call(this, key, value);
            };

            // Block sessionStorage for certain patterns
            const originalSessionStorageSetItem = sessionStorage.setItem;
            sessionStorage.setItem = function(key, value) {
                const shouldBlock = blockedPatterns.some(pattern => 
                    pattern.pattern.test(key) && pattern.category !== 'necessary'
                );
                
                if (shouldBlock) {
                    console.log('Blocked sessionStorage:', key);
                    return;
                }
                
                return originalSessionStorageSetItem.call(this, key, value);
            };

            // Script blocking functionality
            const originalCreateElement = document.createElement;
            document.createElement = function(tagName) {
                const element = originalCreateElement.call(this, tagName);
                
                if (tagName.toLowerCase() === 'script') {
                    const originalSetAttribute = element.setAttribute;
                    element.setAttribute = function(name, value) {
                        if (name.toLowerCase() === 'src' && value) {
                            const shouldBlockScript = blockedPatterns.some(pattern => {
                                return pattern.category === 'advertising' && 
                                       (value.includes('googletagmanager') || 
                                        value.includes('google-analytics') ||
                                        value.includes('googlesyndication') ||
                                        value.includes('googleadservices') ||
                                        value.includes('doubleclick') ||
                                        value.includes('facebook.net') ||
                                        value.includes('fbcdn.net'));
                            });
                            
                            // Also check current consent status
                            const currentConsent = getCookie('manus_gdpr_consent');
                            const currentConsentData = getCookie('manus_gdpr_consent_data');
                            let shouldBlock = shouldBlockScript;
                            
                            if (!shouldBlock && (value.includes('googlesyndication') || value.includes('googleadservices'))) {
                                // Special handling for AdSense scripts
                                try {
                                    if (currentConsentData) {
                                        const consentData = JSON.parse(currentConsentData);
                                        shouldBlock = !consentData.data.advertising;
                                    } else if (currentConsent) {
                                        shouldBlock = currentConsent === 'rejected' || 
                                                    (currentConsent !== 'accepted' && currentConsent !== 'partial');
                                    } else {
                                        shouldBlock = true; // Block by default if no consent
                                    }
                                } catch (e) {
                                    shouldBlock = true; // Block by default on error
                                }
                            }
                            
                            if (shouldBlock) {
                                console.log('Blocked script (GDPR):', value);
                                return; // Don't set the src attribute
                            }
                        }
                        
                        return originalSetAttribute.call(this, name, value);
                    };
                }
                
                return element;
            };

            // Expose utility functions for the consent banner
            window.ManusGDPRCookieBlocker = {
                updateBlockedCookies: function(newBlockedCookies) {
                    // Update blocked cookies dynamically
                    blockedCookies.length = 0;
                    blockedCookies.push(...newBlockedCookies);
                    
                    // Update patterns
                    blockedPatterns.length = 0;
                    blockedPatterns.push(...newBlockedCookies.map(cookie => ({
                        name: cookie.name,
                        domain: cookie.domain,
                        category: cookie.category,
                        pattern: new RegExp(cookie.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'i')
                    })));
                },
                
                clearBlockedCookies: function() {
                    // Clear cookies that match blocked patterns
                    blockedPatterns.forEach(pattern => {
                        if (pattern.category !== 'necessary') {
                            // Clear from document.cookie
                            const cookies = document.cookie.split(';');
                            cookies.forEach(cookie => {
                                const cookieName = cookie.split('=')[0].trim();
                                if (pattern.pattern.test(cookieName)) {
                                    document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                                    document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + window.location.hostname + ';';
                                    document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.' + window.location.hostname + ';';
                                }
                            });
                            
                            // Clear from localStorage
                            try {
                                for (let i = localStorage.length - 1; i >= 0; i--) {
                                    const key = localStorage.key(i);
                                    if (key && pattern.pattern.test(key)) {
                                        localStorage.removeItem(key);
                                    }
                                }
                            } catch (e) {
                                console.warn('Could not clear localStorage:', e);
                            }
                            
                            // Clear from sessionStorage
                            try {
                                for (let i = sessionStorage.length - 1; i >= 0; i--) {
                                    const key = sessionStorage.key(i);
                                    if (key && pattern.pattern.test(key)) {
                                        sessionStorage.removeItem(key);
                                    }
                                }
                            } catch (e) {
                                console.warn('Could not clear sessionStorage:', e);
                            }
                        }
                    });
                },
                
                getBlockedCookies: function() {
                    return blockedCookies;
                },
                
                isBlocked: function(cookieName) {
                    return blockedPatterns.some(pattern => pattern.pattern.test(cookieName));
                }
            };

        })();
        </script>
        <?php
    }

    /**
     * Add cookie scanner integration to footer
     */
    public function add_cookie_scanner_integration() {
        // Only add on admin pages or when testing
        if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <script type="text/javascript">
        // Cookie Scanner Integration for Testing
        if (typeof ManusGDPRCookieScanner !== 'undefined') {
            // Auto-detect and report cookies for testing purposes
            window.addEventListener('load', function() {
                setTimeout(function() {
                    if (window.location.search.includes('manus_gdpr_test_scan=1')) {
                        const cookies = ManusGDPRCookieScanner.detectCookies();
                        console.group('GDPR Cookie Consent Cookie Scanner - Auto Detection');
                        console.log('Detected cookies:', cookies);
                        console.log('Scan report:', ManusGDPRCookieScanner.generateScanReport(cookies));
                        console.groupEnd();
                    }
                }, 2000);
            });
        }
        </script>
        <?php
    }

    /**
     * Add IAB TCF v2.2 support script
     *
     * @since    1.0.0
     */
    public function add_tcf_v2_script() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $enable_tcf = isset( $options['enable_tcf_v2'] ) ? $options['enable_tcf_v2'] : true;
        
        if ( ! $enable_tcf ) {
            return;
        }

        ?>
        <script type="text/javascript">
        (function() {
            'use strict';
            
            // IAB TCF v2.2 Implementation
            window.__tcfapi = window.__tcfapi || function() {
                var args = Array.prototype.slice.call(arguments);
                
                if (!window.__tcfapi.gdprApplies) {
                    // Check if GDPR applies (EU users)
                    window.__tcfapi.gdprApplies = true; // Default to true for compliance
                }
                
                var command = args[0];
                var version = args[1];
                var callback = args[2];
                var parameter = args[3];
                
                // TCF API command handlers
                switch (command) {
                    case 'getTCData':
                        handleGetTCData(version, callback, parameter);
                        break;
                        
                    case 'ping':
                        handlePing(version, callback);
                        break;
                        
                    case 'addEventListener':
                        handleAddEventListener(version, callback, parameter);
                        break;
                        
                    case 'removeEventListener':
                        handleRemoveEventListener(version, callback, parameter);
                        break;
                        
                    case 'getInAppTCData':
                        handleGetInAppTCData(version, callback, parameter);
                        break;
                        
                    case 'getVendorList':
                        handleGetVendorList(version, callback, parameter);
                        break;
                        
                    default:
                        if (typeof callback === 'function') {
                            callback(null, false);
                        }
                        break;
                }
            };
            
            // Store event listeners
            window.__tcfapi.eventListeners = window.__tcfapi.eventListeners || [];
            window.__tcfapi.tcData = window.__tcfapi.tcData || null;
            window.__tcfapi.gdprApplies = true;
            window.__tcfapi.cmpId = 1; // Your CMP ID (register with IAB)
            window.__tcfapi.cmpVersion = 1;
            window.__tcfapi.tcfPolicyVersion = 4;
            window.__tcfapi.cmpStatus = 'loading';
            window.__tcfapi.displayStatus = 'hidden';
            
            // Generate or retrieve TC String from cookie consent
            function generateTCString() {
                var consent = getCookie('manus_gdpr_consent');
                var consentDataCookie = getCookie('manus_gdpr_consent_data');
                var consentData = {};
                
                try {
                    // First try to get detailed consent data with proper URL decoding
                    if (consentDataCookie) {
                        try {
                            // Decode URL-encoded cookie data
                            var decodedData = decodeURIComponent(consentDataCookie);
                            var parsedConsentData = JSON.parse(decodedData);
                            if (parsedConsentData && parsedConsentData.data) {
                                consentData = parsedConsentData.data;
                            }
                        } catch (e) {
                            console.warn('GDPR TCF: Error parsing consent data cookie (trying fallback):', e);
                            // Try without decoding
                            try {
                                var parsedConsentData = JSON.parse(consentDataCookie);
                                if (parsedConsentData && parsedConsentData.data) {
                                    consentData = parsedConsentData.data;
                                }
                            } catch (e2) {
                                console.warn('GDPR TCF: Could not parse consent data cookie at all:', e2);
                            }
                        }
                    } 
                    
                    // If still no data, try the main consent cookie
                    if (Object.keys(consentData).length === 0 && consent) {
                        try {
                            if (consent !== 'accepted' && consent !== 'rejected') {
                                // Try to decode and parse if it's JSON
                                var decodedConsent = decodeURIComponent(consent);
                                consentData = JSON.parse(decodedConsent);
                            } else {
                                // Simple mapping for basic accept/reject
                                consentData = {
                                    necessary: true,
                                    analytics: consent === 'accepted',
                                    advertising: consent === 'accepted',
                                    functional: consent === 'accepted'
                                };
                            }
                        } catch (e) {
                            console.warn('GDPR TCF: Error parsing main consent cookie:', e);
                            // Final fallback - simple accept/reject mapping
                            consentData = {
                                necessary: true,
                                analytics: consent === 'accepted',
                                advertising: consent === 'accepted',
                                functional: consent === 'accepted'
                            };
                        }
                    }
                    
                    // Final fallback if no consent data at all
                    if (Object.keys(consentData).length === 0) {
                        consentData = {
                            necessary: true,
                            analytics: false,
                            advertising: false,
                            functional: false
                        };
                    }
                } catch (e) {
                    console.warn('GDPR TCF: Error parsing consent data, defaulting to reject all:', e);
                    consentData = {
                        necessary: true,
                        analytics: false,
                        advertising: false,
                        functional: false
                    };
                }
                
                // Log consent data for debugging
                console.log('GDPR TCF: Generating TC String with consent data:', consentData);
                
                // Enhanced TC String generation with proper consent handling
                var purposes = '';
                var vendors = '';
                
                // Purpose consents (10 standard TCF purposes)
                var purposeMapping = [
                    consentData.necessary === true,      // 1: Store and/or access information on a device
                    consentData.advertising === true,    // 2: Select basic ads
                    consentData.advertising === true,    // 3: Create a personalised ads profile
                    consentData.advertising === true,    // 4: Select personalised ads
                    consentData.advertising === true,    // 5: Create a personalised content profile
                    consentData.functional === true,     // 6: Select personalised content
                    consentData.analytics === true,      // 7: Measure ad performance
                    consentData.analytics === true,      // 8: Measure content performance
                    consentData.analytics === true,      // 9: Apply market research to generate audience insights
                    consentData.functional === true      // 10: Develop and improve products
                ];
                
                // Convert boolean array to binary string
                purposes = purposeMapping.map(function(consent) { return consent ? '1' : '0'; }).join('');
                
                // Google AdSense vendor consents - specifically handle Google (vendor 755)
                // For simplicity, we'll create a basic vendor consent string
                var vendorConsents = [];
                for (var v = 1; v <= 755; v++) {
                    if (v === 755) { // Google
                        vendorConsents.push(consentData.advertising === true ? '1' : '0');
                    } else if (v <= 100) { // Other major vendors
                        vendorConsents.push(consentData.advertising === true ? '1' : '0');
                    } else {
                        vendorConsents.push('0'); // Default deny for unknown vendors
                    }
                }
                vendors = vendorConsents.join('');
                
                // Create a proper TC string format (simplified)
                var tcStringData = {
                    version: 2,
                    created: Math.floor(Date.now() / 1000),
                    lastUpdated: Math.floor(Date.now() / 1000),
                    cmpId: 1,
                    cmpVersion: 1,
                    consentScreen: 1,
                    consentLanguage: 'IT',
                    vendorListVersion: 1,
                    tcfPolicyVersion: 4,
                    isServiceSpecific: false,
                    useNonStandardStacks: false,
                    purposes: purposes,
                    vendors: vendors.substring(0, 100) // Limit vendor string length
                };
                
                // Generate base64 encoded TC string (simplified format)
                var tcString = 'CP' + btoa(JSON.stringify(tcStringData)).replace(/[^A-Za-z0-9]/g, '').substring(0, 87);
                
                console.log('GDPR TCF: Generated TC String:', tcString);
                console.log('GDPR TCF: Purpose consents:', purposes);
                console.log('GDPR TCF: Vendor consents (first 20):', vendors.substring(0, 20));
                
                return tcString;
            }
            
            function getCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }
            
            function handlePing(version, callback) {
                if (typeof callback === 'function') {
                    callback({
                        gdprApplies: window.__tcfapi.gdprApplies,
                        cmpLoaded: true,
                        cmpStatus: window.__tcfapi.cmpStatus,
                        displayStatus: window.__tcfapi.displayStatus,
                        apiVersion: version,
                        cmpId: window.__tcfapi.cmpId,
                        cmpVersion: window.__tcfapi.cmpVersion,
                        tcfPolicyVersion: window.__tcfapi.tcfPolicyVersion,
                        gvlVersion: 1
                    }, true);
                }
            }
            
            function handleGetTCData(version, callback, parameter) {
                var consent = getCookie('manus_gdpr_consent');
                var consentDataCookie = getCookie('manus_gdpr_consent_data');
                var tcString = generateTCString();
                
                var tcData = {
                    tcString: tcString,
                    tcfPolicyVersion: window.__tcfapi.tcfPolicyVersion,
                    cmpId: window.__tcfapi.cmpId,
                    cmpVersion: window.__tcfapi.cmpVersion,
                    gdprApplies: window.__tcfapi.gdprApplies,
                    eventStatus: consent ? 'tcloaded' : 'cmpuishown',
                    cmpStatus: consent ? 'loaded' : 'loaded',
                    listenerId: parameter || null,
                    isServiceSpecific: false,
                    useNonStandardStacks: false,
                    publisherCC: 'IT', // Publisher country code
                    outOfBand: {
                        allowedVendors: {},
                        disclosedVendors: {}
                    },
                    purpose: {
                        consents: {},
                        legitimateInterests: {}
                    },
                    vendor: {
                        consents: {},
                        legitimateInterests: {}
                    },
                    specialFeatureOptins: {},
                    publisher: {
                        consents: {},
                        legitimateInterests: {},
                        customPurpose: {
                            consents: {},
                            legitimateInterests: {}
                        },
                        restrictions: {}
                    }
                };
                
                // Fill purpose consents based on GDPR Cookie Consent settings
                try {
                    var consentData = {};
                    
                    // Get consent data from the detailed cookie first with proper URL decoding
                    if (consentDataCookie) {
                        try {
                            // Decode URL-encoded cookie data
                            var decodedData = decodeURIComponent(consentDataCookie);
                            var parsedConsentData = JSON.parse(decodedData);
                            if (parsedConsentData && parsedConsentData.data) {
                                consentData = parsedConsentData.data;
                            }
                        } catch (e) {
                            console.warn('GDPR TCF: Error parsing consent data cookie (trying fallback):', e);
                            // Try without decoding
                            try {
                                var parsedConsentData = JSON.parse(consentDataCookie);
                                if (parsedConsentData && parsedConsentData.data) {
                                    consentData = parsedConsentData.data;
                                }
                            } catch (e2) {
                                console.warn('GDPR TCF: Could not parse consent data cookie at all:', e2);
                            }
                        }
                    }
                    
                    // Fallback to simple consent mapping if no detailed data
                    if (Object.keys(consentData).length === 0) {
                        if (consent && consent !== 'accepted' && consent !== 'rejected') {
                            try {
                                var decodedConsent = decodeURIComponent(consent);
                                consentData = JSON.parse(decodedConsent);
                            } catch (e) {
                                console.warn('GDPR TCF: Error parsing main consent cookie:', e);
                                consentData = {};
                            }
                        }
                        
                        // Final fallback - simple accept/reject mapping
                        if (Object.keys(consentData).length === 0) {
                            consentData = {
                                necessary: true,
                                analytics: consent === 'accepted',
                                advertising: consent === 'accepted',
                                functional: consent === 'accepted'
                            };
                        }
                    }
                    
                    console.log('GDPR TCF: Using consent data for TCF response:', consentData);
                    
                    // Map to TCF purposes with explicit boolean values
                    tcData.purpose.consents[1] = consentData.necessary === true; // Store and/or access info
                    tcData.purpose.consents[2] = consentData.advertising === true; // Select basic ads
                    tcData.purpose.consents[3] = consentData.advertising === true; // Create personalized ads profile
                    tcData.purpose.consents[4] = consentData.advertising === true; // Select personalized ads
                    tcData.purpose.consents[5] = consentData.advertising === true; // Create personalized content profile
                    tcData.purpose.consents[6] = consentData.functional === true; // Select personalized content
                    tcData.purpose.consents[7] = consentData.analytics === true; // Measure ad performance
                    tcData.purpose.consents[8] = consentData.analytics === true; // Measure content performance
                    tcData.purpose.consents[9] = consentData.analytics === true; // Apply market research
                    tcData.purpose.consents[10] = consentData.functional === true; // Develop and improve products
                    
                    // Vendor consents - explicitly set Google (755) and other major ad vendors
                    // Google AdSense and other Google advertising services
                    tcData.vendor.consents[755] = consentData.advertising === true; // Google
                    tcData.vendor.consents[1] = consentData.advertising === true; // Other advertising vendors
                    tcData.vendor.consents[2] = consentData.advertising === true;
                    tcData.vendor.consents[3] = consentData.advertising === true;
                    
                    // Explicitly set other vendors based on advertising consent
                    for (var v = 1; v <= 800; v++) {
                        tcData.vendor.consents[v] = consentData.advertising === true;
                    }
                    
                    console.log('GDPR TCF: Final TCF purpose consents:', tcData.purpose.consents);
                    console.log('GDPR TCF: Final TCF vendor consents (Google 755):', tcData.vendor.consents[755]);
                    
                } catch (e) {
                    console.error('GDPR TCF: Error processing consent data:', e);
                    // Default to no consent except necessary if there's an error
                    for (var p = 1; p <= 10; p++) {
                        tcData.purpose.consents[p] = p === 1; // Only storage consent
                    }
                    // Explicitly deny all vendor consents
                    for (var v = 1; v <= 800; v++) {
                        tcData.vendor.consents[v] = false;
                    }
                }
                
                window.__tcfapi.tcData = tcData;
                
                if (typeof callback === 'function') {
                    callback(tcData, true);
                }
            }
            
            function handleAddEventListener(version, callback, parameter) {
                if (typeof callback === 'function') {
                    var listenerId = window.__tcfapi.eventListeners.length;
                    window.__tcfapi.eventListeners.push({
                        id: listenerId,
                        callback: callback,
                        parameter: parameter
                    });
                    
                    // Immediately call with current data
                    handleGetTCData(version, callback, listenerId);
                    
                    if (typeof parameter === 'function') {
                        parameter(listenerId, true);
                    }
                }
            }
            
            function handleRemoveEventListener(version, callback, listenerId) {
                window.__tcfapi.eventListeners = window.__tcfapi.eventListeners.filter(function(listener) {
                    return listener.id !== listenerId;
                });
                
                if (typeof callback === 'function') {
                    callback(true, true);
                }
            }
            
            function handleGetInAppTCData(version, callback, parameter) {
                // For in-app usage - same as getTCData for web
                handleGetTCData(version, callback, parameter);
            }
            
            function handleGetVendorList(version, callback, parameter) {
                // Return a basic vendor list - in production, fetch from IAB
                var vendorList = {
                    gvlSpecificationVersion: 3,
                    vendorListVersion: 1,
                    tcfPolicyVersion: 4,
                    lastUpdated: new Date().toISOString(),
                    purposes: {},
                    specialPurposes: {},
                    features: {},
                    specialFeatures: {},
                    stacks: {},
                    vendors: {}
                };
                
                if (typeof callback === 'function') {
                    callback(vendorList, true);
                }
            }
            
            // Update status when consent changes
            function updateTCFStatus() {
                var consent = getCookie('manus_gdpr_consent');
                window.__tcfapi.cmpStatus = 'loaded';
                window.__tcfapi.displayStatus = consent ? 'hidden' : 'visible';
                
                console.log('GDPR TCF: Updating TCF status, consent:', consent);
                
                // Force regenerate TCF data with current consent
                var tcString = generateTCString();
                
                // Update global TCF data
                handleGetTCData(2, function(tcData, success) {
                    if (success && tcData) {
                        window.__tcfapi.tcData = tcData;
                        console.log('GDPR TCF: Updated TCF data:', tcData);
                        
                        // Notify all event listeners with updated data
                        window.__tcfapi.eventListeners.forEach(function(listener) {
                            try {
                                listener.callback(tcData, true);
                                console.log('GDPR TCF: Notified listener', listener.id);
                            } catch (e) {
                                console.error('GDPR TCF: Error notifying listener', listener.id, e);
                            }
                        });
                        
                        // Force Google AdSense to check consent again
                        if (window.googletag && typeof window.googletag === 'object') {
                            console.log('GDPR TCF: Notifying Google Ad Manager of consent changes');
                            try {
                                // Force Google Ad Manager to re-evaluate consent
                                if (window.googletag.cmd) {
                                    window.googletag.cmd.push(function() {
                                        if (window.googletag.pubads) {
                                            // Clear existing ads if consent was denied
                                            if (!tcData.purpose.consents[2] && !tcData.purpose.consents[3] && !tcData.purpose.consents[4]) {
                                                console.log('GDPR TCF: Clearing Google ads due to consent denial');
                                                window.googletag.pubads().clear();
                                            }
                                            // Refresh consent state
                                            window.googletag.pubads().refresh();
                                        }
                                    });
                                }
                            } catch (e) {
                                console.error('GDPR TCF: Error notifying Google Ad Manager:', e);
                            }
                        }
                        
                        // Force AdSense to check consent again if present
                        if (window.adsbygoogle) {
                            console.log('GDPR TCF: Notifying AdSense of consent changes');
                            try {
                                // AdSense consent notification
                                window.adsbygoogle = window.adsbygoogle || [];
                                
                                // If consent denied, block new ads
                                if (!tcData.purpose.consents[2] && !tcData.purpose.consents[3] && !tcData.purpose.consents[4]) {
                                    console.log('GDPR TCF: Blocking AdSense due to consent denial');
                                    // Remove existing ad elements
                                    var adElements = document.querySelectorAll('.adsbygoogle');
                                    adElements.forEach(function(ad) {
                                        if (ad.parentNode) {
                                            ad.parentNode.removeChild(ad);
                                        }
                                    });
                                } else {
                                    console.log('GDPR TCF: AdSense consent granted, ads can load');
                                }
                            } catch (e) {
                                console.error('GDPR TCF: Error handling AdSense consent:', e);
                            }
                        }
                    }
                });
            }
            
            // Listen for consent changes
            document.addEventListener('manus-gdpr-consent-updated', updateTCFStatus);
            
            // Initialize
            updateTCFStatus();
            
            // Set global variables for compatibility
            window.__tcfapi.a = window.__tcfapi.a || [];
            window.__tcfapi.gdprApplies = true;
            
            console.log('GDPR Cookie Consent: IAB TCF v2.2 API initialized');
            
        })();
        </script>
        <?php
    }

    /**
     * Add additional AdSense and advertising blocking script
     */
    public function add_adsense_blocking_script() {
        ?>
        <script type="text/javascript">
        (function() {
            'use strict';
            
            // Advanced AdSense blocking functionality
            
            // Override fetch to block advertising requests
            if (window.fetch) {
                const originalFetch = window.fetch;
                window.fetch = function(resource, init) {
                    const url = typeof resource === 'string' ? resource : resource.url;
                    
                    // Check if this is an advertising request
                    if (url && shouldBlockAdvertisingRequest(url)) {
                        console.log('GDPR: Blocked advertising fetch request:', url);
                        return Promise.reject(new Error('Blocked by GDPR consent'));
                    }
                    
                    return originalFetch.apply(this, arguments);
                };
            }
            
            // Override XMLHttpRequest to block advertising requests
            const originalXHROpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                if (url && shouldBlockAdvertisingRequest(url)) {
                    console.log('GDPR: Blocked advertising XHR request:', url);
                    // Return a mock response
                    this.responseText = '';
                    this.status = 204;
                    return;
                }
                
                return originalXHROpen.apply(this, arguments);
            };
            
            // Function to check if a URL should be blocked
            function shouldBlockAdvertisingRequest(url) {
                // Get current consent with proper decoding
                const consent = getCookie('manus_gdpr_consent');
                const consentData = getCookie('manus_gdpr_consent_data');
                
                let blockAds = true;
                
                try {
                    if (consentData) {
                        // Decode URL-encoded cookie data
                        const decodedData = decodeURIComponent(consentData);
                        const parsedData = JSON.parse(decodedData);
                        blockAds = !parsedData.data.advertising;
                    } else if (consent) {
                        // Simple consent check
                        blockAds = consent !== 'accepted';
                    }
                } catch (e) {
                    console.warn('GDPR: Error parsing consent for request blocking:', e);
                    blockAds = true; // Default to blocking on error
                }
                
                if (!blockAds) {
                    return false;
                }
                
                // Check if URL is advertising-related
                const adDomains = [
                    'googlesyndication.com',
                    'googleadservices.com',
                    'googletagmanager.com',
                    'doubleclick.net',
                    'googletagservices.com',
                    'google-analytics.com',
                    'facebook.com/tr',
                    'facebook.net',
                    'fbcdn.net'
                ];
                
                return adDomains.some(domain => url.includes(domain));
            }
            
            // Helper function to get cookie
            function getCookie(name) {
                const value = "; " + document.cookie;
                const parts = value.split("; " + name + "=");
                if (parts.length === 2) {
                    return parts.pop().split(";").shift();
                }
                return null;
            }
            
            // Function to clean advertising cookies
            function cleanAdvertisingCookies() {
                const adCookiePatterns = [
                    /^_ga/, /^_gid/, /^_gat/, /^__gads/, /^__gpi/,
                    /^_fbp/, /^_fbc/, /^fr/, /^IDE/, /^test_cookie/,
                    /^NID/, /^DSID/, /^FLC/, /^AID/, /^TAID/,
                    /^google_/, /^__utm/, /^_dc_gtm_/
                ];
                
                // Get all cookies
                const cookies = document.cookie.split(';');
                
                cookies.forEach(cookie => {
                    const cookieName = cookie.split('=')[0].trim();
                    
                    // Check if this cookie matches advertising patterns
                    if (adCookiePatterns.some(pattern => pattern.test(cookieName))) {
                        // Delete the cookie
                        document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                        document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + window.location.hostname + ';';
                        document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.' + window.location.hostname + ';';
                        console.log('GDPR: Cleaned advertising cookie:', cookieName);
                    }
                });
            }
            
            // Listen for consent changes
            document.addEventListener('manus-gdpr-consent-updated', function(event) {
                const consentData = event.detail.consentData;
                
                if (!consentData.advertising) {
                    console.log('GDPR: Advertising consent denied, cleaning cookies and blocking content');
                    cleanAdvertisingCookies();
                    
                    // Also remove localStorage items
                    const adLocalStoragePatterns = [
                        /^_ga/, /^_gid/, /^google_/, /^__utm/,
                        /^_fbp/, /^_fbc/, /^facebook/
                    ];
                    
                    try {
                        for (let i = localStorage.length - 1; i >= 0; i--) {
                            const key = localStorage.key(i);
                            if (key && adLocalStoragePatterns.some(pattern => pattern.test(key))) {
                                localStorage.removeItem(key);
                                console.log('GDPR: Cleaned advertising localStorage item:', key);
                            }
                        }
                    } catch (e) {
                        console.warn('GDPR: Could not clean localStorage:', e);
                    }
                }
            });
            
            // Initial cleanup if advertising consent is denied
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    const consent = getCookie('manus_gdpr_consent');
                    const consentData = getCookie('manus_gdpr_consent_data');
                    
                    let shouldClean = true;
                    
                    try {
                        if (consentData) {
                            // Decode URL-encoded cookie data
                            const decodedData = decodeURIComponent(consentData);
                            const parsedData = JSON.parse(decodedData);
                            shouldClean = !parsedData.data.advertising;
                        } else if (consent) {
                            shouldClean = consent !== 'accepted';
                        }
                    } catch (e) {
                        console.warn('GDPR: Error parsing consent for cleanup:', e);
                        shouldClean = true;
                    }
                    
                    if (shouldClean) {
                        cleanAdvertisingCookies();
                    }
                }, 1000);
            });
            
        })();
        </script>
        <?php
    }

    /**
     * Add custom CSS from settings to the frontend.
     *
     * @since    1.0.0
     */
    private function add_custom_css() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $custom_css = isset( $options['custom_css'] ) ? trim( $options['custom_css'] ) : '';
        
        if ( ! empty( $custom_css ) ) {
            // Add custom CSS inline
            wp_add_inline_style( $this->plugin_name, $this->sanitize_css( $custom_css ) );
        }
    }

    /**
     * Sanitize CSS to prevent XSS while preserving valid CSS.
     *
     * @since    1.0.0
     * @param    string $css Raw CSS input.
     * @return   string Sanitized CSS.
     */
    private function sanitize_css( $css ) {
        // Remove dangerous patterns while preserving valid CSS
        $css = preg_replace( '/javascript\s*:/i', '', $css );
        $css = preg_replace( '/expression\s*\(/i', '', $css );
        $css = preg_replace( '/vbscript\s*:/i', '', $css );
        $css = preg_replace( '/@import\s+["\'].*?["\'];?/i', '', $css );
        $css = preg_replace( '/binding\s*:/i', '', $css );
        $css = preg_replace( '/behavior\s*:/i', '', $css );
        $css = preg_replace( '/data\s*:/i', '', $css );
        
        // Remove HTML tags if any
        $css = strip_tags( $css );
        
        // Add comment for identification
        $css = "/* GDPR Cookie Consent Custom CSS */\n" . $css;
        
        return $css;
    }
}