# TCF API Testing Fix

## Problema Risolto

Il test TCF nell'admin non funzionava perché cercava `window.__tcfapi` nella finestra dell'amministrazione di WordPress, mentre l'API TCF viene caricata solo nel frontend del sito.

## Soluzione Implementata

### 1. Test TCF via Iframe (Admin)
- Il test nell'admin ora crea un iframe invisibile che carica la homepage del sito
- Testa l'API TCF nel contesto del frontend tramite l'iframe
- Gestisce i problemi di cross-origin con messaggi informativi appropriati
- Fornisce feedback dettagliato e istruzioni di troubleshooting

### 2. Pulsante Test nell'Admin
- Aggiunto pulsante "Testa TCF API" nella pagina principale dell'admin
- Mostra lo stato dell'IAB TCF v2.2 (attivo/disattivo)
- Feedback visivo durante l'esecuzione del test

### 3. Gestione Cross-Origin
- Il test gestisce correttamente le restrizioni CORS
- Fornisce istruzioni alternative quando l'iframe non è accessibile
- Fallback con timeout e pulizia automatica delle risorse

## Come Usare il Test

### Dall'Admin
1. Vai alla pagina principale del plugin GDPR Cookie Consent
2. Cerca la sezione "IAB TCF v2.2"
3. Clicca sul pulsante "Testa TCF API"
4. Controlla la console del browser per i risultati

### Dal Frontend
1. Apri qualsiasi pagina pubblica del sito
2. Apri la console del browser
3. Esegui: `testTCFAPI()`

### Via Console Admin
```javascript
ManusGDPRAdmin.testTCFAPI()
```

## Output del Test

Il test verifica:
- ✅ Presenza dell'API `__tcfapi`
- 📡 Comando `ping` (info CMP e stato)
- 📊 Comando `getTCData` (TC string e consensi)
- 👂 Comando `addEventListener` (event listeners)

## Troubleshooting

### Cross-Origin Issues
Se vedi avvisi cross-origin, è normale per motivi di sicurezza del browser. Usa il test diretto nel frontend.

### API Non Trovata
1. Verifica che IAB TCF v2.2 sia abilitato nelle impostazioni
2. Controlla che la pagina frontend si carichi correttamente
3. Verifica che non ci siano errori JavaScript nella console

### Test Timeout
Se il test va in timeout:
1. Verifica la connettività del sito
2. Controlla se ci sono plugin che bloccano gli iframe
3. Usa il test manuale nel frontend

## File Modificati

- `/admin/js/manus-gdpr-admin.js` - Test TCF migliorato con iframe
- `/admin/partials/manus-gdpr-admin-display.php` - Aggiunto pulsante test e stato TCF

## Vantaggi della Soluzione

1. **Test Accurato**: Testa l'API nel contesto corretto (frontend)
2. **User Friendly**: Pulsante nell'admin per test rapido
3. **Diagnostica Completa**: Feedback dettagliato e istruzioni
4. **Gestione Errori**: Fallback e cleanup automatico
5. **Cross-Origin Safe**: Gestisce correttamente le restrizioni browser
