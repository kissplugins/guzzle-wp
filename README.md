# Guzzle WP - Example WordPress Plugin

> **A demonstration project showcasing how to use Guzzle HTTP Client and Symfony DomCrawler in WordPress**

This is an **example/demo plugin** that demonstrates best practices for integrating modern PHP libraries (Guzzle and DomCrawler) into WordPress. It uses Geekbench benchmark scraping as a practical, real-world example.

## 🎯 Purpose

This project serves as a **reference implementation** for developers who want to:

- ✅ Use **Guzzle HTTP Client** in WordPress plugins
- ✅ Integrate **Symfony DomCrawler** for HTML parsing
- ✅ Implement **Composer dependency management** in WordPress
- ✅ Build **modern, secure WordPress plugins** with external libraries
- ✅ Follow **WordPress coding standards** and best practices
- ✅ Create **sortable, interactive tables** with vanilla JavaScript
- ✅ Implement **server-side throttling** and reCAPTCHA integration
- ✅ Use **WordPress transients** for caching
- ✅ Build **AJAX-powered interfaces** without page reloads

## 📚 What You'll Learn

### Core Technologies
- **Guzzle HTTP Client** - Making HTTP requests in WordPress
- **Symfony DomCrawler** - Parsing and extracting data from HTML
- **Composer** - Managing PHP dependencies in WordPress
- **PSR-4 Autoloading** - Modern PHP class organization

### WordPress Integration
- Admin interfaces with custom menu pages
- Frontend shortcodes with attributes
- AJAX handlers (both admin and public)
- WordPress transients for caching
- Settings API and options management
- Nonce verification and security
- Capability checks and permissions

### Advanced Features
- Client-side table sorting (vanilla JavaScript)
- Server-side IP-based throttling
- Google reCAPTCHA v2 integration
- Automated self-test suite
- System name translations
- Responsive design patterns

## 🚀 Demo Features

The plugin demonstrates these capabilities through a Geekbench benchmark scraper:

- 🔍 **Search & Scrape** - Fetch benchmark results from Geekbench Browser
- 📊 **Sortable Tables** - Click column headers to sort (ascending/descending/original)
- 💾 **Smart Caching** - 15-minute WordPress transient cache
- 🎨 **Admin Interface** - Full-featured admin page under Tools menu
- 📱 **Frontend Shortcode** - `[geekbench_results]` with customizable attributes
- ⚡ **AJAX Loading** - No page refresh needed for searches
- 🔒 **Security** - Server-side throttling, reCAPTCHA, nonces, sanitization
- 🧪 **Self-Test Suite** - 8 automated diagnostic tests

## 📦 Installation

### Requirements

- **WordPress** 5.8 or higher
- **PHP** 7.4 or higher
- **Composer** (for dependency management)

### Quick Start

1. **Clone the repository** to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/kissplugins/guzzle-wp.git
   cd guzzle-wp
   ```

2. **Install dependencies** using Composer:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

   > **Note**: The plugin includes auto-installation of Composer dependencies on activation, but manual installation is recommended for development.

3. **Activate the plugin** in WordPress admin:
   - Go to **Plugins → Installed Plugins**
   - Find "Geekbench Browser Scraper"
   - Click **"Activate"**

4. **Explore the demo**:
   - Go to **Tools → Geekbench Scraper** (admin interface)
   - Go to **Tools → Geekbench Settings** (settings & self-test)
   - Add `[geekbench_results]` shortcode to any page (frontend demo)

## 💡 Usage Examples

### Admin Interface Demo

1. Go to **Tools → Geekbench Scraper** in WordPress admin
2. Enter a search query (default: "iPhone18")
3. Click "Search" to fetch and display results
4. Click "Refresh" to bypass cache and get fresh data
5. Click any column header to sort results (ascending → descending → original)

### Frontend Shortcode Demo

Use the `[geekbench_results]` shortcode in any post or page:

```
<!-- Basic usage - default iPhone18 search -->
[geekbench_results]

<!-- Custom search query -->
[geekbench_results query="MacBook Pro"]

<!-- With search form and reCAPTCHA throttling -->
[geekbench_results query="iPhone18" show_search="true"]

<!-- Limited results with custom styling -->
[geekbench_results query="iPhone18" limit="10" table_class="my-custom-table"]
```

### Shortcode Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `query` | `iPhone18` | Search term for Geekbench |
| `limit` | `25` | Number of results to display (max: 30) |
| `show_search` | `false` | Show search form on frontend |
| `table_class` | `geekbench-table` | Custom CSS class for table |

### Settings & Self-Test

Go to **Tools → Geekbench Settings** to:
- Configure Google reCAPTCHA v2 (Site Key & Secret Key)
- Customize system name translations (e.g., "iPhone18,1" → "iPhone 17")
- Set default search query
- Customize search hint text
- Run automated self-test suite (8 diagnostic tests)

## 🔧 Technical Implementation

### Guzzle HTTP Client

The plugin demonstrates how to use Guzzle for HTTP requests in WordPress:

```php
use GuzzleHttp\Client;

