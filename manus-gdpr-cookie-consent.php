<?php
/**
 * Plugin Name: Rubik GDPR Cookie Consent
 * Description: Plugin personalizzato per la gestione del consenso ai cookie e la conformità GDPR, compatibile con Google AdSense.
 * Version: 1.0.1
 * Author: Matteo Morreale
 * License: GPL2
 */

/* 
    The backbone was made with Manus AI, but don't worry. It didn't even compile.
    Than I've used Claude to fix it, but still: it didn't work.
    Finally I've used ChatGPT to fix other things, but still: didn't work.
    At least I've used my brain to fix the rest.
    Now it works, but I don't know how.
    I hope you will enjoy it.
    If you find any bug, please report it to me.
    I can give it a look and maybe fix it, or maybe not an I'll just say that it's a feature.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'MANUS_GDPR_VERSION', '1.0.0' );
define( 'MANUS_GDPR_PATH', plugin_dir_path( __FILE__ ) );
define( 'MANUS_GDPR_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files.
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-activator.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-deactivator.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-admin.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-frontend.php';
require_once MANUS_GDPR_PATH . 'includes/class-manus-gdpr-database.php';

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