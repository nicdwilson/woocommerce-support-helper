<?php
/**
 * Plugin Name: Happy Blueprint Exporter
 * Plugin URI: https://happyplugins.com
 * Description: Extends WooCommerce Blueprint exporter with intelligent private plugin filtering. Only exports private plugins that are available via updaters (like WooCommerce.com extensions) to ensure successful blueprint imports.
 * Version: 1.0.0
 * Author: Happy Plugins
 * Author URI: https://happyplugins.com
 * Text Domain: happy-blueprint-exporter
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 8.9.3
 *
 * @package HappyBlueprintExporter
 */

namespace HappyBlueprintExporter;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('HAPPY_BLUEPRINT_EXPORTER_VERSION', '1.0.0');
define('HAPPY_BLUEPRINT_EXPORTER_FILE', __FILE__);
define('HAPPY_BLUEPRINT_EXPORTER_PATH', plugin_dir_path(__FILE__));
define('HAPPY_BLUEPRINT_EXPORTER_URL', plugin_dir_url(__FILE__));

// Load Composer autoloader
require_once HAPPY_BLUEPRINT_EXPORTER_PATH . 'vendor/autoload.php';

/**
 * Main plugin class
 */
class Happy_Blueprint_Exporter {
    /**
     * @var Private_Plugin_Exporter
     */
    private $private_plugin_exporter;

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
        $this->private_plugin_exporter = new Private_Plugin_Exporter();
        $this->private_plugin_exporter->init();
    }

    /**
     * Check if WooCommerce is active
     */
    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', function() {
                ?>
                <div class="error">
                    <p><?php _e('Happy Blueprint Exporter requires WooCommerce to be installed and active.', 'happy-blueprint-exporter'); ?></p>
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
}

// Initialize the plugin
new Happy_Blueprint_Exporter(); 