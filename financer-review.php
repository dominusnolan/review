<?php

/*
 * Plugin Name: Financer Review
 * Version: 0.1.0
 * Text Domain:     financer-review
 * Domain Path:     /languages
*/

use ComposePress\Dice\Dice;


/**
 * Singleton instance function. We will not use a global at all as that defeats the purpose of a singleton and is a bad design overall
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @return Financer_Review\Plugin
 */
function financer_review() {
	return financer_review_container()->create( '\Financer_Review\Plugin' );
}

/**
* This container singleton enables you to setup unit testing by passing an environment file to map classes in Dice
 *
 * @param string $env
 *
* @return \ComposePress\Dice\Dice
 */
function financer_review_container( $env = 'prod' ) {
	static $container;
	if ( empty( $container ) ) {
		$container = new Dice();
		include __DIR__ . "/config_{$env}.php";
	}

	return $container;
}

/**
 * Init function shortcut
 */
function financer_review_init() {
	financer_review()->init();
}

/**
 * Activate function shortcut
 */
function financer_review_activate( $network_wide ) {
	register_uninstall_hook( __FILE__, 'financer_review_uninstall' );
	financer_review()->init();
	financer_review()->activate( $network_wide );
}

/**
 * Deactivate function shortcut
 */
function financer_review_deactivate( $network_wide ) {
	financer_review()->deactivate( $network_wide );
}

/**
* Uninstall function shortcut
*/
function financer_review_uninstall() {
	financer_review()->uninstall();
}

/**
 * Error for older php
 */
function financer_review_php_upgrade_notice() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s requires a minimum PHP version of 5.4.0. Your current version is: %s. Please contact your host to upgrade.</p>
	</div>', $info['Name'], PHP_VERSION
		)
	);
}

/**
 * Error if vendors autoload is missing
 */
function financer_review_php_vendor_missing() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s is corrupted it seems, please re-install the plugin.</p>
	</div>', $info['Name']
		)
	);
}

/*
 * We want to use a fairly modern php version, feel free to increase the minimum requirement
 */
if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
	add_action( 'admin_notices', 'financer_review_php_upgrade_notice' );
} else {
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		include_once __DIR__ . '/vendor/autoload.php';
		add_action( 'plugins_loaded', 'financer_review_init', 11 );
		register_activation_hook( __FILE__, 'financer_review_activate' );
		register_deactivation_hook( __FILE__, 'financer_review_deactivate' );
	} else {
		add_action( 'admin_notices', 'financer_review_php_vendor_missing' );
	}
}
