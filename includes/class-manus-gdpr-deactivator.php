<?php
/**
 * Fired during plugin deactivation
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Manus AI
 */
class Manus_GDPR_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        self::unschedule_cleanup_hooks();
        
        // Optionally, clean up database tables or settings here.
        // For now, we'll leave the data for re-activation.
    }

    private static function unschedule_cleanup_hooks() {
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

}
