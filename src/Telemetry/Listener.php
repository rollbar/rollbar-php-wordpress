<?php

namespace Rollbar\WordPress\Telemetry;

use InvalidArgumentException;
use Rollbar\Rollbar;
use Rollbar\Telemetry\EventLevel;
use Rollbar\WordPress\Lib\AbstractSingleton;
use Rollbar\WordPress\Plugin;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Telemetry Listener Class
 *
 * @since 3.0.0
 */
class Listener extends AbstractSingleton
{
    /**
     * The default list of actions to instrument with Telemetry.
     */
    public const DEFAULT_ACTIONS = [
        // Lifecycle actions
        'muplugins_loaded' => 0,
        'plugins_loaded' => 0,
        'setup_theme' => 0,
        'after_setup_theme' => 0,
        'init' => 0,
        'wp_loaded' => 0,
        'pre_get_posts' => 1,
        'admin_init' => 0,
        'send_headers' => 1,
        'wp_head' => 0,
        'wp_footer' => 0,
        'shutdown' => 0,
    ];

    /**
     * All available actions in WordPress.
     *
     * This does not include filters or any dynamically named actions. This list is an associative array where the keys
     * are action names and the values are the number of arguments accepted by the action.
     *
     * @link https://developer.wordpress.org/apis/hooks/action-reference/
     */
    public const ALL_ACTIONS = [
        // Lifecycle actions
        'muplugins_loaded' => 0,
        'registered_taxonomy' => 3,
        'registered_post_type' => 2,
        'plugins_loaded' => 0,
        'sanitize_comment_cookies' => 0,
        'setup_theme' => 0,
        'load_textdomain' => 2,
        'after_setup_theme' => 0,
        'auth_cookie_malformed' => 2,
        'auth_cookie_valid' => 2,
        'set_current_user' => 0,
        'init' => 0,
        'widgets_init' => 0,
        'register_sidebar' => 1,
        'wp_register_sidebar_widget' => 1,
        'wp_default_scripts' => 1,
        'wp_default_styles' => 1,
        'admin_bar_init' => 0,
        'add_admin_bar_menus' => 1,
        'wp_loaded' => 0,
        'parse_request' => 1,
        'send_headers' => 1,
        'parse_query' => 1,
        'pre_get_posts' => 1,
        'posts_selection' => 1,
        'wp' => 1,
        'template_redirect' => 0,
        'get_header' => 2,
        'wp_enqueue_scripts' => 0,
        'wp_head' => 0,
        'wp_print_styles' => 0,
        'wp_print_scripts' => 0,
        'get_search_form' => 2,
        'loop_start' => 1,
        'the_post' => 2,
        'loop_end' => 1,
        'get_sidebar' => 2,
        'dynamic_sidebar' => 1,
        'pre_get_comments' => 1,
        'wp_meta' => 0,
        'get_footer' => 2,
        'wp_footer' => 0,
        'wp_print_footer_scripts' => 0,
        'admin_bar_menu' => 1,
        'wp_before_admin_bar_render' => 0,
        'wp_after_admin_bar_render' => 0,
        'shutdown' => 0,
        // Admin actions
        'auth_redirect' => 1,
        'admin_menu' => 1,
        'user_admin_menu' => 1,
        'network_admin_menu' => 1,
        'admin_init' => 0,
        'current_screen' => 1,
        'admin_xml_ns' => 0,
        'admin_enqueue_scripts' => 1,
        'admin_print_styles' => 0,
        'admin_print_scripts' => 0,
        'admin_head' => 0,
        'in_admin_header' => 0,
        'admin_notices' => 0,
        'all_admin_notices' => 0,
        'restrict_manage_posts' => 2,
        'pre_user_query' => 1,
        'in_admin_footer' => 0,
        'admin_footer' => 1,
        'admin_print_footer_scripts' => 0,
        'wp_dashboard_setup' => 0,
        // Post, Taxonomy, and Attachment actions
        'post_submitbox_misc_actions' => 1,
        'add_attachment' => 1,
        'clean_post_cache' => 2,
        'delete_attachment' => 2,
        'wp_trash_post' => 2,
        'trashed_post' => 2,
        'untrash_post' => 2,
        'untrashed_post' => 2,
        'before_delete_post' => 2,
        'delete_post' => 2,
        'deleted_post' => 2,
        'edit_attachment' => 1,
        'edit_post' => 2,
        'pre_post_update' => 2,
        'post_updated' => 3,
        'transition_post_status' => 3,
        'publish_phone' => 1,
        'save_post' => 3,
        'updated_postmeta' => 4,
        'wp_insert_post' => 3,
        'xmlrpc_publish_post' => 1,
        // Taxonomy and Term actions
        'create_term' => 4,
        'created_term' => 4,
        'add_term_relationship' => 3,
        'added_term_relationship' => 3,
        'set_object_terms' => 6,
        'edit_terms' => 3,
        'edited_terms' => 3,
        'edit_term_taxonomy' => 3,
        'edited_term_taxonomy' => 3,
        'edit_term_taxonomies' => 1,
        'edited_term_taxonomies' => 1,
        'pre_delete_term' => 2,
        'delete_term_taxonomy' => 1,
        'deleted_term_taxonomy' => 1,
        'delete_term' => 5,
        'delete_term_relationships' => 3,
        'deleted_term_relationships' => 3,
        'clean_object_term_cache' => 2,
        'clean_term_cache' => 3,
        'split_shared_term' => 4,
        // Comment, Ping, and Trackback actions
        'comment_closed' => 1,
        'comment_id_not_found' => 1,
        'comment_flood_trigger' => 2,
        'comment_on_draft' => 1,
        'comment_post' => 3,
        'edit_comment' => 2,
        'delete_comment' => 2,
        'deleted_comment' => 2,
        'trash_comment' => 2,
        'trashed_comment' => 2,
        'untrash_comment' => 2,
        'untrashed_comment' => 2,
        'spam_comment' => 2,
        'spammed_comment' => 2,
        'unspam_comment' => 2,
        'unspammed_comment' => 2,
        'pingback_post' => 1,
        'pre_ping' => 3,
        'trackback_post' => 1,
        'wp_check_comment_disallowed_list' => 6,
        'wp_insert_comment' => 2,
        'wp_set_comment_status' => 2,
        // RSS, Atom, and RDF actions
        'add_link' => 1,
        'delete_link' => 1,
        'edit_link' => 1,
        'atom_entry' => 0,
        'atom_head' => 0,
        'atom_ns' => 0,
        'commentrss2_item' => 2,
        'rdf_header' => 0,
        'rdf_item' => 0,
        'rdf_ns' => 0,
        'rss_head' => 0,
        'rss_item' => 0,
        'rss2_head' => 0,
        'rss2_item' => 0,
        'rss2_ns' => 0,
        // Template actions
        'comment_form' => 1,
        'comment_form_after' => 0,
        'do_robots' => 0,
        'do_robotstxt' => 0,
        'switch_theme' => 3,
        'after_switch_theme' => 2,
        // Management actions
        'activity_box_end' => 0,
        'add_option' => 2,
        'added_option' => 2,
        'dbx_post_sidebar' => 1,
        'delete_option' => 1,
        'deleted_option' => 1,
        'delete_user' => 3,
        'edit_form_top' => 1,
        'edit_form_after_title' => 1,
        'edit_form_after_editor' => 1,
        'edit_form_advanced' => 1,
        'edit_page_form' => 1,
        'edit_user_profile' => 1,
        'login_form' => 0,
        'login_head' => 0,
        'lost_password' => 1,
        'lostpassword_form' => 0,
        'lostpassword_post' => 2,
        'manage_link_custom_column' => 2,
        'manage_posts_custom_column' => 2,
        'manage_posts_columns' => 2,
        'manage_pages_custom_column' => 2,
        'manage_pages_columns' => 1,
        'manage_media_custom_column' => 2,
        'manage_media_columns' => 2,
        // Make sure not to set 'password_reset' higher than 1. The password is the second argument. We don't want to
        // log it in the Telemetry data.
        'password_reset' => 1,
        'personal_options_update' => 1,
        'profile_personal_options' => 1,
        'profile_update' => 3,
        'quick_edit_custom_box' => 3,
        'register_form' => 0,
        'register_post' => 3,
        'retrieve_password' => 1,
        'show_user_profile' => 1,
        'sidebar_admin_page' => 0,
        'sidebar_admin_setup' => 0,
        'update_option' => 3,
        'updated_option' => 3,
        'user_new_form' => 1,
        'user_profile_update_errors' => 3,
        'wpmu_new_user' => 1,
        'user_register' => 2,
        'welcome_panel' => 0,
        // Make sure not to set 'wp_authenticate' higher than 1. The password is the second argument. We don't want to
        // log it in the Telemetry data.
        'wp_authenticate' => 1,
        'wp_login' => 2,
        'wp_logout' => 1,
        // Advanced actions
        'activated_plugin' => 2,
        'add_meta_boxes' => 2,
        'network_admin_notices' => 0,
        'user_admin_notices' => 0,
        'blog_privacy_selector' => 0,
        'check_admin_referer' => 2,
        'check_ajax_referer' => 2,
        'customize_controls_enqueue_scripts' => 0,
        'customize_register' => 1,
        'customize_preview_init' => 0,
        'deactivated_plugin' => 2,
        'generate_rewrite_rules' => 1,
        'upgrader_process_complete' => 2,
        // Login actions
        'login_init' => 0,
        'login_enqueue_scripts' => 0,
        'login_header' => 0,
        'admin_email_confirm' => 1,
        'admin_email_confirm_form' => 0,
        'validate_password_reset' => 2,
        'resetpass_form' => 1,
        'user_request_action_confirmed' => 1,
        'login_footer' => 0,
    ];

