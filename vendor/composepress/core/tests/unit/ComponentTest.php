<?php

class ComponentTest extends \Codeception\TestCase\WPTestCase {

	public function test_child_component_null() {
		if ( method_exists( $this, 'expectException' ) ) {
			$this->expectException( '\Exception' );
		} else {
			$this->setExpectedException( '\Exception' );
		}
		$component = new ComponentMock();
		$this->assertNull( $component->child->parent );
		$this->assertInstanceOf( '\ComposePress\Core\Abstracts\Component_0_7_4_0', $component->child->plugin );
	}

	/**
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ReflectionException
	 */
	public function test_child_component() {
		$component = new ComponentMock();
		$component->init();
		$this->assertInstanceOf( '\ComposePress\Core\Abstracts\Component_0_7_4_0', $component->child->parent );
		$component->child->parent = test_plugin();
		$this->assertInstanceOf( '\ComposePress\Core\Abstracts\Component_0_7_4_0', $component->child->plugin );
	}

	public function test_child_component_no_getter() {
		$component = new ComponentMock();
		$component->init();
		$this->assertFalse( $component->child2 );
	}

	/**
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ReflectionException
	 */
	public function test_child_component_fail() {
		$component = new ComponentFailMock();
		$this->expectException( '\ComposePress\Core\Exception\ComponentInitFailure' );
		$component->init();
	}

	public function test_child_component_set_parent() {
		$component_child = new ComponentChildMock();
		$component       = new ComponentMock();
		$component_child->set_parent( $component );

		$this->assertEquals( $component, $component_child->get_parent() );
	}

	/**
	 * @throws \ReflectionException
	 */
	public function test_is_component_false() {
		$component = new ComponentMock();
		$this->assertFalse( $component->is_component( false ) );
	}

	/**
	 * @throws \ReflectionException
	 */
	public function test_is_component_null() {
		$component = new ComponentMock();
		$this->assertFalse( $component->is_component( null ) );
	}

	/**
	 * @throws \ReflectionException
	 */
	public function test_is_component_number() {
		$component = new ComponentMock();
		$this->assertFalse( $component->is_component( 1 ) );
	}

	/**
	 * @throws \ReflectionException
	 */
	public function test_is_component_stdclass() {
		$component = new ComponentMock();
		$this->assertFalse( $component->is_component( new stdClass() ) );
	}

	public function test_is_component_cached() {
		$component = new ComponentMock();
		$this->assertTrue( $component->is_component( $component ) );
		$this->assertTrue( $component->is_component( $component ) );
	}

	/**
	 * @throws \Exception
	 */
	public function test_load() {
		$component = new ComponentMock();
		$component->set_parent( test_plugin() );
		$this->assertTrue( $component->load( 'lazy_component' ) );
	}

	/**
	 * @throws \Exception
	 */
	public function test_load_invalid_property() {
		$component = new ComponentMock();
		$this->assertFalse( $component->load( 'fail' ) );
	}

	/**
	 * @throws \Exception
	 */
	public function test_load_invalid_type() {
		$component = new ComponentMock();
		$this->assertFalse( $component->load( 'lazy_component_bad' ) );
	}

	public function test_load_many() {
		$component = new ComponentMock();
		$component->set_parent( test_plugin() );
		$this->assertTrue( $component->load( 'lazy_component_many' ) );
	}

	public function test_load_many_bad() {
		$component = new ComponentMock();
		$component->set_parent( test_plugin() );
		$this->assertFalse( $component->load( 'lazy_component_many_bad' ) );
	}

	public function test_load_many_bad_class() {
		$this->expectException( '\ComposePress\Core\Exception\ComponentMissing' );

		$component = new ComponentMock();
		$component->set_parent( test_plugin() );
		$component->load( 'lazy_component_many_bad_class' );
	}

	/**
	 * @throws \Exception
	 */
	public function test_load_invalid_class() {
		$component = new ComponentMock();
		$this->expectException( '\ComposePress\Core\Exception\ComponentMissing' );
		$component->load( 'lazy_component_bad_class' );
	}

	public function test_is_loaded() {
		$component = new ComponentMock();
		$component->set_parent( test_plugin() );
		$component->load( 'lazy_component' );
		$this->assertTrue( $component->is_loaded( 'lazy_component' ) );
	}

	public function test_is_loaded_invalid_property() {
		$component = new ComponentMock();
		$this->assertFalse( $component->is_loaded( 'fail' ) );
	}

	public function test_is_loaded_stdClass() {
		$component = new ComponentMock();
		$this->assertFalse( $component->is_loaded( 'lazy_component_bad_stdclass' ) );
	}

	public function test_is_loaded_invalid_type() {
		$component = new ComponentMock();
		$this->assertFalse( $component->is_loaded( 'lazy_component_bad' ) );
	}

	public function test_is_loaded_invalid_type_many() {
		$component = new ComponentMock();
		$this->assertFalse( $component->is_loaded( 'lazy_component_many_bad' ) );
		$this->assertFalse( $component->is_loaded( 'lazy_component_many_bad_empty' ) );
	}

	public function test_get_plugin() {
		$component = new ComponentMock();
		$component->set_parent( test_plugin() );
		$this->assertEquals( test_plugin(), $component->plugin );
	}

	public function test_get_plugin_not_set() {
		$component = new ComponentMock();
		$this->expectException( '\ComposePress\Core\Exception\Plugin' );
		$component->plugin;
	}

	public function test_get_plugin_tree_broken() {
		$component_child = new ComponentChildMock();
		$component       = new ComponentMock();
		$component_child->set_parent( $component );
		$this->expectException( '\ComposePress\Core\Exception\Plugin' );
		$component_child->plugin;
	}

	public function test_get_closest() {
		$component_child = new ComponentChildMock();
		$component       = new ComponentMock();
		$component_child->set_parent( $component );
		$component->set_parent( test_plugin() );
		$this->assertInstanceOf( get_class( test_plugin() ), $component_child->get_closest( '\PluginMock' ) );
	}

	public function test_get_closest_fail() {
		$component_child = new ComponentChildMock();
		$component       = new ComponentMock();
		$component_child->set_parent( $component );
		$component->set_parent( test_plugin() );
		$this->assertFalse( $component_child->get_closest( '\blah' ) );
	}

	public function test_create_commponent() {
		$component         = new ComponentMock();
		$component->parent = test_plugin();
		$component->init();

		$this->assertInstanceOf( '\ComposePress\Core\Abstracts\Component_0_7_4_0', $component->create_component( '\ComponentChildMock', 'test' ) );
	}
	public function test_create_commponent_no_args() {
		$component         = new ComponentMock();
		$component->parent = test_plugin();
		$component->init();

		$this->assertInstanceOf( '\ComposePress\Core\Abstracts\Component_0_7_4_0', $component->create_component( '\ComponentChildMock') );
	}

	public function test_create_commponent_var_args() {
		$component         = new ComponentMock();
		$component->parent = test_plugin();
		$component->init();

		$this->assertInstanceOf( '\ComposePress\Core\Abstracts\Component_0_7_4_0', $component->create_component( '\ComponentChildMock', 'test', 'a', 'b', 'c', 1, 2, 3 ) );
	}
}
