<?php
/**
 * Debug script for the minimal autoloader
 */

// Simulate WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/fake/wordpress/path/' );
}

// Load the autoloader
require_once 'release-package/vendor/autoload.php';

// Test with debug output
$class = 'WooCommerceSupportHelper\\Module_Loader';
echo "Testing class: $class\n";

// Convert namespace to file path
$class_file = str_replace( 'WooCommerceSupportHelper\\', '', $class );
$class_file = str_replace( '\\', '/', $class_file );
$class_file = strtolower( $class_file );

echo "After namespace removal: $class_file\n";

// Convert class names to file names (convert underscores to hyphens for file names)
$class_file = str_replace( '_', '-', $class_file );
$class_file = 'class-' . $class_file . '.php';

echo "After conversion: $class_file\n";

// Define possible locations
$locations = array(
    __DIR__ . '/release-package/includes/',
    __DIR__ . '/release-package/includes/blueprint-exporter/',
    __DIR__ . '/release-package/includes/shipping-methods-exporter/',
    __DIR__ . '/release-package/includes/shipping-methods-exporter/woocommerce-shipping-australia-post/',
    __DIR__ . '/release-package/includes/shipping-methods-exporter/woocommerce-shipping-usps/',
);

echo "Looking in locations:\n";
foreach ( $locations as $location ) {
    $file_path = $location . $class_file;
    echo "  $file_path - " . (file_exists( $file_path ) ? "EXISTS" : "NOT FOUND") . "\n";
}

// Test if class exists
if ( class_exists( $class ) ) {
    echo "✅ Class found!\n";
} else {
    echo "❌ Class not found\n";
}
