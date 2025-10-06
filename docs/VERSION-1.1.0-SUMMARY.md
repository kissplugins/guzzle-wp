# Geekbench Browser Scraper v1.1.0 - Update Summary

## 🎉 Version 1.1.0 Released!

This update adds powerful new features for managing system name translations and improves the overall user experience.

---

## ✨ What's New in v1.1.0

### 1. **System Name Translation Feature** 🏷️

Transform cryptic internal model identifiers into user-friendly product names!

**Before:**
```
iPhone18,2 → iPhone18,2
```

**After (with translation):**
```
iPhone18,2 → iPhone 17 Plus (iPhone18,2)
```

**Features:**
- ✅ New settings page: **Tools → Geekbench Settings**
- ✅ Add unlimited custom translations
- ✅ Quick-add buttons for common iPhone 17 models
- ✅ Live preview of translations
- ✅ Translations display in both admin and frontend tables
- ✅ Original name shown in parentheses for reference

**How to Use:**
1. Go to **Tools → Geekbench Settings**
2. Click "Add Translation" or use quick-add buttons
3. Enter System Name (e.g., "iPhone18,2")
4. Enter Display Name (e.g., "iPhone 17 Plus")
5. Click "Save Translations"

**Quick-Add Buttons Included:**
- iPhone 17 (iPhone18,1)
- iPhone 17 Plus (iPhone18,2)
- iPhone 17 Pro (iPhone18,3)
- iPhone 17 Pro Max (iPhone18,4)

---

### 2. **Settings Link in Plugins Page** ⚙️

Quick access to settings directly from the Plugins page!

**Location:** Plugins → Installed Plugins → Geekbench Browser Scraper → **Settings**

No more navigating through menus - one click takes you to the settings page.

---

### 3. **Improved Table Column Order** 📊

Reorganized columns for better readability:

**New Order:**
1. System Name
2. Processor
3. Platform
4. Single-Core Score
5. Multi-Core Score
6. **Upload Date** (moved to end)

**Why?** Upload date is less critical than performance scores, so it's now at the end for better focus on the data that matters most.

---

### 4. **Cleaned Up Repository** 🧹

Removed all unnecessary Guzzle HTTP library files:

**Removed:**
- ❌ Old Guzzle source files (Cookie/, Exception/, Handler/ directories)
- ❌ Development tools (.php-cs-fixer, phpstan, psalm, Makefile)
- ❌ Original Guzzle documentation (docs/, UPGRADING.md, README.md)
- ❌ CI/CD files (.github/, Dockerfile)
- ❌ Other unnecessary files (.editorconfig, vendor-bin/, package-lock.json)

**Result:** Cleaner, leaner plugin with only essential files!

**Plugin Files Now:**
```
guzzle-wp/
├── geekbench-scraper.php       ← Main plugin
├── src/                         ← Only 4 plugin classes
│   ├── Admin.php
│   ├── Plugin.php
│   ├── Scraper.php
│   └── Shortcode.php
├── assets/                      ← CSS/JS
├── templates/                   ← HTML templates (now 4 files)
├── vendor/                      ← Composer dependencies
├── CHANGELOG.md                 ← Version history
├── README-PLUGIN.md             ← Documentation
└── INSTALLATION-GUIDE.md        ← Setup guide
```

---

## 📋 Files Modified/Created

### **Modified Files** (3)
1. ✅ `geekbench-scraper.php` - Updated version to 1.1.0, added settings link
2. ✅ `src/Admin.php` - Added settings page and translation AJAX handler
3. ✅ `templates/results-table.php` - Reordered columns, added translation display

### **New Files** (2)
4. ✅ `templates/settings-page.php` - Settings page template
5. ✅ `CHANGELOG.md` - Version history and changelog

### **Removed Files** (30+)
- All old Guzzle HTTP library source files
- Development and CI/CD configuration files
- Original Guzzle repository documentation

---

## 🚀 Upgrade Instructions

### For Existing Users (v1.0.0 → v1.1.0)

1. **Backup your site** (recommended)
2. **Update the plugin files** (replace all files)
3. **No database migration needed** - settings are stored in WordPress options
4. **Visit the new settings page**: Tools → Geekbench Settings
5. **Add your translations** (optional)

### For New Users

Follow the standard installation process in `INSTALLATION-GUIDE.md`

---

