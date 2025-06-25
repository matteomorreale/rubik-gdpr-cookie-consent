<?php
/**
 * Provide a admin area view for the consent log
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/admin/partials
 */

// Don't access directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current URL for pagination
$current_url = admin_url( 'admin.php?page=' . $this->plugin_name . '-consent-log' );
?>

<div class="wrap">
    <h1><?php _e( 'Registro Consensi', 'manus-gdpr' ); ?></h1>

    <?php if ( empty( $consent_records ) && empty( $search_term ) && empty( $filter_status ) ): ?>
        <div class="notice notice-info">
            <p><?php _e( 'Non sono ancora presenti consensi nel database.', 'manus-gdpr' ); ?></p>
            <p>
                <a href="<?php echo esc_url( add_query_arg( 'insert_test_data', '1' ) ); ?>" class="button button-secondary">
                    <?php _e( 'Inserisci dati di test', 'manus-gdpr' ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="consent-stats-cards">
        <div class="consent-stat-card">
            <h3><?php echo esc_html( $stats['total'] ?? 0 ); ?></h3>
            <p><?php _e( 'Consensi Totali', 'manus-gdpr' ); ?></p>
        </div>
        <div class="consent-stat-card">
            <h3><?php echo esc_html( $stats['accepted'] ?? 0 ); ?></h3>
            <p><?php _e( 'Accettati', 'manus-gdpr' ); ?></p>
        </div>
        <div class="consent-stat-card">
            <h3><?php echo esc_html( $stats['rejected'] ?? 0 ); ?></h3>
            <p><?php _e( 'Rifiutati', 'manus-gdpr' ); ?></p>
        </div>
        <div class="consent-stat-card">
            <h3><?php echo esc_html( $stats['partial'] ?? 0 ); ?></h3>
            <p><?php _e( 'Parziali', 'manus-gdpr' ); ?></p>
        </div>
        <div class="consent-stat-card">
            <h3><?php echo esc_html( $stats['recent'] ?? 0 ); ?></h3>
            <p><?php _e( 'Ultimi 30 giorni', 'manus-gdpr' ); ?></p>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" id="consent-log-filter">
                <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
                
                <label class="screen-reader-text" for="filter-status"><?php _e( 'Filtra per stato', 'manus-gdpr' ); ?></label>
                <select name="filter_status" id="filter-status">
                    <option value=""><?php _e( 'Tutti gli stati', 'manus-gdpr' ); ?></option>
                    <option value="accepted" <?php selected( $filter_status, 'accepted' ); ?>><?php _e( 'Accettato', 'manus-gdpr' ); ?></option>
                    <option value="rejected" <?php selected( $filter_status, 'rejected' ); ?>><?php _e( 'Rifiutato', 'manus-gdpr' ); ?></option>
                    <option value="partial" <?php selected( $filter_status, 'partial' ); ?>><?php _e( 'Parziale', 'manus-gdpr' ); ?></option>
                </select>
                
                <?php submit_button( __( 'Filtra', 'manus-gdpr' ), 'button', 'filter_action', false ); ?>
            </form>
        </div>
        
        <div class="alignright actions">
            <form method="get" class="search-box">
                <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
                <?php if ( $filter_status ): ?>
                    <input type="hidden" name="filter_status" value="<?php echo esc_attr( $filter_status ); ?>" />
                <?php endif; ?>
                <label class="screen-reader-text" for="consent-search-input"><?php _e( 'Cerca consensi:', 'manus-gdpr' ); ?></label>
                <input type="search" id="consent-search-input" name="s" value="<?php echo esc_attr( $search_term ); ?>" placeholder="<?php _e( 'Cerca per IP o ID utente...', 'manus-gdpr' ); ?>" />
                <?php submit_button( __( 'Cerca', 'manus-gdpr' ), 'button', '', false, array( 'id' => 'search-submit' ) ); ?>
            </form>
        </div>
    </div>

    <!-- Consent Log Table -->
    <?php if ( ! empty( $consent_records ) ): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-user"><?php _e( 'Utente', 'manus-gdpr' ); ?></th>
                    <th scope="col" class="manage-column column-ip"><?php _e( 'Indirizzo IP', 'manus-gdpr' ); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e( 'Stato', 'manus-gdpr' ); ?></th>
                    <th scope="col" class="manage-column column-data"><?php _e( 'Dettagli Consenso', 'manus-gdpr' ); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e( 'Data/Ora', 'manus-gdpr' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $consent_records as $record ): ?>
                    <tr>
                        <td class="column-user">
                            <?php echo esc_html( $this->get_user_display_name( $record['user_id'] ) ); ?>
                            <?php if ( ! empty( $record['user_id'] ) ): ?>
                                <br><small>(ID: <?php echo esc_html( $record['user_id'] ); ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td class="column-ip">
                            <code><?php echo esc_html( $record['ip_address'] ); ?></code>
                        </td>
                        <td class="column-status">
                            <?php echo $this->get_consent_status_badge( $record['consent_status'] ); ?>
                        </td>
                        <td class="column-data">
                            <div class="consent-data-toggle">
                                <button type="button" class="button-link consent-toggle" 
                                        data-consent-id="<?php echo esc_attr( $record['id'] ); ?>"
                                        data-show-text="<?php echo esc_attr( __( 'Mostra dettagli', 'manus-gdpr' ) ); ?>"
                                        data-hide-text="<?php echo esc_attr( __( 'Nascondi dettagli', 'manus-gdpr' ) ); ?>"
                                        aria-expanded="false"
                                        aria-controls="consent-details-<?php echo esc_attr( $record['id'] ); ?>">
                                    <?php _e( 'Mostra dettagli', 'manus-gdpr' ); ?>
                                </button>
                                <div class="consent-details" id="consent-details-<?php echo esc_attr( $record['id'] ); ?>" style="display: none; margin-top: 10px;" role="region" aria-labelledby="consent-toggle-<?php echo esc_attr( $record['id'] ); ?>">
                                    <?php echo $this->format_consent_data( $record['consent_data'] ); ?>
                                </div>
                            </div>
                        </td>
                        <td class="column-date">
                            <?php 
                            $date = new DateTime( $record['timestamp'] );
                            echo esc_html( $date->format( 'd/m/Y H:i:s' ) );
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ( $total_pages > 1 ): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php printf( _n( '%s elemento', '%s elementi', $total_items, 'manus-gdpr' ), number_format_i18n( $total_items ) ); ?></span>
                    
                    <?php
                    $page_links = paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => __( '&laquo;', 'manus-gdpr' ),
                        'next_text' => __( '&raquo;', 'manus-gdpr' ),
                        'total'     => $total_pages,
                        'current'   => $current_page,
                        'type'      => 'plain',
                        'add_args'  => array(
                            'filter_status' => $filter_status,
                            's' => $search_term,
                        ),
                    ) );
                    
                    if ( $page_links ) {
                        echo '<span class="pagination-links">' . $page_links . '</span>';
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e( 'Nessun consenso trovato.', 'manus-gdpr' ); ?></p>
            <?php if ( $search_term || $filter_status ): ?>
                <p>
                    <a href="<?php echo esc_url( $current_url ); ?>" class="button">
                        <?php _e( 'Rimuovi filtri', 'manus-gdpr' ); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>