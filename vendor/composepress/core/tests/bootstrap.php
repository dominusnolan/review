<?php

namespace Test_Plugin {

	use ComposePress\Dice\Dice;

	function container( $env = 'prod' ) {
		static $container;
		if ( empty( $container ) ) {
			$container = new Dice();
			include __DIR__ . "/config_{$env}.php";
		}

		return $container;
	}

}

namespace {

	use ComposePress\Dice\Dice;

	if ( ! class_exists( 'PHPUnit_Framework_Assert' ) ) {
		class_alias( 'PHPUnit\Framework\Assert', 'PHPUnit_Framework_Assert' );
	}

	if ( ! class_exists( 'PHPUnit_Framework_TestCase' ) ) {
		class_alias( 'PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase' );
	}

	if ( ! class_exists( 'PHPUnit_Framework_Exception' ) ) {
		class_alias( 'PHPUnit\Framework\Exception', 'PHPUnit_Framework_Exception' );
	}

	/**
	 * @param string $env
	 *
	 * @return \ComposePress\Dice\Dice
	 */
	function test_plugin_container( $env = 'prod' ) {
		static $container;
		if ( empty( $container ) ) {
			$container = new Dice();
			include __DIR__ . "/config_{$env}.php";
		}

		return $container;
	}


	/**
	 * @return bool
	 */
	function test1_container() {
		return false;
	}

	/**
	 * @return mixed
	 */
	function test_plugin() {
		return test_plugin_container()->create( '\PluginMock' );
	}

	global $wp_filter;

	$wp_filter['plugins_url'][10]['modify_plugins_url']     = [
		'function'      => 'modify_plugins_url',
		'accepted_args' => 1,
	];
	$wp_filter['pre_option_siteurl'][10]['modify_site_url'] = [ 'function' => 'modify_site_url', 'accepted_args' => 1 ];
	$wp_filter['pre_option_home'][10]['modify_site_url']    = [ 'function' => 'modify_site_url', 'accepted_args' => 0 ];

	/**
	 * @param $url
	 *
	 * @return mixed
	 */
	function modify_plugins_url( $url ) {
		return str_replace( __DIR__, '/test-plugin', $url );
	}

	function modify_site_url() {
		return 'http://example.org';
	}
}
