<?php
/**
 * Test with simulated WooCommerce environment
 */

// Simulate WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/fake/wordpress/path/' );
}

// Simulate WooCommerce Blueprint classes
if ( ! class_exists( '\Automattic\WooCommerce\Blueprint\ExportSchema' ) ) {
    class_alias( 'stdClass', '\Automattic\WooCommerce\Blueprint\ExportSchema' );
}

if ( ! class_exists( '\Automattic\WooCommerce\Blueprint\Exporters\StepExporter' ) ) {
    class_alias( 'stdClass', '\Automattic\WooCommerce\Blueprint\Exporters\StepExporter' );
}

if ( ! class_exists( '\Automattic\WooCommerce\Blueprint\Exporters\HasAlias' ) ) {
    class_alias( 'stdClass', '\Automattic\WooCommerce\Blueprint\Exporters\HasAlias' );
}

if ( ! class_exists( '\Automattic\WooCommerce\Blueprint\Logger' ) ) {
    class_alias( 'stdClass', '\Automattic\WooCommerce\Blueprint\Logger' );
}

// Load the autoloader
require_once 'release-package/vendor/autoload.php';

echo "Testing with WooCommerce simulation...\n";
echo "=====================================\n\n";

// Test classes that should be found
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
