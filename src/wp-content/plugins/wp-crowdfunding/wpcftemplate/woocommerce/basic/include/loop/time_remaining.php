<?php
defined( 'ABSPATH' ) || exit;
$days_remaining = apply_filters('date_expired_msg', __('0', 'wp-crowdfunding'));
if (wpcf_function()->get_date_remaining()){
    $days_remaining = apply_filters('date_remaining_msg', __(wpcf_function()->get_date_remaining(), 'wp-crowdfunding'));
}

$end_method = get_post_meta(get_the_ID(), 'wpneo_campaign_end_method', true);

if ($end_method != 'never_end'){ ?>
    <div class="wpneo-time-remaining">
        <?php if (wpcf_function()->is_campaign_started()){ ?>
            <div class="wpneo-meta-desc"><?php echo wpcf_function()->get_date_remaining(); ?></div>
            <div class="wpneo-meta-name float-left"><?php _e( 'Days to go','wp-crowdfunding' ); ?></div>
        <?php } else { ?>
            <div class="wpneo-meta-desc"><?php echo wpcf_function()->days_until_launch(); ?></div>
            <div class="wpneo-meta-name float-left"><?php _e( 'Days Until Launch','wp-crowdfunding' ); ?></div>
        <?php } ?>
    </div>
<?php } ?>