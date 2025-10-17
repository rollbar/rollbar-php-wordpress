<?php

namespace Rollbar\WordPress;

use Exception;
use Rollbar\Rollbar;
use Rollbar\RollbarJsHelper;
use Rollbar\WordPress\Admin\FlashMessages;
use Rollbar\WordPress\Admin\SettingsPage;
use Rollbar\WordPress\API\AdminAPI;
use Rollbar\WordPress\Lib\AbstractSingleton;
use Rollbar\WordPress\Settings\SettingType;
use Rollbar\WordPress\Telemetry\Listener;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Class Plugin
 *
 * The main plugin engine room. It is responsible for setting up and running the plugin.
 */
final class Plugin extends AbstractSingleton
{
    public const VERSION = '3.0.0';

    /**
     * Configuration array for Rollbar.
     *
     * @var array
     */
    private array $config;

    /**
     * Settings instance for managing plugin configuration
     *
     * @var Settings|null
     */
    private Settings|null $settings;

    /**
     * The telemetry listener instance.
     *
     * @var Listener|null
     */
    private Listener|null $listener;

    /**
     * Initialize the Plugin instance
     *
     * Sets up settings, initializes configuration, registers hooks, and starts PHP logging.
     */
    protected function __construct()
    {
        $this->settings = Settings::getInstance();
        $this->config = [];
        $this->hooks();
        $this->initPhpLogging();
    }

    /**
     * Post-initialization tasks
     *
     * Sets up admin views and API after the main initialization is complete.
     *
     * @return void
     */
    protected function postInit(): void
    {
        // Set up the telemetry listener.
        if ($this->getSetting('enable_telemetry_listener')) {
            $this->listener = Listener::getInstance();
        }
    }

    /**
     * Handles the 'init' action hook.
     *
     * @return void
     */
    public function onInit(): void
    {
        // Set up admin views and API.
        SettingsPage::getInstance();
        AdminAPI::getInstance();
    }

    /**
     * Updates the plugin configuration
     *
     * Merges the provided configuration with the existing one and applies
     * it to the Rollbar logger if available.
     *
     * @param array $config Configuration options to merge with existing config
     * @return void
     */
    public function configure(array $config): void
    {
        $this->config = array_merge($this->config, $config);

        if ($logger = Rollbar::logger()) {
            $logger->configure($this->config);
        }
    }

    /**
     * Returns true if the admin area should be disabled.
     *
     * Disable the admin settings page if the `ROLLBAR_DISABLE_ADMIN` constant is defined and set to `true`.
     * This allows for the plugin to be used without the admin settings page, for example, if the plugin is managed
     * via the `wp-config.php` file.
     *
     * @return bool
     * @since 3.0.0
     */
    public static function disabledAdmin(): bool
    {
        return defined('ROLLBAR_DISABLE_ADMIN') && constant('ROLLBAR_DISABLE_ADMIN');
    }

    /**
     * Returns true if the admin area should be disabled.
     *
     * @return bool
     * @since 3.0.0
     */
    public static function userCanViewAdmin(): bool
    {
        if (self::disabledAdmin()) {
            return false;
        }

        /**
         * Filter to enable / disable the admin settings page of the plugin for the current user.
         *
         * This filter cannot override the `ROLLBAR_DISABLE_ADMIN` constant.
         *
         * @param bool $allow `true` to enable the admin settings page, `false` to disable it.
         * @since 3.0.0
         */
        return apply_filters('rollbar_user_can_view_admin', current_user_can('manage_options')) === true;
    }

    /**
     * @param string $path
     * @return string
     *
     * @since 3.0.0
     */
    public static function getAssetUrl(string $path): string
    {
        if (str_starts_with($path, '/')) {
            $path = substr($path, 1);
        }
        return plugin_dir_url(ROLLBAR_PLUGIN_FILE) . $path;
    }

    /**
     * Retrieve the value of a specific setting.
     *
     * @param string $setting The name of the setting to retrieve.
     * @return mixed The value of the setting, or null if the setting does not exist.
     *
     * @since 3.0.0
     */
    public function getSetting(string $setting): mixed
    {
        return $this->settings->get($setting) ?? null;
    }

