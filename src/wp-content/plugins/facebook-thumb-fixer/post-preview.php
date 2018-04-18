<?php
$fb_URL 		= 'https://developers.facebook.com/docs/sharing/best-practices#images';
$image_data 	= wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "full" );
$image_width 	= $image_data[1];
$image_height 	= $image_data[2];
if ( has_post_thumbnail() ) { ?>
	<hr class="ftf-rule" />
	
	<p><?php _e( 'View an approximate preview of your post when shared on Facebook.', 'facebook-thumb-fixer' ); ?></p>
	<a class="open-ftf-preview">Preview</a>

	<hr class="ftf-rule" />
	
	<?php _e( 'If in doubt, try forcing Facebook to fetch your page with their debugging tool.', 'facebook-thumb-fixer' ); ?>
	<p><a href="https://developers.facebook.com/tools/debug/sharing/?q=<?php echo get_the_permalink(); ?>" target="_blank" class="debugger-button"><?php _e( 'Debug', 'facebook-thumb-fixer' ); ?></a></p>
	
	<?php if ($image_width < 600 || $image_height < 315) { ?>
		<p class="ftf-warning"><?php echo sprintf( __( '<strong>Oops! </strong>Your featured image dimensions are %1$s x %2$s which is smaller than the minimum 600 x 315 <a href="%3$s" target="_blank">recommended</a> by Facebook.', 'facebook-thumb-fixer' ), $image_width, $image_height, $fb_URL); ?></p>
	<?php } ?>

<?php } else { ?>

	<a class="no-thumb-set"><?php _e( 'Preview Not Available', 'facebook-thumb-fixer' ); ?></a>
	<p><?php _e( "You can't preview until you set a featured image and update this post/page.", "facebook-thumb-fixer" ); ?></p>
	
<?php } ?>

<div class="ftf-live-preview">
	<img src="<?php echo get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/images/preview-top.png'; ?>" />

	<div class="ftf-preview-details">

		<div class="overflow<?php if ($image_width < 600 || $image_height < 315) { echo " too-small"; } ?>">
			<?php echo the_post_thumbnail( $post->ID, 'full' ); ?>
		</div>

		<h1><?php echo the_title(); ?></h1>

		<p>
			<?php
				if ( has_excerpt( $post->ID ) ) {
					$excerpt = get_the_excerpt();
					$excerpt_chars = substr($excerpt, 0, 150);
					echo strip_tags($excerpt_chars);
				} else {
					$post = get_post($post->ID);
					$content = strip_shortcodes(apply_filters('get_the_content', $post->post_content));
					$content_chars = substr($content, 0, 150);
					echo strip_tags($content_chars);
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
