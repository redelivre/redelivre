<?php
defined( 'ABSPATH' ) || exit;

$page_numb = max( 1, get_query_var('paged') );
$posts_per_page = get_option( 'posts_per_page',10 );
$args = array(
    'post_type' 		=> 'product',
    'post_status'		=> array('publish', 'draft'),
    'author'    		=> get_current_user_id(),
    'tax_query' 		=> array(
        array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'crowdfunding',
        ),
    ),
    'posts_per_page'    => $posts_per_page,
    'paged'             => $page_numb
);

$current_page = get_permalink();
$the_query = new WP_Query( $args );
?>

<div class="wpneo-content">
<div class="wpneo-form campaign-listing-page">


<?php if ( $the_query->have_posts() ) : global $post; $i = 1;
    while ( $the_query->have_posts() ) : $the_query->the_post();
        ob_start();
?>
        <div class="wpneo-listings-dashboard wpneo-shadow wpneo-padding15 wpneo-clearfix">
            
            <div class="wpneo-listing-img">
                <a href="<?php echo get_permalink(); ?>" title="<?php  echo get_the_title(); ?>"> <?php echo woocommerce_get_product_thumbnail(); ?></a>
                <div class="overlay">
                    <div>
                        <div>
                            <a class="wp-crowd-btn wp-crowd-btn-primary" href="<?php echo get_permalink(); ?>"><?php _e('View','wp-crowdfunding'); ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wpneo-listing-content clearfix">

                <div class="wpneo-admin-title float-left">
                    <!-- title -->
                    <h4><a href="<?php  echo get_permalink(); ?> "><?php echo get_the_title(); ?></a></h4>
                    
                    <!-- author -->
                    <p class="wpneo-author"><?php _e('by','wp-crowdfunding'); ?> 
                        <a href="<?php echo wpcf_function()->get_author_url( get_the_author_meta( 'user_login' ) ); ?>"><?php echo wpcf_function()->get_author_name(); ?></a>
                    </p>

                    <!-- location -->
                    <div class="wpneo-location">
                        <i class="wpneo-icon wpneo-icon-location fdffgdg"></i>
                        <div class="wpneo-meta-desc"><?php echo wpcf_function()->campaign_location(); ?></div>
                    </div>
                </div>
                <div class="wpneo-admin-location float-right">
                    <?php
                    $operation_btn = '';
                    $operation_btn .= '<div class="wpneo-fields-action">';
                        $page_id = get_option('wpneo_form_page_id');
                        if ($page_id != '') {
                            $permalink_edit     = add_query_arg( array( 'action' => 'edit', 'postid' => get_the_ID() ) , get_permalink($page_id) );
                            $permalink_update   = add_query_arg( array( 'page_type' => 'update', 'postid' => get_the_ID() ) , $current_page );
                            $operation_btn .= '<span><a href="'.$permalink_update.'">'.__("Update", "wp-crowdfunding").'</a></span>';
                            $operation_btn .= '<span><a href="' . $permalink_edit . '" class="wp-crowd-btn wp-crowd-btn-primary">' . __("Edit", "wp-crowdfunding") . '</a></span>';
                        }
                        
                    if (get_post_status() == 'draft'){
	                    $operation_btn .='<span class="wp-crowd-btn wpneo-campaign-status">['.__("Draft", "wp-crowdfunding").']</span>';
                    }
                    $operation_btn .= '</div>';
                    echo $operation_btn;
                    ?>
                </div>
                <div class="wpneo-clearfix"></div>
                <div class="wpneo-percent-rund-wrap">
                    
                    <!-- percent -->
                    <?php $raised_percent = wpcf_function()->get_fund_raised_percent_format(); ?>
                    <div class="crowdfound-pie-chart" data-size="60" data-percent="<?php echo $raised_percent; ?>">
                        <div class="sppb-chart-percent"><span><?php echo $raised_percent; ?></span></div>
                    </div>

                    <!-- fund-raised -->
                    <?php 
                    $raised_percent = wpcf_function()->get_fund_raised_percent_format();
                    $raised = 0;
                    $total_raised = wpcf_function()->get_total_fund();
                    if ($total_raised){
                        $raised = $total_raised;
                    }
                    ?>
                    <div class="crowdfound-fund-raised">
                        <div class="wpneo-meta-desc"><?php echo wc_price($raised); ?></div>
                        <div class="wpneo-meta-name"><?php _e('Fund Raised', 'wp-crowdfunding'); ?></div>
                    </div>

                    <!-- Funding Goal -->
                    <?php $funding_goal = get_post_meta($post->ID, '_nf_funding_goal', true); ?>
                    <div class="crowdfound-funding-goal">
                        <div class="wpneo-meta-desc"><?php echo wc_price( $funding_goal ); ?></div>
                        <div class="wpneo-meta-name"><?php _e('Funding Goal', 'wp-crowdfunding'); ?></div>
                    </div>

                    <!--  Days to go -->
                    <?php $days_remaining = apply_filters('date_expired_msg', __('0', 'wp-crowdfunding'));
                    if (wpcf_function()->get_date_remaining()){
                        $days_remaining = apply_filters('date_remaining_msg', __(wpcf_function()->get_date_remaining(), 'wp-crowdfunding'));
                    }

                    $end_method = get_post_meta(get_the_ID(), 'wpneo_campaign_end_method', true);

                    if ($end_method != 'never_end'){ ?>
                        <div class="crowdfound-time-remaining">
                            <?php if (wpcf_function()->is_campaign_started()){ ?>
                                <div class="wpneo-meta-desc"><?php echo wpcf_function()->get_date_remaining(); ?></div>
                                <div class="wpneo-meta-name"><?php _e( 'Days to go','wp-crowdfunding' ); ?></div>
                            <?php } else { ?>
                                <div class="wpneo-meta-desc"><?php echo wpcf_function()->days_until_launch(); ?></div>
                                <div class="wpneo-meta-name"><?php _e( 'Days Until Launch','wp-crowdfunding' ); ?></div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div><!-- wpneo-percent-rund-wrap -->
            </div><!-- wpneo-listing-content -->
            <?php do_action('wpcf_dashboard_campaign_loop_item_after_content'); ?>
            <div style="clear: both"></div>
        </div>
        <?php $i++;
        $html .= ob_get_clean();
    endwhile;
    wp_reset_postdata();
else :
    $html .= "<p>".__( 'Sorry, no Campaign Found.','wp-crowdfunding' )."</p>";
endif;

$html .= wpcf_function()->get_pagination( $page_numb , $the_query->max_num_pages );

$html .= '<div style="clear: both;"></div>';
$html .= '</div>';
$html .= '</div>';