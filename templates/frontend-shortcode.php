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

// Get hint text from settings
$hint_text = get_option('geekbench_search_hint', 'Search for any device');

// Check if reCAPTCHA is enabled
$recaptcha_enabled = get_option('geekbench_recaptcha_enabled', 0);
$recaptcha_site_key = get_option('geekbench_recaptcha_site_key', '');
?>

<div class="geekbench-scraper-frontend" id="<?php echo esc_attr($instance_id); ?>" data-default-query="<?php echo esc_attr($query); ?>" data-limit="<?php echo esc_attr($limit); ?>">

    <!-- Modern Search Form -->
    <div class="geekbench-search-container">
        <form class="geekbench-search-form" data-instance="<?php echo esc_attr($instance_id); ?>">
            <div class="search-input-wrapper">
                <input
                    type="text"
                    name="query"
                    class="geekbench-search-input"
                    value="<?php echo esc_attr($query); ?>"
                    placeholder="<?php echo esc_attr($hint_text); ?>"
                    autocomplete="off"
                >
                <button type="submit" class="geekbench-search-submit" aria-label="<?php esc_attr_e('Search', 'geekbench-scraper'); ?>">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>

            <!-- Hint Text -->
            <p class="search-hint"><?php echo esc_html($hint_text); ?></p>

            <!-- reCAPTCHA Widget -->
            <?php if ($recaptcha_enabled && !empty($recaptcha_site_key)): ?>
                <div class="recaptcha-wrapper">
                    <div class="g-recaptcha"
                         data-sitekey="<?php echo esc_attr($recaptcha_site_key); ?>"
                         data-callback="geekbenchRecaptchaCallback_<?php echo esc_attr($instance_id); ?>">
                    </div>
                </div>
            <?php endif; ?>

            <input type="hidden" name="limit" value="<?php echo esc_attr($limit); ?>">
            <input type="hidden" name="instance_id" value="<?php echo esc_attr($instance_id); ?>">
        </form>
    </div>

    <!-- Error Message -->
    <div class="geekbench-error" style="display: none;">
        <div class="error-content">
            <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p class="error-message"></p>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div class="geekbench-loading" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
            <div class="spinner-ring"></div>
        </div>
        <p class="loading-text"><?php esc_html_e('Loading results...', 'geekbench-scraper'); ?></p>
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
/* Modern Frontend Styles */
.geekbench-scraper-frontend {
    max-width: 1200px;
    margin: 30px auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Search Container */
.geekbench-search-container {
    background: transparent;
    padding: 0;
    margin-bottom: 30px;
}

.geekbench-search-form {
    width: 100%;
}

/* Search Input Wrapper */
.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
    transition: border-color 0.2s ease;
}

.search-input-wrapper:focus-within {
    border-color: #2271b1;
}

.geekbench-search-input {
    flex: 1;
    padding: 12px 16px;
    border: none;
    background: transparent;
    font-size: 16px;
    outline: none;
    color: #24292e;
}

.geekbench-search-input::placeholder {
    color: #6a737d;
}

