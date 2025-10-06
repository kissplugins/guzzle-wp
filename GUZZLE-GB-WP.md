# Guzzle + DOM WordPress Plugin Development Plan

A phased approach to convert a local Guzzle repository into a WordPress plugin for scraping and displaying Geekbench browser results.

Example URL: https://browser.geekbench.com/search?q=Iphone18

---

## HTML/CSS/JS Structure Analysis

### Page Structure Overview
- **URL Pattern**: `https://browser.geekbench.com/search?q={search_term}`
- **Total Results Found**: 25 results per page for "Iphone18" query
- **Page Type**: Server-rendered HTML (no JavaScript required for data extraction)
- **Framework**: Bootstrap-based responsive layout

### DOM Selectors for Data Extraction

#### Result Container
- **Parent Container**: `div.col-12.list-col` (25 instances found)
- **Inner Wrapper**: `div.list-col-inner > div.row`

#### Data Fields per Result

1. **System Name & Link**
   - **Selector**: `.col-12.col-lg-4 a[href^="/v6/cpu/"]`
   - **Text Content**: Device name (e.g., "iPhone18,2")
   - **Attribute**: `href` contains benchmark ID (e.g., "/v6/cpu/14288820")
   - **Parent**: First column in row

2. **Processor Details**
   - **Selector**: `.col-12.col-lg-4 .list-col-model`
   - **Text Content**: Multi-line (ARM / 4257 MHz / (6 cores))
   - **Format**: Processor type, frequency, core count

3. **Upload Date**
   - **Selector**: `.col-6.col-md-3.col-lg-2:nth-of-type(2) .list-col-text`
   - **Text Content**: "Oct 06, 2025" (may include username link)
   - **Note**: Some entries have `<a href="/user/{username}">` after date

4. **Platform**
   - **Selector**: `.col-6.col-md-3.col-lg-2:nth-of-type(3) .list-col-text`
   - **Text Content**: "iOS", "Android", "macOS", etc.

5. **Single-Core Score**
   - **Selector**: `.col-6.col-md-3.col-lg-2:nth-of-type(4) .list-col-text-score`
   - **Text Content**: Numeric score (e.g., "3831")

6. **Multi-Core Score**
   - **Selector**: `.col-6.col-md-3.col-lg-2:nth-of-type(5) .list-col-text-score`
   - **Text Content**: Numeric score (e.g., "9910")

### CSS Classes Reference
- `.list-col-subtitle`: Field labels ("System", "Uploaded", "Platform", etc.)
- `.list-col-text`: Regular text content
- `.list-col-text-score`: Score values (styled differently)
- `.list-col-model`: Processor/hardware details

### Additional Metadata
- **CSRF Token**: Present in meta tag (`name="csrf-token"`)
- **Character Encoding**: UTF-8
- **Responsive Breakpoints**: Bootstrap grid (col-12, col-lg-4, col-md-3, etc.)

---

## Phase 1: WordPress Plugin Structure & PSR-4 Setup

**Goal**: Transform the Guzzle repo folder into a properly structured WordPress plugin with PSR-4 autoloading.

### High-Level Checklist

- [ ] **Create WordPress plugin foundation**
  - Add main plugin file (`geekbench-scraper.php`) with proper header comments
  - Set up plugin activation/deactivation hooks
  - Create folder structure: `/src`, `/vendor`, `/assets`, `/templates`, `/tests`
  - Add `.gitignore` for `/vendor` and IDE files
  - Create `README.md` with installation and usage instructions

- [ ] **Implement PSR-4 autoloader**
  - Create `composer.json` with PSR-4 namespace mapping (e.g., `GeekbenchScraper\\`)
  - Add Guzzle and symfony/dom-crawler as Composer dependencies
  - Add PHPUnit as dev dependency (`require-dev`)
  - Run `composer install` to generate autoloader and vendor directory
  - Configure autoload-dev for test namespace

