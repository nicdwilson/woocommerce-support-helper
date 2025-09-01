<?php
/**
 * Test script for dynamic shipping steps processing
 * 
 * This script tests the filter-based approach for adding shipping method steps
 * to the blueprint export schema.
 */

// Load the autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing Dynamic Shipping Steps Processing\n";
echo "=========================================\n\n";

// Test the Blueprint Exporter class
try {
    $blueprint_exporter = new \WooCommerceSupportHelper\BlueprintExporter\Blueprint_Exporter();
    echo "✓ Blueprint_Exporter initialized successfully\n\n";
    
    // Test different payload scenarios
    $test_scenarios = array(
        'No shipping selections' => array(
            'settings' => array('general', 'payment'),
            'plugins' => array('woocommerce'),
            'themes' => array('storefront')
        ),
        'Australia Post only' => array(
            'settings' => array('general'),
            'plugin_settings' => array('woocommerce-shipping-australia-post')
        ),
        'USPS only' => array(
            'settings' => array('general'),
            'plugin_settings' => array('woocommerce-shipping-usps')
        ),
        'Both shipping methods' => array(
            'settings' => array('general'),
            'plugin_settings' => array('woocommerce-shipping-australia-post', 'woocommerce-shipping-usps')
        ),
        'Mixed selections' => array(
            'settings' => array('general', 'payment'),
            'plugins' => array('woocommerce'),
            'plugin_settings' => array('woocommerce-shipping-australia-post'),
            'themes' => array('storefront')
        ),
        'Unknown shipping method' => array(
            'settings' => array('general'),
            'plugin_settings' => array('unknown-shipping-method')
        ),
        'Empty plugin_settings' => array(
            'settings' => array('general'),
            'plugin_settings' => array()
        )
    );
    
    foreach ($test_scenarios as $scenario_name => $payload) {
        echo "Testing: {$scenario_name}\n";
        echo "Payload: " . json_encode($payload) . "\n";
        
        // Use reflection to access the private method
        $reflection = new ReflectionClass($blueprint_exporter);
        $method = $reflection->getMethod('steps_payload_to_blueprint_steps');
        $method->setAccessible(true);
        
        $result = $method->invoke($blueprint_exporter, $payload);
        
        echo "Result: " . json_encode($result) . "\n";
        
        // Check if shipping steps are included
        $shipping_steps = array_intersect($result, array('AustraliaPostOptions', 'UspsOptions'));
        if (!empty($shipping_steps)) {
            echo "✓ Shipping steps found: " . implode(', ', $shipping_steps) . "\n";
        } else {
            echo "ℹ No shipping steps in result\n";
        }
        
        echo "\n";
    }
    
    // Test the filter directly
    echo "Testing Filter Directly\n";
    echo "======================\n\n";
    
    $test_steps = array('general', 'payment');
    $test_payload = array(
        'settings' => array('general', 'payment'),
        'plugin_settings' => array('woocommerce-shipping-australia-post', 'woocommerce-shipping-usps')
    );
    
    echo "Initial steps: " . json_encode($test_steps) . "\n";
    echo "Payload: " . json_encode($test_payload) . "\n";
    
    $filtered_steps = apply_filters('wc_support_helper_payload_contains_steps', $test_steps, $test_payload);
    
    echo "After filter: " . json_encode($filtered_steps) . "\n";
    
    // Check if shipping steps are included
    $shipping_steps = array_intersect($filtered_steps, array('AustraliaPostOptions', 'UspsOptions'));
    if (!empty($shipping_steps)) {
        echo "✓ Shipping steps found: " . implode(', ', $shipping_steps) . "\n";
    } else {
        echo "ℹ No shipping steps in result\n";
    }
    
    // Test with single argument (should not cause error)
    echo "\nTesting filter with single argument:\n";
    $single_arg_steps = apply_filters('wc_support_helper_payload_contains_steps', $test_steps);
    echo "Single argument result: " . json_encode($single_arg_steps) . "\n";
    echo "✓ No error occurred with single argument\n";
    
    echo "\n✓ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nTest completed!\n";
