<?php

class ManagerMockBad extends ManagerMock {
	protected $child;

	public function __construct( ComponentChildFailMock $child ) {
		$this->child = $child;
	}

	/**
	 * @return \ComponentChildFailMock
	 */
	public function get_child() {
		return $this->child;
	}
}