- [ ] **Establish plugin namespace architecture**
  - Create base classes in `/src`: `Plugin.php`, `Scraper.php`, `Admin.php`, `Shortcode.php`
  - Set up namespace structure following PSR-4 (matching folder hierarchy)
  - Bootstrap autoloader in main plugin file and initialize plugin class
  - Register shortcode handler for frontend display

---

## Phase 2: Scraper Implementation

**Goal**: Build the core scraping functionality to fetch and parse Geekbench search results.

### High-Level Checklist

- [ ] **Build Guzzle HTTP client wrapper**
  - Create `Scraper.php` class with Guzzle HTTP client initialization
  - Configure User-Agent header to mimic browser requests
  - Add method to fetch Geekbench search URL with proper headers and error handling
  - Implement response validation (200 status code) and HTML content extraction
  - Add timeout settings (10-15 seconds recommended)

- [ ] **Parse HTML with DomCrawler**
  - Use Symfony DomCrawler to parse Geekbench search results HTML
  - Target parent selector: `div.col-12.list-col` (returns ~25 results per page)
  - Extract data fields per result:
    * **System Name**: `.col-12.col-lg-4 a[href^="/v6/cpu/"]` (text + href for benchmark ID)
    * **Processor Info**: `.col-12.col-lg-4 .list-col-model` (parse multi-line: type, MHz, cores)
    * **Upload Date**: `.col-6.col-md-3.col-lg-2:nth-of-type(2) .list-col-text` (trim whitespace/BR tags)
    * **Platform**: `.col-6.col-md-3.col-lg-2:nth-of-type(3) .list-col-text` (iOS, Android, etc.)
    * **Single-Core Score**: `.col-6.col-md-3.col-lg-2:nth-of-type(4) .list-col-text-score` (numeric)
    * **Multi-Core Score**: `.col-6.col-md-3.col-lg-2:nth-of-type(5) .list-col-text-score` (numeric)
  - Structure parsed data into normalized array format (limit to 30 results max)
  - Handle edge cases: missing usernames, empty fields, malformed HTML

- [ ] **Add data caching layer**
  - Implement WordPress transient caching for scraped results (15-minute expiry)
  - Create cache key based on sanitized search query parameters
  - Add manual cache refresh functionality (bypass cache on demand)
  - Store both raw HTML and parsed data for debugging purposes
  - **NOTE**: Data is EPHEMERAL - no database storage, only temporary cache
  - Cache is cleared after expiry or manual refresh - no persistent storage of results

---

## Phase 3: WordPress Admin Interface

**Goal**: Create an interactive admin page to display, sort, and interact with scraped results.

### High-Level Checklist

- [ ] **Build admin menu and page**
  - Register admin menu item under Tools or custom top-level menu
  - Create admin page template with search form for Geekbench queries
  - Add AJAX endpoint for fetching/refreshing scraper results
  - **Default search query**: "iPhone18" (iPhone 17 models with A19/ARM 4257 chip)

- [ ] **Implement sortable results table**
  - Display results in HTML table with columns: System Name, Date, Platform, Single-Core, Multi-Core
  - Add JavaScript for client-side table sorting with ascending/descending toggle on ALL column headers
  - Implement visual indicators (↑/↓ arrows) to show current sort column and direction
  - Default sort: Date (descending - newest first)
  - Style table with WordPress admin CSS classes for native look
  - Make System Name column clickable links to full Geekbench benchmark pages

- [ ] **Add user controls and feedback**
  - Create search input field with submit button to trigger new scrapes
  - Pre-populate search field with "iPhone18" as default value
  - Add "Refresh Results" button to bypass cache
  - Implement loading states, error messages, and success notifications

---

## Phase 4: Frontend Shortcode Implementation

**Goal**: Create a shortcode to display Geekbench results on the frontend (posts/pages).

### High-Level Checklist

- [ ] **Register shortcode**
  - Create shortcode: `[geekbench_results]`
  - Register in main plugin file or dedicated Shortcode class
  - Add shortcode to WordPress via `add_shortcode()` hook

