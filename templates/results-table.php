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
                <th class="sortable" data-sort="date">
                    <?php esc_html_e('Upload Date', 'geekbench-scraper'); ?>
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
                            <?php if (!empty($result['benchmark_url'])): ?>
                                <a href="<?php echo esc_url($result['benchmark_url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html($result['system_name']); ?>
                                    <span class="dashicons dashicons-external" style="font-size: 14px; vertical-align: middle;"></span>
                                </a>
                            <?php else: ?>
                                <?php echo esc_html($result['system_name']); ?>
                            <?php endif; ?>
                        </td>
                        <td class="processor">
                            <small><?php echo esc_html($result['processor_info']); ?></small>
                        </td>
                        <td class="date">
                            <?php echo esc_html($result['upload_date']); ?>
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
}
</style>

