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
$banner_message = isset( $options['banner_message'] ) ? $options['banner_message'] : __( 'Utilizziamo i cookie per migliorare la tua esperienza di navigazione. Cliccando su "Accetta", acconsenti all\"uso di tutti i cookie.', 'manus-gdpr' );
$banner_position = isset( $options['banner_position'] ) ? $options['banner_position'] : 'bottom';
$privacy_page_link = isset( $privacy_page_link ) ? $privacy_page_link : '#'; // Fallback in case it's not set

?>

<div id="manus-gdpr-cookie-banner" class="<?php echo esc_attr( $banner_position ); ?>">
    <p><?php echo esc_html( $banner_message ); ?> <a href="<?php echo esc_url( $privacy_page_link ); ?>" target="_blank"><?php _e( 'Maggiori informazioni', 'manus-gdpr' ); ?></a></p>
    <button id="manus-gdpr-accept-button" data-consent-status="accepted"><?php _e( 'Accetta', 'manus-gdpr' ); ?></button>
    <button id="manus-gdpr-reject-button" data-consent-status="rejected"><?php _e( 'Rifiuta', 'manus-gdpr' ); ?></button>
    <button id="manus-gdpr-manage-button" data-consent-status="manage"><?php _e( 'Gestisci preferenze', 'manus-gdpr' ); ?></button>
</div>