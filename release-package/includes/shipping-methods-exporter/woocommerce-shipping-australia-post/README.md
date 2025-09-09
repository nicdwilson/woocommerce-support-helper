# Australia Post Shipping Method Exporter

This module extends the WooCommerce Support Helper to properly export Australia Post shipping method settings for WooCommerce Blueprint integration.

## Overview

The Australia Post shipping method stores its configuration in two places:
1. **Global settings**: Stored in the `woocommerce_australia_post_settings` site option
2. **Per-method settings**: Stored in individual shipping zone method options like `woocommerce_australia_post_{instance_id}_settings`

This exporter captures both types of settings and formats them for inclusion in WooCommerce Blueprint exports.

## Features

- **Site Options Export**: Exports Australia Post shipping method settings as site options that can be included in Blueprint exports
- **Shipping Zone Integration**: Captures shipping zone-specific method configurations
- **Data Sanitization**: Removes sensitive information (like API keys) while preserving configuration structure
- **Blueprint Integration**: Seamlessly integrates with the WooCommerce Blueprint export system

## How It Works

### 1. Site Options Export

The exporter identifies and exports the following site options:

- `woocommerce_australia_post_settings` - Global Australia Post plugin settings
- `woocommerce_australia_post_{instance_id}_settings` - Individual shipping zone method settings

### 2. Shipping Zone Configurations

For each shipping zone that contains an Australia Post shipping method, the exporter captures:

- Zone ID and name
- Method ID (always 'australia_post')
- Method settings (packing method, box configurations, service settings, etc.)

### 3. Data Sanitization

The exporter sanitizes sensitive data:

- **API Keys**: Replaced with `***CONFIGURED***` placeholder to indicate they were configured
- **Passwords/Secrets**: Completely removed
- **Configuration Data**: Preserved for accurate blueprint recreation

## Integration with Blueprint System

The exporter integrates with the main shipping methods exporter through these methods:

- `get_site_options()` - Provides site options for Blueprint export
- `get_shipping_zone_configurations()` - Provides shipping zone configurations for Blueprint export

These methods are called by the main shipping methods exporter when it hooks into the Blueprint export process.

## Usage

### Basic Export

```php
$exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();

// Get site options for Blueprint
$site_options = $exporter->get_site_options();

// Get shipping zone configurations
$shipping_zones = $exporter->get_shipping_zone_configurations();

// Get full export data
$export_data = $exporter->export_data();
```

### Blueprint Integration

The exporter automatically integrates with the Blueprint system when the shipping methods exporter module is loaded. It hooks into:

- `wooblueprint_export_site_options` - To include Australia Post settings
- `wooblueprint_export_shipping_zones` - To include shipping zone configurations

## Configuration Options

The exporter captures the following Australia Post settings:

### General Settings
- API configuration (with sanitized keys)
- Debug mode settings
- Box packer library preferences

### Packing Settings
- Packing method (per_item, per_order, weight_based, box_packing)
- Custom box configurations
- Maximum weight limits
- Default box sizes
- Letter size configurations

### Service Settings
- Custom service configurations
- Satchel rate settings
- Rate offering preferences
- Available service options
- Extra cover options
- Signature on delivery options

### Advanced Settings
- Tax exclusion settings
- Origin address configuration
- Tax status preferences

## Testing

A test script is provided at `test-australia-post-export.php` to verify the exporter functionality:

```bash
php test-australia-post-export.php
```

This script tests:
- Exporter initialization
- Site options export
- Shipping zone configurations
- Method settings export
- Full export data generation

## Requirements

- WordPress with WooCommerce
- Australia Post shipping plugin activated
- WooCommerce Support Helper plugin loaded

## Dependencies

- `Abstract_Shipping_Exporter` base class
- WooCommerce shipping zones API
- WordPress options API

## Notes

- The exporter only works when the Australia Post plugin is active
- API keys are sanitized to prevent exposure in exports
- All configuration data is preserved for accurate blueprint recreation
- The exporter handles both single-site and multi-site WordPress installations
