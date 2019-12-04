<?php

namespace Financer_Review\Review;


use Financer_Review\Core\Component;
use Financer_Review\Plugin;
use Financer_Review\Display\ReviewDisplay;
/**
 * Class ReviewModule
 *
 * @package Financer_Review\Review
 */
class ReviewModule extends Component {

	/*
	*
	*/
	public function render(){
		foreach ( [ 'wp_ajax_nopriv_register_user', 'wp_ajax_register_user' ] as $hook ) {
            add_action( $hook, function () {
                   // Verify nonce
				  if( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'fn_new_user' ) )
				    die( 'Ooops, something went wrong, please try again later.' );

					if(empty($_POST['user']) && !empty($_POST['mail'])){
				    $_POST['user'] = $_POST['mail'];}

				  // Post values
				    $username = $_POST['user'];
				    $password = $_POST['pass'];
				    $email    = $_POST['mail'];
				    $name     = $_POST['name'];

					$hash = substr(md5(time() . rand() . $email), 0, 16);

				    $userdata = array(
				        'user_login' => $username,
				        'user_pass'  => $password,
				        'user_email' => $email,
				        'first_name' => $name
				    );

				    $user_id = wp_insert_user( $userdata ) ;
					wp_update_user( array ('ID' => $user_id, 'role' => '') ) ;
					add_user_meta( $user_id, 'key_activation', $hash );

					if( !is_wp_error($user_id) ) {

					    $companyName = $_POST['company'];
						//save new user review as draft
						$title = wp_strip_all_tags($companyName) .' Review';
						if (isset($_POST['product'])) {
							$product = $_POST['product'];
							$product_id = get_term_by('slug', $product, 'review_type');
						}
                        $reviewType = $product_id->term_id;
                        $reviewTypeName = $product_id->name;
						$overall_rating = $_POST['rating1'];
						$customer_support = $_POST['rating2'];
						$interest_loan_costs = $_POST['rating3'];
						$flexibility_loan_terms = $_POST['rating4'];
						$website_functionality = $_POST['rating5'];
						$liked = strip_tags(trim($_POST['liked']));
						$disliked = strip_tags(trim($_POST['disliked']));
						$review = array(
							'post_title' => $title,
							'company' => $_POST['post_id'],
							'product_type' => $reviewType,
							'overall_rating' => $overall_rating,
							'customer_support' => $customer_support,
							'interest_loan_costs' => $interest_loan_costs,
							'flexibility_loan_terms' => $flexibility_loan_terms,
							'website_functionality' => $website_functionality,
							'likes' => $liked,
							'dislikes' => $disliked,
							'post_status' => $_POST['ps']
						);
						$new_review = pods( 'reviews' )->add( $review );
						wp_set_object_terms( $new_review, $reviewType, 'review_type' );
						$updateReview = array(
							'ID' => $new_review,
							'post_title'    => $new_review . ' - ' . $title,
							'post_author' => $user_id,
						);
						wp_update_post( $updateReview );

                        // send confirmation email
                        $footerinfo = pods( 'footer_settings' );
                        $fb_link = $footerinfo->row['facebook'] ? $footerinfo->row['facebook'] : 'https://www.facebook.com/financercom/';
                        $twitter_link = $footerinfo->row['twitter'] ? $footerinfo->row['twitter'] : 'https://twitter.com/financercom';
                        $home_link = get_bloginfo('url');

                        $posted_date = get_the_date(get_option('date_format'), $new_review);
                        $link = site_url( 'user_activation?key=' . $hash );
                        $to = $email;
                        $subject = __('Please confirm your registration at Financer.com','fr');

                        ob_start();
                        include(plugin_dir_path( __DIR__ ) . 'Display/template/email_valdation.php');
                        $body = ob_get_contents();
                        ob_end_clean();


                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        wp_mail( $to, $subject, $body, $headers );

						// Save author details to review PODS field
						$company_review = pods( 'reviews', $new_review );
						$company_review_data  = array(
							'author_email' => $user_id->user_email,
							'author_name' => $user_id->display_name
						);
						$company_review->save( $company_review_data );

						// success
						echo '1';
                    } else {
                        echo $user_id->get_error_message();
                    }


				  die();
            } );
        }

