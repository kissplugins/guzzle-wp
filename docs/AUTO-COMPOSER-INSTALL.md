# Auto Composer Install Feature

**Version**: 1.2.0  
**Date**: October 6, 2025  
**Feature**: Automatic Composer dependency installation on plugin activation

---

## 🎯 Overview

The plugin now automatically attempts to install Composer dependencies when activated on a new WordPress site. This eliminates the manual step of running `composer install` and makes the plugin more user-friendly.

---

## 🚀 How It Works

### Activation Flow

1. **User activates plugin** in WordPress Admin → Plugins
2. **Plugin checks** if `vendor/autoload.php` exists
3. **If missing**, plugin attempts auto-installation:
   - **Method 1**: Try local `composer.phar` (if exists in plugin directory)
   - **Method 2**: Try system `composer` command (if available)
4. **Result notification** shown to admin:
   - ✅ **Success**: Green notice confirming installation
   - ⚠️ **Failure**: Yellow notice with manual instructions

### Installation Methods

#### Method 1: Local composer.phar (Preferred)

```bash
# Plugin looks for:
/path/to/plugin/composer.phar

# Runs command:
php composer.phar install --no-dev --optimize-autoloader --no-interaction
```

**Advantages**:
- Works even if system composer is not installed
- Uses specific PHP version
- Portable and self-contained

#### Method 2: System Composer (Fallback)

```bash
# Plugin checks if 'composer' command exists
which composer

# Runs command:
composer install --no-dev --optimize-autoloader --no-interaction
```

**Advantages**:
- No need for local composer.phar
- Uses system-wide composer installation

---

## 📋 PHP Binary Detection

The plugin intelligently finds the PHP binary in this order:

1. **PHP_BINARY constant** (PHP 5.4+)
2. **Common PHP binaries**: `php`, `php8`, `php7`, `php-cli`
3. **System PATH lookup**: Uses `which` command

### Custom PHP Path

