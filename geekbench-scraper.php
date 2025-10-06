<?php
/**
 * Plugin Name: Geekbench Browser Scraper
 * Plugin URI: https://github.com/yourusername/geekbench-scraper
 * Description: Scrapes and displays Geekbench browser results with sortable tables. Default search: iPhone18 (iPhone 17 models).
 * Version: 1.1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: geekbench-scraper
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 */

namespace GeekbenchScraper;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GEEKBENCH_SCRAPER_VERSION', '1.1.0');
define('GEEKBENCH_SCRAPER_PLUGIN_FILE', __FILE__);
define('GEEKBENCH_SCRAPER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GEEKBENCH_SCRAPER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GEEKBENCH_SCRAPER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require Composer autoloader
if (file_exists(GEEKBENCH_SCRAPER_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once GEEKBENCH_SCRAPER_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Show admin notice if Composer dependencies are missing
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong>Geekbench Browser Scraper:</strong> 
                Composer dependencies are missing. Please run <code>composer install</code> 
                in the plugin directory: <code><?php echo esc_html(GEEKBENCH_SCRAPER_PLUGIN_DIR); ?></code>
            </p>
        </div>
        <?php
    });
    return;
}

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return void
 */
function init_plugin() {
    // Get plugin instance
    $plugin = Plugin::get_instance();
    
    // Initialize plugin
    $plugin->init();
}
add_action('plugins_loaded', __NAMESPACE__ . '\\init_plugin');

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @return void
 */
function activate_plugin() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Set default options
    if (!get_option('geekbench_scraper_default_query')) {
        update_option('geekbench_scraper_default_query', 'iPhone18');
    }
    
    if (!get_option('geekbench_scraper_cache_ttl')) {
        update_option('geekbench_scraper_cache_ttl', 900); // 15 minutes
    }
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate_plugin');

/**
 * Plugin deactivation hook
 *
 * @since 1.0.0
 * @return void
 */
function deactivate_plugin() {
    // Clear all cached results
    global $wpdb;
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_geekbench_scraper_%' 
        OR option_name LIKE '_transient_timeout_geekbench_scraper_%'"
    );
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\deactivate_plugin');

/**
 * Add settings link to plugins page
 *
 * @since 1.1.0
 * @param array $links Plugin action links
 * @return array Modified links
 */
function add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('tools.php?page=geekbench-scraper-settings') . '">' . __('Settings', 'geekbench-scraper') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), __NAMESPACE__ . '\\add_settings_link');

/**
 * Plugin uninstall hook
 * Note: This is defined in a separate uninstall.php file as per WordPress standards
 *
 * @since 1.0.0
 */
// See uninstall.php for cleanup logic