		foreach ( [ 'wp_ajax_nopriv_load_more', 'wp_ajax_load_more' ] as $hook ) {
            add_action( $hook, function () {
				$offset = $_POST['offset'];
				$limit = $_POST['limit'];
				$cid = $_POST['company'];
				$cycle = $_POST['cycle'];

				$pod = pods(
					'reviews', [
						'select'  => [
							't.ID as ID',
							't.post_title as title',
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
							'old_id',
							'author_name'
						],
						'limit'     => $limit,
						'offset'	=> $offset,
						'where'  => [
							[
							  'key'   => 'post_status',
							  'value' => 'publish',
							],
							[
							  'key'   => 'company.ID',
							  'value' => $cid,
							],
						],
							'orderby' => 'date DESC',
						]
					);

				if ( !empty( $pod ) ){
					$data = $pod->data();
					    if (is_array($data) || is_object($data)){
							$cycle = $cycle + 6;
							foreach($data as $i => $i_value) {
								include(WP_PLUGIN_DIR.'/financer-review/src/Display/ReviewList.php');
							}
						}

				}

				die();
            } );
        }

		foreach ( [ 'wp_ajax_nopriv_load_more_latest', 'wp_ajax_load_more_latest' ] as $hook ) {
            add_action( $hook, function () {
				$offset = $_POST['offset'];
				$limit = $_POST['limit'];
				$cycle = $_POST['cycle'];
				$carray = $_POST['jarray'];
				$total = $_POST['total'];

				$pod = pods(
					'reviews', [
						'select'  => [
							't.ID as ID',
							't.post_title as title',
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
							'old_id',
							'author_name'
						],
						'limit'     => $limit,
						'offset'	=> $offset,
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

				if ( !empty( $pod ) ){
					$data = $pod->data();
					    if (is_array($data) || is_object($data)){
							$cycle = $cycle + 6;
							foreach($data as $i => $i_value) {
								include(WP_PLUGIN_DIR.'/financer-review/src/Display/LatestReviewList.php');
							}
						}

				}

				die();
            } );
        }

        foreach ( [ 'wp_ajax_nopriv_save_review', 'wp_ajax_save_review' ] as $hook ) {
            add_action( $hook, function () {
                $title = wp_strip_all_tags($_POST['company']) .' Review';
				if (isset($_POST['product'])) {
					$product = $_POST['product'];
					$product_id = get_term_by('slug', $product, 'review_type');
				}
				$cid = $_POST['post_id'];
				$overall_rating = $_POST['rating1'];
				$customer_support = $_POST['rating2'];
				$interest_loan_costs = $_POST['rating3'];
				$flexibility_loan_terms = $_POST['rating4'];
				$website_functionality = $_POST['rating5'];
				$liked = strip_tags(trim($_POST['liked']));
				$disliked = strip_tags(trim($_POST['disliked']));
				$review = array(
					'post_title' => $title,
					'company' => $cid,
					'product_type' => $product_id->term_id ,
					'overall_rating' => $overall_rating,
					'customer_support' => $customer_support,
					'interest_loan_costs' => $interest_loan_costs,
					'flexibility_loan_terms' => $flexibility_loan_terms,
					'website_functionality' => $website_functionality,
					'likes' => $liked,
					'dislikes' => $disliked,
					'post_status' => $_POST['post_status']
			    );
			    $new_review = pods( 'reviews' )->add( $review );
			    wp_set_object_terms( $new_review, $product_id->term_id, 'review_type' );
				$updateReview = array(
					'ID' => $new_review,
					'post_title'    => $new_review . ' - ' . $title,
				);
				wp_update_post( $updateReview );

				// Save author details to review PODS field
				$user = get_user_by('ID', get_post_field( 'post_author', $new_review ) );
				$company_review = pods( 'reviews', $new_review );
                $company_review_data  = array(
                    'author_email' => $user->user_email,
                    'author_name' => $user->display_name
                );
                $company_review->save( $company_review_data );

				$rating = $this->getOverallRating( $cid );
				if( !empty($rating) ){
                    //$avg_rating = $this->getAverageRating($rating);
                    $company_single = pods( 'company_single', $_POST['post_id'] );
                    $company_data = $this->companyRatings($rating, $cid);
                    $company_single->save( $company_data );
				}

				//
                $sizes = array(
                    '1' => 'full',
                    '2' => 'mini'
                );

                $categires = array(
                    '1' => 'overall_rating',
                    '2' => 'customer_support',
                    '3' => 'interest_loan_costs',
                    '4' => 'flexibility_loan_terms',
                    '5' => 'website_functionality',
                );

                    foreach ($sizes as $sizeKey => $size) {
                        foreach ($categires as $catkey => $category) {
                            $locale = get_locale();
                            $_GET['i'] = $cid;
                            $_GET['c'] = $catkey . '';
                            $_GET['s'] = $sizeKey . '';

                            require(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/wp-content/themes/financer/badge_imagesave_template.php');

                        }
                    }
                //

				wp_die();
            } );
        }

		foreach ( [ 'wp_ajax_nopriv_save_reply', 'wp_ajax_save_reply' ] as $hook ) {
            add_action( $hook, function () {
				$review_id = $_POST['review_id'];
				$comment = strip_tags(trim($_POST['content']));
				$company = $_POST['company'];
				$author = $_POST['author'];
				$title = 'Reply to review '. $review_id .' about '. $company;
				$reply = array(
						'post_title' => $title,
						'comment' => $comment,
						'review' => $review_id,
						'author' => $author,
						'post_status' => 'publish',
					);
				$new_reply = pods( 'reviews_reply' )->add( $reply );

				// Save author details to reply PODS field
				$user = get_user_by('ID', get_post_field( 'post_author', $new_reply ) );
				$company_review = pods( 'reviews_reply', $new_reply );
                $company_review_data  = array(
                    'author_email' => $user->user_email,
                    'author_name' => $user->display_name
                );
                $company_review->save( $company_review_data );

				// Return
				if( !is_wp_error($new_reply) ) {
					$review_author = pods('reviews', $review_id);

				    $to = $review_author->display('author_email');
				    $subject = pods_field('general_settings', null, 'review_email_title', true);
				    $body = do_shortcode( pods_field('general_settings', null, 'review_email_content', true) );
				    $headers = array('Content-Type: text/html; charset=UTF-8');
				    wp_mail( $to, $subject, $body, $headers );
					echo '1';
				} else {
					echo $new_reply->get_error_message();
				}
				wp_die();
            });
        }

		foreach ( [ 'wp_ajax_nopriv_save_like', 'wp_ajax_save_like' ] as $hook ) {
            add_action( $hook, function () {
				$new_like = pods( 'reviews' )->save( 'vote_like', $_POST['likevalue'], $_POST['review_id'] );
				if( !is_wp_error($new_like) ) {
					echo '1';
				} else {
					echo $new_like->get_error_message();
				}
				wp_die();
            } );
        }

		foreach ( [ 'wp_ajax_nopriv_like_tip', 'wp_ajax_like_tip' ] as $hook ) {
            add_action( $hook, function () {
				$new_like = pods( 'saving_tip' )->save( 'vote_like', $_POST['likevalue'], $_POST['tip_id'] );
				if( !is_wp_error($new_like) ) {
					echo '1';
				} else {
					echo $new_like->get_error_message();
				}
				wp_die();
            } );
        }

		ob_start();

		wp_enqueue_script( 'FNreviewstar', plugin_dir_url( __DIR__ ) . 'js/FNstar.js'  , [ 'jquery' ] );
		wp_add_inline_script( 'FNreviewstar', $this->StarRating() );
		wp_enqueue_script( 'fn-slick', plugin_dir_url( __DIR__ ) . 'js/slick.js'  , [ 'jquery' ] );
		wp_enqueue_style('circles', plugin_dir_url( __DIR__ ) . 'js/circle.css', array(), '0.1.0', 'all');

		add_action( 'review_form', function(){
			wp_enqueue_script( 'jquery-easing', plugin_dir_url( __DIR__ ) . 'js/jquery.easing.min.js'  , [ 'jquery' ] );
			wp_enqueue_script( 'jquery-form-validator', plugin_dir_url( __DIR__ ) . 'js/jquery.form-validator.js'  , [ 'jquery' ] );
			wp_enqueue_script( 'FNreview', plugin_dir_url( __DIR__ ) . 'js/FNreview.js'  , [ 'jquery' ] );
			wp_add_inline_script( 'FNreview', $this->renderJs() );
			wp_enqueue_script('FNreview');
	 		wp_localize_script('FNreview', 'fn_reg_vars', array('fn_ajax_url' => admin_url( 'admin-ajax.php' ),));

			$ReviewDisplay = new ReviewDisplay();
	        $ReviewDisplay->renderDisplay();
		} );

		add_action( 'review_form_full', function(){
			wp_enqueue_script( 'jquery-easing', plugin_dir_url( __DIR__ ) . 'js/jquery.easing.min.js'  , [ 'jquery' ] );
			wp_enqueue_script( 'jquery-form-validator', plugin_dir_url( __DIR__ ) . 'js/jquery.form-validator.js'  , [ 'jquery' ] );
			wp_enqueue_script( 'FNreview', plugin_dir_url( __DIR__ ) . 'js/FNreview.js'  , [ 'jquery' ] );
			wp_add_inline_script( 'FNreview', $this->renderJs() );
			wp_enqueue_script('FNreview');
	 		wp_localize_script('FNreview', 'fn_reg_vars', array('fn_ajax_url' => admin_url( 'admin-ajax.php' ),));

			$ReviewDisplay = new ReviewDisplay();
			$ReviewDisplay->ReviewList();
	        $ReviewDisplay->renderDisplay();
		} );

		add_action( 'review_form_modal', function(){
			wp_enqueue_script( 'jquery-easing', plugin_dir_url( __DIR__ ) . 'js/jquery.easing.min.js'  , [ 'jquery' ] );
			wp_enqueue_script( 'jquery-form-validator', plugin_dir_url( __DIR__ ) . 'js/jquery.form-validator.js'  , [ 'jquery' ] );
			wp_enqueue_script( 'FNreview', plugin_dir_url( __DIR__ ) . 'js/FNreview.js'  , [ 'jquery' ] );
			wp_add_inline_script( 'FNreview', $this->renderJs() );
			wp_enqueue_script('FNreview');
	 		wp_localize_script('FNreview', 'fn_reg_vars', array('fn_ajax_url' => admin_url( 'admin-ajax.php' ),));

			$ReviewDisplay = new ReviewDisplay();
	        $ReviewDisplay->renderModalDisplay();
		} );

		add_action( 'before_delete_post', function($postid){
			// We check if the global post type isn't ours and just return
		    global $post_type;

		    if ( $post_type != 'reviews' ) return;

		    $delete_pod = pods(
				'reviews', [
				  'select'  => [
					  't.ID as ID',
					  'company.ID as pid',
					  'overall_rating',
					  'customer_support',
                      'interest_loan_costs',
                      'flexibility_loan_terms',
                      'website_functionality'
				  ],
				  'limit'     => 1,
				  'where'  => [
					[
					  'key'   => 'post_status',
					  'value' => 'trash',
					],
					[
					  'key'   => 't.ID',
					  'value' => $postid,
					],

				  ],
				]
			);

		    if ($delete_pod) {
                $rating = $this->getOverallRating( $delete_pod->display('pid') );
                if( !empty($rating) ) {
                    $company_single = pods( 'company_single', $delete_pod->display('pid') );
                    $company_data = $this->companyRatings($rating, $delete_pod->display('pid'));
                    $company_single->save( $company_data );
                }
            }

		} );


		return ob_get_clean();
	}


	/*
	*
	*/
	public function setup(){
		add_action('init', [$this, 'render']);

		add_filter( 'wp_link_query_args', function( $query ){
			 // this is the post type I want to exclude
		     $cpt_to_remove = ['reviews','reviews_reply','saving_tip'];

		     foreach( $cpt_to_remove as $cpt ){
		     	// find the corresponding array key
			    $key = array_search( $cpt, $query['post_type'] );

			    // remove the array item
			    if( $key )
			        unset( $query['post_type'][$key] );
		     }
		    return $query;
		} );

		return true;
	}

	/**
	 *
	 */
	public function renderJs() {
		$output = <<<JS
	   (function($) {
	    $(function() {
				 if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {
					//jQuery time
					var current_fs, next_fs, previous_fs; //fieldsets
					var left, opacity, scale; //fieldset properties which we will animate
					var animating; //flag to prevent quick multi-click glitches

					$(".next").click(function(){
						if(animating) return false;
						animating = true;
						
						current_fs = $(this).parent();
						next_fs = $(this).parent().next();
						
						//activate next step on progressbar using the index of next_fs
						$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
						
						//show the next fieldset
						next_fs.show(); 
						//hide the current fieldset with style
						current_fs.animate({opacity: 0}, {
							step: function(now, mx) {
								//as the opacity of current_fs reduces to 0 - stored in "now"
								//1. scale current_fs down to 80%
								scale = 1 - (1 - now) * 0.2;
								//2. bring next_fs from the right(50%)
								left = (now * 50)+"%";
								//3. increase opacity of next_fs to 1 as it moves in
								opacity = 1 - now;
								current_fs.css({
					        'transform': 'scale('+scale+')',
					        'position': 'absolute'
					      });
								next_fs.css({'left': left, 'opacity': opacity});
							}, 
							duration: 800, 
							complete: function(){
								current_fs.hide();
								animating = false;
							}, 
							//this comes from the custom easing plugin
							easing: 'easeInOutBack'
						});
						//calculate next fieldsets height and add it to form
						var height = $(next_fs).outerHeight() + $("#progressbar").outerHeight();
						$("#msform").height(height + 20);
					});

					$(".previous").click(function(){
						if(animating) return false;
						animating = true;
						
						current_fs = $(this).parent();
						previous_fs = $(this).parent().prev();
						
						//de-activate current step on progressbar
						$("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
						
						//show the previous fieldset
						previous_fs.show();
						//hide the current fieldset with style
						current_fs.animate({opacity: 0}, {
							step: function(now, mx) {
								//as the opacity of current_fs reduces to 0 - stored in "now"
								//1. scale previous_fs from 80% to 100%
								scale = 0.8 + (1 - now) * 0.2;
								//2. take current_fs to the right(50%) - from 0%
								left = ((1-now) * 50)+"%";
								//3. increase opacity of previous_fs to 1 as it moves in
								opacity = 1 - now;
								current_fs.css({'left': left});
								previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
							}, 
							duration: 800, 
							complete: function(){
								current_fs.hide();
								animating = false;
							}, 
							//this comes from the custom easing plugin
							easing: 'easeInOutBack'
						});
						//calculate previous fieldsets height and add it to form
						var height = $(previous_fs).outerHeight() + $("#progressbar").outerHeight();
						$("#msform").height(height + 20);
					});

					$(".submit").click(function(){
						return false;
					});

					
					$('.js-slider-company').slick({
					   infinite: true,
					   slidesToShow: 1,
					   slidesToScroll: 1,
					   dots: false,
					   autoplay: true,
					   autoplaySpeed: 5000,
					   arrows: true
					});
					
					$('.js-slider-saving').slick({
					   infinite: true,
					   slidesToShow: 1,
					   slidesToScroll: 1,
					   dots: false,
					   autoplay: true,
					   autoplaySpeed: 5000,
					   arrows: true
					});
					
					$('.js-slider-mortgage').slick({
					   infinite: true,
					   slidesToShow: 1,
					   slidesToScroll: 1,
					   dots: false,
					   autoplay: true,
					   autoplaySpeed: 5000,
					   arrows: true
					});
					$('.js-slider-loan').slick({
					   infinite: true,
					   slidesToShow: 1,
					   slidesToScroll: 1,
					   dots: false,
					   autoplay: true,
					   autoplaySpeed: 5000,
					   arrows: true
					});
					$('.js-slider-cards').slick({
					   infinite: true,
					   slidesToShow: 1,
					   slidesToScroll: 1,
					   dots: false,
					   autoplay: true,
					   autoplaySpeed: 5000,
					   arrows: true
					});

					$('#scroll-borrowTab').on("click", function() {
						$('.js-slider-loan').slick('setPosition');
					});
					$('#scroll-saveTab').on("click", function() {
						$('.js-slider-saving').slick('setPosition');
					});
					$('#scroll-cardsTab').on("click", function() {
						$('.js-slider-cards').on('setPosition');
						$(".js-slider-cards").slick('slickGoTo', 1, true);
					});
					$('#scroll-mortgageTab').on("click", function() {
						$('.js-slider-mortgage').slick('setPosition');
					});
					$('#scroll-companyInfoTab').on("click", function() {
						$('.js-slider-company').slick('setPosition');
					});

					$("#intermediate").click(function() {
						var r1 = document.getElementById('rating1').value;
						var r2 = document.getElementById('rating2').value;
						var r3 = document.getElementById('rating3').value;
						var r4 = document.getElementById('rating4').value;
						var r5 = document.getElementById('rating5').value;
						var liked = document.getElementById('positive_txt').value;
						var disliked = document.getElementById('negative_txt').value;
						var product = document.getElementById('product').value;
						localStorage.setItem('rating1', r1);
						localStorage.setItem('rating2', r2);
						localStorage.setItem('rating3', r3);
						localStorage.setItem('rating4', r4);
						localStorage.setItem('rating5', r5);
						localStorage.setItem('liked', liked);
						localStorage.setItem('disliked', disliked);
						localStorage.setItem('product', product);
					});
					
				 }
				 else {
					setTimeout(init, 50);
				 }
			    });
	})(jQuery);

JS;

		$output .= $this->AJAXRegistration();
		return $output;
	}

	/**
	 * Submit Review
	 */
	public function AJAXRegistration() {
		$postID = get_the_ID();
		$sending_txt = __('Sending...', 'fr');
		$sent_txt = __('Sent!', 'fr');
		$blogurl = get_bloginfo('url');
		$output = <<<JS
	   (function($) {
			$(function() {
				if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {
					$('#submitbtn').on('click', function() {
						if($('input#gdpr-check:checked').length>0) {
						document.getElementById("submitbtn").value = '{$sending_txt}';
							var ajaxurl = fn_reg_vars.fn_ajax_url;
							var formData=new FormData(document.getElementById('msform')); 
							var postid = '{$postID}';
							var redirectlink = '?_reload#read-reviews';
							var ps = 'publish';
							if ( $('#modal-data-div').length ) {
								var postid = $('#modal-data-div').data('cid');
								var redirectlink = '{$blogurl}' + '/?p=' + postid + '#read-reviews';
								
							}
							formData.append("action", "save_review"); 
							formData.append("post_id",postid);
							formData.append("post_status",ps);
							
							$.ajax({
								type: 'POST',
								data: formData,
								url: ajaxurl,
								cache: false,
								processData: false, 
								contentType: false, 
								success: function(result) {
									document.getElementById("submitbtn").disabled = true;
									$("#lastbackbtn").hide();
									$("#submitbtn").html('{$sent_txt}');
									$("#fsuccess").show();
									window.location.href = redirectlink;
								},
								error: function(jqXHR, textStatus, errorThrown) {
									console.log(jqXHR);
									console.log("AJAX error: " + textStatus + " :: " + errorThrown);
									console.log(JSON.stringify(jqXHR, null, "\t"));
									window.location.href = redirectlink;
								}
							});
						}
					});
				} else {
					setTimeout(init, 50);
				}
			});
		})(jQuery);
JS;

		return $output;

	}


	/**
	 *
	 */
	public function StarRating() {
		$output = <<<JS
		
		    function SimpleStarRating(target) {
		        function attr(name, d) {
		            var a = target.getAttribute(name);
		            return (a ? a : d);
		        }

		        var max = parseInt(attr('data-stars', 5)),
		            disabled = typeof target.getAttribute('disabled') != 'undefined',
		            defaultRating = parseFloat(attr('data-default-rating', 0)),
		            currentRating = -1,
		            stars = [];

		        target.style.display = 'inline-block';

		        for (var s = 0; s < max; s++) {
		            var n = document.createElement('span');
		            n.className = 'star';
		            n.addEventListener('click', starClick);
		            if (s > 0)
		                stars[s - 1].appendChild(n);
		            else
		                target.appendChild(n);

		            stars.push(n);
		        }

		        function disable() {
		            target.setAttribute('disabled', '');
		            disabled = true;
		        }
		        this.disable = disable;

		        function enable() {
		            target.removeAttribute('disabled');
		            disabled = false;
		        }
		        this.enable = enable;

		        function setCurrentRating(rating) {
		            currentRating = rating;
		            target.setAttribute('data-rating', currentRating);
		            showCurrentRating();
		        }
		        this.setCurrentRating = setCurrentRating;

		        function setDefaultRating(rating) {
		            defaultRating = rating;
		            target.setAttribute('data-default-rating', defaultRating);
		            showDefaultRating();
		        }
		        this.setDefaultRating = setDefaultRating;

		        this.onrate = function (rating) {};

		        target.addEventListener('mouseout', function () {
		            disabled = target.getAttribute('disabled') !== null;
		            if (!disabled)
		                showCurrentRating();
		        });

		        target.addEventListener('mouseover', function () {
		            disabled = target.getAttribute('disabled') !== null;
		            if (!disabled)
		                clearRating();
		        });

		        showDefaultRating();

		        function showRating(r) {
		            clearRating();
		            for (var i = 0; i < stars.length; i++) {
		                if (i >= r)
		                    break;
		                if (i === Math.floor(r) && i !== r)
		                    stars[i].classList.add('half');
		                stars[i].classList.add('active');
		            }
		        }

		        function showCurrentRating() {
		            var ratingAttr = parseFloat(attr('data-rating', 0));
		            if (ratingAttr) {
		                currentRating = ratingAttr;
		                showRating(currentRating);
		            } else {
		                showDefaultRating();
		            }
		        }

		        function showDefaultRating() {
		            defaultRating = parseFloat(attr('data-default-rating', 0));
		            showRating(defaultRating);
		        }

		        function clearRating() {
		            for (var i = 0; i < stars.length; i++) {
		                stars[i].classList.remove('active');
		                stars[i].classList.remove('half');
		            }
		        }

		        function starClick(e) {
		            if (disabled) return;

		            if (this === e.target) {
		                var starClicked = stars.indexOf(e.target);
		                if (starClicked !== -1) {
		                    var starRating = starClicked + 1;
		                    setCurrentRating(starRating);
		                    if (typeof this.onrate === 'function')
		                        this.onrate(currentRating);
		                    var evt = new CustomEvent('rate', {
		                        detail: starRating,
		                    });
		                    target.dispatchEvent(evt);
		                }
		            }
		        }
		    }

JS;

		return $output;

	}

	public function sendThankYouEmail($email, $reviewId, $companyName, $link)
    {
        // send confirmation email
        $footerinfo = pods( 'footer_settings' );
        $fb_link = $footerinfo->row['facebook'] ? $footerinfo->row['facebook'] : 'https://www.facebook.com/financercom/';
        $twitter_link = $footerinfo->row['twitter'] ? $footerinfo->row['twitter'] : 'https://twitter.com/financercom';
        $home_link = get_bloginfo('url');

        $posted_date = get_the_date(get_option('date_format'), $reviewId);
        $subject = __('Thank you for your singup at Financer.com','fr');
        ob_start();
        include(plugin_dir_path( __DIR__ ) . 'Display/template/email_thanks.php');
        $body = ob_get_contents();
        ob_end_clean();


        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( $email, $subject, $body, $headers );
    }

    /**
     * @param $id
     * @return array
     */
	public function getOverallRating($id){
        $rating = [];
        $pod = pods(
            'reviews', [
                'select'  => [
                    't.ID as ID',
                    'company.ID as pid',
                    'overall_rating',
                    'customer_support',
                    'interest_loan_costs',
                    'flexibility_loan_terms',
                    'website_functionality'
                ],
                'limit'     => -1,
                'where'  => [
                    [
                        'key'   => 'post_status',
                        'value' => 'publish',
                    ],
                    [
                        'key'   => 'company.ID',
                        'value' => $id,
                    ],

                ],
            ]
        );

        if ( !empty( $pod ) ){
            $data = $pod->data();
            $customer_support = $interest_loan_costs = $flexibility_loan_terms = $website_functionality = $overall_rating = $counter = 0;

            if ( is_array($data) ) {
                foreach($data as $i => $i_value) {
                    $overall_rating = $overall_rating + $i_value->overall_rating;
                    $customer_support = $customer_support + $i_value->customer_support;
                    $interest_loan_costs = $interest_loan_costs + $i_value->interest_loan_costs;
                    $flexibility_loan_terms = $flexibility_loan_terms + $i_value->flexibility_loan_terms;
                    $website_functionality = $website_functionality + $i_value->website_functionality;
                    $counter++;
                }
                $rating['overall_rating'] = $overall_rating;
                $rating['customer_support'] = $customer_support;
                $rating['interest_loan_costs'] = $interest_loan_costs;
                $rating['flexibility_loan_terms'] = $flexibility_loan_terms;
                $rating['website_functionality'] = $website_functionality;
                $rating['total_review'] = $counter;
            }
        }

        return $rating;
	}


	/*
	*
	*/
	public function YearlyRating($id){
		$rating = [];
		$period = date("Y");
		$pod = pods(
		    'reviews', [
		        'select'  => [
		            't.ID as ID',
		            'company.ID as pid',
		            'overall_rating',
		        ],
		        'limit'     => -1,
		        'where'  => [
		            [
		                'key'   => 'post_status',
		                'value' => 'publish',
		            ],
		            [
		              'key'   => 'company.ID',
		              'value' => $id,
		            ],
		            [
		                'key'   => 'post_date',
		                'value'   => [ ( new \DateTime( 'first day of january ' . $period ) )->format( 'Y-m-d' ), ( new \DateTime( '11:59:59 last day of december ' . $period ) )->format( 'Y-m-d H:i:s' ) ],
		                        'compare' => 'BETWEEN'
		            ],
		        ],
		    ]
		);

		if ( !empty( $pod ) ){
		    $data = $pod->data();
		    $overall = $counter = 0;

		    if ( is_array($data) ) {
		        foreach($data as $i => $i_value) {
		            $overall = $overall + $i_value->overall_rating;
		            $counter++;
		        }
		        $rating['overall_rating'] = $overall;
		    }
		}

	    return $rating;
	}

	public function companyRatings($rating, $cid) {
        $overall_rate = $rating['overall_rating'] / $rating['total_review'];
        $customer_support = $rating['customer_support'] / $rating['total_review'];
        $interest_loan_costs = $rating['interest_loan_costs'] / $rating['total_review'];
        $flexibility_loan_terms = $rating['flexibility_loan_terms'] / $rating['total_review'];
        $website_functionality = $rating['website_functionality'] / $rating['total_review'];

        $yearlyrating = $this->YearlyRating( $cid );
        $yearlyrating = $yearlyrating['overall_review'] / $yearlyrating['total_review'];

        $company_data  = array(
            'overall_rating' => $overall_rate,
            'yearlyrating' => $yearlyrating,
            'customer_support' => $customer_support,
            'interest_loan_costs' => $interest_loan_costs,
            'flexibility_loan_terms' => $flexibility_loan_terms,
            'website_functionality' => $website_functionality,
            'total_review' => $rating['total_review']
        );

        return $company_data;
    }

}
