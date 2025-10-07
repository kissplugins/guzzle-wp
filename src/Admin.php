<?php
/**
 * Admin Interface Handler
 *
 * Manages the WordPress admin interface for the Geekbench Scraper plugin.
 * Handles admin menu, page rendering, and AJAX requests.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 * @version 1.0.0
 */

namespace GeekbenchScraper;

/**
 * Admin Class
 *
 * @since 1.0.0
 */
class Admin {
    
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
        
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Register AJAX handlers
        add_action('wp_ajax_geekbench_scraper_fetch', [$this, 'ajax_fetch_results']);
        add_action('wp_ajax_geekbench_scraper_refresh', [$this, 'ajax_refresh_results']);
        add_action('wp_ajax_geekbench_scraper_save_translations', [$this, 'ajax_save_translations']);
        add_action('wp_ajax_geekbench_self_test', [$this, 'ajax_self_test']);
    }
    
    /**
     * Add admin menu item
     *
     * @since 1.0.0
     * @return void
     */
    public function add_admin_menu() {
        // Main page
        add_submenu_page(
            'tools.php',
            __('Geekbench Scraper', 'geekbench-scraper'),
            __('Geekbench Scraper', 'geekbench-scraper'),
            'manage_options',
            'geekbench-scraper',
            [$this, 'render_admin_page']
        );

        // Settings page
        add_submenu_page(
            'tools.php',
            __('Geekbench Settings', 'geekbench-scraper'),
            __('Geekbench Settings', 'geekbench-scraper'),
            'manage_options',
            'geekbench-scraper-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Render admin page
     *
     * @since 1.0.0
     * @return void
     */
    public function render_admin_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'geekbench-scraper'));
        }
        
        // Get default query
        $default_query = get_option('geekbench_scraper_default_query', 'iPhone18');
        
        // Get current query from request or use default
        $current_query = isset($_GET['query']) ? sanitize_text_field($_GET['query']) : $default_query;
        
        // Fetch results
        $results = [];
        $error = null;
        
        if (!empty($current_query)) {
            try {
                $results = $this->scraper->fetch($current_query);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        // Include template
        include GEEKBENCH_SCRAPER_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * AJAX handler for fetching results
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_fetch_results() {
        // Verify nonce
        check_ajax_referer('geekbench_scraper_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions', 'geekbench-scraper'),
            ]);
        }
        
        // Get query parameter
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (empty($query)) {
            wp_send_json_error([
                'message' => __('Search query is required', 'geekbench-scraper'),
            ]);
        }
        
        try {
            // Fetch results
            $results = $this->scraper->fetch($query);
            
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
     * AJAX handler for refreshing results (bypass cache)
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_refresh_results() {
        // Verify nonce
        check_ajax_referer('geekbench_scraper_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions', 'geekbench-scraper'),
            ]);
        }
        
        // Get query parameter
        $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        
        if (empty($query)) {
            wp_send_json_error([
                'message' => __('Search query is required', 'geekbench-scraper'),
            ]);
        }
        
        try {
            // Clear cache first
            $this->scraper->clear_cache($query);
            
            // Fetch fresh results
            $results = $this->scraper->fetch($query, true);
            
            // Render table HTML
            ob_start();
            include GEEKBENCH_SCRAPER_PLUGIN_DIR . 'templates/results-table.php';
            $html = ob_get_clean();
            
            wp_send_json_success([
                'results' => $results,
                'html' => $html,
                'count' => count($results),
                'message' => __('Results refreshed successfully', 'geekbench-scraper'),
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Render settings page
     *
     * @since 1.1.0
     * @return void
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'geekbench-scraper'));
        }

        // Handle frontend settings form submission
        if (isset($_POST['save_frontend_settings']) && check_admin_referer('geekbench_frontend_settings_nonce')) {
            $search_hint = isset($_POST['search_hint']) ? sanitize_text_field(wp_unslash($_POST['search_hint'])) : 'Search for any device';
            update_option('geekbench_search_hint', $search_hint);
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Frontend settings saved successfully!', 'geekbench-scraper') . '</p></div>';
        }

        // Handle reCAPTCHA settings form submission
        if (isset($_POST['save_recaptcha_settings']) && check_admin_referer('geekbench_recaptcha_settings_nonce')) {
            $recaptcha_enabled = isset($_POST['recaptcha_enabled']) ? 1 : 0;
            $recaptcha_site_key = isset($_POST['recaptcha_site_key']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_site_key'])) : '';
            $recaptcha_secret_key = isset($_POST['recaptcha_secret_key']) ? sanitize_text_field(wp_unslash($_POST['recaptcha_secret_key'])) : '';

            update_option('geekbench_recaptcha_enabled', $recaptcha_enabled);
            update_option('geekbench_recaptcha_site_key', $recaptcha_site_key);
            update_option('geekbench_recaptcha_secret_key', $recaptcha_secret_key);

            echo '<div class="notice notice-success is-dismissible"><p>' . __('reCAPTCHA settings saved successfully!', 'geekbench-scraper') . '</p></div>';
        }

        // Handle translations form submission
        if (isset($_POST['geekbench_save_translations']) && check_admin_referer('geekbench_translations_nonce')) {
            $translations = [];

            if (isset($_POST['system_names']) && isset($_POST['display_names'])) {
                // Unslash arrays first, then sanitize
                $system_names = array_map('sanitize_text_field', array_map('wp_unslash', $_POST['system_names']));
                $display_names = array_map('sanitize_text_field', array_map('wp_unslash', $_POST['display_names']));

                foreach ($system_names as $index => $system_name) {
                    if (!empty($system_name) && !empty($display_names[$index])) {
                        $translations[$system_name] = $display_names[$index];
                    }
                }
            }

            update_option('geekbench_scraper_name_translations', $translations);
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Translations saved successfully!', 'geekbench-scraper') . '</p></div>';
        }

        // Get current translations
        $translations = get_option('geekbench_scraper_name_translations', []);

        // Include settings template
        include GEEKBENCH_SCRAPER_PLUGIN_DIR . 'templates/settings-page.php';
    }

    /**
     * AJAX handler for saving translations
     *
     * @since 1.1.0
     * @return void
     */
    public function ajax_save_translations() {
        // Verify nonce
        check_ajax_referer('geekbench_scraper_nonce', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'geekbench-scraper')]);
            return;
        }

        $translations = isset($_POST['translations']) ? $_POST['translations'] : [];
        $sanitized = [];

        foreach ($translations as $system_name => $display_name) {
            $system_name = sanitize_text_field($system_name);
            $display_name = sanitize_text_field($display_name);

            if (!empty($system_name) && !empty($display_name)) {
                $sanitized[$system_name] = $display_name;
            }
        }

        update_option('geekbench_scraper_name_translations', $sanitized);

        wp_send_json_success([
            'message' => __('Translations saved successfully', 'geekbench-scraper'),
            'count' => count($sanitized),
        ]);
    }

    /**
     * Handle self-test AJAX request
     *
     * @since 1.1.1
     * @return void
     */
    public function ajax_self_test() {
        // Verify nonce
        check_ajax_referer('geekbench_self_test', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions', 'geekbench-scraper'),
            ]);
        }

        $test = isset($_POST['test']) ? sanitize_text_field($_POST['test']) : '';

        switch ($test) {
            case 'php_version':
                $this->test_php_version();
                break;

            case 'guzzle_loaded':
                $this->test_guzzle_loaded();
                break;

            case 'domcrawler_loaded':
                $this->test_domcrawler_loaded();
                break;

            case 'wordpress_functions':
                $this->test_wordpress_functions();
                break;

            case 'cache_system':
                $this->test_cache_system();
                break;

            case 'geekbench_connectivity':
                $this->test_geekbench_connectivity();
                break;

            case 'scraper_logic':
                $this->test_scraper_logic();
                break;

            case 'html_parser':
                $this->test_html_parser();
                break;

            default:
                wp_send_json_error([
                    'message' => __('Unknown test', 'geekbench-scraper'),
                ]);
        }
    }

    /**
     * Test PHP version
     *
     * @since 1.1.1
     * @return void
     */
    private function test_php_version() {
        $version = phpversion();
        $required = '7.4';

        if (version_compare($version, $required, '>=')) {
            wp_send_json_success([
                'message' => 'PHP version is compatible',
                'details' => sprintf('Current: %s (Required: %s+)', $version, $required),
            ]);
        } else {
            wp_send_json_error([
                'message' => 'PHP version is too old',
                'details' => sprintf('Current: %s (Required: %s+)', $version, $required),
            ]);
        }
    }

    /**
     * Test if Guzzle is loaded
     *
     * @since 1.1.1
     * @return void
     */
    private function test_guzzle_loaded() {
        if (class_exists('GuzzleHttp\\Client')) {
            $reflection = new \ReflectionClass('GuzzleHttp\\Client');
            $version = \GuzzleHttp\Client::MAJOR_VERSION ?? 'Unknown';

            wp_send_json_success([
                'message' => 'Guzzle HTTP Client is loaded',
                'details' => sprintf('Version: %s', $version),
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Guzzle HTTP Client not found',
                'details' => 'Run <code>composer install</code> to install dependencies',
            ]);
        }
    }

    /**
     * Test if DomCrawler is loaded
     *
     * @since 1.1.1
     * @return void
     */
    private function test_domcrawler_loaded() {
        if (class_exists('Symfony\\Component\\DomCrawler\\Crawler')) {
            wp_send_json_success([
                'message' => 'Symfony DomCrawler is loaded',
                'details' => 'HTML parsing functionality available',
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Symfony DomCrawler not found',
                'details' => 'Run <code>composer install</code> to install dependencies',
            ]);
        }
    }

    /**
     * Test WordPress functions
     *
     * @since 1.1.1
     * @return void
     */
    private function test_wordpress_functions() {
        $required_functions = [
            'get_transient',
            'set_transient',
            'delete_transient',
            'wp_remote_get',
            'sanitize_text_field',
        ];

        $missing = [];
        foreach ($required_functions as $func) {
            if (!function_exists($func)) {
                $missing[] = $func;
            }
        }

        if (empty($missing)) {
            wp_send_json_success([
                'message' => 'All WordPress functions available',
                'details' => sprintf('Checked %d core functions', count($required_functions)),
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Missing WordPress functions',
                'details' => 'Missing: ' . implode(', ', $missing),
            ]);
        }
    }

    /**
     * Test cache system
     *
     * @since 1.1.1
     * @return void
     */
    private function test_cache_system() {
        $test_key = 'geekbench_test_' . time();
        $test_value = 'test_data_' . wp_generate_password(10, false);

        // Try to set transient
        $set_result = set_transient($test_key, $test_value, 60);

        if (!$set_result) {
            wp_send_json_error([
                'message' => 'Failed to set cache',
                'details' => 'Could not write to transient cache',
            ]);
            return;
        }

        // Try to get transient
        $get_result = get_transient($test_key);

        if ($get_result !== $test_value) {
            wp_send_json_error([
                'message' => 'Failed to read cache',
                'details' => 'Transient value mismatch',
            ]);
            return;
        }

        // Try to delete transient
        delete_transient($test_key);

        wp_send_json_success([
            'message' => 'Cache system working correctly',
            'details' => 'Set, get, and delete operations successful',
        ]);
    }

    /**
     * Test Geekbench connectivity
     *
     * @since 1.1.1
     * @return void
     */
    private function test_geekbench_connectivity() {
        try {
            $client = new \GuzzleHttp\Client([
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; GeekbenchScraper/1.0)',
                ],
            ]);

            $response = $client->get('https://browser.geekbench.com/search?q=test');
            $status_code = $response->getStatusCode();

            if ($status_code === 200) {
                wp_send_json_success([
                    'message' => 'Successfully connected to Geekbench',
                    'details' => sprintf('HTTP %d - Server is reachable', $status_code),
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Unexpected response from Geekbench',
                    'details' => sprintf('HTTP %d', $status_code),
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Cannot connect to Geekbench',
                'details' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Test scraper logic with real data
     *
     * @since 1.1.1
     * @return void
     */
    private function test_scraper_logic() {
        try {
            // Test with a known query that should return results
            $test_query = 'iPhone';

            // Attempt to fetch results
            $results = $this->scraper->fetch($test_query);

            // Validate results structure
            if (!is_array($results)) {
                wp_send_json_error([
                    'message' => 'Scraper returned invalid data type',
                    'details' => sprintf('Expected array, got %s', gettype($results)),
                ]);
                return;
            }

            // Check if we got any results
            if (empty($results)) {
                wp_send_json_error([
                    'message' => 'Scraper returned no results',
                    'details' => 'Query: "' . $test_query . '" - This may indicate parsing issues',
                ]);
                return;
            }

            // Validate first result has required fields
            $first_result = $results[0];
            $required_fields = ['system_name', 'single_core_score', 'multi_core_score', 'upload_date', 'benchmark_url'];
            $missing_fields = [];

            foreach ($required_fields as $field) {
                if (!isset($first_result[$field])) {
                    $missing_fields[] = $field;
                }
            }

            if (!empty($missing_fields)) {
                wp_send_json_error([
                    'message' => 'Scraper results missing required fields',
                    'details' => 'Missing: ' . implode(', ', $missing_fields),
                ]);
                return;
            }

            // Validate data quality
            $issues = [];

            if (empty($first_result['system_name'])) {
                $issues[] = 'system_name is empty';
            }

            if (!is_numeric($first_result['single_core_score']) || $first_result['single_core_score'] <= 0) {
                $issues[] = 'single_core_score invalid';
            }

            if (!is_numeric($first_result['multi_core_score']) || $first_result['multi_core_score'] <= 0) {
                $issues[] = 'multi_core_score invalid';
            }

            if (!empty($issues)) {
                wp_send_json_error([
                    'message' => 'Scraper data quality issues',
                    'details' => implode(', ', $issues),
                ]);
                return;
            }

            // All checks passed
            wp_send_json_success([
                'message' => 'Scraper logic working correctly',
                'details' => sprintf('Retrieved %d results with valid data structure', count($results)),
            ]);

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Scraper logic test failed',
                'details' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Test HTML parser with sample data
     *
     * @since 1.1.1
     * @return void
     */
    private function test_html_parser() {
        try {
            // Sample HTML that mimics Geekbench structure
            // ⚠️ CRITICAL: This HTML structure must match actual Geekbench format
            $sample_html = '
                <div class="row">
                    <div class="col-6 col-sm-3">
                        <a href="/v6/cpu/8888888">
                            <span class="list-col-text">iPhone17,1</span>
                        </a>
                    </div>
                    <div class="col-6 col-sm-3">
                        <span class="list-col-text">iOS 18.0</span>
                    </div>
                    <div class="col-6 col-sm-3">
                        <span class="list-col-text">
                            <span class="list-col-inner">
                                <span class="score">3500</span>
                            </span>
                        </span>
                    </div>
                    <div class="col-6 col-sm-3">
                        <span class="list-col-text">
                            <span class="list-col-inner">
                                <span class="score">8500</span>
                            </span>
                        </span>
                    </div>
                    <div class="col-12">
                        <span class="list-col-text">
                            Uploaded Oct 06, 2025
                        </span>
                    </div>
                </div>
            ';

            // Create DomCrawler instance
            $crawler = new \Symfony\Component\DomCrawler\Crawler($sample_html);

            // Test critical selectors
            $tests_passed = 0;
            $tests_failed = 0;
            $details = [];

            // Test 1: System name selector
            try {
                $system_name = $crawler->filter('.col-6.col-sm-3 a .list-col-text')->first()->text();
                if ($system_name === 'iPhone17,1') {
                    $tests_passed++;
                    $details[] = '✓ System name selector working';
                } else {
                    $tests_failed++;
                    $details[] = '✗ System name: expected "iPhone17,1", got "' . $system_name . '"';
                }
            } catch (\Exception $e) {
                $tests_failed++;
                $details[] = '✗ System name selector failed: ' . $e->getMessage();
            }

            // Test 2: Platform selector
            try {
                $platform = $crawler->filter('.col-6.col-sm-3 .list-col-text')->eq(1)->text();
                if ($platform === 'iOS 18.0') {
                    $tests_passed++;
                    $details[] = '✓ Platform selector working';
                } else {
                    $tests_failed++;
                    $details[] = '✗ Platform: expected "iOS 18.0", got "' . $platform . '"';
                }
            } catch (\Exception $e) {
                $tests_failed++;
                $details[] = '✗ Platform selector failed: ' . $e->getMessage();
            }

            // Test 3: Single-core score selector
            try {
                $single_core = $crawler->filter('.score')->eq(0)->text();
                if ($single_core === '3500') {
                    $tests_passed++;
                    $details[] = '✓ Single-core score selector working';
                } else {
                    $tests_failed++;
                    $details[] = '✗ Single-core: expected "3500", got "' . $single_core . '"';
                }
            } catch (\Exception $e) {
                $tests_failed++;
                $details[] = '✗ Single-core selector failed: ' . $e->getMessage();
            }

            // Test 4: Multi-core score selector
            try {
                $multi_core = $crawler->filter('.score')->eq(1)->text();
                if ($multi_core === '8500') {
                    $tests_passed++;
                    $details[] = '✓ Multi-core score selector working';
                } else {
                    $tests_failed++;
                    $details[] = '✗ Multi-core: expected "8500", got "' . $multi_core . '"';
                }
            } catch (\Exception $e) {
                $tests_failed++;
                $details[] = '✗ Multi-core selector failed: ' . $e->getMessage();
            }

            // Test 5: Upload date selector
            try {
                $upload_date = $crawler->filter('.col-12 .list-col-text')->text();
                if (strpos($upload_date, 'Oct 06, 2025') !== false) {
                    $tests_passed++;
                    $details[] = '✓ Upload date selector working';
                } else {
                    $tests_failed++;
                    $details[] = '✗ Upload date: expected "Oct 06, 2025", got "' . $upload_date . '"';
                }
            } catch (\Exception $e) {
                $tests_failed++;
                $details[] = '✗ Upload date selector failed: ' . $e->getMessage();
            }

            // Test 6: URL selector
            try {
                $url = $crawler->filter('.col-6.col-sm-3 a')->first()->attr('href');
                if ($url === '/v6/cpu/8888888') {
                    $tests_passed++;
                    $details[] = '✓ URL selector working';
                } else {
                    $tests_failed++;
                    $details[] = '✗ URL: expected "/v6/cpu/8888888", got "' . $url . '"';
                }
            } catch (\Exception $e) {
                $tests_failed++;
                $details[] = '✗ URL selector failed: ' . $e->getMessage();
            }

            // Determine overall result
            if ($tests_failed === 0) {
                wp_send_json_success([
                    'message' => 'All HTML selectors working correctly',
                    'details' => sprintf('%d/6 selectors passed - %s', $tests_passed, implode('; ', $details)),
                ]);
            } else {
                wp_send_json_error([
                    'message' => sprintf('%d/%d selectors failed', $tests_failed, ($tests_passed + $tests_failed)),
                    'details' => implode('; ', $details),
                ]);
            }

        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'HTML parser test failed',
                'details' => $e->getMessage(),
            ]);
        }
    }
}

