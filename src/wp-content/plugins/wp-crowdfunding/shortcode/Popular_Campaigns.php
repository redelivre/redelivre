<?php
namespace WPCF\shortcode;

defined( 'ABSPATH' ) || exit;

class Popular_Campaigns {

    function __construct() {
        add_shortcode( 'wpcf_popular_campaigns', array( $this, 'popular_campaigns_callback' ) );
    }

    function popular_campaigns_callback( $atts, $shortcode ) {
        
        $a = shortcode_atts(array(
            'number'      => -1,
            'order'     => 'DESC',
        ), $atts, $shortcode );

        $paged = 1;
        if ( get_query_var('paged') ) {
            $paged = absint( get_query_var('paged') );
        } elseif (get_query_var('page')) {
            $paged = absint( get_query_var('page') );
        }

        $query_args = array(
            'post_type'             => 'product',
            'post_status' 			=> 'publish',
            'ignore_sticky_posts'   => 1,
            'meta_key' 		 		=> 'total_sales',
            'posts_per_page'        => $a['number'],
            'paged'                 => $paged,
            'orderby' 		        => 'meta_value_num',
            'order'                 => $a['order'],
            'meta_query' => array(
                array(
                    'key' 		=> 'total_sales',
                    'value' 	=> 0,
                    'compare' 	=> '>',
                )
            ),

            'tax_query' => array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'crowdfunding',
                ),
            )
        );

        query_posts($query_args);

        ob_start();
        wpcf_function()->template('wpneo-listing');
        $html = ob_get_clean();
        wp_reset_query();
        return $html;
    }
}