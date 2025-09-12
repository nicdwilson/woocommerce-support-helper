# WooCommerce Support Helper

A WordPress plugin that extends WooCommerce Blueprint functionality with intelligent private plugin filtering and comprehensive shipping method exports.

## Description

WooCommerce Support Helper enhances WooCommerce Blueprint exports by intelligently filtering private plugins and providing comprehensive shipping method configuration exports. It ensures successful blueprint imports by only including plugins that are available via updaters (like WooCommerce.com extensions).

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- WooCommerce 8.0 or higher

## Features

### Core Features
- **Modular Architecture**: Component-based design for easy extension
- **Module Loader**: Centralized module management system
- **Full HPOS Support**: Compatible with WooCommerce's High-Performance Order Storage

### Blueprint Exporter Module
- **Intelligent Plugin Filtering**: Only exports private plugins available via updaters
- **Environment Awareness**: Different behavior for staging vs production
- **WooCommerce.com Integration**: Checks plugin subscriptions before inclusion
- **WordPress.org Support**: Automatically includes WordPress.org plugins
- **Blueprint Integration**: Seamless integration with WooCommerce Blueprint system

## Installation

### From Release Package (Recommended)
1. Download the latest release from [GitHub Releases](https://github.com/happyplugins/woocommerce-support-helper/releases)
2. Upload the `woocommerce-support-helper` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

### From Source (Development)
1. Clone the repository: `git clone https://github.com/happyplugins/woocommerce-support-helper.git`
2. Run `composer install` to install dependencies
3. Upload the plugin folder to `/wp-content/plugins/`
4. Activate the plugin through WordPress admin

## Module System

The plugin uses a modular architecture where each major feature is organized into its own module:

### Current Modules

#### Blueprint Exporter (`includes/blueprint-exporter/`)
- Extends WooCommerce Blueprint exporter functionality
- Provides intelligent private plugin filtering
- Ensures successful blueprint imports

#### Shipping Methods Exporter (`includes/shipping-methods-exporter/`)
- Exports shipping method settings for various WooCommerce shipping plugins
- Currently supports: 
    1. [USPS Shipping Method for WooCommerce](https://woocommerce.com/products/usps-shipping-method/)
    2. [UPS Shipping Method for WooCommerce](https://woocommerce.com/products/ups-shipping-method/)
    3. [FedEx Shipping Method for WooCommerce](https://woocommerce.com/products/fedex-shipping-module/)
    4. [Australia Post Shipping Method for WooCommerce](https://woocommerce.com/products/australia-post-shipping-method/)
- Planned support: Royal Mail,Table Rate Shipping, Canada Post, etc.
- Integrates with WooCommerce Blueprint system


### Adding New Modules

To add a new module:

1. Create a new directory in `includes/` (e.g., `includes/your-module/`)
2. Create a main module class (e.g., `class-your-module.php`)
3. Register the module in `includes/class-module-loader.php`
4. Follow the established naming conventions and structure

## Development

### Prerequisites

- Composer
- PHP 7.4 or higher

### Setup

1. Clone the repository
2. Run `composer install` to install PHP dependencies
3. Run `composer test:manual` to test the plugin
4. Run `composer test` to run the test suite (when configured)
5. Run `composer phpcs` to check coding standards

### Project Structure

```
woocommerce-support-helper/
├── woocommerce-support-helper.php     # Main plugin file
├── composer.json                       # Dependencies and autoloading
├── create-release.sh                  # Release package creation script
├── includes/                           # Core plugin classes and modules
│   ├── class-module-loader.php        # Module management system
│   ├── class-logger.php               # Logging utility
│   ├── class-support-helper-api.php   # REST API endpoints
│   ├── blueprint-exporter/            # Blueprint Exporter module
│   │   ├── class-blueprint-exporter.php      # Main module class
│   │   ├── class-abstract-exporter.php       # Abstract base class
│   │   ├── class-private-plugin-exporter.php # Private plugin handling
│   │   ├── class-custom-export-schema.php    # Custom export schema
│   │   ├── class-custom-rest-api.php         # REST API integration
│   │   └── README.md                  # Module documentation
│   └── shipping-methods-exporter/     # Shipping Methods Exporter module
│       ├── class-shipping-methods-exporter.php # Main module class
│       ├── woocommerce-shipping-australia-post/ # Australia Post exporter
│       ├── woocommerce-shipping-usps/        # USPS exporter
│       └── README.md                  # Module documentation
├── tests/                             # Test files
│   ├── test-instantiate.php          # Class instantiation tests
│   ├── test-simple.php               # Basic functionality tests
│   ├── test-load.php                 # Loading tests
│   └── README.md                     # Testing documentation
├── vendor/                            # Composer dependencies (production only)
└── .gitignore                         # Git ignore rules
```

### Testing

```bash
# Run manual tests
composer test:manual

# Run PHPUnit tests (when configured)
composer test

# Check coding standards
composer phpcs
```

## Technical Details

### Autoloading
The plugin uses Composer's optimized autoloader with PSR-4 and classmap support for efficient class loading. The release package includes only production dependencies, resulting in a lightweight distribution.

### Release Process
Releases are created using the `create-release.sh` script, which:
- Installs production dependencies only
- Generates an optimized autoloader
- Creates a clean package excluding development files
- Validates package structure and functionality

## License

This project is licensed under the GPL v2 or later.

## Support

- **Documentation**: This README and module-specific documentation
- **Issues**: [GitHub Issues](https://github.com/happyplugins/woocommerce-support-helper/issues)
- **Support**: [Happy Plugins Support](https://happyplugins.com/support)
