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
     * Log a warning message.
     *
     * @param string $message
     * @param array $context
     */
    public static function warning($message, $context = array()) {
        $logger = self::get_logger();
        
        // Check if the logger supports warning method
        if (method_exists($logger, 'warning')) {
            $logger->warning($message, array_merge($context, array('source' => self::SOURCE)));
        } else {
            // Fallback to info if warning is not supported
            $logger->info($message, array_merge($context, array('source' => self::SOURCE, 'level' => 'warning')));
        }
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
    
    /**
     * Check if we're on a Blueprint-related page
     *
     * @return bool
     */
    private static function is_blueprint_page() {
        
        
        // Check if we're on the Blueprint page - simplified approach
        if (isset($_GET['page']) && $_GET['page'] === 'wc-admin' && 
            isset($_GET['path']) && strpos($_GET['path'], 'blueprint') !== false) {
            return true;
        }
        
        // Check if we're in a Blueprint AJAX request
        if (\wp_doing_ajax() && isset($_POST['action']) && strpos($_POST['action'], 'blueprint') !== false) {
            return true;
        }
        
        // Check if we're in a Blueprint REST API request
        if (defined('REST_REQUEST') && REST_REQUEST) {
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($request_uri, 'wc-admin/blueprint') !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Log an info message only on Blueprint pages.
     *
     * @param string $message
     * @param array $context
     */
    public static function blueprint_info($message, $context = array()) {
        if (self::is_blueprint_page()) {
            self::info($message, $context);
        }
    }
    
    /**
     * Log a debug message only on Blueprint pages.
     *
     * @param string $message
     * @param array $context
     */
    public static function blueprint_debug($message, $context = array()) {
        if (self::is_blueprint_page()) {
            self::debug($message, $context);
        }
    }
    
    /**
     * Log a warning message only on Blueprint pages.
     *
     * @param string $message
     * @param array $context
     */
    public static function blueprint_warning($message, $context = array()) {
        if (self::is_blueprint_page()) {
            self::warning($message, $context);
        }
    }
    
    /**
     * Log an error message only on Blueprint pages.
     *
     * @param string $message
     * @param array $context
     */
    public static function blueprint_error($message, $context = array()) {
        if (self::is_blueprint_page()) {
            self::error($message, $context);
        }
    }
    
    /**
     * Log a message only on Blueprint pages with custom level.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public static function blueprint_log($level, $message, $context = array()) {
        if (self::is_blueprint_page()) {
            $logger = self::get_logger();
            if (method_exists($logger, $level)) {
                $logger->$level($message, array_merge($context, array('source' => self::SOURCE)));
            } else {
                // Fallback to info if level is not supported
                $logger->info($message, array_merge($context, array('source' => self::SOURCE, 'level' => $level)));
            }
        }
    }
} 