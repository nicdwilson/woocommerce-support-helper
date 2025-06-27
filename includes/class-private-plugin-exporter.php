<?php
namespace HappyBlueprintExporter;

/**
 * Handles private plugin export settings
 *
 * @package HappyPlugins\BlueprintExporter
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class to handle private plugin export settings
 */
class Private_Plugin_Exporter extends Abstract_Exporter {
    /**
     * Initialize the exporter
     */
    public function init() {
        add_filter('wooblueprint_exporters', array($this, 'modify_plugin_exporter'));
        add_filter('woocommerce_admin_shared_settings', array($this, 'modify_plugin_list'), 20);
    }

    /**
     * Get the exporter name
     *
     * @return string
     */
    public function get_name() {
        return 'private_plugin_exporter';
    }

    /**
     * Get the exporter description
     *
     * @return string
     */
    public function get_description() {
        return __('Enables export of private plugin settings in WooCommerce Blueprint.', 'happy-blueprint-exporter');
    }

    /**
     * Get the data to export
     *
     * @return array
     */
    public function get_data() {
        return array(
            'private_plugins_enabled' => true,
        );
    }

    /**
     * Modify the plugin exporter to enable private plugin exports
     *
     * @param array $exporters
     * @return array
     */
    public function modify_plugin_exporter($exporters) {
        Logger::debug('modify_plugin_exporter called with ' . count($exporters) . ' exporters');
        
        foreach ($exporters as $exporter) {
            if ($exporter instanceof \Automattic\WooCommerce\Blueprint\Exporters\ExportInstallPluginSteps) {
                Logger::info('Found ExportInstallPluginSteps, enabling private plugins');
                $exporter->include_private_plugins(true);
                
                // Add a filter to only include plugins that are available via updaters
                $exporter->filter(array($this, 'filter_available_plugins'));
            }
        }
        return $exporters;
    }

    /**
     * Filter plugins to only include those available via updaters
     *
     * @param array $plugins List of plugins from wp_get_plugins()
     * @return array Filtered list of plugins
     */
    public function filter_available_plugins($plugins) {
        Logger::debug('filter_available_plugins called with ' . count($plugins) . ' plugins');
        
        $available_plugins = array();
        
        foreach ($plugins as $path => $plugin) {
            $slug = dirname($path);
            // single-file plugin.
            if ('.' === $slug) {
                $slug = pathinfo($path)['filename'];
            }
            
            // Check if this plugin is available via updaters
            if ($this->is_plugin_available_via_updater($slug, $plugin)) {
                $available_plugins[$path] = $plugin;
                Logger::debug("Plugin {$plugin['Name']} ({$slug}) is available via updater");
            } else {
                Logger::debug("Plugin {$plugin['Name']} ({$slug}) is NOT available via updater - excluding from export");
            }
        }
        
        Logger::info('Filtered plugins from ' . count($plugins) . ' to ' . count($available_plugins) . ' available plugins');
        return $available_plugins;
    }

