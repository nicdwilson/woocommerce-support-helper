<?php

namespace WooCommerceSupportHelper;

use Automattic\WooCommerce\Blueprint\Exporters\StepExporter;
use Automattic\WooCommerce\Blueprint\Steps\Step;
use Automattic\WooCommerce\Blueprint\Exporters\HasAlias;
use Automattic\WooCommerce\Blueprint\Steps\SetSiteOptions;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Australia Post Shipping Method Exporter
 * 
 * Exports Australia Post shipping method settings for WooCommerce Blueprint
 * 
 * @package WooCommerceSupportHelper
 */
class WooCommerce_Shipping_Australia_Post implements StepExporter, HasAlias {
    
    /**
     * Get the step name for this exporter
     *
     * @return string
     */
    public function get_step_name() {
        return SetSiteOptions::get_step_name();
    }

	public function get_alias() {
		return 'AustraliaPostOptions';
	}

    /**
     * Export Australia Post shipping method settings
     *
     * @return Step
     */
    public function export(): Step {
        // For now, return hardcoded demo data to test if the exporter is working
        $demo_settings = array(
            'woocommerce_australia_post_demo_setting_1' => 'Demo Value 1',
            'woocommerce_australia_post_demo_setting_2' => 'Demo Value 2',
            'woocommerce_australia_post_demo_setting_3' => 'Demo Value 3',
            'woocommerce_australia_post_demo_enabled' => 'yes',
            'woocommerce_australia_post_demo_origin_postcode' => '2000',
            'woocommerce_australia_post_demo_origin_city' => 'Sydney',
            'woocommerce_australia_post_demo_origin_state' => 'NSW',
            'woocommerce_australia_post_demo_origin_country' => 'AU',
            'woocommerce_australia_post_demo_services' => array('standard', 'express', 'priority'),
            'woocommerce_australia_post_demo_handling_fee' => '5.00',
            'woocommerce_australia_post_demo_free_shipping_threshold' => '100.00',
        );
        
        Logger::info('🇦🇺 Australia Post Exporter: Returning demo data', array(
                'demo_settings_count' => count($demo_settings),
                'demo_settings_keys' => array_keys($demo_settings),
        ));
        
        // Create a step to set these options
        return new SetSiteOptions($demo_settings);
    }

	/**
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
