<?php

namespace Rollbar\WordPress;

use Psr\Log\LogLevel;
use Rollbar\Config;
use Rollbar\Defaults;
use Rollbar\Payload\Level;
use Rollbar\WordPress\Lib\AbstractSingleton;
use Rollbar\WordPress\Settings\SettingType;
use Rollbar\WordPress\Telemetry\Listener;
use WP_HTTP_Proxy;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Class Settings
 *
 * Represents the configuration management for the Rollbar plugin. This class is responsible for
 * providing and managing settings that are configured through the WordPress admin interface,
 * environment variables, or system constants. Implements the Singleton design pattern to ensure a
 * single instance of the settings is used application-wide.
 *
 * @since 3.0.0
 */
final class Settings extends AbstractSingleton
{
    /**
     * The option name where the plugin settings are saved.
     */
    private const OPTIONS_KEY = 'rollbar_wp';

    /**
     * The array of plugin settings.
     *
     * @var array
     */
    private array $settings = [];

    /**
     * @inheritdoc
     */
    protected function __construct()
    {
        $this->fetchSettings();
        $this->hooks();
    }

    /**
     * Register the event handlers for the settings.
     *
     * @return void
     */
    private function hooks(): void
    {
        add_filter('pre_update_option_rollbar_wp', $this->preUpdate(...));
    }

