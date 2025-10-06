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
    
    <div class="geekbench-settings-header">
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
</style>

<script>
jQuery(document).ready(function($) {
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

