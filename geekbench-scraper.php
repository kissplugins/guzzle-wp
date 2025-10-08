<?php
/**
 * Plugin Name: Guzzle WP - Example Plugin
 * Plugin URI: https://github.com/kissplugins/guzzle-wp
 * Description: Example/demo plugin showcasing Guzzle HTTP Client and Symfony DomCrawler in WordPress. Uses Geekbench scraping as a practical demonstration.
 * Version: 1.3.4
 * Author: Guzzle WP Contributors Inc. Guzzle, DomCrawler, and KISS Plugins
 * Author URI: https://github.com/kissplugins/guzzle-wp
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
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

// DEBUGGING: Test if this file is being loaded
$test_log = WP_CONTENT_DIR . '/geekbench-test.log';
file_put_contents($test_log, date('[Y-m-d H:i:s] ') . "Plugin file loaded!\n", FILE_APPEND);

// Define plugin constants
define('GEEKBENCH_SCRAPER_VERSION', '1.3.4');
define('GEEKBENCH_SCRAPER_PLUGIN_FILE', __FILE__);
define('GEEKBENCH_SCRAPER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GEEKBENCH_SCRAPER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GEEKBENCH_SCRAPER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require Composer autoloader
if (file_exists(GEEKBENCH_SCRAPER_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once GEEKBENCH_SCRAPER_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Show admin notice if Composer dependencies are missing
    add_action('admin_notices', __NAMESPACE__ . '\\show_missing_dependencies_notice');
    return;
}

// Show success/failure notices for auto-installation
add_action('admin_notices', __NAMESPACE__ . '\\show_composer_install_notices');

/**
 * Initialize the plugin
 *
 * @since 1.0.0
 * @return void
 */
function init_plugin() {
    // Log to custom file
    $log_file = WP_CONTENT_DIR . '/geekbench-init.log';
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Main] init_plugin() called\n", FILE_APPEND);

    // Get plugin instance
    $plugin = Plugin::get_instance();
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Main] Plugin instance retrieved\n", FILE_APPEND);

    // Initialize plugin
    $plugin->init();
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "[Main] Plugin initialized\n", FILE_APPEND);
}
add_action('plugins_loaded', __NAMESPACE__ . '\\init_plugin');

/**
 * Debug AJAX requests
 *
 * @since 1.3.4
 * @return void
 */
function debug_ajax_request() {
    if (defined('DOING_AJAX') && DOING_AJAX) {
        error_log('[DEBUG] AJAX request detected');
        error_log('[DEBUG] Action: ' . (isset($_POST['action']) ? $_POST['action'] : 'none'));
        error_log('[DEBUG] Request URI: ' . $_SERVER['REQUEST_URI']);

        // Check if our AJAX handlers are registered
        global $wp_filter;
        $action_name = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action_name === 'geekbench_scraper_fetch') {
            error_log('[DEBUG] Checking for registered handlers...');
            error_log('[DEBUG] wp_ajax_nopriv_geekbench_scraper_fetch: ' . (isset($wp_filter['wp_ajax_nopriv_geekbench_scraper_fetch']) ? 'YES' : 'NO'));
            error_log('[DEBUG] wp_ajax_geekbench_scraper_fetch: ' . (isset($wp_filter['wp_ajax_geekbench_scraper_fetch']) ? 'YES' : 'NO'));

            if (isset($wp_filter['wp_ajax_nopriv_geekbench_scraper_fetch'])) {
                error_log('[DEBUG] Handlers for wp_ajax_nopriv_geekbench_scraper_fetch: ' . print_r($wp_filter['wp_ajax_nopriv_geekbench_scraper_fetch'], true));
            }
        }
    }
}
add_action('init', __NAMESPACE__ . '\\debug_ajax_request', 1);

/**
 * Test AJAX handler
 *
 * @since 1.3.4
 * @return void
 */
function test_ajax_handler() {
    error_log('[TEST AJAX] test_ajax_simple CALLED!');
    wp_send_json_success(['message' => 'Test AJAX works!']);
}
add_action('wp_ajax_nopriv_test_ajax_simple', __NAMESPACE__ . '\\test_ajax_handler');
add_action('wp_ajax_test_ajax_simple', __NAMESPACE__ . '\\test_ajax_handler');

/**
 * Plugin activation hook
 *
 * @since 1.0.0
 * @return void
 */
