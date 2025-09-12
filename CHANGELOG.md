# Changelog

All notable changes to the WooCommerce Support Helper plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2024-12-19

### Changed
- **Logging Optimization**: Significantly reduced excessive debug logging throughout the plugin
- **Production-Ready Logs**: Now only logs successful major stages and errors
- **Improved Performance**: Reduced logging overhead by removing unnecessary debug statements

### Details
- **Blueprint Exporter**: Removed debug logs for every individual plugin check, now only logs filtering summaries
- **Shipping Exporters**: Removed debug logs for every zone/method discovery, now only logs export completion
- **Main Exporter**: Removed debug logs for every exporter loading step, now only logs major milestones
- **Net Reduction**: Removed 82 lines of excessive logging code across 4 files

### Before vs After
- **Before**: Logged every plugin check, subscription validation, zone discovery, etc.
- **After**: Only logs successful operations, summaries, and actual errors/warnings

## [0.1.0] - 2024-12-18

### Added
- **Initial Release**: WooCommerce Support Helper plugin
- **Blueprint Integration**: Extends WooCommerce Blueprint exporter with intelligent private plugin filtering
- **Private Plugin Filtering**: Only exports plugins available via updaters (WordPress.org, WooCommerce.com)
- **Shipping Methods Export**: Support for Australia Post and USPS shipping method configurations
- **Production Autoloader**: Robust Composer-based autoloading system
- **Automated Release Process**: Clean, optimized release package generation

### Features
- **Smart Plugin Detection**: Automatically detects and filters unavailable private plugins
- **Subscription Validation**: Validates WooCommerce.com plugin subscriptions
- **Environment Awareness**: Includes all plugins in staging/local environments
- **Modular Architecture**: Extensible exporter system for additional shipping methods
- **Blueprint UI Integration**: Seamless integration with WooCommerce Blueprint interface

### Technical Details
- **PHP 7.4+**: Modern PHP compatibility
- **WordPress 6.0+**: Latest WordPress support
- **WooCommerce 8.0+**: Compatible with latest WooCommerce versions
- **HPOS Compatible**: High-Performance Order Storage support
- **PSR-4 Autoloading**: Standard Composer autoloading
- **Production Optimized**: Minimal dependencies, optimized autoloader

---

## Version History

- **0.2.0**: Logging optimization and production-ready improvements
- **0.1.0**: Initial release with core functionality
