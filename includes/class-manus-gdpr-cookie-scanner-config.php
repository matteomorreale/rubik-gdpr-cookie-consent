<?php
/**
 * Cookie Scanner Configuration
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 */

/**
 * Cookie Scanner Configuration Class
 *
 * Contains configuration for cookie detection patterns, categories, and scanning behavior
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/includes
 * @author     Matteo Morreale
 */
class Manus_GDPR_Cookie_Scanner_Config {

    /**
     * Get default cookie patterns for categorization
     */
    public static function get_cookie_patterns() {
        return array(
            'necessary' => array(
                'patterns' => array(
                    // WordPress core
                    'wp-settings-',
                    'wordpress_',
                    'wp_',
                    'wordpress_logged_in_',
                    'wordpress_test_cookie',
                    'wp-wpml_current_language',
                    
                    // Session management
                    'PHPSESSID',
                    'session',
                    'sess_',
                    'JSESSIONID',
                    'ASP.NET_SessionId',
                    
                    // Security
                    'csrf',
                    'xsrf',
                    '_wpnonce',
                    'security',
                    'token',
                    'authenticity_token',
                    
                    // Authentication
                    'auth',
                    'login',
                    'user',
                    'admin',
                    'logged_in',
                    'remember_me',
                    
                    // Cart and checkout (ecommerce)
                    'cart',
                    'checkout',
                    'order',
                    'basket',
                    'wc_',
                    'woocommerce_',
                    
                    // CDN and performance
                    'cf_',
                    'cloudflare',
                    '__cfduid',
                    '_cfuvid'
                ),
                'label' => 'Cookie Necessari',
                'description' => 'Cookie essenziali per il funzionamento del sito web. Non possono essere disabilitati.',
                'color' => '#dc3545',
                'always_allowed' => true
            ),
            
            'analytics' => array(
                'patterns' => array(
                    // Google Analytics
                    '_ga',
                    '_gid',
                    '_gat',
                    '__utma',
                    '__utmb',
                    '__utmc',
                    '__utmt',
                    '__utmz',
                    '__utmv',
                    '_dc_gtm_',
                    'gtm_',
                    'google_analytics',
                    'ga_',
                    '_gali',
                    
                    // Google Tag Manager
                    'gtm',
                    '_gtm',
                    
                    // Adobe Analytics
                    's_cc',
                    's_sq',
                    's_vi',
                    's_fid',
                    'mbox',
                    
                    // Hotjar
                    '_hj',
                    'hjSession',
                    'hjid',
                    'hjIncludedInSample',
                    
                    // Mixpanel
                    'mp_',
                    '__mp_opt_in_out',
                    'mixpanel',
                    
                    // Crazy Egg
                    '_ce',
                    'cebsp',
                    'cebs',
                    
                    // Other analytics
                    'analytics',
                    'stats',
                    'tracking',
                    'visitor',
                    'visit',
                    'session_depth',
                    '_pk_',
                    'matomo',
                    'piwik'
                ),
                'label' => 'Cookie Analitici',
                'description' => 'Cookie per raccogliere informazioni sull\'utilizzo del sito web',
                'color' => '#007cba',
                'always_allowed' => false
            ),
            
            'advertising' => array(
                'patterns' => array(
                    // Google Ads
                    'doubleclick',
                    'googlesyndication',
                    'googleadservices',
                    'adsystem',
                    'IDE',
                    'DSID',
                    '__gads',
                    '__gpi',
                    'NID',
                    'ANID',
                    'CONSENT',
                    '1P_JAR',
                    'AID',
                    'TAID',
                    
                    // Facebook
                    'fbp',
                    'fbq',
                    'tr',
                    '_fbp',
                    'fr',
                    'datr',
                    'sb',
                    'wd',
                    'facebook',
                    'fbsr_',
                    'fbm_',
                    
                    // Twitter
                    'personalization_id',
                    'guest_id',
                    'ct0',
                    'twtr',
                    
                    // LinkedIn
                    'li_gc',
                    'lissc',
                    'li_oatml',
                    'lms_ads',
                    'UserMatchHistory',
                    
                    // Amazon
                    'ad-id',
                    'ad-privacy',
                    'amazon',
                    'aam_uuid',
                    
                    // Microsoft
                    'MUID',
                    'MUIDB',
                    'MR',
                    'MC1',
                    'MS0',
                    'MSCC',
                    
                    // Other advertising
                    'ads',
                    'marketing',
                    'retargeting',
                    'advertising',
                    'ad_',
                    'adnxs',
                    'anj',
                    'uuid2',
                    'demdex',
                    'dpm',
                    'everest_g_v2',
                    'everest_session_v2'
                ),
                'label' => 'Cookie Pubblicitari',
                'description' => 'Cookie per pubblicità personalizzata e targeting',
                'color' => '#ff6900',
                'always_allowed' => false
            ),
            
            'functional' => array(
                'patterns' => array(
                    // Language and localization
                    'lang',
                    'language',
                    'locale',
                    'i18n',
                    'translation',
                    
                    // Currency and region
                    'currency',
                    'country',
                    'region',
                    'timezone',
                    
                    // Theme and display
                    'theme',
                    'color_scheme',
                    'dark_mode',
                    'font_size',
                    'layout',
                    
                    // User preferences
                    'preferences',
                    'settings',
                    'config',
                    'options',
                    'choice',
                    
                    // Video players
                    'youtube',
                    'vimeo',
                    'video',
                    'player',
                    'jwplayer',
                    
                    // Social media widgets
                    'social',
                    'share',
                    'like',
                    'follow',
                    
                    // Chat and support
                    'chat',
                    'support',
                    'help',
                    'intercom',
                    'zendesk',
                    'livechat',
                    
                    // Forms
                    'form',
                    'contact',
                    'newsletter',
                    'subscribe',
                    
                    // Other functional
                    'functional',
                    'feature',
                    'widget',
                    'tool',
                    'utility'
                ),
                'label' => 'Cookie Funzionali',
                'description' => 'Cookie per migliorare l\'esperienza utente e fornire funzionalità aggiuntive',
                'color' => '#00a32a',
                'always_allowed' => false
            )
        );
    }

