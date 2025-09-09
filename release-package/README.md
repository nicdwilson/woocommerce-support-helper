# WooCommerce Support Helper

A modular WordPress plugin that extends WooCommerce with additional functionality for Happy Plugins.

## Description

WooCommerce Support Helper is a modular WordPress plugin that extends WooCommerce functionality through a component-based architecture. It currently includes the Blueprint Exporter module, which intelligently filters private plugins during WooCommerce Blueprint exports.

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

1. Upload the `woocommerce-support-helper` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Run `composer install` in the plugin directory to install dependencies

## Module System

The plugin uses a modular architecture where each major feature is organized into its own module:

### Current Modules

#### Blueprint Exporter (`includes/blueprint-exporter/`)
- Extends WooCommerce Blueprint exporter functionality
- Provides intelligent private plugin filtering
- Ensures successful blueprint imports

#### Shipping Methods Exporter (`includes/shipping-methods-exporter/`)
- Exports shipping method settings for various WooCommerce shipping plugins
- Supports multiple shipping providers (Australia Post, FedEx, Royal Mail, UPS, USPS)
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
├── includes/                           # Core plugin classes and modules
│   ├── class-module-loader.php        # Module management system
│   ├── class-logger.php               # Logging utility
│   ├── blueprint-exporter/            # Blueprint Exporter module
│   │   ├── class-blueprint-exporter.php      # Main module class
│   │   ├── class-abstract-exporter.php       # Abstract base class
│   │   ├── class-private-plugin-exporter.php # Private plugin handling
│   │   └── README.md                  # Module documentation
│   ├── shipping-methods-exporter/     # Shipping Methods Exporter module
│   │   ├── class-shipping-methods-exporter.php # Main module class
│   │   └── README.md                  # Module documentation
├── tests/                             # Test files
│   ├── test-instantiate.php          # Class instantiation tests
│   ├── test-simple.php               # Basic functionality tests
│   ├── test-load.php                 # Loading tests
│   └── README.md                     # Testing documentation
├── vendor/                            # Composer dependencies
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

## License

This project is licensed under the GPL v2 or later.
