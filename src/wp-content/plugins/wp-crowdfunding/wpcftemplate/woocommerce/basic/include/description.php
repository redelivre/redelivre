<?php
defined( 'ABSPATH' ) || exit;
global $post;
if ( ! $post->post_excerpt ) {
    return;
}
?>
<div style="clear: both"></div>
<div class="wpneo-single-short-description">
    <h2><?php _e('Short Story','wp-crowdfunding'); ?></h2>
    <div itemprop="description">
        <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
    </div>
</div>
