<?php
namespace WPCF\shortcode;

defined( 'ABSPATH' ) || exit;

class Project_Listing {

    function __construct() {
        add_shortcode( 'wpcf_listing', array( $this, 'listing_callback' ) );
    }

    function listing_callback( $atts, $shortcode ) {
        if( function_exists('wpcf_function') ){

            $a = shortcode_atts(array(
                'cat'         => null,
                'number'      => -1,
                'order'     => 'DESC',
            ), $atts, $shortcode );

            $paged = 1;
            if ( get_query_var('paged') ){
                $paged = absint( get_query_var('paged') );
            } elseif (get_query_var('page')) {
                $paged = absint( get_query_var('page') );
            }

            $query_args = array(
                'post_type'     => 'product',
                'post_status'   => 'publish',
                'tax_query'     => array(
                    'relation'  => 'AND',
                    array(
                        'taxonomy'  => 'product_type',
                        'field'     => 'slug',
                        'terms'     => 'crowdfunding',
                    ),
                ),
                'posts_per_page'    => $a['number'],
                'paged'             => $paged,
                'orderby' 		    => 'post_title',
                'order'             => $a['order'],
            );

            if (!empty($_GET['author'])) {
                $user_login     = sanitize_text_field( trim( $_GET['author'] ) );
                $user           = get_user_by( 'login', $user_login );
                if ($user) {
                    $user_id    = $user->ID;
                    $query_args = array(
                        'post_type'   => 'product',
                        'author'      => $user_id,
                        'tax_query'   => array(
                            array(
                                'taxonomy'  => 'product_type',
                                'field'     => 'slug',
                                'terms'     => 'crowdfunding',
                            ),
                        ),
                        'posts_per_page' => -1
                    );
                }
            }

            if( $a['cat'] ){
                $cat_array = explode(',', $a['cat']);
                $query_args['tax_query'][] = array(
                    'taxonomy'  => 'product_cat',
                    'field'     => 'slug',
                    'terms'     => $cat_array,
                );
            }
            query_posts($query_args);
            ob_start();
            wpcf_function()->template('wpneo-listing');
            $html = ob_get_clean();
            wp_reset_query();
            return $html;
        }
    }
}
