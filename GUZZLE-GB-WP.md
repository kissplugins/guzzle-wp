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
  - Create folder structure: `/src`, `/vendor`, `/assets`, `/templates`

- [ ] **Implement PSR-4 autoloader**
  - Create `composer.json` with PSR-4 namespace mapping (e.g., `GeekbenchScraper\\`)
  - Add Guzzle and symfony/dom-crawler as Composer dependencies
  - Run `composer install` to generate autoloader and vendor directory

- [ ] **Establish plugin namespace architecture**
  - Create base classes in `/src`: `Plugin.php`, `Scraper.php`, `Admin.php`
  - Set up namespace structure following PSR-4 (matching folder hierarchy)
  - Bootstrap autoloader in main plugin file and initialize plugin class

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

---

## Phase 3: WordPress Admin Interface

**Goal**: Create an interactive admin page to display, sort, and interact with scraped results.

### High-Level Checklist

- [ ] **Build admin menu and page**
  - Register admin menu item under Tools or custom top-level menu
  - Create admin page template with search form for Geekbench queries
  - Add AJAX endpoint for fetching/refreshing scraper results

- [ ] **Implement sortable results table**
  - Display results in HTML table with columns: Date, Platform, Single-Core, Multi-Core
  - Add JavaScript for client-side table sorting (all columns)
  - Style table with WordPress admin CSS classes for native look

- [ ] **Add user controls and feedback**
  - Create search input field with submit button to trigger new scrapes
  - Add "Refresh Results" button to bypass cache
  - Implement loading states, error messages, and success notifications

---

## Additional Considerations

- **Security**: Sanitize all inputs, validate URLs, use WordPress nonces for AJAX requests
- **Error Handling**: Log errors with WP_DEBUG, display user-friendly messages in admin
- **Rate Limiting**: Add delays between requests to respect Geekbench's servers
- **Extensibility**: Use WordPress hooks/filters for future feature additions

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

---

## Version History

### v1.0.0 (Planned)
- Initial plugin structure with PSR-4 autoloading
- Basic Geekbench search scraper with DomCrawler
- Admin interface with sortable results table
- WordPress transient caching (15-minute TTL)