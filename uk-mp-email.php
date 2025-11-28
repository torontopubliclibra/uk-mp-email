<?php
/**
 * Plugin Name: UK MP Email Search
 * Plugin URI: https://github.com/torontopubliclibra/uk-mp-email
 * Description: A WordPress plugin that finds the email addresses of UK Members of Parliament based on postcode input, and then drafts a templated email to send to them. It was designed and built by Dana Teagle for Not A Phase in 2025.
 * Version: 1.0.0
 * Author: Dana Rosamund Teagle
 * Author URI: https://danateagle.com
 * Text Domain: uk-mp-email
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('UKMP_EMAIL_PLUGIN_FILE', __FILE__);
define('UKMP_EMAIL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('UKMP_EMAIL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UKMP_EMAIL_VERSION', '1.0.0');
define('UKMP_EMAIL_TEXT_DOMAIN', 'uk-mp-email');

// Include the main plugin class
require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/class-ukmp-email-plugin.php';

/**
 * Initialize the plugin
 */
function ukmp_email_init() {
    // Ensure all WordPress functions are available
    if (!function_exists('add_action') || !function_exists('add_shortcode')) {
        return;
    }
    
    new UKMP_Email_Plugin();
}

// Hook into WordPress after all plugins are loaded
add_action('plugins_loaded', 'ukmp_email_init');

// Alternative hook for shortcodes if plugins_loaded is too early
add_action('init', function() {
    if (class_exists('UKMP_Email_Plugin') && !did_action('ukmp_email_shortcodes_registered')) {
        // Ensure shortcodes are registered
        do_action('ukmp_email_shortcodes_registered');
    }
});

/**
 * Plugin activation hook
 */
function ukmp_email_activate() {
    // Create database tables if needed
    require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/class-ukmp-email-activator.php';
    UKMP_Email_Activator::activate();
}
register_activation_hook(__FILE__, 'ukmp_email_activate');

/**
 * Plugin deactivation hook
 */
function ukmp_email_deactivate() {
    require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/class-ukmp-email-deactivator.php';
    UKMP_Email_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'ukmp_email_deactivate');