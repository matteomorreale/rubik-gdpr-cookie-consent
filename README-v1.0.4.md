# Rubik GDPR Cookie Consent - v1.0.4

## 🆕 Nuove Funzionalità v1.0.4

### Gestione Automatica Consensi
- **Periodo di retention configurabile**: Scegli dopo quanto tempo cancellare i consensi (1 mese - 10 anni)
- **Pulizia automatica giornaliera**: Cron job che rimuove automaticamente i consensi scaduti
- **Cancellazione manuale**: Pulsanti per cancellare tutti i consensi o solo quelli scaduti
- **Statistiche in tempo reale**: Visualizzazione consensi totali, recenti e scaduti

### Opzioni di Disinstallazione
- **Cancellazione dati opzionale**: Scegli se mantenere o eliminare tutti i dati alla disinstallazione
- **Supporto multisite**: Gestione completa per installazioni WordPress multi-sito

### Sicurezza e Performance
- **Nonce verification** per tutte le operazioni AJAX
- **Query ottimizzate** per grandi dataset
- **Log delle operazioni** per monitoraggio

## 🛠️ Configurazione Consigliata

### Accesso alle Impostazioni
1. Vai su **WordPress Admin → Rubik GDPR → Impostazioni**
2. Scorri fino alla sezione **"Gestione Consensi"**

### Impostazioni Base
- **Periodo di retention**: 1 anno (consigliato per conformità GDPR)
- **Delete on uninstall**: Attivato (per privacy by design)

## 📋 Funzionalità Disponibili

### Amministrazione
- ⚙️ **Configurazione retention**: Seleziona periodo da menu dropdown
- 📊 **Statistiche dashboard**: Visualizzazione consensi in tempo reale
- 🗑️ **Cancellazione manuale**: Pulsanti per pulizia immediata
- 🕒 **Monitoraggio automatico**: Conteggio consensi scaduti

### Automazione
- 🔄 **Cron job giornaliero**: Pulizia automatica alle 00:00
- 📝 **Log operazioni**: Registrazione nel log WordPress
- 🛡️ **Sicurezza integrata**: Verifiche di permessi e nonce

### Disinstallazione
- 🗄️ **Pulizia completa database**: Rimozione tabelle e opzioni
- 🌐 **Supporto multisite**: Gestione per tutti i siti della rete
- ⏰ **Rimozione cron jobs**: Pulizia completa dei task programmati

## 🔧 Troubleshooting

### Il Cron Job Non Funziona
```bash
# Verifica programmazione cron
wp cron event list --hook=manus_gdpr_cleanup_expired_consents

# Test manuale pulizia
wp eval "do_action('manus_gdpr_cleanup_expired_consents');"
```

### Verifica Database
```sql
-- Conta consensi totali
SELECT COUNT(*) FROM wp_manus_gdpr_consents;

-- Conta consensi scaduti (oltre 365 giorni)
SELECT COUNT(*) FROM wp_manus_gdpr_consents 
WHERE timestamp < DATE_SUB(NOW(), INTERVAL 365 DAY);
```

### Debug Operazioni
Attiva il debug WordPress per vedere i log delle operazioni:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📚 Documentazione Completa

Per la documentazione tecnica completa consulta:
- `CONSENT-RETENTION-DOCS.md` - Guida tecnica completa
- `test-consent-retention.php` - Script di test delle funzionalità

## 🚀 Prossime Funzionalità

- **Export consensi**: Esportazione dati in CSV/JSON
- **Import batch**: Importazione consensi da file
- **Dashboard analytics**: Grafici e statistiche avanzate
- **API REST**: Endpoint per integrazione esterna
- **Webhook notifications**: Notifiche per eventi importanti

## 📞 Supporto

Per bug, richieste di funzionalità o supporto tecnico:
- Controlla la documentazione inclusa
- Esegui il test script per diagnostica
- Verifica i log WordPress per errori
- Controlla che i permessi database siano corretti

---
**Versione:** 1.0.4  
**Compatibilità:** WordPress 5.0+, PHP 7.4+  
**Ultimo aggiornamento:** Giugno 2025