## 🔧 Technical Changes

### New Database Options
- `geekbench_scraper_name_translations` - Stores system name translations (array)

### New AJAX Actions
- `geekbench_scraper_save_translations` - Saves translation settings

### New Templates
- `templates/settings-page.php` - Settings page with translation management

### Updated Methods
- `Admin::add_admin_menu()` - Now adds settings submenu
- `Admin::render_settings_page()` - Renders settings page
- `Admin::ajax_save_translations()` - Handles AJAX save

### Column Order Change
- Upload Date moved from position 3 to position 6 (end of table)

---

## 📊 Feature Comparison

| Feature | v1.0.0 | v1.1.0 |
|---------|--------|--------|
| Geekbench Scraping | ✅ | ✅ |
| Admin Interface | ✅ | ✅ |
| Frontend Shortcode | ✅ | ✅ |
| Sortable Tables | ✅ | ✅ |
| Caching (15 min) | ✅ | ✅ |
| **System Name Translations** | ❌ | ✅ **NEW** |
| **Settings Page** | ❌ | ✅ **NEW** |
| **Settings Link in Plugins** | ❌ | ✅ **NEW** |
| **Optimized Column Order** | ❌ | ✅ **NEW** |
| **Clean File Structure** | ❌ | ✅ **NEW** |

---

## 🎯 Use Cases for Translations

### Example 1: iPhone Models
```
iPhone18,1 → iPhone 17
iPhone18,2 → iPhone 17 Plus
iPhone18,3 → iPhone 17 Pro
iPhone18,4 → iPhone 17 Pro Max
```

### Example 2: Mac Models
```
Mac15,3 → MacBook Air M3 13-inch
Mac15,4 → MacBook Air M3 15-inch
Mac15,6 → MacBook Pro M3 14-inch
Mac15,7 → MacBook Pro M3 16-inch
```

### Example 3: Android Devices
```
SM-S928U → Samsung Galaxy S24 Ultra
SM-S926U → Samsung Galaxy S24+
SM-S921U → Samsung Galaxy S24
```

---

## 🐛 Bug Fixes

- Fixed table column order to be more intuitive
- Cleaned up autoloader warnings from old Guzzle files

---

## 📝 Documentation Updates

- ✅ Created comprehensive CHANGELOG.md
- ✅ Updated INSTALLATION-GUIDE.md with v1.1.0 features
- ✅ Updated README-PLUGIN.md with translation feature
- ✅ Added this VERSION-1.1.0-SUMMARY.md

---

## 🔮 What's Next?

### Planned for v1.2.0
- Pagination support for large result sets
- Export results to CSV
- Comparison charts and graphs
- Save favorite searches
- PHPUnit test suite

---

## 💡 Tips & Tricks

### Tip 1: Bulk Add Translations
Use the quick-add buttons to quickly populate common iPhone models, then customize as needed.

### Tip 2: Keep Original Names Visible
Translations show the original name in parentheses, so you always know the internal identifier.

### Tip 3: Remove Unused Translations
Click "Remove" next to any translation you no longer need.

### Tip 4: Preview Before Saving
The "Example" column shows a live preview of how your translation will appear.

---

## 📞 Support

Need help? Check these resources:
- **Documentation**: README-PLUGIN.md
- **Installation Guide**: INSTALLATION-GUIDE.md
- **Changelog**: CHANGELOG.md
- **Development Plan**: GUZZLE-GB-WP.md

---

## ✅ Testing Checklist

Before going live, test these features:

- [ ] Activate plugin successfully
- [ ] Access Tools → Geekbench Scraper (main page)
- [ ] Access Tools → Geekbench Settings (new settings page)
- [ ] Click "Settings" link from Plugins page
- [ ] Add a custom translation
- [ ] Use quick-add buttons for iPhone models
- [ ] Save translations and verify they appear in results table
- [ ] Verify column order: System Name, Processor, Platform, Scores, Date
- [ ] Test frontend shortcode with translations
- [ ] Test table sorting still works correctly

---

## 🎊 Summary

**Version 1.1.0** is a significant update that adds:
- ✅ System name translation feature
- ✅ Settings page with quick-add buttons
- ✅ Settings link in plugins page
- ✅ Improved table column order
- ✅ Cleaned up file structure
- ✅ Comprehensive changelog

**Upgrade now to enjoy these new features!** 🚀

