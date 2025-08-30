<?php
/**
 * Simple test script for basic functionality
 * 
 * This script tests basic class loading and logging without requiring
 * a full WordPress context.
 */

echo "Testing basic functionality...\n\n";

// Test autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✓ Autoloader loaded\n";
} else {
    echo "✗ Autoloader not found\n";
    exit(1);
}

// Test if classes exist
$classes_to_test = [
    'WooCommerceSupportHelper\ShippingMethodsExporter\Abstract_Shipping_Exporter',
    'WooCommerceSupportHelper\ShippingMethodsExporter\Shipping_Methods_Exporter',
    'WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter',
];

foreach ($classes_to_test as $class) {
    if (class_exists($class)) {
        echo "✓ Class found: $class\n";
    } else {
        echo "✗ Class not found: $class\n";
    }
}

echo "\nTesting class instantiation...\n";

try {
    // Test abstract class (should work)
    if (class_exists('WooCommerceSupportHelper\ShippingMethodsExporter\Abstract_Shipping_Exporter')) {
        echo "✓ Abstract class exists\n";
    }
    
    // Test main exporter class (should work now)
    if (class_exists('WooCommerceSupportHelper\ShippingMethodsExporter\Shipping_Methods_Exporter')) {
        echo "✓ Main exporter class exists\n";
        
        // Try to instantiate (this might fail without WordPress context, but should at least not crash on class definition)
        $reflection = new ReflectionClass('WooCommerceSupportHelper\ShippingMethodsExporter\Shipping_Methods_Exporter');
        echo "✓ Main exporter class can be reflected\n";
        
        // Check if it extends the abstract class
        $parent = $reflection->getParentClass();
        if ($parent && $parent->getName() === 'WooCommerceSupportHelper\ShippingMethodsExporter\Abstract_Shipping_Exporter') {
            echo "✓ Main exporter class extends Abstract_Shipping_Exporter\n";
        } else {
            echo "✗ Main exporter class does not extend Abstract_Shipping_Exporter\n";
        }
    }
    
    // Test Australia Post exporter class
    if (class_exists('WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter')) {
        echo "✓ Australia Post exporter class exists\n";
        
        // Check if it extends the abstract class
        $reflection = new ReflectionClass('WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter');
        $parent = $reflection->getParentClass();
        if ($parent && $parent->getName() === 'WooCommerceSupportHelper\ShippingMethodsExporter\Abstract_Shipping_Exporter') {
            echo "✓ Australia Post exporter class extends Abstract_Shipping_Exporter\n";
        } else {
            echo "✗ Australia Post exporter class does not extend Abstract_Shipping_Exporter\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
