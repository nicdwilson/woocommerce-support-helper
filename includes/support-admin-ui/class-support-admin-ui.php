<?php
/**
 * Support Admin UI Class
 *
 * @package WooCommerceSupportHelper\SupportAdminUI
 * @since 1.0.0
 */

namespace WooCommerceSupportHelper\SupportAdminUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class for the Support Admin UI module.
 */
class Support_Admin_UI {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress and WooCommerce hooks.
	 */
	private function init_hooks() {
		// Enqueue admin scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Initialize REST API for React integration
		new \WooCommerceSupportHelper\WC_Support_Helper_API();
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function enqueue_admin_assets() {
		// Only load on WooCommerce admin pages
		if ( ! $this->is_woocommerce_admin_page() ) {
			return;
		}

		// Enqueue React components for activity panel integration
		wp_enqueue_script(
			'wc-support-helper-react',
			WC_SUPPORT_HELPER_URL . 'includes/support-admin-ui/assets/js/dist/register-panel.min.js',
			array( 'wp-element', 'wp-components', 'wp-hooks', 'wp-i18n', 'wc-components', 'react', 'react-dom' ),
			WC_SUPPORT_HELPER_VERSION,
			true
		);

		wp_enqueue_style(
			'wc-support-helper-admin',
			WC_SUPPORT_HELPER_URL . 'includes/support-admin-ui/assets/css/admin.css',
			array(),
			WC_SUPPORT_HELPER_VERSION
		);

		// Enqueue React component styles
		wp_enqueue_style(
			'wc-support-helper-react',
			WC_SUPPORT_HELPER_URL . 'includes/support-admin-ui/assets/css/react-components.css',
			array(),
			WC_SUPPORT_HELPER_VERSION
		);

		// Localize script with API settings for React components
		wp_localize_script(
			'wc-support-helper-react',
			'wcSupportHelper',
			array(
				'apiUrl'  => rest_url( 'woocommerce-support-helper/v1/' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'strings' => array(
					'generating' => __( 'Generating export...', 'woocommerce-support-helper' ),
					'error'      => __( 'Error generating export', 'woocommerce-support-helper' ),
					'success'    => __( 'Export generated successfully', 'woocommerce-support-helper' ),
				),
			)
		);
	}

	/**
	 * Check if current page is a WooCommerce admin page.
	 *
	 * @return bool True if it's a WooCommerce admin page.
	 */
	private function is_woocommerce_admin_page() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return false;
		}

		// Check if it's a WooCommerce screen
		if ( in_array( $screen->id, wc_get_screen_ids() ) ) {
			return true;
		}

		// Check if we're on a WooCommerce admin page
		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'wc-' ) === 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the instance of the Support Admin UI class.
	 *
	 * @return Support_Admin_UI|null The instance of the Support Admin UI class, or null if not available.
	 */
	public static function get_instance() {
		$module_loader    = \WooCommerceSupportHelper\WC_Support_Helper::instance()->get_module_loader();
		$support_admin_ui = $module_loader->get_module( 'support_admin_ui' );

		if ( $support_admin_ui instanceof self ) {
			return $support_admin_ui;
		}

		return null;
	}

	/**
	 * Get module information.
	 *
	 * @return array Module information.
	 */
	public function get_module_info() {
		return array(
			'name'        => 'Support Admin UI',
			'description' => 'Provides React-based admin UI for WooCommerce Support Helper',
			'version'     => '1.0.0',
		);
	}
}
