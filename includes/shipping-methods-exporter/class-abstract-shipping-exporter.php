<?php
namespace WooCommerceSupportHelper\ShippingMethodsExporter;

/**
 * Abstract base class for shipping method exporters
 *
 * @package WooCommerceSupportHelper\ShippingMethodsExporter
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Abstract base class for all shipping method exporters
 */
abstract class Abstract_Shipping_Exporter {
    /**
     * @var string
     */
    protected $plugin_slug;

    /**
     * @var string
     */
    protected $plugin_name;

    /**
     * @var string
     */
    protected $method_id;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the exporter
     */
    abstract protected function init();

    /**
     * Get the exporter name
     *
     * @return string
     */
    abstract public function get_name();

    /**
     * Get the exporter description
     *
     * @return string
     */
    abstract public function get_description();

    /**
     * Check if the shipping plugin is active
     *
     * @return bool
     */
    abstract public function is_plugin_active();

    /**
     * Get shipping method settings
     *
     * @return array
     */
    abstract public function get_method_settings();

    /**
     * Export shipping method data
     *
     * @return array
     */
    abstract public function export_data();

    /**
     * Register with Blueprint system
     *
     * @param array $exporters
     * @return array
     */
    public function register_with_blueprint($exporters) {
        if (!$this->is_plugin_active()) {
            return $exporters;
        }

        // Add this exporter to the Blueprint exporters list
        $exporters[] = $this;
        return $exporters;
    }

    /**
     * Get export information for Blueprint
     *
     * @return array|null
     */
    public function get_export_info() {
        if (!$this->is_plugin_active()) {
            return null;
        }

        return array(
            'id' => $this->method_id,
            'label' => $this->get_name(),
            'description' => $this->get_description(),
            'checked' => true, // Default to checked
            'type' => 'shipping_method',
            'plugin_slug' => $this->plugin_slug,
        );
    }

    /**
     * Get plugin slug
     *
     * @return string
     */
    public function get_plugin_slug() {
        return $this->plugin_slug;
    }

    /**
     * Get plugin name
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Get method ID
     *
     * @return string
     */
    public function get_method_id() {
        return $this->method_id;
    }

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    protected function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Check if a specific plugin is active
     *
     * @param string $plugin_file
     * @return bool
     */
    protected function check_plugin_active($plugin_file = '') {
        if (empty($plugin_file)) {
            $plugin_file = $this->plugin_slug . '/' . $this->plugin_slug . '.php';
        }
        
        return is_plugin_active($plugin_file);
    }

    /**
     * Get plugin option value
     *
     * @param string $option_name
     * @param mixed $default
     * @return mixed
     */
    protected function get_plugin_option($option_name, $default = null) {
        return get_option($option_name, $default);
    }

    /**
     * Get plugin option value with prefix
     *
     * @param string $option_name
     * @param mixed $default
     * @return mixed
     */
    protected function get_plugin_option_with_prefix($option_name, $default = null) {
        $full_option_name = $this->plugin_slug . '_' . $option_name;
        return $this->get_plugin_option($full_option_name, $default);
    }

    /**
     * Sanitize shipping method data
     *
     * @param array $data
     * @return array
     */
    protected function sanitize_shipping_data($data) {
        if (!is_array($data)) {
            return array();
        }

        // Remove sensitive information
        $sensitive_keys = array('api_key', 'password', 'secret', 'token', 'private_key');
        
        foreach ($sensitive_keys as $key) {
            if (isset($data[$key])) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Log debug information
     *
     * @param string $message
     * @param array $context
     */
    protected function log_debug($message, $context = array()) {
        if (class_exists('\WooCommerceSupportHelper\Logger')) {
            \WooCommerceSupportHelper\Logger::debug($message, $context);
        }
    }

    /**
     * Log error information
     *
     * @param string $message
     * @param array $context
     */
    protected function log_error($message, $context = array()) {
        if (class_exists('\WooCommerceSupportHelper\Logger')) {
            \WooCommerceSupportHelper\Logger::error($message, $context);
        }
    }
}
