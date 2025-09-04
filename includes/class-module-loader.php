<?php
/**
 * Module Loader Class
 *
 * @package WooCommerceSupportHelper
 * @since 1.0.0
 */

namespace WooCommerceSupportHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main module loader class
 */
class Module_Loader {
	/**
	 * Array of loaded modules.
	 *
	 * @var array
	 */
	private $modules = array();

	/**
	 * Array of module information.
	 *
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
	public function load_modules() {

		// Load Blueprint Exporter module.
		$this->load_blueprint_exporter_module();

		// Load Shipping Methods Exporter module.
		$this->load_shipping_methods_exporter_module();

		/**
		 * Load Support Admin UI module.
		 * 
		 * Temporarily disabled to focus on core export functionality.
		 * This module provides the React-based activity panel integration
		 * for the Support Helper. When enabled, it adds a Support Helper
		 * tab to the WooCommerce activity panel with export functionality.
		 * 
		 * To re-enable: Uncomment the line below and rebuild JavaScript assets.
		 */
		// $this->load_support_admin_ui_module(); // Temporarily disabled

		// Future modules can be loaded here.
		// $this->load_other_module().
	}

	/**
	 * Load the Blueprint Exporter module.
	 */
	public function load_blueprint_exporter_module() {

		try {
			$blueprint_exporter                      = new \WooCommerceSupportHelper\BlueprintExporter\Blueprint_Exporter();
			$this->modules['blueprint_exporter']     = $blueprint_exporter;
			$this->module_info['blueprint_exporter'] = $blueprint_exporter->get_module_info();
		} catch ( Exception $e ) {
			if ( class_exists( '\WooCommerceSupportHelper\Logger' ) ) {
				\WooCommerceSupportHelper\Logger::debug(
					'Error creating Blueprint Exporter instance',
					array(
						'error' => $e->getMessage(),
						'trace' => $e->getTraceAsString(),
					)
				);
			}
		}
	}

	/**
	 * Load the Shipping Methods Exporter module.
	 */
	private function load_shipping_methods_exporter_module() {

		try {
			$shipping_methods_exporter                      = new \WooCommerceSupportHelper\Shipping_Methods_Exporter();
			$this->modules['shipping_methods_exporter']     = $shipping_methods_exporter;
			$this->module_info['shipping_methods_exporter'] = $shipping_methods_exporter->get_module_info();

		} catch ( Exception $e ) {
			if ( class_exists( '\WooCommerceSupportHelper\Logger' ) ) {
				\WooCommerceSupportHelper\Logger::debug(
					'Error creating Shipping Methods Exporter instance',
					array(
						'error' => $e->getMessage(),
						'trace' => $e->getTraceAsString(),
					)
				);
			}
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
	 * Get a specific module by name.
	 *
	 * @param string $name The name of the module to retrieve.
	 * @return object|null The module object or null if not found.
	 */
	public function get_module( $name ) {
		return isset( $this->modules[ $name ] ) ? $this->modules[ $name ] : null;
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
	 * Check if a module is loaded.
	 *
	 * @param string $name The name of the module to check.
	 * @return bool True if the module is loaded, false otherwise.
	 */
	public function is_module_loaded( $name ) {
		return isset( $this->modules[ $name ] );
	}

	/**
	 * Load the Support Admin UI module.
	 */
	private function load_support_admin_ui_module() {

		try {
			$support_admin_ui                      = new \WooCommerceSupportHelper\SupportAdminUI\Support_Admin_UI();
			$this->modules['support_admin_ui']     = $support_admin_ui;
			$this->module_info['support_admin_ui'] = $support_admin_ui->get_module_info();
		} catch ( Exception $e ) {
			if ( class_exists( '\WooCommerceSupportHelper\Logger' ) ) {
				\WooCommerceSupportHelper\Logger::debug(
					'Error creating Support Admin UI instance',
					array(
						'error' => $e->getMessage(),
						'trace' => $e->getTraceAsString(),
					)
				);
			}
		}
	}

	/**
	 * Get module count
	 *
	 * @return int
	 */
	public function get_module_count() {
		return count( $this->modules );
	}
}
