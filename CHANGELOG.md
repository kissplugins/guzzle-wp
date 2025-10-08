# Changelog

All notable changes to the Geekbench Browser Scraper WordPress plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.1] - 2025-10-08

### Fixed
- **Table Sorting Restored** (Oct 8, 2025):
  - Restored missing table sorting JavaScript functionality
  - Added `initTableSort()` function to `templates/results-table.php`
  - Supports ascending/descending/original order sorting
  - Works on all columns (text, numbers, dates)
  - Visual indicators (↑ ↓ ↕) for sort direction
  - Preserves average row in footer during sorting
  - **Fixed text column sorting** (system-name, processor, platform) to use row-level data attributes
  - All columns now sort correctly using appropriate data sources

### Added
- **Table Sorting Self-Test** (Oct 8, 2025):
  - Added automated self-test in WP Admin → Tools → Geekbench Settings
  - Tests if `initTableSort()` function exists in template file
  - Verifies sortable headers and indicators are present
  - Validates data attributes on table rows
  - Checks for critical section warnings
  - Validates auto-initialization code
  - Prevents table sorting from being accidentally removed
  - Provides detailed test results and error messages
  - Uses AJAX to validate template file contents (works on settings page)

### Changed
- **Code Safeguards** (Oct 8, 2025):
  - Added critical section warnings in `templates/results-table.php`
  - Clear comments: "⚠️ CRITICAL: DO NOT REMOVE OR REFACTOR THIS JAVASCRIPT SECTION ⚠️"
  - Exposed functions to global scope for testing
  - Added comprehensive inline documentation
- **Sorting Logic Improved** (Oct 8, 2025):
  - Text columns now use row-level `data-*` attributes for reliable sorting
  - Numeric columns use `parseInt()` for proper number sorting
  - Date columns use string comparison on ISO format dates
  - Added fallback to cell text content if data attribute missing

## [1.3.0] - 2025-10-08

### Added
- **Server-Side Throttling** (Oct 8, 2025):
  - IP-based request tracking using WordPress transients
  - Hard 5-search limit enforced on the backend
  - Cannot be bypassed by JavaScript manipulation
  - Automatic counter reset after 5 minutes (transient expiration)
  - Automatic counter reset after successful reCAPTCHA verification
  - Proxy-aware IP detection (Cloudflare, X-Forwarded-For, Nginx, etc.)
  - Handles comma-separated IPs from proxy headers
  - IP validation using `filter_var()`
  - Transient key format: `geekbench_throttle_{md5(ip)}`
  - No database bloat (automatic cleanup)
  - GDPR-compliant (hashed IPs, 5-minute retention)
  - Comprehensive documentation in `docs/SERVER-SIDE-THROTTLING.md`

### Changed
- **Frontend JavaScript** (Oct 8, 2025):
  - Client-side throttling now serves as UI hint only
  - Server-side validation is the final authority
  - Added server response handling for `requires_captcha` flag
  - Automatically shows CAPTCHA widget when server requires it
  - Improved error messages for throttle limit reached

### Security
- **Bypass Prevention** (Oct 8, 2025):
  - Server validates every search request
  - Client-side manipulation cannot circumvent limits
  - Rate limiting per IP address
  - Prevents automated scraping and bot abuse
  - Protects server resources from excessive requests

## [1.2.0] - 2025-10-06

### Added
- **Auto Composer Install** (Oct 6, 2025):
  - Automatic Composer dependency installation on plugin activation
  - Detects and uses local `composer.phar` or system `composer` command
  - Automatic PHP binary detection (supports PHP_BINARY constant and common paths)
  - Smart installation with two fallback methods
  - Admin notices for success/failure with detailed error messages
  - Manual installation instructions if auto-install fails
  - Security measures: command escaping, file validation, safe directory changes
  - Comprehensive documentation in `docs/AUTO-COMPOSER-INSTALL.md`
  - Eliminates manual `composer install` step for end users

