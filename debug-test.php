<?php
/**
 * Test Script - Clear Cache and Test Scraper
 *
 * Run this to test the scraper with fresh data
 * Usage: wp eval-file debug-test.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Clear cache
global $wpdb;
$deleted = $wpdb->query(
    "DELETE FROM {$wpdb->options}
    WHERE option_name LIKE '_transient_geekbench_scraper_%'
    OR option_name LIKE '_transient_timeout_geekbench_scraper_%'"
);

echo "Cache cleared! ($deleted transients deleted)\n\n";

// Fetch fresh results
$scraper = new \GeekbenchScraper\Scraper();

echo "Fetching results for 'iPhone18'...\n\n";

try {
    $results = $scraper->fetch('iPhone18', true);

    echo "✓ Found " . count($results) . " results\n\n";

    if (!empty($results)) {
        echo "First result:\n";
        echo "  System Name: " . $results[0]['system_name'] . "\n";
        echo "  Processor: " . $results[0]['processor_info'] . "\n";
        echo "  Platform: " . $results[0]['platform'] . "\n";
        echo "  Upload Date: " . $results[0]['upload_date'] . "\n";
        echo "  Single-Core: " . number_format($results[0]['single_core_score']) . "\n";
        echo "  Multi-Core: " . number_format($results[0]['multi_core_score']) . "\n";
        echo "  URL: " . $results[0]['benchmark_url'] . "\n";
    }

    echo "\n✓ Success! The scraper is working correctly.\n";

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