    /**
     * Get script detection patterns
     */
    public static function get_script_patterns() {
        return array(
            'analytics' => array(
                'googletagmanager.com',
                'google-analytics.com',
                'googletagservices.com',
                'gtag',
                'ga.js',
                'analytics.js',
                'gtm.js',
                'hotjar.com',
                'mixpanel.com',
                'crazyegg.com',
                'matomo.org',
                'piwik.org'
            ),
            'advertising' => array(
                'googlesyndication.com',
                'googleadservices.com',
                'doubleclick.net',
                'facebook.net',
                'fbcdn.net',
                'connect.facebook.net',
                'ads.twitter.com',
                'ads.linkedin.com',
                'amazon-adsystem.com',
                'adsystem.amazon.com'
            ),
            'functional' => array(
                'youtube.com',
                'vimeo.com',
                'twitter.com',
                'instagram.com',
                'tiktok.com',
                'intercom.io',
                'zendesk.com',
                'livechatinc.com'
            )
        );
    }

    /**
     * Get domain-based categorization rules
     */
    public static function get_domain_rules() {
        return array(
            'analytics' => array(
                'google-analytics.com',
                'googletagmanager.com',
                'hotjar.com',
                'mixpanel.com',
                'crazyegg.com',
                'matomo.org',
                'piwik.org'
            ),
            'advertising' => array(
                'googlesyndication.com',
                'googleadservices.com',
                'doubleclick.net',
                'facebook.com',
                'fbcdn.net',
                'twitter.com',
                'linkedin.com',
                'amazon-adsystem.com'
            ),
            'functional' => array(
                'youtube.com',
                'vimeo.com',
                'intercom.io',
                'zendesk.com',
                'livechatinc.com'
            )
        );
    }

