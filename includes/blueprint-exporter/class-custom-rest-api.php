<?php
/**
 * Custom REST API Class
 *
 * @package WooCommerceSupportHelper\BlueprintExporter
 * @since 1.0.0
 */

namespace WooCommerceSupportHelper\BlueprintExporter;

// Only proceed if WooCommerce classes are available.
if ( ! class_exists( '\Automattic\WooCommerce\Admin\Features\Blueprint\RestApi' ) ) {
	return;
}

use Automattic\WooCommerce\Admin\Features\Blueprint\RestApi;
use Automattic\WooCommerce\Blueprint\Exporters\ExportInstallPluginSteps;
use Automattic\WooCommerce\Blueprint\Exporters\ExportInstallThemeSteps;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Response;

/**
 * Custom REST API class that uses our custom ExportSchema
 *
 * This class extends the WooCommerce Blueprint RestApi to use our custom
 * ExportSchema that bypasses step filtering, allowing all exporters to run.
 *
 * @package WooCommerceSupportHelper\BlueprintExporter
 */
class Custom_Rest_Api extends RestApi {

	/**
	 * Handle the export request using our custom ExportSchema
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_HTTP_Response The response object.
	 */
	public function export( $request ) {
		$payload = $request->get_param( 'steps' );
		$steps   = $this->steps_payload_to_blueprint_steps( $payload );

		// Use our custom ExportSchema instead of the default one.
		$exporter = new Custom_Export_Schema();

		if ( isset( $payload['plugins'] ) ) {
			$exporter->on_before_export(
				'installPlugin',
				function ( ExportInstallPluginSteps $exporter ) use ( $payload ) {
					$exporter->filter(
						function ( array $plugins ) use ( $payload ) {
							return array_intersect_key( $plugins, array_flip( $payload['plugins'] ) );
						}
					);
				}
			);
		}

		if ( isset( $payload['themes'] ) ) {
			$exporter->on_before_export(
				'installTheme',
				function ( ExportInstallThemeSteps $exporter ) use ( $payload ) {
					$exporter->filter(
						function ( array $plugins ) use ( $payload ) {
							return array_intersect_key( $plugins, array_flip( $payload['themes'] ) );
						}
					);
				}
			);
		}

		$data = $exporter->export( $steps );

		if ( is_wp_error( $data ) ) {
			return new WP_REST_Response( $data, 400 );
		}

		return new WP_HTTP_Response(
			array(
				'data' => $data,
				'type' => 'json',
			)
		);
	}
}
