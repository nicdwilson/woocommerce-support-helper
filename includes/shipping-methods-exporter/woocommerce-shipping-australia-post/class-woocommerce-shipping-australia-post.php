<?php
/**
 * Australia Post Shipping Method Exporter Class
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
 * Australia Post Shipping Method Exporter.
 *
 * Exports Australia Post shipping method settings for WooCommerce Blueprint.
 *
 * @package WooCommerceSupportHelper
 */
class WooCommerce_Shipping_Australia_Post implements StepExporter, HasAlias {

	/**
	 * Plugin slug for Australia Post shipping method.
	 */
	const PLUGIN_SLUG = 'woocommerce-shipping-australia-post';

	/**
	 * Method ID for Australia Post shipping method.
	 */
	const METHOD_ID = 'australia_post';

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
		return 'wcShippingAustraliaPost';
	}

	/**
	 * Export Australia Post shipping method settings.
	 *
	 * @return Step
	 */
	public function export(): Step {
		Logger::info( '🇦🇺 Australia Post Exporter: Starting configuration export' );

		// Get all Australia Post site options.
		$site_options = $this->get_site_options();

		Logger::info(
			'Australia Post Exporter: Export completed'
		);

		// Create a step to set these options.
		return new SetSiteOptions( $site_options );
	}

	/**
	 * Get Australia Post site options for Blueprint export.
	 *
	 * @return array
	 */
	public function get_site_options() {
		$site_options = array();

		// Get global Australia Post settings.
		$global_settings = get_option( 'woocommerce_australia_post_settings', array() );
		if ( ! empty( $global_settings ) ) {
			$site_options['woocommerce_australia_post_settings'] = $this->sanitize_settings( $global_settings );
			Logger::debug(
				'Found global Australia Post settings',
			);
		}

		// Get per-method settings for each shipping zone.
		$zones_with_australia_post = $this->get_shipping_zones_with_australia_post();

		Logger::debug(
			'Found shipping zones with Australia Post',
		);

		foreach ( $zones_with_australia_post as $zone ) {
			$method_settings = $this->get_method_settings_for_zone( $zone );
			if ( ! empty( $method_settings ) ) {
				$option_name                  = 'woocommerce_australia_post_' . $zone['method_instance_id'] . '_settings';
				$site_options[ $option_name ] = $this->sanitize_settings( $method_settings );
				Logger::debug(
					'🇦🇺 Found method settings for zone',
					array(
						'zone_id'            => $zone['zone_id'],
						'zone_name'          => $zone['zone_name'],
						'method_instance_id' => $zone['method_instance_id'],
						'settings_count'     => count( $method_settings ),
					)
				);
			}
		}

		return $site_options;
	}

	/**
	 * Get shipping zones that have Australia Post shipping method configured.
	 *
	 * @return array
	 */
	public function get_shipping_zones_with_australia_post() {
		$zones_with_australia_post = array();

		$data_store = \WC_Data_Store::load( 'shipping-zone' );

		$raw_zones = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new \WC_Shipping_Zone( $raw_zone );
		}
		$zones[] = new \WC_Shipping_Zone( 0 ); // ADD ZONE "0" MANUALLY.

		if ( ! empty( $zones ) ) {
			Logger::debug( '🇦🇺 Found ' . count( $zones ) . ' shipping zones' );
		}

		foreach ( $zones as $zone ) {
				Logger::debug( '🇦🇺 Found zone ' . $zone->get_id() . ' ' . $zone->get_zone_name() );
				$methods = $zone->get_shipping_methods();
			foreach ( $methods as $method ) {
				if ( $method->id === self::METHOD_ID ) {
					Logger::debug( '🇦🇺 Found method ' . $method->id );
					$zones_with_australia_post[] = array(
						'zone_id'            => $zone->get_id(),
						'zone_name'          => $zone->get_zone_name(),
						'method_instance_id' => $method->get_instance_id(),
					);
				}
			}
		}

		Logger::debug( '🇦🇺 Found ' . count( $zones_with_australia_post ) . ' zones with Australia Post' );

		return $zones_with_australia_post;
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

		$option_name = 'woocommerce_australia_post_' . $zone['method_instance_id'] . '_settings';
		return get_option( $option_name, array() );
	}

	/**
	 * Sanitize settings to remove sensitive information while preserving configuration
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
		$sensitive_keys = array(
			'api_key',
			'api_password',
			'password',
			'secret',
			'token',
			'auth_key',
			'private_key',
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

		$zones_with_australia_post = $this->get_shipping_zones_with_australia_post();
		foreach ( $zones_with_australia_post as $zone ) {
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
			'general'  => array(),
			'packing'  => array(),
			'services' => array(),
			'advanced' => array(),
		);

		// Get global settings.
		$global_settings = get_option( 'woocommerce_australia_post_settings', array() );
		if ( ! empty( $global_settings ) ) {
			$settings['general'] = $this->sanitize_settings( $global_settings );
		}

		// Get zone-specific settings.
		$zones_with_australia_post = $this->get_shipping_zones_with_australia_post();
		foreach ( $zones_with_australia_post as $zone ) {
			$method_settings = $this->get_method_settings_for_zone( $zone );
			if ( ! empty( $method_settings ) ) {
				// Categorize settings based on common Australia Post configuration patterns.
				$categorized = $this->categorize_settings( $method_settings );
				$settings    = array_merge_recursive( $settings, $categorized );
			}
		}

		return $settings;
	}

	/**
	 * Check if the Australia Post plugin is active.
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
	 * Check if the current user has the required capabilities to export Australia Post settings.
	 *
	 * @return bool
	 */
	public function check_step_capabilities(): bool {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return true;
	}
}
