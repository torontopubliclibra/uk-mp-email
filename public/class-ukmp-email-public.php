<?php
/**
 * Public-facing functionality for UK MP Email Search
 *
 * @package UKMP_Email
 */

if (!defined('ABSPATH')) {
    exit;
}

// Fallback for esc_attr() if not available (for non-WordPress environments)
if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

class UKMP_Email_Public {

    /**
     * Constructor
     */
    public function __construct() {
        // Register shortcodes immediately
        $this->register_shortcodes();
        
        // AJAX handlers
        if (function_exists('add_action')) {
            add_action('wp_ajax_ukmp_email_search', array($this, 'handle_ajax_search'));
        }
        add_action('wp_ajax_nopriv_ukmp_email_search', array($this, 'handle_ajax_search'));
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Register shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('ukmp_email_search', array($this, 'search_form_shortcode'));
        add_shortcode('ukmp_email_results', array($this, 'results_shortcode'));
        add_shortcode('ukmp_email_test', array($this, 'test_shortcode'));
    }

    /**
     * Test shortcode
     */
    public function test_shortcode($atts) {
        return '<div class="ukmp-email-status-message">✅ UK MP Email Search Plugin is active and working!</div>';
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        
        wp_enqueue_script(
            'ukmp-email-public',
            UKMP_EMAIL_PLUGIN_URL . 'assets/js/public-simple.js',
            array('jquery'),
            UKMP_EMAIL_VERSION,
            true
        );

        wp_localize_script('ukmp-email-public', 'ukmpemail', array(
            'ajax_url' => admin_url() . 'admin-ajax.php',
            'nonce' => wp_create_nonce('ukmp_email_public_nonce'),
        ));

        wp_enqueue_style(
            'ukmp-email-public',
            UKMP_EMAIL_PLUGIN_URL . 'assets/css/public.css',
            array(),
            UKMP_EMAIL_VERSION
        );
    }

