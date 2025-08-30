<?php
/**
 * Test script for Australia Post exporter
 * 
 * This script tests the Australia Post shipping method exporter to ensure it can
 * properly export shipping method settings for Blueprint integration.
 */

// Load WordPress
require_once __DIR__ . '/vendor/autoload.php';

// Check if we're in a WordPress context
if (!defined('ABSPATH')) {
    echo "This script must be run in a WordPress context.\n";
    exit(1);
}

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    echo "WooCommerce is not active.\n";
    exit(1);
}

// Check if the Australia Post plugin is active
if (!is_plugin_active('woocommerce-shipping-australia-post/woocommerce-shipping-australia-post.php')) {
    echo "Australia Post shipping plugin is not active.\n";
    exit(1);
}

echo "Testing Australia Post exporter...\n\n";

try {
    // Initialize the Australia Post exporter
    $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();
    
    echo "Exporter initialized successfully.\n";
    echo "Plugin active: " . ($exporter->is_plugin_active() ? 'Yes' : 'No') . "\n";
    echo "Plugin slug: " . $exporter->get_plugin_slug() . "\n";
    echo "Method ID: " . $exporter->get_method_id() . "\n\n";
    
    // Test getting site options
    echo "Testing site options export...\n";
    $site_options = $exporter->get_site_options();
    if (!empty($site_options)) {
        echo "Found " . count($site_options) . " site options:\n";
        foreach ($site_options as $option_name => $option_value) {
            echo "  - " . $option_name . ": " . (is_array($option_value) ? 'Array(' . count($option_value) . ' items)' : $option_value) . "\n";
        }
    } else {
        echo "No site options found.\n";
    }
    echo "\n";
    
    // Test getting shipping zone configurations
    echo "Testing shipping zone configurations export...\n";
    $shipping_zones = $exporter->get_shipping_zone_configurations();
    if (!empty($shipping_zones)) {
        echo "Found " . count($shipping_zones) . " shipping zone configurations:\n";
        foreach ($shipping_zones as $zone_config) {
            echo "  - Zone ID: " . $zone_config['zone_id'] . ", Name: " . $zone_config['zone_name'] . "\n";
            if (isset($zone_config['method_settings']) && is_array($zone_config['method_settings'])) {
                echo "    Method settings: " . count($zone_config['method_settings']) . " items\n";
            }
        }
    } else {
        echo "No shipping zone configurations found.\n";
    }
    echo "\n";
    
    // Test getting method settings
    echo "Testing method settings export...\n";
    $method_settings = $exporter->get_method_settings();
    if (!empty($method_settings)) {
        echo "Found method settings with " . count($method_settings) . " categories:\n";
        foreach ($method_settings as $category => $settings) {
            echo "  - " . $category . ": " . (is_array($settings) ? count($settings) . ' items' : $settings) . "\n";
        }
    } else {
        echo "No method settings found.\n";
    }
    echo "\n";
    
    // Test full export data
    echo "Testing full export data...\n";
    $export_data = $exporter->export_data();
    if (!empty($export_data)) {
        echo "Export data contains:\n";
        foreach ($export_data as $key => $value) {
            if (is_array($value)) {
                echo "  - " . $key . ": Array(" . count($value) . " items)\n";
            } else {
                echo "  - " . $key . ": " . $value . "\n";
            }
        }
    } else {
        echo "No export data generated.\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
