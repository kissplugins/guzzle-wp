<?php
/**
 * Settings Page Template
 *
 * Template for the Geekbench Scraper settings page.
 *
 * @package GeekbenchScraper
 * @since 1.1.0
 *
 * Available variables:
 * @var array $translations Array of system name translations
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap geekbench-scraper-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Self-Test Section -->
    <div class="geekbench-self-test-section">
        <h2><?php esc_html_e('System Self-Test', 'geekbench-scraper'); ?></h2>
        <p class="description">
            <?php esc_html_e('Run diagnostic tests to ensure all plugin components are functioning correctly.', 'geekbench-scraper'); ?>
        </p>

        <div id="self-test-status" class="self-test-status">
            <p class="test-summary">
                <span id="test-count-display">
                    <span class="dashicons dashicons-update spin"></span>
                    <?php esc_html_e('Click "Run Tests" to begin...', 'geekbench-scraper'); ?>
                </span>
            </p>
        </div>

        <p>
            <button type="button" class="button button-primary" id="run-self-tests">
                <span class="dashicons dashicons-yes-alt" style="vertical-align: middle;"></span>
                <?php esc_html_e('Run Tests', 'geekbench-scraper'); ?>
            </button>
            <span class="spinner" id="test-spinner" style="float: none; margin: 0 10px;"></span>
        </p>

        <div id="test-results" class="test-results" style="display: none;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 5%;"><?php esc_html_e('Status', 'geekbench-scraper'); ?></th>
                        <th style="width: 30%;"><?php esc_html_e('Test Name', 'geekbench-scraper'); ?></th>
                        <th style="width: 65%;"><?php esc_html_e('Result', 'geekbench-scraper'); ?></th>
                    </tr>
                </thead>
                <tbody id="test-results-tbody">
                    <!-- Test results will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <hr style="margin: 40px 0;">

    <!-- Frontend Settings Section -->
    <div class="geekbench-settings-section">
        <h2><?php esc_html_e('Frontend Settings', 'geekbench-scraper'); ?></h2>
        <p class="description">
            <?php esc_html_e('Configure frontend shortcode appearance and behavior.', 'geekbench-scraper'); ?>
        </p>

        <form method="post" action="">
            <?php wp_nonce_field('geekbench_frontend_settings_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="search_hint"><?php esc_html_e('Search Hint Text', 'geekbench-scraper'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               id="search_hint"
                               name="search_hint"
                               value="<?php echo esc_attr(get_option('geekbench_search_hint', 'Search for any device')); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Hint text displayed below the search input field (10pt gray font).', 'geekbench-scraper'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="save_frontend_settings" class="button button-primary">
                    <?php esc_html_e('Save Frontend Settings', 'geekbench-scraper'); ?>
                </button>
            </p>
        </form>
    </div>

    <hr style="margin: 40px 0;">

    <!-- reCAPTCHA Settings Section -->
    <div class="geekbench-settings-section">
        <h2><?php esc_html_e('Google reCAPTCHA v2 Settings', 'geekbench-scraper'); ?></h2>
        <p class="description">
            <?php esc_html_e('Configure reCAPTCHA to protect your frontend searches from bots and abuse. Uses smart throttling to avoid annoying legitimate users.', 'geekbench-scraper'); ?>
        </p>

        <form method="post" action="">
            <?php wp_nonce_field('geekbench_recaptcha_settings_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="recaptcha_enabled"><?php esc_html_e('Enable reCAPTCHA', 'geekbench-scraper'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   id="recaptcha_enabled"
                                   name="recaptcha_enabled"
                                   value="1"
                                   <?php checked(get_option('geekbench_recaptcha_enabled', 0), 1); ?>>
                            <?php esc_html_e('Require reCAPTCHA for frontend searches (with smart throttling)', 'geekbench-scraper'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Smart throttling: First 5 searches in 5 minutes don\'t require CAPTCHA. After that, CAPTCHA is required.', 'geekbench-scraper'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="recaptcha_site_key"><?php esc_html_e('Site Key', 'geekbench-scraper'); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               id="recaptcha_site_key"
                               name="recaptcha_site_key"
                               value="<?php echo esc_attr(get_option('geekbench_recaptcha_site_key', '')); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php
                            printf(
                                esc_html__('Get your keys from %s', 'geekbench-scraper'),
                                '<a href="https://www.google.com/recaptcha/admin" target="_blank">' . esc_html__('Google reCAPTCHA Admin', 'geekbench-scraper') . '</a>'
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="recaptcha_secret_key"><?php esc_html_e('Secret Key', 'geekbench-scraper'); ?></label>
                    </th>
                    <td>
                        <input type="password"
                               id="recaptcha_secret_key"
                               name="recaptcha_secret_key"
                               value="<?php echo esc_attr(get_option('geekbench_recaptcha_secret_key', '')); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Your reCAPTCHA secret key (kept private on the server).', 'geekbench-scraper'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="save_recaptcha_settings" class="button button-primary">
                    <?php esc_html_e('Save reCAPTCHA Settings', 'geekbench-scraper'); ?>
                </button>
            </p>
        </form>
    </div>

    <hr style="margin: 40px 0;">

    <div class="geekbench-settings-header">
        <h2><?php esc_html_e('System Name Translations', 'geekbench-scraper'); ?></h2>
        <p class="description">
            <?php esc_html_e('Configure system name translations to display user-friendly product names instead of internal model identifiers.', 'geekbench-scraper'); ?>
        </p>
    </div>

    <form method="post" action="">
        <?php wp_nonce_field('geekbench_translations_nonce'); ?>

        <h2><?php esc_html_e('System Name Translations', 'geekbench-scraper'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped" id="translations-table">
            <thead>
                <tr>
                    <th style="width: 35%;"><?php esc_html_e('System Name (Internal)', 'geekbench-scraper'); ?></th>
                    <th style="width: 35%;"><?php esc_html_e('Display Name (User-Friendly)', 'geekbench-scraper'); ?></th>
                    <th style="width: 30%;"><?php esc_html_e('Example', 'geekbench-scraper'); ?></th>
                    <th style="width: 10%;"><?php esc_html_e('Actions', 'geekbench-scraper'); ?></th>
                </tr>
            </thead>
            <tbody id="translations-tbody">
                <?php if (!empty($translations)): ?>
                    <?php foreach ($translations as $system_name => $display_name): ?>
                        <tr class="translation-row">
                            <td>
                                <input type="text" name="system_names[]" value="<?php echo esc_attr($system_name); ?>" class="regular-text" placeholder="e.g., iPhone18,2">
                            </td>
                            <td>
                                <input type="text" name="display_names[]" value="<?php echo esc_attr($display_name); ?>" class="regular-text" placeholder="e.g., iPhone 17 Plus">
                            </td>
                            <td>
                                <code><?php echo esc_html($system_name); ?></code> → <strong><?php echo esc_html($display_name); ?></strong>
                            </td>
                            <td>
                                <button type="button" class="button remove-translation"><?php esc_html_e('Remove', 'geekbench-scraper'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="translation-row">
                        <td>
                            <input type="text" name="system_names[]" value="" class="regular-text" placeholder="e.g., iPhone18,2">
                        </td>
                        <td>
                            <input type="text" name="display_names[]" value="" class="regular-text" placeholder="e.g., iPhone 17 Plus">
                        </td>
                        <td>
                            <em><?php esc_html_e('Preview will appear here', 'geekbench-scraper'); ?></em>
                        </td>
                        <td>
                            <button type="button" class="button remove-translation"><?php esc_html_e('Remove', 'geekbench-scraper'); ?></button>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <p>
            <button type="button" class="button" id="add-translation">
                <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                <?php esc_html_e('Add Translation', 'geekbench-scraper'); ?>
            </button>
        </p>
        
        <h3><?php esc_html_e('Common iPhone Translations', 'geekbench-scraper'); ?></h3>
        <p class="description"><?php esc_html_e('Click to add common iPhone model translations:', 'geekbench-scraper'); ?></p>
        <p>
            <button type="button" class="button button-secondary quick-add" data-system="iPhone18,1" data-display="iPhone 17">iPhone 17</button>
            <button type="button" class="button button-secondary quick-add" data-system="iPhone18,2" data-display="iPhone 17 Plus">iPhone 17 Plus</button>
            <button type="button" class="button button-secondary quick-add" data-system="iPhone18,3" data-display="iPhone 17 Pro">iPhone 17 Pro</button>
            <button type="button" class="button button-secondary quick-add" data-system="iPhone18,4" data-display="iPhone 17 Pro Max">iPhone 17 Pro Max</button>
        </p>
        
        <p class="submit">
            <input type="submit" name="geekbench_save_translations" class="button button-primary" value="<?php esc_attr_e('Save Translations', 'geekbench-scraper'); ?>">
        </p>
    </form>
</div>

<style>
.geekbench-scraper-settings {
    max-width: 1200px;
}

.geekbench-settings-header {
    margin-bottom: 20px;
}

#translations-table {
    margin-top: 20px;
}

#translations-table input[type="text"] {
    width: 100%;
}

.translation-row td {
    vertical-align: middle;
}

.quick-add {
    margin-right: 5px;
    margin-bottom: 5px;
}

/* Self-Test Styles */
.geekbench-self-test-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.self-test-status {
    background: #f0f0f1;
    border-left: 4px solid #72aee6;
    padding: 15px;
    margin: 15px 0;
}

