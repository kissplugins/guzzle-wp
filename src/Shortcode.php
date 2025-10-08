<?php
/**
 * Shortcode Handler for Frontend Display
 *
 * Handles the [geekbench_results] shortcode for displaying
 * Geekbench benchmark results on the frontend.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 * @version 1.0.0
 */

namespace GeekbenchScraper;

/**
 * Shortcode Class
 *
 * @since 1.0.0
 */
class Shortcode {
    
    /**
     * Scraper instance
     *
     * @var Scraper
     */
    private $scraper;
    
    /**
     * Constructor
     *
     * @since 1.0.0
     * @param Scraper $scraper Scraper instance
     */
    public function __construct(Scraper $scraper) {
        $this->scraper = $scraper;
        
        // Register shortcode
        add_shortcode('geekbench_results', [$this, 'render']);
        
        // Register AJAX handlers for frontend
        add_action('wp_ajax_nopriv_geekbench_scraper_fetch', [$this, 'ajax_fetch_results']);
        add_action('wp_ajax_geekbench_scraper_fetch', [$this, 'ajax_fetch_results']);
    }
    
    /**
     * Render shortcode output
     *
     * @since 1.0.0
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render($atts) {
        // Parse attributes with defaults
        $atts = shortcode_atts([
            'default' => 'Apple M4',
            'query' => '', // Deprecated, use 'default' instead
            'limit' => 25,
            'show_search' => true,
            'show_refresh' => false,
            'table_class' => 'geekbench-table',
            'columns' => 'all',
        ], $atts, 'geekbench_results');

        // Sanitize attributes
        // Use 'default' if provided, otherwise fall back to 'query' for backwards compatibility
        $query = !empty($atts['default']) ? sanitize_text_field($atts['default']) : sanitize_text_field($atts['query']);

        // If still empty, use global default
        if (empty($query)) {
            $query = get_option('geekbench_scraper_default_query', 'Apple M4');
        }

        $limit = absint($atts['limit']);
        $show_search = filter_var($atts['show_search'], FILTER_VALIDATE_BOOLEAN);
        $show_refresh = filter_var($atts['show_refresh'], FILTER_VALIDATE_BOOLEAN);
        $table_class = sanitize_html_class($atts['table_class']);
        $columns = sanitize_text_field($atts['columns']);

        // Limit max results
        if ($limit > 30) {
            $limit = 30;
        }

        // Fetch results for initial page load
        $results = [];
        $error = null;

        try {
            $results = $this->scraper->fetch($query);

            // Limit results
            if ($limit > 0 && count($results) > $limit) {
                $results = array_slice($results, 0, $limit);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        // Start output buffering
        ob_start();

        // Include frontend template
        include GEEKBENCH_SCRAPER_PLUGIN_DIR . 'templates/frontend-shortcode.php';

        return ob_get_clean();
    }
    
    /**
     * AJAX handler for fetching results (frontend)
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_fetch_results() {
        // Get query parameter
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 25;

        if (empty($query)) {
            wp_send_json_error([
                'message' => __('Search query is required', 'geekbench-scraper'),
            ]);
        }

        // Server-side throttling check
        $throttle_check = $this->check_throttle_limit();

        if ($throttle_check['requires_captcha']) {
            // reCAPTCHA is required after 5 searches
            if (!get_option('geekbench_recaptcha_enabled', 0)) {
                // reCAPTCHA not enabled but limit reached
                wp_send_json_error([
                    'message' => __('Search limit reached. Please enable reCAPTCHA in settings to continue.', 'geekbench-scraper'),
                ]);
            }

            $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

            if (empty($recaptcha_response)) {
                wp_send_json_error([
                    'message' => __('Please complete the reCAPTCHA verification to continue searching.', 'geekbench-scraper'),
                    'requires_captcha' => true,
                ]);
            }

            // Verify reCAPTCHA
            if (!$this->verify_recaptcha($recaptcha_response)) {
                wp_send_json_error([
                    'message' => __('reCAPTCHA verification failed. Please try again.', 'geekbench-scraper'),
                    'requires_captcha' => true,
                ]);
            }

            // reCAPTCHA verified - reset counter for this IP
            $this->reset_throttle_count();
        } else {
            // Increment search count for this IP
            $this->increment_throttle_count();
        }

        // Limit max results
        if ($limit > 30) {
            $limit = 30;
        }

        try {
            // Fetch results
            $results = $this->scraper->fetch($query);

            // Limit results
            if ($limit > 0 && count($results) > $limit) {
                $results = array_slice($results, 0, $limit);
            }

            // Render table HTML
            ob_start();
            include GEEKBENCH_SCRAPER_PLUGIN_DIR . 'templates/results-table.php';
            $html = ob_get_clean();

            wp_send_json_success([
                'results' => $results,
                'html' => $html,
                'count' => count($results),
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify reCAPTCHA response
     *
     * @since 1.1.1
     * @param string $response reCAPTCHA response token
     * @return bool True if verification successful
     */
    private function verify_recaptcha($response) {
        $secret_key = get_option('geekbench_recaptcha_secret_key', '');

        if (empty($secret_key)) {
            return false;
        }

        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';

        $verify_response = wp_remote_post($verify_url, [
            'body' => [
                'secret' => $secret_key,
                'response' => $response,
                'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            ],
        ]);

        if (is_wp_error($verify_response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($verify_response);
        $result = json_decode($body, true);

        return isset($result['success']) && $result['success'] === true;
    }

    /**
     * Get user's IP address
     *
     * @since 1.3.0
     * @return string IP address
     */
    private function get_user_ip() {
        // Check for proxy headers first
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy
            'HTTP_X_REAL_IP',        // Nginx proxy
            'REMOTE_ADDR',           // Direct connection
        ];

        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];

                // Handle comma-separated IPs (X-Forwarded-For can have multiple)
                if (strpos($ip, ',') !== false) {
                    $ip_list = explode(',', $ip);
                    $ip = trim($ip_list[0]);
                }

                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0'; // Fallback
    }

    /**
     * Get transient key for IP-based throttling
     *
     * @since 1.3.0
     * @return string Transient key
     */
    private function get_throttle_transient_key() {
        $ip = $this->get_user_ip();
        return 'geekbench_throttle_' . md5($ip);
    }

    /**
     * Check if user has reached throttle limit
     *
     * @since 1.3.0
     * @return array Array with 'count' and 'requires_captcha' keys
     */
    private function check_throttle_limit() {
        $transient_key = $this->get_throttle_transient_key();
        $search_count = get_transient($transient_key);

        // Default to 0 if no transient exists
        if ($search_count === false) {
            $search_count = 0;
        }

        // Maximum searches before requiring CAPTCHA
        $max_searches = 5;

        return [
            'count' => intval($search_count),
            'requires_captcha' => intval($search_count) >= $max_searches,
        ];
    }

    /**
     * Increment throttle count for current IP
     *
     * @since 1.3.0
     * @return void
     */
    private function increment_throttle_count() {
        $transient_key = $this->get_throttle_transient_key();
        $search_count = get_transient($transient_key);

        if ($search_count === false) {
            $search_count = 0;
        }

        $search_count++;

        // Store for 5 minutes (300 seconds)
        set_transient($transient_key, $search_count, 5 * MINUTE_IN_SECONDS);
    }

    /**
     * Reset throttle count for current IP
     *
     * @since 1.3.0
     * @return void
     */
    private function reset_throttle_count() {
        $transient_key = $this->get_throttle_transient_key();
        delete_transient($transient_key);
    }
}

