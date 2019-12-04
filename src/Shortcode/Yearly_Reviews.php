<?php
namespace Financer_Review\Shortcode;


use Financer_Review\Core\Shortcodes;
use Financer_Review\Display\ReviewDisplay;

/**
 * Class Shortcode\Yearly_Reviews
 *
 * @package Financer_Review\Shortcodes
 */
class Yearly_Reviews extends Shortcodes{

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
		if ( empty( $atts['number'] ) ) {
			$atts['number'] = 3;
		}

		$pod = pods(
	          'reviews', [
	              'limit'     => $atts['number'] + 1,
	              'select'  => [
	                  't.ID as ID',
	                  't.post_title as title',
	                  't.post_date_gmt as date',
	                  'company.ID as pid',
	                  'product_type.name as type',
	                  'overall_rating',
	                  'customer_support',
	                  'interest_loan_costs',
	                  'flexibility_loan_terms',
	                  'website_functionality',
	                  'likes',
	                  'dislikes',
	                  'vote_like',
	                  'vote_dislike',
					  'old_id',
	                  'author_name'
	              ],
	              'where'  => [
	                [
	                  'key'   => 'post_status',
	                  'value' => 'publish',
	                ],
	                [
						'key'     => 't.post_date_gmt',
						'value'   => [ ( new \DateTime( 'first day of january ' . $atts['year'] ) )->format( 'Y-m-d' ), ( new \DateTime( '-1 days ago'   ) )->format( 'Y-m-d H:i:s' ) ],
						'compare' => 'BETWEEN'
					],
					[
	                  'key'   => 'author_name',
	                  'compare' => 'EXISTS',
	                ],
	              ],
	              'orderby' => 'date DESC',
	          ]
	      );

		if( !empty($pod) && !empty($pod->data()) ){
			ReviewDisplay::render( $pod );
		}

		return ob_get_clean();
	}

}