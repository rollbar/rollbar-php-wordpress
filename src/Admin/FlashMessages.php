<?php

namespace Rollbar\WordPress\Admin;

use Rollbar\WordPress\Html\Template;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * The class to manage flash messages for the user.
 *
 * @since 3.0.0
 */
final class FlashMessages
{
    private const KEY_PREFIX = 'rollbar_wp_flash_messages_';

    /**
     * Adds a flash message for the current user.
     *
     * @param string $message The message.
     * @param string $type The type. Should be one of: "error", "warning", "success", "info".
     * @return void
     */
    public static function addMessage(string $message, string $type = 'success'): void
    {
        if (is_admin() && session_id() && !wp_doing_cron()) {
            return;
        }
        $messages = get_option(self::getKey(), []);

        $messages[] = [
            "type" => $type,
            "message" => $message,
        ];

        update_option(self::getKey(), $messages);
    }

    /**
     * Renders and flushes the messages for the current user.
     *
     * @return string
     */
    public static function flushMessages(): string
    {
        if (is_admin() && session_id() && !wp_doing_cron()) {
            return '';
        }
        $messages = get_option(self::getKey(), []);
        update_option(self::getKey(), []);

        if (empty($messages)) {
            return '';
        }
        return Template::string(
            path: ROLLBAR_PLUGIN_DIR . '/templates/html/flashMessages.php',
            data: [
                'messages' => $messages,
            ],
        );
    }

    /**
     * Returns the option key to use to store messages.
     *
     * @return string
     */
    private static function getKey(): string
    {
        return self::KEY_PREFIX . get_current_user_id();
    }
}
