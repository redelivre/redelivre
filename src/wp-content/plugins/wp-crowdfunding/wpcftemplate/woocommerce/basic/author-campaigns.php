<?php get_header();
global $wp_query;
?>

    <section id="main">
		<?php

        do_action('before_campaigns_by_user_container');

        $author = $wp_query->get_queried_object();
        ?>

        <div class="sub-title">
            <div class="container">
                <div class="sub-title-inner">
				    <h2><?php _e('Campaigns by Author', 'wp-crowdfunding'); ?></h2>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row woo-products">
                <div id="content" class="col-sm-12" role="main">
                    <div class="site-content">

						<?php


						$args = array(
							'post_type' 			=> 'product',
							'post_status' 			=> 'publish',
							'author'                => $author->ID,
							'ignore_sticky_posts'   => 1,
							'fields'                => 'ids',
							'posts_per_page'		=> -1,
							'orderby'               => 'id',
							'order'                 => 'DESC',
						);

						$query = new WP_Query($args);

						$paginated = ! $query->get( 'no_found_rows' );
						$products = (object) array(
							'ids'          => wp_parse_id_list( $query->posts ),
							'total'        => $paginated ? (int) $query->found_posts : count( $query->posts ),
							'total_pages'  => $paginated ? (int) $query->max_num_pages : 1,
							'per_page'     => (int) $query->get( 'posts_per_page' ),
							'current_page' => $paginated ? (int) max( 1, $query->get( 'paged', 1 ) ) : 1,
						);

						$columns = 4;
						$classes    = array('woocommerce', 'columns-'.$columns);

						$wp_query->max_num_pages = $products->total_pages;

						ob_start();

						if ( $products && $products->ids ) {
							// Prime meta cache to reduce future queries.
							update_meta_cache( 'post', $products->ids );
							update_object_term_cache( $products->ids, 'product' );

							// Setup the loop.
							wc_setup_loop( array(
								'columns'      => $columns,
								'name'         => 'products',
								'is_shortcode' => false,
								'is_search'    => false,
								'is_paginated' => false,
								'total'        => $products->total,
								'total_pages'  => $products->total_pages,
								'per_page'     => $products->per_page,
								'current_page' => $products->current_page,
							) );

							do_action( 'woocommerce_before_shop_loop' );

							woocommerce_product_loop_start();

							if ( wc_get_loop_prop( 'total' ) ) {
								foreach ( $products->ids as $product_id ) {
									$GLOBALS['post'] = get_post( $product_id ); // WPCS: override ok.
									setup_postdata( $GLOBALS['post'] );

									//Render product template.
									wc_get_template_part( 'content', 'product' );
								}
							}

							woocommerce_product_loop_end();
							do_action( 'woocommerce_after_shop_loop' );

							wp_reset_postdata();
							wc_reset_loop();
						}

						echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . ob_get_clean() . '</div>';
						?>


                    </div>
                </div> <!-- #content -->
            </div> <!-- .row -->
        </div> <!-- .container -->
    </section>


<?php get_footer();