<?php
namespace WooCommerceSupportHelper;

/**
 * Module loader for WooCommerce Support Helper
 *
 * @package WooCommerceSupportHelper
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main module loader class
 */
class Module_Loader {
    /**
     * @var array
     */
    private $modules = array();

    /**
     * @var array
     */
    private $module_info = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_modules();
    }

    /**
     * Load all available modules
     */
    private function load_modules() {
        // Load Blueprint Exporter module
        $this->load_blueprint_exporter_module();
        
        // Load Shipping Methods Exporter module
        $this->load_shipping_methods_exporter_module();
        
        // Future modules can be loaded here
        // $this->load_other_module();
    }

    /**
     * Load the Blueprint Exporter module
     */
    private function load_blueprint_exporter_module() {
        if (class_exists('\WooCommerceSupportHelper\BlueprintExporter\Blueprint_Exporter')) {
            $blueprint_exporter = new \WooCommerceSupportHelper\BlueprintExporter\Blueprint_Exporter();
            $this->modules['blueprint_exporter'] = $blueprint_exporter;
            $this->module_info['blueprint_exporter'] = $blueprint_exporter->get_module_info();
        }
    }

    /**
     * Load the Shipping Methods Exporter module
     */
    private function load_shipping_methods_exporter_module() {
        if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\Shipping_Methods_Exporter')) {
            $shipping_methods_exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\Shipping_Methods_Exporter();
            $this->modules['shipping_methods_exporter'] = $shipping_methods_exporter;
            $this->module_info['shipping_methods_exporter'] = $shipping_methods_exporter->get_module_info();
        }
    }

    /**
     * Get all loaded modules
     *
     * @return array
     */
    public function get_modules() {
        return $this->modules;
    }

    /**
     * Get a specific module by name
     *
     * @param string $name
     * @return object|null
     */
    public function get_module($name) {
        return isset($this->modules[$name]) ? $this->modules[$name] : null;
    }

    /**
     * Get information about all modules
     *
     * @return array
     */
    public function get_module_info() {
        return $this->module_info;
    }

    /**
     * Check if a module is loaded
     *
     * @param string $name
     * @return bool
     */
    public function is_module_loaded($name) {
        return isset($this->modules[$name]);
    }

    /**
     * Get module count
     *
     * @return int
     */
    public function get_module_count() {
        return count($this->modules);
    }
}
