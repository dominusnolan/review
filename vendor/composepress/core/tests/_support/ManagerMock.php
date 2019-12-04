<?php


use ComposePress\Core\Abstracts\Manager_0_7_4_0;

class ManagerMock extends Manager_0_7_4_0 {
	protected $modules = [
		'ComponentMock',
		'\ComponentChildMock',
	];
}
