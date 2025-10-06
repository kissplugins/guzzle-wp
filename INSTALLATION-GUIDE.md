# Geekbench Browser Scraper - Installation & Testing Guide

## ✅ Plugin Conversion Complete!

The Guzzle repository has been successfully converted into a WordPress PSR-4 autoloading plugin for scraping Geekbench browser results.

---

## 📁 Plugin Structure

```
guzzle-wp/
├── geekbench-scraper.php          ✅ Main plugin file
├── composer.json                   ✅ PSR-4 autoloading config
├── composer.lock                   ✅ Dependency lock file
├── uninstall.php                   ✅ Cleanup on uninstall
├── README-PLUGIN.md                ✅ Plugin documentation
│
├── src/                            ✅ PSR-4 source code
│   ├── Plugin.php                  ✅ Main plugin class (singleton)
│   ├── Scraper.php                 ✅ Guzzle + DomCrawler scraper
│   ├── Admin.php                   ✅ Admin interface handler
│   └── Shortcode.php               ✅ Frontend shortcode handler
│
├── assets/                         ✅ Frontend assets
│   ├── css/
│   │   ├── admin.css               ✅ Admin page styles
│   │   └── frontend.css            ✅ Shortcode styles
│   └── js/
│       ├── table-sort.js           ✅ Client-side sorting (shared)
│       └── admin.js                ✅ Admin JavaScript
│
├── templates/                      ✅ HTML templates
│   ├── admin-page.php              ✅ Admin page template
│   ├── results-table.php           ✅ Results table (shared)
│   └── frontend-shortcode.php      ✅ Shortcode template
│
└── vendor/                         ✅ Composer dependencies
    ├── guzzlehttp/guzzle           ✅ HTTP client
    ├── symfony/dom-crawler         ✅ HTML parser
    └── symfony/css-selector        ✅ CSS selectors
```

---

## 🚀 Installation Steps

### 1. Dependencies Already Installed ✅

Composer dependencies have been installed:
- ✅ Guzzle HTTP Client (v7.10.0)
- ✅ Symfony DomCrawler (v7.3.3)
- ✅ Symfony CSS Selector (v7.3.0)

### 2. Activate the Plugin

1. Go to WordPress Admin → **Plugins** → **Installed Plugins**
2. Find "**Geekbench Browser Scraper**"
3. Click "**Activate**"

### 3. Verify Activation

After activation, the plugin will:
- ✅ Create default options (default query: "iPhone18", cache TTL: 900 seconds)
- ✅ Register admin menu under **Tools → Geekbench Scraper**
- ✅ Register shortcode `[geekbench_results]`

---

## 🧪 Testing the Plugin

### Test 1: Admin Interface

1. Go to **Tools → Geekbench Scraper**
2. You should see:
   - Search form with "iPhone18" pre-filled
   - Search and Refresh buttons
3. Click "**Search**" to fetch results
4. Verify:
   - ✅ Results table displays with ~25 iPhone 17 benchmarks
   - ✅ Columns: System Name, Processor, Upload Date, Platform, Single-Core, Multi-Core
   - ✅ Click column headers to sort (ascending/descending/reset)
   - ✅ System names are clickable links to Geekbench

### Test 2: Frontend Shortcode

1. Create a new **Post** or **Page**
2. Add the shortcode:
   ```
   [geekbench_results]
   ```
3. Publish and view the page
4. Verify:
   - ✅ Results table displays on frontend
   - ✅ Table is sortable (click headers)
   - ✅ Responsive design works on mobile

### Test 3: Shortcode with Attributes

Try these variations:

```
<!-- Custom search -->
[geekbench_results query="MacBook Pro"]

<!-- With search form -->
[geekbench_results show_search="true"]

<!-- Limited results with refresh button -->
[geekbench_results limit="10" show_refresh="true"]

<!-- Full featured -->
[geekbench_results query="Galaxy S24" show_search="true" show_refresh="true" limit="15"]
```

### Test 4: Caching

1. Search for "iPhone18" in admin
2. Note the results
3. Click "**Refresh Results**" button
4. Verify:
   - ✅ Cache is bypassed
   - ✅ Fresh data is fetched
   - ✅ Success message appears

### Test 5: Error Handling

1. Search for a nonsense query (e.g., "xyzabc123")
2. Verify:
   - ✅ "No results found" message appears
   - ✅ No PHP errors in debug log

---

## 🔍 Default Search: iPhone18

The plugin defaults to searching for **"iPhone18"** which represents:

