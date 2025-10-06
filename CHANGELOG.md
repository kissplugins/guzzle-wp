# Changelog

All notable changes to the Geekbench Browser Scraper WordPress plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

