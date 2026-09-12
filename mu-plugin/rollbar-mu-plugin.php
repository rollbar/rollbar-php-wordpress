<?php

/**
 * Plugin Name: Rollbar
 * Plugin URI: https://github.com/rollbar/rollbar-php-wordpress
 * Description: "Must-use" proxy plugin for Rollbar PHP WordPress.
 * Author: Rollbar
 * Author URI: https://rollbar.com
 */

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
// Exit if accessed directly
defined('ABSPATH') || exit;

$rollbar_plugin = dirname(__DIR__) . '/plugins/rollbar/rollbar.php';

if (!file_exists($rollbar_plugin)) {
    return;
}

require $rollbar_plugin;
