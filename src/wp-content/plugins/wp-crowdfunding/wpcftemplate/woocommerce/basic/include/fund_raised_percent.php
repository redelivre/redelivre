<?php
defined( 'ABSPATH' ) || exit;

$raised_percent = wpcf_function()->get_fund_raised_percent_format();
?>
<div class="wpneo-raised-percent">
    <div class="wpneo-meta-name"><?php _e('Raised Percent', 'wp-crowdfunding'); ?> :</div>
    <div class="wpneo-meta-desc" ><?php echo $raised_percent; ?></div>
</div>
<div class="wpneo-raised-bar">
    <div id="neo-progressbar">
        <?php $css_width = wpcf_function()->get_raised_percent(); if( $css_width >= 100 ){ $css_width = 100; } ?>
        <div style="width: <?php echo $css_width; ?>%"></div>
    </div>
</div>