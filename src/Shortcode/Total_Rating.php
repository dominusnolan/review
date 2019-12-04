<?php
namespace Financer_Review\Shortcode;


use Financer_Review\Core\Shortcodes;

/**
 * Class Shortcode\Total_Rating
 *
 * @package Financer_Review\Shortcodes
 */
class Total_Rating extends Shortcodes{

	private static $_instance = 0;

	/**
	 * @param array       $atts
	 *
	 * @param null|string $content
	 *
	 * @param string      $tag
	 * @param bool        $ajax
	 *
	 * @return mixed|string
	 */
	static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		
		ob_start();
		if ( empty( $atts['id'] ) ) {
			$atts['id'] = 4647;
		}


		$pod = pods(
	          'reviews', [
	            'select'  => [
	                't.ID as ID',
	            ],
	            'limit'     => -1,
	            'where'  => [
	              [
	                'key'   => 'post_status',
	                'value' => 'publish',
	              ],
	              [
	                'key'   => 'company.ID',
	                'value' => $atts['id']
	              ],
	              [
	                  'key'   => 'author_name',
	                  'compare' => 'EXISTS',
	              ],

	            ],
	          ]
		);
		$output = 0;


		if( !empty($pod) && !empty($pod->data()) ){
			$output = count($pod->data());
			echo $output;
		}
		return ob_get_clean();
	}

}