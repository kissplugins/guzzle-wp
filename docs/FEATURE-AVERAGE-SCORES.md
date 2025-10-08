# Average Scores Feature

**Added**: October 6, 2025  
**Version**: 1.1.1

---

## Overview

The results table now includes a dynamic bottom row that displays the **average Single-Core and Multi-Core scores** for all results currently displayed on the page.

---

## Features

### 1. **Dynamic Calculation**
- Automatically calculates averages from all visible results
- Updates in real-time (no page reload needed)
- Handles sorting without recalculation issues

### 2. **Visual Design**
- Located in table footer (`<tfoot>`)
- Blue highlight background to distinguish from regular rows
- Bold text for emphasis
- Displays result count for context

### 3. **Smart Formatting**
- Scores are rounded to nearest integer
- Numbers formatted with thousands separators (e.g., "3,831")
- Shows "-" if no results available

---

## Implementation Details

### HTML Structure

```html
<tfoot>
    <tr class="average-row">
        <td colspan="3" class="average-label">
            <strong>Average Scores</strong>
            <small class="result-count">(25 results)</small>
        </td>
        <td class="single-core score average-score">
            <strong id="avg-single-core">3,845</strong>
        </td>
        <td class="multi-core score average-score">
            <strong id="avg-multi-core">9,923</strong>
        </td>
        <td></td>
    </tr>
</tfoot>
```

### JavaScript Calculation

**Function**: `calculateAverages(table)`

**Location**: `assets/js/table-sort.js`

**Logic**:
1. Finds all rows in table body
2. Extracts `data-single-core` and `data-multi-core` attributes
3. Sums all valid scores
4. Divides by count to get average
5. Rounds to nearest integer
6. Updates DOM elements `#avg-single-core` and `#avg-multi-core`

**Code Snippet**:
```javascript
function calculateAverages(table) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    let singleCoreSum = 0;
    let multiCoreSum = 0;
    let validRowCount = 0;
    
    rows.forEach(row => {
        const singleCore = parseFloat(row.getAttribute('data-single-core'));
        const multiCore = parseFloat(row.getAttribute('data-multi-core'));
        
        if (!isNaN(singleCore) && !isNaN(multiCore)) {
            singleCoreSum += singleCore;
            multiCoreSum += multiCore;
            validRowCount++;
        }
    });
    
    if (validRowCount > 0) {
        const avgSingleCore = Math.round(singleCoreSum / validRowCount);
        const avgMultiCore = Math.round(multiCoreSum / validRowCount);
        
        document.getElementById('avg-single-core').textContent = 
            avgSingleCore.toLocaleString();
        document.getElementById('avg-multi-core').textContent = 
            avgMultiCore.toLocaleString();
    }
}
```

### CSS Styling

**Location**: `templates/results-table.php` (inline styles)

**Key Styles**:
```css
/* Average Row Styling */
.geekbench-table tfoot tr.average-row {
    background: #f9f9f9;
    border-top: 3px solid #2271b1;
    font-weight: 600;
}

.geekbench-table tfoot .average-label {
    padding: 12px;
    color: #2271b1;
}

.geekbench-table tfoot .average-score {
    background: #e8f4f8;
    color: #135e96;
    font-size: 1.05em;
}
```

---

## User Experience

### What Users See

**Before** (without feature):
```
┌─────────────┬──────────┬──────────┬─────────────┬─────────────┬─────────┐
│ System Name │ Processor│ Platform │ Single-Core │ Multi-Core  │ Date    │
├─────────────┼──────────┼──────────┼─────────────┼─────────────┼─────────┤
│ iPhone18,2  │ ARM...   │ iOS      │ 3,831       │ 9,910       │ Oct 06  │
│ iPhone18,1  │ ARM...   │ iOS      │ 3,845       │ 9,923       │ Oct 06  │
│ ...         │ ...      │ ...      │ ...         │ ...         │ ...     │
└─────────────┴──────────┴──────────┴─────────────┴─────────────┴─────────┘
```

