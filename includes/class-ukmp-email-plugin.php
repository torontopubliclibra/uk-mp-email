<?php
/**
 * Main plugin class for UK MP Email Search
 *
 * @package UKMP_Email
 */

if (!defined('ABSPATH')) {
    exit;
}

class UKMP_Email_Plugin {
    
    /**
     * Plugin instance
     * @var UKMP_Email_Plugin
     */
    private static $instance = null;
    
    /**
     * Admin instance
     * @var UKMP_Email_Admin
     */
    public $admin;
    
    /**
     * Public instance
     * @var UKMP_Email_Public
     */
    public $public;
    
    /**
     * API handler instance
     * @var UKMP_Email_Handler
     */
    public $api_handler;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the plugin
     */
    private function init() {
        // Load plugin text domain for internationalization
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Initialize plugin components
        add_action('init', array($this, 'init_components'));
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core includes
        require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/class-ukmp-email-loader.php';
        require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/class-ukmp-email-settings.php';
        
        // API handler
        require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/api/class-ukmp-email-handler.php';
        require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/api/class-ukmp-email-cache.php';
        
        // Always load public class for shortcodes
        require_once UKMP_EMAIL_PLUGIN_PATH . 'public/class-ukmp-email-public.php';
        
        // Admin area
        if (is_admin()) {
            require_once UKMP_EMAIL_PLUGIN_PATH . 'admin/class-ukmp-email-admin.php';
        }
    }

    /**
     * Initialize plugin components
     */
    public function init_components() {
        // Initialize API handler
        $this->api_handler = new UKMP_Email_Handler();
        
        // Initialize admin area
        if (is_admin()) {
            $this->admin = new UKMP_Email_Admin();
        }
        
        // Always initialize public area for shortcodes
        $this->public = new UKMP_Email_Public();
    }

    /**
     * Define admin hooks
     */
    private function define_admin_hooks() {
        // Admin hooks will be handled by the admin class
    }

    /**
     * Define public hooks
     */
    private function define_public_hooks() {
        // Public hooks will be handled by the public class
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            UKMP_EMAIL_TEXT_DOMAIN,
            false,
            dirname(plugin_basename(UKMP_EMAIL_PLUGIN_FILE)) . '/languages'
        );
    }
}