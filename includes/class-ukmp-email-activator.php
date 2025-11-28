<?php
/**
 * Plugin activation class for UK MP Email Search
 *
 * @package UKMP_Email
 */

if (!defined('ABSPATH')) {
    exit;
}

class UKMP_Email_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        // Create database tables if needed
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private static function create_tables() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ukmp_email_cache';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            cache_data longtext NOT NULL,
            expiry datetime DEFAULT CURRENT_TIMESTAMP,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY cache_key (cache_key)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $default_settings = array(
            'api_url' => 'https://members-api.parliament.uk/api/Members/Search',
            'api_key' => '',
            'cache_enabled' => true,
            'cache_duration' => 3600, // 1 hour
            'rate_limit' => 100, // requests per hour
            'email_template' => "Dear {MP_NAME},\n\nI am writing to you as my Member of Parliament representing {LOCATION}.\n\n[Your message here]\n\nI would appreciate your response on this matter.\n\nKind regards,\n[Your name]",
            'email_subject' => "Correspondence from your constituent in {POSTCODE}",
        );

        add_option('ukmp_email_settings', $default_settings);
    }
}