# Rollbar for WordPress

[![Plugin Version](https://img.shields.io/wordpress/plugin/v/rollbar.svg)](https://wordpress.org/plugins/rollbar/) [![WordPress Version Compatibility](https://img.shields.io/wordpress/v/rollbar.svg)](https://wordpress.org/plugins/rollbar/) [![Downloads](https://img.shields.io/wordpress/plugin/dt/rollbar.svg)](https://wordpress.org/plugins/rollbar/) [![Rating](https://img.shields.io/wordpress/plugin/r/rollbar.svg)](https://wordpress.org/plugins/rollbar/)

Rollbar full-stack error tracking for WordPress.

Rollbar collects errors that happen in your application, notifies you, and analyzes them so you can debug and fix them.
This plugin integrates Rollbar into your WordPress installation.

#### Helpful Links

* [Documentation](https://docs.rollbar.com/docs/wordpress)
* [Official WordPress.org Plugin](https://wordpress.org/plugins/rollbar/)
* [How Rollbar Works](https://rollbar.com/discover/)
* [Rollbar Pricing](https://rollbar.com/pricing/)

[Official WordPress.org Plugin](https://wordpress.org/plugins/rollbar/)
#### Table of Contents

1. [Installation](#installation)
    1. [Through WordPress Plugin directory](#through-wordpress-plugin-directory)
    2. [Through Packagist](#through-packagist-recommended-new)
    3. [Through WPackagist](#through-wpackagist)
2. [Configuration](#configuration)
    1. [WordPress Admin](#wordpress-admin)
    2. [Programmatic Configuration](#programmatic-configuration)
3. [Advanced Use](#advanced-use)
    1. [Constants](#constants)
    2. [Environment Variables](#environment-variables)
    3. [Filters](#filters)
    4. [Telemetry](#telemetry)
4. [Help / Support](#help--support)
5. [Special thanks](#special-thanks)
6. [Contributing](#contributing)
7. [Testing](#testing)

## Installation

### Through [WordPress Plugin directory](https://wordpress.org/plugins/rollbar/)

The easiest way to install the plugin is from the WordPress Plugin directory. If you have an existing WordPress
installation, and you want to add Rollbar:

1. In your WordPress administration panel go to `Plugins` → `Add New`.
2. Search for "Rollbar" and find `Rollbar` by Rollbar in the search results.
3. Click `Install Now` next to the `Rollbar` plugin.
4. In `Plugins` → `Installed plugins` find `Rollbar` and click `Activate` underneath.

**Warning**: This installation method might not be suitable for complex WordPress projects. The plugin installed this
way will be self-contained and include all of the required dependencies for itself and the `rollbar/rollbar-php` 
library. In complex projects, this might lead to version conflicts between dependencies and other plugins/packages. If
this is an issue in your project, we recommend the "Packagist" installation method.

### Through [Packagist](https://packagist.org/packages/rollbar/rollbar-php-wordpress) *recommended new*

*Note: this only works if your WordPress project is managed with Composer.
Read [Using Composer with WordPress](https://roots.io/using-composer-with-wordpress/) for more details.*

This is a recommended way to install the Rollbar plugin for advanced projects. This way ensures the plugin and all of
its dependencies are managed by Composer.

You can install the plugin by running the following command in the root directory of your WordPress project:

```txt
composer require rollbar/rollbar-php-wordpress:^3.0
```

### Through [WPackagist](https://wpackagist.org/)

*Note: if your WordPress project is managed with Composer, we strongly recommend installing the plugin through
Packagist instead.*

*Installing the plugin from wpackagist.org instead of packagist.org will result in the plugin being managed by
Composer. However, the downside is that it's dependencies will not be managed by composer. Instead they will be packaged 
in the plugin's `vendor` directory not in your project's `vendor` directory. This has the potential to cause name 
collisions if other plugins or your project use different versions of the same dependencies.*

To install the plugin from wpackagist.org run the following steps command in the root directory of your WordPress
project:

```txt
composer require wpackagist-plugin/rollbar
```

## Configuration

The plugin can be configured in the WordPress Admin or programmatically.

### WordPress Admin

The plugin provides a settings page in the WordPress Admin that allows you to configure the plugin. This settings page
can be disabled by setting the `ROLLBAR_DISABLE_ADMIN` constant to `true` in your `wp-config.php` file.

1. In `Plugins` → `Installed plugins` find `Rollbar` and click `Activate` underneath.
2. Log into your [Rollbar account dashboard](https://rollbar.com/login/):
    1. Go to `Settings` → `Project Access Tokens`.
    2. Copy the token value under `post_client_item` and `post_server_item`.
3. In WordPress, navigate to `Settings` → `Rollbar`:
    1. Enable `PHP error logging` and/or `Javascript error logging` depending on your needs.
    2. Paste the tokens you copied in step 7 in `Access Token` section.
    3. Provide the name of your environment in `Environment`. By default, the environment will be taken from `WP_ENV`
       environment variable if it's set otherwise it's blank. We recommend to fill this out either with `development` or
       `production`.
    4. Pick a minimum logging level. Only errors at that or higher level will be reported. For
       reference: [PHP Manual: Predefined Error Constants](http://php.net/manual/en/errorfunc.constants.php).
    5. Click `Save Changes`.

### Programmatic Configuration

The plugin can also be configured programmatically. This is useful if you want to configure the plugin in a more
advanced way or if you want to disable the admin settings page.

```php
// wp-config.php

// Configure the plugin.
define( 'ROLLBAR_SETTINGS', [
    'php_logging_enabled' => true,
    'server_side_access_token' => '<your token>',
    'js_logging_enabled' => true,
    'client_side_access_token' => '<your client token>',
    'environment' => 'development',
    'included_errno' => E_ERROR,
    'enable_person_reporting' => true,
] );

// Optional: disable the admin settings page so the plugin is configured only programmatically.
define( 'ROLLBAR_DISABLE_ADMIN', true );
```

## Advanced Use

### Constants

The plugin provides a number of constants that can be used to configure the plugin. These should typically be defined in
the `wp-config.php` file.

Note: If you define these constants, they will override the settings defined in the WordPress Admin.

* `ROLLBAR_DISABLE_ADMIN` - (optional) Removes the Rollbar Admin menu from the WordPress Admin if set to `true`. This
  allows for the plugin to be used without the admin settings page, for example, if the plugin is managed via the
  `wp-config.php` file.
* `ROLLBAR_SETTINGS` - (optional) An associative array of settings to override the settings values from the WordPress
  Admin. Note: if you disable the admin settings page with `ROLLBAR_DISABLE_ADMIN` constant, this constant must be used
  to configure the plugin.
* `ROLLBAR_ACCESS_TOKEN` - The Rollbar PHP server access token.
* `ROLLBAR_CLIENT_ACCESS_TOKEN` - The Rollbar JS client access token.

#### WordPress Constants

The Rollbar plugin respects the following WordPress constants:

* `WP_ENV` - (optional) The environment name. This is used to determine the environment name for the Rollbar project.
* `WP_PROXY_HOST` - (optional) The proxy host. If both `WP_PROXY_HOST` and `WP_PROXY_PORT` are set, the plugin will use
  the respect the HTTP proxy when making requests to Rollbar.
* `WP_PROXY_PORT` - (optional) The proxy port.
* `WP_PROXY_USERNAME` - (optional) The proxy username.
* `WP_PROXY_PASSWORD` - (optional) The proxy password.
* `WP_PROXY_BYPASS_HOSTS` - (optional) The proxy bypass hosts. This is a comma-separated list of hosts that should not
  be
  proxied. If proxying is enabled, but you don't want to proxy requests to Rollbar, you can add `api.rollbar.com` to
  this list.

### Environment Variables

The plugin looks for the following environment variables to configure itself. Note: these are overridden by the
constants defined above.

* `ROLLBAR_ACCESS_TOKEN` - The Rollbar PHP server access token.
* `ROLLBAR_CLIENT_ACCESS_TOKEN` - The Rollbar JS client access token.
* `WP_ENV` - (optional) The environment name. This is used to determine the environment name for the Rollbar project.

### Filters

The plugin provides a number of filters that allow you to customize the behavior of the plugin.

#### `apply_filters('rollbar_api_admin_permission', bool $value, string $route, WP_REST_Request $request)`

Filter to allow or deny access to a Rollbar route in the WordPress REST API used in the WordPress Admin. Generally,
this should be the same as the `rollbar_user_can_view_admin` filter.

**Parameters**

* `bool $value` - The initial value. Defaults is `true` for admin users, `false` for non-admin users.
* `string $route` - The route being accessed.
* `WP_REST_Request $request` - The REST request object.

#### `apply_filters('rollbar_js_config', array $config)`

Filters the Rollbar JavaScript configuration.

**Parameters**

* `array $config` - The Rollbar JavaScript configuration array.

#### `apply_filters('rollbar_js_nonce', string|null $nonce)`

Filter that can be used to set the nonce attribute value of the frontend JS script.

**Since: 3.1.0**

**Parameters**

* `string|null $nonce` - The nonce value to be used in the script tag. If `null` the attribute is excluded. Default 
  is `null`.

#### `apply_filters('rollbar_plugin_settings', array $settings)`

Filters the Rollbar plugin settings.

**Parameters**

* `array $settings` - The Rollbar plugin settings array.

#### `apply_filters('rollbar_php_config', array $config)`

Filters the Rollbar Core SDK PHP configuration.

**Parameters**

* `array $config` - The Rollbar PHP configuration array.

#### `apply_filters('rollbar_telemetry_actions', array<string, int> $actions)`

Filter the list of actions to instrument with Telemetry.

**Parameters**

* `array<string, int> $actions` - An associative array where the keys are action names and the values are the number of
  arguments accepted by the action.

#### `apply_filters('rollbar_telemetry_custom_handlers', array<string, callable(string, mixed...):string> $handlers)`

Filter the list of custom action event handlers for Telemetry.

Note: The custom action handler will only be called if the action is instrumented with Telemetry. This means you must
select the action on the settings page, or add it to the list of actions using the `rollbar_telemetry_actions` filter.

**Parameters**

* `array<string, callable(string, mixed...):string> $handlers` - An associative array where the keys are action names
  and the values are the custom event handler.

#### `apply_filters('rollbar_user_can_view_admin', bool $disable)`

Filter to enable / disable the admin settings page of the plugin for the current user.

This filter cannot override the `ROLLBAR_DISABLE_ADMIN` constant.

**Parameters**

* `bool $allow` - `true` to enable the admin settings page, `false` to disable it.

### Telemetry

Starting in version 3.0.0 of the Rollbar plugin, Telemetry is enabled by default. Telemetry is a feature that allows
the plugin to track events that occur in your WordPress installation prior to an exception or message being sent to
Rollbar. The Telemetry data is sent to Rollbar along with the exception or message, and can be used to provide
additional context and help debug the issue.

You can modify the list of actions you want to instrument with Telemetry by selecting them on the settings page, or
using the `rollbar_telemetry_actions` filter. To use a custom handler for a specific action, use the
`rollbar_telemetry_custom_handlers` filter. This can also be used to change the handler on any of the default actions.

#### Registering custom actions

You can also instrument custom actions like this:

```php
use Rollbar\WordPress\Telemetry\Listener;

// Register a custom action with a custom handler function.
Listener::getInstance()->instrumentAction(
    action: 'my_custom_action',
    priority: 10,
    acceptedArgs: 2,
    argsHandler: function ($action, ...$args) {
        $foo = $action;
        return 'custom_listener_test_action: ' . implode(', ', $args);
    },
);

// Use the default handler for the action.
Listener::getInstance()->instrumentAction(
    action: 'my_other_custom_action',
    priority: 10,
    acceptedArgs: 1,
    argsHandler: Listener::concatExtraArgs(...),
);
```

Of course, you can also call `Rollbar::captureTelemetryEvent()` directly to send custom events. See the
[Telemetry documentation](https://docs.rollbar.com/docs/php-telemetry) for more information.

## Help / Support

If you run into any issues, please email us at [support@rollbar.com](mailto:support@rollbar.com)

For bug reports, please [open an issue on GitHub](https://github.com/rollbar/rollbar-php-wordpress/issues/new).

## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Added some feature'`)
4. Make sure tests pass (see below).
5. Push to the branch (`git push origin my-new-feature`)
6. Create a new Pull Request

## Testing

To run the tests, you will need to install the dependencies for
[@wordpress/env](https://www.npmjs.com/package/@wordpress/env) including Node.js, git, and Docker.

1. `npm install` - This will install the dependencies to set up the local environment.
2. `npm run start` - This will start a local WordPress installation with the Rollbar plugin installed.
3. `npm run test` - This will run the tests.

You can set the `WP_ENV_PHP_VERSION` environment variable to test with different versions of PHP. If you are changing
the version, you can do so by running `WP_ENV_PHP_VERSION="8.2" npm run wp-env start -- --update` and setting the
environment variable based on the version you want to test with.

## Special thanks

The original author of this package is [@flowdee](https://twitter.com/flowdee/). This is a fork and continuation of his
efforts.

## Tagging

This is only for contributors with committer access:

1. Bump the plugin version.
    1. Bump the plugin version in `readme.txt` under `Stable tag`.
    2. Add record in the `Changelog` section of the `readme.txt`.
    3. Add record in the `Upgrade Notice` section of the `readme.txt`.
    4. Bump the plugin version in `rollbar-php-wordpress.php` in the `Version:` comment.
    5. Bump the plugin version in `src/Plugin.php` in the `\Rollbar\WordPress\Plugin::VERSION` constant.
    6. Add and commit the changes you made to bump the plugin version:
       `git add readme.txt rollbar-php-wordpress.php src/Plugin.php && git commit -m"Bump version to v[version number]"`
    7. Bump versions of the JS and CSS files versions in Settings.php class to force refresh of those assets on users'
       installations.
    8. `git push origin master`
2. Tag the new version from the `master` branch and push upstream with `git tag v[version number] && git push --tags`.
3. Publish a new release on [GitHub](https://github.com/rollbar/rollbar-php-wordpress/releases).
4. Update the WordPress Plugin Directory Subversion Repository.
    1. Fetch the latest contents of Subversion repo with `svn update`.
    2. Remove the contents of `trunk/` with `rm -Rf trunk`.
    3. Update the contents of `trunk/` with a clone of the tag you created in step 2.
        1. Checkout the tag you created in step 2: `git checkout tags/v[version number]`
        2. Run `bin/build.sh` to build the plugin.
        3. Copy the contents of `dist/` to `trunk/`
        4. `svn add trunk --force`
        5. `svn commit -m "Sync with GitHub repo"`
    4. Create the Subversion tag:
       `svn copy https://plugins.svn.wordpress.org/rollbar/trunk https://plugins.svn.wordpress.org/rollbar/tags/[version number] -m" Tag [version number]"`.
       Notice the version number in Subversion doesn't include the "v" prefix.

## Disclaimer

This plugin is a community-driven contribution. All rights reserved to Rollbar. 
