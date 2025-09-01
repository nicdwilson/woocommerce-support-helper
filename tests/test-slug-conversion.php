<?php
/**
 * Test script to demonstrate plugin slug to class name conversion
 */

// Load the autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing Plugin Slug to Class Name Conversion\n";
echo "============================================\n\n";

// Test the conversion function
function plugin_slug_to_class_name($plugin_slug) {
    // Remove 'woocommerce-shipping-' prefix if present
    $name = str_replace('woocommerce-shipping-', '', $plugin_slug);
    
    // Convert hyphens to underscores
    $name = str_replace('-', '_', $name);
    
    // Convert to title case (capitalize first letter of each word)
    $name = str_replace('_', ' ', $name);
    $name = ucwords($name);
    $name = str_replace(' ', '_', $name);
    
    // Handle special cases for abbreviations
    $name = str_replace('Ups', 'UPS', $name);
    $name = str_replace('Usps', 'USPS', $name);
    $name = str_replace('Fedex', 'FedEx', $name);
    
    // Add the WooCommerce_Shipping_ prefix
    return 'WooCommerce_Shipping_' . $name;
}

// Test cases
$test_cases = array(
    'woocommerce-shipping-australia-post' => 'WooCommerce_Shipping_Australia_Post',
    'woocommerce-shipping-fedex' => 'WooCommerce_Shipping_FedEx',
    'woocommerce-shipping-ups' => 'WooCommerce_Shipping_UPS',
    'woocommerce-shipping-usps' => 'WooCommerce_Shipping_USPS',
    'woocommerce-shipping-royalmail' => 'WooCommerce_Shipping_Royalmail',
    'woocommerce-table-rate-shipping' => 'WooCommerce_Table_Rate_Shipping',
);

foreach ($test_cases as $plugin_slug => $expected_class_name) {
    $actual_class_name = plugin_slug_to_class_name($plugin_slug);
    $status = ($actual_class_name === $expected_class_name) ? '✓' : '✗';
    
    echo sprintf(
        "%s %-40s → %s\n",
        $status,
        $plugin_slug,
        $actual_class_name
    );
    
    if ($actual_class_name !== $expected_class_name) {
        echo sprintf("   Expected: %s\n", $expected_class_name);
    }
}

echo "\nTesting with Shipping Methods Exporter class:\n";
echo "===============================================\n";

try {
    // Test if we can instantiate the shipping methods exporter
    $shipping_exporter = new \WooCommerceSupportHelper\Shipping_Methods_Exporter();
    echo "✓ Shipping_Methods_Exporter instantiated successfully\n";
    
    // Test if we can access the supported plugins
    $supported_plugins = $shipping_exporter->get_supported_plugins();
    echo "✓ Supported plugins retrieved: " . count($supported_plugins) . " plugins\n";
    
    foreach ($supported_plugins as $plugin_slug => $plugin_name) {
        $class_name = plugin_slug_to_class_name($plugin_slug);
        $full_class_name = '\\WooCommerceSupportHelper\\' . $class_name;
        
        echo sprintf(
            "  %-40s → %s\n",
            $plugin_slug,
            $class_name
        );
        
        // Check if the class exists
        if (class_exists($full_class_name)) {
            echo "    ✓ Class exists\n";
        } else {
            echo "    ✗ Class not found\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
