<?php
/**
 * USPS Shipping Method Exporter Class
 *
 * @package WooCommerceSupportHelper\ShippingMethodsExporter
 * @since 1.0.0
 */

namespace WooCommerceSupportHelper;

use Automattic\WooCommerce\Blueprint\Exporters\StepExporter;
use Automattic\WooCommerce\Blueprint\Steps\Step;
use Automattic\WooCommerce\Blueprint\Exporters\HasAlias;
use Automattic\WooCommerce\Blueprint\Steps\SetSiteOptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * USPS Shipping Method Exporter.
 *
 * Exports USPS shipping method settings for WooCommerce Blueprint.
 *
 * @package WooCommerceSupportHelper
 */
class WooCommerce_Shipping_Usps implements StepExporter, HasAlias {

	/**
	 * Plugin slug for USPS shipping method.
	 */
	const PLUGIN_SLUG = 'woocommerce-shipping-usps';

	/**
	 * Method ID for USPS shipping method.
	 */
	const METHOD_ID = 'usps';

	/**
	 * Get the step name for this exporter.
	 *
	 * @return string
	 */
	public function get_step_name() {
		return SetSiteOptions::get_step_name();
	}

	/**
	 * Get the alias for this exporter.
	 *
	 * @return string
	 */
	public function get_alias() {
		return 'UspsOptions';
	}

	/**
	 * Export USPS shipping method settings.
	 *
	 * @return Step
	 */
	public function export(): Step {
		// Get all USPS site options.
		$site_options = $this->get_site_options();

		Logger::info( '🇺🇸 USPS Exporter: Export completed successfully' );

		// Create a step to set these options.
		return new SetSiteOptions( $site_options );
	}

	/**
	 * Get USPS site options for Blueprint export.
	 *
	 * @return array
	 */
	public function get_site_options() {
		$site_options = array();

		// Get global USPS settings.
		$global_settings = get_option( 'woocommerce_usps_settings', array() );
		if ( ! empty( $global_settings ) ) {
			$site_options['woocommerce_usps_settings'] = $this->sanitize_settings( $global_settings );
			Logger::info(
				'🇺🇸 Found global USPS settings',
				array(
					'settings_count' => count( $global_settings ),
					'settings_keys'  => array_keys( $global_settings ),
				)
			);
		} else {
			Logger::warning( '🇺🇸 No global USPS settings found' );
		}

		// Get zone-specific settings with better error handling.
		try {
			$shipping_zones = $this->get_shipping_zones_with_usps();
			
			Logger::debug(
				'Found shipping zones with USPS',
			);
	
			foreach ( $shipping_zones as $zone ) {
				$method_settings = $this->get_method_settings_for_zone( $zone );
				if ( ! empty( $method_settings ) ) {
					$option_name                  = 'woocommerce_usps_' . $zone['method_instance_id'] . '_settings';
					$site_options[ $option_name ] = $this->sanitize_settings( $method_settings );
					Logger::debug(
						'🇺🇸 Found method settings for zone',
						array(
							'zone_id'            => $zone['zone_id'],
							'zone_name'          => $zone['zone_name'],
							'method_instance_id' => $zone['method_instance_id'],
							'settings_count'     => count( $method_settings ),
						)
					);
				}
			}

		} catch ( Exception $e ) {
			Logger::error(
				'🇺🇸 Error getting shipping zones with USPS',
				array(
					'error' => $e->getMessage(),
				)
			);
		}

		Logger::debug( '🇺🇸 Found ' . count( $site_options ) . ' USPS site options' );

		return $site_options;
	}

	/**
	 * Get shipping zones that have USPS shipping method configured.
	 *
	 * @return array
	 */
	public function get_shipping_zones_with_usps() {
		$zones_with_usps = array();

		$data_store = \WC_Data_Store::load( 'shipping-zone' );

		$raw_zones = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new \WC_Shipping_Zone( $raw_zone );
		}
		$zones[] = new \WC_Shipping_Zone( 0 ); // ADD ZONE "0" MANUALLY.

		if ( ! empty( $zones ) ) {
			Logger::debug( '🇺🇸 Found ' . count( $zones ) . ' shipping zones' );
		}

		foreach ( $zones as $zone ) {
				Logger::debug( '🇺🇸 Found zone ' . $zone->get_id() . ' ' . $zone->get_zone_name() );
				$methods = $zone->get_shipping_methods();
			foreach ( $methods as $method ) {
				if ( $method->id === self::METHOD_ID ) {
					Logger::debug( '🇺🇸 Found method ' . $method->id );
					$zones_with_usps[] = array(
						'zone_id'            => $zone->get_id(),
						'zone_name'          => $zone->get_zone_name(),
						'method_instance_id' => $method->get_instance_id(),
					);
				}
			}
		}

		Logger::debug( '🇺🇸 Found ' . count( $zones_with_usps ) . ' zones with USPS' );

