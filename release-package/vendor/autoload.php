<?php
/**
 * Minimal autoloader for WooCommerce Support Helper
 * This replaces the full Composer autoloader for release packages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Simple autoloader for our plugin classes
spl_autoload_register( function ( $class ) {
    // Only handle our plugin classes
    if ( strpos( $class, 'WooCommerceSupportHelper\\' ) !== 0 ) {
        return;
    }
    
    // Convert namespace to file path
    $class_file = str_replace( 'WooCommerceSupportHelper\\', '', $class );
    $class_file = str_replace( '\\', '/', $class_file );
    $class_file = strtolower( $class_file );
    
    // Convert class names to file names
    $class_file = str_replace( '_', '-', $class_file );
    $class_file = 'class-' . $class_file . '.php';
    
    // Define possible locations
    $locations = array(
        __DIR__ . '/includes/',
        __DIR__ . '/includes/blueprint-exporter/',
        __DIR__ . '/includes/shipping-methods-exporter/',
        __DIR__ . '/includes/shipping-methods-exporter/woocommerce-shipping-australia-post/',
        __DIR__ . '/includes/shipping-methods-exporter/woocommerce-shipping-usps/',
    );
    
    // Look for the class file
    foreach ( $locations as $location ) {
        $file_path = $location . $class_file;
        if ( file_exists( $file_path ) ) {
            require_once $file_path;
            return;
        }
    }
} );