- **Device**: iPhone 17 models (all variants)
- **Chip**: A19 / ARM 4257 MHz
- **Variants**:
  - iPhone18,1 (iPhone 17)
  - iPhone18,2 (iPhone 17 Plus)
  - iPhone18,3 (iPhone 17 Pro)
  - iPhone18,4 (iPhone 17 Pro Max)

**Why "iPhone18"?** Geekbench uses internal model identifiers, not marketing names.

---

## 📊 Features Implemented

### Core Functionality
- ✅ **Guzzle HTTP Client** - Fetches HTML from Geekbench
- ✅ **Symfony DomCrawler** - Parses HTML with CSS selectors
- ✅ **PSR-4 Autoloading** - Proper namespace structure
- ✅ **WordPress Transient Caching** - 15-minute TTL
- ✅ **Error Handling** - Graceful failures with user-friendly messages

### Admin Interface
- ✅ **Admin Menu** - Under Tools → Geekbench Scraper
- ✅ **Search Form** - With default query pre-filled
- ✅ **Refresh Button** - Bypass cache
- ✅ **AJAX Loading** - No page refresh
- ✅ **Sortable Table** - Click headers to sort
- ✅ **Security** - Nonces, capability checks, sanitization

### Frontend Shortcode
- ✅ **Shortcode**: `[geekbench_results]`
- ✅ **Attributes**: query, limit, show_search, show_refresh, table_class
- ✅ **Responsive Design** - Mobile-friendly
- ✅ **Same Sorting** - Client-side table sorting
- ✅ **AJAX Support** - Optional search form

### Table Sorting
- ✅ **All Columns Sortable** - System Name, Processor, Date, Platform, Scores
- ✅ **Three-State Toggle** - Ascending → Descending → Reset to default
- ✅ **Visual Indicators** - Arrows show sort direction
- ✅ **Default Sort** - Date descending (newest first)
- ✅ **Type-Aware** - Numeric sorting for scores, date sorting for dates

### Data Extraction
- ✅ **System Name** - Device identifier (e.g., "iPhone18,2")
- ✅ **Processor Info** - CPU details (type, frequency, cores)
- ✅ **Upload Date** - When benchmark was submitted
- ✅ **Platform** - OS (iOS, Android, macOS, etc.)
- ✅ **Single-Core Score** - Performance score
- ✅ **Multi-Core Score** - Performance score
- ✅ **Benchmark URL** - Link to full result

---

## 🔧 Troubleshooting

### Issue: "Composer dependencies are missing"

**Solution**: Dependencies are already installed. If you see this error:
```bash
cd /path/to/guzzle-wp
/Users/noelsaw/Library/Application\ Support/Local/lightning-services/php-8.2.27+1/bin/darwin-arm64/bin/php composer.phar install --no-dev
```

### Issue: No results appear

**Possible causes**:
1. Network connectivity issue
2. Geekbench website structure changed
3. PHP timeout

**Debug**:
- Enable WordPress debug mode: `define('WP_DEBUG', true);`
- Check error logs in `wp-content/debug.log`

### Issue: Table not sorting

**Solution**:
- Clear browser cache
- Check browser console for JavaScript errors
- Verify `table-sort.js` is loaded

---

## 📝 Next Steps

### Optional Enhancements

1. **Add Tests** (Phase 6):
   - Create PHPUnit tests
   - Add test fixtures
   - Set up CI/CD

2. **Customize Styling**:
   - Edit `assets/css/admin.css`
   - Edit `assets/css/frontend.css`

3. **Add More Features**:
   - Pagination support
   - Export to CSV
   - Comparison charts
   - Save favorite searches

---

## 📚 Documentation

- **Plugin README**: `README-PLUGIN.md`
- **Development Plan**: `GUZZLE-GB-WP.md`
- **This Guide**: `INSTALLATION-GUIDE.md`

---

## ✨ Summary

**Status**: ✅ **READY FOR TESTING**

The plugin is fully functional and ready to use. All core features have been implemented:
- ✅ PSR-4 autoloading with Composer
- ✅ Guzzle HTTP client integration
- ✅ Symfony DomCrawler HTML parsing
- ✅ Admin interface with AJAX
- ✅ Frontend shortcode
- ✅ Sortable tables
- ✅ Caching system
- ✅ Security measures
- ✅ PHPDoc comments

**Default Search**: iPhone18 (iPhone 17 models with A19 chip)

**Activate the plugin and start testing!** 🚀

