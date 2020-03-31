<?php
defined( 'ABSPATH' ) || exit;
global $post;
$saved_campaign_update = get_post_meta($post->ID, 'wpneo_campaign_updates', true);
$saved_campaign_update_a = json_decode($saved_campaign_update, true);
?>
<div class='campaign_update_wrapper'>
<?php if (is_array($saved_campaign_update_a)) {
    if (count($saved_campaign_update_a) > 0) {
        ?>
        <ul class="wpneo-crowdfunding-update">
            <?php  foreach ($saved_campaign_update_a as $key => $value) { ?>
                <li>
                    <span class="round-circle"></span>
                    <h4><?php echo stripslashes($value['date']); ?></h4>
                    <p class="wpneo-crowdfunding-update-title"><?php echo stripslashes($value['title']); ?></p>
                    <p>
                        <?php
                        $upate_content = apply_filters('the_content', stripslashes($value['details']));
                        echo wpautop($upate_content); ?>
                    </p>
                </li>
            <?php }  //the_content(); ?>
        </ul>
        <?php
    }
} ?>
</div>
