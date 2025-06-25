<?php
/**
 * Manage database tables for the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * Manages database tables for the plugin.
 *
 * This class defines all methods necessary to create and manage the plugin's database tables.
 *
 * @since      1.0.0
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Manus AI
 */
class Manus_GDPR_Database {

    /**
     * Create necessary database tables.
     *
     * @since    1.0.0
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table for storing user consents
        $table_name_consents = $wpdb->prefix . 'manus_gdpr_consents';
        $sql_consents = "CREATE TABLE $table_name_consents (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45) NOT NULL,
            consent_status varchar(20) NOT NULL,
            consent_data longtext NOT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Table for storing cookie and service information
        $table_name_cookies = $wpdb->prefix . 'manus_gdpr_cookies';
        $sql_cookies = "CREATE TABLE $table_name_cookies (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            domain varchar(255) NOT NULL,
            path varchar(255) NOT NULL DEFAULT '/',
            expiry int(11) NOT NULL,
            type varchar(50) NOT NULL,
            description text,
            category varchar(50) NOT NULL,
            service_name varchar(255),
            script_to_block longtext,
            script_to_not_block longtext,
            PRIMARY KEY  (id),
            UNIQUE KEY name_domain_path (name,domain,path)
        ) $charset_collate;";

        // Table for storing cookie scan results
        $table_name_scans = $wpdb->prefix . 'manus_gdpr_cookie_scans';
        $sql_scans = "CREATE TABLE $table_name_scans (
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
        dbDelta( $sql_consents );
        dbDelta( $sql_cookies );
        dbDelta( $sql_scans );
    }

    /**
     * Get all cookies and services from the database.
     *
     * @since    1.0.0
     * @return   array  Array of cookies and services.
     */
    public static function get_all_cookies_and_services() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'manus_gdpr_cookies';
        return $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
    }

    /**
     * Insert or update a cookie/service in the database.
     *
     * @since    1.0.0
     * @param    array  $data  Data to insert/update.
     * @return   bool   True on success, false on failure.
     */
    public static function insert_or_update_cookie_service( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'manus_gdpr_cookies';

        $existing_cookie = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE name = %s AND domain = %s AND path = %s",
                $data['name'], $data['domain'], $data['path']
            )
        );

        if ( $existing_cookie ) {
            return $wpdb->update(
                $table_name,
                $data,
                array( 'id' => $existing_cookie->id )
            );
        } else {
            return $wpdb->insert(
                $table_name,
                $data
            );
        }
    }

    /**
     * Record user consent.
     *
     * @since    1.0.0
     * @param    int    $user_id        User ID (optional).
     * @param    string $ip_address     User IP address.
     * @param    string $consent_status Consent status (e.g., 'accepted', 'rejected', 'partial').
     * @param    array  $consent_data   Serialized array of consent preferences.
     * @return   bool   True on success, false on failure.
     */
    public static function record_consent( $user_id, $ip_address, $consent_status, $consent_data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'manus_gdpr_consents';

        $data = array(
            'user_id'       => $user_id,
            'ip_address'    => $ip_address,
            'consent_status' => $consent_status,
            'consent_data'  => serialize( $consent_data ),
        );

        $format = array( '%d', '%s', '%s', '%s' );

        return $wpdb->insert( $table_name, $data, $format );
    }

    /**
     * Get consent records with pagination and filtering.
     *
     * @since    1.0.0
     * @param    int    $per_page     Number of records per page.
     * @param    int    $current_page Current page number.
     * @param    string $filter_status Filter by consent status.
     * @param    string $search_term   Search term for IP address or user ID.
     * @return   array  Array containing records and total count.
     */
    public static function get_consent_records( $per_page = 20, $current_page = 1, $filter_status = '', $search_term = '' ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'manus_gdpr_consents';
        
        $offset = ( $current_page - 1 ) * $per_page;
        
        $where_conditions = array();
        $where_values = array();
        
        if ( ! empty( $filter_status ) ) {
            $where_conditions[] = "consent_status = %s";
            $where_values[] = $filter_status;
        }
        
        if ( ! empty( $search_term ) ) {
            $where_conditions[] = "(ip_address LIKE %s OR user_id = %d)";
            $where_values[] = '%' . $wpdb->esc_like( $search_term ) . '%';
            $where_values[] = intval( $search_term );
        }
        
        $where_clause = '';
        if ( ! empty( $where_conditions ) ) {
            $where_clause = ' WHERE ' . implode( ' AND ', $where_conditions );
        }
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table_name" . $where_clause;
        if ( ! empty( $where_values ) ) {
            $count_query = $wpdb->prepare( $count_query, $where_values );
        }
        $total_items = $wpdb->get_var( $count_query );
        
        // Get records
        $query = "SELECT * FROM $table_name" . $where_clause . " ORDER BY timestamp DESC LIMIT %d OFFSET %d";
        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        $records = $wpdb->get_results( $wpdb->prepare( $query, $query_values ), ARRAY_A );
        
        return array(
            'records' => $records,
            'total_items' => $total_items,
            'total_pages' => ceil( $total_items / $per_page )
        );
    }

    /**
     * Get consent statistics.
     *
     * @since    1.0.0
     * @return   array  Array with consent statistics.
     */
    public static function get_consent_statistics() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'manus_gdpr_consents';
        
        $stats = array();
        
        // Total consents
        $stats['total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
        
        // Consents by status
        $status_counts = $wpdb->get_results( "SELECT consent_status, COUNT(*) as count FROM $table_name GROUP BY consent_status", ARRAY_A );
        foreach ( $status_counts as $status ) {
            $stats[$status['consent_status']] = $status['count'];
        }
        
        // Recent consents (last 30 days)
        $stats['recent'] = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)" );
        
        return $stats;
    }

    /**
     * Insert sample consent data for testing purposes.
     *
     * @since    1.0.0
     * @return   bool   True on success, false on failure.
     */
    public static function insert_sample_consent_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'manus_gdpr_consents';
        
        // Check if sample data already exists
        $existing_sample = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE ip_address LIKE '192.168.%'" );
        if ( $existing_sample > 0 ) {
            return false; // Sample data already exists
        }
        
        $sample_data = array(
            array(
                'user_id' => 1,
                'ip_address' => '192.168.1.100',
                'consent_status' => 'accepted',
                'consent_data' => serialize( array(
                    'necessary' => true,
                    'analytics' => true,
                    'marketing' => true,
                    'preferences' => true
                ) ),
                'timestamp' => current_time( 'mysql' )
            ),
            array(
                'user_id' => null,
                'ip_address' => '192.168.1.101',
                'consent_status' => 'rejected',
                'consent_data' => serialize( array(
                    'necessary' => true,
                    'analytics' => false,
                    'marketing' => false,
                    'preferences' => false
                ) ),
                'timestamp' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) )
            ),
            array(
                'user_id' => 2,
                'ip_address' => '192.168.1.102',
                'consent_status' => 'partial',
                'consent_data' => serialize( array(
                    'necessary' => true,
                    'analytics' => true,
                    'marketing' => false,
                    'preferences' => true
                ) ),
                'timestamp' => date( 'Y-m-d H:i:s', strtotime( '-2 hours' ) )
            ),
            array(
                'user_id' => null,
                'ip_address' => '192.168.1.103',
                'consent_status' => 'accepted',
                'consent_data' => serialize( array(
                    'necessary' => true,
                    'analytics' => true,
                    'marketing' => true,
                    'preferences' => false
                ) ),
                'timestamp' => date( 'Y-m-d H:i:s', strtotime( '-3 days' ) )
            ),
            array(
                'user_id' => null,
                'ip_address' => '192.168.1.104',
                'consent_status' => 'partial',
                'consent_data' => serialize( array(
                    'necessary' => true,
                    'analytics' => false,
                    'marketing' => true,
                    'preferences' => false
                ) ),
                'timestamp' => date( 'Y-m-d H:i:s', strtotime( '-1 week' ) )
            )
        );
        
        $success = true;
        foreach ( $sample_data as $data ) {
            $result = $wpdb->insert( $table_name, $data );
            if ( $result === false ) {
                $success = false;
            }
        }
        
        return $success;
    }

}