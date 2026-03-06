<?php

/**
 * Plugin Name: Conflict
 * Plugin URI: https://github.com/rollbar/rollbar-php-wordpress/tree/master/tests/conflict
 * Description: A test plugin that conflicts with Rollbar.
 * Version: 1.0.0
 * Author: Rollbar
 * Author URI: https://rollbar.com
 * Text Domain: conflict
 * License: MIT
 * Requires PHP: 8.1
 * Tested up to: 6.9
 * Requires at least: 6.5
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

const CONFLICT_PLUGIN_DIR = __DIR__;

require_once CONFLICT_PLUGIN_DIR . '/vendor/autoload.php';

\Conflict\Plugin::init();
