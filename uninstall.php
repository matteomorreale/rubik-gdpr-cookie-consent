<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * @link       #
 * @since      1.0.3
 *
 * @package    Manus_GDPR
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Get plugin settings to check if data deletion is enabled
$options = get_option( 'manus_gdpr_settings' );
$delete_on_uninstall = isset( $options['delete_data_on_uninstall'] ) ? $options['delete_data_on_uninstall'] : false;

function rubik_gdpr_unschedule_cleanup_hooks() {
    if ( ! function_exists( '_get_cron_array' ) ) {
        return false;
    }

    $cron = _get_cron_array();
    if ( empty( $cron ) ) {
        return false;
    }

    $unscheduled_any = false;

    foreach ( $cron as $timestamp => $hooks ) {
        if ( ! is_array( $hooks ) ) {
            continue;
        }

        foreach ( $hooks as $hook => $events ) {
            if ( ! is_string( $hook ) || strpos( $hook, 'gdpr_cleanup_expired_consents' ) === false ) {
                continue;
            }

            if ( ! is_array( $events ) ) {
                continue;
            }

            foreach ( $events as $event ) {
                wp_unschedule_event( (int) $timestamp, $hook, isset( $event['args'] ) ? (array) $event['args'] : array() );
                $unscheduled_any = true;
            }
        }
    }

    return $unscheduled_any;
}

// Only delete data if the option is enabled
if ( $delete_on_uninstall ) {
    global $wpdb;
    
    // Delete consent data
    $consents_table = $wpdb->prefix . 'manus_gdpr_consents';
    $wpdb->query( "DROP TABLE IF EXISTS $consents_table" );
    
    // Delete cookie data
    $cookies_table = $wpdb->prefix . 'manus_gdpr_cookies';
    $wpdb->query( "DROP TABLE IF EXISTS $cookies_table" );
    
    // Delete scan data
    $scans_table = $wpdb->prefix . 'manus_gdpr_cookie_scans';
    $wpdb->query( "DROP TABLE IF EXISTS $scans_table" );
    
    // Delete plugin options
    delete_option( 'manus_gdpr_settings' );
    delete_option( 'manus_gdpr_db_version' );
    
    // Clear scheduled cron events
    wp_clear_scheduled_hook( 'rubik_gdpr_cleanup_expired_consents' );
    rubik_gdpr_unschedule_cleanup_hooks();
    
    // Delete transients (if any)
    delete_transient( 'manus_gdpr_cookie_scan_cache' );
    
    // On multisite, delete options for all sites
    if ( is_multisite() ) {
        $sites = get_sites();
        foreach ( $sites as $site ) {
            switch_to_blog( $site->blog_id );
            
            // Delete tables for this site
            $consents_table = $wpdb->prefix . 'manus_gdpr_consents';
            $wpdb->query( "DROP TABLE IF EXISTS $consents_table" );
            
            $cookies_table = $wpdb->prefix . 'manus_gdpr_cookies';
            $wpdb->query( "DROP TABLE IF EXISTS $cookies_table" );
            
            $scans_table = $wpdb->prefix . 'manus_gdpr_cookie_scans';
            $wpdb->query( "DROP TABLE IF EXISTS $scans_table" );
            
            // Delete options for this site
            delete_option( 'manus_gdpr_settings' );
            delete_option( 'manus_gdpr_db_version' );
            
            // Clear cron events for this site
            wp_clear_scheduled_hook( 'rubik_gdpr_cleanup_expired_consents' );
            rubik_gdpr_unschedule_cleanup_hooks();
            
            // Delete transients for this site
            delete_transient( 'manus_gdpr_cookie_scan_cache' );
            
            restore_current_blog();
        }
    }
}