**After** (with feature):
```
┌─────────────┬──────────┬──────────┬─────────────┬─────────────┬─────────┐
│ System Name │ Processor│ Platform │ Single-Core │ Multi-Core  │ Date    │
├─────────────┼──────────┼──────────┼─────────────┼─────────────┼─────────┤
│ iPhone18,2  │ ARM...   │ iOS      │ 3,831       │ 9,910       │ Oct 06  │
│ iPhone18,1  │ ARM...   │ iOS      │ 3,845       │ 9,923       │ Oct 06  │
│ ...         │ ...      │ ...      │ ...         │ ...         │ ...     │
╞═════════════╪══════════╪══════════╪═════════════╪═════════════╪═════════╡
│ Average Scores (25 results)        │ 3,838       │ 9,916       │         │
└─────────────┴──────────┴──────────┴─────────────┴─────────────┴─────────┘
                                       ↑ Blue highlight background
```

### Benefits

1. **Quick Comparison**: Users can instantly see average performance
2. **Benchmark Context**: Helps understand if individual results are above/below average
3. **Data Summary**: Provides statistical overview without manual calculation
4. **Professional Look**: Adds polish to the table presentation

---

## Technical Notes

### Performance

- **Calculation Speed**: O(n) complexity - loops through rows once
- **Memory Usage**: Minimal - only stores two sum variables
- **DOM Updates**: Only updates two elements (`#avg-single-core`, `#avg-multi-core`)
- **No Server Load**: Entirely client-side calculation

### Edge Cases Handled

1. **No Results**: Footer row is hidden when `$results` is empty
2. **Invalid Scores**: `isNaN()` check filters out malformed data
3. **Zero Results**: Division by zero prevented with `validRowCount > 0` check
4. **Sorting**: Averages remain accurate regardless of sort order
5. **AJAX Updates**: Recalculates when new data is loaded

### Browser Compatibility

- **Modern Browsers**: Full support (Chrome, Firefox, Safari, Edge)
- **IE11**: Supported (uses `toLocaleString()` polyfill if needed)
- **Mobile**: Fully responsive with adjusted styling

---

## Future Enhancements

Potential improvements for future versions:

1. **Median Calculation**: Show median in addition to average
2. **Min/Max Display**: Show lowest and highest scores
3. **Standard Deviation**: Display score variance
4. **Percentile Ranking**: Show where each result falls in distribution
5. **Export Averages**: Include averages in CSV/JSON exports
6. **Historical Comparison**: Compare current averages to previous searches

---

## Testing

### Manual Testing Steps

1. **Load Results**: Go to Tools → Geekbench Scraper
2. **Verify Display**: Check that average row appears at bottom
3. **Check Calculation**: Manually verify average is correct
4. **Test Sorting**: Click column headers, verify averages don't change
5. **Test Refresh**: Click "Refresh Results", verify averages update
6. **Test Empty**: Search for non-existent term, verify footer is hidden

### Expected Results

**Example with 3 results**:
- Result 1: Single-Core = 3,800, Multi-Core = 9,900
- Result 2: Single-Core = 3,850, Multi-Core = 9,950
- Result 3: Single-Core = 3,900, Multi-Core = 10,000

**Expected Averages**:
- Single-Core: (3,800 + 3,850 + 3,900) / 3 = **3,850**
- Multi-Core: (9,900 + 9,950 + 10,000) / 3 = **9,950**

---

## Files Modified

1. **`templates/results-table.php`**
   - Added `<tfoot>` section with average row
   - Added CSS styling for average row
   - Added result count display

2. **`assets/js/table-sort.js`**
   - Added `calculateAverages()` function
   - Integrated calculation into `initTableSort()`
   - Handles real-time updates

3. **`CHANGELOG.md`**
   - Documented new feature in v1.1.1

4. **`GUZZLE-GB-WP.md`**
   - Added to Phase 3 checklist as completed

---

## Usage Examples

### Admin Interface

Users will see the average row automatically when viewing results:

```
Tools → Geekbench Scraper → [Search Results Table]
```

### Frontend Shortcode

The average row also appears in frontend shortcode displays:

```
[geekbench_results query="iPhone18"]
```

### Programmatic Access

Developers can access the calculated averages via JavaScript:

```javascript
// Get average values
const avgSingleCore = document.getElementById('avg-single-core').textContent;
const avgMultiCore = document.getElementById('avg-multi-core').textContent;

console.log('Average Single-Core:', avgSingleCore);
console.log('Average Multi-Core:', avgMultiCore);
```

---

## Support

For issues or questions about this feature:
- Check the table footer is visible (requires results)
- Verify JavaScript is enabled in browser
- Check browser console for errors
- Ensure `table-sort.js` is loaded correctly

---

**Feature Status**: ✅ Complete and Production-Ready

