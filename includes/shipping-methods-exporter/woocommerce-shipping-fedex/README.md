# FedEx Shipping Method Exporter

This directory contains the exporter for the WooCommerce FedEx Shipping plugin.

## Files

- `class-woocommerce-shipping-fedex.php` - Main exporter class that implements the FedEx shipping method export functionality

## Features

- Exports global FedEx shipping settings
- Exports zone-specific FedEx shipping method configurations
- Sanitizes sensitive information (API keys, account numbers, etc.)
- Categorizes settings into logical groups (general, services, packaging, advanced)
- Provides demo data when no real settings are available
- Implements proper capability checks for export operations

## Usage

The exporter is automatically loaded by the main Shipping Methods Exporter class when the FedEx plugin is active. It will appear in the Blueprint UI under "Include plugin settings" when the WooCommerce FedEx Shipping plugin is installed and active.

## Plugin Requirements

- WooCommerce FedEx Shipping plugin must be installed and active
- Plugin slug: `woocommerce-shipping-fedex`
- Method ID: `fedex`

## Exported Data

The exporter captures:
- Global FedEx settings (`woocommerce_fedex_settings`)
- Zone-specific method settings (`woocommerce_fedex_{instance_id}_settings`)
- Shipping zone configurations with method settings
- Comprehensive method settings organized by category

## Security

All sensitive information is sanitized during export:
- API keys and secrets
- Account numbers
- Meter numbers
- Authentication tokens
- Passwords and private keys

Sensitive values are replaced with `***CONFIGURED***` to indicate that the setting was configured without exposing the actual values.
