# Shipping Methods Exporter

This module provides functionality to export shipping method configurations for WooCommerce Blueprint exports.

## Structure

- `class-shipping-methods-exporter.php` - Main coordinator class that manages all shipping exporters
- `woocommerce-shipping-australia-post/` - Australia Post shipping method exporter
- `woocommerce-shipping-fedex/` - FedEx shipping method exporter (placeholder)
- `woocommerce-shipping-royalmail/` - Royal Mail shipping method exporter (placeholder)
- `woocommerce-shipping-ups/` - UPS shipping method exporter (placeholder)
- `woocommerce-shipping-usps/` - USPS shipping method exporter (placeholder)
- `woocommerce-table-rate-shipping/` - Table Rate Shipping exporter (placeholder)

## How It Works

Each shipping method exporter is completely self-contained and implements the necessary methods:

- `is_plugin_active()` - Checks if the shipping plugin is active
- `get_site_options()` - Returns site options for Blueprint export
- `get_shipping_zone_configurations()` - Returns shipping zone configurations

The main coordinator class:
1. Loads individual exporters
2. Registers with the WooCommerce Blueprint system
3. Collects data from all exporters during export

## Adding New Exporters

To add a new shipping method exporter:

1. Create a new directory under `shipping-methods-exporter/`
2. Create a class that implements the required methods
3. Add the loading logic to `Shipping_Methods_Exporter::init_shipping_exporters()`

## Blueprint Integration

This module integrates with WooCommerce Blueprint by:
- Implementing the `StepExporter` interface
- Returning `SetSiteOptions` steps with shipping configuration data
- Hooking into the `wooblueprint_exporters` filter