    protected function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    protected function postInit(): void
    {
        $enable = Plugin::getInstance()->settingsInstance()->get('enable_telemetry_listener');
        if (!$enable) {
            return;
        }
        $actions = array_flip(Plugin::getInstance()->settingsInstance()->get('telemetry_hooks'));
        if (empty($actions)) {
            $actions = self::DEFAULT_ACTIONS;
        }

        $actions = array_intersect_key(self::ALL_ACTIONS, $actions);
        $customHandlers = $this->getCustomHandlers();

        /**
         * Filter the list of actions to instrument with Telemetry.
         *
         * @param array<string, int> $actions An associative array where the keys are action names and the values are
         *                                    the number of arguments accepted by the action.
         * @since 3.0.0
         */
        $actions = apply_filters('rollbar_telemetry_actions', $actions);

        foreach ($actions as $action => $acceptedArgs) {
            $handler = $customHandlers[$action] ?? self::concatExtraArgs(...);
            $this->instrumentAction(
                action: $action,
                acceptedArgs: $acceptedArgs,
                argsHandler: $handler,
            );
        }
    }

    /**
     * Returns a list of custom action event handlers for Telemetry.
     *
     * @return array<string, callable(string, mixed...):string>
     */
    protected function getCustomHandlers(): array
    {
        /**
         * Filter the list of custom action event handlers for Telemetry.
         *
         * @param array<string, callable(string, mixed...):string> $handlers An associative array where the keys are
         * action names and the values are the corresponding event handler functions.
         * @since 3.0.0
         */
        return apply_filters('rollbar_telemetry_custom_handlers', [
            'after_setup_theme' => self::handlerAfterSetupTheme(...),
            'plugins_loaded' => self::handlerPluginsLoaded(...),
        ]);
    }

