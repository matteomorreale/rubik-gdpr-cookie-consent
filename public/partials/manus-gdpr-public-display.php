<?php
/**
 * Provide a public-facing view for the plugin
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/public/partials
 */

$options = get_option( 'manus_gdpr_settings' );
$banner_message = isset( $options['cookie_banner_message'] ) ? $options['cookie_banner_message'] : __( 'Utilizziamo i cookie per migliorare la tua esperienza di navigazione. Cliccando su "Accetta", acconsenti all\'uso di tutti i cookie.', 'manus-gdpr' );
$banner_position = isset( $options['cookie_banner_position'] ) ? $options['cookie_banner_position'] : 'bottom';
$privacy_page_link = isset( $privacy_page_link ) ? $privacy_page_link : '#'; // Fallback in case it's not set
$show_manage_preferences = isset( $options['show_manage_preferences'] ) ? $options['show_manage_preferences'] : true;

// Custom colors
$bg_color = isset( $options['banner_background_color'] ) ? $options['banner_background_color'] : '#333333';
$text_color = isset( $options['banner_text_color'] ) ? $options['banner_text_color'] : '#ffffff';
$button_color = isset( $options['banner_button_color'] ) ? $options['banner_button_color'] : '#0073aa';

// Overlay settings
$show_overlay = isset( $options['show_banner_overlay'] ) ? $options['show_banner_overlay'] : true;
$overlay_color = isset( $options['banner_overlay_color'] ) ? $options['banner_overlay_color'] : 'rgba(47, 79, 79, 0.6)';

// Theme and layout settings
$theme_mode = isset( $options['theme_mode'] ) ? $options['theme_mode'] : 'light';
$layout_mode = isset( $options['layout_mode'] ) ? $options['layout_mode'] : 'card';

// Build CSS classes for banner and modal
$banner_classes = array( $banner_position, 'theme-' . $theme_mode, 'layout-' . $layout_mode );
$modal_classes = array( 'theme-' . $theme_mode );

?>

<style>
/* Personalizzazione colori banner */
#manus-gdpr-cookie-banner {
    background: <?php echo esc_attr( $bg_color ); ?> !important;
    color: <?php echo esc_attr( $text_color ); ?> !important;
}

.manus-gdpr-banner-text h3,
.manus-gdpr-banner-text p {
    color: <?php echo esc_attr( $text_color ); ?> !important;
}

#manus-gdpr-accept-button {
    background: <?php echo esc_attr( $button_color ); ?> !important;
}

/* Personalizzazione overlay */
.manus-gdpr-overlay {
    background-color: <?php echo esc_attr( $overlay_color ); ?> !important;
}

/* Apply button color to floating icon */
#manus-gdpr-floating-icon {
    background-color: <?php echo esc_attr( $button_color ); ?> !important;
}

/* Prevenire flash della modale - applica subito il tema */
<?php if ( $theme_mode === 'light' || $theme_mode === 'auto' ): ?>
#manus-gdpr-preferences-content {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
    color: #2c3e50 !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

#manus-gdpr-preferences-content h3 {
    color: #2c3e50 !important;
}

#manus-gdpr-preferences-content > p {
    color: #5a6c7d !important;
}

.manus-gdpr-preference-category {
    background: white !important;
    border-color: #e9ecef !important;
}

.manus-gdpr-preference-category h4 {
    color: #2c3e50 !important;
}

.manus-gdpr-preference-category p {
    color: #6c757d !important;
}

.manus-gdpr-close {
    background: #f8f9fa !important;
    color: #6c757d !important;
}
<?php elseif ( $theme_mode === 'dark' ): ?>
#manus-gdpr-preferences-content {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%) !important;
    color: #e2e8f0 !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

#manus-gdpr-preferences-content h3 {
    color: #f7fafc !important;
}

#manus-gdpr-preferences-content > p {
    color: #cbd5e0 !important;
}

.manus-gdpr-preference-category {
    background: #4a5568 !important;
    border-color: #718096 !important;
}

.manus-gdpr-preference-category h4 {
    color: #f7fafc !important;
}

.manus-gdpr-preference-category p {
    color: #cbd5e0 !important;
}

.manus-gdpr-close {
    background: #4a5568 !important;
    color: #e2e8f0 !important;
}
<?php endif; ?>

/* Inizializza subito il display nascosto per prevenire flash */
#manus-gdpr-preferences-modal {
    display: none !important;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease !important;
}

#manus-gdpr-preferences-modal.show {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}
</style>

<!-- Overlay per dare evidenza al banner (se abilitato) -->
<?php if ( $show_overlay ): ?>
<div class="manus-gdpr-overlay" id="manus-gdpr-overlay"></div>
<?php endif; ?>