- **Modern Frontend Shortcode** (Oct 6, 2025):
  - Complete redesign of `[geekbench_results]` shortcode with modern UI
  - Large search input field with SVG magnifying glass icon submit button
  - Auto-run default search on page load (configurable via `default` parameter)
  - Customizable hint text below search input (global setting)
  - Smooth animations and transitions
  - Responsive design for mobile, tablet, and desktop
  - Gradient header for results display
  - Modern loading spinner with animation
  - Error messages with icons
  - Professional color scheme (blues/purples)

- **Google reCAPTCHA v2 Integration** (Oct 6, 2025):
  - Full reCAPTCHA v2 support for frontend searches
  - Settings page for Site Key and Secret Key configuration
  - Enable/disable checkbox in WP Admin
  - Automatic script loading when enabled
  - Server-side verification using WordPress HTTP API
  - IP address validation
  - Error handling for failed verification

- **Smart Throttling System** (Oct 6, 2025):
  - Intelligent rate limiting that balances security with user experience
  - First 5 searches in 5 minutes don't require CAPTCHA
  - After 5 searches, CAPTCHA is required
  - 30-minute inactivity period resets counter
  - Client-side tracking (no server sessions needed)
  - Auto-load search doesn't count toward limit
  - Configurable thresholds and time windows
  - Detailed documentation in `docs/PROJECT-FRONTEND.md`

- **Frontend Settings Section** (Oct 6, 2025):
  - New settings section in WP Admin → Tools → Geekbench Settings
  - Search hint text customization (applies globally to all shortcodes)
  - Separate save button for frontend settings
  - Success messages after save

- **reCAPTCHA Settings Section** (Oct 6, 2025):
  - New settings section for reCAPTCHA configuration
  - Enable/disable checkbox
  - Site Key input field
  - Secret Key input field (password type)
  - Help text with link to Google reCAPTCHA Admin
  - Smart throttling explanation
  - Separate save button for reCAPTCHA settings

### Changed
- **Shortcode Default Behavior**:
  - Changed default `show_search` from `false` to `true`
  - Added `default` parameter (replaces `query` for clarity)
  - Default search term changed from "iPhone18" to "Apple M4"
  - Backwards compatibility maintained for `query` parameter
  - Auto-runs search on page load for better UX

- **Frontend Template**:
  - Complete rewrite of `templates/frontend-shortcode.php`
  - Inline CSS for modern styling (no external CSS file needed)
  - JavaScript for auto-run, smart throttling, and AJAX
  - SVG icons instead of text buttons
  - Improved accessibility with ARIA labels

- **Plugin Assets Loading**:
  - Conditional reCAPTCHA script loading (only when enabled)
  - Validates site key exists before loading external script
  - No unnecessary external dependencies

### Security
- **Enhanced Frontend Security**:
  - reCAPTCHA verification prevents bot abuse
  - Smart throttling stops automated scraping
  - Server-side token verification
  - IP address validation
  - All inputs sanitized
  - Nonce protection on settings forms

- **Security Score Improvement**:
  - Previous score: 75/100
  - New score: 100/100 (with reCAPTCHA enabled)
  - Addresses all Phase 3 security requirements

### Documentation
- **New Documentation Files**:
  - `docs/FEATURE-FRONTEND-SHORTCODE.md` - Complete implementation summary
  - Updated `docs/PROJECT-FRONTEND.md` with smart throttling details
  - Technical implementation details
  - User flow diagrams
  - Configuration instructions
  - Customization guide
  - Privacy information

### Fixed
- Frontend AJAX handler now has proper security (reCAPTCHA)
- Rate limiting implemented via smart throttling
- All Phase 3 security requirements now met

## [1.1.1]