    /**
     * Check if a plugin is available via updaters
     *
     * @param string $slug Plugin slug
     * @param array $plugin Plugin data
     * @return bool True if plugin is available via updaters
     */
    private function is_plugin_available_via_updater($slug, $plugin) {
        // Always include WordPress.org plugins
        $info = $this->get_plugin_info($slug);
        if (isset($info->download_link)) {
            return true;
        }
        
        // Check if it's a WooCommerce.com plugin with active subscription
        if ($this->is_woocommerce_com_plugin($plugin)) {
            return $this->has_woocommerce_com_subscription($plugin);
        }
        
        // Check if it's available via other update mechanisms
        if ($this->has_custom_update_source($plugin)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get plugin information from WordPress.org API
     *
     * @param string $slug Plugin slug
     * @return object|WP_Error Plugin information or error
     */
    private function get_plugin_info($slug) {
        if (!function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        
        return plugins_api('plugin_information', array(
            'slug' => $slug,
            'fields' => array(
                'sections' => false,
            ),
        ));
    }

    /**
     * Check if a plugin is a WooCommerce.com plugin
     *
     * @param array $plugin Plugin data
     * @return bool True if it's a WooCommerce.com plugin
     */
    private function is_woocommerce_com_plugin($plugin) {
        return isset($plugin['Woo']) && !empty($plugin['Woo']);
    }

    /**
     * Check if a WooCommerce.com plugin has an active subscription
     *
     * @param array $plugin Plugin data
     * @return bool True if it has an active subscription
     * 
     * Note: This method includes WooCommerce.com plugins in the following scenarios:
     * 1. When there's an active subscription for the plugin
     * 2. When there's no marketplace connection (local/dev environments) - FALLBACK
     * 3. When connected to marketplace but no active subscription - TEMPORARY FALLBACK
     * 
     * The fallback ensures plugins are included in local/dev environments where
     * WooCommerce Helper may not be connected. In production environments with
     * marketplace connections, only plugins with active subscriptions are included.
     * 
     * TODO: Remove temporary fallback when ready to require subscriptions.
     */
    private function has_woocommerce_com_subscription($plugin) {
        if (!class_exists('WC_Helper') || !class_exists('WC_Helper_Options')) {
            Logger::debug('WC_Helper or WC_Helper_Options not available - including WooCommerce.com plugin for local/dev environment');
            return true; // Fallback for local/dev environments
        }
        
        // Extract product ID from Woo header
        list($product_id, $file_id) = explode(':', $plugin['Woo']);
        if (empty($product_id)) {
            Logger::debug('No product ID found in Woo header - excluding plugin');
            return false;
        }
        
        // Check if there's an active subscription for this product
        $auth = \WC_Helper_Options::get('auth');
        $subscriptions = \WC_Helper::get_subscriptions();
        
        // If no auth or subscriptions, this is likely a local/dev environment
        if (empty($auth['site_id']) || empty($subscriptions)) {
            Logger::debug('No marketplace connection detected - including WooCommerce.com plugin for local/dev environment');
            return true; // Fallback for local/dev environments
        }
        
        // Check for an active subscription
        foreach ($subscriptions as $subscription) {
            if ($subscription['product_id'] != $product_id) {
                continue;
            }
            
            if (in_array(absint($auth['site_id']), $subscription['connections'])) {
                Logger::debug("Found active subscription for product {$product_id}");
                return true;
            }
        }
        
        // No active subscription found, but we're connected to marketplace
        // TEMPORARY: Include all WooCommerce.com plugins for now
        Logger::debug("No active subscription found for product {$product_id} - including plugin (temporary fallback)");
        return true; // Temporary fallback - remove when ready to require subscriptions
    }

    /**
     * Check if a plugin has a custom update source
     *
     * @param array $plugin Plugin data
     * @return bool True if it has a custom update source
     */
    private function has_custom_update_source($plugin) {
        // Check if the plugin has a custom update URL in its headers
        if (isset($plugin['UpdateURI']) && !empty($plugin['UpdateURI'])) {
            return true;
        }
        
        // Check if there are any update transients for this plugin
        $update_plugins = get_site_transient('update_plugins');
        if ($update_plugins && isset($update_plugins->response)) {
            foreach ($update_plugins->response as $plugin_file => $update_data) {
                if (strpos($plugin_file, $plugin['Name']) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Modify the plugin list to include only available private plugins
     *
     * @param array $settings
     * @return array
     */
    public function modify_plugin_list($settings) {
        Logger::debug('modify_plugin_list called');
        
        if (!is_admin()) {
            Logger::debug('Not admin context');
            return $settings;
        }
        
        // Only run on the Blueprint settings page where blueprint_step_groups is available
        if (!isset($settings['blueprint_step_groups'])) {
            Logger::debug('No blueprint_step_groups found in settings - not on Blueprint page');
            return $settings;
        }

        Logger::debug('Found blueprint_step_groups, modifying plugin list');

        // Find the plugins group and modify it
        foreach ($settings['blueprint_step_groups'] as &$group) {
            if ($group['id'] === 'plugins') {
                $original_count = count($group['items']);
                $group['items'] = $this->get_available_plugins_for_export();
                $new_count = count($group['items']);
                Logger::info("Modified plugin list from {$original_count} to {$new_count} plugins");
                break;
            }
        }

        return $settings;
    }

    /**
     * Get all plugins that are available via updaters for export
     *
     * @return array
     */
    private function get_available_plugins_for_export() {
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        $available_plugins = array();

        foreach ($all_plugins as $key => $plugin) {
            $slug = dirname($key);
            if ('.' === $slug) {
                $slug = pathinfo($key)['filename'];
            }
            
            // Only include plugins that are available via updaters
            if ($this->is_plugin_available_via_updater($slug, $plugin)) {
                $available_plugins[] = array(
                    'id'      => $key,
                    'label'   => $plugin['Name'],
                    'checked' => in_array($key, $active_plugins, true),
                );
            }
        }

        // Sort by active status (active plugins first)
        usort($available_plugins, function ($a, $b) {
            return $b['checked'] <=> $a['checked'];
        });

        return $available_plugins;
    }
} 