If you need to use a specific PHP version (e.g., Local by Flywheel's PHP):

```php
// Add to wp-config.php or plugin
define('PHP_BINARY', '/Users/noelsaw/Library/Application Support/Local/lightning-services/php-8.2.27+1/bin/darwin-arm64/bin/php');
```

---

## 🔔 Admin Notices

### Success Notice

```
✅ Geekbench Browser Scraper: Composer dependencies installed successfully!
```

**When shown**: After successful auto-installation  
**Duration**: Shown once, then dismissed  
**Action**: None required - plugin is ready to use

### Failure Notice

```
⚠️ Geekbench Browser Scraper: Unable to automatically install Composer dependencies.

Please run manually:
cd /path/to/plugin/
composer install --no-dev --optimize-autoloader

[Show error details] (expandable)
```

**When shown**: If auto-installation fails  
**Duration**: Shown for 5 minutes  
**Action**: Follow manual installation instructions

### Missing Dependencies Notice

```
❌ Geekbench Browser Scraper: Composer dependencies are missing.

The plugin attempted to install dependencies automatically but was unable to do so.
Please run composer install --no-dev --optimize-autoloader
in the plugin directory: /path/to/plugin/

Alternative: If you have composer.phar in the plugin directory, run:
php composer.phar install --no-dev --optimize-autoloader
```

**When shown**: If dependencies are still missing after activation  
**Duration**: Shown on every admin page until resolved  
**Action**: Manual installation required

---

## 🛠️ Manual Installation (Fallback)

If auto-installation fails, follow these steps:

### Option 1: Using System Composer

```bash
cd /path/to/wp-content/plugins/guzzle-wp/
composer install --no-dev --optimize-autoloader
```

### Option 2: Using Local composer.phar

```bash
cd /path/to/wp-content/plugins/guzzle-wp/
php composer.phar install --no-dev --optimize-autoloader
```

### Option 3: Using Specific PHP Version

```bash
cd /path/to/wp-content/plugins/guzzle-wp/
/path/to/php composer.phar install --no-dev --optimize-autoloader
```

**Example for Local by Flywheel**:
```bash
cd /Users/noelsaw/Local\ Sites/macnerdxyz-05-25/app/public/wp-content/plugins/guzzle-wp/
"/Users/noelsaw/Library/Application Support/Local/lightning-services/php-8.2.27+1/bin/darwin-arm64/bin/php" composer.phar install --no-dev --optimize-autoloader
```

---

## 🔍 Troubleshooting

### Issue: "composer: command not found"

**Solution**: Install Composer globally or use local `composer.phar`

```bash
# Download composer.phar to plugin directory
cd /path/to/plugin/
curl -sS https://getcomposer.org/installer | php
```

### Issue: "PHP binary not found"

**Solution**: Define PHP_BINARY constant

```php
// In wp-config.php
define('PHP_BINARY', '/usr/bin/php');
```

### Issue: "Permission denied"

**Solution**: Fix file permissions

```bash
chmod +x composer.phar
chmod -R 755 /path/to/plugin/
```

### Issue: "Memory limit exceeded"

**Solution**: Increase PHP memory limit

```bash
php -d memory_limit=512M composer.phar install --no-dev --optimize-autoloader
```

---

## 🧪 Testing the Feature

### Test 1: Fresh Installation

1. Upload plugin to WordPress (without vendor directory)
2. Activate plugin
3. Check for success notice
4. Verify `vendor/autoload.php` exists

### Test 2: With composer.phar

1. Place `composer.phar` in plugin directory
2. Delete `vendor` directory
3. Activate plugin
4. Should use local composer.phar

### Test 3: With System Composer

1. Remove `composer.phar` from plugin directory
2. Delete `vendor` directory
3. Ensure system composer is installed
4. Activate plugin
5. Should use system composer

### Test 4: Failure Scenario

1. Remove `composer.phar`
2. Uninstall system composer
3. Delete `vendor` directory
4. Activate plugin
5. Should show failure notice with instructions

---

## 📊 Function Reference

### `auto_install_composer_dependencies()`

**Purpose**: Main function that attempts to install dependencies  
**Called by**: `activate_plugin()` hook  
**Returns**: void  
**Side effects**: Creates transients for success/failure notices

### `get_php_binary()`

**Purpose**: Finds PHP binary path  
**Returns**: `string|false` - PHP binary path or false  
**Checks**:
1. PHP_BINARY constant
2. Common PHP binary names
3. System PATH

### `command_exists($command)`

**Purpose**: Checks if a command exists in system PATH  
**Parameters**: `$command` (string) - Command name  
**Returns**: `bool` - True if exists, false otherwise

### `show_missing_dependencies_notice()`

**Purpose**: Displays error notice when dependencies are missing  
**Called by**: `admin_notices` hook  
**Returns**: void

### `show_composer_install_notices()`

**Purpose**: Displays success/failure notices after auto-installation  
**Called by**: `admin_notices` hook  
**Returns**: void  
**Checks transients**:
- `geekbench_scraper_composer_install_success` (60 seconds)
- `geekbench_scraper_composer_install_failed` (300 seconds)

---

## 🔒 Security Considerations

### Command Execution Safety

- ✅ Uses `escapeshellcmd()` for PHP binary path
- ✅ Uses `escapeshellarg()` for composer.phar path
- ✅ Uses `--no-interaction` flag to prevent prompts
- ✅ Changes directory safely with try/finally
- ✅ Restores original directory after execution

### File System Safety

- ✅ Checks file existence before execution
- ✅ Validates composer.json exists
- ✅ Only runs on plugin activation (not on every page load)
- ✅ Uses WordPress transients for notices (auto-expire)

---

## 📝 Code Location

**File**: `geekbench-scraper.php`  
**Lines**: 68-203 (activation hook and helper functions)  
**Lines**: 232-312 (admin notice functions)

---

## 🎉 Benefits

1. **User-Friendly**: No manual command-line steps required
2. **Automatic**: Works out-of-the-box on most servers
3. **Flexible**: Supports multiple installation methods
4. **Informative**: Clear feedback on success/failure
5. **Safe**: Proper error handling and security measures
6. **Portable**: Works with local composer.phar

---

## 🚀 Future Enhancements

Potential improvements for future versions:

- [ ] Add WP-CLI command for manual installation
- [ ] Support for custom PHP binary path in settings
- [ ] Retry mechanism with exponential backoff
- [ ] Download composer.phar automatically if missing
- [ ] Check for minimum Composer version
- [ ] Validate installed dependencies after installation

---

## 📞 Support

If auto-installation fails:

1. Check the error details in the admin notice
2. Try manual installation (see above)
3. Verify PHP and Composer are installed
4. Check file permissions
5. Review server error logs

For persistent issues, refer to:
- `docs/INSTALLATION-GUIDE.md`
- WordPress debug.log
- Server error logs

