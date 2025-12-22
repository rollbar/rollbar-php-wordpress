<?php

/**
 * Plugin Name: Rollbar
 * Plugin URI: https://wordpress.org/plugins/rollbar
 * Description: Rollbar full-stack error tracking for WordPress.
 * Version: 3.1.1
 * Author: Rollbar
 * Author URI: https://rollbar.com
 * Text Domain: rollbar
 * License: Proprietary
 * Requires PHP: 8.1
 * Tested up to: 6.9
 * Requires at least: 6.5
 *
 * @package         Rollbar\WordPress
 * @copyright       Rollbar, Inc.
 */

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
// Exit if accessed directly
defined('ABSPATH') || exit;

const ROLLBAR_PLUGIN_FILE = __FILE__;
const ROLLBAR_PLUGIN_DIR = __DIR__;

if (!defined('ROLLBAR_NO_VENDOR')) {
    define('ROLLBAR_NO_VENDOR', false);
}

if (false === constant('ROLLBAR_NO_VENDOR')) {
    require_once ROLLBAR_PLUGIN_DIR . '/vendor/autoload.php';
}

const ROLLBAR_PLUGIN_VERSION = \Rollbar\WordPress\Plugin::VERSION;

\Rollbar\WordPress\Plugin::getInstance();