    /**
     * Sets a specific setting with the provided value.
     *
     * @param string $setting The name of the setting to be updated.
     * @param mixed $value The value to be assigned to the specified setting.
     * @return void
     *
     * @since 3.0.0
     */
    public function setSetting(string $setting, mixed $value): void
    {
        $this->settings->set($setting, $value);
    }

    /**
     * Get the Settings instance used by this plugin
     *
     * @return Settings The settings instance
     */
    public function settingsInstance(): Settings
    {
        return $this->settings;
    }

    /**
     * Register WordPress hooks and actions
     *
     * Sets up action hooks for JavaScript logging in both frontend and admin areas.
     *
     * @return void
     */
    private function hooks(): void
    {
        add_action('init', $this->onInit(...));
        add_action('wp_head', $this->initJsLogging(...));
        add_action('admin_head', $this->initJsLogging(...));
    }

    /**
     * Build the error reporting level bitmask
     *
     * Creates a bitmask of PHP error reporting levels up to and including the specified cutoff level.
     *
     * @param int $cutoff The maximum error level to include
     * @return int The combined bitmask of all error levels up to the cutoff
     */
    public static function buildIncludedErrno(int $cutoff): int
    {
        $levels = [
            E_ERROR,
            E_WARNING,
            E_PARSE,
            E_NOTICE,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
            E_USER_ERROR,
            E_USER_WARNING,
            E_USER_NOTICE,
            E_STRICT,
            E_RECOVERABLE_ERROR,
            E_DEPRECATED,
            E_USER_DEPRECATED,
            E_ALL,
        ];

        $included_errno = 0;

        foreach ($levels as $level) {
            if ($level <= $cutoff) {
                $included_errno |= $level;
            }
        }

        return $included_errno;
    }

    /**
     * Initialize PHP error logging with Rollbar
     *
     * Sets up the Rollbar PHP error handler if PHP logging is enabled.
     * Handles configuration errors by displaying appropriate error messages.
     *
     * @param bool $ignoreEnabledSetting If true, the plugin will not check the 'php_logging_enabled' setting first.
     *
     * @return void
     */
    public function initPhpLogging(bool $ignoreEnabledSetting = false): void
    {
        // Return if logging is not enabled
        if (!$ignoreEnabledSetting && false === $this->getSetting('php_logging_enabled')) {
            return;
        }

        // installs global error and exception handlers
        try {
            Rollbar::init($this->buildPHPConfig());
        } catch (Exception $exception) {
            FlashMessages::addMessage(
                message: 'Rollbar is misconfigured. Please, fix your configuration here: <a href="'
                . admin_url('/options-general.php?page=rollbar_wp') . '">Rollbar Settings</a>.',
                type: 'error',
            );
        }
    }

    /**
     * Build the PHP configuration for Rollbar
     *
     * Generates the configuration array for the Rollbar PHP SDK from the plugin settings.
     * Processes boolean values and special settings like error reporting levels and person function.
     *
     * @return array The complete Rollbar PHP configuration
     */
    public function buildPHPConfig(): array
    {
        $config = $this->settings->getAll();

        $config['access_token'] = $this->getSetting('server_side_access_token');
        $config['included_errno'] = self::buildIncludedErrno($this->getSetting('included_errno'));
        $config['timeout'] = intval($this->getSetting('timeout'));

        // Set up telemetry
        $config['telemetry'] = false;
        if ($this->getSetting('enable_telemetry_listener')) {
            $config['telemetry'] = [
                'includeItemsInTelemetry' => $this->getSetting('include_items_in_telemetry'),
                'includeIgnoredItemsInTelemetry' => $this->getSetting('include_ignored_items_in_telemetry'),
            ];
        }

        foreach ($config as $setting => $value) {
            if (SettingType::Boolean !== Settings::getSettingType($setting)) {
                continue;
            }
            $config[$setting] = Settings::toBoolean($value);
        }

        if ($config['enable_person_reporting'] && empty($config['person_fn']) && empty($config['person'])) {
            $config['person_fn'] = Settings::getPersonFunction(...);
        }

        $config['framework'] = 'wordpress ' . get_bloginfo('version');

        /**
         * Filters the Rollbar Core SDK PHP configuration.
         *
         * @param array $config The Rollbar PHP configuration array.
         * @since 3.0.0
         */
        return apply_filters('rollbar_php_config', $config);
    }

