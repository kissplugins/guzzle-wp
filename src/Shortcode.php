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
            'query' => get_option('geekbench_scraper_default_query', 'iPhone18'),
            'limit' => 25,
            'show_search' => false,
            'show_refresh' => false,
            'table_class' => 'geekbench-table',
            'columns' => 'all',
        ], $atts, 'geekbench_results');
        
        // Sanitize attributes
        $query = sanitize_text_field($atts['query']);
        $limit = absint($atts['limit']);
        $show_search = filter_var($atts['show_search'], FILTER_VALIDATE_BOOLEAN);
        $show_refresh = filter_var($atts['show_refresh'], FILTER_VALIDATE_BOOLEAN);
        $table_class = sanitize_html_class($atts['table_class']);
        $columns = sanitize_text_field($atts['columns']);
        
        // Limit max results
        if ($limit > 30) {
            $limit = 30;
        }
        
        // Fetch results
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
}

