<?php
namespace WooCommerceSupportHelper;

/**
 * Main Shipping Methods Exporter module class
 *
 * @package WooCommerceSupportHelper
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main class for the Shipping Methods Exporter module
 */
class Shipping_Methods_Exporter {
    /**
     * @var array
     */
    private $shipping_exporters = array();

    /**
     * @var array
     */
    private $supported_plugins = array(
        'woocommerce-shipping-australia-post' => 'Australia Post',
        'woocommerce-shipping-fedex' => 'FedEx',
        'woocommerce-shipping-royalmail' => 'Royal Mail',
        'woocommerce-shipping-ups' => 'UPS',
        'woocommerce-shipping-usps' => 'USPS',
        'woocommerce-table-rate-shipping' => 'Table Rate Shipping',
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_shipping_exporters();
        $this->init_hooks();
        
        // Exporters now self-register through the autoloader
    }
    

    /**
     * Initialize all shipping exporters
     */
    public function init_shipping_exporters() {
        // Force include our exporter files to ensure they're loaded
        // This bypasses autoloader issues and ensures self-registration code runs
        $exporter_path = __DIR__ . '/woocommerce-shipping-australia-post/class-woocommerce-shipping-australia-post.php';
        
        if (file_exists($exporter_path)) {
            Logger::info('🔍 Australia Post exporter file found, attempting to include', array(
                'path' => $exporter_path,
                'file_size' => filesize($exporter_path),
                'class_exists_before' => class_exists('\WooCommerceSupportHelper\WooCommerce_Shipping_Australia_Post')
            ));
            
            try {
               // require_once $exporter_path;
	            require_once( $exporter_path );
                Logger::info('✅ Australia Post exporter file successfully included', array(
                    'path' => $exporter_path,
                    'class_exists_after' => class_exists('\WooCommerceSupportHelper\WooCommerce_Shipping_Australia_Post')
                ));
            } catch (Exception $e) {
                Logger::error('❌ Error including Australia Post exporter file', array(
                    'path' => $exporter_path,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ));
            }
        }
        
        // TODO: Add other exporters here when implemented
        // $this->load_fedex_exporter();
        // $this->load_royal_mail_exporter();
        // $this->load_ups_exporter();
        // $this->load_usps_exporter();
        // $this->load_table_rate_shipping_exporter();
    }

    /**
     * Initialize WordPress and WooCommerce hooks
     */
    public function init_hooks() {


       
            add_filter('woocommerce_admin_shared_settings', array($this, 'add_shipping_exporter_to_ui'), 25);

			add_filter('wooblueprint_exporters', array( $this, 'add_shipping_exporters' ), 10 );
            
    }


	public function add_shipping_exporters( $exporters ){
			$exporters[] = new WooCommerce_Shipping_Australia_Post();
			return $exporters;
	}
    
    /**
     * Register shipping exporters with the Blueprint system
     *
     * @param array $exporters
     * @return array
     */
    public function register_shipping_exporters($exporters) {
        // Exporters are now self-registering through the autoloader
        // This method is kept for backward compatibility but no longer needed
        Logger::info('🚀 register_shipping_exporters called: Exporters are now self-registering via autoloader');
        return $exporters;
    }

    /**
     * Export shipping method site options for Blueprint
     * This method is called by the main plugin to hook into the Blueprint export process
     *
     * @param array $site_options
     * @return array
     */
    public function export_shipping_method_site_options($site_options) {
        Logger::info('MAIN: export_shipping_method_site_options called', array(
            'input_site_options_count' => count($site_options),
            'input_site_options_keys' => array_keys($site_options),
            'available_exporters' => array_keys($this->shipping_exporters),
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3),
        ));
        
        // Check if we have any exporters available
        if (empty($this->shipping_exporters)) {
            Logger::warning('No shipping exporters available for site options export');
            return $site_options;
        }
        
        $shipping_method_options = array();
        foreach ($this->shipping_exporters as $exporter_key => $exporter) {
            Logger::debug('Processing exporter', array(
                'exporter_key' => $exporter_key,
                'exporter_class' => get_class($exporter),
                'has_get_site_options' => method_exists($exporter, 'get_site_options'),
                'exporter_methods' => get_class_methods($exporter),
            ));
            
            if (method_exists($exporter, 'get_site_options')) {
                $exporter_options = $exporter->get_site_options();
                Logger::debug('Exporter returned options', array(
                    'exporter_key' => $exporter_key,
                    'options_count' => count($exporter_options),
                    'option_keys' => array_keys($exporter_options),
                    'exporter_options' => $exporter_options,
                ));
                $shipping_method_options = array_merge($shipping_method_options, $exporter_options);
            } else {
                Logger::debug('Exporter missing get_site_options method', array(
                    'exporter_key' => $exporter_key,
                    'exporter_class' => get_class($exporter),
                ));
            }
        }
        
        Logger::debug('export_shipping_method_site_options completed', array(
            'shipping_method_options_count' => count($shipping_method_options),
            'shipping_method_options_keys' => array_keys($shipping_method_options),
            'final_site_options_count' => count($site_options) + count($shipping_method_options),
            'final_site_options_keys' => array_keys(array_merge($site_options, $shipping_method_options)),
        ));
        
