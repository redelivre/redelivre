<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php if ( ! empty( $preamble ) ) { ?><p><?php echo $preamble; ?></p><?php } ?>
<p class="tt_error <?php echo esc_attr( $additional_error_class ); ?>"><?php if ( $strong ) : ?><strong><?php endif; ?><?php echo $msg; ?><?php if ( $strong ) : ?><strong><?php endif; ?></p>