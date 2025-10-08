<?php
/**
 * Main Plugin Class
 *
 * Singleton pattern implementation for the Geekbench Scraper plugin.
 * Handles initialization and coordination of all plugin components.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 * @version 1.0.0
 */

namespace GeekbenchScraper;

/**
 * Main Plugin Class
 *
 * @since 1.0.0
 */
class Plugin {
    
    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static $instance = null;
    
    /**
     * Scraper instance
     *
     * @var Scraper|null
     */
    private $scraper = null;
    
    /**
     * Admin instance
     *
     * @var Admin|null
     */
    private $admin = null;
    
    /**
     * Shortcode instance
     *
     * @var Shortcode|null
     */
    private $shortcode = null;
    
    /**
     * Get plugin instance (Singleton)
     *
     * @since 1.0.0
     * @return Plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor to prevent direct instantiation
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Singleton - use get_instance()
    }
    
    /**
     * Initialize the plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Log to custom file
        $log_file = WP_CONTENT_DIR . '/geekbench-init.log';
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Plugin] init() called\n", FILE_APPEND);

        // Initialize scraper
        $this->scraper = new Scraper();
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Plugin] Scraper initialized\n", FILE_APPEND);

        // ============================================================================
        // CRITICAL: Admin vs Shortcode Initialization Order
        // ============================================================================
        // DO NOT REFACTOR THIS SECTION WITHOUT UNDERSTANDING THE FOLLOWING:
        //
        // 1. The Admin class registers admin-specific AJAX handlers:
        //    - wp_ajax_geekbench_scraper_refresh
        //    - wp_ajax_geekbench_scraper_save_translations
        //    - wp_ajax_geekbench_self_test
        //    - wp_ajax_geekbench_test_table_sorting
        //
        // 2. The Shortcode class registers frontend AJAX handlers:
        //    - wp_ajax_geekbench_scraper_fetch (logged-in users)
        //    - wp_ajax_nopriv_geekbench_scraper_fetch (non-logged-in users)
        //
        // 3. WordPress's is_admin() returns TRUE for ALL AJAX requests, even from frontend!
        //
        // 4. SOLUTION: Initialize Admin class for:
        //    - Regular admin pages (is_admin() && !wp_doing_ajax())
        //    - Admin AJAX requests (wp_doing_ajax() && current_user_can('manage_options'))
        //
        //    This ensures:
        //    - Admin AJAX handlers (self-test, etc.) work correctly
        //    - Frontend AJAX requests don't conflict with admin handlers
        //
        // 5. The Shortcode class MUST be initialized for both frontend AND admin to handle
        //    frontend AJAX requests (logged-in and non-logged-in users).
        //
        // TESTING: If you modify this, test ALL:
        //   - Admin interface search (requires login)
        //   - Frontend shortcode search (works without login)
        //   - Admin self-test (Settings page)
        // ============================================================================

        // Initialize admin interface
        // - For regular admin pages: is_admin() && !wp_doing_ajax()
        // - For admin AJAX requests: wp_doing_ajax() && current_user_can('manage_options')
        $is_admin_page = is_admin() && !wp_doing_ajax();
        $is_admin_ajax = wp_doing_ajax() && is_user_logged_in() && current_user_can('manage_options');

        if ($is_admin_page || $is_admin_ajax) {
            $this->admin = new Admin($this->scraper);
            file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Plugin] Admin initialized (admin_page: " . ($is_admin_page ? 'YES' : 'NO') . ", admin_ajax: " . ($is_admin_ajax ? 'YES' : 'NO') . ")\n", FILE_APPEND);
        }

        // Initialize shortcode (frontend and admin)
        // CRITICAL: This MUST be initialized even during AJAX to handle frontend searches
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Plugin] About to initialize Shortcode\n", FILE_APPEND);
        $this->shortcode = new Shortcode($this->scraper);
        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Plugin] Shortcode initialized\n", FILE_APPEND);

        // Load text domain for translations
        add_action('init', [$this, 'load_textdomain']);

        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Plugin] init() completed\n", FILE_APPEND);
    }
    
    /**
     * Load plugin text domain for translations
     *
     * @since 1.0.0
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'geekbench-scraper',
            false,
            dirname(GEEKBENCH_SCRAPER_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Enqueue frontend assets
     *
     * Only enqueue when shortcode is present on the page
     *
     * @since 1.0.0
     * @return void
     */
    public function enqueue_frontend_assets() {
        global $post;

        // Check if shortcode is present
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'geekbench_results')) {
            // Enqueue frontend CSS
            wp_enqueue_style(
                'geekbench-scraper-frontend',
                GEEKBENCH_SCRAPER_PLUGIN_URL . 'assets/css/frontend.css',
                [],
                GEEKBENCH_SCRAPER_VERSION
            );

            // Enqueue table sorting JavaScript
            wp_enqueue_script(
                'geekbench-scraper-table-sort',
                GEEKBENCH_SCRAPER_PLUGIN_URL . 'assets/js/table-sort.js',
                [],
                GEEKBENCH_SCRAPER_VERSION,
                true
            );

            // Enqueue reCAPTCHA if enabled
            if (get_option('geekbench_recaptcha_enabled', 0)) {
                $site_key = get_option('geekbench_recaptcha_site_key', '');
                if (!empty($site_key)) {
                    wp_enqueue_script(
                        'google-recaptcha',
                        'https://www.google.com/recaptcha/api.js',
                        [],
                        null,
                        true
                    );
                }
            }
        }
    }
    
    /**
     * Enqueue admin assets
     *
     * Only enqueue on plugin admin pages
     *
     * @since 1.0.0
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our admin page
        if ('tools_page_geekbench-scraper' !== $hook) {
            return;
        }
        
        // Enqueue admin CSS
        wp_enqueue_style(
            'geekbench-scraper-admin',
            GEEKBENCH_SCRAPER_PLUGIN_URL . 'assets/css/admin.css',
            [],
            GEEKBENCH_SCRAPER_VERSION
        );
        
        // Enqueue table sorting JavaScript
        wp_enqueue_script(
            'geekbench-scraper-table-sort',
            GEEKBENCH_SCRAPER_PLUGIN_URL . 'assets/js/table-sort.js',
            [],
            GEEKBENCH_SCRAPER_VERSION,
            true
        );
        
        // Enqueue admin JavaScript
        wp_enqueue_script(
            'geekbench-scraper-admin',
            GEEKBENCH_SCRAPER_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            GEEKBENCH_SCRAPER_VERSION,
            true
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script(
            'geekbench-scraper-admin',
            'geekbenchScraper',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('geekbench_scraper_nonce'),
                'defaultQuery' => get_option('geekbench_scraper_default_query', 'iPhone18'),
            ]
        );
    }
    
    /**
     * Get scraper instance
     *
     * @since 1.0.0
     * @return Scraper
     */
    public function get_scraper() {
        return $this->scraper;
    }
    
    /**
     * Get admin instance
     *
     * @since 1.0.0
     * @return Admin|null
     */
    public function get_admin() {
        return $this->admin;
    }
    
    /**
     * Get shortcode instance
     *
     * @since 1.0.0
     * @return Shortcode
     */
    public function get_shortcode() {
        return $this->shortcode;
    }
}

