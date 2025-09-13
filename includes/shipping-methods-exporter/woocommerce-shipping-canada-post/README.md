# Canada Post Shipping Exporter

This exporter handles the export of WooCommerce Canada Post shipping method configurations for Blueprint exports.

## Overview

Canada Post Shipping provides integration with Canada Post's shipping services, allowing store owners to:
- Calculate real-time shipping rates
- Print shipping labels
- Track packages
- Access various Canada Post services

## Supported Data

### Site Options
- `woocommerce_canada_post_settings` - Global Canada Post shipping settings
- `woocommerce_canada_post_{instance_id}_settings` - Per-method settings for each shipping zone

### Shipping Zone Configurations
- Zone-specific method settings
- Instance-specific configurations
- Service preferences per zone

## Plugin Detection

The exporter detects Canada Post shipping through:
- Plugin file path: `woocommerce-shipping-canada-post/woocommerce-shipping-canada-post.php`
- Method ID: `canada_post`

## Data Sanitization

The exporter sanitizes sensitive data while preserving configuration structure:
- API keys and credentials are replaced with `***CONFIGURED***`
- Customer numbers and contract IDs are anonymized
- Configuration structure is maintained for accurate recreation

### Sensitive Fields Sanitized
- `api_key`
- `api_password`
- `password`
- `secret`
- `token`
- `auth_key`
- `private_key`
- `user_id`
- `api_secret`
- `account_number`
- `access_key`
- `username`
- `key`
- `account`
- `client_id`
- `client_secret`
- `customer_number`
- `contract_id`
- `merchant_id`

## Export Structure

```php
[
    'site_options' => [
        'woocommerce_canada_post_settings' => [...],
        'woocommerce_canada_post_1_settings' => [...],
        'woocommerce_canada_post_2_settings' => [...]
    ],
    'shipping_zones' => [
        [
            'zone_id' => 1,
            'zone_name' => 'Canada',
            'method_id' => 'canada_post',
            'method_instance_id' => 1,
            'method_settings' => [...]
        ]
    ],
    'method_settings' => [
        'general' => [...],
        'packing' => [...],
        'services' => [...],
        'advanced' => [...]
    ]
]
```

## Settings Categories

### General Settings
- Basic configuration options
- Default settings
- General preferences

### Packing Settings
- Package dimensions
- Weight settings
- Packaging options
- Box configurations

### Services Settings
- Available shipping services
- Domestic vs international
- Delivery options
- Rate calculations

### Advanced Settings
- Debug options
- Tax handling
- Insurance settings
- Free shipping thresholds

## Usage

The exporter is automatically loaded by the `Shipping_Methods_Exporter` class when the Canada Post shipping plugin is active. It implements the `StepExporter` interface and integrates with the WooCommerce Blueprint system.

## Testing

To test the Canada Post shipping exporter:

1. Ensure the Canada Post shipping plugin is installed and active
2. Configure some Canada Post shipping methods in shipping zones
3. Run the exporter to verify data collection and sanitization
4. Check that all Canada Post configurations are properly exported

## Compatibility

This exporter is designed to work with:
- WooCommerce Canada Post Shipping (official plugin)
- Third-party Canada Post shipping plugins
- Custom Canada Post implementations that follow WooCommerce standards

## Notes

- The exporter handles both global settings and per-method configurations
- Sensitive information is sanitized while preserving configuration structure
- The exporter gracefully handles cases where Canada Post shipping is not active
- Supports multiple shipping zones with different Canada Post configurations
