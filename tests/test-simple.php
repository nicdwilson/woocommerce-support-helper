<?php
// Simple test script to verify plugin classes can be loaded
require_once __DIR__ . '/../vendor/autoload.php';

echo "Testing class loading...\n";

// Test if classes exist
if (class_exists('\WooCommerceSupportHelper\Logger')) {
    echo "✓ Logger class found\n";
} else {
    echo "✗ Logger class not found\n";
}

if (class_exists('\WooCommerceSupportHelper\Abstract_Exporter')) {
    echo "✓ Abstract_Exporter class found\n";
} else {
    echo "✗ Abstract_Exporter class not found\n";
}

if (class_exists('\WooCommerceSupportHelper\Private_Plugin_Exporter')) {
    echo "✓ Private_Plugin_Exporter class found\n";
} else {
    echo "✗ Private_Plugin_Exporter class not found\n";
}

echo "Test complete!\n";
