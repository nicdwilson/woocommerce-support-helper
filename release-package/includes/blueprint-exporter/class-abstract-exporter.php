<?php
/**
 * Abstract Exporter Class
 *
 * @package WooCommerceSupportHelper\BlueprintExporter
 * @since 1.0.0
 */

namespace WooCommerceSupportHelper\BlueprintExporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract base class for all exporters
 */
abstract class Abstract_Exporter {
	/**
	 * Initialize the exporter
	 */
	abstract public function init();

	/**
	 * Get the exporter name
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Get the exporter description
	 *
	 * @return string
	 */
	abstract public function get_description();

	/**
	 * Get the data to export
	 *
	 * @return array
	 */
	abstract public function get_data();
}