.self-test-status.all-passed {
    background: #d4edda;
    border-left-color: #28a745;
}

.self-test-status.some-failed {
    background: #f8d7da;
    border-left-color: #dc3545;
}

.test-summary {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.test-summary.passed {
    color: #28a745;
}

.test-summary.failed {
    color: #dc3545;
}

.test-results {
    margin-top: 20px;
}

.test-results table {
    background: #fff;
}

.test-status-icon {
    font-size: 20px;
    line-height: 1;
}

.test-status-icon.passed {
    color: #28a745;
}

.test-status-icon.failed {
    color: #dc3545;
}

.test-status-icon.running {
    color: #72aee6;
}

.test-result-message {
    font-size: 13px;
}

.test-result-message code {
    background: #f0f0f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
}

.test-result-details {
    color: #666;
    font-size: 12px;
    margin-top: 5px;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.spin {
    animation: spin 1s linear infinite;
}

#test-spinner.is-active {
    visibility: visible;
}
</style>

<script>
jQuery(document).ready(function($) {

    // ========================================
    // SELF-TEST FUNCTIONALITY
    // ========================================

    $('#run-self-tests').on('click', function() {
        runSelfTests();
    });

    function runSelfTests() {
        const $button = $('#run-self-tests');
        const $spinner = $('#test-spinner');
        const $status = $('#self-test-status');
        const $results = $('#test-results');
        const $tbody = $('#test-results-tbody');
        const $countDisplay = $('#test-count-display');

        // Disable button and show spinner
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        $results.show();
        $tbody.empty();

        // Update status
        $status.removeClass('all-passed some-failed');
        $countDisplay.html('<span class="dashicons dashicons-update spin"></span> Running tests...');

        // Define all tests
        const tests = [
            {
                name: 'PHP Version Check',
                description: 'Verify PHP version is 7.4 or higher',
                test: testPHPVersion
            },
            {
                name: 'Guzzle HTTP Client',
                description: 'Check if Guzzle HTTP client is loaded',
                test: testGuzzleLoaded
            },
            {
                name: 'DomCrawler Library',
                description: 'Check if Symfony DomCrawler is loaded',
                test: testDomCrawlerLoaded
            },
            {
                name: 'WordPress Functions',
                description: 'Verify WordPress core functions are available',
                test: testWordPressFunctions
            },
            {
                name: 'Cache System',
                description: 'Test WordPress transient cache functionality',
                test: testCacheSystem
            },
            {
                name: 'Geekbench Connectivity',
                description: 'Test connection to Geekbench Browser',
                test: testGeekbenchConnectivity
            },
            {
                name: 'Scraper Logic Test',
                description: 'Test core scraping functionality with real data',
                test: testScraperLogic
            },
            {
                name: 'HTML Parser Test',
                description: 'Test DOM selectors extract data correctly',
                test: testHTMLParser
            },
            {
                name: 'Frontend AJAX Test',
                description: 'Test frontend shortcode AJAX handler registration',
                test: testFrontendAJAX
            },
            {
                name: 'Table Sorting Test',
                description: 'Test table sorting functionality (ascending/descending)',
                test: testTableSorting
            }
        ];

        let passedCount = 0;
        let failedCount = 0;
        let completedCount = 0;

        // Run tests sequentially
        runNextTest(0);

        function runNextTest(index) {
            if (index >= tests.length) {
                // All tests complete
                finishTests();
                return;
            }

            const test = tests[index];

            // Add test row
            const $row = $('<tr></tr>');
            $row.html(`
                <td class="test-status-icon running">
                    <span class="dashicons dashicons-update spin"></span>
                </td>
                <td>
                    <strong>${test.name}</strong><br>
                    <small class="test-result-details">${test.description}</small>
                </td>
                <td class="test-result-message">
                    <em>Running...</em>
                </td>
            `);
            $tbody.append($row);

            // Run the test
            test.test(function(passed, message, details) {
                completedCount++;

                if (passed) {
                    passedCount++;
                    $row.find('.test-status-icon').removeClass('running').addClass('passed')
                        .html('<span class="dashicons dashicons-yes-alt"></span>');
                    $row.find('.test-result-message').html(
                        `<span style="color: #28a745;">✓ ${message}</span>` +
                        (details ? `<div class="test-result-details">${details}</div>` : '')
                    );
                } else {
                    failedCount++;
                    $row.find('.test-status-icon').removeClass('running').addClass('failed')
                        .html('<span class="dashicons dashicons-dismiss"></span>');
                    $row.find('.test-result-message').html(
                        `<span style="color: #dc3545;">✗ ${message}</span>` +
                        (details ? `<div class="test-result-details">${details}</div>` : '')
                    );
                }

                // Update count display
                updateCountDisplay();

                // Run next test
                setTimeout(function() {
                    runNextTest(index + 1);
                }, 300);
            });
        }

        function updateCountDisplay() {
            const total = tests.length;
            const statusClass = failedCount > 0 ? 'failed' : (passedCount === total ? 'passed' : '');

            $countDisplay.html(
                `<strong class="${statusClass}">${passedCount} of ${total} tests passed</strong>`
            );

            if (failedCount > 0) {
                $status.removeClass('all-passed').addClass('some-failed');
            } else if (passedCount === total) {
                $status.removeClass('some-failed').addClass('all-passed');
            }
        }

        function finishTests() {
            $button.prop('disabled', false);
            $spinner.removeClass('is-active');
            updateCountDisplay();
        }
    }

    // ========================================
    // TEST FUNCTIONS
    // ========================================

    function testPHPVersion(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'php_version',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    callback(true, response.data.message, response.data.details);
                } else {
                    callback(false, response.data.message, response.data.details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    function testGuzzleLoaded(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'guzzle_loaded',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    callback(true, response.data.message, response.data.details);
                } else {
                    callback(false, response.data.message, response.data.details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    function testDomCrawlerLoaded(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'domcrawler_loaded',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    callback(true, response.data.message, response.data.details);
                } else {
                    callback(false, response.data.message, response.data.details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    function testWordPressFunctions(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'wordpress_functions',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    callback(true, response.data.message, response.data.details);
                } else {
                    callback(false, response.data.message, response.data.details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    function testCacheSystem(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'cache_system',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    callback(true, response.data.message, response.data.details);
                } else {
                    callback(false, response.data.message, response.data.details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    function testGeekbenchConnectivity(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'geekbench_connectivity',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    callback(true, response.data.message, response.data.details);
                } else {
                    callback(false, response.data.message, response.data.details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    function testScraperLogic(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'scraper_logic',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    callback(true, response.data.message, response.data.details);
                } else {
                    callback(false, response.data.message, response.data.details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    function testHTMLParser(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'html_parser',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    callback(true, response.data.message, response.data.details);
                } else {
                    callback(false, response.data.message, response.data.details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    /**
     * Test Frontend AJAX Handler
     *
     * ⚠️ CRITICAL TEST - DO NOT REMOVE ⚠️
     *
     * This test verifies that the frontend shortcode AJAX handler is properly registered.
     * If this test fails, frontend search will not work (returns -1 or 403 error).
     *
     * This test checks:
     * - Shortcode class exists
     * - wp_ajax_nopriv_geekbench_scraper_fetch is registered (for non-logged-in users)
     * - wp_ajax_geekbench_scraper_fetch is registered (for logged-in users)
     * - No conflicts with Admin class handler
     *
     * @since 1.3.4
     */
    function testFrontendAJAX(callback) {
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_self_test',
                test: 'frontend_ajax',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Build details from test results
                    let details = '<strong>Frontend AJAX validation:</strong><ul>';
                    if (response.data.details) {
                        response.data.details.forEach(function(detail) {
                            if (detail.includes('✅')) {
                                details += `<li style="color: #28a745;">${detail}</li>`;
                            } else if (detail.includes('⚠️')) {
                                details += `<li style="color: #ffc107;">${detail}</li>`;
                            } else {
                                details += `<li>${detail}</li>`;
                            }
                        });
                    }
                    details += '</ul>';
                    details += '<p><em>Note: This ensures frontend shortcode search works for both logged-in and non-logged-in users.</em></p>';

                    callback(true, response.data.message, details);
                } else {
                    let details = '<strong>Frontend AJAX validation failed:</strong><ul>';
                    if (response.data.details) {
                        response.data.details.forEach(function(detail) {
                            if (detail.includes('❌')) {
                                details += `<li style="color: #dc3545; font-weight: bold;">${detail}</li>`;
                            } else if (detail.includes('⚠️')) {
                                details += `<li style="color: #ffc107;">${detail}</li>`;
                            } else {
                                details += `<li>${detail}</li>`;
                            }
                        });
                    }
                    details += '</ul>';
                    details += '<p style="color: #dc3545; font-weight: bold;">⚠️ ACTION REQUIRED: Frontend search will NOT work! Check src/Plugin.php and src/Shortcode.php</p>';

                    callback(false, response.data.message, details);
                }
            },
            error: function() {
                callback(false, 'AJAX request failed', 'Could not communicate with server');
            }
        });
    }

    /**
     * Test Table Sorting Functionality
     *
     * ⚠️ CRITICAL TEST - DO NOT REMOVE ⚠️
     *
     * This test verifies that the table sorting JavaScript is present and functional.
     * If this test fails, table sorting is broken on both admin and frontend.
     *
     * Note: This test runs on the settings page where results-table.php is not loaded.
     * We test by checking if the initTableSort function would be available on pages with tables.
     *
     * @since 1.3.1
     */
    function testTableSorting(callback) {
        // Since we're on the settings page, we need to check if the sorting code exists in the file
        // We'll do this by making an AJAX request to verify the code is present

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'geekbench_test_table_sorting',
                nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Build details from test results
                    let details = '<strong>Table sorting validation:</strong><ul>';
                    if (response.data.details) {
                        response.data.details.forEach(function(detail) {
                            details += `<li>✓ ${detail}</li>`;
                        });
                    }
                    details += '</ul>';
                    details += '<p><em>Note: Full sorting functionality can only be tested on pages with results tables.</em></p>';

                    callback(true, response.data.message, details);
                } else {
                    let details = '<strong>Table sorting validation failed:</strong><ul>';
                    if (response.data.details) {
                        response.data.details.forEach(function(detail) {
                            details += `<li style="color: #dc3545;">✗ ${detail}</li>`;
                        });
                    }
                    details += '</ul>';
                    details += '<p style="color: #dc3545; font-weight: bold;">⚠️ ACTION REQUIRED: Check templates/results-table.php for missing JavaScript!</p>';

                    callback(false, response.data.message, details);
                }
            },
            error: function() {
                callback(false,
                    'AJAX request failed',
                    'Could not communicate with server to test table sorting'
                );
            }
        });
    }

    // ========================================
    // TRANSLATION MANAGEMENT
    // ========================================

    // Add new translation row
    $('#add-translation').on('click', function() {
        const newRow = `
            <tr class="translation-row">
                <td>
                    <input type="text" name="system_names[]" value="" class="regular-text" placeholder="e.g., iPhone18,2">
                </td>
                <td>
                    <input type="text" name="display_names[]" value="" class="regular-text" placeholder="e.g., iPhone 17 Plus">
                </td>
                <td>
                    <em><?php esc_html_e('Preview will appear here', 'geekbench-scraper'); ?></em>
                </td>
                <td>
                    <button type="button" class="button remove-translation"><?php esc_html_e('Remove', 'geekbench-scraper'); ?></button>
                </td>
            </tr>
        `;
        $('#translations-tbody').append(newRow);
    });
    
    // Remove translation row
    $(document).on('click', '.remove-translation', function() {
        if ($('.translation-row').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('<?php esc_html_e('You must have at least one translation row.', 'geekbench-scraper'); ?>');
        }
    });
    
    // Quick add buttons
    $('.quick-add').on('click', function() {
        const systemName = $(this).data('system');
        const displayName = $(this).data('display');
        
        // Check if already exists
        let exists = false;
        $('input[name="system_names[]"]').each(function() {
            if ($(this).val() === systemName) {
                exists = true;
                return false;
            }
        });
        
        if (exists) {
            alert('<?php esc_html_e('This translation already exists.', 'geekbench-scraper'); ?>');
            return;
        }
        
        // Find first empty row or add new one
        let $emptyRow = null;
        $('.translation-row').each(function() {
            const $systemInput = $(this).find('input[name="system_names[]"]');
            const $displayInput = $(this).find('input[name="display_names[]"]');
            
            if ($systemInput.val() === '' && $displayInput.val() === '') {
                $emptyRow = $(this);
                return false;
            }
        });
        
        if ($emptyRow) {
            $emptyRow.find('input[name="system_names[]"]').val(systemName);
            $emptyRow.find('input[name="display_names[]"]').val(displayName);
            $emptyRow.find('td:eq(2)').html('<code>' + systemName + '</code> → <strong>' + displayName + '</strong>');
        } else {
            const newRow = `
                <tr class="translation-row">
                    <td>
                        <input type="text" name="system_names[]" value="${systemName}" class="regular-text">
                    </td>
                    <td>
                        <input type="text" name="display_names[]" value="${displayName}" class="regular-text">
                    </td>
                    <td>
                        <code>${systemName}</code> → <strong>${displayName}</strong>
                    </td>
                    <td>
                        <button type="button" class="button remove-translation"><?php esc_html_e('Remove', 'geekbench-scraper'); ?></button>
                    </td>
                </tr>
            `;
            $('#translations-tbody').append(newRow);
        }
    });
    
    // Update preview on input change
    $(document).on('input', 'input[name="system_names[]"], input[name="display_names[]"]', function() {
        const $row = $(this).closest('tr');
        const systemName = $row.find('input[name="system_names[]"]').val();
        const displayName = $row.find('input[name="display_names[]"]').val();
        
        if (systemName && displayName) {
            $row.find('td:eq(2)').html('<code>' + systemName + '</code> → <strong>' + displayName + '</strong>');
        } else {
            $row.find('td:eq(2)').html('<em><?php esc_html_e('Preview will appear here', 'geekbench-scraper'); ?></em>');
        }
    });
});
</script>

