<?php
/**
 * Test script for USPS exporter
 * 
 * This script tests the USPS shipping method exporter to ensure it can
 * properly export shipping method settings for Blueprint integration.
 */

// Load WordPress
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing USPS Exporter\n";
echo "=====================\n\n";

try {
    // Initialize the USPS exporter
    $exporter = new \WooCommerceSupportHelper\WooCommerce_Shipping_Usps();
    
    echo "✓ USPS Exporter initialized successfully\n";
    echo "Plugin active: " . ($exporter->is_plugin_active() ? 'Yes' : 'No') . "\n";
    echo "Plugin slug: " . $exporter->get_plugin_slug() . "\n";
    echo "Method ID: " . $exporter->get_method_id() . "\n";
    echo "Blueprint alias: " . $exporter->get_alias() . "\n\n";
    
    // Test getting site options
    echo "Testing site options export...\n";
    $site_options = $exporter->get_site_options();
    if (!empty($site_options)) {
        echo "✓ Found " . count($site_options) . " site options:\n";
        foreach ($site_options as $option_name => $option_value) {
            if (is_array($option_value)) {
                echo "  - " . $option_name . ": Array(" . count($option_value) . " items)\n";
                // Show first few items for demo data
                if (strpos($option_name, 'demo') !== false) {
                    $sample_keys = array_slice(array_keys($option_value), 0, 5);
                    echo "    Sample keys: " . implode(', ', $sample_keys) . "\n";
                }
            } else {
                echo "  - " . $option_name . ": " . $option_value . "\n";
            }
        }
    } else {
        echo "✗ No site options found\n";
    }
    echo "\n";
    
    // Test getting shipping zone configurations
    echo "Testing shipping zone configurations export...\n";
    $shipping_zones = $exporter->get_shipping_zone_configurations();
    if (!empty($shipping_zones)) {
        echo "✓ Found " . count($shipping_zones) . " shipping zone configurations:\n";
        foreach ($shipping_zones as $zone_config) {
            echo "  - Zone ID: " . $zone_config['zone_id'] . ", Name: " . $zone_config['zone_name'] . "\n";
            if (isset($zone_config['method_settings']) && is_array($zone_config['method_settings'])) {
                echo "    Method settings: " . count($zone_config['method_settings']) . " items\n";
            }
        }
    } else {
        echo "ℹ No shipping zone configurations found (this is normal if USPS plugin isn't active)\n";
    }
    echo "\n";
    
    // Test getting method settings
    echo "Testing method settings export...\n";
    $method_settings = $exporter->get_method_settings();
    if (!empty($method_settings)) {
        echo "✓ Found method settings with " . count($method_settings) . " categories:\n";
        foreach ($method_settings as $category => $settings) {
            if (is_array($settings)) {
                echo "  - " . $category . ": " . count($settings) . " items\n";
                // Show sample keys for demo data
                if ($category === 'general' && !empty($settings)) {
                    $sample_keys = array_slice(array_keys($settings), 0, 5);
                    echo "    Sample keys: " . implode(', ', $sample_keys) . "\n";
                }
            } else {
                echo "  - " . $category . ": " . $settings . "\n";
            }
        }
    } else {
        echo "✗ No method settings found\n";
    }
    echo "\n";
    
    // Test full export data
    echo "Testing full export data...\n";
    $export_data = $exporter->export_data();
    if (!empty($export_data)) {
        echo "✓ Export data contains:\n";
        foreach ($export_data as $key => $value) {
            if (is_array($value)) {
                echo "  - " . $key . ": Array(" . count($value) . " items)\n";
            } else {
                echo "  - " . $key . ": " . $value . "\n";
            }
        }
    } else {
        echo "✗ No export data generated\n";
    }
    
    // Test Blueprint export method
    echo "\nTesting Blueprint export method...\n";
    try {
        $step = $exporter->export();
        echo "✓ Blueprint export successful\n";
        echo "Step class: " . get_class($step) . "\n";
        
        if (method_exists($step, 'get_json_array')) {
            $step_data = $step->get_json_array();
            echo "Step data keys: " . implode(', ', array_keys($step_data)) . "\n";
        }
    } catch (Exception $e) {
        echo "✗ Blueprint export failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n✓ USPS exporter test completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nTest completed!\n";
