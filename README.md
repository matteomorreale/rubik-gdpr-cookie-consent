![Rubik GDPR Cookie Consent Cover](https://matteomorreale.it/wp-content/uploads/2025/06/Free-GDPR-Cookie-Consent.jpg)

# Rubik FREE GDPR Cookie Consent

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-CC%20BY--NC%204.0-green.svg)](https://creativecommons.org/licenses/by-nc/4.0/)
[![Version](https://img.shields.io/badge/Version-1.0.4-orange.svg)](https://github.com/matteomorreale/rubik-gdpr-cookie-consent/releases)
[![Author](https://img.shields.io/badge/Author-Matteo%20Morreale-blue.svg)](https://github.com/matteomorreale)

> **Modern and comprehensive WordPress plugin for GDPR/CCPA compliant cookie consent management with IAB TCF v2.2 support**

Rubik GDPR Cookie Consent is a professional WordPress plugin that offers a complete solution for cookie consent management, designed to ensure compliance with GDPR, CCPA and other privacy regulations worldwide.

## Key Features

### **Modern and Accessible Design**

- **Glassmorphism UI** with blur and transparency effects
- **Automatic theme** that adapts to system preferences (light/dark/auto)
- **Responsive layout** optimized for all devices
- **Safe Area Support** for devices with notch (iPhone X+, macOS)
- **Smooth animations** with optimized CSS transitions

### **Regulatory Compliance**

- **GDPR compliant** (General Data Protection Regulation)
- **CCPA ready** (California Consumer Privacy Act)
- **IAB TCF v2.2** (Transparency & Consent Framework)
- **Consent documentation** with timestamp and IP tracking
- **Configurable retention policy** for consent data

### **Advanced Consent Management**

- **Customizable categories** (Necessary, Analytics, Advertising, Functional)
- **Granular consent** for each cookie category
- **Floating icon** to modify preferences anytime
- **Automatic cleanup** of expired consents via cron job
- **Export/Import** configurations

### **Administrative Features**

- **Intuitive dashboard** with real-time statistics (total, accepted, rejected, partial)
- **Automatic cookie scanner** to detect all site cookies
- **Advanced consent management** with bulk/selective deletion and automatic expired cleanup
- **Configurable retention policy** (from 1 month to 10 years) with automatic cron job
- **Detailed logging** with status filters, IP/user search and pagination
- **Integrated TCF v2.2 tests** to verify IAB compliance

## Quick Installation

### Method 1: Via WordPress Admin

1. Download the `.zip` file from the [releases page](https://github.com/matteomorreale/rubik-gdpr-cookie-consent/releases)
2. Go to **WordPress Admin** → **Plugins** → **Add New**
3. Click **Upload Plugin** and select the `.zip` file
4. Activate the plugin
5. Go to **Rubik GDPR** → **Settings** to configure

### Method 2: Via FTP

```bash
# Clone the repository
git clone https://github.com/matteomorreale/rubik-gdpr-cookie-consent.git

# Upload to WordPress plugins directory
cp -r rubik-gdpr-cookie-consent /path/to/wordpress/wp-content/plugins/

# Activate via WP-CLI (optional)
wp plugin activate rubik-gdpr-cookie-consent
```

> **Important**: After installation, go to **WordPress Admin** → **Rubik GDPR** to complete the initial configuration.

## Initial Configuration

### 1. Basic Configuration

After activation, go to **WordPress Admin** → **Rubik GDPR** → **Settings**:

- **Enable cookie banner**
- **Customize the message**
- **Choose theme and layout**
- **Set banner position**

### 2. Cookie Categories

Configure categories according to your needs:

```php
// Default categories
$categories = [
    'necessary'   => 'Necessary Cookies',     // Always active
    'analytics'   => 'Analytics Cookies',    // Google Analytics, etc.
    'advertising' => 'Advertising Cookies',  // Google Ads, Facebook, etc.
    'functional'  => 'Functional Cookies'    // Chat, maps, etc.
];
```

### 3. Retention Policy

Set how long to keep consents:

- **30 days** - For testing and development
- **6 months** - Basic configuration for small sites
- **1 year** - Recommended configuration
- **2-10 years** - For comprehensive historical analysis and legal compliance

## Usage Examples

### Custom Consent Banner

```css
/* Custom CSS for the banner */
#matteomorreale-gdpr-cookie-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.matteomorreale-gdpr-buttons button {
    border-radius: 25px;
    transition: all 0.3s ease;
}

#matteomorreale-gdpr-accept-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
```

### JavaScript Integration

```javascript
// Listen to consent events
document.addEventListener('matteomorreale-gdpr-consent-updated', function(event) {
    const { consentStatus, consentData } = event.detail;
    
    console.log('Consent updated:', consentStatus);
    console.log('Details:', consentData);
    
    // Enable/disable services based on consent
    if (consentData.analytics) {
        // Initialize Google Analytics
        gtag('config', 'GA_MEASUREMENT_ID');
    }
    
    if (consentData.advertising) {
        // Initialize Google Ads
        gtag('config', 'AW-CONVERSION_ID');
    }
});

// TCF API test (if enabled)
if (typeof window.__tcfapi === 'function') {
    window.__tcfapi('getTCData', 2, function(tcData, success) {
        if (success) {
            console.log('TCF Data:', tcData);
            console.log('Consent String:', tcData.tcString);
        }
    });
}
```

### WordPress Hooks

```php
// Hook to customize behavior
add_action('manus_gdpr_consent_recorded', function($consent_data) {
    // Custom logic after consent
    if ($consent_data['consent_status'] === 'accepted') {
        // Enable full services
        update_option('enable_full_tracking', true);
    }
});

// Filter to modify categories
add_filter('manus_gdpr_cookie_categories', function($categories) {
    $categories['social'] = 'Social Media Cookies';
    return $categories;
});
```

## Dashboard and Statistics

The admin panel provides a comprehensive overview of consents:

### Real-Time Statistics

- **Total consents** recorded in database
- **Accepted consents** (all cookies enabled)
- **Rejected consents** (only necessary cookies)
- **Partial consents** (selective categories)
- **Recent consents** (last 7/30 days)
- **Expired consents** to be automatically deleted

### Advanced Management

- **Automatic cleanup** via daily cron job
- **Bulk deletion** of all consents
- **Selective deletion** of expired consents
- **Advanced filters** by status and period
- **Search** by IP address or user ID

### Data Export

```bash
# Via WordPress Admin
WordPress Admin → Rubik GDPR → Consent Log → [Filters] → Export CSV

# Via WP-CLI (optional)
wp gdpr export-consents --format=csv --file=consents-2025.csv
```

## API and Developers

### Available WordPress Hooks

```php
/**
 * Available actions
 */

// After consent registration
do_action('manus_gdpr_consent_recorded', $consent_data);

// When banner is displayed
do_action('manus_gdpr_banner_displayed');

// When preferences modal opens
do_action('manus_gdpr_modal_opened');

// During automatic cleanup of expired consents (cron)
do_action('manus_gdpr_expired_consents_cleaned', $deleted_count);

/**
 * Available filters
 */

// Customize banner message
$message = apply_filters('manus_gdpr_banner_message', $message);

// Modify available cookie categories
$categories = apply_filters('manus_gdpr_cookie_categories', $categories);

// Change consent retention period
$retention_days = apply_filters('manus_gdpr_retention_period', $days);

// Customize theme colors
$colors = apply_filters('manus_gdpr_theme_colors', $colors);
```

### Database Class Methods

```php
// Via WordPress Admin Dashboard
Manus_GDPR_Database::record_consent($user_id, $ip, $status, $data);

// Get complete statistics
$stats = Manus_GDPR_Database::get_consent_statistics();
// Returns: ['total' => X, 'accepted' => Y, 'rejected' => Z, 'partial' => W, 'recent' => R]

// Clean expired consents (via cron or manually)
$deleted = Manus_GDPR_Database::clear_expired_consents(365);

// Count consents to be deleted
$expired_count = Manus_GDPR_Database::count_expired_consents(365);
```

### REST API Endpoints

```http
GET    /wp-json/m2-gdpr/v1/consents      # List all consents (admin)
POST   /wp-json/m2-gdpr/v1/consent       # Register new consent
DELETE /wp-json/m2-gdpr/v1/consents/{id} # Delete specific consent
GET    /wp-json/m2-gdpr/v1/statistics    # Consent statistics
POST   /wp-json/m2-gdpr/v1/clean-expired # Clean expired consents
```

> **Note**: REST API endpoints are currently in development for future plugin versions.

## Advanced Configurations

### CSS Customization

You can customize the plugin appearance via custom CSS in the admin panel or your theme:

```css
/* Customize the banner */
#matteomorreale-gdpr-cookie-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-radius: 20px !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
}

/* Custom floating icon */
#matteomorreale-gdpr-floating-icon {
    background: var(--wp-admin-theme-color, #0073aa) !important;
    box-shadow: 0 4px 12px rgba(0,115,170,0.4) !important;
}

/* Advanced dark mode */
[data-theme="dark"] #matteomorreale-gdpr-cookie-banner {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%) !important;
    color: #f7fafc !important;
}
```

### WordPress Multisite Configuration

For WordPress multisite networks, add these configurations:

```php
// wp-config.php - Multisite configuration
define('MANUS_GDPR_MULTISITE_SHARED_CONFIG', true);
define('MANUS_GDPR_NETWORK_ADMIN_ONLY', false);
define('MANUS_GDPR_INHERIT_NETWORK_SETTINGS', true);
```

### Performance Optimization

For high-traffic sites, enable optimizations:

```php
// wp-config.php - Performance optimizations
define('MANUS_GDPR_CACHE_ENABLE', true);
define('MANUS_GDPR_LAZY_LOAD_SCRIPTS', true);
define('MANUS_GDPR_COMPRESS_OUTPUT', true);
define('MANUS_GDPR_ASYNC_CONSENT_LOGGING', true);
```

### CDN and Caching

Configuration for CDN compatibility:

```php
// theme functions.php - CDN support
add_filter('manus_gdpr_static_urls', function($urls) {
    return str_replace(home_url(), 'https://cdn.example.com', $urls);
});

// Cache exclusion for dynamic pages
add_filter('manus_gdpr_no_cache_pages', function($pages) {
    $pages[] = 'consent-preferences';
    return $pages;
});
```

## Testing and Debug

### Compliance Tests

```bash
# Banner and modal test
open test-syntax-fix.html  # User interface test

# Database test
open test-database-fix.html  # CRUD operations test

# Retention policy test
php test-consent-retention.php  # Automatic cleanup test
```

### Debug Mode

```php
// wp-config.php - Enable GDPR debug
define('MANUS_GDPR_DEBUG', true);
define('MANUS_GDPR_LOG_LEVEL', 'debug');

// Verify database tables
global $wpdb;
$table = $wpdb->prefix . 'manus_gdpr_consents';
echo $wpdb->get_var("SHOW TABLES LIKE '$table'");
```

### TCF v2.2 Validation

```javascript
// Browser console - TCF API test
window.testTCFAPI(); // Integrated test function

// Verify TC String generation
console.log(window.__tcfapi);

// Test event listeners
window.__tcfapi('addEventListener', 2, function(tcData, success) {
    console.log('TCF Event:', tcData, success);
});
```

## Compatibility

### Supported Browsers

| Browser | Version | Status |
|---------|----------|---------|
| Chrome  | 80+      | Full |
| Firefox | 75+      | Full |
| Safari  | 13+      | Full |
| Edge    | 80+      | Full |
| IE      | 11       | Basic |

### WordPress

- **WordPress**: 5.0+ (tested up to 6.5)
- **PHP**: 7.4+ (recommended: 8.1+)
- **MySQL**: 5.6+ / MariaDB 10.0+
- **Multisite**: Fully supported

### Compatible Plugins

- **WooCommerce** 5.0+
- **Yoast SEO**
- **Elementor**
- **WP Rocket**
- **W3 Total Cache**
- **CloudFlare**

## Contributing

Help us improve Rubik GDPR Cookie Consent!

### 1. Development Setup

```bash
# Fork and clone the repository
git clone https://github.com/matteomorreale/rubik-gdpr-cookie-consent.git
cd rubik-gdpr-cookie-consent
```

### 2. Guidelines

- **Code**: Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- **Tests**: Add tests for new features
- **Documentation**: Update docs for API changes
- **i18n**: Add translations for new strings

### 3. Pull Request Process

1. Create a branch for your feature: `git checkout -b feature/amazing-feature`
2. Commit your changes: `git commit -m 'Add amazing feature'`
3. Push the branch: `git push origin feature/amazing-feature`
4. Open a Pull Request

## Acknowledgments

### Technologies Used

- **WordPress Plugin API** - Base framework
- **IAB TCF v2.2** - Standard for advertising consent
- **CSS Grid & Flexbox** - Responsive layout
- **Vanilla JavaScript** - Optimized performance
- **MySQL/MariaDB** - Data storage

### Inspirations

- [GDPR Cookie Compliance](https://wordpress.org/plugins/gdpr-cookie-compliance/)
- [Cookiebot](https://www.cookiebot.com/)
- [OneTrust](https://www.onetrust.com/)

### Contributors

[See all contributors on GitHub](https://github.com/matteomorreale/rubik-gdpr-cookie-consent/graphs/contributors)

## Support

### Bug Reports

Found a bug? Help us fix it!

1. Check if there's already a [similar issue](https://github.com/matteomorreale/rubik-gdpr-cookie-consent/issues)
2. Open a [new issue](https://github.com/matteomorreale/rubik-gdpr-cookie-consent/issues/new) with:
   - Detailed problem description
   - Steps to reproduce the bug
   - Screenshots or videos (if helpful)
   - Environment information (WordPress, PHP, browser)

### Feature Requests

Have an idea to improve the plugin?

- Open a [Feature Request](https://github.com/matteomorreale/rubik-gdpr-cookie-consent/issues/new)
- Join the [Discussions](https://github.com/matteomorreale/rubik-gdpr-cookie-consent/discussions)
- Vote for most requested features

---

## License

This project is released under the **Creative Commons Attribution-NonCommercial 4.0 International License (CC BY-NC 4.0)**.

- Non-commercial use only
- Attribution required (credit Matteo Morreale)

See the [LICENSE](LICENSE) file for details.
