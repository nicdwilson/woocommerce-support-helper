<?php
namespace WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost;

/**
 * Australia Post shipping method exporter
 *
 * @package WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use WooCommerceSupportHelper\ShippingMethodsExporter\Abstract_Shipping_Exporter;

/**
 * Australia Post shipping method exporter class
 */
class Australia_Post_Exporter extends Abstract_Shipping_Exporter {
    /**
     * Initialize the exporter
     */
    protected function init() {
        $this->plugin_slug = 'woocommerce-shipping-australia-post';
        $this->plugin_name = 'Australia Post';
        $this->method_id = 'australia_post';
    }

    /**
     * Get the exporter name
     *
     * @return string
     */
    public function get_name() {
        return 'Australia Post Shipping';
    }

    /**
     * Get the exporter description
     *
     * @return string
     */
    public function get_description() {
        return 'Exports Australia Post shipping method settings and configuration';
    }

    /**
     * Check if the Australia Post shipping plugin is active
     *
     * @return bool
     */
    public function is_plugin_active() {
        return $this->check_plugin_active();
    }

    /**
     * Get Australia Post shipping method settings
     *
     * @return array
     */
    public function get_method_settings() {
        if (!$this->is_plugin_active()) {
            return array();
        }

        $settings = array();

        // Get general Australia Post settings
        $general_settings = $this->get_plugin_option('woocommerce_australia_post_settings', array());
        if (!empty($general_settings)) {
            $settings['general'] = $this->sanitize_shipping_data($general_settings);
        }

        // Get specific shipping zone settings
        $shipping_zones = $this->get_shipping_zone_settings();
        if (!empty($shipping_zones)) {
            $settings['shipping_zones'] = $shipping_zones;
        }

        // Get API configuration
        $api_settings = $this->get_api_settings();
        if (!empty($api_settings)) {
            $settings['api'] = $api_settings;
        }

        return $settings;
    }

    /**
     * Export Australia Post shipping method data
     *
     * @return array
     */
    public function export_data() {
        if (!$this->is_plugin_active()) {
            return array();
        }

        $this->log_debug('Exporting Australia Post shipping method data');

        $export_data = array(
            'plugin_info' => array(
                'name' => $this->get_name(),
                'description' => $this->get_description(),
                'plugin_slug' => $this->plugin_slug,
                'method_id' => $this->method_id,
            ),
            'settings' => $this->get_method_settings(),
            'export_timestamp' => current_time('timestamp'),
            'export_version' => '1.0.0',
        );

        $this->log_debug('Australia Post export completed', array('data_size' => count($export_data)));

        return $export_data;
    }

    /**
     * Get shipping zone settings
     *
     * @return array
     */
    private function get_shipping_zone_settings() {
        $zones = array();

        // Get all shipping zones
        $shipping_zones = \WC_Shipping_Zones::get_zones();
        
        foreach ($shipping_zones as $zone_id => $zone_data) {
            $zone = \WC_Shipping_Zones::get_zone($zone_id);
            $shipping_methods = $zone->get_shipping_methods();
            
            foreach ($shipping_methods as $method) {
                if ($method->id === 'australia_post') {
                    $zones[$zone_id] = array(
                        'zone_name' => $zone_data['zone_name'],
                        'method_settings' => $this->sanitize_shipping_data($method->settings),
                    );
                }
            }
        }

        return $zones;
    }

    /**
     * Get API settings
     *
     * @return array
     */
    private function get_api_settings() {
        $api_settings = array();

        // Check for API key
        $api_key = $this->get_plugin_option('woocommerce_australia_post_api_key');
        if ($api_key) {
            $api_settings['has_api_key'] = true;
            $api_settings['api_key_length'] = strlen($api_key);
        } else {
            $api_settings['has_api_key'] = false;
        }

        // Check for other API-related settings
        $api_settings['debug_mode'] = $this->get_plugin_option('woocommerce_australia_post_debug_mode', 'no');
        $api_settings['test_mode'] = $this->get_plugin_option('woocommerce_australia_post_test_mode', 'no');

        return $api_settings;
    }
}
