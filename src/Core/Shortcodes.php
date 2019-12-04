<?php
namespace Financer_Review\Core;


/**
 * Class Shortcodes
 * @package Financer_Review\Core
 */
abstract class Shortcodes {

	protected static $id;

	/**
	 *
	 */
	public static function register() {
		$class      = get_called_class();
		$_id        = strtolower( ( new \ReflectionClass( get_called_class() ) )->getShortName() );
		$class::$id = &$_id;
		add_shortcode(
			$_id, [
				get_called_class(),
				'render',
			]
		);
	}

	/**
	 * @param array|string $atts
	 *
	 * @param string       $content
	 * @param string       $tag
	 * @param bool         $ajax
	 *
	 * @return string
	 */
	abstract static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string;
}
