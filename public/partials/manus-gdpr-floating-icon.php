<?php
/**
 * Floating icon and preferences modal for users who already gave consent
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/public/partials
 */

$options = get_option( 'manus_gdpr_settings' );

// Custom colors
$button_color = isset( $options['banner_button_color'] ) ? $options['banner_button_color'] : '#0073aa';
$show_manage_preferences = isset( $options['show_manage_preferences'] ) ? $options['show_manage_preferences'] : true;

// Theme settings for modal
$theme_mode = isset( $options['theme_mode'] ) ? $options['theme_mode'] : 'light';
$modal_classes = array( 'theme-' . $theme_mode );

// Only show if manage preferences is enabled
if ( ! $show_manage_preferences ) {
    return;
}
?>

<style>
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
        
        // Get current consent data from cookie
        $current_consent = isset( $_COOKIE['manus_gdpr_consent'] ) ? json_decode( stripslashes( $_COOKIE['manus_gdpr_consent'] ), true ) : array();
        if ( is_string( $current_consent ) ) {
            // Handle old format cookie
            $current_consent = array( 'status' => $current_consent );
        }
        
        foreach ( $available_categories as $category => $details ):
            // Check current consent status for this category
            $current_enabled = false;
            if ( isset( $current_consent['data'][$category] ) ) {
                $current_enabled = $current_consent['data'][$category];
            } elseif ( $details['required'] ) {
                $current_enabled = true; // Always enabled for required categories
            }
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
                                   <?php checked( $current_enabled ); ?>
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
<div id="manus-gdpr-floating-icon" style="display: flex;" title="<?php _e( 'Gestisci preferenze cookie', 'manus-gdpr' ); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        <circle cx="12" cy="12" r="2" fill="white"/>
    </svg>
    <span class="manus-gdpr-floating-text"><?php _e( 'Cookie', 'manus-gdpr' ); ?></span>
</div>
