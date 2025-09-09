<?php
/**
 * Support Helper REST API
 *
 * @package WooCommerce_Support_Helper
 */

namespace WooCommerceSupportHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Support Helper REST API Class
 */
class WC_Support_Helper_API {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'wp_ajax_download_export', array( $this, 'handle_download' ) );
	}

	/**
	 * Register REST API routes
	 */
	public function register_routes() {
		register_rest_route(
			'woocommerce-support-helper/v1',
			'/export',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_export' ),
				'permission_callback' => array( $this, 'check_permissions' ),
				'args'                => array(
					'type' => array(
						'required'          => true,
						'type'              => 'string',
						'enum'              => array( 'blueprint', 'shipping' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'woocommerce-support-helper/v1',
			'/settings',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);

		register_rest_route(
			'woocommerce-support-helper/v1',
			'/settings',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Check if user has permission to access the API
	 *
	 * @return bool
	 */
	public function check_permissions() {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Handle export request
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function handle_export( $request ) {
		$type = $request->get_param( 'type' );

		// Map API types to internal export types
		$type_mapping = array(
			'blueprint' => 'site-settings',
			'shipping'  => 'shipping-settings',
		);

		$export_type = isset( $type_mapping[ $type ] ) ? $type_mapping[ $type ] : $type;

		if ( ! in_array( $export_type, array( 'site-settings', 'tax-settings', 'shipping-settings' ) ) ) {
			return new WP_Error(
				'invalid_type',
				__( 'Invalid export type', 'woocommerce-support-helper' ),
				array( 'status' => 400 )
			);
		}

		try {
			// Get the module loader and generate export
			$module_loader      = \WooCommerceSupportHelper\WC_Support_Helper::instance()->get_module_loader();
			$blueprint_exporter = $module_loader->get_module( 'blueprint_exporter' );

			if ( ! $blueprint_exporter ) {
				throw new Exception( __( 'Blueprint exporter not available', 'woocommerce-support-helper' ) );
			}

			// Generate export data
			$export_data = $this->generate_blueprint_export( $export_type, $blueprint_exporter );

			// Create a temporary file for download
			$upload_dir = wp_upload_dir();
			$filename   = 'woocommerce-support-export-' . $type . '-' . date( 'Y-m-d-H-i-s' ) . '.json';
			$file_path  = $upload_dir['path'] . '/' . $filename;

			// Write export data to file
			file_put_contents( $file_path, json_encode( $export_data, JSON_PRETTY_PRINT ) );

			$download_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'download_export',
						'file'   => $filename,
						'type'   => $type,
					),
					admin_url( 'admin-ajax.php' )
				),
				'download_export'
			);

			return new WP_REST_Response(
				array(
					'success'      => true,
					'download_url' => $download_url,
					'filename'     => $filename,
					'message'      => __( 'Export completed successfully', 'woocommerce-support-helper' ),
				),
				200
			);

		} catch ( Exception $e ) {
			return new WP_Error(
				'export_error',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Generate the blueprint export data based on export type.
	 *
	 * @param string $export_type The type of export to generate.
	 * @param object $blueprint_exporter The blueprint exporter module.
	 * @return array Export data.
	 */
	private function generate_blueprint_export( $export_type, $blueprint_exporter ) {
		// Get available exporters
		$exporters = $blueprint_exporter->get_exporters();

		// Define export configurations for each type
		$export_configs = array(
			'site-settings'     => array(
				'wcGeneralSettings'  => true,
				'wcProductSettings'  => true,
				'wcTaxSettings'      => true,
				'wcShippingSettings' => true,
				'wcPaymentSettings'  => true,
				'wcEmailSettings'    => true,
				'wcAdvancedSettings' => true,
				'plugins'            => true,
				'themes'             => true,
				'customSettings'     => true,
			),
			'tax-settings'      => array(
				'wcGeneralSettings' => true,
				'wcTaxSettings'     => true,
				'wcProductSettings' => true,
				'plugins'           => true,
				'customSettings'    => false,
			),
			'shipping-settings' => array(
				'wcGeneralSettings'  => true,
				'wcProductSettings'  => true,
				'wcTaxSettings'      => true,
				'wcShippingSettings' => true,
				'plugins'            => true,
				'customSettings'     => true,
			),
		);

		$config      = $export_configs[ $export_type ];
		$export_data = array();

		// Generate export data based on configuration
		foreach ( $exporters as $exporter ) {
			$exporter_name = get_class( $exporter );
			$exporter_key  = $this->get_exporter_key( $exporter_name );

			if ( isset( $config[ $exporter_key ] ) && $config[ $exporter_key ] ) {
				try {
					$exporter_data                = $exporter->export();
					$export_data[ $exporter_key ] = $exporter_data;
				} catch ( Exception $e ) {
					$export_data[ $exporter_key ] = array( 'error' => $e->getMessage() );
				}
			}
		}

		return $export_data;
	}

	/**
	 * Get exporter key from class name.
	 *
	 * @param string $class_name The class name.
	 * @return string The exporter key.
	 */
	private function get_exporter_key( $class_name ) {
		// Extract the key from the class name
		$parts = explode( '\\', $class_name );
		$class = end( $parts );

		// Convert class name to key format
		$key = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1$2', $class ) );
		$key = str_replace( '_', '', $key );

		return $key;
	}

	/**
	 * Handle file download
	 */
	public function handle_download() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'download_export' ) ) {
			wp_die( __( 'Security check failed', 'woocommerce-support-helper' ) );
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'You do not have permission to perform this action', 'woocommerce-support-helper' ) );
		}

		$filename = isset( $_GET['file'] ) ? sanitize_text_field( $_GET['file'] ) : '';
		$type     = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';

		if ( empty( $filename ) ) {
			wp_die( __( 'No file specified', 'woocommerce-support-helper' ) );
		}

		// Validate filename to prevent directory traversal
		if ( strpos( $filename, '..' ) !== false || strpos( $filename, '/' ) !== false ) {
			wp_die( __( 'Invalid filename', 'woocommerce-support-helper' ) );
		}

		$upload_dir = wp_upload_dir();
		$file_path  = $upload_dir['path'] . '/' . $filename;

		if ( ! file_exists( $file_path ) ) {
			wp_die( __( 'File not found', 'woocommerce-support-helper' ) );
		}

		// Set headers for download
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Expires: 0' );

		// Output file content
		readfile( $file_path );

		// Clean up the temporary file
		unlink( $file_path );

		exit;
	}

	/**
	 * Get settings
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function get_settings( $request ) {
		$settings = array(
			'auto_export'            => get_option( 'wc_support_helper_auto_export', false ),
			'include_sensitive_data' => get_option( 'wc_support_helper_include_sensitive_data', false ),
			'export_format'          => get_option( 'wc_support_helper_export_format', 'json' ),
		);

		return new WP_REST_Response( $settings, 200 );
	}

	/**
	 * Update settings
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function update_settings( $request ) {
		$params = $request->get_params();

		if ( isset( $params['auto_export'] ) ) {
			update_option( 'wc_support_helper_auto_export', (bool) $params['auto_export'] );
		}

		if ( isset( $params['include_sensitive_data'] ) ) {
			update_option( 'wc_support_helper_include_sensitive_data', (bool) $params['include_sensitive_data'] );
		}

		if ( isset( $params['export_format'] ) ) {
			update_option( 'wc_support_helper_export_format', sanitize_text_field( $params['export_format'] ) );
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Settings updated successfully', 'woocommerce-support-helper' ),
			),
			200
		);
	}
}