function activate_plugin() {
    // Auto-install Composer dependencies if missing
    auto_install_composer_dependencies();

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
 * Auto-install Composer dependencies on plugin activation
 *
 * Attempts to automatically run composer install if dependencies are missing.
 * Supports both system composer and local composer.phar.
 *
 * @since 1.2.0
 * @return void
 */
function auto_install_composer_dependencies() {
    // Check if vendor directory already exists
    if (file_exists(GEEKBENCH_SCRAPER_PLUGIN_DIR . 'vendor/autoload.php')) {
        return; // Dependencies already installed
    }

    // Check if composer.json exists
    if (!file_exists(GEEKBENCH_SCRAPER_PLUGIN_DIR . 'composer.json')) {
        return; // No composer.json, can't install
    }

    // Try to install dependencies
    $plugin_dir = GEEKBENCH_SCRAPER_PLUGIN_DIR;
    $composer_phar = $plugin_dir . 'composer.phar';
    $success = false;
    $output = [];
    $return_var = 0;

    // Change to plugin directory
    $original_dir = getcwd();
    chdir($plugin_dir);

    try {
        // Method 1: Try local composer.phar first
        if (file_exists($composer_phar)) {
            // Try to find PHP binary
            $php_binary = get_php_binary();

            if ($php_binary) {
                $command = escapeshellcmd($php_binary) . ' ' . escapeshellarg($composer_phar) . ' install --no-dev --optimize-autoloader --no-interaction 2>&1';
                exec($command, $output, $return_var);

                if ($return_var === 0) {
                    $success = true;
                }
            }
        }

        // Method 2: Try system composer if local composer.phar failed
        if (!$success && command_exists('composer')) {
            $output = [];
            $command = 'composer install --no-dev --optimize-autoloader --no-interaction 2>&1';
            exec($command, $output, $return_var);

            if ($return_var === 0) {
                $success = true;
            }
        }

        // Store installation result for admin notice
        if ($success) {
            set_transient('geekbench_scraper_composer_install_success', true, 60);
        } else {
            set_transient('geekbench_scraper_composer_install_failed', [
                'output' => implode("\n", $output),
                'return_code' => $return_var,
            ], 300); // 5 minutes
        }

    } finally {
        // Always restore original directory
        chdir($original_dir);
    }
}

/**
 * Get PHP binary path
 *
 * Attempts to find the PHP binary in common locations.
 *
 * @since 1.2.0
 * @return string|false PHP binary path or false if not found
 */
function get_php_binary() {
    // Try PHP_BINARY constant first (available in PHP 5.4+)
    if (defined('PHP_BINARY') && PHP_BINARY && is_executable(PHP_BINARY)) {
        return PHP_BINARY;
    }

    // Try common PHP binary names
    $php_binaries = ['php', 'php8', 'php7', 'php-cli'];

    foreach ($php_binaries as $binary) {
        $path = trim(shell_exec('which ' . escapeshellarg($binary) . ' 2>/dev/null'));
        if ($path && is_executable($path)) {
            return $path;
        }
    }

    return false;
}

/**
 * Check if a command exists in the system
 *
 * @since 1.2.0
 * @param string $command Command name to check
 * @return bool True if command exists, false otherwise
 */
function command_exists($command) {
    $test = shell_exec('which ' . escapeshellarg($command) . ' 2>/dev/null');
    return !empty($test);
}

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
 * Show missing dependencies notice
 *
 * @since 1.2.0
 * @return void
 */
function show_missing_dependencies_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong>Geekbench Browser Scraper:</strong>
            Composer dependencies are missing.
        </p>
        <p>
            The plugin attempted to install dependencies automatically but was unable to do so.
            Please run <code>composer install --no-dev --optimize-autoloader</code>
            in the plugin directory: <code><?php echo esc_html(GEEKBENCH_SCRAPER_PLUGIN_DIR); ?></code>
        </p>
        <p>
            <strong>Alternative:</strong> If you have <code>composer.phar</code> in the plugin directory, run:<br>
            <code>php composer.phar install --no-dev --optimize-autoloader</code>
        </p>
    </div>
    <?php
}

/**
 * Show Composer installation notices
 *
 * Displays success or failure messages after auto-installation attempt.
 *
 * @since 1.2.0
 * @return void
 */
function show_composer_install_notices() {
    // Check for success notice
    if (get_transient('geekbench_scraper_composer_install_success')) {
        delete_transient('geekbench_scraper_composer_install_success');
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong>Geekbench Browser Scraper:</strong>
                ✅ Composer dependencies installed successfully!
            </p>
        </div>
        <?php
    }

    // Check for failure notice
    $failure_data = get_transient('geekbench_scraper_composer_install_failed');
    if ($failure_data) {
        delete_transient('geekbench_scraper_composer_install_failed');
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong>Geekbench Browser Scraper:</strong>
                ⚠️ Unable to automatically install Composer dependencies.
            </p>
            <p>
                Please run manually:<br>
                <code>cd <?php echo esc_html(GEEKBENCH_SCRAPER_PLUGIN_DIR); ?></code><br>
                <code>composer install --no-dev --optimize-autoloader</code>
            </p>
            <?php if (!empty($failure_data['output'])): ?>
                <details>
                    <summary>Show error details</summary>
                    <pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;"><?php echo esc_html($failure_data['output']); ?></pre>
                </details>
            <?php endif; ?>
        </div>
        <?php
    }
}

/**
 * Plugin uninstall hook
 * Note: This is defined in a separate uninstall.php file as per WordPress standards
 *
 * @since 1.0.0
 */
// See uninstall.php for cleanup logic

