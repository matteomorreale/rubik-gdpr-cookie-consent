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
        
        // Hook per AJAX cancellazione consensi
        add_action( 'wp_ajax_manus_gdpr_clear_consents', array( $this, 'handle_clear_consents_ajax' ) );

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/css/manus-gdpr-admin.css', array(), MANUS_GDPR_VERSION . '.4', 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../admin/js/manus-gdpr-admin.js', array( 'jquery' ), MANUS_GDPR_VERSION . '.4', false );
        
        // Localizzazione per AJAX
        wp_localize_script( $this->plugin_name, 'manus_gdpr_admin_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'manus_gdpr_clear_consents' )
        ) );

    }

    /**
     * Add the top-level menu page.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {

        add_menu_page(
            __( 'Rubik GDPR', 'manus-gdpr' ),
            __( 'Rubik GDPR', 'manus-gdpr' ),
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
            __( 'Impostazioni', 'manus-gdpr' ),
            __( 'Impostazioni', 'manus-gdpr' ),
            'manage_options',
            $this->plugin_name . '-settings',
            array( $this, 'display_settings_page' )
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
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e( 'Impostazioni GDPR Cookie Consent', 'manus-gdpr' ); ?></h1>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'manus_gdpr_settings_group' );
                do_settings_sections( 'manus-gdpr' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
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

    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register settings for the plugin
        register_setting(
            'manus_gdpr_settings_group',
            'manus_gdpr_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'default' => array()
            )
        );

        // Add settings section
        add_settings_section(
            'manus_gdpr_general_section',
            __( 'Impostazioni Generali', 'manus-gdpr' ),
            array( $this, 'general_section_callback' ),
            'manus-gdpr'
        );

        // Cookie banner settings
        add_settings_field(
            'cookie_banner_enabled',
            __( 'Abilita banner cookie', 'manus-gdpr' ),
            array( $this, 'cookie_banner_enabled_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'cookie_banner_message',
            __( 'Messaggio del banner', 'manus-gdpr' ),
            array( $this, 'cookie_banner_message_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'cookie_banner_position',
            __( 'Posizione del banner', 'manus-gdpr' ),
            array( $this, 'cookie_banner_position_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        // Cookie categories settings
        add_settings_field(
            'cookie_categories',
            __( 'Categorie di cookie', 'manus-gdpr' ),
            array( $this, 'cookie_categories_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        // Compliance settings
        add_settings_field(
            'strict_mode',
            __( 'Modalità rigorosa', 'manus-gdpr' ),
            array( $this, 'strict_mode_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'auto_block_unknown',
            __( 'Blocca cookie sconosciuti automaticamente', 'manus-gdpr' ),
            array( $this, 'auto_block_unknown_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        // Banner appearance settings
        add_settings_field(
            'banner_background_color',
            __( 'Colore di sfondo del banner', 'manus-gdpr' ),
            array( $this, 'banner_background_color_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'banner_text_color',
            __( 'Colore del testo', 'manus-gdpr' ),
            array( $this, 'banner_text_color_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'banner_button_color',
            __( 'Colore dei pulsanti', 'manus-gdpr' ),
            array( $this, 'banner_button_color_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'show_banner_overlay',
            __( 'Mostra overlay di sfondo', 'manus-gdpr' ),
            array( $this, 'show_banner_overlay_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'banner_overlay_color',
            __( 'Colore overlay di sfondo', 'manus-gdpr' ),
            array( $this, 'banner_overlay_color_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'theme_mode',
            __( 'Tema banner e modale', 'manus-gdpr' ),
            array( $this, 'theme_mode_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'layout_mode',
            __( 'Layout banner', 'manus-gdpr' ),
            array( $this, 'layout_mode_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        // Custom CSS settings
        add_settings_field(
            'custom_css',
            __( 'CSS Personalizzato', 'manus-gdpr' ),
            array( $this, 'custom_css_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        // IAB TCF v2.2 settings
        add_settings_field(
            'enable_tcf_v2',
            __( 'Abilita IAB TCF v2.2', 'manus-gdpr' ),
            array( $this, 'enable_tcf_v2_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        // Consent management settings
        add_settings_field(
            'consent_retention_period',
            __( 'Periodo di conservazione consensi', 'manus-gdpr' ),
            array( $this, 'consent_retention_period_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'delete_data_on_uninstall',
            __( 'Cancella dati alla disinstallazione', 'manus-gdpr' ),
            array( $this, 'delete_data_on_uninstall_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );

        add_settings_field(
            'clear_all_consents',
            __( 'Gestione consensi', 'manus-gdpr' ),
            array( $this, 'clear_all_consents_callback' ),
            'manus-gdpr',
            'manus_gdpr_general_section'
        );
    }

    /**
     * Sanitize settings before saving.
     *
     * @since    1.0.0
     * @param    array $input Settings input.
     * @return   array Sanitized settings.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        if ( isset( $input['cookie_banner_enabled'] ) ) {
            $sanitized['cookie_banner_enabled'] = (bool) $input['cookie_banner_enabled'];
        }

        if ( isset( $input['cookie_banner_message'] ) ) {
            $sanitized['cookie_banner_message'] = sanitize_textarea_field( $input['cookie_banner_message'] );
        }

        if ( isset( $input['cookie_banner_position'] ) ) {
            $sanitized['cookie_banner_position'] = sanitize_text_field( $input['cookie_banner_position'] );
        }

        if ( isset( $input['cookie_categories'] ) && is_array( $input['cookie_categories'] ) ) {
            $sanitized['cookie_categories'] = array();
            foreach ( $input['cookie_categories'] as $category => $enabled ) {
                $sanitized['cookie_categories'][sanitize_key( $category )] = (bool) $enabled;
            }
        }

        if ( isset( $input['strict_mode'] ) ) {
            $sanitized['strict_mode'] = (bool) $input['strict_mode'];
        }

        if ( isset( $input['auto_block_unknown'] ) ) {
            $sanitized['auto_block_unknown'] = (bool) $input['auto_block_unknown'];
        }

        if ( isset( $input['banner_background_color'] ) ) {
            $sanitized['banner_background_color'] = sanitize_hex_color( $input['banner_background_color'] );
        }

        if ( isset( $input['banner_text_color'] ) ) {
            $sanitized['banner_text_color'] = sanitize_hex_color( $input['banner_text_color'] );
        }

        if ( isset( $input['banner_button_color'] ) ) {
            $sanitized['banner_button_color'] = sanitize_hex_color( $input['banner_button_color'] );
        }

        if ( isset( $input['show_banner_overlay'] ) ) {
            $sanitized['show_banner_overlay'] = (bool) $input['show_banner_overlay'];
        }

        if ( isset( $input['banner_overlay_color'] ) ) {
            // Sanitize CSS color value (supports rgba, hex, etc.)
            $overlay_color = sanitize_text_field( $input['banner_overlay_color'] );
            // Basic validation for CSS color formats
            if ( preg_match('/^(#[0-9a-fA-F]{3,6}|rgba?\([^)]+\)|[a-zA-Z]+)$/', $overlay_color) ) {
                $sanitized['banner_overlay_color'] = $overlay_color;
            }
        }

        if ( isset( $input['theme_mode'] ) ) {
            $sanitized['theme_mode'] = sanitize_text_field( $input['theme_mode'] );
        }

        if ( isset( $input['layout_mode'] ) ) {
            $sanitized['layout_mode'] = sanitize_text_field( $input['layout_mode'] );
        }

        if ( isset( $input['show_manage_preferences'] ) ) {
            $sanitized['show_manage_preferences'] = (bool) $input['show_manage_preferences'];
        }

        if ( isset( $input['custom_css'] ) ) {
            // Sanitize CSS while preserving valid CSS syntax
            $sanitized['custom_css'] = wp_strip_all_tags( $input['custom_css'] );
        }

        if ( isset( $input['enable_tcf_v2'] ) ) {
            $sanitized['enable_tcf_v2'] = (bool) $input['enable_tcf_v2'];
        }

        // Sanitize consent management settings
        if ( isset( $input['consent_retention_period'] ) ) {
            $valid_periods = array( '30', '180', '365', '730', '1825', '3650' ); // days
            $retention_period = sanitize_text_field( $input['consent_retention_period'] );
            if ( in_array( $retention_period, $valid_periods ) ) {
                $sanitized['consent_retention_period'] = $retention_period;
            } else {
                $sanitized['consent_retention_period'] = '365'; // Default to 1 year
            }
        }

        if ( isset( $input['delete_data_on_uninstall'] ) ) {
            $sanitized['delete_data_on_uninstall'] = (bool) $input['delete_data_on_uninstall'];
        }

        return $sanitized;
    }

    /**
     * General settings section callback.
     *
     * @since    1.0.0
     */
    public function general_section_callback() {
        echo '<p>' . __( 'Configura le impostazioni generali per il consenso ai cookie GDPR.', 'manus-gdpr' ) . '</p>';
    }

    /**
     * Cookie banner enabled field callback.
     *
     * @since    1.0.0
     */
    public function cookie_banner_enabled_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $enabled = isset( $options['cookie_banner_enabled'] ) ? $options['cookie_banner_enabled'] : true;
        
        echo '<input type="checkbox" id="cookie_banner_enabled" name="manus_gdpr_settings[cookie_banner_enabled]" value="1" ' . checked( 1, $enabled, false ) . ' />';
        echo '<label for="cookie_banner_enabled">' . __( 'Mostra il banner di consenso ai cookie', 'manus-gdpr' ) . '</label>';
    }

    /**
     * Cookie banner message field callback.
     *
     * @since    1.0.0
     */
    public function cookie_banner_message_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $message = isset( $options['cookie_banner_message'] ) ? $options['cookie_banner_message'] : 
            __( 'Questo sito utilizza cookie per migliorare la tua esperienza. Continuando a navigare accetti il loro utilizzo.', 'manus-gdpr' );
        
        echo '<textarea id="cookie_banner_message" name="manus_gdpr_settings[cookie_banner_message]" rows="3" cols="50" class="large-text">' . esc_textarea( $message ) . '</textarea>';
        echo '<p class="description">' . __( 'Messaggio mostrato nel banner di consenso ai cookie.', 'manus-gdpr' ) . '</p>';
    }

    /**
     * Cookie banner position field callback.
     *
     * @since    1.0.0
     */
    public function cookie_banner_position_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $position = isset( $options['cookie_banner_position'] ) ? $options['cookie_banner_position'] : 'bottom';
        
        $positions = array(
            'top' => __( 'In alto', 'manus-gdpr' ),
            'bottom' => __( 'In basso', 'manus-gdpr' ),
            'center' => __( 'Centro (popup)', 'manus-gdpr' )
        );
        
        echo '<select id="cookie_banner_position" name="manus_gdpr_settings[cookie_banner_position]">';
        foreach ( $positions as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $position, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __( 'Posizione del banner sulla pagina.', 'manus-gdpr' ) . '</p>';
    }

    /**
     * Cookie categories field callback.
     *
     * @since    1.0.0
     */
    public function cookie_categories_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $categories = isset( $options['cookie_categories'] ) ? $options['cookie_categories'] : array();
        
        $available_categories = array(
            'necessary' => __( 'Cookie necessari', 'manus-gdpr' ),
            'analytics' => __( 'Cookie analitici', 'manus-gdpr' ),
            'advertising' => __( 'Cookie pubblicitari', 'manus-gdpr' ),
            'functional' => __( 'Cookie funzionali', 'manus-gdpr' )
        );
        
        echo '<fieldset>';
        foreach ( $available_categories as $category => $label ) {
            $enabled = isset( $categories[$category] ) ? $categories[$category] : ( $category === 'necessary' );
            $disabled = ( $category === 'necessary' ) ? 'disabled' : '';
            
            echo '<label>';
            echo '<input type="checkbox" name="manus_gdpr_settings[cookie_categories][' . esc_attr( $category ) . ']" value="1" ' . checked( 1, $enabled, false ) . ' ' . $disabled . ' />';
            echo ' ' . esc_html( $label );
            if ( $category === 'necessary' ) {
                echo ' <em>(' . __( 'sempre abilitati', 'manus-gdpr' ) . ')</em>';
            }
            echo '</label><br>';
        }
        echo '</fieldset>';
    }

    /**
     * Strict mode field callback.
     *
     * @since    1.0.0
     */
    public function strict_mode_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $strict_mode = isset( $options['strict_mode'] ) ? $options['strict_mode'] : false;
        
        echo '<input type="checkbox" id="strict_mode" name="manus_gdpr_settings[strict_mode]" value="1" ' . checked( 1, $strict_mode, false ) . ' />';
        echo '<label for="strict_mode">' . __( 'Blocca tutti i cookie non necessari fino al consenso esplicito', 'manus-gdpr' ) . '</label>';
    }

    /**
     * Auto block unknown cookies field callback.
     *
     * @since    1.0.0
     */
    public function auto_block_unknown_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $auto_block = isset( $options['auto_block_unknown'] ) ? $options['auto_block_unknown'] : true;
        
        echo '<input type="checkbox" id="auto_block_unknown" name="manus_gdpr_settings[auto_block_unknown]" value="1" ' . checked( 1, $auto_block, false ) . ' />';
        echo '<label for="auto_block_unknown">' . __( 'Blocca automaticamente i cookie non categorizzati', 'manus-gdpr' ) . '</label>';
    }

    /**
     * Banner background color field callback.
     *
     * @since    1.0.0
     */
    public function banner_background_color_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $color = isset( $options['banner_background_color'] ) ? $options['banner_background_color'] : '#333333';
        
        echo '<input type="color" id="banner_background_color" name="manus_gdpr_settings[banner_background_color]" value="' . esc_attr( $color ) . '" />';
        echo '<p class="description">' . __( 'Colore di sfondo del banner dei cookie.', 'manus-gdpr' ) . '</p>';
    }

    /**
     * Banner text color field callback.
     *
     * @since    1.0.0
     */
    public function banner_text_color_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $color = isset( $options['banner_text_color'] ) ? $options['banner_text_color'] : '#ffffff';
        
        echo '<input type="color" id="banner_text_color" name="manus_gdpr_settings[banner_text_color]" value="' . esc_attr( $color ) . '" />';
        echo '<p class="description">' . __( 'Colore del testo nel banner dei cookie.', 'manus-gdpr' ) . '</p>';
    }

    /**
     * Banner button color field callback.
     *
     * @since    1.0.0
     */
    public function banner_button_color_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $color = isset( $options['banner_button_color'] ) ? $options['banner_button_color'] : '#0073aa';
        
        echo '<input type="color" id="banner_button_color" name="manus_gdpr_settings[banner_button_color]" value="' . esc_attr( $color ) . '" />';
        echo '<p class="description">' . __( 'Colore dei pulsanti nel banner dei cookie.', 'manus-gdpr' ) . '</p>';
    }

    /**
     * Show banner overlay field callback.
     *
     * @since    1.0.0
     */
    public function show_banner_overlay_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $show_overlay = isset( $options['show_banner_overlay'] ) ? $options['show_banner_overlay'] : true;
        
        echo '<input type="checkbox" id="show_banner_overlay" name="manus_gdpr_settings[show_banner_overlay]" value="1" ' . checked( 1, $show_overlay, false ) . ' />';
        echo '<label for="show_banner_overlay">' . __( 'Mostra uno sfondo scuro dietro il banner per dare maggiore evidenza', 'manus-gdpr' ) . '</label>';
        echo '<p class="description">' . __( 'Consigliato: aiuta l\'utente a concentrarsi sul banner cookie e migliora la conformità GDPR.', 'manus-gdpr' ) . '</p>';
    }

    /**
     * Banner overlay color field callback.
     *
     * @since    1.0.0
     */
    public function banner_overlay_color_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $overlay_color = isset( $options['banner_overlay_color'] ) ? $options['banner_overlay_color'] : 'rgba(47, 79, 79, 0.6)';
        
        echo '<input type="text" id="banner_overlay_color" name="manus_gdpr_settings[banner_overlay_color]" value="' . esc_attr( $overlay_color ) . '" class="regular-text" placeholder="rgba(47, 79, 79, 0.6)" />';
        echo '<p class="description">' . __( 'Colore e opacità dell\'overlay di sfondo. Esempi: "rgba(0,0,0,0.5)" (nero 50%), "#333333" (grigio scuro), "rgba(47,79,79,0.6)" (antracite 60%).', 'manus-gdpr' ) . '</p>';
        
        // Color presets
        echo '<div style="margin-top: 10px;">';
        echo '<strong>' . __( 'Preset colori:', 'manus-gdpr' ) . '</strong><br>';
        echo '<button type="button" class="button-secondary overlay-color-preset" data-color="rgba(0,0,0,0.5)" style="margin: 2px; background: rgba(0,0,0,0.5); color: white; border: 1px solid #ccc;">' . __( 'Nero 50%', 'manus-gdpr' ) . '</button>';
        echo '<button type="button" class="button-secondary overlay-color-preset" data-color="rgba(47,79,79,0.6)" style="margin: 2px; background: rgba(47,79,79,0.6); color: white; border: 1px solid #ccc;">' . __( 'Antracite 60%', 'manus-gdpr' ) . '</button>';
        echo '<button type="button" class="button-secondary overlay-color-preset" data-color="rgba(25,25,112,0.4)" style="margin: 2px; background: rgba(25,25,112,0.4); color: white; border: 1px solid #ccc;">' . __( 'Blu scuro 40%', 'manus-gdpr' ) . '</button>';
        echo '<button type="button" class="button-secondary overlay-color-preset" data-color="rgba(139,69,19,0.5)" style="margin: 2px; background: rgba(139,69,19,0.5); color: white; border: 1px solid #ccc;">' . __( 'Marrone 50%', 'manus-gdpr' ) . '</button>';
        echo '</div>';
        
        // JavaScript for color presets
        echo '<script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            const presetButtons = document.querySelectorAll(".overlay-color-preset");
            const colorInput = document.getElementById("banner_overlay_color");
            
            presetButtons.forEach(function(button) {
                button.addEventListener("click", function() {
                    colorInput.value = this.getAttribute("data-color");
                });
            });
        });
        </script>';
    }

    /**
     * Theme mode field callback.
     *
     * @since    1.0.3
     */
    public function theme_mode_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $theme_mode = isset( $options['theme_mode'] ) ? $options['theme_mode'] : 'light';
        
        echo '<select id="theme_mode" name="manus_gdpr_settings[theme_mode]" class="regular-text">';
        echo '<option value="light"' . selected( $theme_mode, 'light', false ) . '>' . __( 'Chiaro', 'manus-gdpr' ) . '</option>';
        echo '<option value="dark"' . selected( $theme_mode, 'dark', false ) . '>' . __( 'Scuro', 'manus-gdpr' ) . '</option>';
        echo '<option value="auto"' . selected( $theme_mode, 'auto', false ) . '>' . __( 'Automatico (rispetta le preferenze del sistema)', 'manus-gdpr' ) . '</option>';
        echo '</select>';
        echo '<p class="description">' . __( 'Scegli il tema per il banner e la modale delle preferenze cookie.', 'manus-gdpr' ) . '</p>';
    }

    /**
     * Layout mode field callback.
     *
     * @since    1.0.3
     */
    public function layout_mode_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $layout_mode = isset( $options['layout_mode'] ) ? $options['layout_mode'] : 'card';
        
        echo '<select id="layout_mode" name="manus_gdpr_settings[layout_mode]" class="regular-text">';
        echo '<option value="card"' . selected( $layout_mode, 'card', false ) . '>' . __( 'Card (centrato con margini)', 'manus-gdpr' ) . '</option>';
        echo '<option value="fullwidth"' . selected( $layout_mode, 'fullwidth', false ) . '>' . __( 'Larghezza completa', 'manus-gdpr' ) . '</option>';
        echo '</select>';
        echo '<p class="description">' . __( 'Scegli il layout del banner cookie. Il layout card è consigliato per la maggior parte dei siti.', 'manus-gdpr' ) . '</p>';
        
        echo '<div style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa;">';
        echo '<strong>' . __( 'Anteprima layout:', 'manus-gdpr' ) . '</strong><br>';
        echo '<strong>Card:</strong> ' . __( 'Banner centrato con margini e bordi arrotondati - aspetto moderno e elegante', 'manus-gdpr' ) . '<br>';
        echo '<strong>Fullwidth:</strong> ' . __( 'Banner a tutta larghezza senza margini laterali - ideale per una presenza più prominente', 'manus-gdpr' );
        echo '</div>';
    }

    /**
     * Custom CSS field callback.
     *
     * @since    1.0.0
     */
    public function custom_css_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $custom_css = isset( $options['custom_css'] ) ? $options['custom_css'] : '';
        
        // CSS placeholder example
        $css_example = '/* Esempio: Personalizza il banner dei cookie */
