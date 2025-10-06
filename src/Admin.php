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
    }
    
    /**
     * Add admin menu item
     *
     * @since 1.0.0
     * @return void
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            __('Geekbench Scraper', 'geekbench-scraper'),
            __('Geekbench Scraper', 'geekbench-scraper'),
            'manage_options',
            'geekbench-scraper',
            [$this, 'render_admin_page']
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
}

