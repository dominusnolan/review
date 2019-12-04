<?php

namespace Financer_Review;


use Financer_Review\Core\Component;

/**
 * Class ReviewComponent
 *
 * @package Financer_Review
 * @property \Financer_Review\Plugin $plugin
 */
class ReviewComponent extends Component {

	public function reviews_posttype(){	
		register_post_type(
            'reviews', [
                'labels'          => [
                    'name'               => __( 'Reviews', 'fr' ),
                    'singular_name'      => __( 'Reviews', 'fr' ),
                    'add_new'            => __( 'Add New', 'fr' ),
                    'add_new_item'       => __( 'Add New Review', 'fr' ),
                    'edit_item'          => __( 'Edit Review', 'fr' ),
                    'new_item'           => __( 'New Review', 'fr' ),
                    'all_items'          => __( 'All Review', 'fr' ),
                    'view_item'          => __( 'View Review', 'fr' ),
                    'search_items'       => __( 'Search Reviews', 'fr' ),
                    'not_found'          => __( 'No Reviews found', 'fr' ),
                    'not_found_in_trash' => __( 'No Reviews found in Trash', 'fr' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Reviews', 'fr' ),
                ],
                'public'          => true,
                'rewrite'         => [ 'slug' => __( 'reviews', 'fr' ), 'with_front' => false ],
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => null,
                'feeds'           => null,
                'supports'        => [
                    'title',
                ],
           	]
        );

        register_post_type(
                    'reviews_reply', [
                        'labels'          => [
                            'name'               => __( 'Reviews Reply', 'fr' ),
                            'singular_name'      => __( 'Reviews Reply', 'fr' ),
                            'add_new'            => __( 'Add New Reply', 'fr' ),
                            'add_new_item'       => __( 'Add New Reply', 'fr' ),
                            'edit_item'          => __( 'Edit Reply', 'fr' ),
                            'new_item'           => __( 'New Reply', 'fr' ),
                            'all_items'          => __( 'All Replies', 'fr' ),
                            'view_item'          => __( 'View Replies', 'fr' ),
                            'search_items'       => __( 'Search Replies', 'fr' ),
                            'not_found'          => __( 'No Replies found', 'fr' ),
                            'not_found_in_trash' => __( 'No Replies found in Trash', 'fr' ),
                            'parent_item_colon'  => '',
                            'menu_name'          => __( 'Review Replies', 'fr' ),
                        ],
                        'public'          => true,
                        'rewrite'         => [ 'slug' => __( 'reviews_reply', 'fr' ), 'with_front' => false ],
                        'capability_type' => 'post',
                        'has_archive'     => false,
                        'show_ui'           => true,
                        'show_admin_column' => true,
                        'hierarchical'    => false,
                        'menu_position'   => null,
                        'feeds'           => null,
                        'supports'        => [
                            'title',
                        ],
                    ]
                );

 		register_taxonomy( 'review_type', [ 'reviews' ], [

                'hierarchical'      => true,
                'labels'            => [
                    'name'          => _x( 'Review Type', 'taxonomy general name', 'fr' ),
                    'singular_name' => _x( 'Review Type', 'taxonomy singular name', 'fr' ),
                ],
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => [ 'slug' => 'review_type' ],
            ]
        );

		add_action( 'add_meta_boxes', function () {
           add_meta_box(
				'review_reply', // $id
				'Replies', // $title
				array( $this, 'review_replies' ), // $callback
				'reviews', // $screen
				'normal', // $context
				'low' // $priority
			);
        } );


        add_filter(
            'manage_reviews_posts_columns',
            function () {
                return [
                    'cb'           => true,
                    'title'        => _x( 'Review', 'financer-review' ),
                    'author' 		=> 'Author',
                    'rating' 		=> 'Ratings',
                    'date'         => __( 'Date' ),
                ];
            }
        );

        add_action(
            'manage_reviews_posts_custom_column',
            function ($column, $post_id) {
                switch ( $column ) {
		        case 'rating':
		        	$pod = pods(
					'reviews', [
							'limit'   => -1,
							'select'  => [
								't.ID as ID',
							],
							'where'   => [
										[
											'key'     => "ID",
											'value'   => $post_id,
											'compare' => '=',
										],
									],
						]
					);
		        	if ( 0 < $pod->total() ) { 
				        while ( $pod->fetch() ) { 
				        	echo $pod->field( 'overall_rating' );
				        }
				    }
		        break;  
		    }
        }
        ,10,2);

        add_filter(
            'manage_edit-reviews_sortable_columns',
            function ($columns) {
                $columns['rating'] = 'rating';
                return $columns;
            }
        ,10,1);

        add_filter( 'comment_post_redirect', 'redirect_after_comment' );
        function redirect_after_comment( $location ) {
            $newurl = substr( $location, 0, strpos( $location, "#comment" ) );
            return $newurl . '?c=y';
        }


	}

	public function review_replies() {
		global $post;  
		$meta = get_post_meta( $post->ID, 'your_fields', true ); 
		$author_id = $post->post_author;
		?>
		
		<input type="hidden" name="your_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>" />
		<p><b>Author: </b> <?php echo get_the_author_meta('display_name', $author_id); ?></p>
		<hr>
		<h4>Replies</h4>
	
    <!-- All fields will go here -->

	<?php }

	/*
	*
	*/
	public function setup(){
		add_action('init', [$this, 'reviews_posttype']);

        $dir = new \DirectoryIterator( dirname( __FILE__ ) . '/Shortcode' );
        foreach ( $dir as $file ) {
            if ( $file->isFile() ) {
                ( new \ReflectionClass( '\Financer_Review\Shortcode\\' . $file->getBasename( '.php' ) ) )->getMethod( 'register' )->invoke( null );
            }
        }
        wp_enqueue_script( 'FNshortcode', plugin_dir_url( __DIR__ ) . '/src/js/FNshortcode.js'  , [ 'jquery' ] );
        wp_add_inline_script( 'FNshortcode', self::renderShortcodeJs() );

		return true;
	}

    /**
     *
     */
    public function renderShortcodeJs() {
            $output = <<<JS
       (function($) {
        $(function() {
                 if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {
                    var ratings0 = document.getElementsByClassName('rating0');
                    for (var i = 0; i < ratings0.length; i++) {
                      var r0 = new SimpleStarRating(ratings0[i]);
                    }
                    var ratings1 = document.getElementsByClassName('rating1');
                    var ratings2 = document.getElementsByClassName('rating2');
                    var ratings3 = document.getElementsByClassName('rating3');
                    var ratings4 = document.getElementsByClassName('rating4');
                    var ratings5 = document.getElementsByClassName('rating5');
                    var ratings6 = document.getElementsByClassName('rating6');
					var ratings7 = document.getElementsByClassName('rating7');
                    
                 }
                 else {
                    setTimeout(init, 50);
                 }
                });
    })(jQuery);
JS;

        return $output;
    }

}
