# Happy Blueprint Exporter

Extends WooCommerce Blueprint exporter with additional functionality for Happy Plugins.

## Description

Happy Blueprint Exporter is a WordPress plugin that extends the WooCommerce Blueprint exporter functionality. It allows you to export and import settings from Happy Plugins products as part of your WooCommerce Blueprint export.

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- WooCommerce 9.9.3 or higher

## Features

- Extends WooCommerce Blueprint exporter
- Full support for HPOS (High-Performance Order Storage)
- Compatible with both traditional and custom order tables
- Modular exporter system for easy extension
- Automatically enables private plugin exports
- Includes private plugin settings in Blueprint exports

## Installation

1. Upload the `happy-blueprint-exporter` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Run `composer install` in the plugin directory to install dependencies

## Private Plugin Support

The plugin automatically enables private plugin exports in WooCommerce Blueprint. This means that when you create a Blueprint export:

1. All private plugins will be included in the export
2. Private plugin settings will be preserved
3. The export will work seamlessly with both public and private plugins

This is particularly useful for:
- Development environments
- Staging sites
- Multi-site setups
- Custom plugin deployments

Note: Make sure you have the necessary permissions and licenses to export private plugins.

## Development

### Prerequisites

- Composer
- PHP 7.4 or higher
- Node.js and npm (for frontend development)

### Setup

1. Clone the repository
2. Run `composer install` to install PHP dependencies
3. Run `composer test` to run the test suite
4. Run `composer phpcs` to check coding standards

### Building Exporters

To create a new exporter:

TBA

### HPOS Support

The plugin is fully compatible with WooCommerce's High-Performance Order Storage (HPOS) system. When creating order-related exporters:


## Directory Structure

TBA

## License

This project is licensed under the GPL v2 or later.

## Support

For support, please visit [Happy Plugins Support](https://happyplugins.com/support).