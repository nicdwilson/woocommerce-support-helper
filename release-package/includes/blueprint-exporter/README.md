# Blueprint Exporter Module

This module extends WooCommerce's Blueprint exporter functionality with intelligent private plugin filtering.

## Purpose

The Blueprint Exporter module ensures that when creating WooCommerce Blueprint exports, only private plugins that are available via updaters (like WooCommerce.com extensions) are included. This prevents blueprint import failures due to unavailable plugins.

## Components

### `class-blueprint-exporter.php`
Main module class that coordinates all exporters and provides the module interface.

### `class-abstract-exporter.php`
Abstract base class for all exporters in this module.

### `class-private-plugin-exporter.php`
Concrete implementation for handling private plugin exports with intelligent filtering.

## Features

- **Smart Plugin Filtering**: Only includes plugins available via updaters
- **Environment Awareness**: Different behavior for staging vs production
- **WooCommerce.com Integration**: Checks plugin subscriptions before inclusion
- **WordPress.org Support**: Automatically includes WordPress.org plugins
- **Blueprint Integration**: Hooks into WooCommerce Blueprint exporter system

## Usage

The module is automatically loaded by the main plugin and integrates with WooCommerce Blueprint exports. No additional configuration is required.

## Extending

To add new exporters:

1. Create a new class extending `Abstract_Exporter`
2. Implement the required abstract methods
3. Register the exporter with the main `Blueprint_Exporter` class

## Dependencies

- WooCommerce 8.0+
- WordPress 6.0+
- PHP 7.4+
