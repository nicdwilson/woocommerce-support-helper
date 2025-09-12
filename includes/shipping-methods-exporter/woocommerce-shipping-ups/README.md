# UPS Shipping Method Exporter

This directory contains the exporter for the WooCommerce UPS Shipping plugin.

## Files

- `class-woocommerce-shipping-ups.php` - Main exporter class that implements the UPS shipping method export functionality

## Features

- Exports global UPS shipping settings
- Exports zone-specific UPS shipping method configurations
- Sanitizes sensitive information (API keys, account numbers, access keys, etc.)
- Categorizes settings into logical groups (general, services, packaging, advanced)
- Provides demo data when no real settings are available
- Implements proper capability checks for export operations

## Usage

The exporter is automatically loaded by the main Shipping Methods Exporter class when the UPS plugin is active. It will appear in the Blueprint UI under "Include plugin settings" when the WooCommerce UPS Shipping plugin is installed and active.

## Plugin Requirements

- WooCommerce UPS Shipping plugin must be installed and active
- Plugin slug: `woocommerce-shipping-ups`
- Method ID: `ups`

## Exported Data

The exporter captures:
- Global UPS settings (`woocommerce_ups_settings`)
- Zone-specific method settings (`woocommerce_ups_{instance_id}_settings`)
- Shipping zone configurations with method settings
- Comprehensive method settings organized by category

## Security

All sensitive information is sanitized during export:
- API keys and secrets
- Account numbers
- Access keys and usernames
- Authentication tokens
- Passwords and private keys

Sensitive values are replaced with `***CONFIGURED***` to indicate that the setting was configured without exposing the actual values.

## UPS-Specific Features

The UPS exporter includes support for:
- Express, Ground, and Priority services
- Next Day and Second Day delivery options
- Worldwide shipping services
- COD (Cash on Delivery) options
- Delivery confirmation settings
- Insurance and signature options