    /**
     * Search form shortcode
     */
    public function search_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'button_text' => 'Find My MP',
            'name_placeholder' => 'Full name',
            'email_placeholder' => 'Email address',
            'postcode_placeholder' => 'UK postcode (e.g. SW1A 1AA)',
        ), $atts);

        ob_start();
        ?>
        <div class="ukmp-email-search-form">
            <div class="ukmp-email-form-inner">
                <h2 class="form-title">Contact Your MP</h2>
                <p class="form-subtitle">Please enter your name, email address, and postcode below to find which Member of Parliament to contact.</p>
                
                <form id="ukmp-email-search-form" method="post">
                    <?php wp_nonce_field('ukmp_email_public_nonce', 'ukmp_email_nonce'); ?>
                    
                    <input 
                        type="text" 
                        id="ukmp-email-name-input" 
                        name="user_name" 
                        placeholder="<?php echo esc_attr($atts['name_placeholder']); ?>"
                        maxlength="100"
                        required
                    />
                    <input 
                        type="email" 
                        id="ukmp-email-user-email-input" 
                        name="user_email" 
                        placeholder="<?php echo esc_attr($atts['email_placeholder']); ?>"
                        maxlength="150"
                        required
                    />
                    <input 
                        type="text" 
                        id="ukmp-email-search-input" 
                        name="search_query" 
                        placeholder="<?php echo esc_attr($atts['postcode_placeholder']); ?>"
                        pattern="[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]?\s*[0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}"
                        title="Please enter a valid UK postcode"
                        maxlength="8"
                        required
                    />
                    
                    <button type="submit" id="ukmp-email-search-button">
                        <?php echo esc_html($atts['button_text']); ?>
                    </button>
                </form>
            </div>

            <div id="ukmp-email-search-loading" class="ukmp-email-loading">
                <span class="ukmp-email-loading-spinner"></span>
                Finding your MP and their contact details...
            </div>
        </div>
        
        <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Results display shortcode
     */
    public function results_shortcode($atts) {
        ob_start();
        ?>
        <div id="ukmp-email-results" class="ukmp-email-results">
            <div class="results-content">
                <!-- MP results will be loaded here via AJAX -->
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle AJAX search
     */
    public function handle_ajax_search() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'ukmp_email_public_nonce')) {
            wp_send_json_error('Security check failed.');
            return;
        }

        $postcode = sanitize_text_field($_POST['search_query'] ?? '');
        $user_name = sanitize_text_field($_POST['user_name'] ?? '');
        $user_email = sanitize_email($_POST['user_email'] ?? '');
        
        if (empty($postcode)) {
            wp_send_json_error('UK postcode is required.');
            return;
        }

        // Clean postcode (remove spaces)
        $clean_postcode = str_replace(' ', '', strtoupper(trim($postcode)));
        
        // Validate UK postcode format
        $pattern = '/^[A-Z]{1,2}[0-9R][0-9A-Z]?[0-9][ABD-HJLNP-UW-Z]{2}$/';
        if (!preg_match($pattern, $clean_postcode)) {
            wp_send_json_error('Please enter a valid UK postcode.');
            return;
        }

        // Make first API call to search for MP
        $search_url = 'https://members-api.parliament.uk/api/Members/Search?Location=' . $clean_postcode . '&skip=0&take=20';
        
        $response = wp_remote_get($search_url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'UK-Parliament-Plugin/1.0'
            )
        ));

        if (is_wp_error($response)) {
            wp_send_json_error('Failed to connect to Parliament API: ' . $response->get_error_message());
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            wp_send_json_error('Parliament API returned error code: ' . $response_code);
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data || !isset($data['items']) || empty($data['items'])) {
            wp_send_json_error('No MP found for postcode ' . $postcode . '. Please check your postcode and try again.');
            return;
        }

        $mp_data = $data['items'][0]['value'] ?? null;
        if (!$mp_data) {
            wp_send_json_error('Invalid MP data received.');
            return;
        }

        $mp_id = $mp_data['id'] ?? '';
        $mp_name = $mp_data['nameFullTitle'] ?? '';
        $location = $mp_data['latestHouseMembership']['membershipFromName'] ?? 
                   $mp_data['latestHouseMembership']['membershipFrom'] ?? '';

        // Make second API call for contact information
        $email = '';
        if ($mp_id) {
            $contact_url = 'https://members-api.parliament.uk/api/Members/' . $mp_id . '/Contact';
            $contact_response = wp_remote_get($contact_url, array(
                'timeout' => 30,
                'headers' => array(
                    'User-Agent' => 'UK-Parliament-Plugin/1.0'
                )
            ));

            if (!is_wp_error($contact_response) && wp_remote_retrieve_response_code($contact_response) === 200) {
                $contact_body = wp_remote_retrieve_body($contact_response);
                $contact_data = json_decode($contact_body, true);
                
                if ($contact_data && isset($contact_data['value']) && is_array($contact_data['value']) && count($contact_data['value']) > 0) {
                    $email = $contact_data['value'][0]['email'] ?? '';
                }
            }
        }

        // Generate email preview
        $email_preview = $this->generate_email_preview($mp_name, $email, $postcode, $location, $user_name, $user_email);

        // Generate HTML response
        $html = $this->generate_mp_html($mp_name, $email, $location, $postcode, $mp_id, $email_preview, $user_name, $user_email);

        wp_send_json_success(array(
            'mp' => array(
                'name' => $mp_name,
                'email' => $email,
                'location' => $location,
                'id' => $mp_id
            ),
            'postcode' => $postcode,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'html' => $html
        ));
    }

    /**
     * Generate MP HTML display
     */
    private function generate_mp_html($name, $email, $location, $postcode, $mp_id, $email_preview, $user_name = '', $user_email = '') {
        $html = '<div class="ukmp-email-mp-profile">';
        $html .= '<div class="mp-profile-inner">';
        
        $html .= '<div class="mp-header">';
        $html .= '<p class="mp-constituency">Your Member of Parliament:</p>';
        if ($mp_id) {
            $html .= '<h3 class="mp-name"><a href="https://members.parliament.uk/member/' . esc_attr($mp_id) . '" target="_blank">' . esc_html($name) . '</a></h3>';
        } else {
            $html .= '<h3 class="mp-name">' . esc_html($name) . '</h3>';
        }
        $html .= '</div>';
        
        $html .= '<div class="mp-details">';
        
        if ($location) {
            $html .= '<div class="detail-item">';
            $html .= '<strong class="detail-item-label">Represents:</strong>';
            $html .= '<span class="detail-item-value">' . esc_html($location) . '</span>';
            $html .= '</div>';
        }

        if ($email) {
            $html .= '<div class="detail-item">';
            $html .= '<strong class="detail-item-label">Email address:</strong>';
            $html .= '<span class="detail-item-value"><a href="mailto:' . esc_attr($email) . '" target="_blank" class="detail-item-email-link">' . esc_html($email) . '</a></span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Email preview section
        if ($email && $email_preview) {
            // Generate subject for preview
            $subject_template = UKMP_EMAIL_Settings::get_setting('email_subject');
            $email_subject = $this->process_email_template($subject_template, $name, $email, $postcode, $location, $user_name, $user_email);
            
            $html .= '<div class="ukmp-email-preview">';
            
            // Subject line preview as editable input
            $html .= '<div class="ukmp-email-subject-container">';
            $html .= '<label for="ukmp-email-subject-input" class="email-subject-label">Subject:</label>';
            $html .= '<input type="text" id="ukmp-email-subject-input" class="ukmp-email-subject-preview" value="' . esc_attr($email_subject) . '" />';
            $html .= '</div>';
            
            // Email body preview as editable textarea
            $html .= '<label for="ukmp-email-body-preview" class="email-body-label">Message:</label>';
            $html .= '<textarea class="ukmp-email-body-preview" rows="10" data-mp-name="' . esc_attr($name) . '" data-mp-email="' . esc_attr($email) . '" data-postcode="' . esc_attr($postcode) . '" data-location="' . esc_attr($location) . '" data-user-name="' . esc_attr($user_name) . '" data-user-email="' . esc_attr($user_email) . '">' . esc_html($email_preview) . '</textarea>';
            $html .= '<p class="email-preview-footer">Feel free to personalize the content above; it will be opened in your default email service when you click the button below.</p>';
            $html .= '</div>';
        }
        
        $html .= '<div class="mp-actions">';
        
        if ($email) {
            $email_url = $this->generate_email_url($name, $email, $postcode, $location, $user_name, $user_email);
            $html .= '<a href="' . esc_attr($email_url) . '" target="_blank" class="contact-mp-link">Send</a>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate email URL with template
     */
    private function generate_email_url($name, $email, $postcode, $location, $user_name = '', $user_email = '') {
        // Get email template and subject from settings
        $template = UKMP_EMAIL_Settings::get_setting('email_template');
        $subject_template = UKMP_EMAIL_Settings::get_setting('email_subject');
        
        if (empty($template)) {
            return 'mailto:' . $email;
        }
        
        // Process template placeholders for body
        $email_body = $this->process_email_template($template, $name, $email, $postcode, $location, $user_name, $user_email);
        
        // Process template placeholders for subject
        $subject = $this->process_email_template($subject_template, $name, $email, $postcode, $location, $user_name, $user_email);
        
        // Fallback to default if subject is empty
        if (empty($subject)) {
            $subject = 'Correspondence from your constituent in ' . strtoupper($location);
        }
        
        // Create mailto URL with subject and body
        $mailto_url = 'mailto:' . urlencode($email) . '?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($email_body);
        
        return $mailto_url;
    }

    /**
     * Process email template and replace placeholders
     */
    private function process_email_template($template, $name, $email, $postcode, $location, $user_name = '', $user_email = '') {
        $placeholders = array(
            '{MP_NAME}' => $name,
            '{POSTCODE}' => strtoupper($postcode),
            '{MP_EMAIL}' => $email,
            '{LOCATION}' => $location,
            '[Your name]' => !empty($user_name) ? $user_name : '[Your name]',
            '[Your Name]' => !empty($user_name) ? $user_name : '[Your Name]',
            '[Your email]' => !empty($user_email) ? $user_email : '[Your email]',
            '[Your Email]' => !empty($user_email) ? $user_email : '[Your Email]',
        );
        
        $processed_template = str_replace(array_keys($placeholders), array_values($placeholders), $template);
        
        return $processed_template;
    }

    /**
     * Generate email preview
     */
    private function generate_email_preview($name, $email, $postcode, $location, $user_name = '', $user_email = '') {
        // Get email template from settings
        $template = UKMP_EMAIL_Settings::get_setting('email_template');
        
        if (empty($template)) {
            return '';
        }
        
        // Process template placeholders
        return $this->process_email_template($template, $name, $email, $postcode, $location, $user_name, $user_email);
    }
}