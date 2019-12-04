<?php


use ComposePress\Core\Abstracts\Component_0_7_4_0;

class ComponentMock extends Component_0_7_4_0 {

	protected $lazy_component = 'ComponentChildMock';
	protected $lazy_component_bad;
	protected $lazy_component_bad_class = 'missing';
	protected $lazy_component_bad_stdclass;
	protected $lazy_component_many = [ 'ComponentChildMock', 'ComponentChildMock' ];
	protected $lazy_component_many_bad = [ 'ComponentChildMock', '\stdClass', false ];
	protected $lazy_component_many_bad_empty = [];
	protected $lazy_component_many_bad_class = [ 'ComponentChildMock', 'missing' ];
	private $child;
	private $child2;

	public function __construct() {
		$this->lazy_component_bad_stdclass = new stdClass();
		$this->child                       = new ComponentChildMock();
		$this->child2                      = new ComponentChildMock();
	}


	/**
	 * @return \ComponentChildMock
	 */
	public function get_child() {
		return $this->child;
	}

	public function is_component( $component, $use_cache = true ) {
		return parent::is_component( $component, $use_cache );
	}

	public function load( $component, $args = [] ) {
		return parent::load( $component, $args );
	}

	public function is_loaded( $component ) {
		return parent::is_loaded( $component );
	}
}
