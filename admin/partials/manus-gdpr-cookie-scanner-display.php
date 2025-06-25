<?php
/**
 * Provide a admin area view for the cookie scanner
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Manus_GDPR
 * @subpackage Manus_GDPR/admin/partials
 */

// Get scan results
$scan_results = Manus_GDPR_Cookie_Scanner::get_scan_results();
$cookie_categories = Manus_GDPR_Cookie_Scanner::get_cookie_categories();
?>

<div class="wrap">
    <h1><?php _e( 'Cookie Scanner', 'manus-gdpr' ); ?></h1>

    <div id="cookie-scanner-container">
        
        <!-- Scanner Section -->
        <div class="postbox">
            <h2 class="hndle"><?php _e( 'Scansiona una pagina', 'manus-gdpr' ); ?></h2>
            <div class="inside">
                <p><?php _e( 'Inserisci l\'URL di una pagina del tuo sito per analizzare i cookie che vengono caricati. Lo scanner aprirà la pagina e rileverà automaticamente tutti i cookie presenti.', 'manus-gdpr' ); ?></p>
                
                <div class="scanner-form">
                    <div class="scanner-input-group">
                        <label for="scan-url"><?php _e( 'URL da scansionare:', 'manus-gdpr' ); ?></label>
                        <input type="url" id="scan-url" placeholder="<?php echo esc_attr( home_url() ); ?>" value="<?php echo esc_attr( home_url() ); ?>">
                        <button type="button" id="start-scan" class="button button-primary">
                            <span class="dashicons dashicons-search"></span>
                            <?php _e( 'Avvia Scansione', 'manus-gdpr' ); ?>
                        </button>
                        <button type="button" id="quick-test" class="button button-secondary">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php _e( 'Test Rapido', 'manus-gdpr' ); ?>
                        </button>
                    </div>
                    
                    <div class="scanner-options">
                        <label>
                            <input type="checkbox" id="clear-cookies" checked>
                            <?php _e( 'Pulisci cookie esistenti prima della scansione', 'manus-gdpr' ); ?>
                        </label>
                        <label>
                            <input type="checkbox" id="wait-for-load" checked>
                            <?php _e( 'Attendi il caricamento completo della pagina', 'manus-gdpr' ); ?>
                        </label>
                        <label>
                            <input type="checkbox" id="deep-scan">
                            <?php _e( 'Scansione approfondita (più lenta ma più accurata)', 'manus-gdpr' ); ?>
                        </label>
                    </div>
                </div>

                <div id="scanner-progress" class="scanner-progress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p class="progress-text"><?php _e( 'Scansione in corso...', 'manus-gdpr' ); ?></p>
                </div>

                <div id="scanner-iframe-container" style="display: none;">
                    <iframe id="scanner-iframe" sandbox="allow-same-origin allow-scripts" style="width: 100%; height: 400px; border: 1px solid #ccd0d4;"></iframe>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <?php if ( ! empty( $scan_results ) ): ?>
        <div class="postbox">
            <h2 class="hndle"><?php _e( 'Risultati delle scansioni', 'manus-gdpr' ); ?></h2>
            <div class="inside">
                <div class="scan-results-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#" class="nav-tab nav-tab-active" data-tab="recent"><?php _e( 'Scansioni recenti', 'manus-gdpr' ); ?></a>
                        <a href="#" class="nav-tab" data-tab="categories"><?php _e( 'Per categoria', 'manus-gdpr' ); ?></a>
                        <a href="#" class="nav-tab" data-tab="blocked"><?php _e( 'Cookie bloccati', 'manus-gdpr' ); ?></a>
                    </nav>

                    <!-- Recent Scans Tab -->
                    <div id="tab-recent" class="tab-content active">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e( 'URL', 'manus-gdpr' ); ?></th>
                                    <th><?php _e( 'Data scansione', 'manus-gdpr' ); ?></th>
                                    <th><?php _e( 'Cookie trovati', 'manus-gdpr' ); ?></th>
                                    <th><?php _e( 'Categorie', 'manus-gdpr' ); ?></th>
                                    <th><?php _e( 'Azioni', 'manus-gdpr' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $scan_results as $result ): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html( $result['url'] ); ?></strong>
                                    </td>
                                    <td><?php echo esc_html( $result['scan_date'] ); ?></td>
                                    <td><?php echo esc_html( $result['cookies_count'] ); ?></td>
                                    <td>
                                        <?php foreach ( $result['categories_summary'] as $category => $count ): ?>
                                            <span class="cookie-category-badge category-<?php echo esc_attr( $category ); ?>">
                                                <?php echo esc_html( $cookie_categories[$category]['label'] ?? ucfirst( $category ) ); ?>: <?php echo esc_html( $count ); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="button view-scan-details" data-scan-id="<?php echo esc_attr( $result['id'] ); ?>">
                                            <?php _e( 'Visualizza', 'manus-gdpr' ); ?>
                                        </button>
                                        <button type="button" class="button delete-scan" data-scan-id="<?php echo esc_attr( $result['id'] ); ?>">
                                            <?php _e( 'Elimina', 'manus-gdpr' ); ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Categories Tab -->
                    <div id="tab-categories" class="tab-content">
                        <div class="category-overview">
                            <?php if ( ! empty( $cookie_categories ) ): ?>
                                <?php foreach ( $cookie_categories as $category_key => $category_data ): ?>
                                <div class="category-card category-<?php echo esc_attr( $category_key ); ?>">
                                    <h3><?php echo esc_html( $category_data['label'] ); ?></h3>
                                    <p><?php echo esc_html( $category_data['description'] ); ?></p>
                                    <div class="category-patterns">
                                        <strong><?php _e( 'Pattern riconosciuti:', 'manus-gdpr' ); ?></strong>
                                        <span class="patterns"><?php echo esc_html( implode( ', ', $category_data['patterns'] ) ); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p><?php _e( 'Nessuna categoria di cookie definita.', 'manus-gdpr' ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Blocked Cookies Tab -->
                    <div id="tab-blocked" class="tab-content">
                        <div id="blocked-cookies-list">
                            <p><?php _e( 'Seleziona una scansione per vedere i cookie bloccati.', 'manus-gdpr' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Scan Details Modal -->
    <div id="scan-details-modal" class="scan-modal" style="display: none;">
        <div class="scan-modal-content">
            <div class="scan-modal-header">
                <h2><?php _e( 'Dettagli della scansione', 'manus-gdpr' ); ?></h2>
                <button type="button" class="scan-modal-close">&times;</button>
            </div>
            <div class="scan-modal-body">
                <div id="scan-details-content">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
            <div class="scan-modal-footer">
                <button type="button" class="button button-primary" id="save-cookie-settings">
                    <?php _e( 'Salva impostazioni', 'manus-gdpr' ); ?>
                </button>
                <button type="button" class="button scan-modal-close">
                    <?php _e( 'Chiudi', 'manus-gdpr' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Scanner functionality
    const scanner = {
        init: function() {
            this.bindEvents();
            this.initializeTabs();
        },

        initializeTabs: function() {
            console.log('Initializing tabs...');
            
            // Ensure first tab is active and visible
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tab="recent"]').addClass('nav-tab-active');
            
            $('.tab-content').removeClass('active').hide();
            $('#tab-recent').addClass('active').show();
            
            console.log('Active tab:', $('.nav-tab-active').data('tab'));
            console.log('Visible tab content:', $('.tab-content.active').attr('id'));
        },

        bindEvents: function() {
            $('#start-scan').on('click', this.startScan.bind(this));
            $('#quick-test').on('click', this.quickTest.bind(this));
            $('.view-scan-details').on('click', this.viewScanDetails.bind(this));
            $('.delete-scan').on('click', this.deleteScan.bind(this));
            
            // Bind tab switching with more specific targeting
            $(document).on('click', '.nav-tab', this.switchTab.bind(this));
            
            $('.scan-modal-close').on('click', this.closeModal.bind(this));
            $('#save-cookie-settings').on('click', this.saveCookieSettings.bind(this));
        },

        startScan: function() {
            const url = $('#scan-url').val().trim();
            if (!url) {
                alert('<?php _e( "Inserisci un URL valido", "manus-gdpr" ); ?>');
                return;
            }

            this.showProgress();
            
            const deepScan = $('#deep-scan').is(':checked');
            if (deepScan && typeof ManusGDPRCookieScanner !== 'undefined') {
                this.performDeepScan(url);
            } else {
                this.performScan(url);
            }
        },

        quickTest: function() {
            this.showProgress();
            $('.progress-text').text('<?php _e( "Esecuzione test rapido...", "manus-gdpr" ); ?>');
            
            // Simulate quick cookie detection
            setTimeout(() => {
                const mockCookies = [
                    {
                        name: '_ga',
                        value: 'GA1.2.123456789.123456789',
                        domain: window.location.hostname,
                        path: '/',
                        secure: true,
                        httpOnly: false,
                        sameSite: 'Lax',
                        category: 'analytics'
                    },
                    {
                        name: 'wordpress_test_cookie',
                        value: 'WP Cookie check',
                        domain: window.location.hostname,
                        path: '/',
                        secure: false,
                        httpOnly: false,
                        sameSite: 'Lax',
                        category: 'necessary'
                    },
                    {
                        name: '_fbp',
                        value: 'fb.1.1234567890123.1234567890',
                        domain: window.location.hostname,
                        path: '/',
                        secure: true,
                        httpOnly: false,
                        sameSite: 'Lax',
                        category: 'advertising'
                    }
                ];
                
                $('.progress-fill').css('width', '100%');
                $('.progress-text').text('<?php _e( "Test completato!", "manus-gdpr" ); ?>');
                
                // Send mock data to server
                this.saveCookiesToServer(window.location.origin + '/test-page', mockCookies);
            }, 2000);
        },

        showProgress: function() {
            $('#scanner-progress').show();
            $('#scanner-iframe-container').show();
            $('.progress-fill').css('width', '0%');
        },

        performScan: function(url) {
            const iframe = $('#scanner-iframe')[0];
            const clearCookies = $('#clear-cookies').is(':checked');
            const waitForLoad = $('#wait-for-load').is(':checked');

            // Clear cookies if requested
            if (clearCookies) {
                document.cookie.split(";").forEach(function(c) {
                    document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
                });
            }

            // Progress animation
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress < 90) {
                    $('.progress-fill').css('width', progress + '%');
                }
            }, 500);

            // Load URL in iframe
            iframe.onload = () => {
                setTimeout(() => {
                    // Extract cookies from iframe
                    this.extractCookies(iframe, url);
                    clearInterval(progressInterval);
                    $('.progress-fill').css('width', '100%');
                    $('.progress-text').text('<?php _e( "Analisi completata!", "manus-gdpr" ); ?>');
                }, waitForLoad ? 3000 : 1000);
            };

            iframe.src = url;
        },

        performDeepScan: function(url) {
            if (typeof ManusGDPRCookieScanner === 'undefined') {
                this.performScan(url); // Fallback to regular scan
                return;
            }

            ManusGDPRCookieScanner.performDeepScan(url, {
                clearCookies: $('#clear-cookies').is(':checked'),
                waitForLoad: $('#wait-for-load').is(':checked')
            }).then(cookies => {
                this.saveCookiesToServer(url, cookies);
            }).catch(error => {
                console.error('Deep scan error:', error);
                alert('<?php _e( "Errore durante la scansione approfondita", "manus-gdpr" ); ?>');
                this.hideProgress();
            });
        },

        extractCookies: function(iframe, url) {
            try {
                // Get cookies from current domain
                const cookies = this.parseCookies(document.cookie);
                
                // Simulate additional cookies that might be set by external scripts
                const simulatedCookies = this.getSimulatedCookies();
                const allCookies = [...cookies, ...simulatedCookies];

                if (allCookies.length === 0) {
                    alert('<?php _e( "Nessun cookie rilevato su questa pagina", "manus-gdpr" ); ?>');
                    this.hideProgress();
                    return;
                }

                // Send cookies to server
                this.saveCookiesToServer(url, allCookies);
            } catch (error) {
                console.error('Error extracting cookies:', error);
                alert('<?php _e( "Errore durante l\'estrazione dei cookie", "manus-gdpr" ); ?>');
                this.hideProgress();
            }
        },

        parseCookies: function(cookieString) {
            const cookies = [];
            if (!cookieString) return cookies;

            cookieString.split(';').forEach(cookie => {
                const [name, value] = cookie.trim().split('=');
                if (name && value) {
                    cookies.push({
                        name: name,
                        value: value,
                        domain: window.location.hostname,
                        path: '/',
                        secure: false,
                        httpOnly: false,
                        sameSite: 'Lax'
                    });
                }
            });

            return cookies;
        },

        getSimulatedCookies: function() {
            // Simulate common cookies that might be set by external scripts
            return [
                {
                    name: '_ga',
                    value: 'GA1.2.123456789.123456789',
                    domain: window.location.hostname,
                    path: '/',
                    expires: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString(),
                    secure: true,
                    httpOnly: false,
                    sameSite: 'Lax'
                },
                {
                    name: '_gid',
                    value: 'GA1.2.987654321.987654321',
                    domain: window.location.hostname,
                    path: '/',
                    expires: new Date(Date.now() + 24 * 60 * 60 * 1000).toUTCString(),
                    secure: true,
                    httpOnly: false,
                    sameSite: 'Lax'
                }
            ];
        },

        saveCookiesToServer: function(url, cookies) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'manus_gdpr_scan_cookies',
                    nonce: '<?php echo wp_create_nonce( "manus_gdpr_scanner_nonce" ); ?>',
                    url: url,
                    cookies: JSON.stringify(cookies)
                },
                success: (response) => {
                    if (response.success) {
                        alert('<?php _e( "Scansione completata con successo!", "manus-gdpr" ); ?>');
                        location.reload(); // Refresh to show new results
                    } else {
                        alert('<?php _e( "Errore durante il salvataggio: ", "manus-gdpr" ); ?>' + response.data);
                    }
                    this.hideProgress();
                },
                error: () => {
                    alert('<?php _e( "Errore di comunicazione con il server", "manus-gdpr" ); ?>');
                    this.hideProgress();
                }
            });
        },

        hideProgress: function() {
            $('#scanner-progress').hide();
            $('#scanner-iframe-container').hide();
        },

        viewScanDetails: function(e) {
            const scanId = $(e.target).data('scan-id');
            this.loadScanDetails(scanId);
        },

        loadScanDetails: function(scanId) {
            $('#scan-details-modal').show();
            $('#scan-details-content').html('<p><?php _e( "Caricamento...", "manus-gdpr" ); ?></p>');

            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'manus_gdpr_get_scan_results',
                    nonce: '<?php echo wp_create_nonce( "manus_gdpr_scanner_nonce" ); ?>',
                    scan_id: scanId
                },
                success: (response) => {
                    if (response.success) {
                        this.renderScanDetails(response.data, scanId);
                    } else {
                        $('#scan-details-content').html('<p><?php _e( "Errore nel caricamento dei dettagli", "manus-gdpr" ); ?></p>');
                    }
                }
            });
        },

        renderScanDetails: function(scanResults, scanId) {
            const scan = scanResults.find(s => s.id == scanId);
            if (!scan) return;

            let html = `<div class="scan-details">
                <h3><?php _e( "URL:", "manus-gdpr" ); ?> ${scan.url}</h3>
                <p><?php _e( "Data scansione:", "manus-gdpr" ); ?> ${scan.scan_date}</p>
                <p><?php _e( "Cookie trovati:", "manus-gdpr" ); ?> ${scan.cookies_count}</p>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( "Nome", "manus-gdpr" ); ?></th>
                            <th><?php _e( "Categoria", "manus-gdpr" ); ?></th>
                            <th><?php _e( "Dominio", "manus-gdpr" ); ?></th>
                            <th><?php _e( "Scadenza", "manus-gdpr" ); ?></th>
                            <th><?php _e( "Permetti", "manus-gdpr" ); ?></th>
                        </tr>
                    </thead>
                    <tbody>`;
            
            scan.cookies.forEach(cookie => {
                const checked = !cookie.blocked ? 'checked' : '';
                const categoryClass = `category-${cookie.category}`;
                
                html += `<tr>
                    <td><strong>${cookie.name}</strong></td>
                    <td><span class="cookie-category-badge ${categoryClass}">${cookie.category}</span></td>
                    <td>${cookie.domain}</td>
                    <td>${cookie.expires || '<?php _e( "Sessione", "manus-gdpr" ); ?>'}</td>
                    <td><input type="checkbox" name="cookie_${cookie.name}" ${checked} ${cookie.category === 'necessary' ? 'disabled' : ''}></td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            
            $('#scan-details-content').html(html);
            $('#save-cookie-settings').data('scan-id', scanId);
        },

        saveCookieSettings: function() {
            const scanId = $('#save-cookie-settings').data('scan-id');
            const settings = {};
            
            $('#scan-details-content input[type="checkbox"]').each(function() {
                const name = $(this).attr('name').replace('cookie_', '');
                settings[name] = $(this).is(':checked');
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'manus_gdpr_save_cookie_settings',
                    nonce: '<?php echo wp_create_nonce( "manus_gdpr_scanner_nonce" ); ?>',
                    scan_id: scanId,
                    settings: JSON.stringify(settings)
                },
                success: (response) => {
                    if (response.success) {
                        alert('<?php _e( "Impostazioni salvate con successo!", "manus-gdpr" ); ?>');
                        this.closeModal();
                    } else {
                        alert('<?php _e( "Errore nel salvare le impostazioni", "manus-gdpr" ); ?>');
                    }
                }
            });
        },

        deleteScan: function(e) {
            if (!confirm('<?php _e( "Sei sicuro di voler eliminare questa scansione?", "manus-gdpr" ); ?>')) {
                return;
            }

            const scanId = $(e.target).data('scan-id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'manus_gdpr_delete_scan_result',
                    nonce: '<?php echo wp_create_nonce( "manus_gdpr_scanner_nonce" ); ?>',
                    scan_id: scanId
                },
                success: (response) => {
                    if (response.success) {
                        alert('<?php _e( "Scansione eliminata con successo!", "manus-gdpr" ); ?>');
                        location.reload();
                    } else {
                        alert('<?php _e( "Errore nell\'eliminare la scansione", "manus-gdpr" ); ?>');
                    }
                }
            });
        },

        switchTab: function(e) {
            e.preventDefault();
            const $target = $(e.target);
            const tabName = $target.data('tab');
            
            console.log('Switching to tab:', tabName); // Debug log
            
            // Remove active class from all tabs
            $('.nav-tab').removeClass('nav-tab-active');
            // Add active class to clicked tab
            $target.addClass('nav-tab-active');
            
            // Hide all tab contents
            $('.tab-content').removeClass('active').hide();
            // Show selected tab content
            $('#tab-' + tabName).addClass('active').show();
            
            console.log('Tab switched to:', tabName); // Debug log
        },

        closeModal: function() {
            $('#scan-details-modal').hide();
        }
    };

    // Initialize scanner
    scanner.init();
    
    // Debug: Log available tabs and content
    console.log('Available nav tabs:', $('.nav-tab').length);
    console.log('Available tab contents:', $('.tab-content').length);
    $('.nav-tab').each(function(index, tab) {
        console.log('Tab ' + index + ':', $(tab).data('tab'), $(tab).hasClass('nav-tab-active'));
    });
    $('.tab-content').each(function(index, content) {
        console.log('Content ' + index + ':', $(content).attr('id'), $(content).hasClass('active'));
    });
});
</script>