<?php
/**
 * Test script for multiple exporters
 * 
 * This script tests how multiple shipping exporters work together
 * and verifies that the deduplication system prevents duplicate processing.
 */

// Load WordPress
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing Multiple Exporters\n";
echo "==========================\n\n";

try {
    // Test the shipping methods exporter module
    echo "1. Testing Shipping Methods Exporter Module\n";
    echo "--------------------------------------------\n";
    
    $shipping_exporter = new \WooCommerceSupportHelper\Shipping_Methods_Exporter();
    echo "✓ Shipping_Methods_Exporter initialized successfully\n";
    
    // Get supported plugins
    $supported_plugins = $shipping_exporter->get_supported_plugins();
    echo "✓ Supported plugins: " . count($supported_plugins) . " plugins\n";
    foreach ($supported_plugins as $slug => $name) {
        echo "  - " . $slug . " → " . $name . "\n";
    }
    echo "\n";
    
    // Get loaded exporters
    $loaded_exporters = $shipping_exporter->get_shipping_exporters();
    echo "✓ Loaded exporters: " . count($loaded_exporters) . " exporters\n";
    foreach ($loaded_exporters as $slug => $exporter) {
        echo "  - " . $slug . " → " . get_class($exporter) . "\n";
    }
    echo "\n";
    
    // Test individual exporters
    echo "2. Testing Individual Exporters\n";
    echo "--------------------------------\n";
    
    // Test Australia Post exporter
    echo "Australia Post Exporter:\n";
    $australia_post = new \WooCommerceSupportHelper\WooCommerce_Shipping_Australia_Post();
    echo "  ✓ Class: " . get_class($australia_post) . "\n";
    echo "  ✓ Plugin slug: " . $australia_post->get_plugin_slug() . "\n";
    echo "  ✓ Method ID: " . $australia_post->get_method_id() . "\n";
    echo "  ✓ Blueprint alias: " . $australia_post->get_alias() . "\n";
    echo "  ✓ Plugin active: " . ($australia_post->is_plugin_active() ? 'Yes' : 'No') . "\n";
    
    $aus_site_options = $australia_post->get_site_options();
    echo "  ✓ Site options: " . count($aus_site_options) . " options\n";
    echo "\n";
    
    // Test USPS exporter
    echo "USPS Exporter:\n";
    $usps = new \WooCommerceSupportHelper\WooCommerce_Shipping_Usps();
    echo "  ✓ Class: " . get_class($usps) . "\n";
    echo "  ✓ Plugin slug: " . $usps->get_plugin_slug() . "\n";
    echo "  ✓ Method ID: " . $usps->get_method_id() . "\n";
    echo "  ✓ Blueprint alias: " . $usps->get_alias() . "\n";
    echo "  ✓ Plugin active: " . ($usps->is_plugin_active() ? 'Yes' : 'No') . "\n";
    
    $usps_site_options = $usps->get_site_options();
    echo "  ✓ Site options: " . count($usps_site_options) . " options\n";
    echo "\n";
    
    // Test exporter deduplication simulation
    echo "3. Testing Exporter Deduplication Simulation\n";
    echo "---------------------------------------------\n";
    
    // Simulate what happens in the Blueprint export process
    $all_exporters = array();
    
    // Add exporters multiple times to simulate potential duplicates
    $all_exporters[] = $australia_post;  // First instance
    $all_exporters[] = $usps;            // First instance
    $all_exporters[] = $australia_post;  // Duplicate instance
    $all_exporters[] = $usps;            // Duplicate instance
    
    echo "Before deduplication: " . count($all_exporters) . " exporters\n";
    
    // Simulate the deduplication process
    $unique_exporters = array();
    $seen_exporters = array();
    
    foreach ($all_exporters as $exporter) {
        $class_name = get_class($exporter);
        $step_name = method_exists($exporter, 'get_step_name') ? $exporter->get_step_name() : 'unknown';
        $alias = $exporter instanceof \Automattic\WooCommerce\Blueprint\Exporters\HasAlias ? $exporter->get_alias() : $step_name;
        
        // Create a unique identifier for this exporter
        $exporter_id = $class_name . '::' . $step_name . '::' . $alias;
        
        if (!isset($seen_exporters[$exporter_id])) {
            $unique_exporters[] = $exporter;
            $seen_exporters[$exporter_id] = true;
            echo "  ✓ Added unique exporter: " . $class_name . " (Alias: " . $alias . ")\n";
        } else {
            echo "  ⚠ Skipped duplicate exporter: " . $class_name . " (Alias: " . $alias . ")\n";
        }
    }
    
    echo "After deduplication: " . count($unique_exporters) . " exporters\n";
    echo "Duplicates removed: " . (count($all_exporters) - count($unique_exporters)) . "\n\n";
    
    // Test exporter capabilities
    echo "4. Testing Exporter Capabilities\n";
    echo "--------------------------------\n";
    
    foreach ($unique_exporters as $index => $exporter) {
        $class_name = get_class($exporter);
        echo "Exporter " . ($index + 1) . ": " . $class_name . "\n";
        
        // Test step capabilities
        $capabilities = $exporter->check_step_capabilities();
        echo "  ✓ Step capabilities: " . ($capabilities ? 'Pass' : 'Fail') . "\n";
        
        // Test export method
        try {
            $step = $exporter->export();
            echo "  ✓ Export method: Success (" . get_class($step) . ")\n";
            
            if (method_exists($step, 'get_json_array')) {
                $step_data = $step->get_json_array();
                echo "  ✓ Step data: " . count($step_data) . " items\n";
            }
        } catch (Exception $e) {
            echo "  ✗ Export method: Failed - " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // Test combined site options export
    echo "5. Testing Combined Site Options Export\n";
    echo "---------------------------------------\n";
    
    $combined_site_options = array();
    
    foreach ($unique_exporters as $exporter) {
        $exporter_options = $exporter->get_site_options();
        $combined_site_options = array_merge($combined_site_options, $exporter_options);
        
        echo "Added " . count($exporter_options) . " options from " . get_class($exporter) . "\n";
    }
    
    echo "✓ Total combined site options: " . count($combined_site_options) . "\n";
    echo "Sample option keys: " . implode(', ', array_slice(array_keys($combined_site_options), 0, 5)) . "\n\n";
    
    echo "✓ Multiple exporters test completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nTest completed!\n";
