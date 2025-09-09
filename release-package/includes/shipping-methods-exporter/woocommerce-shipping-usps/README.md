# USPS Shipping Method Exporter

This module extends the WooCommerce Support Helper to properly export USPS shipping method settings for WooCommerce Blueprint integration.

## Overview

The USPS shipping method stores its configuration in two places:
1. **Global settings**: Stored in the `woocommerce_usps_settings` site option
2. **Per-method settings**: Stored in individual shipping zone method options like `woocommerce_usps_{instance_id}_settings`

This exporter captures both types of settings and formats them for inclusion in WooCommerce Blueprint exports.

## Features

- **Site Options Export**: Exports USPS shipping method settings as site options that can be included in Blueprint exports
- **Shipping Zone Integration**: Captures shipping zone-specific method configurations
- **Data Sanitization**: Removes sensitive information (like user IDs) while preserving configuration structure
- **Blueprint Integration**: Seamlessly integrates with the WooCommerce Blueprint export system
- **Demo Data Support**: Provides realistic demo data for testing when no real configuration exists

## How It Works

### 1. Site Options Export

The exporter identifies and exports the following site options:

- `woocommerce_usps_settings` - Global USPS plugin settings
- `woocommerce_usps_{instance_id}_settings` - Individual shipping zone method settings

### 2. Shipping Zone Configurations

For each shipping zone that contains a USPS shipping method, the exporter captures:

- Zone ID and name
- Method ID (always 'usps')
- Method settings (packaging, services, rates, etc.)

### 3. Data Sanitization

The exporter sanitizes sensitive data:

- **User IDs**: Replaced with `***CONFIGURED***` placeholder to indicate they were configured
- **Passwords/Secrets**: Completely removed
- **Configuration Data**: Preserved for accurate blueprint recreation

### 4. Demo Data

When no real USPS configuration is found, the exporter provides realistic demo data including:

- Domestic services (Priority Mail, First-Class, Media Mail, Library Mail)
- International services (Global Express, Priority International, First-Class International)
- Box weight configurations
- Handling fees and shipping thresholds
- Tax and free shipping settings

## Integration with Blueprint System

The exporter integrates with the main shipping methods exporter through these methods:

- `get_site_options()` - Provides site options for Blueprint export
- `get_shipping_zone_configurations()` - Provides shipping zone configurations for Blueprint export

These methods are called by the main shipping methods exporter when it hooks into the Blueprint export process.

## Usage

### Basic Export

```php
$exporter = new \WooCommerceSupportHelper\WooCommerce_Shipping_Usps();

// Get site options for Blueprint
$site_options = $exporter->get_site_options();

// Get shipping zone configurations
$shipping_zones = $exporter->get_shipping_zone_configurations();

// Get full export data
$export_data = $exporter->export_data();
```

### Blueprint Integration

The exporter automatically integrates with the Blueprint system when the shipping methods exporter module is loaded. It hooks into:

- `wooblueprint_exporters` - To include USPS settings
- `wooblueprint_export_site_options` - To include site options
- `wooblueprint_export_shipping_zones` - To include shipping zone configurations

## Configuration Options

The exporter captures the following USPS settings:

### General Settings
- User ID configuration (with sanitized values)
- Origin postcode
- Debug mode settings
- Fallback handling preferences

### Service Settings
- Domestic service configurations
- International service options
- Rate offering strategies
- Service availability preferences

### Packaging Settings
- Box weight configurations
- Package type preferences
- Weight-based pricing options

### Advanced Settings
- Tax calculation preferences
- Handling fee configurations
- Free shipping thresholds
- Cost and amount limits

## Testing

The exporter includes comprehensive demo data for testing purposes. When no real USPS configuration exists, it will automatically provide realistic test data to ensure the export process works correctly.

## Requirements

- WordPress with WooCommerce
- USPS shipping plugin (optional - demo data provided if not active)
- WooCommerce Support Helper plugin loaded

## Dependencies

- `StepExporter` interface from WooCommerce Blueprint
- `HasAlias` interface for custom exporter aliases
- `SetSiteOptions` step for Blueprint integration
- WooCommerce shipping zones API
- WordPress options API

## Notes

- The exporter works whether the USPS plugin is active or not
- User IDs are sanitized to prevent exposure in exports
- All configuration data is preserved for accurate blueprint recreation
- Demo data is automatically provided for testing scenarios
- The exporter handles both single-site and multi-site WordPress installations

## Class Information

- **Class Name**: `WooCommerce_Shipping_Usps`
- **Plugin Slug**: `woocommerce-shipping-usps`
- **Method ID**: `usps`
- **Blueprint Alias**: `UspsOptions`
- **Step Type**: `SetSiteOptions`
