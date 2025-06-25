<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for enqueueing the admin-specific stylesheet and JavaScript.
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Manus AI
 */
class Manus_GDPR_Admin {

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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/css/manus-gdpr-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/js/manus-gdpr-admin.js', array( 'jquery' ), $this->version, false );

    }

    /**
     * Add the top-level menu page.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        add_menu_page(
            __( 'Manus GDPR', 'manus-gdpr' ),
            __( 'Manus GDPR', 'manus-gdpr' ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_plugin_setup_page' ),
            'dashicons-shield-alt',
            26
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Cookie Scanner', 'manus-gdpr' ),
            __( 'Cookie Scanner', 'manus-gdpr' ),
            'manage_options',
            $this->plugin_name . '-scanner',
            array( $this, 'display_cookie_scanner_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Consent Log', 'manus-gdpr' ),
            __( 'Consent Log', 'manus-gdpr' ),
            'manage_options',
            $this->plugin_name . '-consent-log',
            array( $this, 'display_consent_log_page' )
        );

    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_setup_page() {
        require_once MANUS_GDPR_PATH . 'admin/partials/manus-gdpr-admin-display.php';
    }

    /**
     * Render the cookie scanner page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_cookie_scanner_page() {
        require_once MANUS_GDPR_PATH . 'admin/partials/manus-gdpr-cookie-scanner-display.php';
    }

    /**
     * Render the consent log page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_consent_log_page() {
        // Handle test data insertion (solo per sviluppo)
        if ( isset( $_GET['insert_test_data'] ) && $_GET['insert_test_data'] === '1' && current_user_can( 'manage_options' ) ) {
            if ( Manus_GDPR_Database::insert_sample_consent_data() ) {
                add_action( 'admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Dati di test inseriti con successo!', 'manus-gdpr' ) . '</p></div>';
                } );
            }
        }
        
        // Handle pagination
        $current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $per_page = 20;
        
        // Handle filters
        $filter_status = isset( $_GET['filter_status'] ) ? sanitize_text_field( $_GET['filter_status'] ) : '';
        $search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        
        // Get consent records
        $consent_data = Manus_GDPR_Database::get_consent_records( $per_page, $current_page, $filter_status, $search_term );
        $consent_records = $consent_data['records'];
        $total_items = $consent_data['total_items'];
        $total_pages = $consent_data['total_pages'];
        
        // Get statistics
        $stats = Manus_GDPR_Database::get_consent_statistics();
        
        require_once MANUS_GDPR_PATH . 'admin/partials/manus-gdpr-consent-log-display.php';
    }

    /**
     * Get user display name by user ID.
     *
     * @since    1.0.0
     * @param    int    $user_id User ID.
     * @return   string User display name or 'Guest'.
     */
    public function get_user_display_name( $user_id ) {
        if ( empty( $user_id ) ) {
            return __( 'Ospite', 'manus-gdpr' );
        }
        
        $user = get_user_by( 'id', $user_id );
        if ( $user ) {
            return $user->display_name;
        }
        
        return __( 'Utente sconosciuto', 'manus-gdpr' );
    }

    /**
     * Format consent data for display.
     *
     * @since    1.0.0
     * @param    string $consent_data Serialized consent data.
     * @return   string Formatted consent data.
     */
    public function format_consent_data( $consent_data ) {
        $data = unserialize( $consent_data );
        
        if ( ! is_array( $data ) ) {
            return __( 'Dati non validi', 'manus-gdpr' );
        }
        
        $formatted = array();
        foreach ( $data as $category => $status ) {
            $status_text = $status ? __( 'Accettato', 'manus-gdpr' ) : __( 'Rifiutato', 'manus-gdpr' );
            $formatted[] = ucfirst( $category ) . ': ' . $status_text;
        }
        
        return implode( '<br>', $formatted );
    }

    /**
     * Get consent status badge HTML.
     *
     * @since    1.0.0
     * @param    string $status Consent status.
     * @return   string HTML badge.
     */
    public function get_consent_status_badge( $status ) {
        $badges = array(
            'accepted' => '<span class="consent-badge consent-accepted">' . __( 'Accettato', 'manus-gdpr' ) . '</span>',
            'rejected' => '<span class="consent-badge consent-rejected">' . __( 'Rifiutato', 'manus-gdpr' ) . '</span>',
            'partial'  => '<span class="consent-badge consent-partial">' . __( 'Parziale', 'manus-gdpr' ) . '</span>',
        );
        
        return isset( $badges[$status] ) ? $badges[$status] : '<span class="consent-badge">' . esc_html( $status ) . '</span>';
    }

}