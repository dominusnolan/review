<?php
namespace Financer_Review\Shortcode;
use Financer_Review\Core\Shortcodes;

/**
 * Class Shortcode\Top_Company
 *
 * @package Financer_Review\Shortcodes
 */
class Top_Company extends Shortcodes{

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
		if ( empty( $atts['type'] ) ) {
			$atts['type'] = 'overall_rating';
		}
		if ( empty( $atts['ctype'] ) ) {
			$atts['ctype'] = 'all';
		}
		if ( empty( $atts['nonaff'] ) ) {
			$atts['nonaff'] = 0;
		}

		$query = self::topCompany($atts['number'], $atts['type'], $atts['ctype'], $atts['nonaff'] );
		
		if ( !empty($query) && count( $query ) > 0 ) {
			echo <<<HTML
				<div style="counter-reset: section;">
HTML;
				foreach ( $query as $pos => $result ) {
					$pod = pods( 'company_single', $result->ID );
					if ( $pod->field( 'url' ) != '' ) {
						$url = get_permalink($result->ID);
						$logo = $pod->display( 'logo' );
						$shortcode = do_shortcode( '[rating_stars cid="'.$result->ID.'" type="overall_rating" size="22" stars="1"]' );
						$based_text_open = __( 'based on', 'fr' ) . '&nbsp;';
						$total_rating = do_shortcode( '[total_rating id='.$result->ID.']' );
						$based_text_close = '&nbsp;' . __( 'reviews', 'fr' );
						echo <<<HTML
							<div class="lender-box">
								<a href="{$url}"> 
									<div class="top_lender_img"><img src="{$logo}"/></div>
									{$shortcode}
									<p class="based-on">({$based_text_open}{$total_rating}{$based_text_close})</p>
								</a>
							</div>
HTML;
					}
				}
			echo '</div>';
		}


		return ob_get_clean();
	}

	/**
	 *
	 */
	public static function topCompany($number = 3, $type = 'overall_rating', $ctype = 'all', $nonaff = 0) {
		
		if ( $nonaff == 0 ) {
			$aff = [
				  'key'     => 'd.ej_partner',
				  'value'   => '0',
				];
		} else {
			$aff = [
				  'key'     => 'd.ej_partner',
				  'value'   => array('0', '1'),
				  'compare' => 'IN'
				];
		}
		
		if ($ctype == 'all') {
			$where         = [
				$aff,
				[
				  'key'     => 'overall_rating',
				  'compare' => '>',
				  'value'    => 0
				],
				[
				  'key'     => 'total_review',
				  'compare' => '>',
				  'value'    => 4
				]
			];
		} else {
			$where         = [
				$aff,
				[
				  'key'     => 'overall_rating',
				  'compare' => '>',
				  'value'    => 0
				],
				[ 
				  'key' => 'company_type.slug',
				  'value' => $ctype,
				  'compare' => '='
				],
				[
				  'key'     => 'total_review',
				  'compare' => '>',
				  'value'    => 4
				]
			];
		}

	      $top_company = pods(
	        'company_single', [
	          'limit'   => (int) $number,
	          'select'  => [
	            't.ID as ID',
	            't.post_title as title',
	            't.post_name as name',
	            't.post_status',
	            $type
	          ],
	          'where'   => $where,
	          'orderby' => $type . ' DESC',
	        ]
	      );

	      if( !empty($top_company->data()) ){
	      	return $top_company->data();
	      }else{
	      	return null;
	      }
	}

}
