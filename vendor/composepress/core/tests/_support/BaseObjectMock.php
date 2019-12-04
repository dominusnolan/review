<?php

use ComposePress\Core\Abstracts\BaseObject_0_7_4_0;

/**
 * Class BaseObjectMock
 *
 * @package ComposePress\Core\Abstracts
 * @property string $test
 * @property string $is_test
 */
class BaseObjectMock extends BaseObject_0_7_4_0 {
	private $test = 'test';
	private $is_test = 'test';
	private $get_test = 'test';

	/**
	 * @return mixed
	 */
	public function get_test() {
		return $this->test;
	}

	/**
	 * @param string $test
	 */
	public function set_test( $test ) {
		$this->test = $test;
	}

	public function init() {

	}

	/**
	 * @return string
	 */
	public function is_is_test() {
		return $this->is_test;
	}

	/**
	 * @return string
	 */
	public function get_get_test() {
		return $this->get_test;
	}

}