    /**
     * Returns an array of {@see Setting} that can be used in the plugin.
     *
     * @return array<string, null|Setting>
     */
    public static function settings(): array
    {
        static $settings;
        if (isset($settings)) {
            return $settings;
        }
        $env = Settings::getEnvironment(null);

        $defaults = Defaults::get();

        $settings = [
            'php_logging_enabled' => new Setting(
                id: 'php_logging_enabled',
                type: SettingType::Boolean,
                default: false,
                section: 'rollbar_wp_general',
            ),
            'js_logging_enabled' => new Setting(
                id: 'js_logging_enabled',
                type: SettingType::Boolean,
                default: false,
                section: 'rollbar_wp_general',
            ),
            'environment' => new Setting(
                id: 'environment',
                type: SettingType::Text,
                helpText: '<p><code>WP_ENV</code> environment variable: <code>' . $env . '</code></p>' .
                '<p>Rollbar for WordPress honors the <code>WP_ENV</code> environment variable. ' .
                'If the <code>environment</code> setting is not specified here, it will take ' .
                'precendence over the default value.</strong></p>',
                default: 'production',
                section: 'rollbar_wp_general',
            ),
            'included_errno' => new Setting(
                id: 'included_errno',
                type: SettingType::Select,
                label: 'Logging Level',
                default: E_ERROR,
                options: [
                    E_ERROR => 'Fatal run-time errors (E_ERROR) only',
                    E_WARNING => 'Run-time warnings (E_WARNING) and above',
                    E_PARSE => 'Compile-time parse errors (E_PARSE) and above',
                    E_NOTICE => 'Run-time notices (E_NOTICE) and above',
                    E_USER_ERROR => 'User-generated error messages (E_USER_ERROR) and above',
                    E_USER_WARNING => 'User-generated warning messages (E_USER_WARNING) and above',
                    E_USER_NOTICE => 'User-generated notice messages (E_USER_NOTICE) and above',
                    E_STRICT => 'Suggest code changes to ensure forward compatibility (E_STRICT) and above',
                    E_DEPRECATED => 'Warnings about code that won\'t work in future versions (E_DEPRECATED) and above',
                    E_ALL => 'Absolutely everything (E_ALL)',
                ],
                section: 'rollbar_wp_general',
            ),
            'agent_log_location' => new Setting(
                id: 'agent_log_location',
                type: SettingType::Text,
                default: $defaults->agentLogLocation(),
            ),
            'allow_exec' => new Setting(
                id: 'allow_exec',
                type: SettingType::Boolean,
                default: $defaults->allowExec(),
            ),
            'autodetect_branch' => new Setting(
                id: 'autodetect_branch',
                type: SettingType::Boolean,
                default: $defaults->autodetectBranch(),
            ),
            'branch' => new Setting(
                id: 'branch',
                type: SettingType::Text,
                default: $defaults->branch(),
            ),
            'capture_error_stacktraces' => new Setting(
                id: 'capture_error_stacktraces',
                type: SettingType::Boolean,
                default: $defaults->captureErrorStacktraces(),
            ),
            'code_version' => new Setting(
                id: 'code_version',
                type: SettingType::Text,
                default: $defaults->codeVersion(),
            ),
            'endpoint' => new Setting(
                id: 'endpoint',
                type: SettingType::Text,
                default: $defaults->endpoint(),
            ),
            'fluent_host' => new Setting(
                id: 'fluent_host',
                type: SettingType::Text,
                default: $defaults->fluentHost(),
            ),
            'fluent_port' => new Setting(
                id: 'fluent_port',
                type: SettingType::Text,
                default: $defaults->fluentPort(),
            ),
            'fluent_tag' => new Setting(
                id: 'fluent_tag',
                type: SettingType::Text,
                default: $defaults->fluentTag(),
            ),
            'handler' => new Setting(
                id: 'handler',
                type: SettingType::Select,
                default: $defaults->handler(),
                options: [
                    'blocking' => 'blocking',
                    'agent' => 'agent',
                    'fluent' => 'fluent',
                ],
            ),
            'host' => new Setting(
                id: 'host',
                type: SettingType::Text,
                default: $defaults->host(),
            ),
            'include_error_code_context' => new Setting(
                id: 'include_error_code_context',
                type: SettingType::Boolean,
                default: $defaults->includeErrorCodeContext(),
            ),
            'include_exception_code_context' => new Setting(
                id: 'include_exception_code_context',
                type: SettingType::Boolean,
                default: $defaults->includeExceptionCodeContext(),
            ),
            'include_raw_request_body' => new Setting(
                id: 'include_raw_request_body',
                type: SettingType::Boolean,
                default: $defaults->rawRequestBody(),
            ),
            'local_vars_dump' => new Setting(
                id: 'local_vars_dump',
                type: SettingType::Boolean,
                default: $defaults->localVarsDump(),
            ),
            'log_payload' => new Setting(
                id: 'log_payload',
                type: SettingType::Boolean,
                default: $defaults->logPayload(),
            ),
            'max_items' => new Setting(
                id: 'max_items',
                type: SettingType::Integer,
                default: $defaults->maxItems(),
            ),
            'max_nesting_depth' => new Setting(
                id: 'max_nesting_depth',
                type: SettingType::Integer,
                default: $defaults->maxNestingDepth(),
            ),
            'minimum_level' => new Setting(
                id: 'minimum_level',
                type: SettingType::Select,
                default: $defaults->minimumLevel(),
                options: [
                    100000 => Level::EMERGENCY . ', ' . Level::ALERT . ', ' . Level::CRITICAL,
                    10000 => Level::ERROR . ', ' . Level::WARNING,
                    1000 => Level::WARNING,
                    100 => Level::NOTICE . ', ' . Level::INFO,
                    10 => Level::DEBUG,
                ],
            ),
            'enable_person_reporting' => new Setting(
                id: 'enable_person_reporting',
                type: SettingType::Boolean,
                helpText: 'Adds the current user to the payload as a person. This will allow Rollbar to display the '
                . 'user\'s details in the Rollbar UI.',
                default: false,
            ),
            'capture_ip' => new Setting(
                id: 'capture_ip',
                type: SettingType::Boolean,
                label: 'Capture IP Address',
                default: $defaults->captureIp(),
            ),
            'capture_email' => new Setting(
                id: 'capture_email',
                type: SettingType::Boolean,
                default: $defaults->captureEmail(),
            ),
            'capture_username' => new Setting(
                id: 'capture_username',
                type: SettingType::Boolean,
                default: $defaults->captureUsername(),
            ),
            'proxy' => new Setting(
                id: 'proxy',
                type: SettingType::Text,
                default: '',
            ),
            'raise_on_error' => new Setting(
                id: 'raise_on_error',
                type: SettingType::Boolean,
                default: $defaults->raiseOnError(),
            ),
            'report_suppressed' => new Setting(
                id: 'report_suppressed',
                type: SettingType::Boolean,
                default: $defaults->reportSuppressed(),
            ),
            'root' => new Setting(
                id: 'root',
                type: SettingType::Text,
                default: ABSPATH,
            ),
            'send_message_trace' => new Setting(
                id: 'send_message_trace',
                type: SettingType::Boolean,
                default: $defaults->sendMessageTrace(),
            ),
            'timeout' => new Setting(
                id: 'timeout',
                type: SettingType::Integer,
                default: $defaults->timeout(),
            ),
            'transmit' => new Setting(
                id: 'transmit',
                type: SettingType::Boolean,
                default: $defaults->transmit(),
            ),
            'use_error_reporting' => new Setting(
                id: 'use_error_reporting',
                type: SettingType::Boolean,
                default: $defaults->useErrorReporting(),
            ),
            'verbose' => new Setting(
                id: 'verbose',
                type: SettingType::Select,
                default: $defaults->verbose(),
                options: [
                    LogLevel::EMERGENCY => LogLevel::EMERGENCY,
                    LogLevel::ALERT => LogLevel::ALERT,
                    LogLevel::CRITICAL => LogLevel::CRITICAL,
                    LogLevel::ERROR => LogLevel::ERROR,
                    LogLevel::WARNING => LogLevel::WARNING,
                    LogLevel::NOTICE => LogLevel::NOTICE,
                    LogLevel::INFO => LogLevel::INFO,
                    LogLevel::DEBUG => LogLevel::DEBUG,
                ],
            ),
            'enable_must_use_plugin' => new Setting(
                id: 'enable_must_use_plugin',
                type: SettingType::Boolean,
                helpText: 'Allows Rollbar plugin to be loaded as early as possible as a Must-Use plugin. Activating / '
                . 'deactivating the plugin in the plugins admin panel won\'t have an effect as long as this option '
                . 'in enabled.',
                default: false,
            ),
            'enable_telemetry_listener' => new Setting(
                id: 'enable_telemetry_listener',
                type: SettingType::Boolean,
                helpText: 'Enables the collection of telemetry data to help debug issues.',
                default: true,
                section: 'rollbar_wp_telemetry',
            ),
            'include_items_in_telemetry' => new Setting(
                id: 'include_items_in_telemetry',
                type: SettingType::Boolean,
                helpText: 'Include Rollbar captured exceptions and messages in the telemetry data of future exceptions '
                . 'and messages.',
                default: true,
                section: 'rollbar_wp_telemetry',
            ),
            'include_ignored_items_in_telemetry' => new Setting(
                id: 'include_ignored_items_in_telemetry',
                type: SettingType::Boolean,
                helpText: 'Include Rollbar captured exceptions and messages in the telemetry data of future exceptions '
                . 'and messages even if they they are ignored because they have a lower level.',
                default: false,
                section: 'rollbar_wp_telemetry',
            ),
            'telemetry_hooks' => new Setting(
                id: 'telemetry_hooks',
                type: SettingType::CheckBox,
                label: 'Enable Telemetry Actions',
                helpText: 'Adds a telemetry event for each action that is hooked enabled in this list.',
                default: array_keys(Listener::DEFAULT_ACTIONS),
                options: array_combine(array_keys(Listener::ALL_ACTIONS), array_keys(Listener::ALL_ACTIONS)),
                section: 'rollbar_wp_telemetry',
                inputArgs: [
                    'sort' => true,
                ],
            ),
            // The following fields are intentionally left out of the Admin UI. They will be set later in the plugin or
            // can be programmed by an end user in their WP site.
            'access_token' => null,
            'ca_cert_path' => null,
            'check_ignore' => null,
            'custom' => null,
            'custom_data_method' => null,
            'custom_truncation' => null,
            'base_api_url' => null,
            'enabled' => null,
            'error_sample_rates' => null,
            'exception_sample_rates' => null,
            'log_payload_logger' => null,
            'person' => null,
            'person_fn' => null,
            'scrubber' => null,
            'scrub_fields' => null,
            'scrub_safelist' => null,
            'transformer' => null,
            'telemetry' => null,
            'verbose_logger' => null,
        ];
        return $settings;
    }

