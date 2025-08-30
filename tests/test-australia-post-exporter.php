<?php
/**
 * Test file for Australia Post Exporter
 * 
 * This file tests the enhanced Australia Post shipping method exporter
 * to ensure it captures all the comprehensive settings correctly.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../');
}

// Include WordPress test framework
require_once ABSPATH . 'vendor/autoload.php';

// Test the Australia Post exporter
class Test_Australia_Post_Exporter {
    
    public function test_exporter_initialization() {
        echo "Testing Australia Post Exporter Initialization...\n";
        
        try {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();
            
            echo "✓ Exporter initialized successfully\n";
            echo "  - Name: " . $exporter->get_name() . "\n";
            echo "  - Description: " . $exporter->get_description() . "\n";
            echo "  - Plugin Slug: " . $exporter->get_plugin_slug() . "\n";
            echo "  - Method ID: " . $exporter->get_method_id() . "\n";
            
            return true;
        } catch (Exception $e) {
            echo "✗ Exporter initialization failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function test_plugin_detection() {
        echo "\nTesting Plugin Detection...\n";
        
        try {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();
            $is_active = $exporter->is_plugin_active();
            
            echo "✓ Plugin detection completed\n";
            echo "  - Plugin Active: " . ($is_active ? 'Yes' : 'No') . "\n";
            
            return $is_active;
        } catch (Exception $e) {
            echo "✗ Plugin detection failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function test_settings_export() {
        echo "\nTesting Settings Export...\n";
        
        try {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();
            
            if (!$exporter->is_plugin_active()) {
                echo "⚠ Plugin not active, skipping settings export test\n";
                return false;
            }
            
            $settings = $exporter->get_method_settings();
            
            echo "✓ Settings export completed\n";
            echo "  - Settings sections: " . count($settings) . "\n";
            
            foreach ($settings as $section => $data) {
                echo "    - {$section}: " . (is_array($data) ? count($data) . " items" : "data present") . "\n";
            }
            
            return !empty($settings);
        } catch (Exception $e) {
            echo "✗ Settings export failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function test_full_export() {
        echo "\nTesting Full Export...\n";
        
        try {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();
            
            if (!$exporter->is_plugin_active()) {
                echo "⚠ Plugin not active, skipping full export test\n";
                return false;
            }
            
            $export_data = $exporter->export_data();
            
            echo "✓ Full export completed\n";
            echo "  - Export data keys: " . implode(', ', array_keys($export_data)) . "\n";
            
            if (isset($export_data['settings'])) {
                echo "  - Settings sections: " . implode(', ', array_keys($export_data['settings'])) . "\n";
            }
            
            return !empty($export_data);
        } catch (Exception $e) {
            echo "✗ Full export failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function test_blueprint_integration() {
        echo "\nTesting Blueprint Integration...\n";
        
        try {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();
            
            $export_info = $exporter->get_export_info();
            
            if ($export_info) {
                echo "✓ Blueprint integration successful\n";
                echo "  - Export ID: " . $export_info['id'] . "\n";
                echo "  - Export Label: " . $export_info['label'] . "\n";
                echo "  - Export Type: " . $export_info['type'] . "\n";
                echo "  - Plugin Slug: " . $export_info['plugin_slug'] . "\n";
                return true;
            } else {
                echo "⚠ Blueprint integration not available (plugin may not be active)\n";
                return false;
            }
        } catch (Exception $e) {
            echo "✗ Blueprint integration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function run_all_tests() {
        echo "========================================\n";
        echo "Australia Post Exporter Test Suite\n";
        echo "========================================\n\n";
        
        $tests = [
            'test_exporter_initialization',
            'test_plugin_detection',
            'test_settings_export',
            'test_full_export',
            'test_blueprint_integration'
        ];
        
        $passed = 0;
        $total = count($tests);
        
        foreach ($tests as $test) {
            if ($this->$test()) {
                $passed++;
            }
        }
        
        echo "\n========================================\n";
        echo "Test Results: {$passed}/{$total} tests passed\n";
        echo "========================================\n";
        
        return $passed === $total;
    }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli' || defined('WP_TESTS_DB_NAME')) {
    $tester = new Test_Australia_Post_Exporter();
    $tester->run_all_tests();
}
