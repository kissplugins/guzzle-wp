<?php
/**
 * Results Table Template
 *
 * Shared template for displaying Geekbench results in a sortable table.
 * Used by both admin interface and frontend shortcode.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 *
 * Available variables:
 * @var array $results Array of benchmark results
 * @var string $table_class Optional custom table class (default: 'geekbench-table')
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Set default table class if not provided
if (!isset($table_class)) {
    $table_class = 'geekbench-table';
}
?>

<div class="geekbench-table-wrapper">
    <table class="<?php echo esc_attr($table_class); ?> widefat striped" id="geekbench-results-table">
        <thead>
            <tr>
                <th class="sortable" data-sort="system-name">
                    <?php esc_html_e('System Name', 'geekbench-scraper'); ?>
                    <span class="sort-indicator"></span>
                </th>
                <th class="sortable" data-sort="processor">
                    <?php esc_html_e('Processor', 'geekbench-scraper'); ?>
                    <span class="sort-indicator"></span>
                </th>
                <th class="sortable" data-sort="platform">
                    <?php esc_html_e('Platform', 'geekbench-scraper'); ?>
                    <span class="sort-indicator"></span>
                </th>
                <th class="sortable" data-sort="single-core">
                    <?php esc_html_e('Single-Core', 'geekbench-scraper'); ?>
                    <span class="sort-indicator"></span>
                </th>
                <th class="sortable" data-sort="multi-core">
                    <?php esc_html_e('Multi-Core', 'geekbench-scraper'); ?>
                    <span class="sort-indicator"></span>
                </th>
                <th class="sortable" data-sort="date">
                    <?php esc_html_e('Upload Date', 'geekbench-scraper'); ?>
                    <span class="sort-indicator"></span>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $result): ?>
                    <tr 
                        data-system-name="<?php echo esc_attr($result['system_name']); ?>"
                        data-processor="<?php echo esc_attr($result['processor_info']); ?>"
                        data-date="<?php echo esc_attr($result['upload_date']); ?>"
                        data-platform="<?php echo esc_attr($result['platform']); ?>"
                        data-single-core="<?php echo esc_attr($result['single_core_score']); ?>"
                        data-multi-core="<?php echo esc_attr($result['multi_core_score']); ?>"
                    >
                        <td class="system-name">
                            <?php
                            // Get translated name if available
                            $display_name = $result['system_name'];
                            $translations = get_option('geekbench_scraper_name_translations', array());
                            if (!empty($translations[$result['system_name']])) {
                                $display_name = $translations[$result['system_name']];
                            }
                            ?>
                            <?php if (!empty($result['benchmark_url'])): ?>
                                <a href="<?php echo esc_url($result['benchmark_url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html($display_name); ?>
                                    <?php if ($display_name !== $result['system_name']): ?>
                                        <small class="original-name">(<?php echo esc_html($result['system_name']); ?>)</small>
                                    <?php endif; ?>
                                    <span class="dashicons dashicons-external" style="font-size: 14px; vertical-align: middle;"></span>
                                </a>
                            <?php else: ?>
                                <?php echo esc_html($display_name); ?>
                                <?php if ($display_name !== $result['system_name']): ?>
                                    <small class="original-name">(<?php echo esc_html($result['system_name']); ?>)</small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="processor">
                            <small><?php echo esc_html($result['processor_info']); ?></small>
                        </td>
                        <td class="platform">
                            <span class="platform-badge platform-<?php echo esc_attr(strtolower($result['platform'])); ?>">
                                <?php echo esc_html($result['platform']); ?>
                            </span>
                        </td>
                        <td class="single-core score">
                            <strong><?php echo esc_html(number_format($result['single_core_score'])); ?></strong>
                        </td>
                        <td class="multi-core score">
                            <strong><?php echo esc_html(number_format($result['multi_core_score'])); ?></strong>
                        </td>
                        <td class="date">
                            <?php echo esc_html($result['upload_date']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-results">
                        <?php esc_html_e('No results found.', 'geekbench-scraper'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <?php if (!empty($results)): ?>
        <tfoot>
            <tr class="average-row">
                <td colspan="3" class="average-label">
                    <strong><?php esc_html_e('Average Scores', 'geekbench-scraper'); ?></strong>
                    <small class="result-count">(<?php echo count($results); ?> <?php echo _n('result', 'results', count($results), 'geekbench-scraper'); ?>)</small>
                </td>
                <td class="single-core score average-score">
                    <strong id="avg-single-core">-</strong>
                </td>
                <td class="multi-core score average-score">
                    <strong id="avg-multi-core">-</strong>
                </td>
                <td></td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<style>
.geekbench-table-wrapper {
    overflow-x: auto;
    margin: 20px 0;
}

.geekbench-table {
    width: 100%;
    border-collapse: collapse;
}

.geekbench-table th {
    background: #f0f0f1;
    font-weight: 600;
    text-align: left;
    padding: 12px;
    border-bottom: 2px solid #c3c4c7;
}

.geekbench-table th.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
    padding-right: 25px;
}

.geekbench-table th.sortable:hover {
    background: #e5e5e5;
}

.geekbench-table th .sort-indicator {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.3;
}

.geekbench-table th .sort-indicator::before {
    content: '↕';
    font-size: 14px;
}

.geekbench-table th.sort-asc .sort-indicator::before {
    content: '↑';
    opacity: 1;
}

.geekbench-table th.sort-desc .sort-indicator::before {
    content: '↓';
    opacity: 1;
}

.geekbench-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e0e0e0;
}

.geekbench-table tr:hover {
    background: #f9f9f9;
}

.geekbench-table .system-name a {
    text-decoration: none;
    color: #2271b1;
    font-weight: 500;
}

.geekbench-table .system-name a:hover {
    color: #135e96;
    text-decoration: underline;
}

.geekbench-table .processor {
    color: #666;
    font-size: 0.9em;
}

.geekbench-table .score {
    text-align: right;
    font-family: 'Courier New', monospace;
}

.geekbench-table .platform-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.85em;
    font-weight: 500;
}

.geekbench-table .platform-ios {
    background: #007aff;
    color: white;
}

.geekbench-table .platform-android {
    background: #3ddc84;
    color: #1a1a1a;
}

.geekbench-table .platform-macos {
    background: #000;
    color: white;
}

.geekbench-table .platform-windows {
    background: #0078d4;
    color: white;
}

.geekbench-table .platform-linux {
    background: #fcc624;
    color: #1a1a1a;
}

.geekbench-table .no-results {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}

/* Average Row Styling */
.geekbench-table tfoot tr.average-row {
    background: #f9f9f9;
    border-top: 3px solid #2271b1;
    font-weight: 600;
}