		return $zones_with_usps;
	}

	/**
	 * Get method settings for a specific shipping zone.
	 *
	 * @param array $zone Zone data.
	 * @return array
	 */
	private function get_method_settings_for_zone( $zone ) {
		if ( ! isset( $zone['method_instance_id'] ) ) {
			return array();
		}

		$option_name = 'woocommerce_usps_' . $zone['method_instance_id'] . '_settings';
		return get_option( $option_name, array() );
	}

	/**
	 * Sanitize settings to remove sensitive information while preserving configuration.
	 *
	 * @param array $settings Settings to sanitize.
	 * @return array Sanitized settings
	 */
	private function sanitize_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		$sanitized = $settings;

		// List of sensitive keys to sanitize.
		// 'api_key' is not included in this list.
		$sensitive_keys = array(
			'user_id',
			'password',
			'secret',
			'token',
			'auth_key',
			'private_key',
			'api_key',
			'api_secret',
		);

		// Sanitize sensitive keys.
		foreach ( $sensitive_keys as $key ) {
			if ( isset( $sanitized[ $key ] ) && ! empty( $sanitized[ $key ] ) ) {
				$sanitized[ $key ] = '***CONFIGURED***';
			}
		}

		// Recursively sanitize nested arrays.
		foreach ( $sanitized as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = $this->sanitize_settings( $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Get shipping zone configurations for Blueprint export.
	 *
	 * @return array
	 */
	public function get_shipping_zone_configurations() {
		$zone_configurations = array();

		$zones_with_usps = $this->get_shipping_zones_with_usps();
		foreach ( $zones_with_usps as $zone ) {
			$method_settings = $this->get_method_settings_for_zone( $zone );
			if ( ! empty( $method_settings ) ) {
				$zone_configurations[] = array(
					'zone_id'            => $zone['zone_id'],
					'zone_name'          => $zone['zone_name'],
					'method_id'          => self::METHOD_ID,
					'method_instance_id' => $zone['method_instance_id'],
					'method_settings'    => $this->sanitize_settings( $method_settings ),
				);
			}
		}

		return $zone_configurations;
	}


	/**
	 * Get comprehensive method settings organized by category.
	 *
	 * @return array
	 */
	public function get_method_settings() {
		$settings = array(
			'general'   => array(),
			'services'  => array(),
			'packaging' => array(),
			'advanced'  => array(),
		);

		// Get global settings.
		$global_settings = get_option( 'woocommerce_usps_settings', array() );
		if ( ! empty( $global_settings ) ) {
			$settings['general'] = $this->sanitize_settings( $global_settings );
			Logger::info(
				'🇺🇸 Added global USPS settings to method settings',
				array(
					'settings_count' => count( $global_settings ),
				)
			);
		}

		// Get zone-specific settings.
		try {
			$zones_with_usps = $this->get_shipping_zones_with_usps();
			foreach ( $zones_with_usps as $zone ) {
				$method_settings = $this->get_method_settings_for_zone( $zone );
				if ( ! empty( $method_settings ) ) {
					// Categorize settings based on common USPS configuration patterns.
					$categorized = $this->categorize_settings( $method_settings );
					$settings    = array_merge_recursive( $settings, $categorized );

					Logger::info(
						'🇺🇸 Added zone settings to method settings',
						array(
							'zone_id'           => $zone['zone_id'],
							'zone_name'         => $zone['zone_name'],
							'categorized_count' => count( $categorized ),
						)
					);
				}
			}
		} catch ( Exception $e ) {
			Logger::error(
				'🇺🇸 Error getting zone-specific method settings',
				array(
					'error' => $e->getMessage(),
				)
			);
		}

		// Add demo data if no real settings.
		if ( empty( $settings['general'] ) ) {
			$settings['general'] = $this->get_demo_settings();
			Logger::info( '🇺🇸 Added demo USPS settings due to no real settings found' );
		}

		Logger::info(
			'🇺🇸 Method settings compilation completed',
			array(
				'total_sections' => count( $settings ),
				'sections'       => array_keys( $settings ),
			)
		);

		return $settings;
	}

	/**
	 * Categorize settings into logical groups.
	 *
	 * @param array $settings Settings to categorize.
	 * @return array Categorized settings
	 */
	private function categorize_settings( $settings ) {
		$categorized = array(
			'general'   => array(),
			'services'  => array(),
			'packaging' => array(),
			'advanced'  => array(),
		);

		// Define key patterns for each category.
		$service_keys   = array( 'service', 'domestic', 'international', 'rate' );
		$packaging_keys = array( 'packaging', 'box', 'weight', 'package' );
		$advanced_keys  = array( 'debug', 'tax', 'handling', 'free' );

		foreach ( $settings as $key => $value ) {
			$key_lower = strtolower( $key );

			if ( in_array( $key_lower, $service_keys ) || strpos( $key_lower, 'service' ) !== false ) {
				$categorized['services'][ $key ] = $value;
			} elseif ( in_array( $key_lower, $packaging_keys ) || strpos( $key_lower, 'pack' ) !== false ) {
				$categorized['packaging'][ $key ] = $value;
			} elseif ( in_array( $key_lower, $advanced_keys ) || strpos( $key_lower, 'debug' ) !== false ) {
				$categorized['advanced'][ $key ] = $value;
			} else {
				$categorized['general'][ $key ] = $value;
			}
		}

		return $categorized;
	}

	/**
	 * Get full export data including all USPS configuration.
	 *
	 * @return array
	 */
	public function export_data() {
		return array(
			'site_options'    => $this->get_site_options(),
			'shipping_zones'  => $this->get_shipping_zone_configurations(),
			'method_settings' => $this->get_method_settings(),
		);
	}

	/**
	 * Check if the USPS plugin is active.
	 *
	 * @return bool
	 */
	public function is_plugin_active() {
		return is_plugin_active( self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php' );
	}

	/**
	 * Get the plugin slug.
	 *
	 * @return string
	 */
	public function get_plugin_slug() {
		return self::PLUGIN_SLUG;
	}

	/**
	 * Get the method ID.
	 *
	 * @return string
	 */
	public function get_method_id() {
		return self::METHOD_ID;
	}

	/**
	 * Check if the current user has the required capabilities to export USPS settings.
	 *
	 * @return bool
	 */
	public function check_step_capabilities(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_users' ) ) {
			return false;
		}

		return true;
	}
}
