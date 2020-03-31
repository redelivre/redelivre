<?php
namespace WPCF\shortcode;

defined( 'ABSPATH' ) || exit;

class Campaign_Box {

    function __construct() {
        add_shortcode( 'wpcf_campaign_box', array( $this, 'campaign_box_callback' ) );
    }

    function campaign_box_callback( $atts, $shortcode ) {

        $atts = shortcode_atts( array(
            'campaign_id' => 0,
        ), $atts, $shortcode );

        if ( !  $atts['campaign_id'] ){
            return false;
        }

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

        global $wp_query;

        // Backup query object so following loops think this is a product page.
        $previous_wp_query = $wp_query;
        $wp_query          = $single_product;

        ob_start();
        while ( $single_product->have_posts() ) {
            $single_product->the_post();
            ?>

            <div class="wpneo-listings three">
                <?php do_action('wpcf_campaign_loop_item_before_content'); ?>
                <div class="wpneo-listing-content">
                    <?php do_action('wpcf_campaign_loop_item_content'); ?>
                </div>
                <?php do_action('wpcf_campaign_loop_item_after_content'); ?>
            </div>
            
            <?php
        }
        // restore $previous_wp_query and reset post data.
        $wp_query = $previous_wp_query;
        $output_string = ob_get_clean();
        wp_reset_postdata();

        return $output_string;
    }

}