$client = new Client([
    'timeout' => 30,
    'verify' => true,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (compatible; WordPress/Guzzle)',
    ],
]);

$response = $client->get('https://browser.geekbench.com/search?q=' . urlencode($query));
$html = (string) $response->getBody();
```

### Symfony DomCrawler

The plugin shows how to parse HTML with DomCrawler:

```php
use Symfony\Component\DomCrawler\Crawler;

$crawler = new Crawler($html);

$results = $crawler->filter('table.table-hover tbody tr')->each(function (Crawler $row) {
    return [
        'system_name' => $row->filter('td')->eq(0)->text(),
        'processor_info' => $row->filter('td')->eq(1)->text(),
        'single_core_score' => $row->filter('td')->eq(3)->text(),
        'multi_core_score' => $row->filter('td')->eq(4)->text(),
    ];
});
```

### WordPress Transients Caching

Demonstrates efficient caching with WordPress transients:

```php
// Check cache first
$cache_key = 'geekbench_results_' . md5($query);
$cached = get_transient($cache_key);

if ($cached !== false) {
    return $cached;
}

// Fetch fresh data
$results = $this->scraper->fetch($query);

// Cache for 15 minutes
set_transient($cache_key, $results, 15 * MINUTE_IN_SECONDS);
```

### Server-Side Throttling

Shows IP-based throttling with WordPress transients:

```php
// Track searches per IP
$ip = $this->get_user_ip();
$throttle_key = 'geekbench_throttle_' . md5($ip);
$count = (int) get_transient($throttle_key);

if ($count >= 5) {
    // Require reCAPTCHA after 5 searches
    return ['requires_captcha' => true];
}

// Increment counter
set_transient($throttle_key, $count + 1, 5 * MINUTE_IN_SECONDS);
```

## 🏗️ Architecture & Code Organization

### File Structure

```
guzzle-wp/
├── geekbench-scraper.php    # Main plugin file (bootstrap)
├── composer.json             # Composer dependencies (Guzzle, DomCrawler)
├── LICENSE                   # MIT License
├── README.md                 # This file
├── CHANGELOG.md              # Version history
│
├── src/                      # PSR-4 autoloaded source code
│   ├── Plugin.php            # Main plugin class (initialization)
│   ├── Scraper.php           # Guzzle + DomCrawler scraper logic
│   ├── Admin.php             # Admin interface & AJAX handlers
│   └── Shortcode.php         # Frontend shortcode & throttling
│
├── templates/                # Reusable HTML templates
│   ├── admin-page.php        # Admin interface template
│   ├── settings-page.php     # Settings page with self-test
│   ├── results-table.php     # Shared sortable table template
│   └── frontend-shortcode.php # Frontend shortcode template
│
├── assets/                   # Frontend assets
│   ├── css/
│   │   ├── admin.css         # Admin interface styles
│   │   └── frontend.css      # Frontend shortcode styles
│   └── js/
│       └── admin.js          # Admin interface JavaScript
│
├── docs/                     # Documentation
│   ├── ROADMAP.md            # Future development plans
│   ├── FEATURE-*.md          # Feature documentation
│   └── *.md                  # Technical guides
│
└── vendor/                   # Composer dependencies (auto-generated)
    ├── guzzlehttp/           # Guzzle HTTP Client
    └── symfony/              # Symfony DomCrawler
```

### Key Design Patterns

#### PSR-4 Autoloading
```php
// composer.json
{
    "autoload": {
        "psr-4": {
            "GeekbenchScraper\\": "src/"
        }
    }
}
```

#### Dependency Injection
```php
class Plugin {
    private $scraper;
    private $admin;
    private $shortcode;

    public function __construct() {
        $this->scraper = new Scraper();
        $this->admin = new Admin($this->scraper);
        $this->shortcode = new Shortcode($this->scraper);
    }
}
```

#### Template Separation
- Logic in PHP classes (`src/`)
- Presentation in templates (`templates/`)
- Reusable components (e.g., `results-table.php` used by both admin and frontend)

### Development Setup

```bash
# Clone repository
git clone https://github.com/kissplugins/guzzle-wp.git
cd guzzle-wp

# Install all dependencies (including dev)
composer install

# Run code quality checks
composer phpcs

