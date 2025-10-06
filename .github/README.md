# GitHub Actions CI/CD

This directory contains GitHub Actions workflows for automated testing and code quality checks.

## Workflows

### 1. PHP Lint (`php-lint.yml`)
**Purpose**: Checks PHP syntax across multiple PHP versions

**Runs on**:
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches
- Manual trigger via workflow_dispatch

**PHP Versions Tested**:
- PHP 7.4
- PHP 8.0
- PHP 8.1
- PHP 8.2
- PHP 8.3

**What it does**:
- Validates `composer.json` and `composer.lock`
- Runs PHP syntax check (`php -l`) on all PHP files
- Checks for parse errors in source files
- Caches Composer dependencies for faster runs

---

### 2. WordPress Coding Standards (`wordpress-coding-standards.yml`)
**Purpose**: Enforces WordPress coding standards using PHPCS

**Runs on**:
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches
- Manual trigger via workflow_dispatch

**PHP Versions Tested**:
- PHP 7.4
- PHP 8.0
- PHP 8.1
- PHP 8.2

**What it does**:
- Installs WordPress Coding Standards (WPCS)
- Configures PHPCS with WordPress rules
- Runs PHPCS checks against all PHP files
- Generates summary and detailed reports
- Annotates code with violations (on PHP 8.0)

**Standards Checked**:
- WordPress Core
- WordPress Extra
- WordPress Docs
- PHPCompatibilityWP (PHP 7.4+)

---

### 3. Continuous Integration (`ci.yml`)
**Purpose**: Comprehensive CI pipeline combining all checks

**Runs on**:
- Push to `main` or `develop` branches
- Pull requests to `main` or `develop` branches
- Manual trigger via workflow_dispatch

**Jobs**:
1. **PHP Lint**: Syntax check across PHP 7.4-8.3
2. **WordPress Coding Standards**: PHPCS checks
3. **PHP Compatibility**: Ensures PHP 7.4+ compatibility
4. **Security Check**: Scans for vulnerable dependencies
5. **Summary**: Generates CI pipeline summary report

**Job Dependencies**:
```
php-lint
  ├── phpcs (depends on php-lint)
  ├── php-compatibility (depends on php-lint)
  └── security-check (independent)
       └── summary (depends on all jobs)
```

---

## Local Development

### Running Checks Locally

Before pushing code, run these commands locally:

#### 1. PHP Syntax Check
```bash
find . -type f -name "*.php" ! -path "./vendor/*" ! -path "./tests/*" -print0 | xargs -0 -n1 php -l
```

#### 2. WordPress Coding Standards
```bash
# Install dependencies first
composer install

# Run PHPCS
composer run-script lint

# Auto-fix issues (where possible)
composer run-script lint:fix
```

#### 3. PHP Compatibility Check
```bash
vendor/bin/phpcs -p src/ --standard=PHPCompatibilityWP --runtime-set testVersion 7.4-
```

#### 4. Security Audit
```bash
composer audit
```

---

## Configuration Files

### `phpcs.xml`
Located in the project root, this file configures PHPCS with:
- WordPress coding standards
- Custom exclusions (vendor, tests, etc.)
- Text domain validation (`geekbench-scraper`)
- Prefix validation (`geekbench_scraper`, `GeekbenchScraper`)
- Minimum WordPress version (5.8)
- PHP compatibility (7.4+)

### Composer Scripts
Defined in `composer.json`:
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

## Badges

Add these badges to your main README.md:

```markdown
![PHP Lint](https://github.com/yourusername/geekbench-scraper/workflows/PHP%20Lint/badge.svg)
![WordPress Coding Standards](https://github.com/yourusername/geekbench-scraper/workflows/WordPress%20Coding%20Standards/badge.svg)
![CI](https://github.com/yourusername/geekbench-scraper/workflows/Continuous%20Integration/badge.svg)
```

---

## Troubleshooting

### PHPCS Not Finding WordPress Standards
If you see "ERROR: Referenced sniff 'WordPress' does not exist":
```bash
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs,vendor/phpcompatibility/phpcompatibility-wp
vendor/bin/phpcs -i
```

### Composer Cache Issues
Clear the cache and reinstall:
```bash
composer clear-cache
rm -rf vendor/
composer install
```

### GitHub Actions Failing Locally Works
Ensure you're using the same PHP version:
```bash
php -v
composer install --prefer-dist --no-progress
```

---

## Customization

### Excluding Files from PHPCS
Edit `phpcs.xml` and add:
```xml
<exclude-pattern>*/path/to/exclude/*</exclude-pattern>
```

### Changing PHP Versions
Edit the workflow files and modify the `matrix.php-version` array:
```yaml
matrix:
  php-version: ['7.4', '8.0', '8.1', '8.2', '8.3']
```

### Adding Custom Rules
Edit `phpcs.xml` and add rules:
```xml
<rule ref="WordPress.Security.EscapeOutput"/>
```

---

## Resources

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [PHP_CodeSniffer Documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [WPCS GitHub Repository](https://github.com/WordPress/WordPress-Coding-Standards)
- [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility)

---

## Maintenance

### Updating Dependencies
```bash
composer update --with-dependencies
```

### Updating Workflows
When updating workflows, test locally first:
```bash
# Install act (GitHub Actions local runner)
brew install act

# Run workflow locally
act -j phpcs
```

---

## Support

For issues with GitHub Actions:
1. Check the Actions tab in your GitHub repository
2. Review the workflow logs
3. Ensure all dependencies are installed
4. Verify phpcs.xml configuration
5. Test locally before pushing

