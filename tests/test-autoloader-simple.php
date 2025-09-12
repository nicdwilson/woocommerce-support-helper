<?php
/**
 * Simple test script for the minimal autoloader
 * Tests only classes that don't have external dependencies
 */

// Simulate WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/fake/wordpress/path/' );
}

// Load the autoloader
require_once 'release-package/vendor/autoload.php';

// Test classes that should be found (without external dependencies)
$test_classes = [
    'WooCommerceSupportHelper\\Module_Loader',
    'WooCommerceSupportHelper\\Logger',
    'WooCommerceSupportHelper\\WC_Support_Helper_API',
    'WooCommerceSupportHelper\\BlueprintExporter\\Blueprint_Exporter',
    'WooCommerceSupportHelper\\BlueprintExporter\\Custom_Export_Schema',
    'WooCommerceSupportHelper\\BlueprintExporter\\Private_Plugin_Exporter',
    'WooCommerceSupportHelper\\BlueprintExporter\\Custom_Rest_Api',
    'WooCommerceSupportHelper\\BlueprintExporter\\Abstract_Exporter',
];

echo "Testing autoloader (simple test)...\n";
echo "===================================\n\n";

$success_count = 0;
$total_count = count( $test_classes );

foreach ( $test_classes as $class ) {
    if ( class_exists( $class ) ) {
        echo "✅ $class - Found\n";
        $success_count++;
    } else {
        echo "❌ $class - Not found\n";
    }
}

echo "\n";
echo "Results: $success_count/$total_count classes found\n";

if ( $success_count === $total_count ) {
    echo "🎉 All classes loaded successfully!\n";
    exit( 0 );
} else {
    echo "⚠️  Some classes failed to load\n";
    exit( 1 );
}
