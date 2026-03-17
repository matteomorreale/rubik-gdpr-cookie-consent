<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Manus AI
 */
class Manus_GDPR_i18n {


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        $languages_rel_path = dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/';

        load_plugin_textdomain( 'm2-gdpr', false, $languages_rel_path );

        $locale = determine_locale();
        $locale = apply_filters( 'plugin_locale', $locale, 'm2-gdpr' );

        $languages_dir = dirname( dirname( __FILE__ ) ) . '/languages/';

        $m2_mo = $languages_dir . 'm2-gdpr-' . $locale . '.mo';
        if ( is_readable( $m2_mo ) ) {
            load_textdomain( 'm2-gdpr', $m2_mo );
        }

        $legacy_mo = $languages_dir . 'manus-gdpr-' . $locale . '.mo';
        if ( is_readable( $legacy_mo ) ) {
            load_textdomain( 'm2-gdpr', $legacy_mo );
        }

    }

}
