# Guzzle Scraper - WordPress Plugin

A WordPress plugin using Guzzle HTTP Client and Symfony DomCrawler

for scraping and displaying Geekbench browser benchmark results with sortable tables.

## Features

- 🔍 **Search Geekbench Results** - Search for any device (default: iPhone18 / iPhone 17 models)
- 📊 **Sortable Tables** - Click column headers to sort ascending/descending
- 💾 **Smart Caching** - 15-minute cache to reduce server load
- 🎨 **Admin Interface** - Full-featured admin page under Tools menu
- 📱 **Frontend Shortcode** - Display results on any post/page
- ⚡ **AJAX Loading** - No page refresh needed
- 🔒 **Secure** - Nonces, sanitization, and capability checks

## Installation

### Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Composer (for dependency management)

### Steps

1. **Clone or download** this plugin to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone [your-repo-url] geekbench-scraper
   ```

2. **Install dependencies** using Composer:
   ```bash
   cd geekbench-scraper
   composer install --no-dev --optimize-autoloader
   ```

3. **Activate the plugin** in WordPress admin:
   - Go to Plugins → Installed Plugins
   - Find "Geekbench Browser Scraper"
   - Click "Activate"

## Usage

### Admin Interface

1. Go to **Tools → Geekbench Scraper** in WordPress admin
2. Enter a search query (default: "iPhone18")
3. Click "Search" to fetch results
4. Click "Refresh" to bypass cache and get fresh data
5. Click any column header to sort results

### Frontend Shortcode

Use the `[geekbench_results]` shortcode in any post or page:

```
<!-- Basic usage - default iPhone18 search -->
[geekbench_results]

<!-- Custom search query -->
[geekbench_results query="MacBook Pro"]

<!-- Limited results with search form -->
[geekbench_results query="iPhone18" limit="10" show_search="true"]

<!-- Full featured with custom styling -->
[geekbench_results query="iPhone18" show_search="true" show_refresh="true" table_class="my-custom-table"]
```

### Shortcode Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `query` | `iPhone18` | Search term for Geekbench |
| `limit` | `25` | Number of results to display (max: 30) |
| `show_search` | `false` | Show search form on frontend |
| `show_refresh` | `false` | Show refresh button on frontend |
| `table_class` | `geekbench-table` | Custom CSS class for table |
| `columns` | `all` | Which columns to display |

## Default Search: iPhone18

The plugin defaults to searching for "iPhone18" which represents:
- **iPhone 17** models (all variants)
- **Chip**: A19 / ARM 4257 MHz
- **Variants**:
  - iPhone18,1 (iPhone 17)
  - iPhone18,2 (iPhone 17 Plus)
  - iPhone18,3 (iPhone 17 Pro)
  - iPhone18,4 (iPhone 17 Pro Max)

**Why "iPhone18"?** Geekbench uses internal model identifiers, not marketing names. iPhone 17 = iPhone18,x in the Geekbench database.

## Data Structure

Each benchmark result includes:

- **System Name** - Device identifier (e.g., "iPhone18,2")
- **Processor Info** - CPU details (type, frequency, cores)
- **Upload Date** - When the benchmark was submitted
- **Platform** - Operating system (iOS, Android, macOS, etc.)
- **Single-Core Score** - Single-threaded performance score
- **Multi-Core Score** - Multi-threaded performance score
- **Benchmark URL** - Link to full Geekbench result page

## Caching

- Results are cached for **15 minutes** using WordPress transients
- Cache is automatically cleared after expiration
- Use "Refresh" button to bypass cache and fetch fresh data
- **No database storage** - all data is ephemeral

## Development

### File Structure

```
geekbench-scraper/
├── geekbench-scraper.php    # Main plugin file
├── composer.json             # Composer dependencies
├── src/                      # PSR-4 source code
│   ├── Plugin.php            # Main plugin class
│   ├── Scraper.php           # Guzzle + DomCrawler scraper
│   ├── Admin.php             # Admin interface
│   └── Shortcode.php         # Frontend shortcode
├── assets/                   # CSS/JS assets
│   ├── css/
│   └── js/
├── templates/                # HTML templates
└── tests/                    # PHPUnit tests
```

### Running Tests

```bash
# Install dev dependencies
composer install

# Run tests
composer test

# Generate coverage report
composer test:coverage
```

## Changelog

### v1.0.0 (2025-10-06)
- Initial release
- Geekbench search scraper with DomCrawler
- Admin interface with sortable tables
- Frontend shortcode `[geekbench_results]`
- WordPress transient caching (15-minute TTL)
- Default search: iPhone18 (iPhone 17 models)
- Client-side table sorting
- PHPDoc comments on all classes
- PHPUnit test suite

## License

MIT - See LICENSE file for details
Carried over from original Guzzle library

## Credits

- Built with [Guzzle HTTP Client](https://github.com/guzzle/guzzle)
- HTML parsing with [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html)
- Data source: [Geekbench Browser](https://browser.geekbench.com/)

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/kissplugins/guzzle-wp).

