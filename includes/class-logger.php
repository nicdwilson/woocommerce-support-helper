<?php
namespace WooCommerceSupportHelper;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
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
        if (function_exists('wc_get_logger')) {
            return wc_get_logger();
        }
        // Fallback: create a dummy logger if WooCommerce is not loaded
        return new class {
            public function log($level, $message, $context = array()) {
                error_log("[{$level}] {$message}");
            }
            public function info($message, $context = array()) {
                $this->log('info', $message, $context);
            }
            public function debug($message, $context = array()) {
                $this->log('debug', $message, $context);
            }
            public function error($message, $context = array()) {
                $this->log('error', $message, $context);
            }
        };
    }

    /**
     * Log an info message.
     *
     * @param string $message
     * @param array $context
     */
    public static function info($message, $context = array()) {
        self::get_logger()->info($message, array_merge($context, array('source' => self::SOURCE)));
    }

    /**
     * Log a debug message.
     *
     * @param string $message
     * @param array $context
     */
    public static function debug($message, $context = array()) {
        self::get_logger()->debug($message, array_merge($context, array('source' => self::SOURCE)));
    }

    /**
     * Log an error message.
     *
     * @param string $message
     * @param array $context
     */
    public static function error($message, $context = array()) {
        self::get_logger()->error($message, array_merge($context, array('source' => self::SOURCE)));
    }
} 