# Changelog

Tutte le modifiche significative al progetto verranno documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e questo progetto aderisce al [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.4] - 2025-01-25

### ✨ Nuove Funzionalità

#### 🎨 UI/UX Modernizzata
- **Glassmorphism design** con effetti sfocatura e trasparenza
- **Tema automatico** (light/dark/auto) basato su preferenze sistema
- **Layout responsive** con supporto safe area per dispositivi notch
- **Overlay configurabile** con colori personalizzabili
- **Floating icon** per riaprire modale preferenze sempre visibile
- **Animazioni fluide** con transizioni CSS ottimizzate

#### 🛠️ Gestione Avanzata Consensi
- **Retention policy configurabile** (da 1 mese a 10 anni)
- **Cancellazione massiva** di tutti i consensi via dashboard admin
- **Cancellazione selettiva** dei consensi scaduti
- **Pulizia automatica** tramite cron job giornaliero
- **Statistiche dettagliate** con conteggi in tempo reale

#### 🔐 Sicurezza e Conformità
- **Nonce verification** per tutte le operazioni AJAX
- **Capability check** per accesso alle funzionalità admin
- **Data sanitization** migliorata per tutti gli input
- **Prepared statements** per tutte le query database

### 🐛 Bugfix Critici

#### Database Schema Fix
- **Risolto errore** `Unknown column 'consent_date'` sostituendo con `timestamp`
- **Completate funzioni JS** per gestione AJAX cancellazione consensi
- **Corrette query SQL** per compatibilità con schema database attuale

#### Compatibilità WordPress
- **Sostituito** `get_page_by_title()` deprecato con `WP_Query`
- **Risolti warning PHP** per parametri mancanti
- **Aggiornate versioni** CSS/JS per cache busting

### 🔧 Miglioramenti Tecnici

#### Performance
- **Ottimizzato caricamento** CSS/JS condizionale
- **Ridotte query database** con caching intelligente
- **Migliorata gestione** eventi JavaScript

#### Amministrazione
- **Dashboard rinnovata** con cards statistiche
- **Filtri avanzati** nel log consensi
- **Ricerca per IP/utente** con paginazione
- **Feedback visivo** per operazioni in corso

#### API e Hooks
- **Nuovi action hooks** per estensibilità
- **Filtri configurabili** per personalizzazione
- **Metodi database** per manipolazione consensi
- **Eventi JavaScript** per integrazione third-party

### 📚 Documentazione

#### File Tecnici Aggiunti
- `CONSENT-RETENTION-DOCS.md` - Documentazione retention policy
- `SYNTAX-ERROR-FIX.md` - Guida risoluzione errori sintassi
- `CHANGELOG-DATABASE-FIX.md` - Dettagli fix database
- `test-consent-retention.php` - Script test funzionalità
- `test-syntax-fix.html` - Test interfaccia utente
- `test-database-fix.html` - Test operazioni database

#### README Aggiornato
- **Sezioni ampliate** con esempi pratici
- **Configurazioni avanzate** per multisite e CDN
- **Guida testing** per debug e validazione
- **API reference** completa con esempi

### 🚀 Funzionalità Operative

#### Cron Job Sistema
- **Attivazione automatica** al plugin enable
- **Pulizia giornaliera** consensi scaduti alle 00:00
- **Disattivazione sicura** al plugin disable
- **Logging operazioni** per monitoraggio

#### Gestione Uninstall
- **Opzione configurabile** per mantenere/eliminare dati
- **Pulizia completa** tabelle database
- **Rimozione cron jobs** e scheduled events
- **Reset impostazioni** WordPress

### ⚙️ Configurazioni Avanzate

#### Multisite Support
- **Configurazione condivisa** tra siti rete
- **Gestione centralizzata** da network admin
- **Ereditarietà impostazioni** configurabile

#### CDN Compatibility
- **URL rewriting** per risorse statiche
- **Cache exclusion** per pagine dinamiche
- **Performance optimization** per alto traffico

---

## [1.0.3] - 2024-12-15

### Aggiunte
- Supporto IAB TCF v2.2
- Floating icon per gestione preferenze
- Tema scuro/chiaro automatico

### Miglioramenti
- Interfaccia utente responsive
- Ottimizzazioni performance
- Compatibilità WordPress 6.4

### Correzioni
- Fix layout mobile
- Risolti conflitti CSS
- Migliorata accessibilità

---

## [1.0.2] - 2024-11-20

### Aggiunte
- Cookie scanner automatico
- Dashboard statistiche
- Export dati consensi

### Miglioramenti
- Velocità caricamento
- Compatibilità browser
- Traduzioni italiane

---

## [1.0.1] - 2024-10-10

### Aggiunte
- Gestione categorie cookie
- Personalizzazione colori
- Modalità rigorosa GDPR

### Correzioni
- Bug salvataggio impostazioni
- Conflitti plugin terze parti
- Problemi compatibilità PHP 8

---

## [1.0.0] - 2024-09-01

### Rilascio Iniziale
- Banner consenso cookie
- Gestione preferenze utente
- Conformità GDPR base
- Pannello amministrazione
- Documentazione del consenso

---

## 🔄 Prossimi Rilasci

### [1.1.0] - In Sviluppo
- **API REST** completa per integrazione esterna
- **Webhook** per notifiche consenso
- **Dashboard analytics** avanzata
- **Export/Import** configurazioni
- **Geolocalizzazione** automatica utenti

### [1.2.0] - Pianificato
- **A/B testing** banner design
- **Machine learning** per ottimizzazione conversioni
- **Integrazione CRM** (HubSpot, Salesforce)
- **CCPA support** completo
- **Multilingual** supporto automatico

---

## 📋 Note Sviluppo

### Versioning
Il progetto segue il [Semantic Versioning](https://semver.org/):
- **MAJOR**: Modifiche API incompatibili
- **MINOR**: Nuove funzionalità backwards-compatible
- **PATCH**: Bug fixes backwards-compatible

### Supporto
- **LTS**: Versioni pari (1.0.x, 1.2.x) - Supporto 2 anni
- **Standard**: Versioni dispari (1.1.x, 1.3.x) - Supporto 1 anno
- **Hotfix**: Patch critiche per tutte le versioni supportate

### Ambiente Test
- **WordPress**: 5.0+ fino a 6.5-dev
- **PHP**: 7.4, 8.0, 8.1, 8.2, 8.3
- **MySQL**: 5.6+ / MariaDB 10.0+
- **Browser**: Chrome, Firefox, Safari, Edge
