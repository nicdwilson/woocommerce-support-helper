<?php
/**
 * Test file for Custom ExportSchema
 * 
 * This file tests that our custom ExportSchema class can be instantiated
 * and that it properly extends the WooCommerce ExportSchema.
 */

// Include WordPress test framework if available
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Test that our custom class can be loaded
echo "Testing Custom ExportSchema...\n";

try {
    // Test that the class exists
    if (class_exists('\WooCommerceSupportHelper\BlueprintExporter\Custom_Export_Schema')) {
        echo "✓ Custom_Export_Schema class found\n";
        
        // Test that it extends the WooCommerce ExportSchema
        $reflection = new ReflectionClass('\WooCommerceSupportHelper\BlueprintExporter\Custom_Export_Schema');
        $parent = $reflection->getParentClass();
        
        if ($parent && $parent->getName() === 'Automattic\WooCommerce\Blueprint\ExportSchema') {
            echo "✓ Custom_Export_Schema properly extends WooCommerce ExportSchema\n";
        } else {
            echo "✗ Custom_Export_Schema does not extend WooCommerce ExportSchema\n";
        }
        
        // Test that we can create an instance
        try {
            $instance = new \WooCommerceSupportHelper\BlueprintExporter\Custom_Export_Schema();
            echo "✓ Custom_Export_Schema instance created successfully\n";
        } catch (Exception $e) {
            echo "✗ Failed to create Custom_Export_Schema instance: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "✗ Custom_Export_Schema class not found\n";
    }
    
    // Test that our custom REST API class can be loaded
    if (class_exists('\WooCommerceSupportHelper\BlueprintExporter\Custom_Rest_Api')) {
        echo "✓ Custom_Rest_Api class found\n";
        
        // Test that it extends the WooCommerce RestApi
        $reflection = new ReflectionClass('\WooCommerceSupportHelper\BlueprintExporter\Custom_Rest_Api');
        $parent = $reflection->getParentClass();
        
        if ($parent && $parent->getName() === 'Automattic\WooCommerce\Admin\Features\Blueprint\RestApi') {
            echo "✓ Custom_Rest_Api properly extends WooCommerce RestApi\n";
        } else {
            echo "✗ Custom_Rest_Api does not extend WooCommerce RestApi\n";
        }
        
    } else {
        echo "✗ Custom_Rest_Api class not found\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