    /**
     * Captures a Telemetry event for a WordPress action.
     *
     * @param string $action The action to log.
     * @param int $priority The priority at which the action should be executed. Default is 10.
     * @param int $acceptedArgs The number of arguments the action accepts. Default is 1. If this is greater than 1, the
     * `$argsHandler` argument is required.
     * @param null|callable(string, mixed...):string $argsHandler This is a function that will be used to serialize the
     * arguments passed to the action into a string. If null, the default behavior is to log the action name only.
     * @return void
     *
     * @throws InvalidArgumentException If `acceptedArgs` is greater than 1 and `argsHandler` is not provided.
     */
    public function instrumentAction(
        string $action,
        int $priority = 10,
        int $acceptedArgs = 1,
        callable $argsHandler = null,
    ): void {
        if (null === $argsHandler && $acceptedArgs > 1) {
            throw new InvalidArgumentException(
                'If acceptedArgs is greater than 1, argsHandler must be provided.',
            );
        }
        add_action(
            hook_name: $action,
            callback: function (...$args) use ($action, $argsHandler) {
                $message = $action;
                if (null !== $argsHandler) {
                    $message = $argsHandler($action, ...$args);
                }
                $this->log('Action triggered: ' . $message);
            },
            priority: $priority,
            accepted_args: $acceptedArgs,
        );
    }

