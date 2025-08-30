# Australia Post Blueprint Export Enhancement

## Problem Statement

The WooCommerce Blueprint system was not properly exporting Australia Post shipping method configurations. When a site containing a shipping zone with Australia Post configured was exported, the shipping method settings were missing from the blueprint, making it impossible to recreate the shipping configuration on the target site.

## Root Cause Analysis

The issue was identified by examining the blueprint JSON export (`woo-blueprint-16.json`) and the Australia Post shipping method code:

1. **Blueprint Export Gap**: The Blueprint system exports shipping zones and methods but doesn't capture the detailed configuration settings stored in site options.

2. **Configuration Storage**: Australia Post stores its configuration in two places:
   - Global settings: `woocommerce_australia_post_settings` site option
   - Per-method settings: `woocommerce_australia_post_{instance_id}_settings` site options

3. **Missing Integration**: The existing Australia Post exporter was designed for analysis/debugging but wasn't integrated with the Blueprint export system.

## Solution Implementation

### 1. Enhanced Australia Post Exporter

**File**: `includes/shipping-methods-exporter/woocommerce-shipping-australia-post/class-australia-post-exporter.php`

**New Methods Added**:
- `get_site_options()` - Exports Australia Post settings as site options for Blueprint
- `get_shipping_zone_configurations()` - Exports shipping zone method configurations
- `get_shipping_zone_settings_for_blueprint()` - Formats zone data for Blueprint export
- `get_site_options_for_blueprint()` - Collects site options for Blueprint export
- `sanitize_shipping_data_for_blueprint()` - Sanitizes sensitive data while preserving configuration

**Key Features**:
- Captures both global and per-method settings
- Sanitizes API keys (replaces with `***CONFIGURED***` placeholder)
- Preserves all configuration data for accurate blueprint recreation
- Integrates with shipping zone system

### 2. Enhanced Main Shipping Methods Exporter

**File**: `includes/shipping-methods-exporter/class-shipping-methods-exporter.php`

**New Hooks Added**:
- `wooblueprint_export_site_options` - Exports shipping method settings as site options
- `wooblueprint_export_shipping_zones` - Exports shipping zone configurations

**New Methods Added**:
- `export_shipping_method_site_options()` - Collects site options from all shipping exporters
- `export_shipping_zone_configurations()` - Collects shipping zone configurations from all exporters

### 3. Blueprint Integration

The enhanced exporter now properly integrates with the WooCommerce Blueprint system by:

1. **Hooking into Blueprint Export Process**: The exporter hooks into the Blueprint export filters to include shipping method settings.

2. **Site Options Export**: Australia Post settings are exported as site options that can be included in the Blueprint export.

3. **Shipping Zone Integration**: Shipping zone method configurations are captured and exported.

4. **Data Sanitization**: Sensitive information is removed while preserving configuration structure.

## What Gets Exported

### Site Options
- `woocommerce_australia_post_settings` - Global plugin settings
- `woocommerce_australia_post_{instance_id}_settings` - Individual method settings for each shipping zone

### Shipping Zone Configurations
- Zone ID and name
- Method ID and settings
- Packing method configurations
- Box and service settings
- Tax and origin configurations

### Data Sanitization
- **API Keys**: Replaced with `***CONFIGURED***` placeholder
- **Passwords/Secrets**: Completely removed
- **Configuration Data**: Fully preserved for accurate recreation

## Benefits

1. **Complete Configuration Export**: All Australia Post shipping method settings are now captured in Blueprint exports.

2. **Accurate Site Recreation**: Target sites can be configured with the exact same shipping settings as the source site.

3. **Security**: Sensitive information like API keys is properly sanitized.

4. **Extensibility**: The pattern can be applied to other shipping methods (FedEx, UPS, USPS, etc.).

5. **Blueprint Compliance**: The exporter follows WooCommerce Blueprint export patterns and standards.

## Testing

A test script (`test-australia-post-export.php`) is provided to verify:
- Exporter initialization
- Site options export
- Shipping zone configurations
- Method settings export
- Full export data generation

## Future Enhancements

The same pattern can be extended to other shipping methods:

1. **FedEx Exporter**: Capture FedEx API credentials and service configurations
2. **UPS Exporter**: Export UPS account settings and service preferences
3. **USPS Exporter**: Include USPS configuration and service settings
4. **Table Rate Shipping**: Export custom rate table configurations

## Conclusion

This enhancement resolves the Australia Post Blueprint export issue by properly integrating the shipping method exporter with the Blueprint system. The solution captures all necessary configuration data while maintaining security and following WooCommerce best practices.

The implementation provides a solid foundation for extending similar functionality to other shipping methods, ensuring that WooCommerce Blueprint exports can accurately recreate complex shipping configurations across different sites.