<!-- Banner cookie moderno -->
<div id="manus-gdpr-cookie-banner" class="<?php echo esc_attr( implode( ' ', $banner_classes ) ); ?>">
    <div class="manus-gdpr-banner-content">
        <div class="manus-gdpr-banner-header">
            <div class="manus-gdpr-banner-icon">
                🍪
            </div>
            <div class="manus-gdpr-banner-text">
                <h3><?php _e( 'Informativa sui Cookie', 'manus-gdpr' ); ?></h3>
                <p>
                    <?php echo esc_html( $banner_message ); ?> 
                    <a href="<?php echo esc_url( $privacy_page_link ); ?>" target="_blank">
                        <?php _e( 'Maggiori informazioni', 'manus-gdpr' ); ?>
                    </a>
                </p>
            </div>
        </div>
        
        <div class="manus-gdpr-buttons">
            <button id="manus-gdpr-accept-button" data-consent-status="accepted">
                <?php _e( 'Accetta tutti', 'manus-gdpr' ); ?>
            </button>
            <button id="manus-gdpr-reject-button" data-consent-status="rejected">
                <?php _e( 'Rifiuta tutti', 'manus-gdpr' ); ?>
            </button>
            <?php if ( $show_manage_preferences ): ?>
                <button id="manus-gdpr-manage-button" data-consent-status="manage">
                    <?php _e( 'Gestisci preferenze', 'manus-gdpr' ); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal per gestione preferenze -->
<div id="manus-gdpr-preferences-modal" style="display: none;">
    <div id="manus-gdpr-preferences-content" class="<?php echo esc_attr( implode( ' ', $modal_classes ) ); ?>">
        <span class="manus-gdpr-close">&times;</span>
        <h3><?php _e( 'Gestisci le tue preferenze sui cookie', 'manus-gdpr' ); ?></h3>
        <p><?php _e( 'Puoi scegliere quali categorie di cookie accettare. I cookie necessari sono sempre attivi per garantire il funzionamento del sito.', 'manus-gdpr' ); ?></p>
        
        <?php
        $cookie_categories = isset( $options['cookie_categories'] ) ? $options['cookie_categories'] : array();
        $available_categories = array(
            'necessary' => array(
                'title' => __( 'Cookie Necessari', 'manus-gdpr' ),
                'description' => __( 'Questi cookie sono essenziali per il funzionamento del sito web e non possono essere disabilitati.', 'manus-gdpr' ),
                'required' => true
            ),
            'analytics' => array(
                'title' => __( 'Cookie Analitici', 'manus-gdpr' ),
                'description' => __( 'Questi cookie ci aiutano a capire come i visitatori interagiscono con il sito web raccogliendo e segnalando informazioni in forma anonima.', 'manus-gdpr' ),
                'required' => false
            ),
            'advertising' => array(
                'title' => __( 'Cookie Pubblicitari', 'manus-gdpr' ),
                'description' => __( 'Questi cookie vengono utilizzati per fornire ai visitatori annunci pubblicitari personalizzati basati sulle pagine visitate in precedenza.', 'manus-gdpr' ),
                'required' => false
            ),
            'functional' => array(
                'title' => __( 'Cookie Funzionali', 'manus-gdpr' ),
                'description' => __( 'Questi cookie consentono al sito web di fornire funzionalità e personalizzazione migliorate.', 'manus-gdpr' ),
                'required' => false
            )
        );
        
        foreach ( $available_categories as $category => $details ):
            $enabled = isset( $cookie_categories[$category] ) ? $cookie_categories[$category] : $details['required'];
        ?>
            <div class="manus-gdpr-preference-category">
                <div class="manus-gdpr-preference-toggle">
                    <div>
                        <h4><?php echo esc_html( $details['title'] ); ?></h4>
                        <p><?php echo esc_html( $details['description'] ); ?></p>
                    </div>
                    <div>
                        <label class="switch">
                            <input type="checkbox" 
                                   id="manus-gdpr-<?php echo esc_attr( $category ); ?>" 
                                   data-category="<?php echo esc_attr( $category ); ?>"
                                   <?php checked( $enabled ); ?>
                                   <?php disabled( $details['required'] ); ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <button id="manus-gdpr-save-preferences" class="button button-primary"><?php _e( 'Salva Preferenze', 'manus-gdpr' ); ?></button>
        </div>
    </div>
</div>

<!-- Floating icon per riaprire le preferenze cookie -->
<div id="manus-gdpr-floating-icon" style="display: none;" title="<?php _e( 'Gestisci preferenze cookie', 'manus-gdpr' ); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        <circle cx="12" cy="12" r="2" fill="white"/>
    </svg>
    <span class="manus-gdpr-floating-text"><?php _e( 'Cookie', 'manus-gdpr' ); ?></span>
</div>