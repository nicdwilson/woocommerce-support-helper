<?php
/**
 * Shipping Methods Exporter Class
 *
 * @package WooCommerceSupportHelper\ShippingMethodsExporter
 * @since 1.0.0
 */

namespace WooCommerceSupportHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class for the Shipping Methods Exporter module.
 */
class Shipping_Methods_Exporter {
	/**
	 * Array of loaded shipping exporters.
	 *
	 * @var array
	 */
	private $shipping_exporters = array();

	/**
	 * Array of supported shipping plugins.
	 *
	 * Structured like this:
	 * 'plugin-slug' => array(
	 *     'exporter' => 'Class_Name',
	 *     'label'    => 'Plugin Display Name'
	 * )
	 *
	 * @var array
	 */
	private $supported_plugins = array(
		'woocommerce-shipping-australia-post' => array(
			'exporter' => 'WooCommerce_Shipping_Australia_Post',
			'label'    => 'WooCommerce Australia Post Shipping',
		),
		'woocommerce-shipping-usps' => array(
			'exporter' => 'WooCommerce_Shipping_Usps',
			'label'    => 'WooCommerce USPS Shipping',
		),
		'woocommerce-shipping-fedex' => array(
			'exporter' => 'WooCommerce_Shipping_Fedex',
			'label'    => 'WooCommerce Fedex Shipping',
		),
		'woocommerce-shipping-ups' => array(
			'exporter' => 'WooCommerce_Shipping_Ups',
			'label'    => 'WooCommerce UPS Shipping',
		),
		'woocommerce-table-rate-shipping' => array(
			'exporter' => 'WooCommerce_Table_Rate_Shipping',
			'label'    => 'WooCommerce Table Rate Shipping',
		),
		'woocommerce-shipping-canada-post' => array(
			'exporter' => 'WooCommerce_Shipping_Canada_Post',
			'label'    => 'WooCommerce Canada Post Shipping',
		),
		'woocommerce-shipping-royalmail' => array(
			'exporter' => 'WooCommerce_Shipping_Royal_Mail',
			'label'    => 'WooCommerce Royal Mail Shipping',
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_shipping_exporters();
		$this->init_hooks();
	}

	/**
	 * Initialize all shipping exporters.
	 */
	public function init_shipping_exporters() {
		// Load all supported shipping exporters dynamically.
		foreach ( $this->supported_plugins as $plugin_slug => $plugin_data ) {
			$this->load_shipping_exporter( $plugin_slug, $plugin_data['label'], $plugin_data['exporter'] );
		}
	}

	/**
	 * Load a specific shipping exporter.
	 *
	 * @param string $plugin_slug The plugin slug.
	 * @param string $plugin_name The plugin display name.
	 * @param string $class_name The exporter class name.
	 */
	private function load_shipping_exporter( $plugin_slug, $plugin_name, $class_name ) {
		$exporter_path   = __DIR__ . '/' . $plugin_slug . '/class-' . $plugin_slug . '.php';
		$full_class_name = '\\WooCommerceSupportHelper\\' . $class_name;

		if ( file_exists( $exporter_path ) ) {

			try {
				require_once $exporter_path;

				if ( class_exists( $full_class_name ) ) {
					$exporter = new $full_class_name();

					// Check if the plugin is active before adding the exporter.
					if ( method_exists( $exporter, 'is_plugin_active' ) && $exporter->is_plugin_active() ) {
						$this->shipping_exporters[ $plugin_slug ] = $exporter;
					}
				} else {
					Logger::warning(
						'⚠️ ' . $plugin_name . ' exporter class not found after including file',
						array(
							'path'           => $exporter_path,
							'expected_class' => $full_class_name,
						)
					);
				}
			} catch ( Exception $e ) {
				Logger::error(
					'Error loading ' . $plugin_name . ' exporter',
					array(
						'path'  => $exporter_path,
						'error' => $e->getMessage(),
						'file'  => $e->getFile(),
						'line'  => $e->getLine(),
					)
				);
			}
		} else {
			Logger::warning(
				'Shipping exporter file not found',
				array(
					'path' => $exporter_path,
				)
			);
		}
	}


	/**
	 * Initialize WordPress and WooCommerce hooks
	 */
	public function init_hooks() {

		/**
		 * Add custom export options to Blueprint UI
		 */
		add_filter( 'woocommerce_admin_shared_settings', array( $this, 'add_shipping_exporter_to_ui' ), 25 );

		/**
		 * Add custom export functions
		 */
		add_filter( 'wooblueprint_exporters', array( $this, 'add_shipping_exporters' ), 10 );

		/**
		 * Make sure the payload is persisted
		 */
		add_filter( 'wc_support_helper_payload_contains_steps', array( $this, 'add_steps_to_export_schema' ), 10, 2 );
	}

	/**
	 * Add shipping exporters to the Blueprint exporters list.
	 *
	 * @param array $exporters The current list of exporters.
	 * @return array The updated list of exporters.
	 */
	public function add_shipping_exporters( $exporters ) {
		// Add only active shipping exporters to the Blueprint exporters list.
		$active_exporters = $this->get_active_shipping_exporters();

		foreach ( $active_exporters as $plugin_slug => $exporter ) {
			$exporters[] = $exporter;
		}

		return $exporters;
	}

	/**
	 * Export shipping method site options for Blueprint.
	 * This method is called by the main plugin to hook into the Blueprint export process.
	 *
	 * @param array $site_options The current site options.
	 * @return array The updated site options.
	 */
	public function export_shipping_method_site_options( $site_options ) {

		// Check if we have any active exporters available.
		$active_exporters = $this->get_active_shipping_exporters();
		if ( empty( $active_exporters ) ) {
			return $site_options;
		}

		$shipping_method_options = array();
		foreach ( $active_exporters as $exporter_key => $exporter ) {

			if ( method_exists( $exporter, 'get_site_options' ) ) {
				$exporter_options        = $exporter->get_site_options();
				$shipping_method_options = array_merge( $shipping_method_options, $exporter_options );
			}
		}

		$final_options = array_merge( $site_options, $shipping_method_options );

		if ( ! empty( $shipping_method_options ) ) {
			Logger::info( 'Shipping Export: Site options export completed - ' . count( $shipping_method_options ) . ' options added' );
		}

		return $final_options;
	}

	/**
	 * Export shipping zone configurations for Blueprint.
	 *
	 * @param array $shipping_zones The current shipping zones.
	 * @return array The updated shipping zones.
	 */
	public function export_shipping_zone_configurations( $shipping_zones ) {

		// Check if we have any active exporters available.
		$active_exporters = $this->get_active_shipping_exporters();
		if ( empty( $active_exporters ) ) {
			Logger::info( 'No active shipping exporters available for zone configuration export' );
			return $shipping_zones;
		}

		$shipping_zone_options = array();
		foreach ( $active_exporters as $exporter_key => $exporter ) {

			if ( method_exists( $exporter, 'get_shipping_zone_configurations' ) ) {
				$exporter_zones        = $exporter->get_shipping_zone_configurations();
				$shipping_zone_options = array_merge( $shipping_zone_options, $exporter_zones );

				Logger::debug(
					'Exported zone configurations from active exporter',
					array(
						'exporter_key' => $exporter_key,
						'zones_count'  => count( $exporter_zones ),
					)
				);
			} else {
				Logger::debug(
					'Exporter does not implement get_shipping_zone_configurations',
					array(
						'exporter_key'   => $exporter_key,
						'exporter_class' => get_class( $exporter ),
					)
				);
			}
		}

		Logger::info(
			'Shipping zone configurations export completed',
			array(
				'active_exporters_processed' => count( $active_exporters ),
				'total_zones_added'          => count( $shipping_zone_options ),
			)
		);

		return array_merge( $shipping_zones, $shipping_zone_options );
	}

	/**
	 * Called when Blueprint export starts.
	 */
	public function on_blueprint_export() {

		// Try to export shipping methods data.
		$site_options   = $this->export_shipping_method_site_options( array() );
		$shipping_zones = $this->export_shipping_zone_configurations( array() );
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
	 * Get only active shipping exporters
	 *
	 * @return array
	 */
	public function get_active_shipping_exporters() {
		$active_exporters = array();

		foreach ( $this->shipping_exporters as $plugin_slug => $exporter ) {
			if ( method_exists( $exporter, 'is_plugin_active' ) && $exporter->is_plugin_active() ) {
				$active_exporters[ $plugin_slug ] = $exporter;
			}
		}

		return $active_exporters;
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
			'name'              => 'Shipping Methods Exporter',
			'description'       => 'Exports shipping method settings for various WooCommerce shipping plugins',
			'version'           => '1.0.0',
			'supported_plugins' => $this->supported_plugins,
			'exporters'         => array_map(
				function ( $exporter ) {
					return array(
						'name'        => 'Shipping Exporter',
						'description' => 'Exports shipping method settings',
					);
				},
				$this->shipping_exporters
			),
		);
	}

	/**
	 * Add the shipping exporter to the WooCommerce admin shared settings.
	 * This makes it visible in the Blueprint UI's "Add New Step" dropdown.
	 *
	 * @param array $settings The current settings.
	 * @return array The updated settings.
	 */
	public function add_shipping_exporter_to_ui( $settings ) {
		// Get available shipping exporters.
		$shipping_items = array();

		// Add items only for active shipping exporters.
		$active_exporters = $this->get_active_shipping_exporters();

		foreach ( $active_exporters as $plugin_slug => $exporter ) {
			$plugin_name = isset( $this->supported_plugins[ $plugin_slug ] ) ? $this->supported_plugins[ $plugin_slug ] : ucfirst( str_replace( '-', ' ', $plugin_slug ) );

			$shipping_items[] = array(
				'id'      => $plugin_slug,
				'label'   => $plugin_name,
				'checked' => true,
			);
		}

		// Only add the group if we have active shipping items.
		if ( ! empty( $shipping_items ) ) {
			$settings['blueprint_step_groups'][] = array(
				'id'          => 'plugin_settings',
				'description' => __( 'Includes plugin settings', 'woocommerce-support-helper' ),
				'label'       => __( 'Include plugin settings', 'woocommerce-support-helper' ),
				'icon'        => 'layout',
				'items'       => $shipping_items,
			);

		} else {
			Logger::info( 'No active shipping exporters to add to Blueprint UI' );
		}

		return $settings;
	}

	/**
	 * Add shipping method steps to the export schema based on payload selections.
	 *
	 * @param array $blueprint_steps Current blueprint steps.
	 * @param array $payload The original payload from the frontend (optional).
	 * @return array Modified blueprint steps.
	 */
	public function add_steps_to_export_schema( $blueprint_steps, $payload ) {

		// If no payload provided, return original steps.
		if ( empty( $payload ) ) {
			Logger::debug( 'No payload provided, returning original steps' );
			return $blueprint_steps;
		}

		// Check if plugin_settings are selected in the payload.
		if ( ! isset( $payload['plugin_settings'] ) || empty( $payload['plugin_settings'] ) ) {
			Logger::debug( 'No plugin_settings found in payload' );
			return $blueprint_steps;
		}

		$selected_plugins = $payload['plugin_settings'];

		// Map selected plugin slugs to exporter aliases.
		foreach ( $selected_plugins as $plugin_slug ) {
			// Check if this is one of our supported shipping plugins.
			if ( isset( $this->supported_plugins[ $plugin_slug ] ) ) {
				// Get the exporter instance for this plugin.
				if ( isset( $this->shipping_exporters[ $plugin_slug ] ) ) {
					$exporter = $this->shipping_exporters[ $plugin_slug ];

					// Check if the plugin is active before processing.
					if ( method_exists( $exporter, 'is_plugin_active' ) && $exporter->is_plugin_active() ) {
						// Get the alias from the exporter.
						if ( method_exists( $exporter, 'get_alias' ) ) {
							$alias              = $exporter->get_alias();
							$shipping_aliases[] = $alias;

						} else {
							Logger::warning(
								'Exporter does not implement get_alias method',
								array(
									'plugin_slug'    => $plugin_slug,
									'exporter_class' => get_class( $exporter ),
								)
							);
						}
					} else {
						Logger::warning(
							'Skipped inactive plugin in export schema',
							array(
								'plugin_slug' => $plugin_slug,
								'reason'      => 'Plugin not active',
							)
						);
					}
				} else {
					Logger::warning(
						'Exporter not found for plugin slug',
						array(
							'plugin_slug'         => $plugin_slug,
							'available_exporters' => array_keys( $this->shipping_exporters ),
						)
					);
				}
			} else {
				Logger::debug(
					'Plugin slug not in supported plugins',
					array(
						'plugin_slug'       => $plugin_slug,
						'supported_plugins' => array_keys( $this->supported_plugins ),
					)
				);
			}
		}

		if ( ! empty( $shipping_aliases ) ) {
			$blueprint_steps = array_merge( $blueprint_steps, $shipping_aliases );
		}

		return $blueprint_steps;
	}
}
