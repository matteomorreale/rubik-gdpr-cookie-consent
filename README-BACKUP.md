# Rubik GDPR Cookie Consent 🍪

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple## 📊 Dashboard e Statistiche

Il pannello admin fornisce una panoramica completa dei consensi:

### Statistiche in Tempo Reale

- 📈 **Consensi totali** registrati nel database
- ✅ **Consensi accettati** (tutti i cookie abilitati)
- ❌ **Consensi rifiutati** (solo cookie necessari)
- ⚖️ **Consensi parziali** (categorie selettive)
- 🕒 **Consensi recenti** (ultimi 7/30 giorni)
- ⏰ **Consensi scaduti** da cancellare automaticamente

### Gestione Avanzata

- **Pulizia automatica** via cron job giornaliero
- **Cancellazione massiva** di tutti i consensi
- **Cancellazione selettiva** dei consensi scaduti  
- **Filtri avanzati** per stato e periodo
- **Ricerca** per IP address o ID utentetps://php.net/)
[![License](https://img.shields.io/badge/License-GPL2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.4-orange.svg)](https://github.com/yourusername/rubik-gdpr-cookie-consent/releases)

> **Plugin WordPress moderno e completo per la gestione del consenso ai cookie conforme GDPR/CCPA con supporto IAB TCF v2.2**

Rubik GDPR Cookie Consent è un plugin WordPress professionale che offre una soluzione completa per la gestione del consenso ai cookie, progettato per garantire la conformità alle normative GDPR, CCPA e altri regolamenti sulla privacy a livello mondiale.

![Banner Preview](screenshot-banner.png)

## ✨ Caratteristiche Principali

### 🎨 **Design Moderno e Accessibile**
- **Glassmorphism UI** con effetti di sfocatura e trasparenza
- **Tema automatico** che si adatta alle preferenze del sistema (light/dark/auto)
- **Layout responsive** ottimizzato per tutti i dispositivi
- **Safe Area Support** per dispositivi con notch (iPhone X+, macOS)
- **Animazioni fluide** con transizioni CSS ottimizzate

### 🛡️ **Conformità Normative**
- **GDPR compliant** (Regolamento Generale sulla Protezione dei Dati)
- **CCPA ready** (California Consumer Privacy Act)
- **IAB TCF v2.2** (Transparency & Consent Framework)
- **Documentazione del consenso** con timestamp e IP tracking
- **Retention policy** configurabile per i dati di consenso

### ⚙️ **Gestione Avanzata dei Consensi**
- **Categorie personalizzabili** (Necessari, Analitici, Pubblicitari, Funzionali)
- **Consenso granulare** per ogni categoria di cookie
- **Floating icon** per modificare le preferenze in qualsiasi momento
- **Pulizia automatica** dei consensi scaduti tramite cron job
- **Export/Import** delle configurazioni

### 🔧 **Funzionalità Amministrative**
- **Dashboard intuitiva** con statistiche in tempo reale (totali, accettati, rifiutati, parziali)
- **Cookie scanner automatico** per rilevare tutti i cookie del sito
- **Gestione avanzata consensi** con cancellazione massiva/selettiva e pulizia automatica scaduti
- **Retention policy configurabile** (da 1 mese a 10 anni) con cron job automatico
- **Log dettagliato** con filtri per stato, ricerca per IP/utente e paginazione
- **Test integrati TCF v2.2** per verificare la conformità IAB

## 🚀 Installazione Rapida

### Metodo 1: Via WordPress Admin

1. Scarica il file `.zip` dalla [pagina releases](https://github.com/yourusername/rubik-gdpr-cookie-consent/releases)
2. Vai su **WordPress Admin** → **Plugin** → **Aggiungi nuovo**
3. Clicca su **Carica plugin** e seleziona il file `.zip`
4. Attiva il plugin
5. Vai su **Rubik GDPR** → **Impostazioni** per configurare

### Metodo 2: Via FTP

```bash
# Clona il repository
git clone https://github.com/yourusername/rubik-gdpr-cookie-consent.git

# Carica nella directory dei plugin WordPress
cp -r rubik-gdpr-cookie-consent /path/to/wordpress/wp-content/plugins/

# Attiva tramite WP-CLI (opzionale)
wp plugin activate rubik-gdpr-cookie-consent
```

### Metodo 3: Via Composer

```bash
composer require yourusername/rubik-gdpr-cookie-consent
```

> **Importante**: Dopo l'installazione, vai su **WordPress Admin** → **Rubik GDPR** per completare la configurazione iniziale.

## 📖 Configurazione Iniziale

### 1. Configurazione Base

Dopo l'attivazione, vai su **WordPress Admin** → **Rubik GDPR** → **Impostazioni**:

- ✅ **Abilita banner cookie**
- 📝 **Personalizza il messaggio**
- 🎨 **Scegli tema e layout**
- 📍 **Imposta posizione del banner**

### 2. Categorie di Cookie

Configura le categorie secondo le tue necessità:

```php
// Categorie predefinite
$categories = [
    'necessary'   => 'Cookie Necessari',     // Sempre attivi
    'analytics'   => 'Cookie Analitici',    // Google Analytics, etc.
    'advertising' => 'Cookie Pubblicitari', // Google Ads, Facebook, etc.
    'functional'  => 'Cookie Funzionali'    // Chat, mappe, etc.
];
```

### 3. Retention Policy

Imposta per quanto tempo conservare i consensi:

- 📅 **30 giorni** - Per test e sviluppo
- 📅 **6 mesi** - Configurazione base per siti piccoli
- 📅 **1 anno** - Configurazione consigliata
- 📅 **2-10 anni** - Per analisi storiche approfondite e conformità legale

## 🎯 Esempi di Utilizzo

### Banner di Consenso Personalizzato

```css
/* CSS personalizzato per il banner */
.manus-gdpr-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.manus-gdpr-button {
    border-radius: 25px;
    transition: all 0.3s ease;
}

.manus-gdpr-accept:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
```

### Integrazione JavaScript

```javascript
// Ascolta eventi di consenso
document.addEventListener('manus-gdpr-consent-updated', function(event) {
    const { consentStatus, consentData } = event.detail;
    
    console.log('Consenso aggiornato:', consentStatus);
    console.log('Dettagli:', consentData);
    
    // Attiva/disattiva servizi in base al consenso
    if (consentData.analytics) {
        // Inizializza Google Analytics
        gtag('config', 'GA_MEASUREMENT_ID');
    }
    
    if (consentData.advertising) {
        // Inizializza Google Ads
        gtag('config', 'AW-CONVERSION_ID');
    }
});

// Test API TCF (se abilitata)
if (typeof window.__tcfapi === 'function') {
    window.__tcfapi('getTCData', 2, function(tcData, success) {
        if (success) {
            console.log('TCF Data:', tcData);
            console.log('Consent String:', tcData.tcString);
        }
    });
}
```

### Hook WordPress

```php
// Hook per personalizzare il comportamento
add_action('manus_gdpr_consent_recorded', function($consent_data) {
    // Logica personalizzata dopo il consenso
    if ($consent_data['consent_status'] === 'accepted') {
        // Attiva servizi completi
        update_option('enable_full_tracking', true);
    }
});

// Filtro per modificare le categorie
add_filter('manus_gdpr_cookie_categories', function($categories) {
    $categories['social'] = 'Cookie Social Media';
    return $categories;
});
```

## 📊 Dashboard e Statistiche

### Statistiche Disponibili

- 📈 **Consensi totali** registrati
- 🕒 **Consensi recenti** (ultimi 7 giorni)
- ⏰ **Consensi scaduti** da cancellare
- 📊 **Distribuzione per tipo** (accettati/rifiutati/parziali)
- 🌍 **Geolocalizzazione** degli utenti (se abilitata)

### Esportazione Dati

```bash
# Via WordPress Admin
WordPress Admin → Rubik GDPR → Consent Log → [Filtri] → Export CSV

# Via WP-CLI (opzionale)
wp gdpr export-consents --format=csv --file=consensi-2025.csv
```

## 🔧 API e Sviluppatori

### Hooks WordPress Disponibili

```php
/**
 * Actions disponibili
 */

// Dopo la registrazione di un consenso
do_action('manus_gdpr_consent_recorded', $consent_data);

// Quando viene mostrato il banner
do_action('manus_gdpr_banner_displayed');

// All'apertura della modale preferenze
do_action('manus_gdpr_modal_opened');

// Durante la pulizia automatica dei consensi scaduti (cron)
do_action('manus_gdpr_expired_consents_cleaned', $deleted_count);

/**
 * Filtri disponibili
 */

// Personalizza il messaggio del banner
$message = apply_filters('manus_gdpr_banner_message', $message);

// Modifica le categorie di cookie disponibili
$categories = apply_filters('manus_gdpr_cookie_categories', $categories);

// Cambia il periodo di retention dei consensi
$retention_days = apply_filters('manus_gdpr_retention_period', $days);

// Personalizza i colori del tema
$colors = apply_filters('manus_gdpr_theme_colors', $colors);
```

### Metodi della Classe Database

```php
// Via WordPress Admin Dashboard
Manus_GDPR_Database::record_consent($user_id, $ip, $status, $data);

// Ottieni statistiche complete
$stats = Manus_GDPR_Database::get_consent_statistics();
// Ritorna: ['total' => X, 'accepted' => Y, 'rejected' => Z, 'partial' => W, 'recent' => R]

// Pulisci consensi scaduti (tramite cron o manualmente)
$deleted = Manus_GDPR_Database::clear_expired_consents(365);

// Conta consensi da eliminare
$expired_count = Manus_GDPR_Database::count_expired_consents(365);
```

### API REST Endpoints

```http
GET    /wp-json/manus-gdpr/v1/consents      # Lista tutti i consensi (admin)
POST   /wp-json/manus-gdpr/v1/consent       # Registra nuovo consenso
DELETE /wp-json/manus-gdpr/v1/consents/{id} # Elimina consenso specifico
GET    /wp-json/manus-gdpr/v1/statistics    # Statistiche dei consensi
POST   /wp-json/manus-gdpr/v1/clean-expired # Pulisci consensi scaduti
```

> **Nota**: Gli endpoint API REST sono attualmente in sviluppo per versioni future del plugin.

## 🛠️ Configurazioni Avanzate

### Personalizzazione CSS

Puoi personalizzare l'aspetto del plugin tramite CSS personalizzato nel pannello admin o nel tuo tema:

```css
/* Personalizza il banner */
.manus-gdpr-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
}

/* Floating icon personalizzato */
.manus-gdpr-floating-icon {
    background: var(--wp-admin-theme-color, #0073aa) !important;
    box-shadow: 0 4px 12px rgba(0,115,170,0.4) !important;
}

/* Dark mode avanzato */
[data-theme="dark"] .manus-gdpr-banner {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
    color: #f7fafc !important;
}
```

### Configurazione Multisite WordPress

Per reti WordPress multisite, aggiungi queste configurazioni:

```php
// wp-config.php - Configurazione multisite
define('MANUS_GDPR_MULTISITE_SHARED_CONFIG', true);
define('MANUS_GDPR_NETWORK_ADMIN_ONLY', false);
define('MANUS_GDPR_INHERIT_NETWORK_SETTINGS', true);
```

### Performance Ottimizzata

Per siti ad alto traffico, abilita le ottimizzazioni:

```php
// wp-config.php - Ottimizzazioni performance
define('MANUS_GDPR_CACHE_ENABLE', true);
define('MANUS_GDPR_LAZY_LOAD_SCRIPTS', true);
define('MANUS_GDPR_COMPRESS_OUTPUT', true);
define('MANUS_GDPR_ASYNC_CONSENT_LOGGING', true);
```

### CDN e Caching

Configurazione per compatibilità con CDN:

```php
// functions.php del tema - Supporto CDN
add_filter('manus_gdpr_static_urls', function($urls) {
    return str_replace(home_url(), 'https://cdn.example.com', $urls);
});

// Esclusione da cache per pagine dinamiche
add_filter('manus_gdpr_no_cache_pages', function($pages) {
    $pages[] = 'consent-preferences';
    return $pages;
});
```

### 🧪 Testing e Debug

#### Test di Conformità

```bash
# Test del banner e modale
open test-syntax-fix.html  # Test interfaccia utente

# Test del database
open test-database-fix.html  # Test operazioni CRUD

# Test retention policy
php test-consent-retention.php  # Test pulizia automatica
```

#### Debug Mode

```php
// wp-config.php - Abilita debug GDPR
define('MANUS_GDPR_DEBUG', true);
define('MANUS_GDPR_LOG_LEVEL', 'debug');

// Verifica tabelle database
global $wpdb;
$table = $wpdb->prefix . 'manus_gdpr_consents';
echo $wpdb->get_var("SHOW TABLES LIKE '$table'");
```

#### Validazione TCF v2.2

```javascript
// Console del browser - Test API TCF
window.testTCFAPI(); // Funzione di test integrata

// Verifica TC String generation
console.log(window.__tcfapi);

// Test event listeners
window.__tcfapi('addEventListener', 2, function(tcData, success) {
    console.log('TCF Event:', tcData, success);
});
```

## 📱 Compatibilità

### Browser Supportati

| Browser | Versione | Status |
|---------|----------|---------|
| Chrome  | 80+      | ✅ Full |
| Firefox | 75+      | ✅ Full |
| Safari  | 13+      | ✅ Full |
| Edge    | 80+      | ✅ Full |
| IE      | 11       | ⚠️ Basic |

### WordPress

- **WordPress**: 5.0+ (testato fino a 6.5)
- **PHP**: 7.4+ (raccomandato: 8.1+)
- **MySQL**: 5.6+ / MariaDB 10.0+
- **Multisite**: ✅ Completamente supportato

### Plugin Compatibili

- ✅ **WooCommerce** 5.0+
- ✅ **Yoast SEO**
- ✅ **Elementor**
- ✅ **WP Rocket**
- ✅ **W3 Total Cache**
- ✅ **CloudFlare**

## 🤝 Contribuire

Aiutaci a migliorare Rubik GDPR Cookie Consent!

### 1. Setup Sviluppo

```bash
# Fork e clona il repository
git clone https://github.com/yourusername/rubik-gdpr-cookie-consent.git
cd rubik-gdpr-cookie-consent

# Installa dipendenze
npm install
composer install

# Setup ambiente di sviluppo
npm run dev:setup
```

### 2. Linee Guida

- 📝 **Codice**: Segui gli [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- 🧪 **Test**: Aggiungi test per nuove funzionalità
- 📖 **Documentazione**: Aggiorna docs per modifiche API
- 🌍 **i18n**: Aggiungi traduzioni per nuove stringhe

### 3. Pull Request Process

1. Crea un branch per la tua feature: `git checkout -b feature/amazing-feature`
2. Commit delle modifiche: `git commit -m 'Add amazing feature'`
3. Push del branch: `git push origin feature/amazing-feature`
4. Apri una Pull Request

## 📄 Licenza

Questo progetto è licenziato sotto la **GNU General Public License v2.0**.

```text
Copyright (C) 2025 Matteo Morreale

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

Vedi il file [LICENSE](LICENSE) per i dettagli completi.

## 🎉 Riconoscimenti

### Tecnologie Utilizzate

- **WordPress Plugin API** - Framework base
- **IAB TCF v2.2** - Standard per il consenso pubblicitario
- **CSS Grid & Flexbox** - Layout responsive
- **Vanilla JavaScript** - Performance ottimizzate
- **MySQL/MariaDB** - Storage dati

### Ispirazioni

- [GDPR Cookie Compliance](https://wordpress.org/plugins/gdpr-cookie-compliance/)
- [Cookiebot](https://www.cookiebot.com/)
- [OneTrust](https://www.onetrust.com/)

### Contributors

[Vedi tutti i contributori su GitHub](https://github.com/yourusername/rubik-gdpr-cookie-consent/graphs/contributors)

## 🆘 Supporto

### 📚 Documentazione

- [Guida Utente Completa](docs/user-guide.md)
- [Documentazione API](docs/api-reference.md)
- [FAQ](docs/faq.md)
- [Troubleshooting](docs/troubleshooting.md)

### 💬 Community

- **Forum di Supporto**: [community.rubik-gdpr.com](https://community.rubik-gdpr.com)
- **Discord**: [discord.gg/rubik-gdpr](https://discord.gg/rubik-gdpr)
- **Stack Overflow**: Tag `rubik-gdpr`

### 🐛 Bug Reports

Hai trovato un bug? Aiutaci a risolverlo!

1. Controlla se esiste già una [issue simile](https://github.com/yourusername/rubik-gdpr-cookie-consent/issues)
2. Apri una [nuova issue](https://github.com/yourusername/rubik-gdpr-cookie-consent/issues/new) con:
   - Descrizione dettagliata del problema
   - Passi per riprodurre il bug
   - Screenshot o video (se utili)
   - Informazioni sull'ambiente (WordPress, PHP, browser)

### 💡 Feature Requests

Hai un'idea per migliorare il plugin?

- Apri una [Feature Request](https://github.com/yourusername/rubik-gdpr-cookie-consent/issues/new?template=feature_request.md)
- Partecipa alle [Discussions](https://github.com/yourusername/rubik-gdpr-cookie-consent/discussions)
- Vota le feature più richieste

---

---

## 🍪 Fatto con ❤️ per la privacy e la conformità web

[Website](https://rubik-gdpr.com) • [Documentation](docs/) • [Support](https://github.com/yourusername/rubik-gdpr-cookie-consent/issues) • [License](LICENSE)

⭐ Se questo progetto ti è stato utile, lascia una stella su GitHub!
