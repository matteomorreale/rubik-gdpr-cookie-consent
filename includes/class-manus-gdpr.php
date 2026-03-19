<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Manus AI
 */
class Manus_GDPR {

    /**
     * The loader that is responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Manus_GDPR_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'RUBIK_GDPR_VERSION' ) ) {
            $this->version = RUBIK_GDPR_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'm2-gdpr';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Manus_GDPR_Loader. Orchestrates the hooks of the plugin.
     * - Manus_GDPR_i18n. Defines internationalization functionality.
     * - Manus_GDPR_Admin. Defines all hooks for the admin area.
     * - Manus_GDPR_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-manus-gdpr-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-manus-gdpr-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-manus-gdpr-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-manus-gdpr-frontend.php';

        $this->loader = new Manus_GDPR_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Manus_GDPR_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Manus_GDPR_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Manus_GDPR_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Manus_GDPR_Frontend( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_footer', $plugin_public, 'display_cookie_banner' );
        $this->loader->add_action( 'wp_head', $plugin_public, 'block_scripts' );
        $this->loader->add_action( 'wp_ajax_matteomorreale_gdpr_consent_action', $plugin_public, 'handle_consent_action' );
        $this->loader->add_action( 'wp_ajax_nopriv_matteomorreale_gdpr_consent_action', $plugin_public, 'handle_consent_action' );
        $this->loader->add_action( 'rubik_gdpr_cleanup_expired_consents', $this, 'cleanup_expired_consents' );
        $this->loader->add_action( 'init', $this, 'migrate_cron_hooks' );

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Manus_GDPR_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Clean up expired consents based on retention settings.
     * Called by cron job.
     *
     * @since    1.0.3
     */
    public function cleanup_expired_consents() {
        // Get retention settings
        $options = get_option( 'manus_gdpr_settings' );
        $retention_days = isset( $options['consent_retention_period'] ) ? (int) $options['consent_retention_period'] : 365;
        $retention_days = apply_filters( 'rubik_gdpr_retention_period', $retention_days );
        
        // Load database class
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-manus-gdpr-database.php';
        
        // Clean up expired consents
        $deleted = Manus_GDPR_Database::clear_expired_consents( $retention_days );
        
        // Log the cleanup (optional)
        if ( $deleted > 0 ) {
            error_log( "GDPR Cookie Consent: Cleaned up $deleted expired consents (older than $retention_days days)" );
        }

        do_action( 'rubik_gdpr_expired_consents_cleaned', $deleted, $retention_days );
    }

    public function migrate_cron_hooks() {
        if ( wp_next_scheduled( 'rubik_gdpr_cleanup_expired_consents' ) ) {
            $this->unschedule_cleanup_hooks();
            return;
        }

        if ( $this->unschedule_cleanup_hooks() ) {
            wp_schedule_event( time(), 'daily', 'rubik_gdpr_cleanup_expired_consents' );
        }
    }

    private function unschedule_cleanup_hooks() {
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

                if ( $hook === 'rubik_gdpr_cleanup_expired_consents' ) {
                    continue;
                }

                if ( ! is_array( $events ) ) {
                    continue;
                }

                foreach ( $events as $sig => $event ) {
                    wp_unschedule_event( (int) $timestamp, $hook, isset( $event['args'] ) ? (array) $event['args'] : array() );
                    $unscheduled_any = true;
                }
            }
        }

        return $unscheduled_any;
    }

}
