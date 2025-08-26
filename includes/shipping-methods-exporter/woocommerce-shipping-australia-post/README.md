# Australia Post Shipping Exporter

This exporter handles the export of Australia Post shipping method settings and configuration through the WooCommerce Support Helper plugin.

## Purpose

The Australia Post Shipping Exporter extracts configuration data from the WooCommerce Australia Post shipping plugin, making it available for export through the Blueprint system or other export mechanisms.

## Features

- **General Settings Export**: Exports main plugin configuration
- **Shipping Zone Settings**: Captures shipping zone-specific configurations
- **API Configuration**: Exports API-related settings (without sensitive data)
- **Data Sanitization**: Automatically removes sensitive information like API keys

## Exported Data

### General Settings
- Plugin configuration options
- Shipping method settings
- General shipping rules

### Shipping Zones
- Zone-specific Australia Post configurations
- Method settings for each zone
- Shipping rules and restrictions

### API Configuration
- API key presence (not the actual key)
- Debug mode settings
- Test mode configuration

## Security

The exporter automatically sanitizes sensitive data:
- API keys are removed
- Passwords are excluded
- Private tokens are filtered out
- Only configuration structure is exported

## Usage

This exporter is automatically loaded by the Shipping Methods Exporter module when the Australia Post plugin is active.

## Dependencies

- WooCommerce Australia Post shipping plugin
- WooCommerce 8.0+
- WordPress 6.0+
- PHP 7.4+