        $final_options = array_merge($site_options, $shipping_method_options);
        
        Logger::info('Shipping method site options export completed', array(
            'original_count' => count($site_options),
            'shipping_added_count' => count($shipping_method_options),
            'final_count' => count($final_options),
            'shipping_keys_added' => array_keys($shipping_method_options),
        ));
        
        return $final_options;
    }

    /**
     * Export shipping zone configurations for Blueprint
     *
     * @param array $shipping_zones
     * @return array
     */
    public function export_shipping_zone_configurations($shipping_zones) {
        Logger::debug('export_shipping_zone_configurations called', array(
            'input_shipping_zones_count' => count($shipping_zones),
            'available_exporters' => array_keys($this->shipping_exporters),
        ));
        
        // Check if we have any exporters available
        if (empty($this->shipping_exporters)) {
            Logger::warning('No shipping exporters available for zone configurations export');
            return $shipping_zones;
        }
        
        $shipping_zone_options = array();
        foreach ($this->shipping_exporters as $exporter_key => $exporter) {
            Logger::debug('Processing exporter for zone configs', array(
                'exporter_key' => $exporter_key,
                'exporter_class' => get_class($exporter),
                'has_get_shipping_zone_configurations' => method_exists($exporter, 'get_shipping_zone_configurations'),
            ));
            
            if (method_exists($exporter, 'get_shipping_zone_configurations')) {
                $exporter_zones = $exporter->get_shipping_zone_configurations();
                Logger::debug('Exporter returned zone configs', array(
                    'exporter_key' => $exporter_key,
                    'zones_count' => count($exporter_zones),
                ));
                $shipping_zone_options = array_merge($shipping_zone_options, $exporter_zones);
            } else {
                Logger::debug('Exporter missing get_shipping_zone_configurations method', array(
                    'exporter_key' => $exporter_key,
                    'exporter_class' => get_class($exporter),
                ));
            }
        }
        
        Logger::debug('export_shipping_zone_configurations completed', array(
            'shipping_zone_options_count' => count($shipping_zone_options),
            'final_shipping_zones_count' => count($shipping_zones) + count($shipping_zone_options),
        ));
        
        return array_merge($shipping_zones, $shipping_zone_options);
    }

    /**
     * Called when Blueprint export starts
     */
    public function on_blueprint_export() {
        Logger::debug('wooblueprint_export hook called - starting shipping methods export');
        
        // Check if we have any exporters available
        if (empty($this->shipping_exporters)) {
            Logger::warning('No shipping exporters available for Blueprint export');
            return;
        }
        
        // Try to export shipping methods data
        $site_options = $this->export_shipping_method_site_options(array());
        $shipping_zones = $this->export_shipping_zone_configurations(array());
        
        Logger::debug('Shipping methods export completed during blueprint export', array(
            'site_options_count' => count($site_options),
            'shipping_zones_count' => count($shipping_zones),
        ));
    }

    /**
     * Get shipping methods available for export
     *
     * @return array
     */
    public function get_shipping_methods_for_export() {
        $methods = array();
        
        // Check if we have any exporters available
        if (empty($this->shipping_exporters)) {
            Logger::warning('No shipping exporters available for export UI');
            return $methods;
        }
        
        // Add the main shipping methods exporter as a general option
        $methods[] = array(
            'id' => 'australia_post_shipping',
            'label' => __('Shipping Methods', 'woocommerce'),
            'description' => __('Includes all shipping method settings and configurations.', 'woocommerce'),
            'checked' => true,
        );
        
        // Add individual shipping method exporters
        foreach ($this->shipping_exporters as $key => $exporter) {
            if (method_exists($exporter, 'get_export_info')) {
                $info = $exporter->get_export_info();
                if ($info) {
                    $methods[] = $info;
                }
            } else {
                // Fallback for exporters without get_export_info
                $methods[] = array(
                    'id' => $key . '_shipping',
                    'label' => ucfirst(str_replace('-', ' ', $key)),
                    'description' => 'Shipping method settings',
                    'checked' => true,
                );
            }
        }
        
        Logger::debug('Shipping methods for export UI prepared', array(
            'methods_count' => count($methods),
            'methods' => $methods,
        ));
        
        return $methods;
    }





    /**
     * Get all shipping exporters
     *
     * @return array
     */
    public function get_shipping_exporters() {
        return $this->shipping_exporters;
    }
    
    /**
     * Get supported shipping plugins
     *
     * @return array
     */
    public function get_supported_plugins() {
        return $this->supported_plugins;
    }
    
    /**
     * Get module information
     *
     * @return array
     */
    public function get_module_info() {
        return array(
            'name' => 'Shipping Methods Exporter',
            'description' => 'Exports shipping method settings for various WooCommerce shipping plugins',
            'version' => '1.0.0',
            'supported_plugins' => $this->supported_plugins,
            'exporters' => array_map(function($exporter) {
                return array(
                    'name' => 'Shipping Exporter',
                    'description' => 'Exports shipping method settings',
                );
            }, $this->shipping_exporters),
        );
    }
    
    /**
     * Add the shipping exporter to the WooCommerce admin shared settings
     * This makes it visible in the Blueprint UI's "Add New Step" dropdown.
     *
     * @param array $settings
     * @return array
     */
    public function add_shipping_exporter_to_ui($settings) {
        // Get available shipping exporters
        $shipping_items = array();
         
        $shipping_items[] = array(
                'id'      => 'australia_post_shipping',
                'label'   => 'Australia Post',
                'checked' => true,
            );
        
        // Only add the group if we have shipping items
        if (!empty($shipping_items)) {
            $settings['blueprint_step_groups'][] = array(
                'id'          => 'shipping_plugins',
                'description' => __( 'Includes shipping plugin settings', 'woocommerce' ),
                'label'       => __( 'Shipping plugin settings', 'woocommerce' ),
                'icon'        => 'layout',
                'items'       => $shipping_items,
            );
        }

        return $settings;
    }



    

    /**
     * Register Blueprint hooks after WooCommerce is fully loaded
     */
    public function register_blueprint_hooks() {
        Logger::info('🚀🚀🚀 register_blueprint_hooks called - Registering Blueprint hooks');
        
        // Debug: Check what WooCommerce Blueprint classes are available
        Logger::info('🔍 DEBUG: Checking available WooCommerce Blueprint classes', array(
            'class_exists_StepExporter' => class_exists('\Automattic\WooCommerce\Blueprint\Exporters\StepExporter'),
            'class_exists_ExportSchema' => class_exists('\Automattic\WooCommerce\Blueprint\ExportSchema'),
            'class_exists_UseWPFunctions' => class_exists('\Automattic\WooCommerce\Blueprint\UseWPFunctions'),
            'class_exists_SetSiteOptions' => class_exists('\Automattic\WooCommerce\Blueprint\Steps\SetSiteOptions'),
            'class_exists_Blueprint' => class_exists('\Automattic\WooCommerce\Blueprint\Blueprint'),
            'woocommerce_loaded' => did_action('woocommerce_loaded'),
            'plugins_loaded_priority' => current_filter(),
        ));
        
        // Also check for any classes with "Blueprint" in the name
        $blueprint_classes = array();
        foreach (get_declared_classes() as $class) {
            if (strpos($class, 'Blueprint') !== false) {
                $blueprint_classes[] = $class;
            }
        }
        Logger::info('🔍 DEBUG: Found classes with "Blueprint" in name', array(
            'blueprint_classes' => $blueprint_classes,
        ));
        
        // Check if WooCommerce Blueprint files exist
        $wc_plugin_dir = WP_PLUGIN_DIR . '/woocommerce';
        $blueprint_files = array(
            'packages/blueprint/src/Exporters/StepExporter.php',
            'packages/blueprint/src/ExportSchema.php',
            'packages/blueprint/src/UseWPFunctions.php',
            'packages/blueprint/src/Steps/SetSiteOptions.php',
        );
        
        $existing_files = array();
        foreach ($blueprint_files as $file) {
            $full_path = $wc_plugin_dir . '/' . $file;
            $existing_files[$file] = file_exists($full_path);
        }
        
        Logger::info('🔍 DEBUG: WooCommerce Blueprint files check', array(
            'wc_plugin_dir' => $wc_plugin_dir,
            'existing_files' => $existing_files,
        ));
        
        // Try to manually include and check the StepExporter file
        if (file_exists($wc_plugin_dir . '/packages/blueprint/src/Exporters/StepExporter.php')) {
            Logger::info('🔍 DEBUG: StepExporter file exists, checking its contents');
            $file_contents = file_get_contents($wc_plugin_dir . '/packages/blueprint/src/Exporters/StepExporter.php');
            if ($file_contents) {
                // Look for namespace declaration
                if (preg_match('/namespace\s+([^;]+);/', $file_contents, $matches)) {
                    Logger::info('🔍 DEBUG: Found namespace in StepExporter file', array(
                        'namespace' => $matches[1],
                        'expected_namespace' => 'Automattic\WooCommerce\Blueprint\Exporters',
                    ));
                }
            }
        }
        
        // NOTE: Hook registration is now done directly in the constructor for immediate availability
        // This method is kept for backward compatibility but no longer registers hooks
        Logger::info('🚀🚀🚀 register_blueprint_hooks called - Hooks are now registered directly in constructor');
        
        // Still hook into the export process directly for additional functionality
        if (class_exists('\Automattic\WooCommerce\Blueprint\Exporters\StepExporter')) {
            add_action('wooblueprint_export', array($this, 'on_blueprint_export'));
            Logger::info('🚀🚀🚀 SUCCESS: wooblueprint_export action hook registered');
        } else {
            Logger::warning('❌❌❌ FAILED: StepExporter interface not found - cannot register wooblueprint_export action');
        }
        
        Logger::info('🚀🚀🚀 register_blueprint_hooks completed');
    }
    



}