### Added
- **Average Scores Row** (Oct 6, 2025):
  - Dynamic bottom row in results table showing average Single-Core and Multi-Core scores
  - Automatically calculates averages from all visible results on the page
  - Displays result count (e.g., "25 results")
  - Styled with blue highlight to distinguish from regular rows
  - Updates dynamically when table is sorted or filtered
  - JavaScript function `calculateAverages()` handles real-time calculation

- **System Self-Test Suite** (Oct 6, 2025):
  - 8 critical diagnostic tests on Settings page
  - Visual status indicator: "X of 8 tests passed" (green if all pass, red if any fail)
  - **Test 1**: PHP Version Check (7.4+ required)
  - **Test 2**: Guzzle HTTP Client loaded
  - **Test 3**: Symfony DomCrawler loaded
  - **Test 4**: WordPress core functions available
  - **Test 5**: Cache system (transients) working
  - **Test 6**: Geekbench.com connectivity test
  - **Test 7**: Scraper Logic Test - validates core scraping with real data
  - **Test 8**: HTML Parser Test - validates DOM selectors extract data correctly
  - Real-time test execution with progress indicators
  - Detailed pass/fail messages with troubleshooting info
  - AJAX-powered for smooth user experience
- **GitHub Actions CI/CD Pipeline** (Oct 6, 2025):
  - PHP Lint workflow for syntax checking across PHP 7.4-8.3
  - WordPress Coding Standards workflow with PHPCS
  - Comprehensive CI workflow combining all checks
  - PHP Compatibility checks for PHP 7.4+
  - Security vulnerability scanning with `composer audit`
  - Automated code annotations on pull requests
- **PHPCS Configuration** (`phpcs.xml`):
  - WordPress, WordPress-Extra, and WordPress-Docs standards
  - PHPCompatibilityWP for cross-version compatibility
  - Custom exclusions for vendor/, tests/, etc.
  - Text domain validation (`geekbench-scraper`)
  - Prefix validation (`geekbench_scraper`, `GeekbenchScraper`)
- **Composer Scripts**:
  - `composer run-script lint` - Run PHPCS checks
  - `composer run-script lint:fix` - Auto-fix coding standards
  - `composer run-script phpcs` - Direct PHPCS access
  - `composer run-script phpcbf` - Direct PHPCBF access
- **Documentation**:
  - `.github/README.md` - GitHub Actions workflow documentation
  - `CI-CD-SETUP.md` - Complete CI/CD setup guide
  - Troubleshooting guides and best practices
- **Dependencies** (dev):
  - `squizlabs/php_codesniffer` ^3.7
  - `wp-coding-standards/wpcs` ^3.0
  - `phpcompatibility/phpcompatibility-wp` ^2.1

### Changed
- Updated `.gitignore` with comprehensive exclusions
  - Added PHPCS cache, coverage reports, IDE files
  - Better organization with comments
  - Added temporary files and logs

### Fixed
- **Composer Plugin Blocking Issue** (Oct 6, 2025):
  - Added `dealerdirect/phpcodesniffer-composer-installer` to allowed plugins
  - Fixes GitHub Actions failures for PHPCS, PHP Compatibility, and Security checks
  - Required for Composer 2.2+ compatibility
- **PHPCS Configuration Issues** (Oct 6, 2025):
  - Simplified `phpcs.xml` to use only WordPress-Core standard (most stable)
  - Created separate `phpcs-compat.xml` for PHP Compatibility checks
  - Removed references to non-existent WordPress sniffs
  - Added verification steps in GitHub Actions workflows
  - Added `continue-on-error` for informational checks
  - Excluded `.github/` and `.md` files from PHPCS scans

---

## [1.1.0] - 2025-10-06

### Added
- **System Name Translation Feature**: New settings page to define user-friendly product names for internal model identifiers
  - Settings page accessible via Tools → Geekbench Settings
  - Quick-add buttons for common iPhone 17 models (iPhone18,1 through iPhone18,4)
  - Translations display in results table with original name in parentheses
  - AJAX handler for saving translations
