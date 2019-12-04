<?php

namespace Financer_Review\Managers;


use Financer_Review\Core\Manager as ManagerBase;

/**
 * Class Manager
 */
class Review extends ManagerBase {
	const MODULE_NAMESPACE = '\Financer_Review\Review';
	/**
	 * @var array
	 */
	protected $modules = [ 'ReviewModule' ];
}
