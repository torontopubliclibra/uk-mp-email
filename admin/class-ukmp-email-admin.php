<?php
/**
 * Admin functionality for UK MP Email Search
 *
 * @package UKMP_Email
 */

if (!defined('ABSPATH')) {
    exit;
}

class UKMP_Email_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_ukmp_email_test_connection', array($this, 'test_api_connection'));
        add_action('wp_ajax_ukmp_email_clear_cache', array($this, 'clear_cache'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('UK MP Email Settings', UKMP_EMAIL_TEXT_DOMAIN),
            __('UK MP Email Settings', UKMP_EMAIL_TEXT_DOMAIN),
            'manage_options',
            'ukmp-email-settings',
            array($this, 'admin_page')
        );
    }

    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting(
            'ukmp_email_settings_group',
            'ukmp_email_settings',
            array($this, 'validate_settings')
        );

        // Email template section
        add_settings_section(
            'ukmp_email_template_section',
            __('Email Template Settings', UKMP_EMAIL_TEXT_DOMAIN),
            array($this, 'email_template_section_callback'),
            'ukmp-email-settings'
        );

        // Email template field
        add_settings_field(
            'email_template',
            __('Email Template', UKMP_EMAIL_TEXT_DOMAIN),
            array($this, 'email_template_callback'),
            'ukmp-email-settings',
            'ukmp_email_template_section'
        );

        // Email subject field
        add_settings_field(
            'email_subject',
            __('Email Subject', UKMP_EMAIL_TEXT_DOMAIN),
            array($this, 'email_subject_callback'),
            'ukmp-email-settings',
            'ukmp_email_template_section'
        );

        // Cache section
        add_settings_section(
            'ukmp_email_cache_section',
            __('Cache Settings', UKMP_EMAIL_TEXT_DOMAIN),
            array($this, 'cache_section_callback'),
            'ukmp-email-settings'
        );

        // Cache enabled field
        add_settings_field(
            'cache_enabled',
            __('Enable Cache', UKMP_EMAIL_TEXT_DOMAIN),
            array($this, 'cache_enabled_callback'),
            'ukmp-email-settings',
            'ukmp_email_cache_section'
        );

        // Cache duration field
        add_settings_field(
            'cache_duration',
            __('Cache Duration (seconds)', UKMP_EMAIL_TEXT_DOMAIN),
            array($this, 'cache_duration_callback'),
            'ukmp-email-settings',
            'ukmp_email_cache_section'
        );
    }

    /**
     * Validate settings
     */
    public function validate_settings($input) {
        return UKMP_EMAIL_Settings::validate_settings($input);
    }

    /**
     * Admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="ukmp-email-admin-header">
                <p><?php _e('Configure your UK MP Email plugin settings including email templates and performance options.', UKMP_EMAIL_TEXT_DOMAIN); ?></p>
            </div>

            <form action="options.php" method="post">
                <?php
                settings_fields('ukmp_email_settings_group');
                do_settings_sections('ukmp-email-settings');
                submit_button();
                ?>
            </form>

            <div class="ukmp-email-admin-tools">
                <h2><?php _e('Tools', UKMP_EMAIL_TEXT_DOMAIN); ?></h2>
                
                <p>
                    <button type="button" id="test-api-connection" class="button">
                        <?php _e('Test API Connection', UKMP_EMAIL_TEXT_DOMAIN); ?>
                    </button>
                    <span id="connection-status"></span>
                </p>

                <p>
                    <button type="button" id="clear-cache" class="button">
                        <?php _e('Clear Cache', UKMP_EMAIL_TEXT_DOMAIN); ?>
                    </button>
                    <span id="cache-status"></span>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Section callbacks
     */
    public function cache_section_callback() {
        echo '<p>' . __('Configure cache settings to improve performance.', UKMP_EMAIL_TEXT_DOMAIN) . '</p>';
    }

    public function email_template_section_callback() {
        echo '<p>' . __('Configure the default email template that will be used when users click "Email Your MP". You can use placeholders like {MP_NAME} and {POSTCODE} which will be automatically replaced.', UKMP_EMAIL_TEXT_DOMAIN) . '</p>';
    }

    /**
     * Field callbacks
     */

    public function cache_enabled_callback() {
        $value = UKMP_EMAIL_Settings::get_setting('cache_enabled');
        echo '<input type="checkbox" name="ukmp_email_settings[cache_enabled]" value="1" ' . checked(1, $value, false) . ' />';
        echo ' ' . __('Enable caching to improve performance', UKMP_EMAIL_TEXT_DOMAIN);
    }

    public function cache_duration_callback() {
        $value = UKMP_EMAIL_Settings::get_setting('cache_duration');
        echo '<input type="number" name="ukmp_email_settings[cache_duration]" value="' . esc_attr($value) . '" min="60" max="86400" class="small-text" />';
        echo '<p class="description">' . __('How long to cache API responses (in seconds). Default: 3600 (1 hour).', UKMP_EMAIL_TEXT_DOMAIN) . '</p>';
    }

    public function email_template_callback() {
        $value = UKMP_EMAIL_Settings::get_setting('email_template');
        echo '<textarea name="ukmp_email_settings[email_template]" rows="10" cols="80" class="large-text" style="font-family: monospace;">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">';
        echo '<strong>' . __('Available placeholders:', UKMP_EMAIL_TEXT_DOMAIN) . '</strong><br>';
        echo '<code>{MP_NAME}</code> - ' . __('Full name and title of the MP', UKMP_EMAIL_TEXT_DOMAIN) . '<br>';
        echo '<code>{POSTCODE}</code> - ' . __('The postcode entered by the user', UKMP_EMAIL_TEXT_DOMAIN) . '<br>';
        echo '<code>{MP_EMAIL}</code> - ' . __('MP\'s email address', UKMP_EMAIL_TEXT_DOMAIN) . '<br>';
        echo '<code>{LOCATION}</code> - ' . __('The constituency or area the MP represents', UKMP_EMAIL_TEXT_DOMAIN) . '<br>';
        echo '<code>[Your name]</code> or <code>[Your Name]</code> - ' . __('Replaced with the name entered by the user (if provided)', UKMP_EMAIL_TEXT_DOMAIN) . '<br>';
        echo '<code>[Your email]</code> or <code>[Your Email]</code> - ' . __('Replaced with the email address entered by the user (if provided)', UKMP_EMAIL_TEXT_DOMAIN) . '<br><br>';
        echo __('This template will be used as the email body when users click "Email Your MP". The placeholders will be automatically replaced with the actual MP details and user information. A preview will be shown to users before they send. Users can enter their name and email address in the search form to personalize the message.', UKMP_EMAIL_TEXT_DOMAIN);
        echo '</p>';
    }

    public function email_subject_callback() {
        $value = UKMP_EMAIL_Settings::get_setting('email_subject');
        echo '<input type="text" name="ukmp_email_settings[email_subject]" value="' . esc_attr($value) . '" class="regular-text" style="width: 100%; max-width: 600px;" />';
        echo '<p class="description">';
        echo __('Subject line for emails sent to MPs. Available placeholders: {MP_NAME}, {POSTCODE}, {LOCATION}, [Your name], [Your email]. This subject will be shown in the email preview.', UKMP_EMAIL_TEXT_DOMAIN);
        echo '</p>';
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_ukmp-email-settings') {
            return;
        }

        wp_enqueue_script(
            'ukmp-email-admin',
            UKMP_EMAIL_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            UKMP_EMAIL_VERSION,
            true
        );

        wp_localize_script('ukmp-email-admin', 'ukmpApiAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ukmp_email_admin_nonce'),
            'strings' => array(
                'testing' => __('Testing connection...', UKMP_EMAIL_TEXT_DOMAIN),
                'connection_success' => __('Connection successful!', UKMP_EMAIL_TEXT_DOMAIN),
                'connection_failed' => __('Connection failed:', UKMP_EMAIL_TEXT_DOMAIN),
                'clearing_cache' => __('Clearing cache...', UKMP_EMAIL_TEXT_DOMAIN),
                'cache_cleared' => __('Cache cleared successfully!', UKMP_EMAIL_TEXT_DOMAIN),
                'cache_clear_failed' => __('Failed to clear cache:', UKMP_EMAIL_TEXT_DOMAIN),
            )
        ));

        wp_enqueue_style(
            'ukmp-email-admin',
            UKMP_EMAIL_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            UKMP_EMAIL_VERSION
        );
    }

    /**
     * Test API connection via AJAX
     */
    public function test_api_connection() {
        check_ajax_referer('ukmp_email_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', UKMP_EMAIL_TEXT_DOMAIN));
        }

        $api_handler = new UKMP_EMAIL_Handler();
        $result = $api_handler->make_request('test'); // Assuming there's a test endpoint

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(__('API connection successful!', UKMP_EMAIL_TEXT_DOMAIN));
        }
    }

    /**
     * Clear cache via AJAX
     */
    public function clear_cache() {
        check_ajax_referer('ukmp_email_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', UKMP_EMAIL_TEXT_DOMAIN));
        }

        require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/api/class-ukmp-email-cache.php';
        $cache = new UKMP_EMAIL_Cache();
        $result = $cache->clear_all();

        if ($result !== false) {
            wp_send_json_success(__('Cache cleared successfully!', UKMP_EMAIL_TEXT_DOMAIN));
        } else {
            wp_send_json_error(__('Failed to clear cache.', UKMP_EMAIL_TEXT_DOMAIN));
        }
    }
}