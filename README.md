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

1. Create a new class in `includes/Exporters/` that extends `AbstractExporter`
2. For order-related exporters, extend `AbstractOrderExporter` instead
3. Implement the required abstract methods:
   - `get_label()`
   - `get_description()`
   - `get_alias()`
   - `export()`
4. Register your exporter in the `Plugin::init_exporters()` method

Example order exporter:

```php
namespace HappyPlugins\BlueprintExporter\Exporters;

use Automattic\WooCommerce\Blueprint\Steps\SetSiteOptions;

class MyOrderExporter extends AbstractOrderExporter {
    public function get_label(): string {
        return __('My Order Settings', 'happy-blueprint-exporter');
    }

    public function get_description(): string {
        return __('Exports my custom order settings', 'happy-blueprint-exporter');
    }

    public function get_alias(): string {
        return 'setMyOrderSettings';
    }

    public function export(): SetSiteOptions {
        $settings = [
            'orders' => $this->get_orders([
                'status' => ['completed', 'processing'],
                'limit' => 100,
            ]),
            'custom_option' => get_option('my_custom_option'),
        ];

        return new SetSiteOptions($settings);
    }
}
```

### HPOS Support

The plugin is fully compatible with WooCommerce's High-Performance Order Storage (HPOS) system. When creating order-related exporters:

1. Extend `AbstractOrderExporter` instead of `AbstractExporter`
2. Use the provided methods to handle orders:
   - `get_order_data()` - Gets order data using the appropriate storage method
   - `get_orders()` - Gets orders using the appropriate storage method
   - `is_hpos_enabled()` - Checks if HPOS is enabled

The exporter will automatically use the correct storage method (HPOS or legacy) based on the store's configuration.

## Directory Structure

```
happy-blueprint-exporter/
├── includes/
│   ├── Exporters/
│   │   ├── AbstractExporter.php
│   │   └── AbstractOrderExporter.php
│   └── PrivatePluginExporter.php
├── languages/
├── tests/
├── vendor/
├── composer.json
├── happy-blueprint-exporter.php
└── README.md
```

## License

This project is licensed under the GPL v2 or later.

## Support

For support, please visit [Happy Plugins Support](https://happyplugins.com/support).