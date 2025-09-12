# Tests Directory

This directory contains test files for the WooCommerce Support Helper plugin.

## Test Files

### `test-load.php`
Basic class loading test that verifies all plugin classes can be loaded via the autoloader.

### `test-simple.php`
Simple class existence test that checks if all required classes are available.

### `test-instantiate.php`
Class instantiation test that attempts to create instances of the main plugin classes.

## Running Tests

### Manual Tests
```bash
# Run the instantiation test
php tests/test-instantiate.php

# Run the simple test
php tests/test-simple.php

# Run the load test
php tests/test-load.php
```

### Using Composer
```bash
# Run the manual test via composer
composer test:manual

# Run PHPUnit tests (when configured)
composer test

# Run coding standards check
composer phpcs
```

## Test Purpose

These tests are designed to:
1. Verify that the plugin classes can be loaded correctly
2. Ensure the Composer autoloader is working with the PSR-4 namespace
3. Validate that classes can be instantiated without errors
4. Provide a quick way to test the plugin during development
5. Verify the production autoloader works correctly

## Notes

- These are basic functional tests, not comprehensive unit tests
- They primarily verify the plugin structure and autoloading
- For full testing, use PHPUnit with proper test cases
- Tests should be run after any namespace or structural changes
- Tests validate both development and production autoloader configurations

## Autoloader Testing

The tests specifically verify:
- ✅ PSR-4 namespace resolution (`WooCommerceSupportHelper\`)
- ✅ Classmap autoloading for non-PSR-4 classes
- ✅ Production autoloader optimization
- ✅ Release package autoloader functionality
