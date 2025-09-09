<?php
/**
 * Custom Export Schema Class
 *
 * @package WooCommerceSupportHelper\BlueprintExporter
 * @since 1.0.0
 */

namespace WooCommerceSupportHelper\BlueprintExporter;

// Only proceed if WooCommerce classes are available.
if ( ! class_exists( '\Automattic\WooCommerce\Blueprint\ExportSchema' ) ) {
	return;
}

use Automattic\WooCommerce\Blueprint\ExportSchema;
use Automattic\WooCommerce\Blueprint\Exporters\StepExporter;
use Automattic\WooCommerce\Blueprint\Exporters\HasAlias;
use Automattic\WooCommerce\Blueprint\Logger;
use WP_Error;

/**
 * Custom ExportSchema class that bypasses step filtering
 *
 * This class extends the WooCommerce ExportSchema to allow custom exporters
 * to run regardless of the UI step selection, while maintaining compatibility
 * with the existing WooCommerce blueprint system.
 *
 * @package WooCommerceSupportHelper\BlueprintExporter
 */
class Custom_Export_Schema extends ExportSchema {

	/**
	 * Export the schema steps without filtering by step names
	 *
	 * @param string[] $steps Array of step names to export, optional.
	 *
	 * @return array|WP_Error The exported schema array or a WP_Error if the export fails.
	 */
	public function export( $steps = array() ) {
		$loading_page_path = $this->wp_apply_filters( 'wooblueprint_export_landingpage', '/' );

		/**
		 * Validate that the landing page path is a valid relative local URL path.
		 *
		 * Accepts:
		 * - /
		 * - /path/to/page
		 *
		 * Rejects:
		 * - http://example.com/path/to/page
		 * - invalid-path
		 */
		if ( ! preg_match( '#^/$|^/[^/].*#', $loading_page_path ) ) {
			return new WP_Error( 'wooblueprint_invalid_landing_page_path', 'Invalid loading page path.' );
		}

		$schema = array(
			'landingPage' => $loading_page_path,
			'steps'       => array(),
		);

		$built_in_exporters = ( new \Automattic\WooCommerce\Blueprint\BuiltInExporters() )->get_all();

		// Ensure shipping exporters are initialized before getting exporters.
		if ( class_exists( '\WooCommerceSupportHelper\Shipping_Methods_Exporter' ) ) {
			try {
				$shipping_exporter = new \WooCommerceSupportHelper\Shipping_Methods_Exporter();
			} catch ( Exception $e ) {
				if ( class_exists( '\WooCommerceSupportHelper\Logger' ) ) {
					\WooCommerceSupportHelper\Logger::error(
						'❌ Error initializing Shipping Methods Exporter during export',
						array(
							'error' => $e->getMessage(),
							'file'  => $e->getFile(),
							'line'  => $e->getLine(),
						)
					);
				}
			}
		}

		/**
		 * Filters the step exporters.
		 *
		 * Allows adding/removing custom step exporters.
		 *
		 * @param StepExporter[] $exporters Array of step exporters.
		 *
		 * @since 0.0.1
		 */
		$exporters = $this->wp_apply_filters( 'wooblueprint_exporters', array_merge( $this->exporters, $built_in_exporters ) );

		// Validate that the exporters are instances of StepExporter.
		$exporters = array_filter(
			$exporters,
			function ( $exporter ) {
				return $exporter instanceof StepExporter;
			}
		);

		// TODO: Provide a whitelist of acceptable exporters.

		// CUSTOM MODIFICATION: Skip the step name filtering that prevents custom exporters from running.
		// This allows all registered exporters to run regardless of UI step selection.

		if ( count( $steps ) ) {
			foreach ( $exporters as $key => $exporter ) {
				$name = $exporter->get_step_name();

				$alias = $exporter instanceof HasAlias ? $exporter->get_alias() : $name;
				if ( ! in_array( $name, $steps, true ) && ! in_array( $alias, $steps, true ) ) {
					unset( $exporters[ $key ] );
				}
			}
		}

		// Deduplicate exporters to prevent duplicate processing.
		$exporters = $this->deduplicate_exporters( $exporters );

		// Make sure the user has the required capabilities to export the steps.
		foreach ( $exporters as $exporter ) {
			if ( ! $exporter->check_step_capabilities() ) {
				return new WP_Error( 'wooblueprint_insufficient_permissions', 'Insufficient permissions to export for step: ' . $exporter->get_step_name() );
			}
		}

		$logger = new Logger();
		$logger->start_export( $exporters );

		foreach ( $exporters as $index => $exporter ) {
			try {

				$this->publish( 'onBeforeExport', $exporter );
				$step = $exporter->export();
				$this->custom_add_result_to_schema( $schema, $step );

			} catch ( \Throwable $e ) {
				$step_name = $exporter instanceof HasAlias ? $exporter->get_alias() : $exporter->get_step_name();
				$logger->export_step_failed( $step_name, $e );

				return new WP_Error( 'wooblueprint_export_step_failed', 'Export step failed: ' . $e->getMessage() );
			}
		}

		$logger->complete_export( $exporters );

		return $schema;
	}

	/**
	 * Deduplicate exporters to prevent duplicate processing
	 *
	 * This method ensures that each exporter is only processed once by:
	 * 1. Using class name as primary identifier
	 * 2. Using step name as secondary identifier for exporters with same class
	 * 3. Keeping the first occurrence of each unique exporter
	 *
	 * @param array $exporters Array of exporters to deduplicate.
	 * @return array Deduplicated array of exporters
	 */
	private function deduplicate_exporters( $exporters ) {
		$unique_exporters = array();

		foreach ( $exporters as $exporter ) {
			if ( ! $exporter instanceof StepExporter ) {
				continue;
			}

			$class_name = get_class( $exporter );
			$step_name  = method_exists( $exporter, 'get_step_name' ) ? $exporter->get_step_name() : 'unknown';
			$alias      = $exporter instanceof HasAlias ? $exporter->get_alias() : $step_name;

			// Create a unique identifier for this exporter.
			$exporter_id = wp_hash( $class_name . $alias . $step_name );

			if ( ! isset( $seen_exporters[ $exporter_id ] ) ) {
				$seen_exporters[ $exporter_id ] = $class_name;
				$unique_exporters[]             = $exporter;
			}
		}

		\WooCommerceSupportHelper\Logger::info( 'Unique Exporters: ' . wp_json_encode( $unique_exporters ) );

		return $unique_exporters;
	}

	/**
	 * Custom implementation of add_result_to_schema since the parent method is private
	 *
	 * @param array      $schema Schema array to add steps to.
	 * @param array|Step $step   Step or array of steps to add.
	 */
	private function custom_add_result_to_schema( array &$schema, $step ): void {
		if ( is_array( $step ) ) {
			foreach ( $step as $_step ) {
				$schema['steps'][] = $_step->get_json_array();
			}
			return;
		}

		$schema['steps'][] = $step->get_json_array();
	}
}
