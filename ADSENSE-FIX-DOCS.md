# GDPR Cookie Consent - Correzione Problema AdSense

## Problema Risolto

Il plugin aveva un problema con la gestione del consenso per Google AdSense: quando l'utente cliccava su "Rifiuta", le pubblicità venivano comunque caricate perché il segnale TCF (Transparency and Consent Framework) IAB non veniva gestito correttamente.

## 🚀 Aggiornamenti v1.0.9 (LATEST)

### 🔧 **Correzioni Critiche**

#### 1. **Funzioni Globali Non Definite**
**Errori risolti:**
- `Uncaught ReferenceError: getCookie is not defined`
- `Uncaught ReferenceError: setCookie is not defined` 

**Problema:** Le funzioni `getCookie` e `setCookie` erano disponibili solo nel contesto del plugin, ma venivano chiamate da script esterni (LiteSpeed, altri plugin, temi).

**Soluzione:**
- Rese globali `window.getCookie` e `window.setCookie`
- Aggiunta nella `<head>` con priorità massima (prima di tutti gli altri script)
- Disponibili a **tutti** gli script della pagina

#### 2. **Nuova Funzione Helper Globale** 🆕
**Nuova funzione:** `window.hasGDPRConsent(category)`

**Utilizzo per sviluppatori:**
```javascript
// Verifica consenso pubblicitario
if (window.hasGDPRConsent('advertising')) {
    // Carica script pubblicitari
}

// Verifica consenso analytics
if (window.hasGDPRConsent('analytics')) {
    // Inizializza tracking
}
```

#### 3. **Compatibilità Universale** ✨
- **LiteSpeed Cache:** Risolti errori con script delayed
- **Temi e Plugin:** Funzioni disponibili ovunque
- **Script Inline:** Accesso garantito alle utility GDPR

---

## 🚀 Aggiornamenti v1.0.8

### ✅ **Correzioni Precedenti**

#### 1. **Ricaricamento Automatico per Pubblicità** 🔄
**Funzionalità:** Quando l'utente modifica le sue preferenze e abilita le pubblicità, la pagina viene automaticamente ricaricata.

**Come funziona:**
- Controllo se il consenso pubblicitario cambia da `false` a `true`
- Notifica visiva: "✓ Consenso salvato! Ricaricamento per abilitare le pubblicità..."
- Ricaricamento automatico dopo 1.5 secondi

#### 2. **Esperienza Utente Migliorata** ✨
- Notifica di conferma prima del reload
- Feedback visivo chiaro sulle azioni
- Gestione intelligente dei cambiamenti di consenso

---

## Errori Corretti nelle Versioni Precedenti

### 1. **Parsing Cookie URL-Encoded**
**Errore:** `SyntaxError: Unexpected token '%', "%7B%22stat"... is not valid JSON`

**Causa:** I cookie vengono salvati in formato URL-encoded ma il codice tentava di fare il parsing diretto senza decodificarli.

**Correzione:** Aggiunta funzione `decodeURIComponent()` prima del parsing JSON in tutte le funzioni che leggono i cookie.

### 2. **Conflitto nella Logica del Consenso**
**Errore:** TCF mostrava consensi contraddittori (prima rifiutati, poi accettati).

**Causa:** Logica di fallback che sovrascriveva i consensi espliciti.

**Correzione:** Rivista la priorità di lettura dei cookie: prima `manus_gdpr_consent_data`, poi `manus_gdpr_consent`.

### 3. **Errori di Rete AdSense**
**Errore:** `Uncaught (in promise) Error: Blocked by GDPR consent`

**Causa:** Il blocco delle richieste AdSense causava errori JavaScript non catturati.

**Stato:** ✅ **Funziona correttamente** - Questo errore indica che il plugin sta effettivamente bloccando le richieste pubblicitarie quando il consenso è rifiutato.

## Modifiche Apportate

### 1. Miglioramento del TCF v2.2 IAB

**File modificato:** `includes/class-manus-gdpr-frontend.php`

- **Funzione `generateTCString()`**: Migliorata per gestire cookie URL-encoded
- **Funzione `handleGetTCData()`**: Corretta per parsing robusto dei cookie
- **Funzione `updateTCFStatus()`**: Potenziata per notificare Google AdSense
- **Funzione `shouldBlockAdvertisingRequest()`**: Aggiunta gestione errori nel parsing

### 2. Blocco Attivo degli Script Pubblicitari

**Nuova funzione:** `add_adsense_blocking_script()`

- Intercetta richieste `fetch()` e `XMLHttpRequest` per domini pubblicitari
- Blocca attivamente le chiamate a googlesyndication.com, doubleclick.net, etc.
- Pulisce automaticamente i cookie pubblicitari quando il consenso è rifiutato

### 3. Gestione Cookie URL-Encoded

**File modificato:** `public/js/manus-gdpr-public.js`