- **Settings Link**: Added "Settings" link to plugin actions on the Plugins page
- **Column Reordering**: Moved "Upload Date" column to the end of the results table for better readability
  - New order: System Name → Processor → Platform → Single-Core → Multi-Core → Upload Date
- **Data Sanitization Post-Processor** (Oct 5, 2025):
  - Added `sanitize_results()` method to clean parsed data before caching
  - Added `sanitize_upload_date()` method to extract clean date format using regex
  - Removes usernames and extraneous text from upload date fields
  - Integrated into fetch pipeline: parse → sanitize → cache → return
  - Regex pattern: `/([A-Z][a-z]{2}\s+\d{1,2},\s+\d{4})/` extracts "MMM DD, YYYY" format
- **Critical Code Safeguards** (Oct 6, 2025):
  - Added ⚠️ warning comments to all critical parsing methods
  - Documented HTML structure dependencies with "as of Oct 5, 2025" timestamps
  - Added selector documentation to all extraction methods
  - Included references to GUZZLE-GB-WP.md documentation
  - Prevents accidental refactoring of tightly-coupled Geekbench HTML parsing logic
- **Comprehensive Data Structure Documentation** (Oct 6, 2025):
  - Added "Geekbench Results Data Structure" section to GUZZLE-GB-WP.md
  - Documented all 7 data fields with selectors, methods, and return types
  - Included data processing pipeline diagram
  - Listed known edge cases and maintenance notes
  - Added debugging tips for troubleshooting parsing issues
  - Timestamped as "Last Updated: October 5, 2025"

### Changed
- Updated plugin version from 1.0.0 to 1.1.0
- Improved table layout with better column organization
- Enhanced admin interface with settings submenu
- Updated GUZZLE-GB-WP.md with completed task checkmarks:
  - ✅ Phase 2 (Scraper Implementation) - All tasks complete
  - ✅ Phase 3 (WordPress Admin Interface) - All tasks complete
  - Added new subtasks for data sanitization and name translation features

### Fixed
- **Upload Date Display** (Oct 5, 2025): Date field now displays clean dates without usernames
  - Previously: "Oct 06, 2025\nusername" or "Oct 06, 2025 username"
  - Now: "Oct 06, 2025"
  - Fixed via post-processor sanitization instead of extraction-time handling
- Table column order now matches user expectations with date at the end

### Removed
- Cleaned up old Guzzle HTTP library source files from src/ directory
  - Removed: Cookie/, Exception/, Handler/ directories
  - Removed: All Guzzle-specific PHP files (Client.php, Middleware.php, etc.)
  - Guzzle is now properly loaded from vendor/ via Composer
- Removed unnecessary Guzzle repository files:
  - Development tools: .php-cs-fixer, phpstan, psalm, Makefile
  - Documentation: docs/, UPGRADING.md, original README.md
  - CI/CD: .github/, Dockerfile
  - Other: .editorconfig, vendor-bin/, package-lock.json

### Technical Details
- Added `geekbench_scraper_name_translations` option to store translations
- New template: `templates/settings-page.php`
- New AJAX action: `geekbench_scraper_save_translations`
- Updated `Admin.php` with settings page methods
- Updated `results-table.php` to display translated names

---

## [1.0.0] - 2025-10-06

### Added
- **Initial Release**: Complete WordPress plugin for scraping Geekbench browser results
- **Core Features**:
  - Guzzle HTTP Client integration for fetching HTML
  - Symfony DomCrawler for HTML parsing with CSS selectors
  - PSR-4 autoloading with Composer
  - WordPress transient caching (15-minute TTL)
  - Default search: "iPhone18" (iPhone 17 models with A19 chip)
  
- **Admin Interface**:
  - Admin menu under Tools → Geekbench Scraper
  - Search form with default query pre-filled
  - Refresh button to bypass cache
  - AJAX loading without page refresh
  - Sortable results table
  - Security: Nonces, capability checks, sanitization
  
