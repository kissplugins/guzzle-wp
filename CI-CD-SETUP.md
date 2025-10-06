# CI/CD Setup Guide

This guide explains how to set up and use the GitHub Actions CI/CD pipeline for the Geekbench Scraper WordPress Plugin.

## Quick Start

### 1. Install Dependencies

First, install the required Composer dependencies including WordPress Coding Standards:

```bash
composer install
```

This will install:
- `squizlabs/php_codesniffer` - PHP_CodeSniffer
- `wp-coding-standards/wpcs` - WordPress Coding Standards
- `phpcompatibility/phpcompatibility-wp` - PHP Compatibility checks for WordPress

### 2. Configure PHPCS

Configure PHPCS to recognize WordPress Coding Standards:

```bash
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs,vendor/phpcompatibility/phpcompatibility-wp
```

Verify the installation:

```bash
vendor/bin/phpcs -i
```

You should see output like:
```
The installed coding standards are MySource, PEAR, PSR1, PSR2, PSR12, Squiz, Zend, WordPress, WordPress-Core, WordPress-Docs, WordPress-Extra, PHPCompatibility, PHPCompatibilityParagonieRandomCompat, PHPCompatibilityParagonieSodiumCompat and PHPCompatibilityWP
```

### 3. Test Locally

Before pushing to GitHub, test the checks locally:

#### PHP Syntax Check
```bash
find . -type f -name "*.php" ! -path "./vendor/*" ! -path "./tests/*" -print0 | xargs -0 -n1 php -l
```

#### WordPress Coding Standards
```bash
composer run-script lint
```

#### Auto-fix Coding Standards Issues
```bash
composer run-script lint:fix
```

#### PHP Compatibility Check
```bash
vendor/bin/phpcs -p src/ --standard=PHPCompatibilityWP --runtime-set testVersion 7.4-
```

### 4. Push to GitHub

Once all checks pass locally, push your code to GitHub:

```bash
git add .
git commit -m "Add GitHub Actions CI/CD"
git push origin main
```

The GitHub Actions workflows will automatically run on:
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches

---

## GitHub Actions Workflows

### Available Workflows

1. **PHP Lint** (`.github/workflows/php-lint.yml`)
   - Checks PHP syntax across PHP 7.4, 8.0, 8.1, 8.2, 8.3
   - Validates composer.json
   - Runs parse error checks

2. **WordPress Coding Standards** (`.github/workflows/wordpress-coding-standards.yml`)
   - Runs PHPCS with WordPress standards
   - Generates summary and detailed reports
   - Annotates code with violations

3. **Continuous Integration** (`.github/workflows/ci.yml`)
   - Comprehensive pipeline with all checks
   - PHP Lint + PHPCS + Compatibility + Security
   - Generates summary report

### Viewing Results

1. Go to your GitHub repository
2. Click on the "Actions" tab
3. Select a workflow run to view details
4. Click on individual jobs to see logs

---

## Configuration Files

### `phpcs.xml`

The PHPCS configuration file defines:
- **Standards**: WordPress, WordPress-Extra, WordPress-Docs
- **Exclusions**: vendor/, tests/, node_modules/
- **Text Domain**: `geekbench-scraper`
- **Prefixes**: `geekbench_scraper`, `GeekbenchScraper`
- **Minimum WP Version**: 5.8
- **PHP Version**: 7.4+

### `composer.json`

Added scripts for easy command execution:
```json
{
  "scripts": {
    "lint": "phpcs --standard=phpcs.xml",
    "lint:fix": "phpcbf --standard=phpcs.xml",
    "phpcs": "phpcs",
    "phpcbf": "phpcbf"
  }
}
```

---

## Common Commands

### Run All Checks
```bash
# PHP Lint
find . -type f -name "*.php" ! -path "./vendor/*" ! -path "./tests/*" -print0 | xargs -0 -n1 php -l

# PHPCS
composer run-script lint

# PHP Compatibility
vendor/bin/phpcs -p src/ --standard=PHPCompatibilityWP --runtime-set testVersion 7.4-

# Security Audit
composer audit
```

### Fix Coding Standards Issues
```bash
# Auto-fix (where possible)
composer run-script lint:fix

# Or use phpcbf directly
vendor/bin/phpcbf --standard=phpcs.xml
```

### Check Specific Files
```bash
# Check single file
vendor/bin/phpcs src/Scraper.php

# Check directory
vendor/bin/phpcs src/

# Check with specific standard
vendor/bin/phpcs --standard=WordPress src/Scraper.php
```

