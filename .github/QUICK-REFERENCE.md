# CI/CD Quick Reference Card

Quick commands for running code quality checks locally.

## Setup (One-time)

```bash
# Install dependencies
composer install

# Configure PHPCS
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs,vendor/phpcompatibility/phpcompatibility-wp

# Verify installation
vendor/bin/phpcs -i
```

---

## Daily Commands

### Before Committing

```bash
# 1. Check PHP syntax
find . -type f -name "*.php" ! -path "./vendor/*" ! -path "./tests/*" -print0 | xargs -0 -n1 php -l

# 2. Check coding standards
composer run-script lint

# 3. Auto-fix issues
composer run-script lint:fix

# 4. Check again
composer run-script lint
```

### Quick Checks

```bash
# Check single file
vendor/bin/phpcs src/Scraper.php

# Check directory
vendor/bin/phpcs src/

# Fix single file
vendor/bin/phpcbf src/Scraper.php
```

---

## Reports

```bash
# Summary
vendor/bin/phpcs --standard=phpcs.xml --report=summary

# Full details
vendor/bin/phpcs --standard=phpcs.xml --report=full

# By violation type
vendor/bin/phpcs --standard=phpcs.xml --report=source

# JSON output
vendor/bin/phpcs --standard=phpcs.xml --report=json
```

---

## Compatibility Checks

```bash
# PHP 7.4+ compatibility
vendor/bin/phpcs -p src/ --standard=PHPCompatibilityWP --runtime-set testVersion 7.4-

# Specific PHP version
vendor/bin/phpcs -p src/ --standard=PHPCompatibilityWP --runtime-set testVersion 8.0-
```

---

## Security

```bash
# Check for vulnerabilities
composer audit

# Update dependencies
composer update --with-dependencies
```

---

## GitHub Actions

### Trigger Manually

1. Go to repository → Actions tab
2. Select workflow (e.g., "PHP Lint")
3. Click "Run workflow"
4. Select branch and click "Run workflow"

### View Results

1. Go to repository → Actions tab
2. Click on workflow run
3. Click on job to see details
4. Review logs and annotations

---

## Common Issues

### "WordPress standard not found"
```bash
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs,vendor/phpcompatibility/phpcompatibility-wp
```

### "Too many violations"
```bash
# Auto-fix first
composer run-script lint:fix

# Then check what's left
composer run-script lint
```

### "Cache issues"
```bash
# Clear PHPCS cache
rm -rf .phpcs-cache

# Clear Composer cache
composer clear-cache
rm -rf vendor/
composer install
```

---

## File Locations

- **PHPCS Config**: `phpcs.xml` (project root)
- **Workflows**: `.github/workflows/*.yml`
- **Documentation**: `.github/README.md`, `CI-CD-SETUP.md`
- **Composer Scripts**: `composer.json` → `scripts` section

---

## Standards Applied

- ✅ WordPress Core
- ✅ WordPress Extra
- ✅ WordPress Docs
- ✅ PHPCompatibilityWP (7.4+)
- ✅ PSR-1 (Class declarations)

---

## Exclusions

- ❌ `vendor/` - Third-party code
- ❌ `tests/` - Test files
- ❌ `node_modules/` - Frontend dependencies
- ❌ `*.js`, `*.css` - Non-PHP files

---

## Pre-commit Checklist

- [ ] PHP syntax check passes
- [ ] PHPCS check passes (or violations documented)
- [ ] Auto-fixed what can be fixed
- [ ] Tested functionality still works
- [ ] Reviewed git diff
- [ ] Updated documentation if needed

---

## Useful Flags

```bash
# Show progress
vendor/bin/phpcs -p

# Show sniff codes
vendor/bin/phpcs -s

# Ignore warnings
vendor/bin/phpcs -n

# Set severity (1-10)
vendor/bin/phpcs --severity=5

# Parallel processing
vendor/bin/phpcs --parallel=8
```

---

## Help

```bash
# PHPCS help
vendor/bin/phpcs --help

# List installed standards
vendor/bin/phpcs -i

# Show standard details
vendor/bin/phpcs --standard=WordPress -e

# Explain sniff
vendor/bin/phpcs --standard=WordPress --generator=Text
```

---

## Resources

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [PHPCS Wiki](https://github.com/squizlabs/PHP_CodeSniffer/wiki)
- [WPCS GitHub](https://github.com/WordPress/WordPress-Coding-Standards)
- [GitHub Actions Docs](https://docs.github.com/en/actions)

---

**Last Updated**: October 6, 2025

