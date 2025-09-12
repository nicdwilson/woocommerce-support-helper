<?php
/**
 * Test UPS Exporter
 *
 * @package WooCommerceSupportHelper\Tests
 * @since 1.0.0
 */

// Mock WordPress functions if not available
if ( ! function_exists( 'is_plugin_active' ) ) {
    function is_plugin_active( $plugin ) {
        return false; // Mock as inactive for testing
    }
}

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return $default; // Mock as empty for testing
    }
}

if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability ) {
        return true; // Mock as having capabilities for testing
    }
}

// Load WordPress
require_once __DIR__ . '/../vendor/autoload.php';

// Include the UPS exporter class directly
require_once __DIR__ . '/../includes/shipping-methods-exporter/woocommerce-shipping-ups/class-woocommerce-shipping-ups.php';

echo "Testing UPS Exporter...\n\n";

try {
    // Test 1: Instantiate the exporter
    echo "1. Testing instantiation...\n";
    $ups_exporter = new \WooCommerceSupportHelper\WooCommerce_Shipping_Ups();
    echo "   ✓ UPS exporter instantiated successfully\n";
    echo "   Plugin slug: " . $ups_exporter->get_plugin_slug() . "\n";
    echo "   Method ID: " . $ups_exporter->get_method_id() . "\n";
    echo "   Alias: " . $ups_exporter->get_alias() . "\n\n";

    // Test 2: Check plugin active status
    echo "2. Testing plugin active check...\n";
    $is_active = $ups_exporter->is_plugin_active();
    echo "   Plugin active: " . ( $is_active ? 'Yes' : 'No' ) . "\n";
    if ( ! $is_active ) {
        echo "   Note: UPS plugin is not active, but exporter can still be tested\n";
    }
    echo "\n";

    // Test 3: Test site options export
    echo "3. Testing site options export...\n";
    $site_options = $ups_exporter->get_site_options();
    echo "   Site options count: " . count( $site_options ) . "\n";
    if ( ! empty( $site_options ) ) {
        echo "   Options found:\n";
        foreach ( array_keys( $site_options ) as $option_name ) {
            echo "     - $option_name\n";
        }
    } else {
        echo "   No site options found (plugin not active or no settings configured)\n";
    }
    echo "\n";

    // Test 4: Test shipping zone configurations
    echo "4. Testing shipping zone configurations...\n";
    $zone_configs = $ups_exporter->get_shipping_zone_configurations();
    echo "   Zone configurations count: " . count( $zone_configs ) . "\n";
    if ( ! empty( $zone_configs ) ) {
        echo "   Zone configurations found:\n";
        foreach ( $zone_configs as $config ) {
            echo "     - Zone ID: {$config['zone_id']}, Name: {$config['zone_name']}\n";
        }
    } else {
        echo "   No zone configurations found (plugin not active or no zones configured)\n";
    }
    echo "\n";

    // Test 5: Test method settings
    echo "5. Testing method settings...\n";
    $method_settings = $ups_exporter->get_method_settings();
    echo "   Method settings categories: " . implode( ', ', array_keys( $method_settings ) ) . "\n";
    echo "   General settings count: " . count( $method_settings['general'] ) . "\n";
    echo "   Services settings count: " . count( $method_settings['services'] ) . "\n";
    echo "   Packaging settings count: " . count( $method_settings['packaging'] ) . "\n";
    echo "   Advanced settings count: " . count( $method_settings['advanced'] ) . "\n\n";

    // Test 6: Test capability check
    echo "6. Testing capability check...\n";
    $has_capabilities = $ups_exporter->check_step_capabilities();
    echo "   User has required capabilities: " . ( $has_capabilities ? 'Yes' : 'No' ) . "\n\n";

    // Test 7: Test full export data
    echo "7. Testing full export data...\n";
    $export_data = $ups_exporter->export_data();
    echo "   Export data keys: " . implode( ', ', array_keys( $export_data ) ) . "\n";
    echo "   Site options in export: " . count( $export_data['site_options'] ) . "\n";
    echo "   Shipping zones in export: " . count( $export_data['shipping_zones'] ) . "\n";
    echo "   Method settings in export: " . count( $export_data['method_settings'] ) . "\n\n";

    echo "✓ All tests completed successfully!\n";

} catch ( Exception $e ) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
