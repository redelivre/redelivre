<br />
<p><a class="open-ftf-preview"><?php _e( 'Homepage Preview', 'facebook-thumb-fixer' ); ?></a> <a href="https://developers.facebook.com/tools/debug/sharing/?q=<?php echo get_bloginfo( 'url' ); ?>" target="_blank" class="homepage-debug"><?php _e( 'Debug', 'facebook-thumb-fixer' ); ?></a></p>
<p class="description"><?php _e( 'View an approximate preview of your homepage when shared on Facebook.', 'facebook-thumb-fixer' ); ?></p>

<div class="ftf-live-preview">
	<img src="<?php echo plugins_url('images/', __FILE__ ) . 'preview-top.png'; ?>" />

	<div class="ftf-preview-details">

		<?php $fbt_value = get_option( 'default_fb_thumb'); ?>
		<div class="overflow home-thumb-image">
			<img src="<?php if($fbt_value) { esc_attr_e( $fbt_value ); } ?>" />
		</div>

		<h1><?php echo get_bloginfo( 'name' ); ?></h1>

		<p>
			<?php
				$description = get_bloginfo( 'description' );
				if ( $description ) {
					$excerpt_chars = substr($description, 0, 150);
					echo strip_tags($excerpt_chars);
				}
			?>
		</p>
		<span class="ftf-domain"><?php echo $_SERVER['SERVER_NAME']; ?></span>
	</div>
</div>

<div class="ftf-mask"></div>

<script>
	// Popup
	jQuery(function($) {
		$('.open-ftf-preview').click(function(){
			$(".ftf-live-preview").toggleClass("show-ftf-live-preview");
			$(".ftf-mask").toggleClass("show-ftf-mask");
		});
		$('.ftf-mask').click(function(){
			$(this).toggleClass("show-ftf-mask");
			$(".ftf-live-preview").toggleClass("show-ftf-live-preview");
		});
	});
</script>