- [ ] **Shortcode attributes**
  - `query` - Search term (default: "iPhone18")
  - `limit` - Number of results to display (default: 25, max: 30)
  - `columns` - Which columns to show (default: all)
  - Example: `[geekbench_results query="iPhone18" limit="10"]`

- [ ] **Frontend table rendering**
  - Reuse same scraper logic from admin interface
  - Use same caching mechanism (15-minute transient)
  - Render HTML table with frontend-friendly styling
  - Include responsive CSS for mobile devices
  - Add table wrapper with class for custom styling hooks

- [ ] **Frontend sorting functionality**
  - Include same JavaScript sorting functionality as admin
  - Enqueue frontend-specific CSS/JS only when shortcode is present
  - Use `wp_enqueue_script()` conditionally based on shortcode detection
  - Maintain same three-state toggle (ascending/descending/default)

- [ ] **Frontend-specific features**
  - Optional search form on frontend (attribute: `show_search="true"`)
  - Optional refresh button (attribute: `show_refresh="true"`)
  - Customizable table classes (attribute: `table_class="custom-class"`)
  - AJAX loading without page refresh
  - Loading spinner/skeleton during data fetch

- [ ] **Security considerations**
  - Sanitize all shortcode attributes
  - Use WordPress nonces for AJAX requests from frontend
  - Rate limiting to prevent abuse (max requests per IP/session)
  - Cache results per unique query to reduce server load

### Shortcode Usage Examples

```
<!-- Basic usage - default iPhone18 search -->
[geekbench_results]

<!-- Custom search query -->
[geekbench_results query="MacBook Pro"]

<!-- Limited results with search form -->
[geekbench_results query="iPhone18" limit="10" show_search="true"]

<!-- Full featured with custom styling -->
[geekbench_results query="iPhone18" show_search="true" show_refresh="true" table_class="my-custom-table"]

<!-- Minimal columns -->
[geekbench_results columns="system,single_core,multi_core"]
```

---

## Additional Considerations

- **Security**: Sanitize all inputs, validate URLs, use WordPress nonces for AJAX requests
- **Error Handling**: Log errors with WP_DEBUG, display user-friendly messages in admin
- **Rate Limiting**: Add delays between requests to respect Geekbench's servers
- **Extensibility**: Use WordPress hooks/filters for future feature additions
- **Data Persistence**: NO database storage - all data is ephemeral (cache-only, 15-min TTL)
- **Sort State**: Sort preferences are NOT saved - resets to default on page reload
- **Documentation**: PHPDoc comments required for all classes, methods, and properties
- **Testing**: Unit tests for critical modules to prevent regression errors

---

## Phase 5: Documentation & Testing

**Goal**: Ensure code quality, maintainability, and prevent regression errors.

### High-Level Checklist

- [ ] **PHPDoc Documentation Standards**
  - Add PHPDoc blocks to ALL classes with `@package`, `@since`, `@version`
  - Document ALL public/protected methods with `@param`, `@return`, `@throws`
  - Document ALL class properties with `@var` type hints
  - Include usage examples in class-level PHPDoc blocks
  - Follow WordPress PHPDoc standards and PSR-5 recommendations

- [ ] **Unit Testing Setup**
  - Install PHPUnit via Composer (`composer require --dev phpunit/phpunit`)
  - Create `/tests` directory with PHPUnit configuration (`phpunit.xml`)
  - Set up WordPress test environment using WP-CLI scaffold
  - Create base test case class extending `WP_UnitTestCase`

- [ ] **Critical Module Tests**
  - **Scraper Tests** (`tests/ScraperTest.php`):
    * Test HTTP request handling with mocked responses
    * Test HTML parsing with sample Geekbench HTML
    * Test data extraction accuracy (all 6 fields)
    * Test error handling (network failures, malformed HTML)
    * Test cache key generation
  - **Shortcode Tests** (`tests/ShortcodeTest.php`):
    * Test shortcode registration
    * Test attribute parsing and defaults
    * Test output rendering with mocked data
    * Test sanitization of user inputs
  - **Admin Tests** (`tests/AdminTest.php`):
    * Test admin menu registration
    * Test AJAX endpoint responses
    * Test nonce validation
    * Test capability checks

