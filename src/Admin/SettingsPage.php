<?php

namespace Rollbar\WordPress\Admin;

use Rollbar\WordPress\Html\Template;
use Rollbar\WordPress\Lib\AbstractSingleton;
use Rollbar\WordPress\Plugin;
use Rollbar\WordPress\Setting;
use Rollbar\WordPress\Settings;

use function add_action;
use function add_filter;
use function add_settings_field;
use function add_settings_section;
use function plugin_dir_url;
use function register_setting;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Class Settings Page
 *
 * Creates and manages the admin settings page for the Rollbar plugin.
 *
 * @since 3.0.0
 */
final class SettingsPage extends AbstractSingleton
{
    private Plugin $plugin;

    /**
     * @inheritdoc
     */
    protected function __construct()
    {
        if (!Plugin::userCanViewAdmin()) {
            return;
        }
        $this->hooks();
    }

    /**
     * @inheritdoc
     */
    protected function postInit(): void
    {
        $this->plugin = Plugin::getInstance();
    }

    /**
     * Register the event handlers for the settings page.
     *
     * @return void
     */
    private function hooks(): void
    {
        add_action('admin_menu', $this->addAdminMenu(...));
        add_filter('plugin_action_links_' . plugin_basename(ROLLBAR_PLUGIN_FILE), $this->addAdminMenuLink(...));
        add_action('admin_init', $this->addSettings(...));
        add_action('admin_enqueue_scripts', $this->enqueueAdminScripts(...));
        add_action('admin_post_rollbar_wp_restore_defaults', $this->restoreDefaultsAction(...));
    }

    /**
     * Adds the settings page to the admin menu.
     *
     * @return void
     * @hook admin_menu
     */
    public function addAdminMenu(): void
    {
        add_submenu_page(
            parent_slug: 'options-general.php',
            page_title: 'Rollbar',
            menu_title: 'Rollbar',
            capability: 'manage_options',
            menu_slug: 'rollbar_wp',
            callback: $this->optionsPage(...),
        );
    }

    /**
     * Adds the settings link to the plugins list view.
     *
     * @param string[] $links
     * @return array
     * @hook plugin_action_links_{plugin}
     */
    public function addAdminMenuLink(array $links): array
    {
        $url = admin_url('options-general.php?' . http_build_query(['page' => 'rollbar_wp']));

        $links[] = '<a href="' . $url . '">Settings</a>';

        return $links;
    }

    /**
     * Registers the plugin settings to be displayed on the settings page.
     *
     * @return void
     */
    public function addSettings(): void
    {
        $advancedOptions = Settings::settings();

        register_setting(
            option_group: 'rollbar_wp',
            option_name: 'rollbar_wp',
        );

        // SECTION: General
        add_settings_section(
            id: 'rollbar_wp_general',
            title: false,
            callback: false,
            page: 'rollbar_wp',
        );

        // On/Off and Tokens
        add_settings_field(
            id: 'rollbar_wp_status',
            title: 'Status',
            callback: function () {
                Template::print(
                    ROLLBAR_PLUGIN_DIR . '/templates/admin/settings-status.php',
                    [
                        'php_logging_enabled' => (!empty($this->plugin->getSetting('php_logging_enabled'))) ? 1 : 0,
                        'server_side_access_token' => $this->plugin->getSetting('server_side_access_token'),
                        'js_logging_enabled' => (!empty($this->plugin->getSetting('js_logging_enabled'))) ? 1 : 0,
                        'client_side_access_token' => $this->plugin->getSetting('client_side_access_token'),
                    ],
                );
            },
            page: 'rollbar_wp',
            section: 'rollbar_wp_general',
        );

        $this->addSettingsField($advancedOptions['environment']);
        $this->addSettingsField($advancedOptions['included_errno']);

        // SECTION: Advanced
        add_settings_section(
            id: 'rollbar_wp_advanced',
            title: false, // The title is created by the callback.
            callback: $this->advancedSectionHeader(...),
            page: 'rollbar_wp',
        );

        foreach ($advancedOptions as $option) {
            if (null === $option || $option->section !== 'rollbar_wp_advanced') {
                continue;
            }

            $this->addSettingsField($option);
        }

        // SECTION: Advanced
        add_settings_section(
            id: 'rollbar_wp_telemetry',
            title: false, // The title is created by the callback.
            callback: $this->telemetrySectionHeader(...),
            page: 'rollbar_wp',
        );

        foreach ($advancedOptions as $option) {
            if (null === $option || $option->section !== 'rollbar_wp_telemetry') {
                continue;
            }

            $this->addSettingsField($option);
        }
    }

