<?php
/**
 * Provide a admin area view for the plugin
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div class="card">
        <h2 class="title"><?php _e( 'Benvenuto in GDPR Cookie Consent Cookie Consent', 'manus-gdpr' ); ?></h2>
        <p><?php _e( 'Questo plugin aiuta il tuo sito web a essere conforme al GDPR gestendo i consensi ai cookie degli utenti.', 'manus-gdpr' ); ?></p>
        
        <h3><?php _e( 'Azioni rapide', 'manus-gdpr' ); ?></h3>
        <ul>
            <li><a href="<?php echo admin_url( 'admin.php?page=' . 'manus-gdpr-settings' ); ?>" class="button button-primary"><?php _e( 'Configura Impostazioni', 'manus-gdpr' ); ?></a></li>
            <li><a href="<?php echo admin_url( 'admin.php?page=' . 'manus-gdpr-scanner' ); ?>" class="button button-secondary"><?php _e( 'Avvia Cookie Scanner', 'manus-gdpr' ); ?></a></li>
            <li><a href="<?php echo admin_url( 'admin.php?page=' . 'manus-gdpr-consent-log' ); ?>" class="button button-secondary"><?php _e( 'Visualizza Log Consensi', 'manus-gdpr' ); ?></a></li>
        </ul>
    </div>

    <div class="card">
        <h3><?php _e( 'Stato Plugin', 'manus-gdpr' ); ?></h3>
        <?php
        $options = get_option( 'manus_gdpr_settings', array() );
        $banner_enabled = isset( $options['cookie_banner_enabled'] ) ? $options['cookie_banner_enabled'] : false;
        ?>
        <p>
            <strong><?php _e( 'Banner Cookie:', 'manus-gdpr' ); ?></strong>
            <?php if ( $banner_enabled ): ?>
                <span style="color: green;"><?php _e( 'Attivo', 'manus-gdpr' ); ?></span>
            <?php else: ?>
                <span style="color: red;"><?php _e( 'Disattivo', 'manus-gdpr' ); ?></span>
            <?php endif; ?>
        </p>
        
        <?php
        global $wpdb;
        $consent_table = $wpdb->prefix . 'manus_gdpr_consents';
        $consent_count = $wpdb->get_var( "SELECT COUNT(*) FROM $consent_table" );
        ?>
        <p>
            <strong><?php _e( 'Consensi Registrati:', 'manus-gdpr' ); ?></strong>
            <?php echo esc_html( $consent_count ); ?>
        </p>
    </div>

    <div class="card">
        <h3><?php _e( 'Informazioni Plugin', 'manus-gdpr' ); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e( 'Versione:', 'manus-gdpr' ); ?></th>
                <td><?php echo esc_html( defined('MANUS_GDPR_VERSION') ? MANUS_GDPR_VERSION : '1.0.0' ); ?></td>
            </tr>
            <tr>
                <th scope="row"><?php _e( 'Database:', 'manus-gdpr' ); ?></th>
                <td>
                    <?php
                    global $wpdb;
                    $consent_table = $wpdb->prefix . 'manus_gdpr_consents';
                    $scanner_table = $wpdb->prefix . 'manus_gdpr_cookie_scans';
                    
                    $consent_count = $wpdb->get_var( "SELECT COUNT(*) FROM $consent_table" );
                    $scanner_count = $wpdb->get_var( "SELECT COUNT(*) FROM $scanner_table" );
                    
                    echo sprintf( __( 'Scansioni cookie: %d', 'manus-gdpr' ), $scanner_count );
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( 'Stato GDPR:', 'manus-gdpr' ); ?></th>
                <td>
                    <?php
                    $strict_mode = isset( $options['strict_mode'] ) ? $options['strict_mode'] : false;
                    if ( $banner_enabled && $strict_mode ): ?>
                        <span style="color: green; font-weight: bold;"><?php _e( '✓ Conforme GDPR', 'manus-gdpr' ); ?></span>
                    <?php elseif ( $banner_enabled ): ?>
                        <span style="color: orange; font-weight: bold;"><?php _e( '⚠ Parzialmente conforme', 'manus-gdpr' ); ?></span>
                    <?php else: ?>
                        <span style="color: red; font-weight: bold;"><?php _e( '✗ Non conforme', 'manus-gdpr' ); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e( 'IAB TCF v2.2:', 'manus-gdpr' ); ?></th>
                <td>
                    <?php
                    $tcf_enabled = isset( $options['enable_tcf_v2'] ) ? $options['enable_tcf_v2'] : true;
                    if ( $tcf_enabled ): ?>
                        <span style="color: green; font-weight: bold;"><?php _e( '✓ Attivo', 'manus-gdpr' ); ?></span>
                        <button type="button" id="test-tcf-api" class="button button-secondary" style="margin-left: 10px;">
                            <?php _e( 'Testa TCF API', 'manus-gdpr' ); ?>
                        </button>
                    <?php else: ?>
                        <span style="color: orange; font-weight: bold;"><?php _e( '⚠ Disattivo', 'manus-gdpr' ); ?></span>
                        <small style="display: block; margin-top: 5px;">
                            <?php _e( 'Attivalo nelle impostazioni per abilitare il test', 'manus-gdpr' ); ?>
                        </small>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

</div>