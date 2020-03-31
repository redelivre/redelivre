<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wpneo-listing-img">
    <a href="<?php echo get_permalink(); ?>" title="<?php  echo get_the_title(); ?>"> <?php echo woocommerce_get_product_thumbnail(); ?></a>
    <div class="overlay">
		<div>
			<div>
				<a href="<?php echo get_permalink(); ?>"><?php _e('View Campaign','wp-crowdfunding'); ?></a>
			</div>
		</div>
	</div>
</div>