    /**
     * Concatenates the action name with additional arguments for logging.
     *
     * @param string $action The action name to log.
     * @param mixed ...$args Additional arguments to concatenate with the action name.
     * @return string The resulting log message string.
     */
    public static function concatExtraArgs(string $action, mixed ...$args): string
    {
        $args = array_map(function ($arg) {
            return match (gettype($arg)) {
                'object' => self::objectRepresentation($arg),
                'array' => 'Array(' . count($arg) . ')',
                'boolean' => $arg ? 'true' : 'false',
                'resource' => 'resource(' . get_resource_type($arg) . ': ' . get_resource_id($arg) . ')',
                'NULL' => 'null',
                default => (string)$arg, // All other types can be cast to a string.
            };
        }, $args);
        return $action . ': ' . implode(', ', $args);
    }

    /**
     * Captures a Telemetry event for a WordPress action hook.
     *
     * @param string $message The message to log.
     * @param EventLevel $level The level of the event.
     * @return void
     */
    public function log(string $message, EventLevel $level = EventLevel::Info): void
    {
        $telemeter = Rollbar::getTelemeter();
        if (null === $telemeter) {
            return;
        }
        $telemeter->captureLog($message, $level);
    }

    /**
     * Custom handler for the `after_setup_theme` action.
     *
     * Adds the active theme and its ancestors (if it is a child theme) to the log message.
     *
     * @param string $action
     * @param mixed ...$args
     * @return string
     */
    public static function handlerAfterSetupTheme(string $action, mixed ...$args): string
    {
        $theme = wp_get_theme();
        $themes = [$theme->get_stylesheet_directory()];
        while ($theme->parent()) {
            $theme = $theme->parent();
            $themes[] = $theme->get_stylesheet_directory();
        }
        return $action . ': Active Theme: ' . implode(' > ', $themes);
    }

    /**
     * Custom handler for the `plugins_loaded` action.
     *
     * Adds the list of active plugins to the log message.
     *
     * @param string $action
     * @param mixed ...$args
     * @return string
     */
    public static function handlerPluginsLoaded(string $action, mixed ...$args): string
    {
        return $action . ': Active Plugins: ' . implode(', ', get_option('active_plugins'));
    }

    /**
     * Serializes an object into a string representation.
     *
     * @param object $object The object to serialize.
     * @return string
     */
    protected static function objectRepresentation(object $object): string
    {
        if ($object instanceof \UnitEnum) {
            return 'enum(' . $object::class . '::' . $object->name . ')';
        }
        if ($object instanceof \Closure) {
            return 'closure';
        }
        $class = get_class($object);
        if (!property_exists($object, 'id')) {
            return 'object(' . $class . ')';
        }
        $vars = get_object_vars($object);
        $id = $vars['id'] ?? 'unknown';

        return 'object(' . $class . ') {id: ' . $id . '}';
    }
}
