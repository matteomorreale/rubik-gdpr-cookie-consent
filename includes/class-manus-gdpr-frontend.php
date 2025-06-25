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

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../public/css/manus-gdpr-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../public/js/manus-gdpr-public.js', array( 'jquery' ), $this->version, false );

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
            $existing_privacy_page = get_page_by_title( $privacy_page_title );
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
            include_once MANUS_GDPR_PATH . 'public/partials/manus-gdpr-public-display.php';
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
                echo '<!-- Script blocked by Manus GDPR: ' . esc_html( $script ) . ' -->';
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

        // Set cookie for consent
        setcookie( 'manus_gdpr_consent', $consent_status, time() + YEAR_IN_SECONDS, '/' );

        wp_send_json_success( 'Consent recorded successfully.' );
    }

}