    /**
     * Returns the type of the setting, otherwise null if the key is not found, or it is skipped.
     *
     * @param string $key The setting key.
     * @return SettingType|null
     */
    public static function getSettingType(string $key): null|SettingType
    {
        return self::settings()[$key]?->type ?? null;
    }

    /**
     * Returns the default value for a setting.
     *
     * @param string $setting
     * @return mixed
     */
    public static function getDefaultOption(string $setting): mixed
    {
        return self::settings()[$setting]?->default ?? null;
    }

    /**
     * The filter applied before the `rollbar_wp` settings are saved to the DB.
     *
     * @param array $settings The array of settings.
     * @return array
     */
    public static function preUpdate(array $settings): array
    {
        // Empty checkboxes don't get sent in the $_POST. Fill missing boolean settings with default values.
        foreach (Settings::settings() as $setting) {
            if (null == $setting || isset($settings[$setting->id]) || SettingType::Boolean !== $setting->type) {
                continue;
            }
            $settings[$setting->id] = false;
        }

        $settings['enabled'] = $settings['php_logging_enabled'] ?? false;

        if (isset($settings['enable_must_use_plugin']) && $settings['enable_must_use_plugin']) {
            try {
                Plugin::getInstance()->enableMustUsePlugin();
            } catch (\Exception $exception) {
                add_action('admin_notices', function () {
                    echo '<div class="error notice"><p><strong>Error:</strong>'
                    . 'failed to enable the Rollbar Must-Use plugin.</p></div>';
                });
                $settings['enable_must_use_plugin'] = false;
            }
        } else {
            try {
                Plugin::getInstance()->disableMustUsePlugin();
            } catch (\Exception $exception) {
                add_action('admin_notices', function () {
                    echo '<div class="error notice"><p><strong>Error:</strong>'
                        . 'failed to disable the Rollbar Must-Use plugin.</p></div>';
                });
                $settings['enable_must_use_plugin'] = true;
            }
        }

        // Don't store default values in the database, so future updates to default values in the SDK get propagated.
        foreach ($settings as $setting_name => $setting_value) {
            if ($setting_value == Plugin::getInstance()->settingsInstance()->getDefaultOption($setting_name)) {
                unset($settings[$setting_name]);
            }
        }

        return $settings;
    }

