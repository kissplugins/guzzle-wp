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
// Core settings (v1.0.0)
delete_option('geekbench_scraper_default_query');
delete_option('geekbench_scraper_cache_ttl');

// System name translations (v1.1.0)
delete_option('geekbench_scraper_name_translations');

// Frontend settings (v1.2.0)
delete_option('geekbench_search_hint');

// reCAPTCHA settings (v1.2.0)
delete_option('geekbench_recaptcha_enabled');
delete_option('geekbench_recaptcha_site_key');
delete_option('geekbench_recaptcha_secret_key');

// Delete all transients (cached results)
global $wpdb;
$wpdb->query(
    "DELETE FROM {$wpdb->options}
    WHERE option_name LIKE '_transient_geekbench_scraper_%'
    OR option_name LIKE '_transient_timeout_geekbench_scraper_%'
    OR option_name LIKE '_transient_geekbench_rate_limit_%'
    OR option_name LIKE '_transient_timeout_geekbench_rate_limit_%'"
);

// Clear any cached data
wp_cache_flush();

