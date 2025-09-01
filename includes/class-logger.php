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
	 * Log an info message for Blueprint operations.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_info( $message, $context = array() ) {
		self::info( $message, $context );
	}

	/**
	 * Log a debug message for Blueprint operations.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_debug( $message, $context = array() ) {
		self::debug( $message, $context );
	}

	/**
	 * Log a warning message for Blueprint operations.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_warning( $message, $context = array() ) {
		self::warning( $message, $context );
	}

	/**
	 * Log an error message for Blueprint operations.
	 *
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_error( $message, $context = array() ) {
		self::error( $message, $context );
	}

	/**
	 * Log a message for Blueprint operations with custom level.
	 *
	 * @param string $level   The log level to use.
	 * @param string $message The message to log.
	 * @param array  $context The context array.
	 */
	public static function blueprint_log( $level, $message, $context = array() ) {
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