### Generate Reports
```bash
# Summary report
vendor/bin/phpcs --standard=phpcs.xml --report=summary

# Full report
vendor/bin/phpcs --standard=phpcs.xml --report=full

# Source report (shows violation counts by sniff)
vendor/bin/phpcs --standard=phpcs.xml --report=source

# JSON report
vendor/bin/phpcs --standard=phpcs.xml --report=json
```

---

## Customizing Standards

### Excluding Specific Rules

Edit `phpcs.xml` to exclude rules:

```xml
<rule ref="WordPress">
    <!-- Exclude specific sniff -->
    <exclude name="WordPress.PHP.YodaConditions.NotYoda"/>
</rule>
```

### Adding Custom Rules

Add rules to `phpcs.xml`:

```xml
<!-- Enforce specific rule -->
<rule ref="WordPress.Security.EscapeOutput"/>

<!-- Configure rule properties -->
<rule ref="WordPress.WP.I18n">
    <properties>
        <property name="text_domain" type="array">
            <element value="geekbench-scraper"/>
        </property>
    </properties>
</rule>
```

### Ignoring Specific Files

Add to `phpcs.xml`:

```xml
<exclude-pattern>*/specific-file.php</exclude-pattern>
<exclude-pattern>*/directory/*</exclude-pattern>
```

---

## Troubleshooting

### Issue: "Referenced sniff 'WordPress' does not exist"

**Solution**: Configure PHPCS installed paths:
```bash
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs,vendor/phpcompatibility/phpcompatibility-wp
vendor/bin/phpcs -i
```

### Issue: "No coding standards are installed"

**Solution**: Reinstall dependencies:
```bash
rm -rf vendor/
composer install
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs,vendor/phpcompatibility/phpcompatibility-wp
```

### Issue: GitHub Actions failing but local passes

**Solution**: Ensure same PHP version:
```bash
# Check your PHP version
php -v

# Use the same version in GitHub Actions
# Edit .github/workflows/*.yml and set php-version
```

### Issue: Too many PHPCS violations

**Solution**: Auto-fix what you can:
```bash
# Fix automatically
composer run-script lint:fix

# Then manually fix remaining issues
composer run-script lint
```

---

## Best Practices

### Before Committing

1. **Run PHP Lint**: Ensure no syntax errors
2. **Run PHPCS**: Fix coding standards violations
3. **Run Tests**: Ensure functionality works
4. **Review Changes**: Check git diff

### Pre-commit Hook (Optional)

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash

echo "Running PHP Lint..."
find . -type f -name "*.php" ! -path "./vendor/*" ! -path "./tests/*" -print0 | xargs -0 -n1 php -l
if [ $? -ne 0 ]; then
    echo "PHP Lint failed. Commit aborted."
    exit 1
fi

echo "Running PHPCS..."
composer run-script lint
if [ $? -ne 0 ]; then
    echo "PHPCS failed. Commit aborted."
    exit 1
fi

echo "All checks passed!"
exit 0
```

Make it executable:
```bash
chmod +x .git/hooks/pre-commit
```

---

## CI/CD Pipeline Flow

```
┌─────────────────┐
│   Push/PR       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   PHP Lint      │ ◄── Tests PHP 7.4, 8.0, 8.1, 8.2, 8.3
└────────┬────────┘
         │
         ├──────────────────┬──────────────────┐
         ▼                  ▼                  ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
│     PHPCS       │ │ PHP Compat      │ │ Security Check  │
└────────┬────────┘ └────────┬────────┘ └────────┬────────┘
         │                   │                   │
         └───────────────────┴───────────────────┘
                             │
                             ▼
                    ┌─────────────────┐
                    │    Summary      │
                    └─────────────────┘
```

---

## Resources

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PHP_CodeSniffer Wiki](https://github.com/squizlabs/PHP_CodeSniffer/wiki)
- [WPCS GitHub](https://github.com/WordPress/WordPress-Coding-Standards)
- [GitHub Actions Docs](https://docs.github.com/en/actions)
- [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility)

---

## Next Steps

1. ✅ Install dependencies: `composer install`
2. ✅ Configure PHPCS: `vendor/bin/phpcs --config-set installed_paths ...`
3. ✅ Test locally: `composer run-script lint`
4. ✅ Fix issues: `composer run-script lint:fix`
5. ✅ Push to GitHub
6. ✅ Monitor Actions tab for results
7. ✅ Add status badges to README.md

---

## Support

For issues or questions:
- Check `.github/README.md` for detailed workflow documentation
- Review `phpcs.xml` for configuration details
- Run `vendor/bin/phpcs --help` for PHPCS options
- Check GitHub Actions logs for error details

