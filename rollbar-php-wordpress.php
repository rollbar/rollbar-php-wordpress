<?php
/**
 * Plugin Name:     Rollbar
 * Plugin URI:      https://wordpress.org/plugins/rollbar
 * Description:     Rollbar full-stack error tracking for WordPress
 * Version:         2.6.4
 * Author:          Rollbar
 * Author URI:      https://rollbar.com
 * Text Domain:     rollbar
 * Requires PHP:    5.6
 * Tested up to:    5.8.4
 *
 * @package         Rollbar\Wordpress
 * @author          flowdee,arturmoczulski
 * @copyright       Rollbar, Inc.
 */
 
namespace Rollbar\Wordpress;

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function composer_notice() {
	$class = 'notice notice-error';
	$message = __( 'Rollbar requires composer dependencies to be installed.', 'rollbar' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

if ( ! file_exists( \plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ){
	add_action( 'admin_notices', __NAMESPACE__ . '\\composer_notice' ); 
	return;
}
/*
 * Libs
 * 
 * The included copy of rollbar-php is only going to be loaded if the it has
 * not been loaded through Composer yet.
 */
if( !class_exists('Rollbar\Rollbar') || !class_exists('Rollbar\Wordpress\Plugin') ) {
    require_once \plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

\Rollbar\Wordpress\Plugin::load();
