<?php
/**
 * Plugin deactivation class
 *
 * @package UKMP_EMAIL
 */

if (!defined('ABSPATH')) {
    exit;
}

class UKMP_Email_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear any scheduled events
        wp_clear_scheduled_hook('ukmp_email_cleanup_cache');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear cache if needed
        self::clear_cache();
    }

    /**
     * Clear plugin cache
     */
    private static function clear_cache() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ukmp_email_cache';
        $wpdb->query("DELETE FROM $table_name");
    }
}