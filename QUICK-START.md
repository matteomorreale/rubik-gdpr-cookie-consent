# 🚀 Guida Rapida di Installazione

> Installa e configura Rubik GDPR Cookie Consent in meno di 5 minuti!

## ⚡ Setup Express (2 minuti)

### 1. Scarica e Installa

```bash
# Opzione A: WordPress Admin
WordPress Admin → Plugin → Aggiungi nuovo → Carica → rubik-gdpr.zip

# Opzione B: FTP/cPanel
Estrai in: /wp-content/plugins/rubik-gdpr-cookie-consent/

# Opzione C: WP-CLI
wp plugin install rubik-gdpr-cookie-consent --activate
```

### 2. Attivazione Immediata

1. Vai su **Plugin** → **Plugin installati**
2. Trova **Rubik GDPR Cookie Consent**
3. Clicca **Attiva**
4. ✅ **Fatto!** Il banner apparirà automaticamente

## ⚙️ Configurazione Base (3 minuti)

### Step 1: Impostazioni Principali

Vai su **WordPress Admin** → **Rubik GDPR**

```text
✅ Abilita banner cookie          [ON]
📝 Messaggio personalizzato      [Modifica a piacere]
📍 Posizione banner             [In basso - consigliato]
🎨 Tema                         [Auto - si adatta al sistema]
```

### Step 2: Categorie Cookie

```text
✅ Cookie Necessari              [Sempre ON - obbligatorio]
✅ Cookie Analitici             [ON - Google Analytics]
✅ Cookie Pubblicitari          [ON - Google Ads]
✅ Cookie Funzionali            [ON - Chat, mappe]
```

### Step 3: Retention Policy

```text
🕒 Conservazione consensi: [1 anno - consigliato]
🗑️ Cancella alla disinstallazione: [A tua scelta]
```

### Step 4: Salva e Testa

1. Clicca **Salva modifiche**
2. Apri il sito in **navigazione privata**
3. Verifica che il banner appaia
4. Testa accetta/rifiuta/personalizza

## 🎯 Configurazioni Avanzate

### Per Siti Aziendali

```php
// wp-config.php - Ottimizzazioni performance
define('MANUS_GDPR_CACHE_ENABLE', true);
define('MANUS_GDPR_LAZY_LOAD_SCRIPTS', true);
```

### Per Multisito

```php
// wp-config.php - Gestione centralizzata
define('MANUS_GDPR_MULTISITE_SHARED_CONFIG', true);
define('MANUS_GDPR_NETWORK_ADMIN_ONLY', true);
```

### Per Sviluppatori

```php
// functions.php - Hook personalizzati
add_action('manus_gdpr_consent_recorded', function($data) {
    // La tua logica personalizzata
    if ($data['consent_status'] === 'accepted') {
        // Attiva servizi completi
    }
});
```

## 🧪 Verifica Conformità

### Checklist GDPR ✅

- [x] Banner di consenso presente
- [x] Opzioni granulari per categoria
- [x] Possibilità di rifiutare
- [x] Revoca consenso (floating icon)
- [x] Documentazione con timestamp
- [x] Retention policy configurata

### Test IAB TCF v2.2

Apri console browser e digita:

```javascript
// Test API TCF
window.testTCFAPI();

// Verifica TC String
window.__tcfapi('getTCData', 2, function(data) {
    console.log('TC String:', data.tcString);
});
```

## 🔧 Risoluzione Problemi Express

### ❌ Banner non appare

```text
1. Vai su Rubik GDPR → Impostazioni
2. Verifica "Abilita banner cookie" sia ✅
3. Svuota cache (WP Rocket, W3TC, etc.)
4. Testa in navigazione privata
```

### ❌ Conflitti CSS

```css
/* Aggiungi in Aspetto → Personalizza → CSS */
.manus-gdpr-banner {
    z-index: 999999 !important;
}
```

### ❌ JavaScript Errors

```text
1. Disattiva altri plugin temporaneamente
2. Testa con tema di default (Twenty Twenty-Four)
3. Controlla console per errori specifici
4. Apri issue su GitHub con dettagli
```

## 📊 Monitoraggio Post-Installazione

### Dashboard Admin

Vai su **Rubik GDPR** → **Registro Consensi** per vedere:

- 📈 **Consensi totali** raccolti
- ✅ **Tasso di accettazione**
- ⚖️ **Preferenze parziali**
- 🕒 **Consensi recenti**

### Statistiche Utili

```text
📊 Consensi totali: [visualizzato in dashboard]
🔄 Conversione banner: [accept rate %]
🧹 Pulizia automatica: [ogni giorno alle 00:00]
💾 Storage utilizzato: [visualizza dimensione DB]
```

## 🎨 Personalizzazioni Rapide

### Stile Banner Personalizzato

```css
/* Tema scuro personalizzato */
.manus-gdpr-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white !important;
    border-radius: 15px !important;
}

/* Pulsanti colorati */
.manus-gdpr-accept {
    background: #00a32a !important;
    border-color: #00a32a !important;
}

.manus-gdpr-reject {
    background: #d63638 !important;
    border-color: #d63638 !important;
}
```

### Messaggi Personalizzati

Vai su **Rubik GDPR** → **Impostazioni** e personalizza:

```text
🍪 "Utilizziamo cookie per migliorare la tua esperienza. 
    Scegli quali accettare per una navigazione ottimale."
    
🔗 "Consulta la nostra [privacy policy] per maggiori dettagli."

⚙️ "Puoi modificare le tue preferenze in qualsiasi momento 
    cliccando l'icona cookie in basso a destra."
```

## 📞 Supporto Rapido

### 🆘 Problemi Comuni

| Problema | Soluzione Rapida |
|----------|------------------|
| Banner non visibile | Controlla cache + navigation privata |
| Conflitti theme | Testa con tema default |
| JS errors | Disattiva altri plugin temporaneamente |
| Mobile issues | Verifica responsive in DevTools |

### 🔗 Link Utili

- 📚 [Documentazione Completa](README.md)
- 🐛 [Report Bug](https://github.com/yourusername/rubik-gdpr-cookie-consent/issues)
- 💡 [Feature Request](https://github.com/yourusername/rubik-gdpr-cookie-consent/discussions)
- 📧 [Supporto Diretto](mailto:support@rubik-gdpr.com)

---

## ✅ Checklist Completamento

Hai completato l'installazione quando:

- [x] Plugin attivato e configurato
- [x] Banner appare correttamente
- [x] Tutte le opzioni funzionano (accetta/rifiuta/personalizza)
- [x] Floating icon visibile dopo consenso
- [x] Dashboard admin accessibile
- [x] Statistiche vengono registrate
- [x] Conformità GDPR verificata

**🎉 Congratulazioni! Rubik GDPR Cookie Consent è operativo sul tuo sito!**

---

*Tempo totale installazione: ~5 minuti | Difficoltà: Principiante | Supporto: Completo*