- [ ] **Integration Tests**
  - Test full scrape-to-display workflow
  - Test caching behavior (set, get, expire, refresh)
  - Test frontend shortcode rendering in post context
  - Test admin interface with real WordPress environment

- [ ] **Regression Prevention**
  - Create test fixtures with sample Geekbench HTML snapshots
  - Test against multiple HTML structure variations
  - Add tests when bugs are discovered (test-driven bug fixes)
  - Run tests before each commit/deployment

- [ ] **Running Tests**
  - Install WordPress test suite: `bash bin/install-wp-tests.sh wordpress_test root '' localhost latest`
  - Run all tests: `composer test` or `vendor/bin/phpunit`
  - Run specific test: `vendor/bin/phpunit tests/ScraperTest.php`
  - Generate code coverage: `vendor/bin/phpunit --coverage-html coverage/`
  - Set up pre-commit hook to run tests automatically

### PHPDoc Examples

**Class Documentation:**
```php
/**
 * Geekbench Browser Scraper
 *
 * Handles fetching and parsing Geekbench search results using Guzzle HTTP client
 * and Symfony DomCrawler for HTML parsing.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 * @version 1.0.0
 *
 * @example
 * $scraper = new Scraper();
 * $results = $scraper->fetch('iPhone18');
 */
class Scraper {
    /**
     * Guzzle HTTP client instance
     *
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * Cache expiration time in seconds
     *
     * @var int
     */
    private $cache_ttl = 900; // 15 minutes
}
```

**Method Documentation:**
```php
/**
 * Fetch and parse Geekbench search results
 *
 * Retrieves search results from Geekbench Browser, parses the HTML,
 * and returns structured data. Results are cached for 15 minutes.
 *
 * @since 1.0.0
 *
 * @param string $query Search term (e.g., 'iPhone18', 'MacBook Pro')
 * @param bool $bypass_cache Whether to bypass cache and fetch fresh data
 * @return array Array of benchmark results with keys: system_name, upload_date,
 *               platform, single_core_score, multi_core_score, benchmark_url
 * @throws \GuzzleHttp\Exception\GuzzleException If HTTP request fails
 * @throws \RuntimeException If HTML parsing fails
 *
 * @example
 * try {
 *     $results = $scraper->fetch('iPhone18');
 *     foreach ($results as $result) {
 *         echo $result['system_name'] . ': ' . $result['single_core_score'];
 *     }
 * } catch (\Exception $e) {
 *     error_log('Scraper error: ' . $e->getMessage());
 * }
 */
public function fetch($query, $bypass_cache = false) {
    // Implementation
}
```

### Test Examples

**Scraper Unit Test:**
```php
/**
 * Test Scraper class functionality
 *
 * @package GeekbenchScraper\Tests
 * @since 1.0.0
 */
class ScraperTest extends WP_UnitTestCase {

    /**
     * Test HTML parsing extracts correct data
     *
     * @test
     */
    public function test_parse_html_extracts_all_fields() {
        $html = file_get_contents(__DIR__ . '/fixtures/iphone18-search.html');
        $scraper = new Scraper();
        $results = $scraper->parse_html($html);

        $this->assertIsArray($results);
        $this->assertCount(25, $results);
        $this->assertArrayHasKey('system_name', $results[0]);
        $this->assertArrayHasKey('single_core_score', $results[0]);
        $this->assertEquals('iPhone18,2', $results[0]['system_name']);
        $this->assertEquals(3831, $results[0]['single_core_score']);
    }

    /**
     * Test cache key generation is consistent
     *
     * @test
     */
    public function test_cache_key_generation() {
        $scraper = new Scraper();
        $key1 = $scraper->get_cache_key('iPhone18');
        $key2 = $scraper->get_cache_key('iPhone18');
        $key3 = $scraper->get_cache_key('MacBook Pro');

        $this->assertEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);
    }

    /**
     * Test error handling for malformed HTML
     *
     * @test
     */
    public function test_handles_malformed_html_gracefully() {
        $scraper = new Scraper();
        $results = $scraper->parse_html('<html><body>Invalid</body></html>');

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
```

