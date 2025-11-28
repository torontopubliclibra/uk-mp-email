<?php
/**
 * Plugin settings for UK MP Email Search
 *
 * @package UKMP_Email
 */

if (!defined('ABSPATH')) {
    exit;
}

class UKMP_Email_Settings {

    /**
     * Settings option name
     */
    const OPTION_NAME = 'ukmp_email_settings';

    /**
     * Default settings
     * @var array
     */
    private static $defaults = array(
        'api_url' => '',
        'api_key' => '',
        'cache_enabled' => true,
        'cache_duration' => 3600,
        'rate_limit' => 100,
        'timeout' => 30,
        'retry_attempts' => 3,
        'email_template' => "Dear {MP_NAME},\n\nI am writing to you, my Member of Parliament, as a constituent of {LOCATION}.\n\n[Your message here]\n\nI would appreciate your response on this matter.\n\nSincerely,\n[Your name]\n[Your email]",
        'email_subject' => "",
    );

    /**
     * Get all settings
     */
    public static function get_settings() {
        $settings = get_option(self::OPTION_NAME, self::$defaults);
        return wp_parse_args($settings, self::$defaults);
    }

    /**
     * Get a specific setting
     */
    public static function get_setting($key, $default = null) {
        $settings = self::get_settings();
        
        if (isset($settings[$key])) {
            return $settings[$key];
        }
        
        return $default !== null ? $default : (isset(self::$defaults[$key]) ? self::$defaults[$key] : null);
    }

    /**
     * Update settings
     */
    public static function update_settings($new_settings) {
        $current_settings = self::get_settings();
        $updated_settings = wp_parse_args($new_settings, $current_settings);
        
        return update_option(self::OPTION_NAME, $updated_settings);
    }

    /**
     * Update a specific setting
     */
    public static function update_setting($key, $value) {
        $settings = self::get_settings();
        $settings[$key] = $value;
        
        return self::update_settings($settings);
    }

    /**
     * Delete all settings
     */
    public static function delete_settings() {
        return delete_option(self::OPTION_NAME);
    }

    /**
     * Reset to defaults
     */
    public static function reset_to_defaults() {
        return update_option(self::OPTION_NAME, self::$defaults);
    }

    /**
     * Validate settings
     */
    public static function validate_settings($settings) {
        $validated = array();
        
        // Validate API URL
        if (isset($settings['api_url'])) {
            $validated['api_url'] = esc_url_raw($settings['api_url']);
        }
        
        // Validate API key
        if (isset($settings['api_key'])) {
            $validated['api_key'] = sanitize_text_field($settings['api_key']);
        }
        
        // Validate cache enabled
        if (isset($settings['cache_enabled'])) {
            $validated['cache_enabled'] = (bool) $settings['cache_enabled'];
        }
        
        // Validate cache duration
        if (isset($settings['cache_duration'])) {
            $validated['cache_duration'] = absint($settings['cache_duration']);
        }
        
        // Validate rate limit
        if (isset($settings['rate_limit'])) {
            $validated['rate_limit'] = absint($settings['rate_limit']);
        }
        
        // Validate timeout
        if (isset($settings['timeout'])) {
            $validated['timeout'] = absint($settings['timeout']);
        }
        
        // Validate retry attempts
        if (isset($settings['retry_attempts'])) {
            $validated['retry_attempts'] = absint($settings['retry_attempts']);
        }
        
        // Validate email template
        if (isset($settings['email_template'])) {
            $validated['email_template'] = wp_kses_post($settings['email_template']);
        }
        
        // Validate email subject
        if (isset($settings['email_subject'])) {
            $validated['email_subject'] = sanitize_text_field($settings['email_subject']);
        }
        
        return $validated;
    }
}