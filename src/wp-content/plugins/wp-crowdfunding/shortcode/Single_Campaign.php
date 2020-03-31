<?php
namespace WPCF\shortcode;

defined( 'ABSPATH' ) || exit;

class Single_Campaign {

    function __construct() {
        add_shortcode( 'wpcf_single_campaign', array( $this, 'single_campaign_callback' ) );
    }

    function single_campaign_callback( $atts, $shortcode ){
        $atts = shortcode_atts( array(
            'campaign_id' => 0,
        ), $atts, $shortcode );

        $args = array(
            'posts_per_page'      => 1,
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => 1,
            'no_found_rows'       => 1,
        );

        if ( isset( $atts['campaign_id'] ) ) {
            $args['p'] = absint( $atts['campaign_id'] );
        }

        $single_product = new \WP_Query( $args );

        // For "is_single" to always make load comments_template() for reviews.
        $single_product->is_single = true;

        ob_start();

        global $wp_query;

        // Backup query object so following loops think this is a product page.
        $previous_wp_query = $wp_query;
        $wp_query          = $single_product;

        wp_enqueue_script( 'wc-single-product' );

        while ( $single_product->have_posts() ) {
            $single_product->the_post();
            wpcf_function()->template('single-crowdfunding-content-only');
        }

        // restore $previous_wp_query and reset post data.
        $wp_query = $previous_wp_query;
        wp_reset_postdata();
        $final_content = ob_get_clean();

        return '<div class="woocommerce">' . $final_content . '</div>';
    }
}