**Shortcode Unit Test:**
```php
/**
 * Test Shortcode class functionality
 *
 * @package GeekbenchScraper\Tests
 * @since 1.0.0
 */
class ShortcodeTest extends WP_UnitTestCase {

    /**
     * Test shortcode is registered
     *
     * @test
     */
    public function test_shortcode_is_registered() {
        $this->assertTrue(shortcode_exists('geekbench_results'));
    }

    /**
     * Test default attributes are applied
     *
     * @test
     */
    public function test_default_attributes() {
        $output = do_shortcode('[geekbench_results]');

        $this->assertStringContainsString('iPhone18', $output);
        $this->assertStringContainsString('geekbench-table', $output);
    }

    /**
     * Test custom attributes override defaults
     *
     * @test
     */
    public function test_custom_attributes() {
        $output = do_shortcode('[geekbench_results query="MacBook" limit="10"]');

        $this->assertStringContainsString('MacBook', $output);
    }

    /**
     * Test XSS protection in attributes
     *
     * @test
     */
    public function test_sanitizes_malicious_input() {
        $output = do_shortcode('[geekbench_results query="<script>alert(1)</script>"]');

        $this->assertStringNotContainsString('<script>', $output);
    }
}
```

### Testing Best Practices

**Test Coverage Goals:**
- Minimum 80% code coverage for critical modules (Scraper, Shortcode)
- 100% coverage for security-critical functions (sanitization, validation)
- All public methods must have at least one test

**Test Naming Convention:**
- Use descriptive names: `test_parse_html_extracts_all_fields()`
- Follow pattern: `test_[method]_[scenario]_[expected_result]()`
- Use `@test` annotation for clarity

**Mock Data Strategy:**
- Store real Geekbench HTML snapshots in `/tests/fixtures/`
- Create multiple fixtures for different scenarios:
  - `iphone18-search.html` - Standard results
  - `empty-results.html` - No matches found
  - `malformed.html` - Invalid HTML structure
  - `single-result.html` - Edge case with one result
- Update fixtures when Geekbench changes HTML structure

**Continuous Testing:**
- Add `composer test` script to `composer.json`:
  ```json
  "scripts": {
      "test": "phpunit",
      "test:coverage": "phpunit --coverage-html coverage/"
  }
  ```
- Run tests locally before committing
- Consider GitHub Actions for automated testing on push

**Test Data Assertions:**
- Always assert data types: `assertIsArray()`, `assertIsInt()`, `assertIsString()`
- Verify array structure: `assertArrayHasKey()`, `assertCount()`
- Check sanitization: `assertStringNotContainsString('<script>')`
- Validate URLs: `assertStringStartsWith('https://')`

---

## Technical Implementation Notes

### DomCrawler Parsing Strategy

```php
// Pseudo-code for parsing logic
$crawler = new Crawler($html);

$results = $crawler->filter('div.col-12.list-col')->each(function (Crawler $node) {
    return [
        'system_name' => $node->filter('.col-12.col-lg-4 a')->text(),
        'benchmark_url' => $node->filter('.col-12.col-lg-4 a')->attr('href'),
        'processor_info' => trim($node->filter('.list-col-model')->text()),
        'upload_date' => trim($node->filter('.col-6.col-md-3.col-lg-2')->eq(1)->filter('.list-col-text')->text()),
        'platform' => trim($node->filter('.col-6.col-md-3.col-lg-2')->eq(2)->filter('.list-col-text')->text()),
        'single_core_score' => (int) trim($node->filter('.list-col-text-score')->eq(0)->text()),
        'multi_core_score' => (int) trim($node->filter('.list-col-text-score')->eq(1)->text()),
    ];
});
```

