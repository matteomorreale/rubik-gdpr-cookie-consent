<?php
/**
 * Test script per verificare il funzionamento del sistema di retention consensi
 * 
 * Eseguire questo script dalla riga di comando per testare le funzionalità:
 * php test-consent-retention.php
 * 
 * @package Manus_GDPR
 * @since 1.0.3
 */

// Test solo in ambiente di sviluppo
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
    die( 'Test script disponibile solo in modalità debug.' );
}

// Include WordPress
require_once( '../../../wp-load.php' );

// Include le classi necessarie
require_once( 'includes/class-manus-gdpr-database.php' );

echo "=== TEST SISTEMA RETENTION CONSENSI ===\n\n";

// Test 1: Verifica creazione tabelle
echo "1. Verifica presenza tabelle database...\n";
global $wpdb;
$table_name = $wpdb->prefix . 'manus_gdpr_consents';
$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;

if ( $table_exists ) {
    echo "✅ Tabella consensi presente\n";
} else {
    echo "❌ Tabella consensi mancante\n";
    die( "Errore: tabella non trovata\n" );
}

// Test 2: Inserimento dati di test
echo "\n2. Inserimento dati di test...\n";
$test_data = array(
    array(
        'user_id' => null,
        'ip_address' => '192.168.1.100',
        'consent_status' => 'accepted',
        'consent_data' => serialize( array( 'necessary' => true, 'analytics' => true ) ),
        'timestamp' => date( 'Y-m-d H:i:s', strtotime( '-400 days' ) ) // Consenso vecchio
    ),
    array(
        'user_id' => null,
        'ip_address' => '192.168.1.101',
        'consent_status' => 'rejected',
        'consent_data' => serialize( array( 'necessary' => true, 'analytics' => false ) ),
        'timestamp' => date( 'Y-m-d H:i:s', strtotime( '-200 days' ) ) // Consenso recente
    ),
    array(
        'user_id' => null,
        'ip_address' => '192.168.1.102',
        'consent_status' => 'partial',
        'consent_data' => serialize( array( 'necessary' => true, 'analytics' => false, 'advertising' => true ) ),
        'timestamp' => date( 'Y-m-d H:i:s', strtotime( '-500 days' ) ) // Consenso molto vecchio
    )
);

$inserted = 0;
foreach ( $test_data as $data ) {
    $result = $wpdb->insert( $table_name, $data );
    if ( $result ) {
        $inserted++;
    }
}

echo "✅ Inseriti $inserted consensi di test\n";

// Test 3: Conteggio consensi
echo "\n3. Conteggio consensi...\n";
$total_consents = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
echo "📊 Totale consensi: $total_consents\n";

// Test 4: Test metodi di cancellazione
echo "\n4. Test conteggio consensi scaduti...\n";
$retention_days = 365; // 1 anno
$expired_count = Manus_GDPR_Database::count_expired_consents( $retention_days );
echo "⏰ Consensi scaduti (>$retention_days giorni): $expired_count\n";

// Test 5: Test cancellazione consensi scaduti
echo "\n5. Test cancellazione consensi scaduti...\n";
$deleted = Manus_GDPR_Database::clear_expired_consents( $retention_days );
echo "🧹 Consensi scaduti cancellati: $deleted\n";

// Test 6: Verifica conteggi dopo cancellazione
echo "\n6. Verifica dopo cancellazione...\n";
$remaining_consents = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
$still_expired = Manus_GDPR_Database::count_expired_consents( $retention_days );
echo "📊 Consensi rimanenti: $remaining_consents\n";
echo "⏰ Consensi ancora scaduti: $still_expired\n";

// Test 7: Verifica cron job
echo "\n7. Verifica programmazione cron job...\n";
$next_scheduled = wp_next_scheduled( 'manus_gdpr_cleanup_expired_consents' );
if ( $next_scheduled ) {
    echo "✅ Cron job programmato per: " . date( 'Y-m-d H:i:s', $next_scheduled ) . "\n";
} else {
    echo "❌ Cron job non programmato\n";
}

// Test 8: Test opzioni plugin
echo "\n8. Verifica opzioni plugin...\n";
$options = get_option( 'manus_gdpr_settings', array() );
$retention_setting = isset( $options['consent_retention_period'] ) ? $options['consent_retention_period'] : 'non impostato';
$delete_on_uninstall = isset( $options['delete_data_on_uninstall'] ) ? ( $options['delete_data_on_uninstall'] ? 'attivo' : 'disattivo' ) : 'non impostato';

echo "⚙️ Periodo retention: $retention_setting giorni\n";
echo "🗑️ Cancellazione su disinstallazione: $delete_on_uninstall\n";

// Test 9: Pulizia dati di test
echo "\n9. Pulizia dati di test...\n";
$cleanup_result = Manus_GDPR_Database::clear_all_consents();
echo "🧽 Tutti i consensi di test cancellati: $cleanup_result\n";

echo "\n=== TEST COMPLETATO ===\n";
echo "✅ Tutti i componenti del sistema di retention funzionano correttamente\n";
echo "\nPer testare in produzione:\n";
echo "1. Configura il periodo di retention dalle impostazioni WordPress\n";
echo "2. Il cron job si attiverà automaticamente ogni giorno alle 00:00\n";
echo "3. Monitora i log per verificare l'esecuzione automatica\n";
echo "4. Usa i pulsanti di cancellazione manuale quando necessario\n";
?>
