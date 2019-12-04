<?php

class PluginTest extends \Codeception\TestCase\WPTestCase {

	public function test_get_slug() {
		$this->assertEquals( 'test-plugin', test_plugin()->get_slug() );
	}

	public function test_get_safe_slug() {
		$this->assertEquals( 'test_plugin', test_plugin()->get_safe_slug() );
	}

	public function test_get_version() {
		$this->assertEquals( '0.1.0', test_plugin()->get_version() );
	}

	public function test_get_plugin_file() {
		$this->assertTrue( is_string( test_plugin()->get_plugin_file() ) );
	}

	public function test_get_container() {
		$this->assertInstanceOf( '\ComposePress\Dice\Dice', test_plugin()->get_container() );
	}

	public function test_get_dependencies_exist() {
		$this->assertTrue( test_plugin()->get_dependencies_exist() );
	}

	public function test_init() {
		$this->assertTrue( test_plugin()->init() );
	}

	public function test_get_asset_url() {
		$this->assertEquals( 'http://example.org/wp-content/plugins/test-plugin/test.js', test_plugin()->get_asset_url( 'test.js' ) );
	}

	public function test_get_asset_url_file() {
		$this->assertEquals( 'http://example.org/wp-content/plugins/test-plugin/test-plugin.php', test_plugin()->get_asset_url( realpath( __DIR__ . '/../test-plugin.php' ) ) );
	}

	/**
	 * @throws \Exception
	 */
	public function test_init_fail() {
		$this->assertFalse( $this->make( 'PluginMock', [ 'get_dependencies_exist' => false ] )->init() );
	}

	public function test_get_plugin_info() {
		$this->assertTrue( is_array( test_plugin()->get_plugin_info() ) );
	}

	public function test_get_plugin_info_name() {
		$this->assertEquals( 'Test plugin', test_plugin()->get_plugin_info( 'Name' ) );
	}

	/**
	 * @throws \ComposePress\Core\Exception\ContainerInvalid
	 * @throws \ComposePress\Core\Exception\ContainerNotExists
	 */
	public function test_container_bad_slug() {
		$this->expectException( '\ComposePress\Core\Exception\ContainerNotExists' );
		new PluginMockBadSlug();
	}

	/**
	 * @throws \ComposePress\Core\Exception\ContainerInvalid
	 * @throws \ComposePress\Core\Exception\ContainerNotExists
	 */
	public function test_container_bad_container() {
		$this->expectException( '\ComposePress\Core\Exception\ContainerInvalid' );
		new PluginMockBadContainer();
	}

	/**
	 * @throws \ComposePress\Core\Exception\ContainerInvalid
	 * @throws \ComposePress\Core\Exception\ContainerNotExists
	 */
	public function test_container_namespace() {
		new PluginMockNamepaceContainer();
	}

	/**
	 * @throws \ComposePress\Core\Exception\ContainerInvalid
	 * @throws \ComposePress\Core\Exception\ContainerNotExists
	 */
	public function test_container_namespace_bad_beginning() {
		$this->expectException( '\ComposePress\Core\Exception\ContainerNotExists' );
		new PluginMockBadBeginningNamepaceContainer();
	}

	/**
	 * @throws \ComposePress\Core\Exception\ContainerInvalid
	 * @throws \ComposePress\Core\Exception\ContainerNotExists
	 */
	public function test_container_namespace_bad_end() {
		$this->expectException( '\ComposePress\Core\Exception\ContainerNotExists' );
		new PluginMockBadEndNamepaceContainer();
	}

	/**
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\ContainerInvalid
	 * @throws \ComposePress\Core\Exception\ContainerNotExists
	 * @throws \ReflectionException
	 */
	public function test_setup_fail() {

		$this->expectException( '\ComposePress\Core\Exception\ComponentInitFailure' );

		$this->assertFalse( ( new PluginMockBadSetup() )->init() );
	}
}
