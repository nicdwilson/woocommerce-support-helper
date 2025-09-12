<?php
/**
 * Test a single class to debug the issue
 */

// Simulate WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/fake/wordpress/path/' );
}

// Load the autoloader
require_once 'release-package/vendor/autoload.php';

echo "Testing single class...\n";
echo "======================\n\n";

// Test Custom_Export_Schema class
$class = 'WooCommerceSupportHelper\\BlueprintExporter\\Custom_Export_Schema';
echo "Testing class: $class\n";

if ( class_exists( $class ) ) {
    echo "✅ Class found!\n";
} else {
    echo "❌ Class not found\n";
    
    // Try to load the file directly
    $file_path = 'release-package/includes/blueprint-exporter/class-custom-export-schema.php';
    echo "Trying to load file directly: $file_path\n";
    
    if ( file_exists( $file_path ) ) {
        echo "File exists, loading...\n";
        require_once $file_path;
        
        if ( class_exists( $class ) ) {
            echo "✅ Class found after direct load!\n";
        } else {
            echo "❌ Class still not found after direct load\n";
        }
    } else {
        echo "❌ File not found\n";
    }
}
