/**
 * Table Sorting JavaScript
 *
 * Handles client-side sorting for Geekbench results tables.
 * Supports ascending/descending toggle on all columns.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 */

(function() {
    'use strict';
    
    /**
     * Initialize table sorting
     */
    function initTableSort() {
        const tables = document.querySelectorAll('.geekbench-table');
        
        tables.forEach(table => {
            const headers = table.querySelectorAll('th.sortable');
            
            headers.forEach(header => {
                header.addEventListener('click', function() {
                    sortTable(table, this);
                });
            });
            
            // Set default sort (date descending)
            const dateHeader = table.querySelector('th[data-sort="date"]');
            if (dateHeader) {
                sortTable(table, dateHeader, 'desc');
            }
        });
    }
    
    /**
     * Sort table by column
     *
     * @param {HTMLTableElement} table Table element
     * @param {HTMLElement} header Header element clicked
     * @param {string|null} forceDirection Force sort direction ('asc' or 'desc')
     */
    function sortTable(table, header, forceDirection = null) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const sortKey = header.dataset.sort;
        
        // Determine sort direction
        let direction = forceDirection;
        if (!direction) {
            if (header.classList.contains('sort-asc')) {
                direction = 'desc';
            } else if (header.classList.contains('sort-desc')) {
                direction = null; // Reset to default
            } else {
                direction = 'asc';
            }
        }
        
        // Remove sort classes from all headers
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        
        // If resetting to default, sort by date descending
        if (direction === null) {
            const dateHeader = table.querySelector('th[data-sort="date"]');
            if (dateHeader) {
                sortTable(table, dateHeader, 'desc');
            }
            return;
        }
        
        // Add sort class to current header
        header.classList.add('sort-' + direction);
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = getSortValue(a, sortKey);
            const bValue = getSortValue(b, sortKey);
            
            let comparison = 0;
            
            // Numeric comparison for scores
            if (sortKey === 'single-core' || sortKey === 'multi-core') {
                comparison = parseFloat(aValue) - parseFloat(bValue);
            }
            // Date comparison
            else if (sortKey === 'date') {
                comparison = compareDates(aValue, bValue);
            }
            // String comparison
            else {
                comparison = aValue.localeCompare(bValue);
            }
            
            return direction === 'asc' ? comparison : -comparison;
        });
        
        // Re-append rows in sorted order
        rows.forEach(row => tbody.appendChild(row));
    }
    
    /**
     * Get sort value from row
     *
     * @param {HTMLElement} row Table row
     * @param {string} key Sort key
     * @return {string} Sort value
     */
    function getSortValue(row, key) {
        const dataAttr = 'data-' + key;
        return row.getAttribute(dataAttr) || '';
    }
    
    /**
     * Compare two date strings
     *
     * @param {string} dateA First date string
     * @param {string} dateB Second date string
     * @return {number} Comparison result
     */
    function compareDates(dateA, dateB) {
        const parsedA = parseDate(dateA);
        const parsedB = parseDate(dateB);
        
        if (!parsedA || !parsedB) {
            return dateA.localeCompare(dateB);
        }
        
        return parsedA - parsedB;
    }
    
    /**
     * Parse date string to timestamp
     *
     * Handles formats like "Oct 06, 2025"
     *
     * @param {string} dateStr Date string
     * @return {number|null} Timestamp or null if invalid
     */
    function parseDate(dateStr) {
        if (!dateStr) return null;
        
        try {
            const date = new Date(dateStr);
            return date.getTime();
        } catch (e) {
            return null;
        }
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTableSort);
    } else {
        initTableSort();
    }
    
    // Re-initialize when new content is loaded via AJAX
    // This handles both admin and frontend AJAX updates
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('geekbench-results-updated', initTableSort);
    }
    
    // Also expose globally for manual initialization
    window.geekbenchInitTableSort = initTableSort;
    
})();

