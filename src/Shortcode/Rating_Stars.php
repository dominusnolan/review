<?php
namespace Financer_Review\Shortcode;


use Financer_Review\Core\Shortcodes;

/**
 * Class Shortcode\Rating_Stars
 *
 * @package Financer_Review\Shortcodes
 */
class Rating_Stars extends Shortcodes{

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
            $atts['cid'] = 1;
        }
        if ( empty( $atts['type'] ) ) {
            $atts['type'] = 'overall_rating';
        }
        if ( empty( $atts['size'] ) ) {
            $atts['size'] = 1;
        }
        if ( empty( $atts['class'] ) ) {
            $atts['class'] = '';
        }

        $rating = 0;
        $txt1 = __('Rating','fr');

        $pod = pods( 'company_single', $atts['cid'] );
        $rating = $pod->display( $atts['type'] );
        $rating = str_replace(',', '.', $rating);

        if ( !empty( $pod ) ){
            $output = '';
            $reviewslink = get_the_permalink( $atts['cid'] )  . '#read-reviews';

            if ($atts['stars'] == 0) {
                $output .= round($rating, 2);
            } else {
                if ($atts['size'] == 1) {
                    $output .= '<a href="'.$reviewslink.'"><span class="rating rating0 '.$atts['class'].'" data-default-rating="'. round($rating, 2) .'" title="'.$txt1.' '. round($rating, 2) .'/5" disabled></span></a>';
                } else {
                    $output .= '<a href="'.$reviewslink.'"><span class="rating rating0 '.$atts['class'].'" data-default-rating="'. round($rating, 2) .'" title="'.$txt1.' '. round($rating, 2) .'/5" style="font-size: '. $atts['size'] .'px;" disabled></span></a>';
                }
            }

            echo $output;
        }


        return ob_get_clean();
    }

}
