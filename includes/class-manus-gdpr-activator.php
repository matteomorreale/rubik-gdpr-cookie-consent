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
        require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-database.php';
        Manus_GDPR_Database::create_tables();
        
        // Schedule automatic cleanup of expired consents (daily)
        if ( ! wp_next_scheduled( 'manus_gdpr_cleanup_expired_consents' ) ) {
            wp_schedule_event( time(), 'daily', 'manus_gdpr_cleanup_expired_consents' );
        }
    }

}