# Gestione Automatica Consensi - Documentazione

## Panoramica

Il plugin Rubik GDPR Cookie Consent include un sistema completo per la gestione automatica dei consensi degli utenti, permettendo di:

1. **Configurare il periodo di retention dei consensi**
2. **Cancellare automaticamente i consensi scaduti**
3. **Gestire la cancellazione dei dati alla disinstallazione**
4. **Cancellare manualmente tutti i consensi o solo quelli scaduti**

## Funzionalità Implementate

### 1. Periodo di Retention Consensi

Gli amministratori possono configurare per quanto tempo i consensi devono essere conservati nel database:

- **1 mese** (30 giorni)
- **6 mesi** (180 giorni)
- **1 anno** (365 giorni) - *default*
- **2 anni** (730 giorni)
- **5 anni** (1825 giorni)
- **10 anni** (3650 giorni)

**Percorso:** WordPress Admin → Rubik GDPR → Impostazioni → Gestione Consensi

### 2. Pulizia Automatica (Cron Job)

Il sistema include un cron job che viene eseguito **giornalmente** alle 00:00 per rimuovere automaticamente i consensi scaduti secondo il periodo di retention configurato.

#### Attivazione Cron Job
- Si attiva automaticamente all'attivazione del plugin
- Si disattiva automaticamente alla disattivazione del plugin
- Viene rimosso completamente alla disinstallazione (se configurato)

#### Log delle Operazioni
Le operazioni di pulizia automatica vengono registrate nel log degli errori di WordPress per monitoraggio:
```
GDPR Cookie Consent: Cleaned up 15 expired consents (older than 365 days)
```

### 3. Cancellazione Manuale

Gli amministratori hanno accesso a due pulsanti per la cancellazione manuale:

#### 🗑️ Cancella Tutti i Consensi
- Rimuove **tutti** i consensi dal database
- Richiede conferma con warning di irreversibilità
- Mostra il numero di consensi cancellati

#### 🧹 Cancella Solo Consensi Scaduti
- Rimuove solo i consensi più vecchi del periodo di retention configurato
- Utilizza la stessa logica del cron job automatico
- Mostra il numero di consensi scaduti cancellati

### 4. Statistiche in Tempo Reale

La pagina delle impostazioni mostra statistiche aggiornate:

- **📊 Totale consensi nel database**
- **🕒 Consensi degli ultimi 7 giorni**
- **⏰ Consensi scaduti da cancellare** (in rosso se presenti)
- **✅ Stato pulizia** (verde se nessun consenso scaduto)

### 5. Cancellazione Dati alla Disinstallazione

Gli amministratori possono scegliere se cancellare tutti i dati alla disinstallazione del plugin:

#### Opzione Attivata
- Cancella tutte le tabelle del database (`manus_gdpr_consents`, `manus_gdpr_cookies`, `manus_gdpr_cookie_scans`)
- Rimuove tutte le opzioni e impostazioni
- Cancella i cron job programmati
- Rimuove i transient cache
- Supporta installazioni multisite

#### Opzione Disattivata (Default)
- I dati rimangono nel database
- Utile per reinstallazioni future
- Permette di mantenere lo storico dei consensi

## Implementazione Tecnica

### File Modificati/Creati

1. **`includes/class-manus-gdpr-admin.php`**
   - Aggiunto hook AJAX `handle_clear_consents_ajax()`
   - Migliorata visualizzazione statistiche

2. **`includes/class-manus-gdpr-database.php`**
   - `clear_all_consents()` - Cancella tutti i consensi
   - `clear_expired_consents($retention_days)` - Cancella consensi scaduti
   - `count_expired_consents($retention_days)` - Conta consensi scaduti

3. **`includes/class-manus-gdpr.php`**
   - Aggiunto hook cron `cleanup_expired_consents()`
   - Metodo `cleanup_expired_consents()` per pulizia automatica

4. **`includes/class-manus-gdpr-activator.php`**
   - Programmazione cron job giornaliero all'attivazione

5. **`includes/class-manus-gdpr-deactivator.php`**
   - Rimozione cron job alla disattivazione

6. **`uninstall.php`** *(nuovo)*
   - Gestione completa cancellazione dati alla disinstallazione
   - Supporto multisite

### Hook e Azioni WordPress

```php
// Cron job giornaliero
wp_schedule_event(time(), 'daily', 'manus_gdpr_cleanup_expired_consents');

// Hook AJAX per cancellazione manuale
add_action('wp_ajax_manus_gdpr_clear_consents', 'handle_clear_consents_ajax');

// Hook per esecuzione pulizia automatica
add_action('manus_gdpr_cleanup_expired_consents', 'cleanup_expired_consents');
```

### Sicurezza

- **Nonce verification** per tutte le richieste AJAX
- **Capability check** (`manage_options`) per operazioni admin
- **Data sanitization** per tutti gli input utente
- **Prepared statements** per query database

## Configurazione Consigliata

### Per Siti GDPR Compliant
- **Retention:** 1-2 anni (bilanciamento tra compliance e performance)
- **Delete on uninstall:** Attivato (per privacy by design)

### Per Siti con Analytics Avanzate
- **Retention:** 2-5 anni (per analisi storiche)
- **Delete on uninstall:** Disattivato (per mantenere dati)

### Per Siti di Test/Sviluppo
- **Retention:** 1 mese (pulizia frequente)
- **Delete on uninstall:** Attivato (ambiente pulito)

## Monitoraggio e Manutenzione

### Verifica Cron Job
```bash
# WordPress CLI - Lista cron jobs
wp cron event list

# Verifica job specifico
wp cron event list --hook=manus_gdpr_cleanup_expired_consents
```

### Debug Operazioni
Le operazioni di pulizia vengono registrate nel log WordPress:
```php
error_log("GDPR Cookie Consent: Cleaned up $deleted expired consents");
```

### Performance
- Il cron job è ottimizzato per grandi dataset
- Utilizza query con LIMIT per evitare timeout
- Le statistiche utilizzano query COUNT() ottimizzate

## Compatibilità

- **WordPress:** 5.0+
- **PHP:** 7.4+
- **Database:** MySQL 5.6+ / MariaDB 10.0+
- **Multisite:** Completamente supportato
- **GDPR/CCPA:** Conforme ai requisiti di retention

## Troubleshooting

### Il Cron Job Non Funziona
1. Verifica che WordPress cron sia attivo
2. Controlla che il server supporti cron jobs
3. Verifica log degli errori per messaggi di debug

### Cancellazione Non Funziona
1. Controlla permessi database
2. Verifica che l'utente abbia capability `manage_options`
3. Controlla nonce e AJAX security

### Performance Lente
1. Considera di indicizzare la colonna `timestamp` nella tabella consensi
2. Monitora la dimensione della tabella
3. Imposta retention più aggressiva per dataset grandi