    /**
     * Returns all the settings as an array.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->settings;
    }

    /**
     * Returns a single setting, otherwise null if the $name is invalid.
     *
     * @param string $name The setting name.
     * @return mixed
     */
    public function get(string $name): mixed
    {
        return $this->settings[$name] ?? null;
    }

    /**
     * Sets a value to the setting of the given name.
     *
     * @param string $name The name of the setting.
     * @param mixed $value The value of the setting.
     * @return void
     */
    public function set(string $name, mixed $value): void
    {
        $this->settings[$name] = $value;
    }

    /**
     * Save settings to the DB.
     *
     * @param array $settings
     * @return void
     */
    public function saveSettings(array $settings): void
    {
        $option = get_option('rollbar_wp');

        $option = array_merge($option, $settings);

        foreach ($settings as $setting => $value) {
            $this->settings[$setting] = $value;
        }

        update_option('rollbar_wp', $option);
    }

    /**
     * Sets all the settings back to the default value.
     *
     * @return void
     */
    public function restoreDefaults()
    {
        $settings = [];

        foreach (Settings::listOptions() as $option) {
            $settings[$option] = $this->getDefaultOption($option);
        }

        $this->saveSettings($settings);
    }

    /**
     * Returns the array of option names for the Rollbar configs.
     *
     * @return string[]
     */
    public static function listOptions(): array
    {
        return array_merge(
            Config::listOptions(),
            array_keys(self::settings()),
        );
    }

