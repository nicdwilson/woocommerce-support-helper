<?php
/**
 * Simple test script for Australia Post exporter
 */

echo "Starting test script...\n";

// Load the autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "Autoloader loaded\n";

echo "Testing Australia Post Exporter\n";
echo "===============================\n\n";

try {
    echo "Attempting to create exporter...\n";
    
    // Test the actual class
    $exporter = new \WooCommerceSupportHelper\WooCommerce_Shipping_Australia_Post();
    echo "✓ Exporter initialized successfully\n";
    echo "  - Step Name: " . $exporter->get_step_name() . "\n";
    echo "  - Alias: " . $exporter->get_alias() . "\n";
    echo "  - Plugin Slug: " . $exporter->get_plugin_slug() . "\n";
    echo "  - Method ID: " . $exporter->get_method_id() . "\n\n";
    
    // Test site options export
    echo "Testing Site Options Export:\n";
    $site_options = $exporter->get_site_options();
    echo "  - Total site options: " . count($site_options) . "\n";
    
    if (!empty($site_options)) {
        foreach ($site_options as $option_name => $option_value) {
            echo "    - {$option_name}: " . (is_array($option_value) ? count($option_value) . " items" : "value present") . "\n";
        }
    } else {
        echo "    - No site options found\n";
    }
    
    echo "\n";
    
    // Test shipping zones
    echo "Testing Shipping Zones:\n";
    $zones = $exporter->get_shipping_zones_with_australia_post();
    echo "  - Zones with Australia Post: " . count($zones) . "\n";
    
    if (!empty($zones)) {
        foreach ($zones as $zone) {
            echo "    - Zone: {$zone['zone_name']} (ID: {$zone['zone_id']}, Method Instance: {$zone['method_instance_id']})\n";
        }
    } else {
        echo "    - No zones with Australia Post found\n";
    }
    
    echo "\n";
    
    // Test method settings
    echo "Testing Method Settings:\n";
    $method_settings = $exporter->get_method_settings();
    echo "  - Method settings sections: " . count($method_settings) . "\n";
    
    foreach ($method_settings as $section => $data) {
        echo "    - {$section}: " . (is_array($data) ? count($data) . " items" : "data present") . "\n";
    }
    
    echo "\n";
    
    // Test the actual export
    echo "Testing Export:\n";
    $step = $exporter->export();
    echo "  - Export step type: " . get_class($step) . "\n";
    
    if (method_exists($step, 'get_data')) {
        $step_data = $step->get_data();
        echo "  - Step data keys: " . implode(', ', array_keys($step_data)) . "\n";
    }
    
    echo "\n✓ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} catch (Error $e) {
    echo "✗ Fatal Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nTest completed!\n";