### Data Normalization

- **Date Format**: Convert "Oct 06, 2025" to `Y-m-d` format for sorting
- **Processor Info**: Split multi-line text into separate fields (type, frequency, cores)
- **Scores**: Cast to integers for proper numerical sorting
- **Benchmark URL**: Prepend base URL `https://browser.geekbench.com` to relative paths

### Error Scenarios to Handle

1. **Network Failures**: Timeout, DNS errors, connection refused
2. **HTTP Errors**: 404, 500, rate limiting (429)
3. **Parsing Failures**: Missing elements, changed DOM structure
4. **Empty Results**: No matches found for search query
5. **Malformed Data**: Non-numeric scores, invalid dates

### Performance Optimization

- **Lazy Loading**: Only fetch data when admin page is accessed
- **Pagination**: If Geekbench returns pagination, handle multiple pages (future enhancement)
- **Compression**: Accept gzip/deflate encoding in Guzzle requests
- **Connection Reuse**: Use persistent HTTP connections for multiple requests

### Table Sorting Implementation

**Client-Side JavaScript Sorting:**
- All column headers are clickable for sorting
- First click: Sort ascending (↑)
- Second click: Sort descending (↓)
- Third click: Reset to default sort (Date descending)
- Visual indicators show active sort column and direction

**Sortable Columns:**
1. **System Name** - Alphabetical (A-Z / Z-A)
2. **Upload Date** - Chronological (Oldest/Newest)
3. **Platform** - Alphabetical (A-Z / Z-A)
4. **Single-Core Score** - Numerical (Low-High / High-Low)
5. **Multi-Core Score** - Numerical (Low-High / High-Low)

**Sort State:**
- NOT persisted to database or user preferences
- Resets to default (Date descending) on page reload
- Purely client-side manipulation of DOM table rows

**Implementation Approach:**
- Use vanilla JavaScript or lightweight library (e.g., Tablesort.js)
- Store data attributes on table rows for proper type-aware sorting
- Example: `<tr data-date="2025-10-06" data-single-score="3831">`

### Composer Configuration Example

**composer.json:**
```json
{
    "name": "geekbench-scraper/wordpress-plugin",
    "description": "WordPress plugin for scraping and displaying Geekbench browser results",
    "type": "wordpress-plugin",
    "license": "GPL-2.0-or-later",
    "require": {
        "php": ">=7.4",
        "guzzlehttp/guzzle": "^7.0",
        "symfony/dom-crawler": "^6.0",
        "symfony/css-selector": "^6.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0",
        "yoast/phpunit-polyfills": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "GeekbenchScraper\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "GeekbenchScraper\\Tests\\": "tests/"
        }
    }
}
```

### PHPUnit Configuration Example

**phpunit.xml:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="tests/bootstrap.php"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
    stopOnFailure="false">
    <testsuites>
        <testsuite name="Geekbench Scraper Test Suite">
            <directory>./tests</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">./src</directory>
        </whitelist>
    </filter>
</phpunit>
```

**tests/bootstrap.php:**
```php
<?php
/**
 * PHPUnit bootstrap file
 *
 * @package GeekbenchScraper\Tests
 */

// Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// WordPress test environment
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
    require dirname(__DIR__) . '/geekbench-scraper.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

require $_tests_dir . '/includes/bootstrap.php';
```

### Shortcode Implementation Strategy

**Class Structure:**
```php
// src/Shortcode.php
namespace GeekbenchScraper;

/**
 * Shortcode handler for frontend display
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 */
class Shortcode {
    /**
     * Scraper instance
     *
     * @var Scraper
     */
    private $scraper;

