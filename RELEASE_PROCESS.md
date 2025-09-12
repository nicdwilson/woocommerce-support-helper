# Release Process

This document outlines the process for creating release packages for WooCommerce Support Helper.

## 🚀 Automated Release Process

### GitHub Workflows

#### 1. **Automatic Release on Tags** (`.github/workflows/release.yml`)
- **Trigger**: Push tags matching `v*` pattern (e.g., `v1.0.0`)
- **Actions**:
  - Installs production dependencies only
  - Creates clean release package
  - Excludes all development dependencies
  - Creates GitHub release with zip file
  - Generates release notes automatically

#### 2. **Manual Release Testing** (`.github/workflows/test-release.yml`)
- **Trigger**: Manual dispatch or changes to release files
- **Actions**:
  - Tests the release creation process
  - Validates package structure
  - Uploads test package as artifact

#### 3. **Package Testing** (`.github/workflows/package-test.yml`)
- **Trigger**: Called by other workflows
- **Actions**:
  - Validates package contents
  - Tests PHP syntax
  - Verifies autoloader functionality
  - Checks package size and structure

### Creating a Release

#### Option 1: Automatic Release (Recommended)
```bash
# 1. Update version in woocommerce-support-helper.php
# 2. Commit changes
git add .
git commit -m "chore: Bump version to 1.0.1"

# 3. Create and push tag
git tag v1.0.1
git push origin v1.0.1

# 4. GitHub Actions will automatically:
#    - Create the release package
#    - Upload it to GitHub releases
#    - Generate release notes
```

#### Option 2: Manual Release
```bash
# 1. Run the release script locally
./create-release.sh

# 2. Upload the generated zip file manually to GitHub releases
```

## 📦 Release Package Contents

### ✅ **Included Files**
- `woocommerce-support-helper.php` - Main plugin file
- `includes/` - All plugin modules and classes
- `vendor/autoload.php` - Production Composer autoloader
- `vendor/composer/` - Production Composer files (autoload_*.php, ClassLoader.php, InstalledVersions.php)
- `README.md` - Plugin documentation
- `LICENSE` - GPL license
- `RELEASE_NOTES_v*.md` - Release notes (if available)
- `composer.json` - Dependencies reference
- `phpcs.xml` - Coding standards config

### ❌ **Excluded Files**
- `vendor/phpunit/` - PHPUnit testing framework
- `vendor/sebastian/` - PHPUnit dependencies
- `vendor/squizlabs/` - PHP CodeSniffer
- `vendor/wp-coding-standards/` - WordPress Coding Standards
- `vendor/phpcsstandards/` - PHPCS utilities
- `vendor/nikic/` - PHP Parser
- `vendor/doctrine/` - Object instantiation
- `vendor/myclabs/` - Deep copying
- `tests/` - Test files
- `.github/` - GitHub workflows
- `node_modules/` - Node.js dependencies
- Development configuration files

## 🔧 Local Development

### Testing Release Package Locally
```bash
# Run the release script
./create-release.sh

# The script will:
# 1. Create a clean release package
# 2. Verify all essential files are present
# 3. Test PHP syntax
# 4. Test autoloader functionality
# 5. Create a zip file
# 6. Generate a size report
```

### Manual Package Creation
If you need to create a package manually:

```bash
# 1. Install production dependencies
composer install --no-dev --optimize-autoloader

# 2. Create release directory
mkdir release-package

# 3. Copy essential files
cp woocommerce-support-helper.php release-package/
cp -r includes release-package/
cp README.md LICENSE release-package/
cp composer.json phpcs.xml release-package/

# 4. Copy minimal vendor files
mkdir -p release-package/vendor/composer
cp vendor/autoload.php release-package/vendor/
cp vendor/composer/autoload_*.php release-package/vendor/composer/
cp vendor/composer/ClassLoader.php release-package/vendor/composer/
cp vendor/composer/InstalledVersions.php release-package/vendor/composer/

# 5. Create zip
cd release-package
zip -r ../woocommerce-support-helper-v1.0.0.zip . -x "*.git*" "*.DS_Store*"
```

## 📊 Package Size Optimization

### Before Optimization
- **Full vendor directory**: ~50MB+
- **Includes**: All development dependencies
- **Files**: 1000+ files

### After Optimization
- **Production vendor directory**: ~2-3MB
- **Includes**: Only production Composer autoloader files
- **Files**: ~50-100 files
- **Size reduction**: ~95% smaller

## ✅ Quality Checks

The release process includes several quality checks:

1. **File Structure Validation**
   - Essential files present
   - Correct directory structure
   - No development files included

2. **PHP Syntax Validation**
   - All PHP files have valid syntax
   - No parse errors

3. **Autoloader Testing**
   - Autoloader loads successfully
   - Main plugin class can be loaded

4. **Package Size Verification**
   - Package is reasonably sized
   - No unnecessary files included

5. **Dependency Verification**
   - Development dependencies excluded
   - Only runtime dependencies included

## 🚨 Troubleshooting

### Common Issues

#### 1. **Autoloader Not Working**
- Check that `vendor/autoload.php` is present
- Verify `vendor/composer/` directory has required files
- Ensure PHP version compatibility
- Run `composer install --no-dev --optimize-autoloader` to regenerate

#### 2. **Package Too Large**
- Check for development dependencies in vendor/
- Verify .gitignore is working correctly
- Run `composer install --no-dev` before packaging

#### 3. **Missing Files**
- Check that all essential files are copied
- Verify includes/ directory is complete
- Ensure documentation files are present

#### 4. **PHP Syntax Errors**
- Run `php -l` on all PHP files
- Check for missing semicolons or brackets
- Verify PHP version compatibility

### Getting Help

If you encounter issues with the release process:

1. Check the GitHub Actions logs
2. Run the local release script with verbose output
3. Verify all dependencies are installed
4. Check file permissions
5. Review the package contents manually

## 📝 Release Notes

Release notes are automatically generated from:
- Git commit messages
- Pull request descriptions
- Tag annotations
- RELEASE_NOTES_v*.md files

For manual release notes, create a `RELEASE_NOTES_v{VERSION}.md` file in the root directory.

## 🔄 Version Management

### Version Numbering
- Use semantic versioning (MAJOR.MINOR.PATCH)
- Examples: v1.0.0, v1.0.1, v1.1.0, v2.0.0

### Version Updates
1. Update version in `woocommerce-support-helper.php`
2. Update version constant `WC_SUPPORT_HELPER_VERSION`
3. Update version in plugin header
4. Commit changes
5. Create and push tag

### Changelog
- Keep `RELEASE_NOTES_v*.md` files for major versions
- Use GitHub releases for detailed changelog
- Include breaking changes, new features, and bug fixes
