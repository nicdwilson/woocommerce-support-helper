# Royal Mail Shipping Exporter

This exporter handles the export of WooCommerce Royal Mail shipping method configurations for Blueprint exports.

## Overview

Royal Mail Shipping provides integration with Royal Mail's shipping services, allowing store owners to:
- Calculate real-time shipping rates for UK and international destinations
- Print shipping labels
- Track packages
- Access various Royal Mail services including Special Delivery, Signed For, and standard services

## Supported Data

### Site Options
- `woocommerce_royal_mail_settings` - Global Royal Mail shipping settings
- `woocommerce_royal_mail_{instance_id}_settings` - Per-method settings for each shipping zone

### Shipping Zone Configurations
- Zone-specific method settings
- Instance-specific configurations
- Service preferences per zone

## Plugin Detection

The exporter detects Royal Mail shipping through:
- Plugin file path: `woocommerce-shipping-royalmail/woocommerce-shipping-royalmail.php`
- Method ID: `royal_mail`

## Data Sanitization

The exporter sanitizes sensitive data while preserving configuration structure:
- API keys and credentials are replaced with `***CONFIGURED***`
- Customer numbers and contract IDs are anonymized
- Postcodes are sanitized for privacy
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
- `postcode`
- `postal_code`

## Export Structure

```php
[
    'site_options' => [
        'woocommerce_royal_mail_settings' => [...],
        'woocommerce_royal_mail_1_settings' => [...],
        'woocommerce_royal_mail_2_settings' => [...]
    ],
    'shipping_zones' => [
        [
            'zone_id' => 1,
            'zone_name' => 'United Kingdom',
            'method_id' => 'royal_mail',
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
- Origin postcode settings

### Packing Settings
- Package dimensions
- Weight settings
- Packaging options
- Parcel configurations
- Box settings

### Services Settings
- Available shipping services
- Domestic vs international services
- Delivery options
- Rate calculations
- Tracking options
- Special services (Signed For, Special Delivery, etc.)

### Advanced Settings
- Debug options
- Tax handling
- Insurance settings
- Free shipping thresholds
- Signature requirements

## Royal Mail Services

The exporter supports various Royal Mail services including:
- **Standard Services**: 1st Class, 2nd Class
- **Signed For Services**: 1st Class Signed For, 2nd Class Signed For
- **Special Delivery**: Next Day, Saturday Guaranteed
- **International Services**: International Standard, International Tracked & Signed
- **Parcel Services**: Parcel Force, Royal Mail 24/48

## Usage

The exporter is automatically loaded by the `Shipping_Methods_Exporter` class when the Royal Mail shipping plugin is active. It implements the `StepExporter` interface and integrates with the WooCommerce Blueprint system.

## Testing

To test the Royal Mail shipping exporter:

1. Ensure the Royal Mail shipping plugin is installed and active
2. Configure some Royal Mail shipping methods in shipping zones
3. Run the exporter to verify data collection and sanitization
4. Check that all Royal Mail configurations are properly exported

## Compatibility

This exporter is designed to work with:
- WooCommerce Royal Mail Shipping (official plugin)
- Third-party Royal Mail shipping plugins
- Custom Royal Mail implementations that follow WooCommerce standards

## Notes

- The exporter handles both global settings and per-method configurations
- Sensitive information is sanitized while preserving configuration structure
- The exporter gracefully handles cases where Royal Mail shipping is not active
- Supports multiple shipping zones with different Royal Mail configurations
- Handles both domestic UK and international shipping services
- Supports various Royal Mail service types and delivery options
