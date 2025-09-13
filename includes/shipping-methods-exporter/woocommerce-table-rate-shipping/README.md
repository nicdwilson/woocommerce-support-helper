# Table Rate Shipping Exporter

This exporter handles the export of WooCommerce Table Rate Shipping method configurations for Blueprint exports.

## Overview

Table Rate Shipping allows store owners to define multiple shipping rates based on various conditions such as:
- Destination (country, state, zip code)
- Weight ranges
- Price ranges
- Item count
- Shipping class

## Supported Data

### Site Options
- `woocommerce_table_rate_settings` - Global table rate shipping settings
- `woocommerce_table_rate_{instance_id}_settings` - Per-method settings for each shipping zone
- `woocommerce_table_rate_data` - Custom table rate data from database tables

### Database Tables
The exporter checks for and exports data from:
- `{prefix}_woocommerce_shipping_table_rates` - Main table rate data
- Additional table rate related options:
  - `woocommerce_table_rate_priorities`
  - `woocommerce_table_rate_conditions`
  - `woocommerce_table_rate_labels`

### Shipping Zone Configurations
- Zone-specific method settings
- Instance-specific configurations
- Rate table data per zone

## Plugin Detection

The exporter detects table rate shipping through multiple methods:
1. Checks for common plugin file paths:
   - `woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php`
   - `woocommerce-table-rate-shipping/table-rate-shipping.php`
2. Verifies if the `table_rate` shipping method is available in WooCommerce

## Data Sanitization

The exporter sanitizes sensitive data while preserving configuration structure:
- API keys and credentials are replaced with `***CONFIGURED***`
- Rate keys are anonymized
- Configuration structure is maintained for accurate recreation

## Export Structure

```php
[
    'site_options' => [
        'woocommerce_table_rate_settings' => [...],
        'woocommerce_table_rate_1_settings' => [...],
        'woocommerce_table_rate_data' => [
            'table_rates' => [...],
            'woocommerce_table_rate_priorities' => [...],
            'woocommerce_table_rate_conditions' => [...],
            'woocommerce_table_rate_labels' => [...]
        ]
    ],
    'shipping_zones' => [
        [
            'zone_id' => 1,
            'zone_name' => 'United States',
            'method_id' => 'table_rate',
            'method_instance_id' => 1,
            'method_settings' => [...]
        ]
    ],
    'method_settings' => [
        'general' => [...],
        'table_rates' => [...],
        'conditions' => [...],
        'advanced' => [...]
    ]
]
```

## Usage

The exporter is automatically loaded by the `Shipping_Methods_Exporter` class when the table rate shipping plugin is active. It implements the `StepExporter` interface and integrates with the WooCommerce Blueprint system.

## Testing

To test the table rate shipping exporter:

1. Ensure a table rate shipping plugin is installed and active
2. Configure some table rate shipping methods in shipping zones
3. Run the exporter to verify data collection and sanitization
4. Check that all table rate configurations are properly exported

## Compatibility

This exporter is designed to work with:
- WooCommerce Table Rate Shipping (official plugin)
- Third-party table rate shipping plugins
- Custom table rate implementations that follow WooCommerce standards

## Notes

- The exporter handles both global settings and per-method configurations
- Database table data is exported when available
- Sensitive information is sanitized while preserving configuration structure
- The exporter gracefully handles cases where table rate shipping is not active
