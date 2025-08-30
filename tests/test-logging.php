<?php
/**
 * Test script for logging and exporter initialization
 * 
 * This script tests the logging functionality and exporter initialization
 * to debug why the Australia Post settings are not being exported.
 */

// Load WordPress
require_once __DIR__ . '/vendor/autoload.php';

// Check if we're in a WordPress context
if (!defined('ABSPATH')) {
    echo "This script must be run in a WordPress context.\n";
    exit(1);
}

echo "Testing logging and exporter initialization...\n\n";

try {
    // Test if the Logger class exists
    if (class_exists('\WooCommerceSupportHelper\Logger')) {
        echo "✓ Logger class found\n";
        
        // Test logging
        \WooCommerceSupportHelper\Logger::debug('Test debug message');
        \WooCommerceSupportHelper\Logger::info('Test info message');
        echo "✓ Logging test completed\n";
    } else {
        echo "✗ Logger class not found\n";
    }
    
    echo "\n";
    
    // Test if the shipping methods exporter class exists
    if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\Shipping_Methods_Exporter')) {
        echo "✓ Shipping Methods Exporter class found\n";
        
        // Test if the Australia Post exporter class exists
        if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter')) {
            echo "✓ Australia Post Exporter class found\n";
            
            // Test instantiation
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();
            echo "✓ Australia Post Exporter instantiated successfully\n";
            
            // Test plugin active check
            $is_active = $exporter->is_plugin_active();
            echo "✓ Plugin active check: " . ($is_active ? 'Yes' : 'No') . "\n";
            
            // Test getting site options
            $site_options = $exporter->get_site_options();
            echo "✓ Site options retrieved: " . count($site_options) . " options\n";
            
            // Test getting shipping zone configurations
            $zone_configs = $exporter->get_shipping_zone_configurations();
            echo "✓ Zone configurations retrieved: " . count($zone_configs) . " zones\n";
            
        } else {
            echo "✗ Australia Post Exporter class not found\n";
        }
        
    } else {
        echo "✗ Shipping Methods Exporter class not found\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
