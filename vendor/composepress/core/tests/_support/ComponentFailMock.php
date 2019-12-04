<?php


use ComposePress\Core\Abstracts\Component_0_7_4_0;

class ComponentFailMock extends Component_0_7_4_0 {

	private $child;
	private $child2;

	public function __construct() {
		$this->child  = new ComponentChildFailMock();
		$this->child2 = new ComponentChildFailMock();
	}

	/**
	 * @return \ComponentChildMock
	 */
	public function get_child() {
		return $this->child;
	}
}
