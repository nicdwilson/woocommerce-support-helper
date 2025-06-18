<?php
// Test script to verify plugin classes can be loaded
require_once __DIR__ . '/vendor/autoload.php';

echo "Testing class loading...\n";

try {
    // Test loading the main classes
    $logger = new \HappyBlueprintExporter\Logger();
    echo "✓ Logger class loaded successfully\n";
    
    // Check if Abstract_Exporter class exists (don't instantiate since it's abstract)
    if (class_exists('\HappyBlueprintExporter\Abstract_Exporter')) {
        echo "✓ Abstract_Exporter class loaded successfully\n";
    } else {
        echo "✗ Abstract_Exporter class not found\n";
    }
    
    $private_plugin_exporter = new \HappyBlueprintExporter\Private_Plugin_Exporter();
    echo "✓ Private_Plugin_Exporter class loaded successfully\n";
    
    echo "\nAll classes loaded successfully! Plugin is ready to use.\n";
    
} catch (Exception $e) {
    echo "✗ Error loading classes: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Additional debugging
echo "\nDebugging information:\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Autoloader file: " . __DIR__ . '/vendor/autoload.php' . "\n";
echo "Autoloader exists: " . (file_exists(__DIR__ . '/vendor/autoload.php') ? 'Yes' : 'No') . "\n";

// Check if classes exist
echo "Logger class exists: " . (class_exists('\HappyBlueprintExporter\Logger') ? 'Yes' : 'No') . "\n";
echo "Abstract_Exporter class exists: " . (class_exists('\HappyBlueprintExporter\Abstract_Exporter') ? 'Yes' : 'No') . "\n";
echo "Private_Plugin_Exporter class exists: " . (class_exists('\HappyBlueprintExporter\Private_Plugin_Exporter') ? 'Yes' : 'No') . "\n"; 