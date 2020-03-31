<?php
defined( 'ABSPATH' ) || exit;
global $post;
$campaign_id = $post->ID;
?>
<div id="campaign_loved_html">
    <?php wpcf_function()->campaign_loved(); ?>
</div>
