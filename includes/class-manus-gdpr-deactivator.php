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
        // Clear scheduled cron jobs
        wp_clear_scheduled_hook( 'manus_gdpr_cleanup_expired_consents' );
        
        // Optionally, clean up database tables or settings here.
        // For now, we'll leave the data for re-activation.
    }

}