<?php
/**
 * Plugin Name: WooCommerce Support Helper
 * Plugin URI: https://happyplugins.com
 * Description: Extends WooCommerce Blueprint exporter with intelligent private plugin filtering. Only exports private plugins that are available via updaters (like WooCommerce.com extensions) to ensure successful blueprint imports.
 * Version: 1.0.0
 * Author: Happy Plugins
 * Author URI: https://happyplugins.com
 * Text Domain: woocommerce-support-helper
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 8.9.3
 *
 * @package WooCommerceSupportHelper
 */

namespace WooCommerceSupportHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('WC_SUPPORT_HELPER_VERSION', '1.0.0');
define('WC_SUPPORT_HELPER_FILE', __FILE__);
define('WC_SUPPORT_HELPER_PATH', plugin_dir_path(__FILE__));
define('WC_SUPPORT_HELPER_URL', plugin_dir_url(__FILE__));

// Load Composer autoloader
require_once WC_SUPPORT_HELPER_PATH . 'vendor/autoload.php';

/**
 * Main plugin class
 */
class WC_Support_Helper {
    /**
     * @var Module_Loader
     */
    private $module_loader;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_components();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'check_woocommerce'));
        add_action('woocommerce_init', array($this, 'declare_hpos_compatibility'));
    }

    /**
     * Initialize components
     */
    private function init_components() {
        $this->module_loader = new Module_Loader();
    }

    /**
     * Check if WooCommerce is active
     */
    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                ?>
                <div class="error">
                    <p><?php _e('WooCommerce Support Helper requires WooCommerce to be installed and active.', 'woocommerce-support-helper'); ?></p>
                </div>
                <?php
            });
            return;
        }
    }

    /**
     * Declare HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }

    /**
     * Get the module loader
     *
     * @return Module_Loader
     */
    public function get_module_loader() {
        return $this->module_loader;
    }

    /**
     * Get plugin information
     *
     * @return array
     */
    public function get_plugin_info() {
        return array(
            'name' => 'WooCommerce Support Helper',
            'version' => WC_SUPPORT_HELPER_VERSION,
            'modules' => $this->module_loader ? $this->module_loader->get_module_info() : array(),
        );
    }
}

// Initialize the plugin
new WC_Support_Helper(); 