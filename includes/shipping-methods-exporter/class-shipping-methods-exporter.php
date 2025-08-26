<?php
namespace WooCommerceSupportHelper\ShippingMethodsExporter;

/**
 * Main Shipping Methods Exporter module class
 *
 * @package WooCommerceSupportHelper\ShippingMethodsExporter
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main class for the Shipping Methods Exporter module
 */
class Shipping_Methods_Exporter {
    /**
     * @var array
     */
    private $shipping_exporters = array();

    /**
     * @var array
     */
    private $supported_plugins = array(
        'woocommerce-shipping-australia-post' => 'Australia Post',
        'woocommerce-shipping-fedex' => 'FedEx',
        'woocommerce-shipping-royalmail' => 'Royal Mail',
        'woocommerce-shipping-ups' => 'UPS',
        'woocommerce-shipping-usps' => 'USPS',
        'woocommerce-table-rate-shipping' => 'Table Rate Shipping',
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_shipping_exporters();
        $this->init_hooks();
    }

    /**
     * Initialize all shipping exporters
     */
    private function init_shipping_exporters() {
        // Load individual shipping method exporters
        $this->load_australia_post_exporter();
        $this->load_fedex_exporter();
        $this->load_royal_mail_exporter();
        $this->load_ups_exporter();
        $this->load_usps_exporter();
        $this->load_table_rate_shipping_exporter();
    }

    /**
     * Initialize WordPress and WooCommerce hooks
     */
    private function init_hooks() {
        // Hook into WooCommerce Blueprint exporters
        add_filter('wooblueprint_exporters', array($this, 'register_shipping_exporters'));
        
        // Hook into WooCommerce admin settings for shipping
        add_filter('woocommerce_admin_shared_settings', array($this, 'modify_shipping_settings'), 30);
        
        // Add shipping methods to Blueprint export
        add_action('wooblueprint_export_shipping_methods', array($this, 'export_shipping_methods'));
    }

    /**
     * Load Australia Post exporter
     */
    private function load_australia_post_exporter() {
        if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter')) {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\AustraliaPost\Australia_Post_Exporter();
            $this->shipping_exporters['australia_post'] = $exporter;
        }
    }

    /**
     * Load FedEx exporter
     */
    private function load_fedex_exporter() {
        if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\FedEx\FedEx_Exporter')) {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\FedEx\FedEx_Exporter();
            $this->shipping_exporters['fedex'] = $exporter;
        }
    }

    /**
     * Load Royal Mail exporter
     */
    private function load_royal_mail_exporter() {
        if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\RoyalMail\Royal_Mail_Exporter')) {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\RoyalMail\Royal_Mail_Exporter();
            $this->shipping_exporters['royal_mail'] = $exporter;
        }
    }

    /**
     * Load UPS exporter
     */
    private function load_ups_exporter() {
        if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\UPS\UPS_Exporter')) {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\UPS\UPS_Exporter();
            $this->shipping_exporters['ups'] = $exporter;
        }
    }

    /**
     * Load USPS exporter
     */
    private function load_usps_exporter() {
        if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\USPS\USPS_Exporter')) {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\USPS\USPS_Exporter();
            $this->shipping_exporters['usps'] = $exporter;
        }
    }

    /**
     * Load Table Rate Shipping exporter
     */
    private function load_table_rate_shipping_exporter() {
        if (class_exists('\WooCommerceSupportHelper\ShippingMethodsExporter\TableRateShipping\Table_Rate_Shipping_Exporter')) {
            $exporter = new \WooCommerceSupportHelper\ShippingMethodsExporter\TableRateShipping\Table_Rate_Shipping_Exporter();
            $this->shipping_exporters['table_rate_shipping'] = $exporter;
        }
    }

    /**
     * Register shipping exporters with Blueprint
     *
     * @param array $exporters
     * @return array
     */
    public function register_shipping_exporters($exporters) {
        foreach ($this->shipping_exporters as $exporter) {
            if (method_exists($exporter, 'register_with_blueprint')) {
                $exporters = $exporter->register_with_blueprint($exporters);
            }
        }
        return $exporters;
    }

    /**
     * Modify shipping settings in WooCommerce admin
     *
     * @param array $settings
     * @return array
     */
    public function modify_shipping_settings($settings) {
        // Add shipping methods to Blueprint settings if available
        if (isset($settings['blueprint_step_groups'])) {
            foreach ($settings['blueprint_step_groups'] as &$group) {
                if ($group['id'] === 'shipping') {
                    $group['items'] = $this->get_shipping_methods_for_export();
                    break;
                }
            }
        }
        return $settings;
    }

    /**
     * Export shipping methods data
     */
    public function export_shipping_methods() {
        $shipping_data = array();
        
        foreach ($this->shipping_exporters as $key => $exporter) {
            if (method_exists($exporter, 'export_data')) {
                $shipping_data[$key] = $exporter->export_data();
            }
        }
        
        return $shipping_data;
    }

    /**
     * Get shipping methods available for export
     *
     * @return array
     */
    private function get_shipping_methods_for_export() {
        $methods = array();
        
        foreach ($this->shipping_exporters as $key => $exporter) {
            if (method_exists($exporter, 'get_export_info')) {
                $info = $exporter->get_export_info();
                if ($info) {
                    $methods[] = $info;
                }
            }
        }
        
        return $methods;
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
     * Get a specific shipping exporter by name
     *
     * @param string $name
     * @return object|null
     */
    public function get_shipping_exporter($name) {
        return isset($this->shipping_exporters[$name]) ? $this->shipping_exporters[$name] : null;
    }

    /**
     * Check if a shipping method is supported
     *
     * @param string $method_name
     * @return bool
     */
    public function is_shipping_method_supported($method_name) {
        return isset($this->supported_plugins[$method_name]);
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
            'name' => 'Shipping Methods Exporter',
            'description' => 'Exports shipping method settings for various WooCommerce shipping plugins',
            'version' => '1.0.0',
            'supported_plugins' => $this->supported_plugins,
            'exporters' => array_map(function($exporter) {
                return array(
                    'name' => method_exists($exporter, 'get_name') ? $exporter->get_name() : 'Unknown',
                    'description' => method_exists($exporter, 'get_description') ? $exporter->get_description() : '',
                );
            }, $this->shipping_exporters),
        );
    }
}