# Run automated tests (if available)
composer test
```

## 📋 Feature Highlights

### ✅ Implemented Features

- **Guzzle HTTP Client Integration** - Modern HTTP requests with timeout, SSL verification, custom headers
- **Symfony DomCrawler Integration** - Powerful HTML parsing and data extraction
- **Composer Dependency Management** - PSR-4 autoloading, optimized for production
- **Admin Interface** - Full-featured admin page under Tools menu
- **Frontend Shortcode** - `[geekbench_results]` with customizable attributes
- **AJAX-Powered** - No page reloads for searches and refreshes
- **WordPress Transients Caching** - 15-minute cache with manual refresh option
- **Sortable Tables** - Client-side sorting (ascending/descending/original) with visual indicators
- **Server-Side Throttling** - IP-based rate limiting (5 searches per 5 minutes)
- **Google reCAPTCHA v2** - Security integration after throttle limit
- **System Name Translations** - Map internal IDs to marketing names (e.g., "iPhone18,1" → "iPhone 17")
- **Settings Page** - Configure reCAPTCHA, translations, default query, search hints
- **Self-Test Suite** - 8 automated diagnostic tests for system validation
- **Security Best Practices** - Nonces, capability checks, input sanitization, output escaping
- **Responsive Design** - Mobile-friendly tables and forms
- **Average Calculations** - Automatic average scores in table footer
- **Critical Code Safeguards** - Warnings and self-tests to prevent accidental refactoring

### 📖 Documentation

- **CHANGELOG.md** - Complete version history
- **ROADMAP.md** - Future development plans
- **docs/FEATURE-*.md** - Detailed feature documentation
- **Inline PHPDoc** - Comprehensive code documentation

## 🔄 Version History

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

**Current Version**: 1.3.1

**Recent Updates**:
- v1.3.1 (2025-10-08) - Table sorting fixes and enhanced self-tests
- v1.3.0 (2025-10-08) - Server-side throttling with IP tracking
- v1.2.0 (2025-10-06) - Frontend shortcode with reCAPTCHA
- v1.1.1 (2025-10-06) - Self-test suite and average scores
- v1.1.0 (2025-10-06) - System name translations and settings
- v1.0.0 (2025-10-06) - Initial release

## 📄 License

**MIT License**

Copyright (c) 2025 Guzzle WP Contributors

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

---

**Dependencies**:
- [Guzzle HTTP Client](https://github.com/guzzle/guzzle) - MIT License
- [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html) - MIT License

## 🙏 Credits

### Built With

- **[Guzzle HTTP Client](https://github.com/guzzle/guzzle)** - PHP HTTP client that makes it easy to send HTTP requests
- **[Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html)** - Powerful component for DOM navigation and HTML parsing
- **[WordPress](https://wordpress.org/)** - The world's most popular CMS

### Data Source

- **[Geekbench Browser](https://browser.geekbench.com/)** - Public benchmark database used for demonstration purposes

### Inspiration

This project was created to demonstrate how modern PHP libraries can be integrated into WordPress plugins while following WordPress coding standards and best practices.

## 🤝 Contributing

This is an **example/demo project**, but contributions are welcome!

### How to Contribute

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Commit your changes** (`git commit -m 'Add some amazing feature'`)
4. **Push to the branch** (`git push origin feature/amazing-feature`)
5. **Open a Pull Request**

### Development Guidelines

- Follow **WordPress Coding Standards** (PHPCS configuration included)
- Add **PHPDoc comments** to all classes and methods
- Update **CHANGELOG.md** for any changes
- Test on **WordPress 5.8+** and **PHP 7.4+**
- Ensure **Composer dependencies** are properly managed

## 📞 Support & Questions

### For Issues or Questions

- **GitHub Issues**: [https://github.com/kissplugins/guzzle-wp/issues](https://github.com/kissplugins/guzzle-wp/issues)
- **Discussions**: [https://github.com/kissplugins/guzzle-wp/discussions](https://github.com/kissplugins/guzzle-wp/discussions)

### For Learning & Reference

This project is designed as a **learning resource**. Feel free to:
- ✅ Fork and modify for your own projects
- ✅ Use code snippets in your plugins
- ✅ Study the implementation patterns
- ✅ Ask questions via GitHub Discussions

---

## 🎓 Learning Resources

### Related Documentation

- [Guzzle Documentation](http://docs.guzzlephp.org/)
- [Symfony DomCrawler Documentation](https://symfony.com/doc/current/components/dom_crawler.html)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [Composer Documentation](https://getcomposer.org/doc/)

### Key Concepts Demonstrated

1. **HTTP Requests in WordPress** - Using Guzzle instead of `wp_remote_get()`
2. **HTML Parsing** - DomCrawler vs. regex or simple HTML parsers
3. **Dependency Management** - Composer in WordPress plugins
4. **Modern PHP** - Namespaces, PSR-4, type hints, dependency injection
5. **WordPress APIs** - Transients, Options, AJAX, Shortcodes, Settings
6. **Security** - Nonces, capability checks, sanitization, escaping, throttling
7. **User Experience** - AJAX, caching, sorting, responsive design

---

**Made with ❤️ as a learning resource for the WordPress community**

**Repository**: [https://github.com/kissplugins/guzzle-wp](https://github.com/kissplugins/guzzle-wp)

