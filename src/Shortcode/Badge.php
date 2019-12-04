<?php
namespace Financer_Review\Shortcode;


use Financer_Review\Core\Shortcodes;

/**
 * Class Shortcode\Badge
 *
 * @package Financer_Review\Shortcodes
 */
class Badge extends Shortcodes{

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
		if ( empty( $atts['cid'] ) ) {
			$atts['cid'] = 4647;
		}
		if ( empty( $atts['large'] ) ) {
			$atts['large'] = 0;
		}
		if ( empty( $atts['type'] ) ) {
			$atts['type'] = 'overall_rating';
		}

		if ($atts['type'] == 'overall_rating') {
			$catname = __( 'Overall Rating', 'fr' );
		} elseif ($atts['type'] === 'customer_support') {
			$catname = __( 'Support & Service', 'fr' );
		} elseif ($atts['type'] === 'interest_loan_costs') {
			$catname = __( 'Interest & Costs', 'fr' );
		} elseif ($atts['type'] === 'flexibility_loan_terms') {
			$catname = __( 'Flexibility & Terms', 'fr' );
		} elseif ($atts['type'] === 'website_functionality') {
			$catname = __( 'Website & Functionality', 'fr' );
		} else {
			$catname = __( 'Overall Rating', 'fr' );
		}

	    $rating = 0;
		$siteurl = get_bloginfo('url');
		$pod = pods( 'company_single', $atts['cid'] );
		$rating = $pod->display( $atts['type'] );
        $rating = str_replace(',', '.', $rating);

		if ( $rating == '0' || $rating == '1' || $rating == '2' || $rating == '3' || $rating == '4' || $rating =='5' ) {
			$round_rating = number_format($rating, 1, '.', '');
		} else {
			$round_rating = round($rating, 2);
		}


		if ($atts['large'] == 0) {
	      $output = <<<HTML
	         <div class="badge_wrap">
	              <div style="background-color:#2098ce;width:120px;height: 100%;display: flex;flex-direction: row;justify-content: center;align-items: center;align-content: center;">
	                  <div style="font-size:34px;color:#ffffff;"> 
	                      {$round_rating}
	                  </div>
	              </div>
	              <div style="background-color:#ffffff;width:200px;height: 100%;display: flex;flex-direction: row;justify-content: center;align-items: center;align-content: center;">
	                  <div style="text-align: center;">
	                      <a href="{$siteurl}" target="_blank"><img src="{$siteurl}/wp-content/uploads/sites/2/logo-financer.svg" style="width:130px;height:auto;margin:0;max-width:130px;"></a>
	                      <div style="text-align:center;font-size:10px;color:#999;font-weight:500;text-transform: uppercase;">
	                          {$catname}
	                      </div>
	                      <div style="text-align:center;">
	                           <span class="rating rating0" data-default-rating="{$round_rating}" title="Rating {$round_rating}/5" disabled></span>
	                      </div>
	                  </div>
	              </div>
	          </div>
HTML;
		} elseif ($atts['large'] == 1) {
			$tooltiptxt = __('<strong>What does this number mean?</strong><p>This is is the overall rating score of this company based on customer reviews. </p><p>Our scoring model consists of 4 other variables that users can grade separately, which can be seen under Detailed Rating.</p>','fr');
			$corner_img = get_template_directory_uri() . '/images/financer-icon-50x50.png';
	      $output = <<<HTML
	         <div class="badge_wrap_large">
	              <div class="badge_main_container">
						<div class="corner_logo">
							<img src="{$corner_img}" />							
						</div>
	                  <div class="rating_container"> 
	                      {$round_rating}
	                  </div>
					  <div class="infotip"><span class="dashicons dashicons-info"></span><span class="infotiptext">{$tooltiptxt}</span></div>
	              </div>
	              <div class="badge_rating">
	                  <div class="badge_rating_info">
	                      <a href="{$siteurl}" target="_blank"><img src="{$siteurl}/wp-content/uploads/sites/2/logo-financer.svg" ></a>
	                      <div class="badge_product">
	                          {$catname}
	                      </div>
	                      <div class="badge_stars">
	                           <span class="rating rating0 largerating" data-default-rating="{$round_rating}" title="Rating {$round_rating}/5" disabled></span>
	                      </div>
	                  </div>
	              </div>
	          </div>
HTML;
		}

	      echo  $output;
		return ob_get_clean();
	}

}