- **Funzione `getCurrentConsentData()`**: Aggiunta gestione robusta per cookie URL-encoded
- Doppio tentativo di parsing: prima con `decodeURIComponent()`, poi senza
- Gestione errori migliorata con fallback multipli

### 4. Debug e Logging Migliorato

- Messaggi di errore più informativi
- Logging separato per ogni tentativo di parsing
- Informazioni dettagliate sullo stato del consenso

## Come Testare la Correzione

1. **Test Base:**
   - Aprire il sito con AdSense
   - Cliccare su "Rifiuta" nel banner GDPR
   - Verificare che le pubblicità NON vengano caricate
   - ✅ Gli errori "Blocked by GDPR consent" nella console sono **normali** e indicano che il blocco funziona

2. **Test con Debug:**
   - Aggiungere `?gdpr_debug=1` all'URL
   - Verificare che "Advertising Consent: NO" sia mostrato
   - Controllare la console del browser - dovrebbero esserci meno errori di parsing

3. **Test Cookie Parsing:**
   - Console del browser: `document.cookie`
   - Verificare che i cookie contengano `%7B` (URL-encoded `{`)
   - Il plugin ora dovrebbe decodificarli correttamente

## Messaggi della Console - Cosa è Normale

### ✅ **Messaggi OK (indicano funzionamento corretto):**
- `GDPR: Blocked advertising fetch request: https://...`
- `GDPR TCF: Generating TC String with consent data: {advertising: false}`
- `GDPR TCF: Final TCF purpose consents: {2: false, 3: false, 4: false}`
- `Blocked by GDPR consent` (nelle Promise)

### ⚠️ **Messaggi di Warning (gestiti correttamente):**
- `GDPR TCF: Error parsing consent data cookie (trying fallback)`
- `GDPR: Error parsing consent for request blocking`

### ❌ **Errori da Correggere:**
- `SyntaxError: Unexpected token '%'` ← Dovrebbe essere risolto
- Consensi contraddittori nel TCF ← Dovrebbe essere risolto

## Compatibilità

- ✅ Google AdSense
- ✅ Google Ad Manager (DFP)  
- ✅ Facebook Pixel
- ✅ Google Analytics
- ✅ IAB TCF v2.2
- ✅ Cookie URL-encoded e non-encoded
- ✅ Tutti i browser moderni

## Note Tecniche v1.0.7

La correzione utilizza un approccio multi-livello con gestione errori robusta:

1. **Livello Cookie**: Parsing robusto con fallback multipli
2. **Livello TCF**: Segnali corretti alle librerie pubblicitarie  
3. **Livello Rete**: Blocco delle richieste HTTP/HTTPS
4. **Livello DOM**: Rimozione/sostituzione elementi pubblicitari
5. **Livello Storage**: Pulizia cookie e storage locali

Il plugin ora gestisce correttamente tutti i formati di cookie e fornisce feedback chiaro sul funzionamento tramite i log della console.

**Nuova funzione:** `manus_gdpr_debug_info()`

- Aggiungere `?gdpr_debug=1` all'URL per vedere lo stato del consenso
- Mostra informazioni dettagliate su cookie e consensi TCF
- Disponibile solo per amministratori

## Come Testare la Correzione

1. **Test Base:**
   - Aprire il sito con AdSense
   - Cliccare su "Rifiuta" nel banner GDPR
   - Verificare che le pubblicità NON vengano caricate

2. **Test con Debug:**
   - Aggiungere `?gdpr_debug=1` all'URL
   - Verificare che "Advertising Consent: NO" sia mostrato
   - Controllare la console del browser per i log TCF

3. **Test TCF API:**
   - Aprire la console del browser
   - Eseguire `window.testTCFAPI()` per vedere i dati TCF
   - Verificare che `tcData.purpose.consents[2]` sia `false` quando rifiutato

## Differenze Principali

### Prima della Correzione
- Il TCF inviava segnali ambigui ad AdSense
- Gli script pubblicitari venivano bloccati solo parzialmente
- Non c'era differenza pratica tra "Accetta" e "Rifiuta"

### Dopo la Correzione
- Il TCF invia segnali espliciti e chiari
- Blocco proattivo di richieste di rete pubblicitarie
- Pulizia automatica di cookie e storage pubblicitari
- Monitoraggio in tempo reale degli elementi AdSense
- Comunicazione diretta con Google Ad Manager

## Compatibilità

- ✅ Google AdSense
- ✅ Google Ad Manager (DFP)
- ✅ Facebook Pixel
- ✅ Google Analytics
- ✅ IAB TCF v2.2
- ✅ Tutti i browser moderni

## Note Tecniche

La correzione utilizza un approccio multi-livello:

1. **Livello TCF**: Segnali corretti alle librerie pubblicitarie
2. **Livello Rete**: Blocco delle richieste HTTP/HTTPS
3. **Livello DOM**: Rimozione/sostituzione elementi pubblicitari
4. **Livello Storage**: Pulizia cookie e storage locali

Questo garantisce che il rifiuto del consenso sia rispettato a tutti i livelli del sistema.
