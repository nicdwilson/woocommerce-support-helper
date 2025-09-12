<?php
/**
 * Plugin Name: WooCommerce Support Helper
 * Plugin URI: https://happyplugins.com
 * Description: Extends WooCommerce Blueprint exporter with intelligent private plugin filtering. Only exports private plugins that are available via updaters (like WooCommerce.com extensions) to ensure successful blueprint imports.
 * Version: 0.2.0
 * Author: @nicw, WooCommerce Growth Team
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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'WC_SUPPORT_HELPER_VERSION', '1.0.0' );
define( 'WC_SUPPORT_HELPER_FILE', __FILE__ );
define( 'WC_SUPPORT_HELPER_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_SUPPORT_HELPER_URL', plugin_dir_url( __FILE__ ) );

// Load Composer autoloader.
require_once WC_SUPPORT_HELPER_PATH . 'vendor/autoload.php';

// Include the REST API
require_once WC_SUPPORT_HELPER_PATH . 'includes/class-support-helper-api.php';

/**
 * Main plugin class.
 *
 * Handles the initialization and management of the WooCommerce Support Helper plugin.
 */
class WC_Support_Helper {
	/**
	 * Module loader instance.
	 *
	 * @var Module_Loader
	 */
	private $module_loader;

	/**
	 * Order_Simulator The instance of Order_Generator
	 *
	 * @var    object
	 * @access private
	 * @since  1.0.0
	 */
	private static object $instance;

	/**
	 * Main Order_Generator Instance
	 *
	 * Ensures only one instance of Order_Generator is loaded or can be loaded.
	 *
	 * @return WC_Support_Helper instance
	 * @since  1.0.0
	 * @static
	 */
	public static function instance(): object {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

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
	public function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'check_woocommerce' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
	}

	/**
	 * Load plugin text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'woocommerce-support-helper',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	/**
	 * Initialize components
	 */
	public function init_components() {

		$this->module_loader = new Module_Loader();
	}

	/**
	 * Check if WooCommerce is active
	 */
	public function check_woocommerce() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				function () {
					?>
				<div class="error">
					<p>WooCommerce Support Helper requires WooCommerce to be installed and active.</p>
				</div>
					<?php
				}
			);
			return;
		}
	}

	/**
	 * Declare HPOS compatibility
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
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
			'name'    => 'WooCommerce Support Helper',
			'version' => WC_SUPPORT_HELPER_VERSION,
			'modules' => $this->module_loader ? $this->module_loader->get_module_info() : array(),
		);
	}
}

// Initialize the plugin.
add_action( 'init', array( '\WooCommerceSupportHelper\WC_Support_Helper', 'instance' ) );