    /**
     * Get scanning configuration
     */
    public static function get_scan_config() {
        return array(
            'phases' => array(
                'initial_load' => array(
                    'name' => 'Caricamento iniziale',
                    'duration' => 2000,
                    'description' => 'Caricamento della pagina e rilevamento cookie iniziali'
                ),
                'user_interaction' => array(
                    'name' => 'Simulazione interazione utente',
                    'duration' => 1500,
                    'description' => 'Simulazione di click e scroll per attivare script'
                ),
                'delayed_scripts' => array(
                    'name' => 'Attesa script ritardati',
                    'duration' => 3000,
                    'description' => 'Attesa per script che si caricano dopo l\'interazione'
                ),
                'final_analysis' => array(
                    'name' => 'Analisi finale',
                    'duration' => 1000,
                    'description' => 'Raccolta e categorizzazione finale dei cookie'
                )
            ),
            'options' => array(
                'clear_cookies_before_scan' => true,
                'wait_for_page_load' => true,
                'simulate_user_interaction' => true,
                'detect_storage_apis' => true,
                'analyze_scripts' => true,
                'predict_cookies' => true,
                'deep_scan' => false // Riservato per future implementazioni
            ),
            'limits' => array(
                'max_cookies_per_scan' => 200,
                'max_scan_duration' => 30000, // 30 seconds
                'max_cookie_value_length' => 500,
                'max_scans_per_hour' => 10
            )
        );
    }

    /**
     * Get user-friendly explanations for cookie categories
     */
    public static function get_category_explanations() {
        return array(
            'necessary' => array(
                'title' => 'Cookie Strettamente Necessari',
                'description' => 'Questi cookie sono essenziali per il funzionamento del sito web e non possono essere disabilitati. Includono cookie per l\'autenticazione, la sicurezza e le funzionalità di base.',
                'examples' => array(
                    'Cookie di sessione per mantenere il login',
                    'Token di sicurezza per prevenire attacchi CSRF',
                    'Impostazioni di accessibilità',
                    'Carrello della spesa negli e-commerce'
                ),
                'legal_basis' => 'Interesse legittimo - necessari per il funzionamento del servizio',
                'retention' => 'Solitamente per la durata della sessione o fino al logout'
            ),
            'analytics' => array(
                'title' => 'Cookie Analitici e di Performance',
                'description' => 'Questi cookie raccolgono informazioni su come i visitatori utilizzano il sito web, permettendo di migliorare le prestazioni e l\'esperienza utente.',
                'examples' => array(
                    'Google Analytics per statistiche di utilizzo',
                    'Hotjar per analisi del comportamento utente',
                    'Cookie per monitorare la velocità di caricamento',
                    'Strumenti di A/B testing'
                ),
                'legal_basis' => 'Consenso dell\'utente richiesto',
                'retention' => 'Da 24 ore a 2 anni, a seconda del servizio'
            ),
            'advertising' => array(
                'title' => 'Cookie Pubblicitari e di Marketing',
                'description' => 'Questi cookie vengono utilizzati per mostrare pubblicità personalizzata e misurare l\'efficacia delle campagne pubblicitarie.',
                'examples' => array(
                    'Google Ads per pubblicità personalizzata',
                    'Facebook Pixel per retargeting',
                    'Cookie per il tracking delle conversioni',
                    'Piattaforme di affiliate marketing'
                ),
                'legal_basis' => 'Consenso esplicito dell\'utente richiesto',
                'retention' => 'Solitamente da 30 giorni a 2 anni'
            ),
            'functional' => array(
                'title' => 'Cookie Funzionali e di Preferenze',
                'description' => 'Questi cookie permettono al sito web di ricordare le scelte dell\'utente e fornire funzionalità personalizzate.',
                'examples' => array(
                    'Preferenze di lingua e localizzazione',
                    'Impostazioni del tema (modalità scura/chiara)',
                    'Widget di social media',
                    'Lettori video incorporati'
                ),
                'legal_basis' => 'Consenso dell\'utente consigliato',
                'retention' => 'Da 30 giorni a 1 anno'
            )
        );
    }

    /**
     * Get privacy impact scores for different cookie categories
     */
    public static function get_privacy_impact_scores() {
        return array(
            'necessary' => array(
                'score' => 1,
                'level' => 'Basso',
                'description' => 'Impatto minimo sulla privacy, essenziali per il funzionamento'
            ),
            'functional' => array(
                'score' => 2,
                'level' => 'Medio-Basso',
                'description' => 'Impatto moderato, migliorano l\'esperienza utente'
            ),
            'analytics' => array(
                'score' => 3,
                'level' => 'Medio',
                'description' => 'Raccolgono dati sull\'utilizzo, possono essere aggregati'
            ),
            'advertising' => array(
                'score' => 4,
                'level' => 'Alto',
                'description' => 'Alto impatto sulla privacy, creano profili dettagliati'
            )
        );
    }
}
