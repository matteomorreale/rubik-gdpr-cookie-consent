# Contribuire a Rubik GDPR Cookie Consent

Grazie per il tuo interesse nel contribuire a Rubik GDPR Cookie Consent! Questo documento fornisce le linee guida per contribuire al progetto.

## 🚀 Come Iniziare

### 1. Setup Ambiente di Sviluppo

```bash
# Fork del repository
git clone https://github.com/yourusername/rubik-gdpr-cookie-consent.git
cd rubik-gdpr-cookie-consent

# Crea un branch per la tua feature
git checkout -b feature/nome-feature

# Setup ambiente WordPress locale
# Usa Local by Flywheel, XAMPP, o simili
```

### 2. Struttura del Progetto

```text
rubik-gdpr-cookie-consent/
├── manus-gdpr-cookie-consent.php    # Plugin principale
├── includes/                        # Classi core PHP
├── admin/                          # Pannello amministrazione
├── public/                         # Frontend assets
├── languages/                      # File traduzioni
├── docs/                          # Documentazione
└── tests/                         # Test files
```

### 3. Requisiti di Sistema

- **WordPress**: 5.0+
- **PHP**: 7.4+ (consigliato: 8.1+)
- **MySQL**: 5.6+ / MariaDB 10.0+
- **Node.js**: 16+ (per build tools)
- **Composer**: 2.0+

## 📝 Tipi di Contributi

### 🐛 Bug Reports

Prima di aprire una issue per un bug:

1. **Cerca** nelle issue esistenti
2. **Testa** con l'ultima versione
3. **Fornisci** informazioni dettagliate:
   - Versione WordPress
   - Versione PHP
   - Versione plugin
   - Passi per riprodurre
   - Comportamento atteso vs attuale
   - Screenshot/video se utili

#### Template Bug Report

```markdown
**Descrizione Bug**
Breve descrizione del problema.

**Come Riprodurre**
1. Vai su '...'
2. Clicca su '....'
3. Scorri fino a '....'
4. Vedi errore

**Comportamento Atteso**
Cosa dovrebbe succedere.

**Screenshot**
Se applicabili, aggiungi screenshot.

**Ambiente:**
- WordPress: [versione]
- PHP: [versione]
- Browser: [browser + versione]
- Plugin version: [versione]
```

### ✨ Feature Requests

Per nuove funzionalità:

1. **Verifica** che non esista già
2. **Spiega** il caso d'uso
3. **Descrivi** la soluzione proposta
4. **Considera** alternative

### 🔧 Code Contributions

#### Linee Guida Codice

