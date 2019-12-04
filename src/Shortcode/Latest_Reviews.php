<?php
namespace Financer_Review\Shortcode;


use Financer_Review\Core\Shortcodes;
use Financer_Review\Display\ReviewDisplay;

/**
 * Class Shortcode\Latest_Reviews
 *
 * @package Financer_Review\Shortcodes
 */
class Latest_Reviews extends Shortcodes{

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
			$atts['number'] = 10;
		}
		
		if ( empty( $atts['ctype'] ) ) {
			$companies = pods( 'company_single', [ 'limit' => -1 ] );
			$carray = [];
			while ( $companies->fetch() ) {
				$carray[] = $companies->display( 'id' );
			} 
			$pod = pods(
				  'reviews', [
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
					  'limit'     => $atts['number'],
					  'where'  => [
						[
						  'key'   => 'post_status',
						  'value' => 'publish',
						],
						[
						  'key'   => 'company.ID',
						  'compare' => 'IN',
						  'value' => $carray,
						]
					  ],
					  'orderby' => 'date DESC',
				  ]
			  );
			
			$podoffset = $atts['number'];
			$podtotal = wp_count_posts( 'reviews' )->publish;		
			$title = __('Latest User Reviews','fr');
			
			if( !empty($pod) && !empty($pod->data()) ){
				ReviewDisplay::render( $pod, $podtotal, $podoffset, $title, $carray );
			}
			return ob_get_clean();
			
		} else {	
			$companies = pods( 'company_single', [ 'limit' => -1, 'where' => [ [ 'key' => 'company_type.slug', 'value' => $atts["ctype"], 'compare' => '=' ] ] ] );
			$carray = [];
			while ( $companies->fetch() ) {
				$carray[] = $companies->display( 'id' );
			} 
			$pod = pods(
				  'reviews', [
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
					  'limit'     => $atts['number'],
					  'where'  => [
						[
						  'key'   => 'post_status',
						  'value' => 'publish',
						],
						[
						  'key'   => 'company.ID',
						  'compare' => 'IN',
						  'value' => $carray,
						]
					  ],
					  'orderby' => 'date DESC',
				  ]
			  );
			
			$podoffset = $atts['number'];
			$podtotal = $pod->total_found();
			
			if ($podtotal <= $podoffset) { 
				$podoffset = $podtotal;
			}
			
			if ($atts["ctype"] == 'bank') { 
				$title = __('Latest Bank Reviews','fr');
			} else if ($atts["ctype"] == 'general_company') { 
				$title = __('Latest General Reviews','fr');
			} else if ($atts["ctype"] == 'loan_company') { 
				$title = __('Latest Loan Reviews','fr');
			} else {
				$title = __('Latest User Reviews','fr');
			}

			if( !empty($pod) && !empty($pod->data()) ){
				ReviewDisplay::render( $pod, $podtotal, $podoffset, $title, $carray );
			}
			return ob_get_clean();
			
			
			
		}
	}

}