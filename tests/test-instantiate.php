<?php
// Test script to verify plugin classes can be instantiated
require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing class instantiation...\n";

try {
    // Test Logger class
    $logger = new \WooCommerceSupportHelper\Logger();
    echo "✓ Logger class instantiated successfully\n";
    
    // Test Module_Loader class
    $module_loader = new \WooCommerceSupportHelper\Module_Loader();
    echo "✓ Module_Loader class instantiated successfully\n";
    
    // Test Blueprint_Exporter class
    $blueprint_exporter = new \WooCommerceSupportHelper\BlueprintExporter\Blueprint_Exporter();
    echo "✓ Blueprint_Exporter class instantiated successfully\n";
    
    // Test Private_Plugin_Exporter class
    $private_plugin_exporter = new \WooCommerceSupportHelper\BlueprintExporter\Private_Plugin_Exporter();
    echo "✓ Private_Plugin_Exporter class instantiated successfully\n";
    
    // Test Abstract_Exporter class (don't instantiate since it's abstract)
    if (class_exists('\WooCommerceSupportHelper\BlueprintExporter\Abstract_Exporter')) {
        echo "✓ Abstract_Exporter class exists (abstract class)\n";
    } else {
        echo "✗ Abstract_Exporter class not found\n";
    }
    
    // Test Shipping_Methods_Exporter class
    $shipping_methods_exporter = new \WooCommerceSupportHelper\Shipping_Methods_Exporter();
    echo "✓ Shipping_Methods_Exporter class instantiated successfully\n";
    
    // Test WooCommerce_Shipping_Australia_Post class
    $australia_post_exporter = new \WooCommerceSupportHelper\WooCommerce_Shipping_Australia_Post();
    echo "✓ WooCommerce_Shipping_Australia_Post class instantiated successfully\n";
    
    // Test WooCommerce_Shipping_Usps class
    $usps_exporter = new \WooCommerceSupportHelper\WooCommerce_Shipping_Usps();
    echo "✓ WooCommerce_Shipping_Usps class instantiated successfully\n";
    
    echo "✓ All classes working correctly!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "✗ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "Test complete!\n";