.geekbench-table tfoot tr.average-row:hover {
    background: #f9f9f9;
}

.geekbench-table tfoot .average-label {
    padding: 12px;
    color: #2271b1;
}

.geekbench-table tfoot .result-count {
    display: inline-block;
    margin-left: 8px;
    color: #666;
    font-weight: 400;
    font-size: 0.9em;
}

.geekbench-table tfoot .average-score {
    background: #e8f4f8;
    color: #135e96;
    font-size: 1.05em;
}

/* Responsive */
@media (max-width: 768px) {
    .geekbench-table {
        font-size: 0.9em;
    }

    .geekbench-table th,
    .geekbench-table td {
        padding: 8px;
    }

    .geekbench-table .processor {
        font-size: 0.8em;
    }

    .geekbench-table tfoot .result-count {
        display: block;
        margin-left: 0;
        margin-top: 4px;
    }
}
</style>

<!--
    ⚠️ CRITICAL: DO NOT REMOVE OR REFACTOR THIS JAVASCRIPT SECTION ⚠️
    This section contains the table sorting functionality.
    Removing this will break table sorting on both admin and frontend.
    Last verified: 2025-10-08
    Self-test available in: WP Admin → Tools → Geekbench Settings
-->
<script>
/**
 * Table Sorting Functionality
 *
 * ⚠️ CRITICAL COMPONENT - DO NOT REMOVE ⚠️
 *
 * This function provides ascending/descending sorting for all table columns.
 * Used by both admin interface and frontend shortcode.
 *
 * Features:
 * - Click column header to sort ascending
 * - Click again to sort descending
 * - Click third time to restore original order
 * - Visual indicators (↑ ↓ ↕)
 * - Handles text, numbers, and dates
 * - Preserves average row in footer
 *
 * @since 1.0.0
 * @version 1.3.1
 */
function initTableSort() {
    const table = document.querySelector('#geekbench-results-table');
    if (!table) return;

    const headers = table.querySelectorAll('th.sortable');
    const tbody = table.querySelector('tbody');

    if (!tbody) return;

    // Store original order
    const originalOrder = Array.from(tbody.querySelectorAll('tr'));

    headers.forEach(header => {
        let currentSort = null; // null = original, 'asc' = ascending, 'desc' = descending

        header.addEventListener('click', function() {
            const sortKey = this.getAttribute('data-sort');

            // Remove sort classes from all headers
            headers.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });

            // Determine next sort state
            if (currentSort === null) {
                currentSort = 'asc';
                this.classList.add('sort-asc');
            } else if (currentSort === 'asc') {
                currentSort = 'desc';
                this.classList.add('sort-desc');
            } else {
                currentSort = null;
                // Restore original order
                originalOrder.forEach(row => tbody.appendChild(row));
                return;
            }

            // Get all rows
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Sort rows
            rows.sort((a, b) => {
                let aValue, bValue;

                if (sortKey === 'single-core' || sortKey === 'multi-core') {
                    // Numeric sort for scores
                    aValue = parseInt(a.getAttribute('data-' + sortKey)) || 0;
                    bValue = parseInt(b.getAttribute('data-' + sortKey)) || 0;
                } else if (sortKey === 'date') {
                    // Date sort
                    aValue = a.getAttribute('data-date') || '';
                    bValue = b.getAttribute('data-date') || '';
                } else if (sortKey === 'system-name') {
                    // System name sort - use data attribute from row
                    aValue = (a.getAttribute('data-system-name') || '').toLowerCase();
                    bValue = (b.getAttribute('data-system-name') || '').toLowerCase();
                } else if (sortKey === 'processor') {
                    // Processor sort - use data attribute from row
                    aValue = (a.getAttribute('data-processor') || '').toLowerCase();
                    bValue = (b.getAttribute('data-processor') || '').toLowerCase();
                } else if (sortKey === 'platform') {
                    // Platform sort - use data attribute from row
                    aValue = (a.getAttribute('data-platform') || '').toLowerCase();
                    bValue = (b.getAttribute('data-platform') || '').toLowerCase();
                } else {
                    // Fallback: try to get value from cell text content
                    const columnIndex = Array.from(headers).findIndex(h => h.getAttribute('data-sort') === sortKey);
                    if (columnIndex >= 0) {
                        const aCell = a.cells[columnIndex];
                        const bCell = b.cells[columnIndex];
                        aValue = aCell ? aCell.textContent.trim().toLowerCase() : '';
                        bValue = bCell ? bCell.textContent.trim().toLowerCase() : '';
                    } else {
                        aValue = '';
                        bValue = '';
                    }
                }

                if (currentSort === 'asc') {
                    return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
                } else {
                    return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
                }
            });

            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });
}

