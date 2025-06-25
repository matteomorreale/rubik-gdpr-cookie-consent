<?php
/**
 * Cookie Scanner functionality
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * Cookie Scanner class
 *
 * Handles cookie detection and analysis for GDPR compliance
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Matteo Morreale
 */
class Manus_GDPR_Cookie_Scanner {

    /**
     * Cookie categories and their patterns
     */
    private static $cookie_categories = array(
        'necessary' => array(
            'patterns' => array(
                'wp-settings-',
                'wordpress_',
                'wp_',
                'PHPSESSID',
                'session',
                'csrf',
                'xsrf',
                '_wpnonce',
                'wordpress_logged_in_',
                'wordpress_test_cookie'
            ),
            'label' => 'Cookie Necessari',
            'description' => 'Cookie essenziali per il funzionamento del sito'
        ),
        'analytics' => array(
            'patterns' => array(
                '_ga',
                '_gid',
                '_gat',
                '__utma',
                '__utmb',
                '__utmc',
                '__utmt',
                '__utmz',
                '_dc_gtm_',
                'gtm_',
                'google_analytics'
            ),
            'label' => 'Cookie Analitici',
            'description' => 'Cookie per analisi e statistiche del sito'
        ),
        'advertising' => array(
            'patterns' => array(
                'doubleclick',
                'googlesyndication',
                'googleadservices',
                'adsystem',
                'fbp',
                'fbq',
                'tr',
                '_fbp',
                'fr',
                'datr',
                'sb',
                'wd'
            ),
            'label' => 'Cookie Pubblicitari',
            'description' => 'Cookie per pubblicità personalizzata'
        ),
        'functional' => array(
            'patterns' => array(
                'lang',
                'language',
                'currency',
                'theme',
                'preferences',
                'settings'
            ),
            'label' => 'Cookie Funzionali',
            'description' => 'Cookie per migliorare l\'esperienza utente'
        )
    );

    /**
     * Initialize the scanner
     */
    public static function init() {
        add_action( 'wp_ajax_manus_gdpr_scan_cookies', array( __CLASS__, 'ajax_scan_cookies' ) );
        add_action( 'wp_ajax_manus_gdpr_get_scan_results', array( __CLASS__, 'ajax_get_scan_results' ) );
        add_action( 'wp_ajax_manus_gdpr_save_cookie_settings', array( __CLASS__, 'ajax_save_cookie_settings' ) );
        add_action( 'wp_ajax_manus_gdpr_delete_scan_result', array( __CLASS__, 'ajax_delete_scan_result' ) );
    }

    /**
     * AJAX handler for cookie scanning
     */
    public static function ajax_scan_cookies() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'manus_gdpr_scanner_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $url = sanitize_url( $_POST['url'] ?? '' );
        $cookies_data = json_decode( stripslashes( $_POST['cookies'] ?? '[]' ), true );

        if ( empty( $url ) || ! is_array( $cookies_data ) ) {
            wp_send_json_error( 'Invalid data provided' );
        }

        $scan_id = self::save_scan_result( $url, $cookies_data );