    /**
     * Returns a reasonable boolean from a given value.
     *
     * @param mixed $value
     * @return bool
     */
    public static function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on']);
        }
        if (is_int($value)) {
            return $value !== 0;
        }
        return boolval($value);
    }

    /**
     * Fetch settings provided in Admin -> Tools -> Rollbar
     *
     * @returns void
     */
    private function fetchSettings(): void
    {
        $options = $this->settings;

        if (!Plugin::disabledAdmin() && $saved_options = get_option(self::OPTIONS_KEY)) {
            $options = array_merge($options, $saved_options);
        }
        if (defined('ROLLBAR_SETTINGS') && is_array(constant('ROLLBAR_SETTINGS'))) {
            $options = array_merge($options, constant('ROLLBAR_SETTINGS'));
        }

        $options['environment'] = self::getEnvironment($options['environment'] ?? null);
        $options['proxy'] = self::getProxySettings($options['proxy'] ?? null);
        $options['server_side_access_token'] = self::getServerAccessToken(
            $options['server_side_access_token'] ?? null,
        );
        $options['client_side_access_token'] = self::getClientAccessToken(
            $options['client_side_access_token'] ?? null,
        );

        $settings = [
            'php_logging_enabled' => (!empty($options['php_logging_enabled'])) ? 1 : 0,
            'js_logging_enabled' => (!empty($options['js_logging_enabled'])) ? 1 : 0,
            'server_side_access_token' => (!empty($options['server_side_access_token'])) ?
                esc_attr(trim($options['server_side_access_token'])) :
                '',
            'client_side_access_token' => (!empty($options['client_side_access_token'])) ?
                trim($options['client_side_access_token']) :
                '',
            'included_errno' => (!empty($options['included_errno'])) ?
                esc_attr(trim($options['included_errno'])) :
                self::getDefaultOption('included_errno'),
        ];

        foreach (self::listOptions() as $option) {
            // 'access_token' and 'enabled' are different in WordPress plugin
            // look for 'server_side_access_token' and 'php_logging_enabled' above
            if (in_array($option, ['access_token', 'enabled'])) {
                continue;
            }

            if (!isset($options[$option])) {
                $value = $this->getDefaultOption($option);
            } else {
                $value = $options[$option];
            }

            $settings[$option] = $value;
        }

        /**
         * Filter the Rollbar plugin settings.
         *
         * @param array $settings The Rollbar plugin settings array.
         * @since 3.0.0
         *
         */
        $this->settings = apply_filters('rollbar_plugin_settings', $settings);
    }

    /**
     * Returns the provided environment if set, otherwise retrieves it from the system config or environment.
     *
     * @param string|null $environment The current environment value.
     * @return string|null
     */
    public static function getEnvironment(string|null $environment): string|null
    {
        if (null !== $environment) {
            $environment = trim($environment);
        }
        if (empty($environment)) {
            $environment = null;
        }
        $environment = trim(self::getSystemSetting('WP_ENV', $environment) ?? '');
        if (!empty($environment)) {
            return $environment;
        }
        return trim(self::getSystemSetting('WP_ENVIRONMENT_TYPE', $environment) ?? '');
    }

    /**
     * The default `person_fn` method.
     *
     * @return array|null
     */
    public static function getPersonFunction(): null|array
    {
        $user = wp_get_current_user();
        if (!$user) {
            return null;
        }
        return [
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
        ];
    }

    /**
     * Returns the provided client access token if set, otherwise retrieves it from the system config or environment.
     *
     * @param string|null $token The current access token value.
     * @return string|null The access token to use for the frontend.
     */
    private static function getClientAccessToken(string|null $token): string|null
    {
        if (!empty($token)) {
            $token = trim($token);
        }
        if (empty($token)) {
            $token = null;
        }
        return trim(self::getSystemSetting('ROLLBAR_CLIENT_ACCESS_TOKEN', $token) ?? '');
    }

    /**
     * Returns the provided server access token if set, otherwise retrieves it from the system config or environment.
     *
     * @param string|null $token The current access token value.
     * @return string|null The access token to use server-side.
     */
    private static function getServerAccessToken(string|null $token): string|null
    {
        if (!empty($token)) {
            $token = trim($token);
        }
        if (empty($token)) {
            $token = null;
        }
        return trim(self::getSystemSetting('ROLLBAR_ACCESS_TOKEN', $token) ?? '');
    }

    /**
     * Retrieves the specified setting from either a constant or environment variable.
     *
     * If the default is provided, it will be used if the constant does not exist. If the default is `null`, the
     * environment variable will be used if it exists. Otherwise, `null` will be returned.
     *
     * @param string $key The name of the constant or environment variable.
     * @param mixed $default The default value to use if the setting is not found.
     * @return mixed The value of the constant, environment variable, the default, or null.
     */
    private static function getSystemSetting(string $key, mixed $default = null): mixed
    {
        if (defined($key)) {
            return constant($key);
        }
        if (null !== $default) {
            return $default;
        }
        if ($value = getenv($key)) {
            return $value;
        }
        return $default;
    }

    /**
     * Retrieves the proxy settings based on provided input or system config.
     *
     * @param string|array|null $proxy The proxy settings as string, array, or null. If null or empty, attempts to fetch
     *                                 system-defined proxy constants.
     *
     * @return string|array|null The proxy settings in string, array, or null if no proxy is defined or enabled.
     */
    private function getProxySettings(string|array|null $proxy): string|array|null
    {
        if (!empty($proxy)) {
            return $proxy;
        }
        $proxy = new WP_HTTP_Proxy();
        if (!$proxy->is_enabled() || !$proxy->send_through_proxy('https://api.rollbar.com/api')) {
            return null;
        }
        $proxySettings = [
            'address' => $proxy->host() . ':' . $proxy->port(),
        ];
        if ($proxy->username() && $proxy->password()) {
            $proxySettings['username'] = $proxy->username();
            $proxySettings['password'] = $proxy->password();
        }
        return $proxySettings;
    }
}