.geekbench-search-submit {
    padding: 10px 16px;
    background: #2271b1;
    border: none;
    cursor: pointer;
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.geekbench-search-submit:hover {
    background: #135e96;
}

.geekbench-search-submit:active {
    background: #0f4c75;
}

.search-icon {
    width: 20px;
    height: 20px;
    color: #ffffff;
}

/* Hint Text */
.search-hint {
    margin: 8px 0 0 0;
    font-size: 12px;
    color: #666;
    font-weight: 400;
}

/* reCAPTCHA Wrapper */
.recaptcha-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

/* Error Message */
.geekbench-error {
    background: #fff5f5;
    border: 1px solid #feb2b2;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
}

.error-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.error-icon {
    width: 24px;
    height: 24px;
    color: #e53e3e;
    flex-shrink: 0;
    margin-top: 2px;
}

.error-message {
    color: #742a2a;
    margin: 0;
    font-size: 15px;
    line-height: 1.5;
}

/* Loading Indicator */
.geekbench-loading {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.loading-spinner {
    display: inline-block;
    position: relative;
    width: 80px;
    height: 80px;
}

.spinner-ring {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 64px;
    height: 64px;
    margin: 8px;
    border: 6px solid #2271b1;
    border-radius: 50%;
    animation: spinner-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-color: #2271b1 transparent transparent transparent;
}

.spinner-ring:nth-child(1) {
    animation-delay: -0.45s;
}

.spinner-ring:nth-child(2) {
    animation-delay: -0.3s;
}

.spinner-ring:nth-child(3) {
    animation-delay: -0.15s;
}

@keyframes spinner-ring {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.loading-text {
    margin-top: 20px;
    font-size: 16px;
    color: #6a737d;
    font-weight: 500;
}

/* Results Container */
.geekbench-results {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.results-header {
    padding: 24px 30px;
    border-bottom: 1px solid #e1e4e8;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
}

.results-header h3 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
}

.results-count {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

.geekbench-empty {
    text-align: center;
    padding: 60px 20px;
    color: #6a737d;
}

.geekbench-empty p {
    font-size: 16px;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .geekbench-search-container {
        padding: 20px;
    }

    .geekbench-search-input {
        font-size: 16px;
        padding: 16px 20px;
    }

    .search-hint {
        font-size: 13px;
        margin-left: 20px;
    }

    .results-header {
        padding: 20px;
    }

    .results-header h3 {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .geekbench-scraper-frontend {
        margin: 20px 10px;
    }

    .geekbench-search-container {
        padding: 16px;
        border-radius: 8px;
    }

    .search-input-wrapper {
        border-radius: 40px;
    }

    .geekbench-search-input {
        font-size: 15px;
        padding: 14px 18px;
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

    const searchForm = instance.querySelector('.geekbench-search-form');
    const searchInput = instance.querySelector('.geekbench-search-input');
    const resultsContainer = instance.querySelector('.geekbench-results');
    const loadingIndicator = instance.querySelector('.geekbench-loading');
    const errorContainer = instance.querySelector('.geekbench-error');
    const errorMessage = instance.querySelector('.error-message');
    const recaptchaEnabled = <?php echo $recaptcha_enabled ? 'true' : 'false'; ?>;

    // Smart throttling: Track search count (client-side hint)
    // Note: Server-side validation is the final authority
    let searchCount = 0;
    let lastSearchTime = Date.now();
    const THROTTLE_WINDOW = 5 * 60 * 1000; // 5 minutes
    const MAX_SEARCHES_BEFORE_CAPTCHA = 5;

    // Reset counter after inactivity
    function checkThrottleReset() {
        if (Date.now() - lastSearchTime > 30 * 60 * 1000) { // 30 minutes
            searchCount = 0;
        }
    }

    // Check if CAPTCHA is required (client-side hint)
    // Server will enforce this regardless of client-side state
    function isCaptchaRequired() {
        if (!recaptchaEnabled) return false;
        checkThrottleReset();
        return searchCount >= MAX_SEARCHES_BEFORE_CAPTCHA;
    }

    // Auto-run default search on page load
    window.addEventListener('DOMContentLoaded', function() {
        // Calculate averages for any pre-loaded results
        calculateAverages();

        const defaultQuery = instance.dataset.defaultQuery;
        const limit = instance.dataset.limit;
        if (defaultQuery) {
            // First search doesn't require CAPTCHA
            fetchResults(defaultQuery, limit, false, true);
        }
    });

    // Search form handler
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const query = searchInput.value.trim();
            const limit = searchForm.querySelector('[name="limit"]').value;

            if (!query) {
                showError('<?php esc_html_e('Please enter a search term', 'geekbench-scraper'); ?>');
                return;
            }

            // Check if CAPTCHA is required and verify
            if (isCaptchaRequired() && typeof grecaptcha !== 'undefined') {
                const recaptchaResponse = grecaptcha.getResponse();
                if (!recaptchaResponse) {
                    showError('<?php esc_html_e('Please complete the reCAPTCHA verification', 'geekbench-scraper'); ?>');
                    return;
                }
            }

            fetchResults(query, limit);
        });

        // Enter key support
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // reCAPTCHA callback
    window['geekbenchRecaptchaCallback_<?php echo esc_js($instance_id); ?>'] = function() {
        // CAPTCHA completed, enable search
        console.log('reCAPTCHA verified');
    };

    // Show error message
    function showError(message) {
        if (errorContainer && errorMessage) {
            errorMessage.textContent = message;
            errorContainer.style.display = 'block';
            setTimeout(() => {
                errorContainer.style.display = 'none';
            }, 5000);
        }
    }

    // Hide error message
    function hideError() {
        if (errorContainer) {
            errorContainer.style.display = 'none';
        }
    }

    // Calculate and display average scores
    function calculateAverages() {
        const table = document.querySelector('#geekbench-results-table');
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        if (rows.length === 0) return;

        let singleCoreSum = 0;
        let multiCoreSum = 0;
        let count = 0;

        rows.forEach(row => {
            const singleCore = parseInt(row.getAttribute('data-single-core'));
            const multiCore = parseInt(row.getAttribute('data-multi-core'));

            if (!isNaN(singleCore) && !isNaN(multiCore)) {
                singleCoreSum += singleCore;
                multiCoreSum += multiCore;
                count++;
            }
        });

        if (count > 0) {
            const avgSingleCore = Math.round(singleCoreSum / count);
            const avgMultiCore = Math.round(multiCoreSum / count);

            const avgSingleCoreEl = document.getElementById('avg-single-core');
            const avgMultiCoreEl = document.getElementById('avg-multi-core');

            if (avgSingleCoreEl) {
                avgSingleCoreEl.textContent = avgSingleCore.toLocaleString();
            }
            if (avgMultiCoreEl) {
                avgMultiCoreEl.textContent = avgMultiCore.toLocaleString();
            }
        }
    }

    // Fetch results via AJAX
    function fetchResults(query, limit, refresh = false, isAutoLoad = false) {
        hideError();

        // Show loading
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
        if (resultsContainer) {
            resultsContainer.style.opacity = '0.5';
        }

        // Prepare data
        const data = new FormData();
        data.append('action', 'geekbench_scraper_fetch');
        data.append('query', query);
        data.append('limit', limit);

        // Add reCAPTCHA token if required
        if (isCaptchaRequired() && typeof grecaptcha !== 'undefined') {
            const recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse) {
                data.append('g-recaptcha-response', recaptchaResponse);
            }
        }

        // AJAX request
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update results
                if (resultsContainer && data.data.html) {
                    resultsContainer.innerHTML = data.data.html;
                    resultsContainer.style.opacity = '1';
                }

                // Increment search count for throttling
                if (!isAutoLoad) {
                    searchCount++;
                    lastSearchTime = Date.now();
                }

                // Reset reCAPTCHA if it was used
                if (typeof grecaptcha !== 'undefined') {
                    grecaptcha.reset();
                }

                // Re-initialize table sorting if available
                if (typeof initTableSort === 'function') {
                    initTableSort();
                }

                // Calculate and display averages
                calculateAverages();
            } else {
                // Show error
                const errorMsg = data.data?.message || '<?php esc_html_e('An error occurred', 'geekbench-scraper'); ?>';
                showError(errorMsg);

                // If server requires CAPTCHA, show the widget
                if (data.data?.requires_captcha && recaptchaEnabled) {
                    const recaptchaContainer = instance.querySelector('.g-recaptcha');
                    if (recaptchaContainer) {
                        recaptchaContainer.style.display = 'block';
                        // Scroll to CAPTCHA
                        recaptchaContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showError('<?php esc_html_e('Network error occurred. Please try again.', 'geekbench-scraper'); ?>');
        })
        .finally(() => {
            // Hide loading
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

