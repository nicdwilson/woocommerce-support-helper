<?php
namespace WooCommerceSupportHelper\BlueprintExporter;

/**
 * Main Blueprint Exporter module class
 *
 * @package WooCommerceSupportHelper\BlueprintExporter
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main class for the Blueprint Exporter module
 */
class Blueprint_Exporter {
    /**
     * @var Private_Plugin_Exporter
     */
    private $private_plugin_exporter;

    /**
     * @var array
     */
    private $exporters = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_exporters();
    }

    /**
     * Initialize all exporters
     */
    private function init_exporters() {
        // Initialize the private plugin exporter
        $this->private_plugin_exporter = new Private_Plugin_Exporter();
        $this->exporters[] = $this->private_plugin_exporter;

        // Hook into WordPress and WooCommerce
        $this->init_hooks();
    }

    /**
     * Initialize WordPress and WooCommerce hooks
     */
    private function init_hooks() {
        // Hook into WooCommerce Blueprint exporters
        add_filter('wooblueprint_exporters', array($this, 'modify_plugin_exporter'));
        
        // Hook into WooCommerce admin settings
        add_filter('woocommerce_admin_shared_settings', array($this, 'modify_plugin_list'), 20);
    }

    /**
     * Get all registered exporters
     *
     * @return array
     */
    public function get_exporters() {
        return $this->exporters;
    }

    /**
     * Get a specific exporter by name
     *
     * @param string $name
     * @return Abstract_Exporter|null
     */
    public function get_exporter($name) {
        foreach ($this->exporters as $exporter) {
            if ($exporter->get_name() === $name) {
                return $exporter;
            }
        }
        return null;
    }

    /**
     * Register a new exporter
     *
     * @param Abstract_Exporter $exporter
     */
    public function register_exporter(Abstract_Exporter $exporter) {
        $this->exporters[] = $exporter;
    }

    /**
     * Modify the plugin exporter to enable private plugin exports
     *
     * @param array $exporters
     * @return array
     */
    public function modify_plugin_exporter($exporters) {
        if ($this->private_plugin_exporter) {
            return $this->private_plugin_exporter->modify_plugin_exporter($exporters);
        }
        return $exporters;
    }

    /**
     * Modify the plugin list to include only available private plugins
     *
     * @param array $settings
     * @return array
     */
    public function modify_plugin_list($settings) {
        if ($this->private_plugin_exporter) {
            return $this->private_plugin_exporter->modify_plugin_list($settings);
        }
        return $settings;
    }

    /**
     * Get module information
     *
     * @return array
     */
    public function get_module_info() {
        return array(
            'name' => 'Blueprint Exporter',
            'description' => 'Extends WooCommerce Blueprint exporter with intelligent private plugin filtering',
            'version' => '1.0.0',
            'exporters' => array_map(function($exporter) {
                return array(
                    'name' => $exporter->get_name(),
                    'description' => $exporter->get_description(),
                );
            }, $this->exporters),
        );
    }
}
