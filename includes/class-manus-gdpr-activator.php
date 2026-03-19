<?php
/**
 * Fired during plugin activation
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Manus AI
 */
class Manus_GDPR_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        require_once RUBIK_GDPR_PATH . 'includes/class-manus-gdpr-database.php';
        Manus_GDPR_Database::create_tables();
        
        // Schedule automatic cleanup of expired consents (daily)
        self::unschedule_cleanup_hooks( true );

        if ( ! wp_next_scheduled( 'rubik_gdpr_cleanup_expired_consents' ) ) {
            wp_schedule_event( time(), 'daily', 'rubik_gdpr_cleanup_expired_consents' );
        }
    }

    private static function unschedule_cleanup_hooks( $keep_rubik ) {
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

                if ( $keep_rubik && $hook === 'rubik_gdpr_cleanup_expired_consents' ) {
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

}