    /**
     * Registers the static files for the admin page.
     *
     * @param string $hook
     * @return void
     */
    public function enqueueAdminScripts(string $hook): void
    {
        if ($hook != 'settings_page_rollbar_wp') {
            return;
        }

        wp_enqueue_style(
            'rollbar-admin-css',
            plugin_dir_url(ROLLBAR_PLUGIN_FILE) . '/public/admin/rollbar.css',
            false,
            Plugin::VERSION,
        );

        wp_enqueue_script(
            'rollbar-admin-js',
            Plugin::getAssetUrl('/public/admin/rollbar.js'),
            [],
            Plugin::VERSION,
        );

        wp_localize_script(
            handle: 'rollbar-admin-js',
            object_name: 'rollbarSettings',
            l10n: [
                'nonce' => wp_create_nonce('rollbar_wp_api_test_logging'),
                'rest_nonce' => wp_create_nonce('wp_rest'),
                'rest_root' => esc_url_raw(rest_url()),
                'plugin_url' => plugin_dir_url(ROLLBAR_PLUGIN_FILE),
            ],
        );
    }

    /**
     * Adds a setting field to the admin setting page.
     *
     * @param Setting $setting
     * @param array{value: mixed} $args The arguments to pass to the callback. If the 'value' key is set it will be used
     *                                  as the value of the input field.
     * @return void
     */
    private function addSettingsField(Setting $setting, array $args = []): void
    {
        add_settings_field(
            id: 'rollbar_wp_' . $setting->id,
            title: $setting->getLabelElement(),
            callback: $setting->render(...),
            page: 'rollbar_wp',
            section: $setting->section,
            args: $args,
        );
    }

    /**
     * Renders the header for the "Advanced" section of the settings page.
     *
     * @return void
     */
    public function advancedSectionHeader(): void
    {
        Template::print(ROLLBAR_PLUGIN_DIR . '/templates/admin/settings-section-header.php', [
            'id' => 'rollbar_settings_advanced_header',
            'title' => 'Advanced Settings',
            'description' => '<p>See the <a href="https://docs.rollbar.com/docs/php-configuration-reference" '
             . 'target="_blank">configuration documentation</a> for more details on each of these settings.</p>',
        ]);
    }

    /**
     * Renders the header for the "Advanced" section of the settings page.
     *
     * @return void
     */
    public function telemetrySectionHeader(): void
    {
        Template::print(ROLLBAR_PLUGIN_DIR . '/templates/admin/settings-section-header.php', [
            'id' => 'rollbar_settings_telemetry_header',
            'title' => 'Telemetry Settings',
            'description' => '<p>See the <a href="https://docs.rollbar.com/docs/php-telemetry" target="_blank">Rollbar'
            . 'telemetry documentation</a> and <a href="https://developer.wordpress.org/apis/hooks/action-reference/" '
            . 'target="_blank">WordPress action reference</a> for more details on these settings.</p>',
        ]);
    }

    /**
     * Renders the settings page.
     *
     * @return void
     */
    public function optionsPage(): void
    {
        Template::print(ROLLBAR_PLUGIN_DIR . '/templates/admin/settings.php');
    }

    /**
     * Function called when the "Restore All Defaults" button is clicked.
     *
     * @return void
     */
    public static function restoreDefaultsAction(): void
    {
        if (!check_admin_referer('rollbar_wp_restore_defaults')) {
            return;
        }
        Settings::getInstance()->restoreDefaults();

        FlashMessages::addMessage(
            message: 'Default Rollbar settings restored.',
        );

        wp_redirect(admin_url('/options-general.php?page=rollbar_wp'));
        exit();
    }
}
