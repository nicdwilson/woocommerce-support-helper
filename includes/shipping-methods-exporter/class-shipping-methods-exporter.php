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
     * 
     * Structured like this:
     * 'plugin-slug' => 'Plugin Name',
     * Then, as we go along, directories are structured as `plugin-slug`
     * Class files are named `class-plugin-slug.php`
     * Classes are named `Plugin_Slug`
     * 
     */
    private $supported_plugins = array(
        'woocommerce-shipping-australia-post' => 'WooCommerce Australia Post Shipping',
        'woocommerce-shipping-usps' => 'WooCommerce USPS Shipping',
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_shipping_exporters();
        $this->init_hooks();
    }
    

    /**
     * Initialize all shipping exporters
     */
    public function init_shipping_exporters() {
        // Load all supported shipping exporters dynamically
        foreach ($this->supported_plugins as $plugin_slug => $plugin_name) {
            $this->load_shipping_exporter($plugin_slug, $plugin_name);
        }
    }

    /**
     * Load a specific shipping exporter
     *
     * @param string $plugin_slug The plugin slug
     * @param string $plugin_name The plugin display name
     */
    private function load_shipping_exporter($plugin_slug, $plugin_name) {
        $exporter_path = __DIR__ . '/' . $plugin_slug . '/class-' . $plugin_slug . '.php';
        $class_name = $this->plugin_slug_to_class_name($plugin_slug);
        $full_class_name = '\\WooCommerceSupportHelper\\' . $class_name;
        
        if (file_exists($exporter_path)) {

            try {
                require_once($exporter_path);
                
                if (class_exists($full_class_name)) {
                    $exporter = new $full_class_name();
                    $this->shipping_exporters[$plugin_slug] = $exporter;

                } else {
                    Logger::warning('⚠️ ' . $plugin_name . ' exporter class not found after including file', array(
                        'path' => $exporter_path,
                        'expected_class' => $full_class_name
                    ));
                }
            } catch (Exception $e) {
                Logger::error('❌ Error loading ' . $plugin_name . ' exporter', array(
                    'path' => $exporter_path,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ));
            }
        } else {
           // todo log if file is missing altogether
        }
    }

    /**
     * Convert plugin slug to class name
     * 
     * Examples:
     * - woocommerce-shipping-australia-post → WooCommerce_Shipping_Australia_Post
     * - woocommerce-shipping-fedex → WooCommerce_Shipping_Fedex
     * - woocommerce-shipping-ups → WooCommerce_Shipping_Ups
     *
     * @param string $plugin_slug The plugin slug (e.g., 'woocommerce-shipping-australia-post')
     * @return string The class name (e.g., 'WooCommerce_Shipping_Australia_Post')
     */
    private function plugin_slug_to_class_name($plugin_slug) {
        // Remove 'woocommerce-shipping-' prefix if present
        $name = str_replace('woocommerce-shipping-', '', $plugin_slug);
        
        // Convert hyphens to underscores
        $name = str_replace('-', '_', $name);
        
        // Convert to title case (capitalize first letter of each word)
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '_', $name);
        
        // Handle special cases for abbreviations
        $name = str_replace('Ups', 'UPS', $name);
        $name = str_replace('Usps', 'USPS', $name);
        $name = str_replace('Fedex', 'FedEx', $name);
        
        // Add the WooCommerce_Shipping_ prefix
        return 'WooCommerce_Shipping_' . $name;
    }

    /**
     * Initialize WordPress and WooCommerce hooks
     */
    public function init_hooks() {

	    /**
	     * Add custom export options to Blueprint UI
	     */
		add_filter('woocommerce_admin_shared_settings', array($this, 'add_shipping_exporter_to_ui'), 25);

	    /**
	     * Add custom export functions
	     */
		add_filter('wooblueprint_exporters', array( $this, 'add_shipping_exporters' ), 10 );
            
    }


	public function add_shipping_exporters( $exporters ){
		// Add all loaded shipping exporters to the Blueprint exporters list
		foreach ($this->shipping_exporters as $plugin_slug => $exporter) {
			$exporters[] = $exporter;
			Logger::debug('🚀 Added shipping exporter to Blueprint exporters', array(
				'plugin_slug' => $plugin_slug,
				'exporter_class' => get_class($exporter)
			));
		}
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
        
        // Check if we have any exporters available
        if (empty($this->shipping_exporters)) {
            return $site_options;
        }
        
        $shipping_method_options = array();
        foreach ($this->shipping_exporters as $exporter_key => $exporter) {
            
            if (method_exists($exporter, 'get_site_options')) {
                $exporter_options = $exporter->get_site_options();
                $shipping_method_options = array_merge($shipping_method_options, $exporter_options);
            }
        }
        
        $final_options = array_merge($site_options, $shipping_method_options);
        
        return $final_options;
    }

    /**
     * Export shipping zone configurations for Blueprint
     *
     * @param array $shipping_zones
     * @return array
     */
    public function export_shipping_zone_configurations($shipping_zones) {
        
        // Check if we have any exporters available
        if (empty($this->shipping_exporters)) {
            return $shipping_zones;
        }
        
        $shipping_zone_options = array();
        foreach ($this->shipping_exporters as $exporter_key => $exporter) {
            
            if (method_exists($exporter, 'get_shipping_zone_configurations')) {
                $exporter_zones = $exporter->get_shipping_zone_configurations();
                $shipping_zone_options = array_merge($shipping_zone_options, $exporter_zones);
            } else {
            }
        }

        return array_merge($shipping_zones, $shipping_zone_options);
    }

    /**
     * Called when Blueprint export starts
     */
    public function on_blueprint_export() {
        
        // Try to export shipping methods data
        $site_options = $this->export_shipping_method_site_options(array());
        $shipping_zones = $this->export_shipping_zone_configurations(array());

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
        
        // Add items for all loaded shipping exporters
        foreach ($this->shipping_exporters as $plugin_slug => $exporter) {
            $plugin_name = isset($this->supported_plugins[$plugin_slug]) ? $this->supported_plugins[$plugin_slug] : ucfirst(str_replace('-', ' ', $plugin_slug));
            
            $shipping_items[] = array(
                'id'      => $plugin_slug,
                'label'   => $plugin_name,
                'checked' => true,
            );
        }
        
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

}





