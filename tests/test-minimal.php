<?php
/**
 * Minimal test to isolate the issue
 */

echo "Starting minimal test...\n";

// Load the autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "Autoloader loaded\n";

// Check if the class exists
echo "Checking if class exists...\n";
if (class_exists('\WooCommerceSupportHelper\WooCommerce_Shipping_Australia_Post')) {
    echo "✓ Class exists\n";
} else {
    echo "✗ Class does not exist\n";
    exit(1);
}

// Check if Logger class exists
echo "Checking if Logger class exists...\n";
if (class_exists('\WooCommerceSupportHelper\Logger')) {
    echo "✓ Logger class exists\n";
} else {
    echo "✗ Logger class does not exist\n";
    exit(1);
}

echo "All checks passed!\n";
