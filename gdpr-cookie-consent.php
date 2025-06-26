<?php
/**
 * Plugin Name: Rubik GDPR Cookie Consent
 * Description: Custom plugin for cookie consent management and GDPR compliance, compatible with Google AdSense.
 * Version: 1.0.7
 * Author: Matteo Morreale
 * Author URI:        https://matteomorreale.it
 * Author URI: https://github.com/matteomorreale
 * License: CC BY-NC 4.0
 * License URI: https://creativecommons.org/licenses/by-nc/4.0/
 * Text Domain: manus-gdpr
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8.1
 * Requires PHP: 7.4
 * Network: false
 */

/*
    Copyright (C) 2025 Matteo Morreale

    This work is licensed under Creative Commons Attribution-NonCommercial 4.0 International License.
    
    You are free to:
    - Share: copy and redistribute the material in any medium or format
    - Adapt: remix, transform, and build upon the material
    
    Under the following terms:
    - Attribution: You must give appropriate credit to Matteo Morreale, provide a link to the license, 
      and indicate if changes were made. You may do so in any reasonable manner, but not in any way 
      that suggests the licensor endorses you or your use.
    - NonCommercial: You may not use the material for commercial purposes.
    
    Full license: https://creativecommons.org/licenses/by-nc/4.0/
    
    Original Author: Matteo Morreale
    GitHub: https://github.com/matteomorreale
    
    The backbone was made with Manus AI, but don't worry. It didn't even compile.
    Than I've used Claude to fix it, but still: it didn't work.
    Finally I've used ChatGPT to fix other things, but still: didn't work.
    At least I've used my brain to fix the rest.
    Now it works, but I don't know how.
    I hope you will enjoy it.
    If you find any bug, please report it to me.
    I can give it a look and maybe fix it, or maybe not and I'll just say that it's a feature.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'MANUS_GDPR_VERSION', '1.0.7' );
define( 'MANUS_GDPR_PATH', plugin_dir_path( __FILE__ ) );
define( 'MANUS_GDPR_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files.
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-activator.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-deactivator.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-admin.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-frontend.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-database.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-cookie-scanner-config.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-cookie-scanner.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing hooks.
 */
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr.php';

/**
 * Begins execution of the plugin.
 * Since everything within the plugin is registered via hooks, the action
 * can be taken at any point in the plugin's life.
 */
function run_manus_gdpr() {
    $plugin = new Manus_GDPR();
    $plugin->run();
}
run_manus_gdpr();

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-manus-gdpr-activator.php
 */
function activate_manus_gdpr() {
    Manus_GDPR_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_manus_gdpr' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-manus-gdpr-deactivator.php
 */
function deactivate_manus_gdpr() {
    Manus_GDPR_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_manus_gdpr' );

/**
 * Debug function to test GDPR consent behavior
 * Add ?gdpr_debug=1 to any URL to see consent status
 */
function manus_gdpr_debug_info() {
    if ( isset( $_GET['gdpr_debug'] ) && current_user_can( 'manage_options' ) ) {
        echo '<div style="position: fixed; top: 10px; right: 10px; background: white; padding: 15px; border: 2px solid #333; z-index: 999999; font-family: monospace; font-size: 12px; max-width: 400px;">';
        echo '<h4>GDPR Debug Info</h4>';
        echo '<strong>Consent Cookie:</strong> ' . ( isset( $_COOKIE['manus_gdpr_consent'] ) ? $_COOKIE['manus_gdpr_consent'] : 'Not set' ) . '<br>';
        echo '<strong>Consent Data Cookie:</strong> ' . ( isset( $_COOKIE['manus_gdpr_consent_data'] ) ? $_COOKIE['manus_gdpr_consent_data'] : 'Not set' ) . '<br>';
        echo '<strong>Time:</strong> ' . date( 'Y-m-d H:i:s' ) . '<br>';
        
        if ( isset( $_COOKIE['manus_gdpr_consent_data'] ) ) {
            $consent_data = json_decode( $_COOKIE['manus_gdpr_consent_data'], true );
            if ( $consent_data && isset( $consent_data['data'] ) ) {
                echo '<strong>Advertising Consent:</strong> ' . ( $consent_data['data']['advertising'] ? 'YES' : 'NO' ) . '<br>';
                echo '<strong>Analytics Consent:</strong> ' . ( $consent_data['data']['analytics'] ? 'YES' : 'NO' ) . '<br>';
                echo '<strong>Functional Consent:</strong> ' . ( $consent_data['data']['functional'] ? 'YES' : 'NO' ) . '<br>';
            }
        }
        
        echo '<hr><small>Add ?gdpr_debug=1 to URL to see this info</small>';
        echo '</div>';
        
        // Add JavaScript debug info
        echo '<script>
        console.group("GDPR Debug Info");
        console.log("Consent cookie:", document.cookie.match(/manus_gdpr_consent=([^;]+)/));
        console.log("Consent data cookie:", document.cookie.match(/manus_gdpr_consent_data=([^;]+)/));
        if (typeof window.__tcfapi === "function") {
            window.__tcfapi("getTCData", 2, function(tcData, success) {
                console.log("TCF Data:", tcData);
                console.log("Purpose consents:", tcData ? tcData.purpose.consents : "No TCF data");
                console.log("Vendor consents:", tcData ? tcData.vendor.consents : "No vendor data");
            });
        }
        console.groupEnd();
        </script>';
    }
}
add_action( 'wp_footer', 'manus_gdpr_debug_info' );