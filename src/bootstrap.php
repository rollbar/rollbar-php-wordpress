<?php

/**
 * Composer autoloads this file if the plugin is installed as a Composer dependency.
 *
 * This file defines the ROLLBAR_NO_VENDOR constant to prevent the plugin from loading the vendor directory.
 *
 * Note: the vendor directory is not included in the Packagist package. Only the plugin's source code is included.
 * However, the vendor directory will be included in the plugin's source code when the plugin is installed via the
 * WordPress plugin directory.
 */

if (!defined('ROLLBAR_NO_VENDOR')) {
    define('ROLLBAR_NO_VENDOR', true);
}
