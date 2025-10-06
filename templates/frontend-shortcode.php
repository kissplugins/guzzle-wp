<?php
/**
 * Frontend Shortcode Template
 *
 * Template for the [geekbench_results] shortcode output.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 *
 * Available variables:
 * @var string $query Search query
 * @var int $limit Result limit
 * @var bool $show_search Show search form
 * @var bool $show_refresh Show refresh button
 * @var string $table_class Table CSS class
 * @var array $results Array of benchmark results
 * @var string|null $error Error message if any
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Generate unique ID for this shortcode instance
$instance_id = 'geekbench-' . uniqid();
?>

<div class="geekbench-scraper-frontend" id="<?php echo esc_attr($instance_id); ?>">
    
    <?php if ($show_search): ?>
        <!-- Search Form -->
        <div class="geekbench-search-form">
            <form class="geekbench-search" data-instance="<?php echo esc_attr($instance_id); ?>">
                <div class="search-input-group">
                    <input 
                        type="text" 
                        name="query" 
                        class="geekbench-search-input" 
                        value="<?php echo esc_attr($query); ?>"
                        placeholder="<?php esc_attr_e('Search Geekbench...', 'geekbench-scraper'); ?>"
                    >
                    <button type="submit" class="geekbench-search-button">
                        <?php esc_html_e('Search', 'geekbench-scraper'); ?>
                    </button>
                    
                    <?php if ($show_refresh): ?>
                        <button type="button" class="geekbench-refresh-button">
                            <?php esc_html_e('Refresh', 'geekbench-scraper'); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="limit" value="<?php echo esc_attr($limit); ?>">
            </form>
        </div>
    <?php elseif ($show_refresh): ?>
        <!-- Refresh Button Only -->
        <div class="geekbench-controls">
            <button type="button" class="geekbench-refresh-button" data-instance="<?php echo esc_attr($instance_id); ?>" data-query="<?php echo esc_attr($query); ?>" data-limit="<?php echo esc_attr($limit); ?>">
                <?php esc_html_e('Refresh Results', 'geekbench-scraper'); ?>
            </button>
        </div>
    <?php endif; ?>
    
    <!-- Error Message -->
    <?php if ($error): ?>
        <div class="geekbench-error">
            <p><strong><?php esc_html_e('Error:', 'geekbench-scraper'); ?></strong> <?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Loading Indicator -->
    <div class="geekbench-loading" style="display: none;">
        <div class="loading-spinner"></div>
        <p><?php esc_html_e('Loading results...', 'geekbench-scraper'); ?></p>
    </div>
    
    <!-- Results Container -->
    <div class="geekbench-results">
        <?php if (!empty($results)): ?>
            <div class="results-header">
                <h3>
                    <?php 
                    printf(
                        esc_html__('Geekbench Results for "%s"', 'geekbench-scraper'),
                        esc_html($query)
                    );
                    ?>
                </h3>
                <p class="results-count">
                    <?php 
                    printf(
                        esc_html(_n('%d result found', '%d results found', count($results), 'geekbench-scraper')),
                        count($results)
                    );
                    ?>
                </p>
            </div>
            
            <?php include GEEKBENCH_SCRAPER_PLUGIN_DIR . 'templates/results-table.php'; ?>
            
        <?php elseif (!$error): ?>
            <div class="geekbench-empty">
                <p><?php esc_html_e('No results to display.', 'geekbench-scraper'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.geekbench-scraper-frontend {
    margin: 20px 0;
}

.geekbench-search-form {
    margin-bottom: 20px;
}

.search-input-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.geekbench-search-input {
    flex: 1;
    min-width: 200px;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.geekbench-search-button,
.geekbench-refresh-button {
    padding: 10px 20px;
    background: #2271b1;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
}

.geekbench-search-button:hover,
.geekbench-refresh-button:hover {
    background: #135e96;
}

.geekbench-refresh-button {
    background: #666;
}

.geekbench-refresh-button:hover {
    background: #444;
}

.geekbench-controls {
    margin-bottom: 20px;
}

.geekbench-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.geekbench-loading {
    text-align: center;
    padding: 40px;
}

.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2271b1;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.geekbench-empty {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}

.results-header {
    margin-bottom: 15px;
}

.results-header h3 {
    margin: 0 0 5px 0;
    font-size: 1.5em;
}

.results-count {
    margin: 0;
    color: #666;
    font-size: 0.9em;
}

/* Responsive */
@media (max-width: 600px) {
    .search-input-group {
        flex-direction: column;
    }
    
    .geekbench-search-input {
        width: 100%;
    }
    
    .geekbench-search-button,
    .geekbench-refresh-button {
        width: 100%;
    }
}
</style>

<script>
(function() {
    const instance = document.getElementById('<?php echo esc_js($instance_id); ?>');
    if (!instance) return;
    
    const searchForm = instance.querySelector('.geekbench-search');
    const refreshButton = instance.querySelector('.geekbench-refresh-button');
    const resultsContainer = instance.querySelector('.geekbench-results');
    const loadingIndicator = instance.querySelector('.geekbench-loading');
    
    // Search form handler
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(searchForm);
            fetchResults(formData.get('query'), formData.get('limit'));
        });
    }
    
    // Refresh button handler
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            const query = refreshButton.dataset.query || searchForm?.querySelector('[name="query"]')?.value;
            const limit = refreshButton.dataset.limit || searchForm?.querySelector('[name="limit"]')?.value;
            fetchResults(query, limit, true);
        });
    }
    
    // Fetch results via AJAX
    function fetchResults(query, limit, refresh = false) {
        // Show loading
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        if (resultsContainer) {
            resultsContainer.style.opacity = '0.5';
        }
        
        // Prepare data
        const data = new FormData();
        data.append('action', refresh ? 'geekbench_scraper_refresh' : 'geekbench_scraper_fetch');
        data.append('query', query);
        data.append('limit', limit);
        
        // AJAX request
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && resultsContainer) {
                resultsContainer.innerHTML = data.data.html;
            } else {
                if (resultsContainer) {
                    resultsContainer.innerHTML = '<div class="geekbench-error"><p>' + (data.data?.message || 'An error occurred') + '</p></div>';
                }
            }
        })
        .catch(error => {
            if (resultsContainer) {
                resultsContainer.innerHTML = '<div class="geekbench-error"><p>Network error occurred</p></div>';
            }
        })
        .finally(() => {
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
            if (resultsContainer) {
                resultsContainer.style.opacity = '1';
            }
        });
    }
})();
</script>

