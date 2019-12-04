<?php

class ManagerTest extends \Codeception\TestCase\WPTestCase {
	public function test_init() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$this->assertTrue( $manager->init() );
	}

	public function test_init_already_ran() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$this->assertTrue( $manager->init() );
		$this->assertFalse( $manager->init() );
	}

	public function test_get_modules() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$modules = $manager->get_modules();

		$this->assertTrue( is_array( $modules ) );
		$this->assertNotEmpty( $modules );
	}

	public function test_get_module() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertInstanceOf( 'ComponentMock', $manager->get_module( 'ComponentMock' ) );
	}

	public function test_get_module_null() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertFalse( $manager->get_module( null ) );
	}

	public function test_get_module_with_slash() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertInstanceOf( 'ComponentChildMock', $manager->get_module( 'ComponentChildMock' ) );
	}

	public function test_get_module_bad() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertFalse( $manager->get_module( 'fail' ) );
	}

	public function test_get_module_isset() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertTrue( isset( $manager->ComponentChildMock ) );
	}

	public function test_get_module_isset_bad() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertFalse( isset( $manager->fail ) );
	}

	public function test_get_module_get() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertInstanceOf( 'ComponentChildMock', $manager->ComponentChildMock );
	}

	public function test_get_module_get_bad() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertFalse( $manager->fail );
	}

	public function test_get_module_first_part_lowercase() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertInstanceOf( 'ComponentChildMock', $manager->componentChildMock );
	}

	public function test_get_module_snake_case() {
		$manager = new ManagerMock();
		$manager->set_parent( test_plugin() );
		$manager->init();
		$this->assertInstanceOf( 'ComponentChildMock', $manager->component_child_mock );
		// Cache
		$this->assertInstanceOf( 'ComponentChildMock', $manager->component_child_mock );
	}

	public function test_manager_fail() {
		$manager = new ManagerMockBad( new ComponentChildFailMock() );
		$manager->set_parent( test_plugin() );

		$this->expectException( '\ComposePress\Core\Exception\ComponentInitFailure' );
		$manager->init();
	}
}