.manus-gdpr-banner {
    border-radius: 12px !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
}

.manus-gdpr-banner .manus-gdpr-message {
    font-size: 16px !important;
    line-height: 1.6 !important;
}

.manus-gdpr-button {
    border-radius: 25px !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.manus-gdpr-accept:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
}

/* Esempio: Personalizza il modal delle preferenze */
.manus-gdpr-modal {
    border-radius: 16px !important;
    max-width: 600px !important;
}

.manus-gdpr-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    padding: 20px !important;
}

.cookie-toggle .toggle-slider {
    background: #2271b1 !important;
}';
        
        echo '<div class="manus-gdpr-css-editor-wrapper">';
        echo '<textarea id="custom_css" name="manus_gdpr_settings[custom_css]" rows="15" cols="50" class="large-text code" spellcheck="false" placeholder="' . esc_attr( $css_example ) . '">' . esc_textarea( $custom_css ) . '</textarea>';
        
        echo '<div class="manus-gdpr-css-help" style="margin-top: 15px;">';
        echo '<h4>' . __( 'Classi CSS principali per la personalizzazione:', 'manus-gdpr' ) . '</h4>';
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 10px 0;">';
        
        // Banner dei Cookie
        echo '<div class="css-help-section">';
        echo '<h5 style="margin: 0 0 8px 0; color: #2271b1;">🍪 Banner dei Cookie</h5>';
        echo '<div style="font-family: monospace; font-size: 12px; background: #f6f7f7; padding: 8px; border-radius: 4px;">';
        echo '<div><strong>.manus-gdpr-banner</strong> - Container principale</div>';
        echo '<div><strong>.manus-gdpr-banner.position-top</strong> - Banner in alto</div>';
        echo '<div><strong>.manus-gdpr-banner.position-bottom</strong> - Banner in basso</div>';
        echo '<div><strong>.manus-gdpr-banner.position-center</strong> - Banner centro</div>';
        echo '<div><strong>.manus-gdpr-message</strong> - Testo del messaggio</div>';
        echo '<div><strong>.manus-gdpr-buttons</strong> - Container pulsanti</div>';
        echo '<div><strong>.manus-gdpr-accept</strong> - Pulsante accetta</div>';
        echo '<div><strong>.manus-gdpr-reject</strong> - Pulsante rifiuta</div>';
        echo '<div><strong>.manus-gdpr-preferences</strong> - Pulsante preferenze</div>';
        echo '</div>';
        echo '</div>';
        
        // Modal delle Preferenze
        echo '<div class="css-help-section">';
        echo '<h5 style="margin: 0 0 8px 0; color: #2271b1;">⚙️ Modal Preferenze</h5>';
        echo '<div style="font-family: monospace; font-size: 12px; background: #f6f7f7; padding: 8px; border-radius: 4px;">';
        echo '<div><strong>.manus-gdpr-modal-overlay</strong> - Sfondo scuro</div>';
        echo '<div><strong>.manus-gdpr-modal</strong> - Container modal</div>';
        echo '<div><strong>.manus-gdpr-modal-header</strong> - Intestazione modal</div>';
        echo '<div><strong>.manus-gdpr-modal-body</strong> - Contenuto modal</div>';
        echo '<div><strong>.manus-gdpr-modal-footer</strong> - Footer modal</div>';
        echo '<div><strong>.manus-gdpr-close</strong> - Pulsante chiudi (×)</div>';
        echo '<div><strong>.cookie-category</strong> - Sezione categoria</div>';
        echo '<div><strong>.cookie-toggle</strong> - Switch toggle</div>';
        echo '<div><strong>.toggle-slider</strong> - Slider del toggle</div>';
        echo '</div>';
        echo '</div>';
        
        // Elementi generali
        echo '<div class="css-help-section">';
        echo '<h5 style="margin: 0 0 8px 0; color: #2271b1;">🎨 Elementi Generali</h5>';
        echo '<div style="font-family: monospace; font-size: 12px; background: #f6f7f7; padding: 8px; border-radius: 4px;">';
        echo '<div><strong>.manus-gdpr-button</strong> - Stile base pulsanti</div>';
        echo '<div><strong>.manus-gdpr-button.primary</strong> - Pulsante primario</div>';
        echo '<div><strong>.manus-gdpr-button.secondary</strong> - Pulsante secondario</div>';
        echo '<div><strong>.manus-gdpr-text</strong> - Testo generale</div>';
        echo '<div><strong>.manus-gdpr-title</strong> - Titoli</div>';
        echo '<div><strong>.manus-gdpr-description</strong> - Descrizioni</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Quick templates
        echo '<div style="margin-top: 15px;">';
        echo '<h4>' . __( 'Template Rapidi:', 'manus-gdpr' ) . '</h4>';
        echo '<div style="display: flex; gap: 10px; flex-wrap: wrap; margin: 10px 0;">';
        echo '<button type="button" class="button" onclick="insertCSSTemplate(\'rounded\')">' . __( 'Stile Arrotondato', 'manus-gdpr' ) . '</button>';
        echo '<button type="button" class="button" onclick="insertCSSTemplate(\'shadow\')">' . __( 'Con Ombra', 'manus-gdpr' ) . '</button>';
        echo '<button type="button" class="button" onclick="insertCSSTemplate(\'gradient\')">' . __( 'Gradiente', 'manus-gdpr' ) . '</button>';
        echo '<button type="button" class="button" onclick="insertCSSTemplate(\'minimal\')">' . __( 'Minimalista', 'manus-gdpr' ) . '</button>';
        echo '</div>';
        echo '</div>';
        
        echo '<div style="margin-top: 15px; padding: 12px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">';
        echo '<strong>💡 Suggerimenti:</strong><br>';
        echo '• Usa <code>!important</code> solo se necessario per sovrascrivere gli stili esistenti<br>';
        echo '• Testa sempre i cambiamenti su diversi dispositivi e browser<br>';
        echo '• Mantieni l\'accessibilità usando colori con buon contrasto<br>';
        echo '• Le modifiche CSS vengono applicate automaticamente al frontend<br>';
        echo '• Usa i template rapidi come punto di partenza per le tue personalizzazioni';
        echo '</div>';
        echo '</div>';
        
        echo '<p class="description">' . __( 'Aggiungi il tuo CSS personalizzato per modificare l\'aspetto del banner dei cookie e del modal delle preferenze. Il CSS verrà applicato automaticamente nel frontend.', 'manus-gdpr' ) . '</p>';
        echo '</div>';
        
        // JavaScript for template insertion
        echo '<script type="text/javascript">
        function insertCSSTemplate(template) {
            const textarea = document.getElementById("custom_css");
            let css = "";
            
            switch(template) {
                case "rounded":
                    css = `
/* Template: Stile Arrotondato */
.manus-gdpr-banner {
    border-radius: 20px !important;
    padding: 25px !important;
}

.manus-gdpr-button {
    border-radius: 25px !important;
    padding: 10px 20px !important;
}

.manus-gdpr-modal {
    border-radius: 20px !important;
}
`;
                    break;
                case "shadow":
                    css = `
/* Template: Con Ombra */
.manus-gdpr-banner {
    box-shadow: 0 8px 32px rgba(0,0,0,0.15) !important;
    border: none !important;
}

.manus-gdpr-button {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    transition: all 0.3s ease !important;
}

.manus-gdpr-button:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.25) !important;
    transform: translateY(-2px) !important;
}
`;
                    break;
                case "gradient":
                    css = `
/* Template: Gradiente */
.manus-gdpr-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
}

.manus-gdpr-accept {
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%) !important;
    color: #333 !important;
}

.manus-gdpr-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
}
`;
                    break;
                case "minimal":
                    css = `
/* Template: Minimalista */
.manus-gdpr-banner {
    background: transparent !important;
    border: 2px solid #333 !important;
    color: #333 !important;
}

.manus-gdpr-button {
    background: transparent !important;
    border: 1px solid #333 !important;
    color: #333 !important;
    padding: 8px 16px !important;
}

.manus-gdpr-accept {
    background: #333 !important;
    color: white !important;
}
`;
                    break;
            }
            
            if (textarea.value) {
                textarea.value += "\\n\\n" + css;
            } else {
                textarea.value = css;
            }
            
            textarea.focus();
        }
        </script>';
    }

    /**
     * Enable TCF v2.2 field callback.
     *
     * @since    1.0.0
     */
    public function enable_tcf_v2_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $enabled = isset( $options['enable_tcf_v2'] ) ? $options['enable_tcf_v2'] : true; // Default enabled
        
        echo '<input type="checkbox" id="enable_tcf_v2" name="manus_gdpr_settings[enable_tcf_v2]" value="1" ' . checked( 1, $enabled, false ) . ' />';
        echo '<label for="enable_tcf_v2">' . __( 'Abilita supporto IAB Transparency & Consent Framework v2.2', 'manus-gdpr' ) . '</label>';
        echo '<p class="description">' . __( 'Attiva il supporto per lo standard IAB TCF v2.2 per la gestione del consenso pubblicitario. Raccomandato per siti con pubblicità programmatica.', 'manus-gdpr' ) . '</p>';
        
        // Test status display
        echo '<div id="tcf-test-status" style="margin-top: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">';
        echo '<h4 style="margin: 0 0 8px 0;">' . __( 'Test API TCF:', 'manus-gdpr' ) . '</h4>';
        echo '<p id="tcf-api-status" style="margin: 0; color: #666;"><em>' . __( 'Clicca su "Test API" per verificare il supporto TCF...', 'manus-gdpr' ) . '</em></p>';
        echo '<button type="button" id="test-tcf-api" class="button" style="margin-top: 8px;">' . __( 'Test API TCF', 'manus-gdpr' ) . '</button>';
        echo '</div>';
        
        // JavaScript for testing
        echo '<script type="text/javascript">
        document.getElementById("test-tcf-api").addEventListener("click", function() {
            const statusEl = document.getElementById("tcf-api-status");
            const button = this;
            
            button.disabled = true;
            button.textContent = "Test in corso...";
            statusEl.innerHTML = "<em>Verifico il supporto TCF nel frontend...</em>";
            
            // Test via iframe del frontend
            const testFrame = document.createElement("iframe");
            testFrame.style.cssText = "position: absolute; top: -9999px; left: -9999px; width: 1px; height: 1px; border: none;";
            testFrame.src = "' . home_url() . '?tcf_test=1&nocache=" + Date.now();
            
            document.body.appendChild(testFrame);
            
            let timeoutId = setTimeout(function() {
                updateTestResult(false, ["⚠ Timeout - Il test ha impiegato troppo tempo", "Verifica che il sito frontend sia accessibile"]);
            }, 10000);
            
            testFrame.onload = function() {
                try {
                    clearTimeout(timeoutId);
                    
                    const frameWindow = testFrame.contentWindow;
                    let tcfAvailable = false;
                    let tcfDetails = [];
                    
                    // Aspetta un momento per permettere agli script di caricarsi
                    setTimeout(function() {
                        try {
                            if (typeof frameWindow.__tcfapi === "function") {
                                tcfAvailable = true;
                                tcfDetails.push("✓ __tcfapi trovata nel frontend");
                                
                                // Test ping
                                frameWindow.__tcfapi("ping", 2, function(pingReturn, success) {
                                    if (success && pingReturn) {
                                        tcfDetails.push("✓ Ping TCF riuscito");
                                        tcfDetails.push("✓ CMP ID: " + pingReturn.cmpId);
                                        tcfDetails.push("✓ GDPR Applies: " + (pingReturn.gdprApplies ? "Sì" : "No"));
                                        tcfDetails.push("✓ Status: " + pingReturn.cmpStatus);
                                        
                                        // Test getTCData
                                        frameWindow.__tcfapi("getTCData", 2, function(tcData, tcSuccess) {
                                            if (tcSuccess && tcData) {
                                                tcfDetails.push("✓ getTCData funziona");
                                                tcfDetails.push("✓ TC String: " + (tcData.tcString ? "Presente" : "Assente"));
                                                tcfDetails.push("✓ Event Status: " + tcData.eventStatus);
                                                
                                                // Conta purpose consents
                                                const purposeCount = Object.keys(tcData.purpose.consents || {}).length;
                                                tcfDetails.push("✓ Purpose Consents: " + purposeCount + " configurati");
                                            } else {
                                                tcfDetails.push("⚠ getTCData non ha restituito dati validi");
                                            }
                                            
                                            updateTestResult(true, tcfDetails);
                                        });
                                    } else {
                                        tcfDetails.push("✗ Ping TCF fallito");
                                        updateTestResult(false, tcfDetails);
                                    }
                                });
                            } else {
                                tcfDetails.push("✗ __tcfapi non trovata nel frontend");
                                tcfDetails.push("ℹ Possibili cause:");
                                tcfDetails.push("  - TCF v2.2 non abilitato nelle impostazioni");
                                tcfDetails.push("  - JavaScript bloccato o errori nel frontend");
                                tcfDetails.push("  - Cache del browser/plugin attiva");
                                updateTestResult(false, tcfDetails);
                            }
                        } catch (e) {
                            tcfDetails.push("✗ Errore accesso iframe: " + e.message);
                            tcfDetails.push("ℹ Possibile problema di CORS o sicurezza");
                            updateTestResult(false, tcfDetails);
                        }
                        
                        // Cleanup
                        setTimeout(function() {
                            if (testFrame.parentNode) {
                                testFrame.parentNode.removeChild(testFrame);
                            }
                        }, 1000);
                    }, 2000); // Aspetta 2 secondi per il caricamento
                    
                } catch (e) {
                    clearTimeout(timeoutId);
                    updateTestResult(false, ["✗ Errore durante il test: " + e.message]);
                }
            };
            
            testFrame.onerror = function() {
                clearTimeout(timeoutId);
                updateTestResult(false, ["✗ Errore caricamento frontend", "Verifica che il sito sia accessibile"]);
            };
            
            function updateTestResult(success, details) {
                button.disabled = false;
                button.textContent = "Test API TCF";
                
                if (success) {
                    statusEl.innerHTML = `
                        <div style="color: #46b450; padding: 10px; background: #f0f8f0; border: 1px solid #46b450; border-radius: 4px;">
                            <strong>✅ TCF API v2.2 funziona correttamente!</strong><br>
                            <small style="line-height: 1.6;">${details.join("<br>")}</small>
                        </div>
                    `;
                } else {
                    statusEl.innerHTML = `
                        <div style="color: #dc3232; padding: 10px; background: #fef7f7; border: 1px solid #dc3232; border-radius: 4px;">
                            <strong>❌ TCF API non disponibile o non funzionante</strong><br>
                            <small style="line-height: 1.6;">${details.join("<br>")}</small><br><br>
                            <strong>Soluzioni:</strong><br>
                            <small>
                            1. Assicurati che "Abilita IAB TCF v2.2" sia spuntato sopra<br>
                            2. Salva le impostazioni<br>
                            3. Svuota la cache del browser e del sito<br>
                            4. Verifica che non ci siano errori JavaScript nella console
                            </small>
                        </div>
                    `;
                }
            }
        });
        </script>';
    }

    /**
     * Consent retention period field callback.
     *
     * @since    1.0.3
     */
    public function consent_retention_period_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $retention_period = isset( $options['consent_retention_period'] ) ? $options['consent_retention_period'] : '365';
        
        $periods = array(
            '30' => __( '1 mese (30 giorni)', 'manus-gdpr' ),
            '180' => __( '6 mesi (180 giorni)', 'manus-gdpr' ),
            '365' => __( '1 anno (365 giorni)', 'manus-gdpr' ),
            '730' => __( '2 anni (730 giorni)', 'manus-gdpr' ),
            '1825' => __( '5 anni (1825 giorni)', 'manus-gdpr' ),
            '3650' => __( '10 anni (3650 giorni)', 'manus-gdpr' )
        );
        
        echo '<select id="consent_retention_period" name="manus_gdpr_settings[consent_retention_period]" class="regular-text">';
        foreach ( $periods as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '"' . selected( $retention_period, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select>';
        
        echo '<p class="description">' . __( 'Tempo di conservazione dei consensi nel database. I consensi più vecchi verranno automaticamente cancellati.', 'manus-gdpr' ) . '</p>';
        
        // Show current stats
        global $wpdb;
        $table_name = $wpdb->prefix . 'manus_gdpr_consents';
        
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
            $total_consents = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
            $old_consents = $wpdb->get_var( $wpdb->prepare( 
                "SELECT COUNT(*) FROM $table_name WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)", 
                intval( $retention_period ) 
            ) );
            
            echo '<div style="margin-top: 10px; padding: 10px; background: #f0f0f1; border-radius: 4px;">';
            echo '<strong>' . __( 'Statistiche consensi:', 'manus-gdpr' ) . '</strong><br>';
            echo sprintf( __( 'Totale consensi: %d', 'manus-gdpr' ), $total_consents ) . '<br>';
            if ( $old_consents > 0 ) {
                echo '<span style="color: #d63638;">' . sprintf( __( 'Consensi da cancellare: %d', 'manus-gdpr' ), $old_consents ) . '</span>';
            } else {
                echo '<span style="color: #00a32a;">' . __( 'Nessun consenso da cancellare', 'manus-gdpr' ) . '</span>';
            }
            echo '</div>';
        }
    }

    /**
     * Delete data on uninstall field callback.
     *
     * @since    1.0.3
     */
    public function delete_data_on_uninstall_callback() {
        $options = get_option( 'manus_gdpr_settings', array() );
        $delete_on_uninstall = isset( $options['delete_data_on_uninstall'] ) ? $options['delete_data_on_uninstall'] : false;
        
        echo '<input type="checkbox" id="delete_data_on_uninstall" name="manus_gdpr_settings[delete_data_on_uninstall]" value="1" ' . checked( 1, $delete_on_uninstall, false ) . ' />';
        echo '<label for="delete_data_on_uninstall">' . __( 'Cancella tutti i dati alla disinstallazione del plugin', 'manus-gdpr' ) . '</label>';
        echo '<p class="description">' . __( 'Se attivato, tutti i consensi e le impostazioni verranno eliminati quando il plugin viene disinstallato. <strong>Attenzione:</strong> questa azione è irreversibile.', 'manus-gdpr' ) . '</p>';
        
        echo '<div style="margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px;">';
        echo '<strong>⚠️ ' . __( 'Importante:', 'manus-gdpr' ) . '</strong><br>';
        echo __( 'Se questa opzione è disattivata, i dati rimarranno nel database anche dopo la disinstallazione del plugin. Questo può essere utile se prevedi di reinstallare il plugin in futuro.', 'manus-gdpr' );
        echo '</div>';
    }

    /**
     * Clear all consents field callback.
     *
     * @since    1.0.3
     */
    public function clear_all_consents_callback() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'manus_gdpr_consents';
        
        echo '<div style="background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 8px;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #495057;">' . __( 'Gestione Consensi Salvati', 'manus-gdpr' ) . '</h4>';
        
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
            $total_consents = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
            $recent_consents = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)" );
            
            // Get retention period for expired consents count
            $options = get_option( 'manus_gdpr_settings', array() );
            $retention_period = isset( $options['consent_retention_period'] ) ? intval( $options['consent_retention_period'] ) : 365;
            $expired_consents = Manus_GDPR_Database::count_expired_consents( $retention_period );
            
            echo '<div style="margin-bottom: 15px;">';
            echo '<strong>' . __( 'Statistiche:', 'manus-gdpr' ) . '</strong><br>';
            echo sprintf( __( '📊 Totale consensi nel database: <strong>%d</strong>', 'manus-gdpr' ), $total_consents ) . '<br>';
            echo sprintf( __( '🕒 Consensi degli ultimi 7 giorni: <strong>%d</strong>', 'manus-gdpr' ), $recent_consents ) . '<br>';
            
            if ( $expired_consents > 0 ) {
                echo '<span style="color: #d63638;">⏰ ' . sprintf( __( 'Consensi scaduti (più vecchi di %d giorni): <strong>%d</strong>', 'manus-gdpr' ), $retention_period, $expired_consents ) . '</span>';
            } else {
                echo '<span style="color: #00a32a;">✅ ' . __( 'Nessun consenso scaduto da cancellare', 'manus-gdpr' ) . '</span>';
            }
            echo '</div>';
            
            if ( $total_consents > 0 ) {
                echo '<div style="margin-bottom: 15px;">';
                echo '<button type="button" id="clear-all-consents" class="button button-secondary" style="color: #d63638; border-color: #d63638;">';
                echo '🗑️ ' . __( 'Cancella Tutti i Consensi', 'manus-gdpr' );
                echo '</button>';
                echo '<button type="button" id="clear-old-consents" class="button button-secondary" style="margin-left: 10px;">';
                echo '🧹 ' . __( 'Cancella Solo Consensi Scaduti', 'manus-gdpr' );
                echo '</button>';
                echo '</div>';
                
                echo '<div id="clear-consents-result" style="display: none; margin-top: 10px;"></div>';
            } else {
                echo '<p style="color: #6c757d; font-style: italic;">' . __( 'Nessun consenso presente nel database.', 'manus-gdpr' ) . '</p>';
            }
        } else {
            echo '<p style="color: #6c757d; font-style: italic;">' . __( 'Tabella consensi non trovata. I consensi verranno salvati al primo utilizzo.', 'manus-gdpr' ) . '</p>';
        }
        
        echo '</div>';
        
        // JavaScript for clear consents functionality
        echo '<script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Clear all consents
            document.getElementById("clear-all-consents")?.addEventListener("click", function() {
                if (confirm("' . esc_js( __( 'Sei sicuro di voler cancellare TUTTI i consensi? Questa azione è irreversibile!', 'manus-gdpr' ) ) . '")) {
                    clearConsents("all");
                }
            });
            
            // Clear old consents
            document.getElementById("clear-old-consents")?.addEventListener("click", function() {
                if (confirm("' . esc_js( __( 'Cancellare i consensi scaduti secondo il periodo di conservazione impostato?', 'manus-gdpr' ) ) . '")) {
                    clearConsents("old");
                }
            });
            
            function clearConsents(type) {
                const resultDiv = document.getElementById("clear-consents-result");
                const buttons = document.querySelectorAll("#clear-all-consents, #clear-old-consents");
                
                // Disable buttons
                buttons.forEach(btn => btn.disabled = true);
                
                // Show loading
                resultDiv.style.display = "block";
                resultDiv.innerHTML = "<p style=\"color: #0073aa;\">⏳ " + "' . esc_js( __( 'Cancellazione in corso...', 'manus-gdpr' ) ) . '" + "</p>";
                
                // AJAX request
                fetch(manus_gdpr_admin_ajax.ajax_url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: new URLSearchParams({
                        action: "manus_gdpr_clear_consents",
                        type: type,
                        nonce: manus_gdpr_admin_ajax.nonce
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = "<p style=\"color: #00a32a;\">✅ " + data.data + "</p>";
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        resultDiv.innerHTML = "<p style=\"color: #d63638;\">❌ " + (data.data || "' . esc_js( __( 'Errore durante la cancellazione', 'manus-gdpr' ) ) . '") + "</p>";
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = "<p style=\"color: #d63638;\">❌ " + "' . esc_js( __( 'Errore di comunicazione', 'manus-gdpr' ) ) . '" + " - " + error.message + "</p>";
                })
                .finally(() => {
                    // Re-enable buttons
                    buttons.forEach(btn => btn.disabled = false);
                });
            }
        });
        </script>';
    }

    /**
     * Handle AJAX request for clearing consents
     *
     * @since    1.0.3
     */
    public function handle_clear_consents_ajax() {
        // Verifica nonce per sicurezza
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'manus_gdpr_clear_consents' ) ) {
            wp_die( __( 'Security check failed', 'manus-gdpr' ) );
        }
        
        // Verifica capacità utente
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Insufficient permissions', 'manus-gdpr' ) );
        }
        
        $type = sanitize_text_field( $_POST['type'] );
        
        if ( $type === 'all' ) {
            $deleted = Manus_GDPR_Database::clear_all_consents();
            if ( $deleted !== false ) {
                wp_send_json_success( sprintf( __( 'Cancellati %d consensi.', 'manus-gdpr' ), $deleted ) );
            } else {
                wp_send_json_error( __( 'Errore durante la cancellazione di tutti i consensi.', 'manus-gdpr' ) );
            }
        } elseif ( $type === 'old' ) {
            $options = get_option( 'manus_gdpr_settings' );
            $retention_days = isset( $options['consent_retention_period'] ) ? (int) $options['consent_retention_period'] : 365;
            
            $deleted = Manus_GDPR_Database::clear_expired_consents( $retention_days );
            if ( $deleted !== false ) {
                wp_send_json_success( sprintf( __( 'Cancellati %d consensi scaduti (più vecchi di %d giorni).', 'manus-gdpr' ), $deleted, $retention_days ) );
            } else {
                wp_send_json_error( __( 'Errore durante la cancellazione dei consensi scaduti.', 'manus-gdpr' ) );
            }
        } else {
            wp_send_json_error( __( 'Tipo di cancellazione non valido.', 'manus-gdpr' ) );
        }
    }

}