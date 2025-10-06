# System Self-Test Suite

**Added**: October 6, 2025  
**Version**: 1.1.1

---

## Overview

The Settings page now includes a comprehensive **System Self-Test Suite** with 6 critical diagnostic tests to ensure all plugin components are functioning correctly.

---

## Features

### 1. **Visual Status Indicator**
- Shows "X of 8 tests passed" at the top
- **Green text** if all tests pass ✅
- **Red text** if one or more tests fail ❌
- Real-time updates as tests run

### 2. **8 Critical Tests**

#### Test 1: PHP Version Check
- **Purpose**: Verify PHP version is 7.4 or higher
- **Pass Criteria**: PHP >= 7.4
- **Fail Message**: "PHP version is too old"
- **Details**: Shows current version vs. required version

#### Test 2: Guzzle HTTP Client
- **Purpose**: Check if Guzzle HTTP client is loaded
- **Pass Criteria**: `GuzzleHttp\Client` class exists
- **Fail Message**: "Guzzle HTTP Client not found"
- **Details**: Shows Guzzle version number
- **Fix**: Run `composer install`

#### Test 3: Symfony DomCrawler
- **Purpose**: Check if DomCrawler library is loaded
- **Pass Criteria**: `Symfony\Component\DomCrawler\Crawler` class exists
- **Fail Message**: "Symfony DomCrawler not found"
- **Details**: Confirms HTML parsing functionality
- **Fix**: Run `composer install`

#### Test 4: WordPress Core Functions
- **Purpose**: Verify WordPress functions are available
- **Pass Criteria**: All required functions exist:
  - `get_transient`
  - `set_transient`
  - `delete_transient`
  - `wp_remote_get`
  - `sanitize_text_field`
- **Fail Message**: "Missing WordPress functions"
- **Details**: Lists missing functions

#### Test 5: Cache System
- **Purpose**: Test WordPress transient cache functionality
- **Pass Criteria**: Can set, get, and delete transients
- **Fail Message**: "Failed to set/read cache"
- **Details**: Tests full cache lifecycle
- **How it works**:
  1. Creates test transient with random value
  2. Reads it back and verifies value matches
  3. Deletes the test transient
  4. All operations must succeed

#### Test 6: Geekbench Connectivity
- **Purpose**: Test connection to Geekbench Browser
- **Pass Criteria**: HTTP 200 response from `browser.geekbench.com`
- **Fail Message**: "Cannot connect to Geekbench"
- **Details**: Shows HTTP status code or error message
- **Timeout**: 10 seconds
- **Possible Issues**:
  - Network connectivity
  - Firewall blocking
  - Geekbench server down
  - DNS resolution failure

#### Test 7: Scraper Logic Test ⭐ NEW
- **Purpose**: Test core scraping functionality with real Geekbench data
- **Pass Criteria**: Successfully fetches and parses real results from Geekbench
- **Fail Message**: Various based on failure point
- **What it tests**:
  1. Scraper can fetch data from Geekbench
  2. Returns array of results (not empty)
  3. Results contain all required fields: `system_name`, `single_core`, `multi_core`, `upload_date`, `url`
  4. Data quality validation (non-empty names, valid scores)
- **Test Query**: "iPhone" (known to return results)
- **Details**: Shows number of results retrieved
- **Why it's critical**: Validates the entire scraping pipeline end-to-end

#### Test 8: HTML Parser Test ⭐ NEW
- **Purpose**: Test DOM selectors extract data correctly from HTML
- **Pass Criteria**: All 6 selectors extract correct values from sample HTML
- **Fail Message**: Shows which selectors failed
- **What it tests**:
  1. System name selector (`.col-6.col-sm-3 a .list-col-text`)
  2. Platform selector (`.col-6.col-sm-3 .list-col-text`)
  3. Single-core score selector (`.score` first)
  4. Multi-core score selector (`.score` second)
  5. Upload date selector (`.col-12 .list-col-text`)
  6. URL selector (`.col-6.col-sm-3 a` href attribute)
- **Sample Data**: Uses mock HTML that mimics Geekbench structure
- **Details**: Shows which selectors passed/failed with expected vs actual values
- **Why it's critical**: If Geekbench changes their HTML structure, this test will catch it immediately

### 3. **User Experience**
- Click "Run Tests" button to start
- Tests run sequentially with visual progress
- Each test shows:
  - ✅ Green checkmark if passed
  - ❌ Red X if failed
  - 🔄 Spinning icon while running
- Detailed results table with:
  - Status icon
  - Test name and description
  - Result message with details

---

## Usage

### How to Run Tests

1. **Go to Settings**: WordPress Admin → Tools → Geekbench Settings
2. **Find Self-Test Section**: At the top of the page
3. **Click "Run Tests"**: Button with checkmark icon
4. **Wait for Results**: Tests run automatically (takes ~5-10 seconds)
5. **Review Results**: Check which tests passed/failed

### Interpreting Results