    /**
     * Initialize JavaScript error logging with Rollbar
     *
     * Sets up the Rollbar JavaScript error handler if JS logging is enabled.
     * Checks for valid configuration and outputs the necessary JavaScript code.
     *
     * @return void
     */
    public function initJsLogging(): void
    {
        // Return if logging is not enabled
        if (false === $this->getSetting('js_logging_enabled')) {
            return;
        }

        // Return if access token is not set
        if (empty($this->getSetting('client_side_access_token'))) {
            FlashMessages::addMessage(
                message: 'Rollbar is misconfigured. Please, fix your configuration here: <a href="'
                . admin_url('/options-general.php?page=rollbar_wp') . '">Rollbar Settings</a>.',
                type: 'error',
            );
            return;
        }

        $rollbarJs = RollbarJsHelper::buildJs($this->buildJsConfig());

        echo $rollbarJs;
    }

    /**
     * Build the JavaScript configuration for Rollbar
     *
     * Generates the configuration array for the Rollbar JavaScript SDK from the plugin settings.
     * Applies filters to allow customization of the JavaScript configuration.
     *
     * @return array The complete Rollbar JavaScript configuration
     */
    public function buildJsConfig(): array
    {
        $config = [
            'accessToken' => $this->getSetting('client_side_access_token'),
            'captureUncaught' => true,
            'payload' => [
                'environment' => $this->getSetting('environment'),
            ],
        ];

        /**
         * Filters the Rollbar JavaScript configuration.
         *
         * @param array $config The Rollbar JavaScript configuration array.
         * @since 3.0.0
         *
         */
        return apply_filters('rollbar_js_config', $config);
    }

    /**
     * Check if the Must-Use plugin is enabled
     *
     * Determines if the Rollbar Must-Use plugin is installed and active.
     *
     * @return bool True if the Must-Use plugin exists, false otherwise
     */
    public static function mustUsePluginEnabled(): bool
    {
        return file_exists(plugin_dir_path(__DIR__) . '../../mu-plugins/rollbar-mu-plugin.php');
    }

    /**
     * Enable the Must-Use plugin
     *
     * Copies the Rollbar Must-Use plugin into the WordPress mu-plugins directory,
     * creating the directory if it doesn't exist.
     *
     * @return void
     * @throws Exception If the directory cannot be created or the plugin cannot be copied
     */
    public function enableMustUsePlugin(): void
    {
        $muPluginsDir = plugin_dir_path(__DIR__) . '../../mu-plugins/';

        $muPluginFilepath = plugin_dir_path(__DIR__) . 'mu-plugin/rollbar-mu-plugin.php';

        if (!file_exists($muPluginsDir) && !mkdir($muPluginsDir, 0700)) {
            throw new Exception('Can\'t create the mu-plugins directory: ' . $muPluginsDir);
        }

        if (!copy($muPluginFilepath, $muPluginsDir . 'rollbar-mu-plugin.php')) {
            throw new Exception('Can\'t copy mu-plugin from ' . $muPluginFilepath . ' to ' . $muPluginsDir);
        }
    }

    /**
     * Disable the Must-Use plugin
     *
     * Removes the Rollbar Must-Use plugin from the WordPress mu-plugins directory if it exists.
     *
     * @return void
     * @throws Exception If the plugin file cannot be deleted
     */
    public function disableMustUsePlugin(): void
    {
        $file = plugin_dir_path(__DIR__) . '../../mu-plugins/rollbar-mu-plugin.php';
        if (file_exists($file) && !unlink($file)) {
            throw new Exception('Can\'t delete the mu-plugin');
        }
    }
}
