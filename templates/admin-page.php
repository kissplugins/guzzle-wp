<?php
/**
 * Admin Page Template
 *
 * Template for the Geekbench Scraper admin page.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 *
 * Available variables:
 * @var string $default_query Default search query
 * @var string $current_query Current search query
 * @var array $results Array of benchmark results
 * @var string|null $error Error message if any
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap geekbench-scraper-admin">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<div class="geekbench-scraper-header">
		<p class="description">
			<?php esc_html_e( 'Search and view Geekbench browser benchmark results. Default search: iPhone18 (iPhone 17 models with A19 chip).', 'geekbench-scraper' ); ?>
		</p>
	</div>
	
	<!-- Search Form -->
	<div class="geekbench-scraper-search-form">
		<form method="get" action="" id="geekbench-search-form">
			<input type="hidden" name="page" value="geekbench-scraper">
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="search-query">
							<?php esc_html_e( 'Search Query', 'geekbench-scraper' ); ?>
						</label>
					</th>
					<td>
						<input 
							type="text" 
							id="search-query" 
							name="query" 
							value="<?php echo esc_attr( $current_query ); ?>" 
							class="regular-text"
							placeholder="<?php echo esc_attr( $default_query ); ?>"
						>
						<p class="description">
							<?php esc_html_e( 'Enter a device name or model (e.g., "iPhone18", "MacBook Pro", "Galaxy S24")', 'geekbench-scraper' ); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<p class="submit">
				<button type="submit" class="button button-primary" id="search-button">
					<span class="dashicons dashicons-search"></span>
					<?php esc_html_e( 'Search', 'geekbench-scraper' ); ?>
				</button>
				
				<button type="button" class="button" id="refresh-button">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Refresh Results', 'geekbench-scraper' ); ?>
				</button>
				
				<span class="spinner" id="loading-spinner"></span>
			</p>
		</form>
	</div>
	
	<!-- Error Message -->
	<?php if ( $error ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><strong><?php esc_html_e( 'Error:', 'geekbench-scraper' ); ?></strong> <?php echo esc_html( $error ); ?></p>
		</div>
	<?php endif; ?>
	
	<!-- Results Section -->
	<div class="geekbench-scraper-results" id="results-container">
		<?php if ( ! empty( $results ) ) : ?>
			<h2>
				<?php
				printf(
					esc_html__( 'Results for "%1$s" (%2$d found)', 'geekbench-scraper' ),
					esc_html( $current_query ),
					count( $results )
				);
				?>
			</h2>
			
			<?php include GEEKBENCH_SCRAPER_PLUGIN_DIR . 'templates/results-table.php'; ?>
			
		<?php elseif ( ! $error ) : ?>
			<div class="notice notice-info">
				<p><?php esc_html_e( 'Enter a search query above to fetch Geekbench results.', 'geekbench-scraper' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>

<style>
.geekbench-scraper-admin {
	max-width: 1200px;
}

.geekbench-scraper-header {
	margin-bottom: 20px;
}

.geekbench-scraper-search-form {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px;
	margin: 20px 0;
}

#loading-spinner {
	float: none;
	margin-left: 10px;
	vertical-align: middle;
}

#loading-spinner.is-active {
	visibility: visible;
}

.geekbench-scraper-results {
	margin-top: 30px;
}
</style>

<script>
jQuery(document).ready(function($) {
	const $searchForm = $('#geekbench-search-form');
	const $searchButton = $('#search-button');
	const $refreshButton = $('#refresh-button');
	const $spinner = $('#loading-spinner');
	const $resultsContainer = $('#results-container');
	const $searchQuery = $('#search-query');
	
	// Refresh button handler
	$refreshButton.on('click', function(e) {
		e.preventDefault();
		
		const query = $searchQuery.val() || '<?php echo esc_js( $default_query ); ?>';
		
		// Show loading state
		$spinner.addClass('is-active');
		$refreshButton.prop('disabled', true);
		$searchButton.prop('disabled', true);
		
		// AJAX request to refresh results
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'geekbench_scraper_refresh',
				nonce: geekbenchScraper.nonce,
				query: query
			},
			success: function(response) {
				if (response.success) {
					// Update results
					$resultsContainer.html(
						'<h2>' + 
						'<?php esc_html_e( 'Results for', 'geekbench-scraper' ); ?> "' + query + '" (' + response.data.count + ' <?php esc_html_e( 'found', 'geekbench-scraper' ); ?>)' +
						'</h2>' +
						response.data.html
					);
					
					// Show success message
					if (response.data.message) {
						showNotice('success', response.data.message);
					}
				} else {
					showNotice('error', response.data.message || '<?php esc_html_e( 'Failed to refresh results', 'geekbench-scraper' ); ?>');
				}
			},
			error: function() {
				showNotice('error', '<?php esc_html_e( 'Network error occurred', 'geekbench-scraper' ); ?>');
			},
			complete: function() {
				$spinner.removeClass('is-active');
				$refreshButton.prop('disabled', false);
				$searchButton.prop('disabled', false);
			}
		});
	});
	
	// Helper function to show notices
	function showNotice(type, message) {
		const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
		$('.geekbench-scraper-header').after($notice);
		
		// Auto-dismiss after 5 seconds
		setTimeout(function() {
			$notice.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
	}
});
</script>