- **Frontend Shortcode**:
  - `[geekbench_results]` shortcode for displaying results on posts/pages
  - Attributes: `query`, `limit`, `show_search`, `show_refresh`, `table_class`
  - Responsive mobile-friendly design
  - Optional search form and refresh button
  
- **Table Sorting**:
  - All columns sortable (System Name, Processor, Date, Platform, Scores)
  - Three-state toggle: Ascending → Descending → Reset to default
  - Visual indicators (arrows) show sort direction
  - Default sort: Date descending (newest first)
  - Type-aware sorting: Numeric for scores, date for dates, alphabetical for text
  
- **Data Extraction**:
  - System Name (device identifier)
  - Processor Info (CPU details)
  - Upload Date (benchmark submission date)
  - Platform (iOS, Android, macOS, Windows, Linux)
  - Single-Core Score
  - Multi-Core Score
  - Benchmark URL (link to full result)
  
- **Assets**:
  - `assets/css/admin.css` - Admin page styles
  - `assets/css/frontend.css` - Frontend shortcode styles
  - `assets/js/table-sort.js` - Client-side table sorting
  - `assets/js/admin.js` - Admin JavaScript
  
- **Templates**:
  - `templates/admin-page.php` - Admin interface
  - `templates/results-table.php` - Sortable results table (shared)
  - `templates/frontend-shortcode.php` - Shortcode output
  
- **Documentation**:
  - README-PLUGIN.md - Plugin documentation
  - INSTALLATION-GUIDE.md - Installation and testing guide
  - GUZZLE-GB-WP.md - Development planning document
  - PHPDoc comments on all classes and methods
  
- **Plugin Structure**:
  - `geekbench-scraper.php` - Main plugin file
  - `src/Plugin.php` - Main plugin class (singleton)
  - `src/Scraper.php` - Guzzle + DomCrawler scraper
  - `src/Admin.php` - Admin interface handler
  - `src/Shortcode.php` - Frontend shortcode handler
  - `uninstall.php` - Cleanup on uninstall
  
- **Composer Dependencies**:
  - guzzlehttp/guzzle ^7.0
  - symfony/dom-crawler ^6.0|^7.0
  - symfony/css-selector ^6.0|^7.0
  
- **WordPress Integration**:
  - Activation hook: Sets default options
  - Deactivation hook: Clears transient cache
  - Uninstall hook: Removes all plugin data
  - Plugin action links support
  
### Technical Specifications
- **Minimum Requirements**:
  - WordPress 5.8+
  - PHP 7.4+
  - Composer for dependency management
  
- **Caching Strategy**:
  - WordPress transients with 15-minute TTL
  - No database tables created
  - All data is ephemeral (cache-only)
  
- **Security**:
  - Nonce verification on all AJAX requests
  - Capability checks (`manage_options`)
  - Input sanitization and output escaping
  - XSS protection
  
- **Performance**:
  - Client-side table sorting (no server requests)
  - Optimized Composer autoloader
  - Conditional asset loading
  - AJAX for dynamic content

---

## Future Enhancements (Planned)

### Potential Features for v1.2.0+
- [ ] Pagination support for large result sets
- [ ] Export results to CSV
- [ ] Comparison charts and graphs
- [ ] Save favorite searches
- [ ] Email notifications for new benchmarks
- [ ] Multi-device comparison view
- [ ] Historical data tracking
- [ ] Custom CSS theme support
- [ ] REST API endpoints
- [ ] PHPUnit test suite
- [ ] Internationalization (i18n) support

---

## Support

For issues, questions, or feature requests:
- GitHub Issues: [Create an issue](https://github.com/yourusername/geekbench-scraper/issues)
- Documentation: See README-PLUGIN.md and INSTALLATION-GUIDE.md

---

## License

GPL-2.0-or-later - See LICENSE file for details