        if ( $scan_id ) {
            wp_send_json_success( array(
                'scan_id' => $scan_id,
                'message' => 'Scansione completata con successo'
            ) );
        } else {
            wp_send_json_error( 'Errore nel salvare i risultati della scansione' );
        }
    }

    /**
     * Save scan results to database
     */
    private static function save_scan_result( $url, $cookies_data ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'manus_gdpr_cookie_scans';

        // Create table if not exists
        self::create_scan_table();

        $processed_cookies = array();
        
        foreach ( $cookies_data as $cookie ) {
            $category = self::categorize_cookie( $cookie['name'] ?? '' );
            $processed_cookies[] = array(
                'name' => sanitize_text_field( $cookie['name'] ?? '' ),
                'value' => sanitize_text_field( substr( $cookie['value'] ?? '', 0, 100 ) ), // Limit value length
                'domain' => sanitize_text_field( $cookie['domain'] ?? '' ),
                'path' => sanitize_text_field( $cookie['path'] ?? '' ),
                'expires' => sanitize_text_field( $cookie['expires'] ?? '' ),
                'secure' => (bool) ( $cookie['secure'] ?? false ),
                'httpOnly' => (bool) ( $cookie['httpOnly'] ?? false ),
                'sameSite' => sanitize_text_field( $cookie['sameSite'] ?? '' ),
                'category' => $category,
                'blocked' => $category !== 'necessary' // Block non-necessary cookies by default
            );
        }

        $result = $wpdb->insert(
            $table_name,
            array(
                'url' => $url,
                'cookies_data' => serialize( $processed_cookies ),
                'scan_date' => current_time( 'mysql' ),
                'user_id' => get_current_user_id()
            ),
            array( '%s', '%s', '%s', '%d' )
        );

        return $result !== false ? $wpdb->insert_id : false;
    }

    /**
     * Create scan results table
     */
    private static function create_scan_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'manus_gdpr_cookie_scans';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            url varchar(255) NOT NULL,
            cookies_data longtext NOT NULL,
            scan_date datetime DEFAULT CURRENT_TIMESTAMP,
            user_id int(11) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY idx_scan_date (scan_date),
            KEY idx_user_id (user_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Categorize cookie based on its name
     */
    private static function categorize_cookie( $cookie_name ) {
        $cookie_name = strtolower( $cookie_name );

        foreach ( self::$cookie_categories as $category => $data ) {
            foreach ( $data['patterns'] as $pattern ) {
                if ( strpos( $cookie_name, strtolower( $pattern ) ) !== false ) {
                    return $category;
                }
            }
        }

        return 'functional'; // Default category
    }

    /**
     * AJAX handler for getting scan results
     */
    public static function ajax_get_scan_results() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'manus_gdpr_scanner_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $results = self::get_scan_results();
        wp_send_json_success( $results );
    }

    /**
     * Get scan results from database
     */
    public static function get_scan_results( $limit = 50 ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'manus_gdpr_cookie_scans';

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY scan_date DESC LIMIT %d",
            $limit
        ) );

        $processed_results = array();
        foreach ( $results as $result ) {
            $cookies_data = unserialize( $result->cookies_data );
            $processed_results[] = array(
                'id' => $result->id,
                'url' => $result->url,
                'cookies' => $cookies_data,
                'scan_date' => $result->scan_date,
                'user_id' => $result->user_id,
                'cookies_count' => count( $cookies_data ),
                'categories_summary' => self::get_categories_summary( $cookies_data )
            );
        }

        return $processed_results;
    }

    /**
     * Get categories summary for scan result
     */
    private static function get_categories_summary( $cookies_data ) {
        $summary = array();
        
        foreach ( $cookies_data as $cookie ) {
            $category = $cookie['category'] ?? 'functional';
            $summary[$category] = ( $summary[$category] ?? 0 ) + 1;
        }

        return $summary;
    }

    /**
     * AJAX handler for saving cookie settings
     */
    public static function ajax_save_cookie_settings() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'manus_gdpr_scanner_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $scan_id = intval( $_POST['scan_id'] ?? 0 );
        $cookie_settings = json_decode( stripslashes( $_POST['settings'] ?? '{}' ), true );

        if ( $scan_id && is_array( $cookie_settings ) ) {
            $result = self::update_cookie_settings( $scan_id, $cookie_settings );
            
            if ( $result ) {
                wp_send_json_success( 'Impostazioni salvate con successo' );
            }
        }

        wp_send_json_error( 'Errore nel salvare le impostazioni' );
    }

    /**
     * Update cookie settings in database
     */
    private static function update_cookie_settings( $scan_id, $cookie_settings ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'manus_gdpr_cookie_scans';

        // Get current data
        $current_data = $wpdb->get_var( $wpdb->prepare(
            "SELECT cookies_data FROM $table_name WHERE id = %d",
            $scan_id
        ) );

        if ( ! $current_data ) {
            return false;
        }

        $cookies_data = unserialize( $current_data );

        // Update blocked status
        foreach ( $cookies_data as &$cookie ) {
            $cookie_name = $cookie['name'];
            if ( isset( $cookie_settings[$cookie_name] ) ) {
                $cookie['blocked'] = ! $cookie_settings[$cookie_name]; // Inverted because settings contain "allowed" status
            }
        }

        // Save updated data
        return $wpdb->update(
            $table_name,
            array( 'cookies_data' => serialize( $cookies_data ) ),
            array( 'id' => $scan_id ),
            array( '%s' ),
            array( '%d' )
        );
    }

    /**
     * AJAX handler for deleting scan result
     */
    public static function ajax_delete_scan_result() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'manus_gdpr_scanner_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $scan_id = intval( $_POST['scan_id'] ?? 0 );

        if ( $scan_id ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'manus_gdpr_cookie_scans';

            $result = $wpdb->delete(
                $table_name,
                array( 'id' => $scan_id ),
                array( '%d' )
            );

            if ( $result !== false ) {
                wp_send_json_success( 'Scansione eliminata con successo' );
            }
        }

        wp_send_json_error( 'Errore nell\'eliminare la scansione' );
    }

    /**
     * Get cookie categories
     */
    public static function get_cookie_categories() {
        return self::$cookie_categories;
    }

    /**
     * Get blocked cookies for frontend
     */
    public static function get_blocked_cookies() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'manus_gdpr_cookie_scans';

        // Get the most recent scan result
        $latest_scan = $wpdb->get_var(
            "SELECT cookies_data FROM $table_name ORDER BY scan_date DESC LIMIT 1"
        );

        if ( ! $latest_scan ) {
            return array();
        }

        $cookies_data = unserialize( $latest_scan );
        $blocked_cookies = array();

        foreach ( $cookies_data as $cookie ) {
            if ( $cookie['blocked'] ?? false ) {
                $blocked_cookies[] = array(
                    'name' => $cookie['name'],
                    'domain' => $cookie['domain'] ?? '',
                    'category' => $cookie['category'] ?? 'functional'
                );
            }
        }

        return $blocked_cookies;
    }
}

// Initialize the scanner
Manus_GDPR_Cookie_Scanner::init();
