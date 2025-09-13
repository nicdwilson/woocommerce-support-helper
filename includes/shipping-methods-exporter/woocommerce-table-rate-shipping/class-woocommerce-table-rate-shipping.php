<?php
/**
 * Table Rate Shipping Method Exporter Class
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
 * Table Rate Shipping Method Exporter.
 *
 * Exports Table Rate shipping method settings for WooCommerce Blueprint.
 *
 * @package WooCommerceSupportHelper
 */
class WooCommerce_Table_Rate_Shipping implements StepExporter, HasAlias {

	/**
	 * Plugin slug for Table Rate shipping method.
	 */
	const PLUGIN_SLUG = 'woocommerce-table-rate-shipping';

	/**
	 * Method ID for Table Rate shipping method.
	 */
	const METHOD_ID = 'table_rate';

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
		return 'wcShippingTableRate';
	}

	/**
	 * Export Table Rate shipping method settings.
	 *
	 * @return Step
	 */
	public function export(): Step {
		// Get all Table Rate site options.
		$site_options = $this->get_site_options();

		Logger::info( 'Table Rate Exporter: Export completed successfully' );

		// Create a step to set these options.
		return new SetSiteOptions( $site_options );
	}

	/**
	 * Get Table Rate site options for Blueprint export.
	 *
	 * @return array
	 */
	public function get_site_options() {
		$site_options = array();

		// Get global Table Rate settings.
		$global_settings = get_option( 'woocommerce_table_rate_settings', array() );
		if ( ! empty( $global_settings ) ) {
			$site_options['woocommerce_table_rate_settings'] = $this->sanitize_settings( $global_settings );
		}

		// Get per-method settings for each shipping zone.
		$zones_with_table_rate = $this->get_shipping_zones_with_table_rate();

		foreach ( $zones_with_table_rate as $zone ) {
			$method_settings = $this->get_method_settings_for_zone( $zone );
			if ( ! empty( $method_settings ) ) {
				$option_name                  = 'woocommerce_table_rate_' . $zone['method_instance_id'] . '_settings';
				$site_options[ $option_name ] = $this->sanitize_settings( $method_settings );
			}
		}

		// Export table rate data from custom tables if available.
		$table_rate_data = $this->get_table_rate_data();
		if ( ! empty( $table_rate_data ) ) {
			$site_options['woocommerce_table_rate_data'] = $table_rate_data;
		}

		return $site_options;
	}

	/**
	 * Get shipping zones that have Table Rate shipping method configured.
	 *
	 * @return array
	 */
	public function get_shipping_zones_with_table_rate() {
		$zones_with_table_rate = array();

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
					$zones_with_table_rate[] = array(
						'zone_id'            => $zone->get_id(),
						'zone_name'          => $zone->get_zone_name(),
						'method_instance_id' => $method->get_instance_id(),
					);
				}
			}
		}

		return $zones_with_table_rate;
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

		$option_name = 'woocommerce_table_rate_' . $zone['method_instance_id'] . '_settings';
		return get_option( $option_name, array() );
	}

	/**
	 * Get table rate data from custom database tables.
	 *
	 * @return array
	 */
	private function get_table_rate_data() {
		global $wpdb;

		$table_rate_data = array();

		// Check if the table rate shipping tables exist.
		$table_name = $wpdb->prefix . 'woocommerce_shipping_table_rates';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
			// Get all table rate entries.
			$results = $wpdb->get_results( "SELECT * FROM $table_name", ARRAY_A );
			
			if ( ! empty( $results ) ) {
				$table_rate_data['table_rates'] = $this->sanitize_table_rate_data( $results );
			}
		}

		// Also check for any other table rate related options.
		$table_rate_options = array(
			'woocommerce_table_rate_priorities',
			'woocommerce_table_rate_conditions',
			'woocommerce_table_rate_labels',
		);

		foreach ( $table_rate_options as $option_name ) {
			$option_value = get_option( $option_name, array() );
			if ( ! empty( $option_value ) ) {
				$table_rate_data[ $option_name ] = $this->sanitize_settings( $option_value );
			}
		}

		return $table_rate_data;
	}

	/**
	 * Sanitize table rate data to remove sensitive information.
	 *
	 * @param array $data Table rate data to sanitize.
	 * @return array Sanitized data
	 */
	private function sanitize_table_rate_data( $data ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}

		$sanitized = array();

		foreach ( $data as $row ) {
			$sanitized_row = $row;
			
			// Remove or sanitize sensitive fields if any exist.
			$sensitive_fields = array( 'rate_id', 'rate_key', 'rate_condition' );
			
			foreach ( $sensitive_fields as $field ) {
				if ( isset( $sanitized_row[ $field ] ) && ! empty( $sanitized_row[ $field ] ) ) {
					// For table rates, we typically want to preserve the structure
					// but might want to anonymize certain identifiers.
					if ( $field === 'rate_key' ) {
						$sanitized_row[ $field ] = '***CONFIGURED***';
					}
				}
			}

			$sanitized[] = $sanitized_row;
		}

		return $sanitized;
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

		$zones_with_table_rate = $this->get_shipping_zones_with_table_rate();
		foreach ( $zones_with_table_rate as $zone ) {
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
			'general'     => array(),
			'table_rates' => array(),
			'conditions'  => array(),
			'advanced'    => array(),
		);

		// Get global settings.
		$global_settings = get_option( 'woocommerce_table_rate_settings', array() );
		if ( ! empty( $global_settings ) ) {
			$settings['general'] = $this->sanitize_settings( $global_settings );
		}

		// Get zone-specific settings.
		$zones_with_table_rate = $this->get_shipping_zones_with_table_rate();
		foreach ( $zones_with_table_rate as $zone ) {
			$method_settings = $this->get_method_settings_for_zone( $zone );
			if ( ! empty( $method_settings ) ) {
				// Categorize settings based on common Table Rate configuration patterns.
				$categorized = $this->categorize_settings( $method_settings );
				$settings    = array_merge_recursive( $settings, $categorized );
			}
		}

		// Get table rate data.
		$table_rate_data = $this->get_table_rate_data();
		if ( ! empty( $table_rate_data ) ) {
			$settings['table_rates'] = $table_rate_data;
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
			'general'     => array(),
			'table_rates' => array(),
			'conditions'  => array(),
			'advanced'    => array(),
		);

		// Define key patterns for each category.
		$condition_keys = array( 'condition', 'rate', 'priority', 'label' );
		$advanced_keys  = array( 'debug', 'tax', 'handling', 'free', 'calculation' );

		foreach ( $settings as $key => $value ) {
			$key_lower = strtolower( $key );

			if ( in_array( $key_lower, $condition_keys ) || strpos( $key_lower, 'condition' ) !== false ) {
				$categorized['conditions'][ $key ] = $value;
			} elseif ( in_array( $key_lower, $advanced_keys ) || strpos( $key_lower, 'debug' ) !== false ) {
				$categorized['advanced'][ $key ] = $value;
			} else {
				$categorized['general'][ $key ] = $value;
			}
		}

		return $categorized;
	}

	/**
	 * Get full export data including all Table Rate configuration.
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
	 * Check if the Table Rate plugin is active.
	 *
	 * @return bool
	 */
	public function is_plugin_active() {
		// Check for common table rate shipping plugin files.
		$possible_files = array(
			self::PLUGIN_SLUG . '/' . self::PLUGIN_SLUG . '.php',
			'woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php',
		);

		foreach ( $possible_files as $file ) {
			if ( is_plugin_active( $file ) ) {
				Logger::info( 'Table Rate Shipping plugin detected via file: ' . $file );
				return true;
			}
		}

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
	 * Check if the current user has the required capabilities to export Table Rate settings.
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
