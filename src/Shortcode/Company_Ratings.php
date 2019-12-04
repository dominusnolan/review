<?php
namespace Financer_Review\Shortcode;


use Financer_Review\Core\Shortcodes;

/**
 * Class Shortcode\Company_Ratings
 *
 * @package Financer_Review\Shortcodes
 */
class Company_Ratings extends Shortcodes{

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
		if (  is_null( $atts['id'] ) ) {
			$atts['id'] = 4647;
		}

		if (  is_null( $atts['first'] ) ) {
			$atts['first'] = 1;
		}
		echo '<style>
				.list_comp_ratings .rating0 {display:block !important}
			</style>';
		$counter = do_shortcode( '[total_rating id='.$atts['id'].']' );	
		if ($atts['first'] == 1) {
			$first1 = '<div class="overall_rating_title">'. __('From ','fr') . $counter . __(' Reviews','fr').'</div>';
			$first2 = do_shortcode( '[rating_stars cid="'.$atts['id'].'" type="overall_rating" stars="1" class="largerating"]' );
		} elseif ($atts['first'] == 0) {
			$first1 = $first2 = '';
		}
		$output = '
					<div class="list_comp_ratings">
						<div class="list_comp_ratings_left">
							'.$first1.'			
							<div class="list_rating_title">'. __('Interest & Costs','fr').'</div>
							<div class="list_rating_title">'. __('Flexibility & Terms','fr').'</div>
							<div class="list_rating_title">'. __('Website & Functionality','fr').'</div>
							<div class="list_rating_title">'. __('Support & Service','fr').'</div>
						</div>
						<div class="list_comp_ratings_right">
							'.$first2.'
							'.do_shortcode( '[rating_stars cid="'.$atts['id'].'" type="interest_loan_costs" stars="1"]' ).'
							'.do_shortcode( '[rating_stars cid="'.$atts['id'].'" type="flexibility_loan_terms" stars="1"]' ).'
							'.do_shortcode( '[rating_stars cid="'.$atts['id'].'" type="website_functionality" stars="1"]' ).'
							'.do_shortcode( '[rating_stars cid="'.$atts['id'].'" type="customer_support" stars="1"]' ).'
						</div>
					</div>
		';
	  	echo $output;


		return ob_get_clean();
	}

}