<?php
/**
 * Royal Mail Shipping Method Exporter Class
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
 * Royal Mail Shipping Method Exporter.
 *
 * Exports Royal Mail shipping method settings for WooCommerce Blueprint.
 *
 * @package WooCommerceSupportHelper
 */
class WooCommerce_Shipping_Royal_Mail implements StepExporter, HasAlias {

	/**
	 * Plugin slug for Royal Mail shipping method.
	 */
	const PLUGIN_SLUG = 'woocommerce-shipping-royalmail';

	/**
	 * Method ID for Royal Mail shipping method.
	 */
	const METHOD_ID = 'royal_mail';

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
		return 'wcShippingRoyalMail';
	}

	/**
	 * Export Royal Mail shipping method settings.
	 *
	 * @return Step
	 */
	public function export(): Step {
		// Get all Royal Mail site options.
		$site_options = $this->get_site_options();

		Logger::info( 'Royal Mail Exporter: Export completed successfully' );

		// Create a step to set these options.
		return new SetSiteOptions( $site_options );
	}

	/**
	 * Get Royal Mail site options for Blueprint export.
	 *
	 * @return array
	 */
	public function get_site_options() {
		$site_options = array();

		// Get global Royal Mail settings.
		$global_settings = get_option( 'woocommerce_royal_mail_settings', array() );
		if ( ! empty( $global_settings ) ) {
			$site_options['woocommerce_royal_mail_settings'] = $this->sanitize_settings( $global_settings );
		}

		// Get per-method settings for each shipping zone.
		$zones_with_royal_mail = $this->get_shipping_zones_with_royal_mail();

		foreach ( $zones_with_royal_mail as $zone ) {
			$method_settings = $this->get_method_settings_for_zone( $zone );
			if ( ! empty( $method_settings ) ) {
				$option_name                  = 'woocommerce_royal_mail_' . $zone['method_instance_id'] . '_settings';
				$site_options[ $option_name ] = $this->sanitize_settings( $method_settings );
			}
		}

		return $site_options;
	}

	/**
	 * Get shipping zones that have Royal Mail shipping method configured.
	 *
	 * @return array
	 */
	public function get_shipping_zones_with_royal_mail() {
		$zones_with_royal_mail = array();

		$data_store = \WC_Data_Store::load( 'shipping-zone' );

		$raw_zones = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new \WC_Shipping_Zone( $raw_zone );
		}
		$zones[] = new \WC_Shipping_Zone( 0 ); // ADD ZONE "0" MANUALLY.

		foreach ( $zones as $zone ) {
			$methods = $zone->get_shipping_methods();
			foreach ( $methods as $method ) {
				if ( $method->id === self::METHOD_ID ) {
					$zones_with_royal_mail[] = array(
						'zone_id'            => $zone->get_id(),
						'zone_name'          => $zone->get_zone_name(),
						'method_instance_id' => $method->get_instance_id(),
					);
				}
			}
		}

		return $zones_with_royal_mail;
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

		$option_name = 'woocommerce_royal_mail_' . $zone['method_instance_id'] . '_settings';
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
		$sensitive_keys = array(
			'api_key',
			'api_password',
			'password',
			'secret',
			'token',
			'auth_key',
			'private_key',
			'user_id',
			'api_secret',
			'account_number',
			'access_key',
			'username',
			'key',
			'account',
			'client_id',
			'client_secret',
			'customer_number',
			'contract_id',
			'merchant_id',
			'postcode',
			'postal_code',
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

		$zones_with_royal_mail = $this->get_shipping_zones_with_royal_mail();
		foreach ( $zones_with_royal_mail as $zone ) {
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
		$global_settings = get_option( 'woocommerce_royal_mail_settings', array() );
		if ( ! empty( $global_settings ) ) {
			$settings['general'] = $this->sanitize_settings( $global_settings );
		}

		// Get zone-specific settings.
		$zones_with_royal_mail = $this->get_shipping_zones_with_royal_mail();
		foreach ( $zones_with_royal_mail as $zone ) {
			$method_settings = $this->get_method_settings_for_zone( $zone );
			if ( ! empty( $method_settings ) ) {
				// Categorize settings based on common Royal Mail configuration patterns.
				$categorized = $this->categorize_settings( $method_settings );
				$settings    = array_merge_recursive( $settings, $categorized );
			}
		}

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
			'general'  => array(),
			'packing'  => array(),
			'services' => array(),
			'advanced' => array(),
		);

		// Define key patterns for each category.
		$service_keys   = array( 'service', 'domestic', 'international', 'rate', 'delivery', 'tracking' );
		$packing_keys   = array( 'packaging', 'box', 'weight', 'package', 'dimension', 'parcel' );
		$advanced_keys  = array( 'debug', 'tax', 'handling', 'free', 'insurance', 'signature' );

		foreach ( $settings as $key => $value ) {
			$key_lower = strtolower( $key );

			if ( in_array( $key_lower, $service_keys ) || strpos( $key_lower, 'service' ) !== false ) {
				$categorized['services'][ $key ] = $value;
			} elseif ( in_array( $key_lower, $packing_keys ) || strpos( $key_lower, 'pack' ) !== false ) {
				$categorized['packing'][ $key ] = $value;
			} elseif ( in_array( $key_lower, $advanced_keys ) || strpos( $key_lower, 'debug' ) !== false ) {
				$categorized['advanced'][ $key ] = $value;
			} else {
				$categorized['general'][ $key ] = $value;
			}
		}

		return $categorized;
	}

	/**
	 * Get full export data including all Royal Mail configuration.
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
	 * Check if the Royal Mail plugin is active.
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
	 * Check if the current user has the required capabilities to export Royal Mail settings.
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
