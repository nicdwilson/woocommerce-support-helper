#!/bin/bash

# WooCommerce Support Helper - Release Package Creator
# This script creates a clean release package excluding development dependencies

set -e

echo "🚀 Creating WooCommerce Support Helper Release Package"
echo "=================================================="

# Check if we're in the right directory
if [ ! -f "woocommerce-support-helper.php" ]; then
    echo "❌ Error: Please run this script from the plugin root directory"
    exit 1
fi

# Get version from main plugin file
VERSION=$(grep "Version:" woocommerce-support-helper.php | sed 's/.*Version: *//' | tr -d ' ')
echo "📦 Version: $VERSION"

# Create release directory
RELEASE_DIR="release-package"
echo "📁 Creating release directory: $RELEASE_DIR"
rm -rf $RELEASE_DIR
mkdir -p $RELEASE_DIR

# Copy essential files
echo "📋 Copying essential files..."

# Main plugin file
cp woocommerce-support-helper.php $RELEASE_DIR/

# Includes directory
cp -r includes $RELEASE_DIR/

# Documentation
cp README.md $RELEASE_DIR/
cp LICENSE $RELEASE_DIR/
[ -f "RELEASE_NOTES_v$VERSION.md" ] && cp "RELEASE_NOTES_v$VERSION.md" $RELEASE_DIR/

# Composer files for reference
cp composer.json $RELEASE_DIR/
cp composer.lock $RELEASE_DIR/

# Coding standards config
cp phpcs.xml $RELEASE_DIR/

# Create minimal vendor directory
echo "🔧 Setting up minimal vendor directory..."
mkdir -p $RELEASE_DIR/vendor

# Use our minimal autoloader instead of Composer's
cp create-minimal-autoloader.php $RELEASE_DIR/vendor/autoload.php

# Create .gitignore for release
echo "📝 Creating .gitignore for release..."
cat > $RELEASE_DIR/.gitignore << 'EOF'
# Development dependencies
vendor/phpunit/
vendor/sebastian/
vendor/phar-io/
vendor/theseer/
vendor/squizlabs/
vendor/wp-coding-standards/
vendor/phpcsstandards/
vendor/dealerdirect/
vendor/nikic/
vendor/doctrine/
vendor/myclabs/
vendor/composer/bin/
vendor/composer/installers/

# Keep only essential files
!vendor/autoload.php
!vendor/composer/autoload_*.php
!vendor/composer/ClassLoader.php
!vendor/composer/InstalledVersions.php

# Development files
.github/
.git/
tests/
node_modules/
*.log
.DS_Store
Thumbs.db
EOF

# Verify package structure
echo "🔍 Verifying package structure..."
echo "=== Package Contents ==="
find $RELEASE_DIR -type f | head -20
echo "..."
TOTAL_FILES=$(find $RELEASE_DIR -type f | wc -l)
echo "Total files: $TOTAL_FILES"

# Check essential files
echo "=== Essential Files Check ==="
[ -f "$RELEASE_DIR/woocommerce-support-helper.php" ] && echo "✅ Main plugin file"
[ -f "$RELEASE_DIR/vendor/autoload.php" ] && echo "✅ Autoloader"
[ -d "$RELEASE_DIR/includes" ] && echo "✅ Includes directory"
[ -f "$RELEASE_DIR/README.md" ] && echo "✅ README"
[ -f "$RELEASE_DIR/LICENSE" ] && echo "✅ License"

# Check for excluded dev dependencies
echo "=== Development Dependencies Check ==="
[ ! -d "$RELEASE_DIR/vendor/phpunit" ] && echo "✅ PHPUnit excluded"
[ ! -d "$RELEASE_DIR/vendor/squizlabs" ] && echo "✅ PHPCS excluded"
[ ! -d "$RELEASE_DIR/vendor/wp-coding-standards" ] && echo "✅ WPCS excluded"

# Test PHP syntax
echo "=== PHP Syntax Check ==="
SYNTAX_ERRORS=0
for file in $(find $RELEASE_DIR -name "*.php"); do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo "❌ Syntax error in $file"
        SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
    fi
done

if [ $SYNTAX_ERRORS -eq 0 ]; then
    echo "✅ All PHP files have valid syntax"
else
    echo "❌ Found $SYNTAX_ERRORS PHP syntax errors"
    exit 1
fi

# Test autoloader
echo "=== Autoloader Test ==="
cd $RELEASE_DIR
if php -r "require_once 'vendor/autoload.php'; echo '✅ Autoloader loads successfully' . PHP_EOL;" 2>/dev/null; then
    echo "✅ Autoloader works correctly"
else
    echo "❌ Autoloader test failed"
    exit 1
fi
cd ..

# Create zip package
PACKAGE_NAME="woocommerce-support-helper-v$VERSION.zip"
echo "📦 Creating zip package: $PACKAGE_NAME"
cd $RELEASE_DIR
zip -r "../$PACKAGE_NAME" . -x "*.git*" "*.DS_Store*" "Thumbs.db" > /dev/null
cd ..

# Package size report
PACKAGE_SIZE=$(du -sh $PACKAGE_NAME | cut -f1)
echo "📊 Package size: $PACKAGE_SIZE"

# Final verification
echo "=== Final Package Verification ==="
echo "Package: $PACKAGE_NAME"
echo "Size: $PACKAGE_SIZE"
echo "Files: $(unzip -l $PACKAGE_NAME | tail -1 | awk '{print $2}')"
echo "Essential vendor files: $(unzip -l $PACKAGE_NAME | grep vendor | wc -l)"

echo ""
echo "🎉 Release package created successfully!"
echo "📁 Package: $PACKAGE_NAME"
echo "📊 Size: $PACKAGE_SIZE"
echo "📋 Files: $(unzip -l $PACKAGE_NAME | tail -1 | awk '{print $2}')"
echo ""
echo "✅ Ready for distribution!"
