<?php

namespace Conflict;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * This is a tiny plugin that will create a conflict with any other plugin that also uses Monolog unscoped.
 *
 * This is used to validate the scoping of Rollbar plugin's Monolog dependency.
 */
final class Plugin
{
    /**
     * @var Logger $logger The logger instance.
     */
    private static Logger $logger;

    /**
     * Private constructor to prevent instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Initializes the plugin.
     *
     * @return void
     */
    public static function init(): void
    {
        self::$logger = new Logger('conflict');
        self::$logger->pushHandler(new StreamHandler(WP_CONTENT_DIR . '/logs/conflict.log', Logger::WARNING));

        add_action('init', self::onInit(...));
    }

    /**
     * Logs when the plugin is initialized.
     *
     * @return void
     */
    public static function onInit(): void
    {
        self::$logger->warning('Conflict plugin initialized.');
    }
}