**PHP:**
- Segui [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Usa **PSR-4** autoloading
- **Commenta** funzioni complesse
- **Sanitizza** tutti gli input
- **Valida** tutti gli output

**JavaScript:**
- Usa **ES6+** quando possibile
- **Semicoloni** sempre
- **CamelCase** per variabili
- **Commenta** logica complessa

**CSS:**
- **BEM methodology** per naming
- **Mobile-first** approach
- **CSS custom properties** per temi
- **Compatibilità** browser moderni

#### Example Code Style

```php
<?php
/**
 * Class description
 *
 * @since    1.0.0
 * @package  Manus_GDPR
 */
class Manus_GDPR_Example {

    /**
     * Method description
     *
     * @since    1.0.0
     * @param    string $param Description
     * @return   bool   Success status
     */
    public function example_method( $param ) {
        // Sanitize input
        $param = sanitize_text_field( $param );
        
        // Your logic here
        $result = do_something( $param );
        
        return $result;
    }
}
```

```javascript
/**
 * Function description
 * @param {string} param - Parameter description
 * @returns {boolean} Success status
 */
function exampleFunction(param) {
    // Validate input
    if (!param || typeof param !== 'string') {
        return false;
    }
    
    // Your logic here
    const result = doSomething(param);
    
    return result;
}
```

### 📚 Documentazione

- **README.md** - Overview e installazione
- **Code comments** - Inline documentation
- **PHPDoc blocks** - Per tutte le funzioni/classi
- **Changelog** - Per tutte le modifiche

### 🧪 Testing

#### Test Requirements

- **Testa** su WordPress 5.0+
- **Testa** su PHP 7.4, 8.0, 8.1+
- **Testa** su browser moderni
- **Verifica** accessibilità base
- **Controlla** performance impact

#### Test Commands

```bash
# PHP Code Style
composer run phpcs

# Fix PHP Code Style
composer run phpcbf

# Run PHP Tests
composer run test

# JavaScript Lint
npm run lint

# Build Assets
npm run build
```

## 🔄 Processo Pull Request

### 1. Preparazione

```bash
# Aggiorna il tuo fork
git fetch upstream
git checkout main
git merge upstream/main

# Crea feature branch
git checkout -b feature/my-feature
```

### 2. Sviluppo

- **Commit piccoli** e logici
- **Messaggi commit** descrittivi
- **Test** ogni modifica
- **Aggiorna** documentazione se necessario

#### Commit Message Format

```text
type(scope): subject

body

footer
```

**Types:**
- `feat`: nuova funzionalità
- `fix`: bug fix
- `docs`: documentazione
- `style`: formatting/style
- `refactor`: refactoring codice
- `test`: aggiunta test
- `chore`: maintenance

**Esempi:**
```bash
git commit -m "feat(admin): add consent retention settings"
git commit -m "fix(database): resolve column name error"
git commit -m "docs(readme): update installation instructions"
```

### 3. Pull Request

1. **Push** del branch
2. **Apri** Pull Request su GitHub
3. **Completa** il template
4. **Rispondi** ai feedback
5. **Aggiorna** se necessario

#### PR Template

```markdown
## 📋 Descrizione
Breve descrizione delle modifiche.

## 🔗 Issue Collegata
Fixes #(issue number)

## 🧪 Testing
- [ ] Test funzionali
- [ ] Test compatibilità
- [ ] Test performance

## 📝 Checklist
- [ ] Code segue style guide
- [ ] Aggiunti test per nuove funzionalità
- [ ] Documentazione aggiornata
- [ ] Changelog aggiornato
```

## 🏷️ Versioning & Release

### Semantic Versioning

- **MAJOR** (1.0.0): Breaking changes
- **MINOR** (1.1.0): Nuove funzionalità
- **PATCH** (1.0.1): Bug fixes

### Release Process

1. **Feature freeze** per release
2. **Testing** approfondito
3. **Update** version numbers
4. **Update** changelog
5. **Create** release tag
6. **Deploy** su WordPress.org

## 🎯 Aree di Contribuzione

### 🔥 Alta Priorità

- **Testing automatizzato** (PHPUnit, Jest)
- **Accessibilità** improvements
- **Performance** optimization
- **Mobile** responsive fixes

### 🌟 Media Priorità

- **Traduzioni** lingue europee
- **UI/UX** improvements
- **Additional** cookie categories
- **Integration** con servizi terzi

### 💡 Bassa Priorità

- **Advanced** analytics
- **A/B testing** framework
- **Machine learning** features
- **Mobile app** integration

## 🏆 Riconoscimenti

### Come Vengono Riconosciuti i Contributori

- **Attribution** in README
- **Mention** nel changelog
- **Badge** contributore GitHub
- **Swag** del progetto (per contributori attivi)

### Livelli Contributore

1. **🌱 First-time** - Prima contribuzione
2. **🔧 Regular** - 5+ contribuzioni
3. **⭐ Frequent** - 15+ contribuzioni
4. **👑 Core** - 50+ contribuzioni + responsabilità

## 📞 Supporto

### Dove Chiedere Aiuto

- **GitHub Issues** - Bug e feature requests
- **GitHub Discussions** - Domande generali
- **Discord** - Chat tempo reale (coming soon)
- **Email** - Contatti diretti per contributor attivi

### Mentorship Program

Per nuovi contributori:
- **Buddy system** con contributor esperti
- **Easy issues** etichettate per principianti
- **Code review** dettagliata e educativa
- **1:1 sessions** per progetti complessi

## 📚 Risorse Utili

### WordPress Development

- [Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Security](https://developer.wordpress.org/plugins/security/)

### GDPR & Privacy

- [GDPR Official Text](https://gdpr-info.eu/)
- [IAB TCF v2.2](https://iabeurope.eu/tcf-2-0/)
- [Privacy by Design](https://www.ipc.on.ca/privacy-by-design/)

### Tools & Resources

- [Local by Flywheel](https://localwp.com/) - WordPress dev environment
- [Query Monitor](https://wordpress.org/plugins/query-monitor/) - Debug plugin
- [Debug Bar](https://wordpress.org/plugins/debug-bar/) - Debug toolbar

---

## 🤝 Codice di Condotta

Seguiamo il [Contributor Covenant](https://www.contributor-covenant.org/) per mantenere una community welcoming e inclusiva.

### I Nostri Valori

- **Rispetto** per tutti i contributori
- **Collaborazione** costruttiva
- **Apprendimento** continuo
- **Qualità** del codice
- **Trasparenza** nei processi

### Comportamenti Inaccettabili

- Linguaggio offensivo o discriminatorio
- Harassment di qualsiasi tipo
- Spam o autopromozione eccessiva
- Violazione di copyright o licenze

### Segnalazioni

Per segnalare violazioni: conduct@rubik-gdpr.com

---

**Grazie per contribuire a rendere il web più sicuro e conforme alla privacy! 🍪**
