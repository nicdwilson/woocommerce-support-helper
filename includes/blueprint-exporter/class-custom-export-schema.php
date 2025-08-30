<?php

namespace WooCommerceSupportHelper\BlueprintExporter;

// Only proceed if WooCommerce classes are available
if (!class_exists('\Automattic\WooCommerce\Blueprint\ExportSchema')) {
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

        // Ensure shipping exporters are initialized before getting exporters
        if (class_exists('\WooCommerceSupportHelper\Shipping_Methods_Exporter')) {
            try {
                $shipping_exporter = new \WooCommerceSupportHelper\Shipping_Methods_Exporter();
                if (class_exists('\WooCommerceSupportHelper\Logger')) {
                    \WooCommerceSupportHelper\Logger::info('🚀 Shipping Methods Exporter initialized during export');
	                \WooCommerceSupportHelper\Logger::info( 'built_in_exporters: ' . print_r( $built_in_exporters, true ));
                }
            } catch (Exception $e) {
                if (class_exists('\WooCommerceSupportHelper\Logger')) {
                    \WooCommerceSupportHelper\Logger::error('❌ Error initializing Shipping Methods Exporter during export', array(
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ));
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

	    \WooCommerceSupportHelper\Logger::info( 'this_exporters: ' . print_r( $this->exporters, true ));

	    \WooCommerceSupportHelper\Logger::info( 'exporters: ' . print_r( $exporters, true ));

        
        // Validate that the exporters are instances of StepExporter.
        $exporters = array_filter(
            $exporters,
            function ( $exporter ) {
                return $exporter instanceof StepExporter;
            }
        );
        
        // Log what exporters we found
        if (class_exists('\WooCommerceSupportHelper\Logger')) {
            \WooCommerceSupportHelper\Logger::info('🔍 Custom_Export_Schema: Found exporters', array(
                'total_exporters' => count($exporters),
                'exporter_classes' => array_map('get_class', $exporters),
                'exporter_step_names' => array_map(function($exporter) {
                    return method_exists($exporter, 'get_step_name') ? $exporter->get_step_name() : 'unknown';
                }, $exporters),
            ));
        }

        // CUSTOM MODIFICATION: Skip the step name filtering that prevents custom exporters from running
        // This allows all registered exporters to run regardless of UI step selection

        if ( count( $steps ) ) {
            foreach ( $exporters as $key => $exporter ) {
                $name  = $exporter->get_step_name();
                $alias = $exporter instanceof HasAlias ? $exporter->get_alias() : $name;
            }
        }

        // Make sure the user has the required capabilities to export the steps.
        foreach ( $exporters as $exporter ) {
            if ( ! $exporter->check_step_capabilities() ) {
                return new WP_Error( 'wooblueprint_insufficient_permissions', 'Insufficient permissions to export for step: ' . $exporter->get_step_name() );
            }
        }

        $logger = new Logger();
        $logger->start_export( $exporters );

        foreach ( $exporters as $exporter ) {
            try {
                // Log which exporter we're processing
                if (class_exists('\WooCommerceSupportHelper\Logger')) {
                    \WooCommerceSupportHelper\Logger::info('🔍 Custom_Export_Schema: Processing exporter', array(
                        'exporter_class' => get_class($exporter),
                        'step_name' => method_exists($exporter, 'get_step_name') ? $exporter->get_step_name() : 'unknown',
                    ));
                }
                
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
