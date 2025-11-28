<?php
/**
 * Cache handler for UK MP Email Search
 *
 * @package UKMP_Email
 */

if (!defined('ABSPATH')) {
    exit;
}

class UKMP_Email_Cache {

    /**
     * Cache table name
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ukmp_email_cache';
    }

    /**
     * Get cached data
     */
    public function get($key) {
        global $wpdb;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT cache_data FROM {$this->table_name} WHERE cache_key = %s AND expiry > NOW()",
                $key
            )
        );

        if ($result && !empty($result->cache_data)) {
            return maybe_unserialize($result->cache_data);
        }

        return false;
    }

    /**
     * Set cached data
     */
    public function set($key, $data, $expiry = 3600) {
        global $wpdb;

        $expiry_time = date('Y-m-d H:i:s', time() + $expiry);
        $serialized_data = maybe_serialize($data);

        return $wpdb->replace(
            $this->table_name,
            array(
                'cache_key' => $key,
                'cache_data' => $serialized_data,
                'expiry' => $expiry_time,
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s')
        );
    }

    /**
     * Delete cached data
     */
    public function delete($key) {
        global $wpdb;

        return $wpdb->delete(
            $this->table_name,
            array('cache_key' => $key),
            array('%s')
        );
    }

    /**
     * Clear expired cache entries
     */
    public function clear_expired() {
        global $wpdb;

        return $wpdb->query(
            "DELETE FROM {$this->table_name} WHERE expiry < NOW()"
        );
    }

    /**
     * Clear all cache
     */
    public function clear_all() {
        global $wpdb;

        return $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }

    /**
     * Get cache statistics
     */
    public function get_stats() {
        global $wpdb;

        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $expired = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE expiry < NOW()");
        $active = $total - $expired;

        return array(
            'total' => (int) $total,
            'active' => (int) $active,
            'expired' => (int) $expired,
        );
    }
}