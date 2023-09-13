<?php
/**
 * Plugin Name:     Rollbar
 * Plugin URI:      https://wordpress.org/plugins/rollbar
 * Description:     Rollbar full-stack error tracking for WordPress
 * Version:         2.7.1
 * Author:          Rollbar
 * Author URI:      https://rollbar.com
 * Text Domain:     rollbar
 * Requires PHP:    7.0
 * Tested up to:    6.3.1
 *
 * @package         Rollbar\Wordpress
 * @author          flowdee,arturmoczulski
 * @copyright       Rollbar, Inc.
 */
 
namespace Rollbar\Wordpress;

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/*
 * Libs
 * 
 * The included copy of rollbar-php is only going to be loaded if the it has
 * not been loaded through Composer yet.
 */
if( !class_exists('Rollbar\Rollbar') || !class_exists('Rollbar\Wordpress\Plugin') ) {
	// if PHP version is less than 8, use the php7 dependencies
	if ( version_compare( PHP_VERSION, '8.0.0', '<' ) ) {
		require_once \plugin_dir_path( __FILE__ ) . 'php7/vendor/autoload.php';
	} else {
		require_once \plugin_dir_path( __FILE__ ) . 'php8/vendor/autoload.php';
	}
}

\Rollbar\Wordpress\Plugin::load();
