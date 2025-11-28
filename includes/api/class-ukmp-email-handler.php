<?php
/**
 * API Handler class for UK MP Email Search
 *
 * @package UKMP_Email
 */

if (!defined('ABSPATH')) {
    exit;
}

class UKMP_Email_Handler {

    /**
     * API base URL
     * @var string
     */
    private $api_url;

    /**
     * API key
     * @var string
     */
    private $api_key;

    /**
     * Cache handler
     * @var UKMP_EMAIL_Cache
     */
    private $cache;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api_url = UKMP_EMAIL_Settings::get_setting('api_url');
        $this->api_key = UKMP_EMAIL_Settings::get_setting('api_key');
        
        // Initialize cache
        require_once UKMP_EMAIL_PLUGIN_PATH . 'includes/api/class-ukmp-email-cache.php';
        $this->cache = new UKMP_EMAIL_Cache();
    }

    /**
     * Make API request
     */
    public function make_request($endpoint, $params = array(), $method = 'GET') {
        // Generate cache key
        $cache_key = $this->generate_cache_key($endpoint, $params, $method);
        
        // Try to get from cache first
        if (UKMP_EMAIL_Settings::get_setting('cache_enabled')) {
            $cached_result = $this->cache->get($cache_key);
            if ($cached_result !== false) {
                return $cached_result;
            }
        }

        // Prepare request
        $url = $this->build_url($endpoint);
        $args = $this->prepare_request_args($params, $method);
        
        // For GET requests with params, URL is built in prepare_request_args
        if ($method === 'GET' && !empty($params) && isset($args['url'])) {
            $url = $args['url'];
        }

        // Make the request
        $response = wp_remote_request($url, $args);

        // Handle response
        $result = $this->handle_response($response);

        // Cache the result if successful
        if (!is_wp_error($result) && UKMP_EMAIL_Settings::get_setting('cache_enabled')) {
            $cache_duration = UKMP_EMAIL_Settings::get_setting('cache_duration', 3600);
            $this->cache->set($cache_key, $result, $cache_duration);
        }

        return $result;
    }

    /**
     * Build full API URL
     */
    private function build_url($endpoint) {
        // For Parliament API contact endpoint
        if (!empty($endpoint) && strpos($endpoint, '/Contact') !== false) {
            return 'https://members-api.parliament.uk/api/Members/' . $endpoint;
        }
        
        // For Parliament API search, the URL is the endpoint itself
        if (empty($endpoint)) {
            return $this->api_url;
        }
        return trailingslashit($this->api_url) . ltrim($endpoint, '/');
    }

    /**
     * Prepare request arguments
     */
    private function prepare_request_args($params, $method) {
        $args = array(
            'method' => strtoupper($method),
            'timeout' => UKMP_EMAIL_Settings::get_setting('timeout', 30),
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'UKMP-EMAIL-Plugin/' . UKMP_EMAIL_VERSION,
            ),
        );

        // Add API key to headers if available
        if (!empty($this->api_key)) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->api_key;
        }

        // Add body for POST/PUT requests
        if (in_array($method, array('POST', 'PUT', 'PATCH')) && !empty($params)) {
            $args['body'] = wp_json_encode($params);
        } elseif ($method === 'GET' && !empty($params)) {
            // For Parliament API, we need to build the URL with query parameters
            $base_url = $this->build_url('');
            $args['url'] = add_query_arg($params, $base_url);
        }

        return $args;
    }

    /**
     * Handle API response
     */
    private function handle_response($response) {
        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        // Check for successful response
        if ($response_code >= 200 && $response_code < 300) {
            $data = json_decode($response_body, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            } else {
                return new WP_Error('json_decode_error', 'Failed to decode JSON response');
            }
        }

        // Handle error responses
        return new WP_Error(
            'api_error',
            sprintf('API request failed with status code: %d', $response_code),
            array('response_code' => $response_code, 'response_body' => $response_body)
        );
    }

    /**
     * Generate cache key
     */
    private function generate_cache_key($endpoint, $params, $method) {
        $key_data = array(
            'endpoint' => $endpoint,
            'params' => $params,
            'method' => $method,
            'api_url' => $this->api_url,
        );

        return 'ukmp_email_' . md5(serialize($key_data));
    }

    /**
     * Get MP contact information by ID
     */
    public function get_mp_contact($mp_id) {
        if (empty($mp_id)) {
            return new WP_Error('missing_mp_id', 'MP ID is required.');
        }
        
        // Use the contact endpoint
        $endpoint = $mp_id . '/Contact';
        return $this->make_request($endpoint);
    }

    /**
     * Search UK Parliament members by postcode
     */
    public function search_by_postcode($postcode, $filters = array()) {
        // Remove spaces from postcode
        $clean_postcode = str_replace(' ', '', strtoupper(trim($postcode)));
        
        // Validate UK postcode format
        if (!$this->is_valid_uk_postcode($clean_postcode)) {
            return new WP_Error('invalid_postcode', 'Please enter a valid UK postcode.');
        }
        
        $params = array_merge(array(
            'Location' => $clean_postcode,
            'skip' => 0,
            'take' => 20
        ), $filters);
        
        $initial_result = $this->make_request('', $params);
        
        // If we have a valid result with MP data, fetch contact information
        if (!is_wp_error($initial_result) && 
            isset($initial_result['items']) && 
            is_array($initial_result['items']) && 
            count($initial_result['items']) > 0) {
            
            $mp_id = $initial_result['items'][0]['value']['id'] ?? null;
            
            if ($mp_id) {
                // Fetch contact information
                $contact_result = $this->get_mp_contact($mp_id);
                
                if (!is_wp_error($contact_result)) {
                    // Merge contact data into the initial result
                    $initial_result['items'][0]['contact'] = $contact_result;
                }
            }
        }
        
        return $initial_result;
    }

    /**
     * Validate UK postcode format
     */
    private function is_valid_uk_postcode($postcode) {
        // UK postcode regex pattern
        $pattern = '/^[A-Z]{1,2}[0-9R][0-9A-Z]?[0-9][ABD-HJLNP-UW-Z]{2}$/';
        return preg_match($pattern, $postcode);
    }

    /**
     * Search data via API (legacy method)
     */
    public function search($query, $filters = array()) {
        return $this->search_by_postcode($query, $filters);
    }

    /**
     * Get data by ID
     */
    public function get_by_id($id) {
        return $this->make_request('item/' . $id);
    }

    /**
     * Get multiple items
     */
    public function get_items($params = array()) {
        return $this->make_request('items', $params);
    }
}