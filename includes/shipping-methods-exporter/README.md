# Shipping Methods Exporter Module

This module provides comprehensive export functionality for various WooCommerce shipping plugins through the WooCommerce Support Helper plugin.

## Purpose

The Shipping Methods Exporter module allows users to export shipping method configurations and settings from supported WooCommerce shipping plugins, making them available for import through the Blueprint system or other export mechanisms.

## Supported Shipping Plugins

- **Australia Post** (`woocommerce-shipping-australia-post`)
- **FedEx** (`woocommerce-shipping-fedex`)
- **Royal Mail** (`woocommerce-shipping-royalmail`)
- **UPS** (`woocommerce-shipping-ups`)
- **USPS** (`woocommerce-shipping-usps`)
- **Table Rate Shipping** (`woocommerce-table-rate-shipping`)

## Architecture

### Main Module Class
- **`class-shipping-methods-exporter.php`**: Coordinates all shipping exporters and integrates with Blueprint

### Abstract Base Class
- **`class-abstract-shipping-exporter.php`**: Defines common interface and methods for all shipping exporters

### Individual Exporters
Each supported shipping plugin has its own exporter class in a dedicated subfolder:
- `woocommerce-shipping-australia-post/`
- `woocommerce-shipping-fedex/`
- `woocommerce-shipping-royalmail/`
- `woocommerce-shipping-ups/`
- `woocommerce-shipping-usps/`
- `woocommerce-table-rate-shipping/`

## Features

- **Automatic Plugin Detection**: Only loads exporters for active shipping plugins
- **Blueprint Integration**: Seamlessly integrates with WooCommerce Blueprint export system
- **Data Sanitization**: Automatically removes sensitive information like API keys
- **Shipping Zone Support**: Exports shipping zone-specific configurations
- **Extensible Design**: Easy to add new shipping plugin support

## Integration Points

### Blueprint System
- Registers shipping exporters with Blueprint
- Provides shipping methods for export selection
- Handles shipping data export during Blueprint generation

### WooCommerce Admin
- Integrates with WooCommerce admin settings
- Adds shipping methods to Blueprint configuration
- Provides user interface for export selection

## Data Export

Each exporter provides:
- Plugin information and metadata
- Configuration settings (sanitized)
- Shipping zone configurations
- API settings (without sensitive data)
- Export metadata (timestamp, version)

## Security Features

- **Automatic Sanitization**: Removes sensitive data before export
- **Configurable Filtering**: Easy to customize what data is exported
- **Audit Trail**: Logs export activities for debugging

## Usage

The module is automatically loaded by the main plugin and integrates with WooCommerce Blueprint exports. No additional configuration is required.

## Extending

To add support for a new shipping plugin:

1. Create a new subfolder in `includes/shipping-methods-exporter/`
2. Create an exporter class extending `Abstract_Shipping_Exporter`
3. Implement the required abstract methods
4. Register the exporter in the main module class

## Dependencies

- WooCommerce 8.0+
- WordPress 6.0+
- PHP 7.4+
- Supported shipping plugins (optional)
