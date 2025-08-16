<?php

namespace Rollbar\WordPress\API;

use Rollbar\Payload\Level as Level;
use Rollbar\Rollbar;
use Rollbar\WordPress\Lib\AbstractSingleton;
use Rollbar\WordPress\Plugin;
use Throwable;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Class Admin API
 *
 * @since 3.0.0
 */
class AdminAPI extends AbstractSingleton
{
    public const API_VERSION = '1';
    public const API_NAMESPACE = 'rollbar/v' . self::API_VERSION;

    /**
     * Constructor for the AdminAPI class.
     */
    public function __construct()
    {
        add_action('rest_api_init', $this->registerRoutes(...));
    }

    /**
     * Registers the REST API routes for the Rollbar WordPress plugin.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        register_rest_route(
            route_namespace: self::API_NAMESPACE,
            route: '/test-php-logging',
            args: [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => $this->handleTestPhpLogging(...),
                'permission_callback' => function (WP_REST_Request $request): bool {
                    /**
                     * Filter to allow or deny access to a Rollbar route in the WordPress REST API used in the WordPress
                     * Admin.
                     *
                     * @param bool $value The initial value. Defaults is `true` for admin users, `false` for non-admin
                     *                    users.
                     * @param string $route The route being accessed.
                     * @param WP_REST_Request $request The REST request object.
                     * @since 3.0.0
                     *
                     */
                    return apply_filters(
                        hook_name: 'rollbar_api_admin_permission',
                        value: current_user_can('manage_options'),
                        route: 'test-php-logging',
                        request: $request,
                    );
                },
            ],
        );
    }

    /**
     * Handles a test request for PHP logging functionality within the plugin.
     *
     * Endpoint: rollbar/v1/test-php-logging
     *
     * @param WP_REST_Request $request The REST request object containing the request data.
     * @return WP_REST_Response The REST response object containing the result of the logging test.
     */
    public function handleTestPhpLogging(WP_REST_Request $request): WP_REST_Response
    {
        $plugin = Plugin::getInstance();

        try {
            $plugin->initPhpLogging();
            $response = Rollbar::report(
                Level::INFO,
                'Test message from Rollbar WordPress plugin using PHP: ' .
                'integration with WordPress successful',
            );
        } catch (Throwable $exception) {
            return new WP_REST_Response(
                [
                    'message' => $exception->getMessage(),
                    'success' => false,
                ],
                500,
            );
        }

        $info = $response->getInfo();

        $response = [
            'code' => $response->getStatus(),
            'success' => true,
        ];
        if (is_array($info)) {
            $response = array_merge($response, $info);
        } else {
            $response['message'] = $info;
        }

        return new WP_REST_Response($response, 200);
    }
}
