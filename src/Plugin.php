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
        // Initialize scraper
        $this->scraper = new Scraper();
        
        // Initialize admin interface (only in admin)
        if (is_admin()) {
            $this->admin = new Admin($this->scraper);
        }
        
        // Initialize shortcode (frontend and admin)
        $this->shortcode = new Shortcode($this->scraper);
        
        // Load text domain for translations
        add_action('init', [$this, 'load_textdomain']);
        
        // Enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
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

