<?php
/**
 * Test Support Admin UI Module
 *
 * @package WooCommerceSupportHelper\Tests
 * @since 1.0.0
 */

// Simple test script - no PHPUnit required

// Include the main plugin file
require_once dirname( __DIR__ ) . '/woocommerce-support-helper.php';

echo "Testing Support Admin UI Module Loading...\n\n";

// Test 1: Check if the main plugin class exists
if ( class_exists( '\WooCommerceSupportHelper\WC_Support_Helper' ) ) {
    echo "✓ Main plugin class exists\n";
} else {
    echo "✗ Main plugin class does not exist\n";
    exit( 1 );
}

// Test 2: Check if the module loader exists
if ( class_exists( '\WooCommerceSupportHelper\Module_Loader' ) ) {
    echo "✓ Module loader class exists\n";
} else {
    echo "✗ Module loader class does not exist\n";
    exit( 1 );
}

// Test 3: Check if the Support Admin UI class exists
if ( class_exists( '\WooCommerceSupportHelper\SupportAdminUI\Support_Admin_UI' ) ) {
    echo "✓ Support Admin UI class exists\n";
} else {
    echo "✗ Support Admin UI class does not exist\n";
    exit( 1 );
}

// Test 4: Try to instantiate the main plugin
try {
    $plugin = \WooCommerceSupportHelper\WC_Support_Helper::instance();
    echo "✓ Main plugin instantiated successfully\n";
} catch ( Exception $e ) {
    echo "✗ Failed to instantiate main plugin: " . $e->getMessage() . "\n";
    exit( 1 );
}

// Test 5: Check if the Support Admin UI module is loaded
$module_loader = $plugin->get_module_loader();
if ( $module_loader->is_module_loaded( 'support_admin_ui' ) ) {
    echo "✓ Support Admin UI module is loaded\n";
} else {
    echo "✗ Support Admin UI module is not loaded\n";
    exit( 1 );
}

// Test 6: Get module info
$support_admin_ui = $module_loader->get_module( 'support_admin_ui' );
if ( $support_admin_ui ) {
    $module_info = $support_admin_ui->get_module_info();
    echo "✓ Support Admin UI module info retrieved:\n";
    echo "  - Name: " . $module_info['name'] . "\n";
    echo "  - Description: " . $module_info['description'] . "\n";
    echo "  - Version: " . $module_info['version'] . "\n";
} else {
    echo "✗ Failed to get Support Admin UI module\n";
    exit( 1 );
}

// Test 7: Check total module count
$module_count = $module_loader->get_module_count();
echo "✓ Total modules loaded: " . $module_count . "\n";

echo "\nAll tests passed! Support Admin UI module is working correctly.\n";