    /**
     * Constructor
     *
     * @param Scraper $scraper Scraper instance
     */
    public function __construct(Scraper $scraper) {
        $this->scraper = $scraper;
        add_shortcode('geekbench_results', [$this, 'render']);
    }

    /**
     * Render shortcode output
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render($atts) {
        // Parse attributes with defaults
        $atts = shortcode_atts([
            'query' => 'iPhone18',
            'limit' => 25,
            'show_search' => false,
            'show_refresh' => false,
            'table_class' => 'geekbench-table',
            'columns' => 'all'
        ], $atts);

        // Fetch data using scraper
        // Render template
        // Enqueue scripts/styles
    }
}
```

**Asset Enqueuing:**
- Only enqueue CSS/JS when shortcode is detected on page
- Use `has_shortcode()` in `wp_enqueue_scripts` hook
- Share same sorting JavaScript between admin and frontend
- Separate CSS files for admin vs frontend styling

**Template Rendering:**
- Create `/templates/frontend-table.php` for shortcode output
- Create `/templates/admin-table.php` for admin page output
- Share common table structure, different wrapper styling
- Use WordPress template loading functions for extensibility

---

## Project File Structure

```
guzzle-wp/
├── geekbench-scraper.php          # Main plugin file with header
├── composer.json                   # Composer dependencies & autoloading
├── phpunit.xml                     # PHPUnit configuration
├── README.md                       # Installation & usage docs
├── .gitignore                      # Ignore vendor/, IDE files
│
├── src/                            # PSR-4 source code
│   ├── Plugin.php                  # Main plugin class (singleton)
│   ├── Scraper.php                 # Guzzle + DomCrawler scraper
│   ├── Admin.php                   # Admin interface handler
│   └── Shortcode.php               # Frontend shortcode handler
│
├── assets/                         # Frontend assets
│   ├── css/
│   │   ├── admin.css               # Admin page styles
│   │   └── frontend.css            # Shortcode styles
│   └── js/
│       └── table-sort.js           # Client-side sorting (shared)
│
├── templates/                      # HTML templates
│   ├── admin-table.php             # Admin results table
│   └── frontend-table.php          # Shortcode results table
│
├── tests/                          # PHPUnit tests
│   ├── bootstrap.php               # Test environment setup
│   ├── ScraperTest.php             # Scraper unit tests
│   ├── ShortcodeTest.php           # Shortcode unit tests
│   ├── AdminTest.php               # Admin interface tests
│   └── fixtures/                   # Test data
│       └── iphone18-search.html    # Sample Geekbench HTML
│
└── vendor/                         # Composer dependencies (gitignored)
    ├── guzzlehttp/
    ├── symfony/
    └── autoload.php
```

---

## Default Search Configuration

**Default Query**: `iPhone18`
- **Device**: iPhone 17 models (all variants)
- **Chip**: A19 / ARM 4257 MHz
- **Geekbench Code**: "iPhone18" (internal model identifier)
- **Variants Included**:
  - iPhone18,1 (iPhone 17)
  - iPhone18,2 (iPhone 17 Plus)
  - iPhone18,3 (iPhone 17 Pro)
  - iPhone18,4 (iPhone 17 Pro Max)

**Why "iPhone18"?**
- Geekbench uses internal model identifiers, not marketing names
- iPhone 17 = iPhone18,x in Geekbench database
- Searching "iPhone18" returns all iPhone 17 benchmark results

---

## Version History

### v1.0.0 (Planned)
- Initial plugin structure with PSR-4 autoloading
- Basic Geekbench search scraper with DomCrawler
- Admin interface with sortable results table
- Frontend shortcode `[geekbench_results]` for public display
- WordPress transient caching (15-minute TTL)
- Default search: iPhone18 (iPhone 17 models with A19 chip)
- Client-side table sorting (ascending/descending toggle)
- No database storage - ephemeral data only
- **PHPDoc comments on all classes and methods**
- **PHPUnit test suite for critical modules**
- **Test fixtures for regression prevention**
- **Composer dependency management**