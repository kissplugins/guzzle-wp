<?php
/**
 * Uninstall Script
 *
 * Fired when the plugin is uninstalled.
 * Cleans up all plugin data from the database.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 */

// Exit if accessed directly or not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
delete_option('geekbench_scraper_default_query');
delete_option('geekbench_scraper_cache_ttl');

// Delete all transients (cached results)
global $wpdb;
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_geekbench_scraper_%' 
    OR option_name LIKE '_transient_timeout_geekbench_scraper_%'"
);

// Clear any cached data
wp_cache_flush();

