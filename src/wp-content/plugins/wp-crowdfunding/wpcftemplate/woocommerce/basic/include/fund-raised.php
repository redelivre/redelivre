<?php
defined( 'ABSPATH' ) || exit;
$end_method = get_post_meta(get_the_ID(), 'wpneo_campaign_end_method', true);
?>

<div class="campaign-funding-info">
    <ul>
        <li><p class="funding-amount"><?php echo wpcf_function()->price(wpcf_function()->total_goal(get_the_ID())); ?></p>
            <span class="info-text"><?php _e('Funding Goal', 'wp-crowdfunding') ?></span>
        </li>
        <li>
            <p class="funding-amount"><?php echo wpcf_function()->price(wpcf_function()->fund_raised()); ?></p>
            <span class="info-text"><?php _e('Funds Raised', 'wp-crowdfunding') ?></span>
        </li>
        <?php if ($end_method != 'never_end'){
            ?>
            <li>
                <?php if (wpcf_function()->is_campaign_started()){ ?>
                    <span class="info-text"><?php echo wpcf_function()->get_date_remaining().' '; _e( 'Days to go','wp-crowdfunding' ); ?></span>
                <?php } else { ?>
                    <span class="info-text"><?php echo wpcf_function()->days_until_launch().' '; _e( 'Days Until Launch','wp-crowdfunding' ); ?></span>
                <?php } ?>
            </li>
        <?php } ?>

        <li>
            <p class="funding-amount">
                <?php
                    if( $end_method == 'target_goal' ){
                        _e('Target Goal', 'wp-crowdfunding');
                    }else if( $end_method == 'target_date' ){
                        _e('Target Date', 'wp-crowdfunding');
                    }else if( $end_method == 'target_goal_and_date' ){
                        _e('Goal and Date', 'wp-crowdfunding');
                    }else{
                        _e('Campaign Never Ends', 'wp-crowdfunding');
                    }
                ?>
            </p>
            <span class="info-text"><?php _e('Campaign End Method', 'wp-crowdfunding') ?></span>
        </li>
    </ul>
</div>