<?php
namespace WooCommerceSupportHelper\BlueprintExporter;

/**
 * Main Blueprint Exporter module class
 *
 * @package WooCommerceSupportHelper
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Main class for the Blueprint Exporter module
 */
class Blueprint_Exporter {
    /**
     * @var Private_Plugin_Exporter
     */
    private $private_plugin_exporter;

    /**
     * @var array
     */
    private $exporters = array();

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_exporters();
    }

    /**
     * Initialize all exporters
     */
    private function init_exporters() {
        // Initialize the private plugin exporter
        $this->private_plugin_exporter = new Private_Plugin_Exporter();
        $this->exporters[] = $this->private_plugin_exporter;

        // Hook into WordPress and WooCommerce
        $this->init_hooks();
    }

    /**
     * Initialize WordPress and WooCommerce hooks
     */
    private function init_hooks() {
        // Hook into WooCommerce Blueprint exporters
        add_filter('wooblueprint_exporters', array($this, 'modify_plugin_exporter'));
        
        // Hook into WooCommerce admin settings
        add_filter('woocommerce_admin_shared_settings', array($this, 'modify_plugin_list'), 20);
        
        // Hook into REST API to replace the default Blueprint export endpoint with our custom one
        add_action('rest_api_init', array($this, 'replace_blueprint_rest_api'), 5);
    }

    /**
     * Get all registered exporters
     *
     * @return array
     */
    public function get_exporters() {
        return $this->exporters;
    }

    /**
     * Get a specific exporter by name
     *
     * @param string $name
     * @return Abstract_Exporter|null
     */
    public function get_exporter($name) {
        foreach ($this->exporters as $exporter) {
            if ($exporter->get_name() === $name) {
                return $exporter;
            }
        }
        return null;
    }

    /**
     * Register a new exporter
     *
     * @param Abstract_Exporter $exporter
     */
    public function register_exporter(Abstract_Exporter $exporter) {
        $this->exporters[] = $exporter;
    }

    /**
     * Modify the plugin exporter to enable private plugin exports
     *
     * @param array $exporters
     * @return array
     */
    public function modify_plugin_exporter($exporters) {
        if ($this->private_plugin_exporter) {
            return $this->private_plugin_exporter->modify_plugin_exporter($exporters);
        }
        return $exporters;
    }

    /**
     * Modify the plugin list to include only available private plugins
     *
     * @param array $settings
     * @return array
     */
    public function modify_plugin_list($settings) {
        if ($this->private_plugin_exporter) {
            return $this->private_plugin_exporter->modify_plugin_list($settings);
        }
        return $settings;
    }

    /**
     * Replace the default WooCommerce Blueprint REST API with our custom version
     * that uses our custom ExportSchema
     */
    public function replace_blueprint_rest_api() {
        // Remove the default WooCommerce Blueprint REST API routes
        // We need to do this by deregistering the existing routes and registering our own
        
        // Get the REST server instance
        $rest_server = rest_get_server();
        
        // Remove existing routes for the blueprint export endpoint
        $routes = $rest_server->get_routes();
        if (isset($routes['/wc-admin/v1/blueprint/export'])) {
            // Unregister the existing route
            unset($routes['/wc-admin/v1/blueprint/export']);
        }
        
        // Register our custom route
        register_rest_route(
            'wc-admin',
            '/blueprint/export',
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'handle_export_request'),
                    'permission_callback' => array($this, 'check_export_permission'),
                    'args'                => array(
                        'steps' => array(
                            'description' => __( 'A list of plugins to install', 'woocommerce' ),
                            'type'        => 'object',
                            'properties'  => array(
                                'settings' => array(
                                    'type'  => 'array',
                                    'items' => array(
                                        'type' => 'string',
                                    ),
                                ),
                                'plugins'  => array(
                                    'type'  => 'array',
                                    'items' => array(
                                        'type' => 'string',
                                    ),
                                ),
                                'themes'   => array(
                                    'type'  => 'array',
                                    'items' => array(
                                        'type' => 'string',
                                    ),
                                ),
                            ),
                            'default'     => array(),
                            'required'    => true,
                        ),
                    ),
                ),
            )
        );
    }
    
    /**
     * Handle the export request using our custom ExportSchema
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_HTTP_Response The response object.
     */
    public function handle_export_request($request) {
        \WooCommerceSupportHelper\Logger::info('🚀 Custom Blueprint export handler called', array(
            'request_params' => $request->get_params(),
            'request_params_type' => gettype($request->get_params()),
            'request_params_keys' => array_keys($request->get_params()),
            'steps_param_raw' => $request->get_param('steps'),
            'steps_param_type' => gettype($request->get_param('steps')),
            'user_id' => get_current_user_id(),
            'user_can_manage_woocommerce' => current_user_can('manage_woocommerce'),
        ));
        
        $payload = $request->get_param( 'steps' );
        $steps   = $this->steps_payload_to_blueprint_steps( $payload );

        \WooCommerceSupportHelper\Logger::info('🚀 Steps processing', array(
            'payload' => $payload,
            'payload_type' => gettype($payload),
            'payload_keys' => is_array($payload) ? array_keys($payload) : 'not_array',
            'steps' => $steps,
            'steps_type' => gettype($steps),
            'steps_count' => is_array($steps) ? count($steps) : 'not_array',
            'steps_sample' => is_array($steps) ? array_slice($steps, 0, 5) : 'not_array',
        ));

        // Use our custom ExportSchema instead of the default one
        if (class_exists('\WooCommerceSupportHelper\BlueprintExporter\Custom_Export_Schema')) {
            \WooCommerceSupportHelper\Logger::info('🚀 Using Custom_Export_Schema');
            $exporter = new Custom_Export_Schema();
        } else {
            \WooCommerceSupportHelper\Logger::warning('🚀 Custom_Export_Schema not available, falling back to WooCommerce ExportSchema');
            $exporter = new \Automattic\WooCommerce\Blueprint\ExportSchema();
        }

        if ( isset( $payload['plugins'] ) ) {
            $exporter->on_before_export(
                'installPlugin',
                function ( $exporter ) use ( $payload ) {
                    if (method_exists($exporter, 'filter')) {
                        $exporter->filter(
                            function ( array $plugins ) use ( $payload ) {
                                return array_intersect_key( $plugins, array_flip( $payload['plugins'] ) );
                            }
                        );
                    }
                }
            );
        }

        if ( isset( $payload['themes'] ) ) {
            $exporter->on_before_export(
                'installTheme',
                function ( $exporter ) use ( $payload ) {
                    if (method_exists($exporter, 'filter')) {
                        $exporter->filter(
                            function ( array $plugins ) use ( $payload ) {
                                return array_intersect_key( $plugins, array_flip( $payload['themes'] ) );
                            }
                        );
                    }
                }
            );
        }

        \WooCommerceSupportHelper\Logger::info('🚀 Starting export with exporter', array(
            'exporter_class' => get_class($exporter),
            'steps' => $steps,
        ));
        
        $data = $exporter->export( $steps );

        \WooCommerceSupportHelper\Logger::info('🚀 Export completed', array(
            'data_keys' => array_keys($data),
            'steps_count' => isset($data['steps']) ? count($data['steps']) : 0,
            'exporter_class_used' => get_class($exporter),
        ));
        
        // Debug: Log the structure of steps data
        if (isset($data['steps']) && is_array($data['steps'])) {
            \WooCommerceSupportHelper\Logger::info('🔍 Steps data structure', array(
                'steps_count' => count($data['steps']),
                'first_step_type' => isset($data['steps'][0]) ? gettype($data['steps'][0]) : 'none',
                'first_step_class' => isset($data['steps'][0]) && is_object($data['steps'][0]) ? get_class($data['steps'][0]) : 'not_object',
                'steps_sample' => array_slice($data['steps'], 0, 3), // First 3 steps for debugging
            ));
        }

        if ( is_wp_error( $data ) ) {
            return new \WP_REST_Response( $data, 400 );
        }

        // Debug: Log what we're about to return
        \WooCommerceSupportHelper\Logger::info('🔍 Final response data', array(
            'data_type' => gettype($data),
            'data_keys' => is_array($data) ? array_keys($data) : 'not_array',
            'data_json' => json_encode($data),
            'data_serialized' => serialize($data),
        ));
        
        // Try to return the data in the same format as WooCommerce's original handler
        if (is_wp_error($data)) {
            return new \WP_REST_Response($data, 400);
        }
        
        // Return as JSON response similar to WooCommerce's original format
        return new \WP_REST_Response(
            array(
                'data' => $data,
                'type' => 'json',
            )
        );
    }
    
    /**
     * Convert step list from the frontend to the backend format.
     * Copied from WooCommerce's RestApi class
     *
     * @param array $steps steps payload from the frontend.
     * @return array
     */
    private function steps_payload_to_blueprint_steps( $steps ) {
        \WooCommerceSupportHelper\Logger::info('🔍 Converting steps payload', array(
            'input_steps' => $steps,
            'input_type' => gettype($steps),
            'input_keys' => is_array($steps) ? array_keys($steps) : 'not_array',
        ));
        
        $blueprint_steps = array();

        if ( isset( $steps['settings'] ) && count( $steps['settings'] ) > 0 ) {
            \WooCommerceSupportHelper\Logger::info('🔍 Found settings in payload', array(
                'settings_count' => count($steps['settings']),
                'settings_sample' => array_slice($steps['settings'], 0, 5),
            ));
            $blueprint_steps = array_merge( $blueprint_steps, $steps['settings'] );
        }

        if ( isset( $steps['plugins'] ) && count( $steps['plugins'] ) > 0 ) {
            \WooCommerceSupportHelper\Logger::info('🔍 Found plugins in payload', array(
                'plugins_count' => count($steps['plugins']),
                'plugins_sample' => array_slice($steps['plugins'], 0, 5),
            ));
            $blueprint_steps[] = 'installPlugin';
        }

        if ( isset( $steps['themes'] ) && count( $steps['themes'] ) > 0 ) {
            \WooCommerceSupportHelper\Logger::info('🔍 Found themes in payload', array(
                'themes_count' => count($steps['themes']),
                'themes_sample' => array_slice($steps['themes'], 0, 5),
            ));
            $blueprint_steps[] = 'installTheme';
        }

        \WooCommerceSupportHelper\Logger::info('🔍 Steps conversion completed', array(
            'output_steps' => $blueprint_steps,
            'output_count' => count($blueprint_steps),
        ));

        return $blueprint_steps;
    }
    
    /**
     * Check export permission (copied from WooCommerce's RestApi class)
     *
     * @return bool|\WP_Error
     */
    public function check_export_permission() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return new \WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot export WooCommerce Blueprints.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
        }
        return true;
    }

    /**
     * Get module information
     *
     * @return array
     */
    public function get_module_info() {
        return array(
            'name' => 'Blueprint Exporter',
            'description' => 'Extends WooCommerce Blueprint exporter with intelligent private plugin filtering and custom ExportSchema',
            'version' => '1.0.0',
            'exporters' => array_map(function($exporter) {
                return array(
                    'name' => $exporter->get_name(),
                    'description' => $exporter->get_description(),
                );
            }, $this->exporters),
        );
    }
}