**All Tests Passed (8 of 8)**:
```
✅ 8 of 8 tests passed
```
- Plugin is fully functional
- All dependencies installed correctly
- Network connectivity working
- Scraper and parser working correctly
- No action needed

**Some Tests Failed (e.g., 6 of 8)**:
```
❌ 6 of 8 tests passed
```
- Review failed tests in the table below
- Read error messages for troubleshooting
- Follow suggested fixes
- Re-run tests after fixing issues

---

## Implementation Details

### Frontend (JavaScript)

**Location**: `templates/settings-page.php`

**Key Functions**:
- `runSelfTests()` - Main test orchestrator
- `runNextTest(index)` - Sequential test execution
- `testPHPVersion(callback)` - AJAX call for PHP test
- `testGuzzleLoaded(callback)` - AJAX call for Guzzle test
- `testDomCrawlerLoaded(callback)` - AJAX call for DomCrawler test
- `testWordPressFunctions(callback)` - AJAX call for WordPress test
- `testCacheSystem(callback)` - AJAX call for cache test
- `testGeekbenchConnectivity(callback)` - AJAX call for connectivity test
- `testScraperLogic(callback)` - AJAX call for scraper logic test
- `testHTMLParser(callback)` - AJAX call for HTML parser test

**Test Flow**:
```
User clicks "Run Tests"
  ↓
runSelfTests() initializes
  ↓
runNextTest(0) starts first test
  ↓
AJAX request to server
  ↓
Server runs test and returns result
  ↓
Update UI with pass/fail
  ↓
runNextTest(1) starts next test
  ↓
... repeat for all 6 tests ...
  ↓
finishTests() updates final status
```

### Backend (PHP)

**Location**: `src/Admin.php`

**AJAX Handler**: `ajax_self_test()`
- Verifies nonce for security
- Checks user permissions (`manage_options`)
- Routes to appropriate test method

**Test Methods**:
- `test_php_version()` - Uses `phpversion()` and `version_compare()`
- `test_guzzle_loaded()` - Uses `class_exists('GuzzleHttp\\Client')`
- `test_domcrawler_loaded()` - Uses `class_exists('Symfony\\Component\\DomCrawler\\Crawler')`
- `test_wordpress_functions()` - Uses `function_exists()` for each function
- `test_cache_system()` - Uses `set_transient()`, `get_transient()`, `delete_transient()`
- `test_geekbench_connectivity()` - Uses Guzzle to make HTTP request
- `test_scraper_logic()` - Calls `$this->scraper->fetch()` with test query, validates results structure and data quality
- `test_html_parser()` - Creates sample HTML, uses DomCrawler to test all 6 critical selectors

**Response Format**:
```php
// Success
wp_send_json_success([
    'message' => 'Test passed message',
    'details' => 'Additional details'
]);

// Failure
wp_send_json_error([
    'message' => 'Test failed message',
    'details' => 'Error details and fix suggestions'
]);
```

---

## Troubleshooting

### Test 1 Failed: PHP Version

**Error**: "PHP version is too old"

**Fix**:
1. Check current PHP version: `php -v`
2. Upgrade to PHP 7.4 or higher
3. Contact hosting provider if needed
4. Update server configuration

### Test 2 Failed: Guzzle HTTP Client

**Error**: "Guzzle HTTP Client not found"

**Fix**:
```bash
cd /path/to/plugin
composer install
```

**Verify**:
```bash
ls -la vendor/guzzlehttp/
```

### Test 3 Failed: Symfony DomCrawler

**Error**: "Symfony DomCrawler not found"

**Fix**:
```bash
cd /path/to/plugin
composer install
```

**Verify**:
```bash
ls -la vendor/symfony/dom-crawler/
```

### Test 4 Failed: WordPress Functions

**Error**: "Missing WordPress functions"

**Possible Causes**:
- WordPress not fully loaded
- Plugin loaded too early
- WordPress core files corrupted

**Fix**:
1. Deactivate and reactivate plugin
2. Check WordPress installation integrity
3. Reinstall WordPress core files

### Test 5 Failed: Cache System

**Error**: "Failed to set/read cache"

**Possible Causes**:
- Database connection issues
- Permissions problems
- Object cache plugin conflict

**Fix**:
1. Check database connection
2. Verify `wp_options` table exists
3. Temporarily disable object cache plugins
4. Check file permissions

### Test 6 Failed: Geekbench Connectivity

**Error**: "Cannot connect to Geekbench"

**Possible Causes**:
- No internet connection
- Firewall blocking outbound requests
- Geekbench server down
- DNS resolution failure

**Fix**:
1. Check server internet connectivity: `ping browser.geekbench.com`
2. Check firewall rules
3. Verify DNS resolution: `nslookup browser.geekbench.com`
4. Try again later (server may be temporarily down)
5. Contact hosting provider about outbound connection restrictions

### Test 7 Failed: Scraper Logic Test

**Error**: "Scraper returned no results" or "Scraper data quality issues"

