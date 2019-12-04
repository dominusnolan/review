<?php


namespace Financer_Review;

use Financer_Review\Core\Plugin as PluginBase;
use Financer_Review\Managers\Review;

/**
 * Class Plugin
 *
 * @package Financer_Review
 */
class Plugin extends PluginBase {

	/**
	 * Plugin version
	 */
	const VERSION = '0.1.0';

	/**
	 * Plugin slug name
	 */
	const PLUGIN_SLUG = 'financer-review';

	/**
	 * Plugin namespace
	 */
	const PLUGIN_NAMESPACE = '\Financer_Review';

	/**
	 * @var ReviewComponent
	 */
	private $review_component;

	/**
	 * @var Review Module
	 */
	private $review_manager;


	/**
	 * Plugin constructor.
	 *
	 */
	public function __construct(ReviewComponent $review_component, Review $review_manager) {
		$this->review_component = $review_component;
		$this->review_manager   = $review_manager;
		parent::__construct();
	}

	/**
	 * Method to check if plugin has its dependencies. If not, it silently aborts
	 *
	 * @return bool
	 */
	protected function get_dependancies_exist() {
		return true;
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	protected function load_components() {
		// Conditionally lazy load components with $this->load()
		return true;
	}

	/**
	 * @return bool
	 */
	public function setup() {
		return true;
	}

	/**
	 * Plugin activation and upgrade
	 *
	 * @param $network_wide
	 *
	 * @return void
	 */
	public function activate( $network_wide ) {

	}

	/**
	 * Plugin de-activation
	 *
	 * @param $network_wide
	 *
	 * @return void
	 */
	public function deactivate( $network_wide ) {

	}

	/**
	 * Plugin uninstall
	 *
	 * @return void
	 */
	public function uninstall() {

	}


	/**
	 * @return ReviewComponent
	 */
	public function get_review_component() {
		return $this->review_component;
	}


	/**
	 * @return \Financer_Review\Managers\Review
	 */
	public function get_review_manager() {
		return $this->review_manager;
	}

}
