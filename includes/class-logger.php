<?php
/**
 * Logger Class
 *
 * @package WooCommerceSupportHelper
 * @since 1.0.0
 */

namespace WooCommerceSupportHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Logger class for WooCommerce Support Helper
 */
class Logger {
	/**
	 * The log source identifier.
	 */
	const SOURCE = 'woocommerce-support-helper';

	/**
	 * Get the WooCommerce logger instance.
	 *
	 * @return \WC_Logger|object
	 */
	protected static function get_logger() {
		if ( function_exists( 'wc_get_logger' ) ) {
			return wc_get_logger();
		}
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function info( $message, $context = array() ) {
		self::get_logger()->info( $message, array_merge( $context, array( 'source' => self::SOURCE ) ) );
	}

	/**
	 * Log a debug message.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function debug( $message, $context = array() ) {
		self::get_logger()->debug( $message, array_merge( $context, array( 'source' => self::SOURCE ) ) );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function warning( $message, $context = array() ) {
		$logger = self::get_logger();

		// Check if the logger supports warning method.
		if ( method_exists( $logger, 'warning' ) ) {
			$logger->warning( $message, array_merge( $context, array( 'source' => self::SOURCE ) ) );
		} else {
			// Fallback to info if warning is not supported.
			$logger->info(
				$message,
				array_merge(
					$context,
					array(
						'source' => self::SOURCE,
						'level'  => 'warning',
					)
				)
			);
		}
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function error( $message, $context = array() ) {
		self::get_logger()->error( $message, array_merge( $context, array( 'source' => self::SOURCE ) ) );
	}

	/**
	 * Check if we're on a Blueprint-related page with admin referrer verification.
	 *
	 * @return bool
	 */
	private static function is_blueprint_page() {

		// Verify we're in the admin area.
		if ( ! is_admin() ) {
			return false;
		}

				// Check if we're on the Blueprint page with admin referrer verification.
		if ( isset( $_GET['page'] ) && 'wc-admin' === $_GET['page'] &&
			isset( $_GET['path'] ) ) {

			$path = wp_unslash( $_GET['path'] );
			if ( strpos( $path, 'blueprint' ) !== false ) {
				// Verify the request is coming from the admin area.
				$referer = wp_get_referer();
				if ( $referer && strpos( $referer, admin_url() ) === 0 ) {
					return true;
				}
			}
		}

		// Check if we're in a Blueprint AJAX request with admin referrer verification.
		if ( wp_doing_ajax() && isset( $_POST['action'] ) ) {
			$action = wp_unslash( $_POST['action'] );
			if ( strpos( $action, 'blueprint' ) !== false ) {
				// Verify the AJAX request is coming from the admin area.
				$referer = wp_get_referer();
				if ( $referer && strpos( $referer, admin_url() ) === 0 ) {
					return true;
				}
			}
		}

		// Check if we're in a Blueprint REST API request.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$request_uri = wp_unslash( $_SERVER['REQUEST_URI'] ?? '' );
			if ( strpos( $request_uri, 'wc-admin/blueprint' ) !== false ) {
				// REST API requests are handled by WordPress core security.
				return true;
			}
		}

		return false;
	}

	/**
	 * Log an info message only on Blueprint pages.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_info( $message, $context = array() ) {
		if ( self::is_blueprint_page() ) {
			self::info( $message, $context );
		}
	}

	/**
	 * Log a debug message only on Blueprint pages.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_debug( $message, $context = array() ) {
		if ( self::is_blueprint_page() ) {
			self::debug( $message, $context );
		}
	}

	/**
	 * Log a warning message only on Blueprint pages.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_warning( $message, $context = array() ) {
		if ( self::is_blueprint_page() ) {
			self::warning( $message, $context );
		}
	}

	/**
	 * Log an error message only on Blueprint pages.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_error( $message, $context = array() ) {
		if ( self::is_blueprint_page() ) {
			self::error( $message, $context );
		}
	}

	/**
	 * Log a message only on Blueprint pages with custom level.
	 *
	 * @param string $level   The log level to use.
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_log( $level, $message, $context = array() ) {
		if ( self::is_blueprint_page() ) {
			$logger = self::get_logger();
			if ( method_exists( $logger, $level ) ) {
				$logger->$level( $message, array_merge( $context, array( 'source' => self::SOURCE ) ) );
			} else {
				// Fallback to info if level is not supported.
				$logger->info(
					$message,
					array_merge(
						$context,
						array(
							'source' => self::SOURCE,
							'level'  => $level,
						)
					)
				);
			}
		}
	}
}