// Auto-initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTableSort);
} else {
    initTableSort();
}

/**
 * Self-Test Function for Table Sorting
 *
 * ⚠️ DO NOT REMOVE - Used by settings page self-test ⚠️
 *
 * Tests if table sorting functionality is working correctly.
 * Called from WP Admin → Tools → Geekbench Settings
 *
 * @returns {Object} Test results with status and message
 */
function testTableSorting() {
    const results = {
        passed: true,
        tests: [],
        message: ''
    };

    // Test 1: Check if initTableSort function exists
    if (typeof initTableSort !== 'function') {
        results.passed = false;
        results.tests.push({
            name: 'Function Exists',
            status: 'FAIL',
            message: 'initTableSort function not found'
        });
        results.message = '❌ CRITICAL: Table sorting function is missing!';
        return results;
    }
    results.tests.push({
        name: 'Function Exists',
        status: 'PASS',
        message: 'initTableSort function found'
    });

    // Test 2: Check if table exists
    const table = document.querySelector('#geekbench-results-table');
    if (!table) {
        results.tests.push({
            name: 'Table Exists',
            status: 'SKIP',
            message: 'No table on current page (this is OK for settings page)'
        });
    } else {
        results.tests.push({
            name: 'Table Exists',
            status: 'PASS',
            message: 'Table found on page'
        });

        // Test 3: Check if sortable headers exist
        const sortableHeaders = table.querySelectorAll('th.sortable');
        if (sortableHeaders.length === 0) {
            results.passed = false;
            results.tests.push({
                name: 'Sortable Headers',
                status: 'FAIL',
                message: 'No sortable headers found'
            });
        } else {
            results.tests.push({
                name: 'Sortable Headers',
                status: 'PASS',
                message: `Found ${sortableHeaders.length} sortable columns`
            });
        }

        // Test 4: Check if sort indicators exist
        const sortIndicators = table.querySelectorAll('.sort-indicator');
        if (sortIndicators.length === 0) {
            results.passed = false;
            results.tests.push({
                name: 'Sort Indicators',
                status: 'FAIL',
                message: 'No sort indicators found'
            });
        } else {
            results.tests.push({
                name: 'Sort Indicators',
                status: 'PASS',
                message: `Found ${sortIndicators.length} sort indicators`
            });
        }

        // Test 5: Check if table rows have required data attributes
        const firstRow = table.querySelector('tbody tr');
        if (firstRow && !firstRow.classList.contains('no-results')) {
            const requiredAttrs = ['data-system-name', 'data-processor', 'data-platform', 'data-single-core', 'data-multi-core', 'data-date'];
            const missingAttrs = [];

            requiredAttrs.forEach(attr => {
                if (!firstRow.hasAttribute(attr)) {
                    missingAttrs.push(attr);
                }
            });

            if (missingAttrs.length > 0) {
                results.passed = false;
                results.tests.push({
                    name: 'Data Attributes',
                    status: 'FAIL',
                    message: `Missing attributes: ${missingAttrs.join(', ')}`
                });
            } else {
                results.tests.push({
                    name: 'Data Attributes',
                    status: 'PASS',
                    message: 'All required data attributes present on table rows'
                });
            }
        } else {
            results.tests.push({
                name: 'Data Attributes',
                status: 'SKIP',
                message: 'No data rows to test (table is empty)'
            });
        }
    }

    // Generate summary message
    if (results.passed) {
        results.message = '✅ All table sorting tests passed!';
    } else {
        results.message = '❌ Some table sorting tests failed. Check details above.';
    }

    return results;
}

// Expose to global scope for settings page
window.initTableSort = initTableSort;
window.testTableSorting = testTableSorting;
</script>
<!-- END CRITICAL SECTION - DO NOT REMOVE -->