**Possible Causes**:
- Geekbench HTML structure changed
- Network issues preventing data fetch
- Parsing logic broken
- Cache returning stale/empty data

**Fix**:
1. **Clear cache**: Delete all transients starting with `geekbench_results_`
   ```php
   // In WordPress admin, run this in a temporary plugin or theme:
   global $wpdb;
   $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_geekbench_results_%'");
   ```
2. **Test manually**: Go to main plugin page and search for "iPhone"
3. **Check error logs**: Look in WordPress debug.log for errors
4. **Verify Test 6 passed**: If connectivity test failed, fix that first
5. **Check Geekbench website**: Visit https://browser.geekbench.com/search?q=iPhone manually
6. **Update selectors**: If Geekbench changed their HTML, update `src/Scraper.php` selectors

**Error**: "Scraper results missing required fields"

**Fix**:
1. Run Test 8 (HTML Parser) to identify which selectors are broken
2. Update the affected selectors in `src/Scraper.php`
3. Check the critical code warnings in `src/Scraper.php` - don't refactor those sections

### Test 8 Failed: HTML Parser Test

**Error**: "X/6 selectors failed"

**Possible Causes**:
- Sample HTML in test doesn't match actual Geekbench structure
- Selectors in `src/Scraper.php` were modified incorrectly
- DomCrawler syntax error

**Fix**:
1. **Check which selectors failed**: Read the detailed error message
2. **Compare with live HTML**:
   ```bash
   curl -s "https://browser.geekbench.com/search?q=iPhone" > geekbench.html
   # Open geekbench.html and inspect the structure
   ```
3. **Update sample HTML in test**: If Geekbench changed their structure, update the sample HTML in `test_html_parser()` method
4. **Update selectors in Scraper**: Update the corresponding selectors in `src/Scraper.php`:
   - System name: Line ~XXX
   - Platform: Line ~XXX
   - Scores: Line ~XXX
   - Upload date: Line ~XXX
   - URL: Line ~XXX
5. **Re-run Test 7**: After fixing selectors, verify scraper logic test passes

**Common Selector Issues**:
- **System name fails**: Check `.col-6.col-sm-3 a .list-col-text` selector
- **Scores fail**: Check `.score` selector and `eq(0)` / `eq(1)` indices
- **Date fails**: Check `.col-12 .list-col-text` selector and regex pattern
- **URL fails**: Check `.col-6.col-sm-3 a` selector and `attr('href')`

---

## Security

### Nonce Verification
All AJAX requests use WordPress nonces:
```javascript
nonce: '<?php echo wp_create_nonce('geekbench_self_test'); ?>'
```

### Permission Checks
Only administrators can run tests:
```php
if (!current_user_can('manage_options')) {
    wp_send_json_error(['message' => 'Insufficient permissions']);
}
```

### Input Sanitization
Test names are sanitized:
```php
$test = sanitize_text_field($_POST['test']);
```

---

## Performance

- **Total Time**: ~10-20 seconds for all 8 tests
- **Individual Test Time**:
  - PHP Version: <100ms
  - Guzzle Loaded: <100ms
  - DomCrawler Loaded: <100ms
  - WordPress Functions: <100ms
  - Cache System: <200ms
  - Geekbench Connectivity: 1-10 seconds (network dependent)
  - Scraper Logic: 2-10 seconds (fetches real data)
  - HTML Parser: <200ms (uses sample data)

- **Server Load**: Minimal (simple checks + 1 real scrape)
- **Network Usage**:
  - Test 6: 1 HTTP request to Geekbench (connectivity check)
  - Test 7: 1 HTTP request to Geekbench (full scrape with "iPhone" query)

---

## Files Modified

1. **`templates/settings-page.php`**
   - Added self-test UI section
   - Added CSS styling for test results
   - Added JavaScript for test execution

2. **`src/Admin.php`**
   - Added `ajax_self_test()` AJAX handler
   - Added 6 test methods
   - Registered AJAX action hook

3. **`CHANGELOG.md`**
   - Documented new feature in v1.1.1

4. **`GUZZLE-GB-WP.md`**
   - Added to Phase 3 checklist as completed

---

## Future Enhancements

Potential improvements for future versions:

1. **Additional Tests**:
   - Test scraper functionality with sample HTML
   - Test database table structure
   - Test file permissions
   - Test memory limits

2. **Automated Testing**:
   - Schedule tests to run daily
   - Email admin if tests fail
   - Log test results over time

3. **Export Results**:
   - Download test results as PDF
   - Share results with support team
   - Compare results over time

4. **Fix Suggestions**:
   - One-click fix buttons for common issues
   - Automatic dependency installation
   - Guided troubleshooting wizard

---

## Support

For issues with self-tests:
1. Run tests multiple times to confirm failure
2. Check error messages for specific guidance
3. Review this documentation for troubleshooting steps
4. Check WordPress debug log for additional errors
5. Contact support with test results screenshot

---

**Feature Status**: ✅ Complete and Production-Ready

