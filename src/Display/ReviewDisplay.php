<?php
namespace Financer_Review\Display;


/**
 * Class Display
 * @package Financer_Review\Display
 */
class ReviewDisplay  {
   /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public static function render(\Pods $pod, $podtotal, $podoffset, $title, $carray){
      $data = $pod->data();
	  echo '<div class="boxwrap aboutfeature"><div class="wrap"><h2>'. $title .'</h2>';
	  echo '<div class="review-counter">' . __('Displaying','fr') . ' <span class="counter-offset">' . $podoffset . '</span> '. __('out of','fr') .' ' . $podtotal . ' ' . __('Reviews','fr') . '</div></div></div>';

      if (!empty($data))
      {
		$cycle = 5;
        echo '<section class="reviewHolder"><div class="reviews"><ol class="commentlist" style="display:block !important">';
        foreach($data as $i => $i_value) {
			include('LatestReviewList.php');
        } // End of review loop
        $pod->fetch();
        echo '</ol>';
		echo '<div class="review-counter center">' . __('Displaying','fr') . ' <span class="counter-offset">' . $podoffset . '</span> '. __('out of','fr') .' ' . $podtotal . ' ' . __('Reviews','fr') . '</div>';
		//Load More Button
			if ($podtotal > $podoffset) {
				$jarray = json_encode($carray);
				$lmtxt = __('See More Reviews','fr');
				$nmrtxt = __('No more reviews. Why don\'t you write one?','fr');
				echo <<<HTML
				<div class="center" id="loadmorediv">
					<a class="button small load-more" href="#" id="load-more" data-txt="{$lmtxt}">{$lmtxt}</a>
					<span id="nomorereviews" style="display:none;">{$nmrtxt}</span>
				</div>
				<div class="loadmore_loader">Loading...</div>
				<script>
				(function ($) {
					$(function ($) {
					if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {
						var offset = {$podoffset};
						var limit = {$podoffset};
						var jarray = {$jarray};
						var total = {$podtotal};
						var cycle = 0;
						$('#load-more').on('click', function(e) {
							if (e.preventDefault) {
								e.preventDefault();
							} else {
								e.returnValue = false;
							}
							$('.loadmore_loader').show();
							var txt = $('#load-more').attr('data-txt');
							var ajax_url = frontend_ajax_object.ajaxurl;
							cycle++;
							data = {
								action: 'load_more_latest',
								offset: offset,
								limit: limit,
								cycle: cycle,
								jarray: jarray,
								total: total,
							};
							$.post( ajax_url, data, function(response) {
								if( response ) {
									$(response).appendTo('.commentlist');
									$('.counter-offset').html(offset);
									$('#load-more').html(txt);
									cc = cycle + 6;
									var ratings6 = document.getElementsByClassName('rating' + cc);
									for (var i = 0; i < ratings6.length; i++) {
										var r = new SimpleStarRating(ratings6[i]);
										ratings6[i].addEventListener('rate', function(e) {
											console.log('Rating: ' + e.detail);
										});
									}
									$('.loadmore_loader').hide();

								} else {
									$('#load-more').css('display', 'none');
									$('.loadmore_loader').hide();
									$('.counter-offset').html(total);
									$('#nomorereviews').css('display', 'inline-block');
								}
							});
							offset = offset + limit;
							
						});
						
					} else {
						setTimeout(init, 50);
					}
					});
				})(jQuery);
				</script>
HTML;
			} //end load more button
			echo '</div></section>';
      } // End of checking if pod data has value
    }

     /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public static function renderReply($id)
    {
        $reply_pod = pods(
          'reviews_reply', [
            'select'  => [
              't.ID as ID',
              't.post_title as title',
              'review.ID as pid',
              'comment',
              'author_name'

            ],
            'limit'     => -1,
            'where'  => [
            [
              'key'   => 'post_status',
              'value' => 'publish',
            ],
            [
              'key'   => 'review.ID',
              'value' => $id,
            ],

            ],
          ]
        );


    }

    

	public function renderModalDisplay()
    {

	$rating7 = '0';
	$cname = get_the_title();
	$introtxt =  __( 'Thank you for choosing', 'fr' );
	$introtxt .= '&nbsp;<span class="rate_comp_name">'.$cname.'</span>.&nbsp;';
	$introtxt .=  __( 'If you would like to review the company, this would be greatly beneficial to other customers.', 'fr' );



	global $reviewform;

	$reviewform .= '
		<div class="form-main-wrap">
			<form id="msform" name="review_form" class="form-horizontal event-form" action="" method="POST" enctype="multipart/form-data">
				<div id="modal-data-div" data-cid="" data-cname="" data-clogo=""></div>
				<div class="rate_start" id="rate_start">
				  <div class="modal_intro">
					<img src="'. $clogo .'">
					<p>' . $introtxt . '</p>
				  </div>
				  <div class="rate_form_wrap"> 
					<div class="rate-left"> 
					  <div class="rate-left-one">
						<h3>'. __('Please rate ','fr') .' <span class="rate_comp_name">'.$cname.'</span></h3>
					  </div>
					  <div class="rate-left-two">
						<div style="display: block"><span id="overall" class="rating rating7 largerating" data-default-rating="'. $rating7 .'"></span></div>
						<input type="hidden" name="rating7" id="rating7" value="'. $rating7 .'">
					  </div>   
					</div>
					<div class="rate-right">
					  <img src="' . get_bloginfo('url') . '/wp-content/uploads/sites/2/feedback.png" title="'. __('We need your feedback!','fr') .'">
					</div>		
				  </div>
				</div>
			</form>
		</div>
		';

	  echo $reviewform;
    return true;
    }


}
