<?php
	$post_id = get_the_ID();
	$single_postinfo = et_get_option( 'origin_postinfo2' );

	$et_settings = array();
	$et_settings = maybe_unserialize( get_post_meta( $post_id, '_et_origin_settings', true ) );

	$big_thumbnail = isset( $et_settings['thumbnail'] ) ? $et_settings['thumbnail'] : '';

	if ( '' != $big_thumbnail ) echo '<div style="background-image: url(' . esc_url( $big_thumbnail ) . ');" id="big_thumbnail"></div>';
?>

<div id="main-content"<?php if ( '' == $big_thumbnail ) echo ' class="et-no-big-image"'; ?>>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	<?php if (et_get_option('origin_integration_single_top') <> '' && et_get_option('origin_integrate_singletop_enable') == 'on') echo (et_get_option('origin_integration_single_top')); ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-content clearfix' ); ?>>
		<div class="main-title">
			<h1><?php the_title(); ?></h1>
		<?php
			if ( $single_postinfo ){
				echo '<p class="meta-info">';
				et_postinfo_meta( $single_postinfo, et_get_option('origin_date_format'), esc_html__('0 comments','Origin'), esc_html__('1 comment','Origin'), '% ' . esc_html__('comments','Origin') );
				echo '</p>';
			}
		?>
		</div> <!-- .main-title -->

	<?php if ( ( has_post_thumbnail( $post_id ) || '' != get_post_meta( $post_id, 'Thumbnail', true ) ) && 'on' == et_get_option( 'origin_thumbnails' ) ) { ?>
		<div class="post-thumbnail">
		<?php
			if ( has_post_thumbnail( $post_id ) ) the_post_thumbnail( 'full' );
			else printf( '<img src="%1$s" alt="%2$s" />', esc_attr( get_post_meta( $post_id, 'Thumbnail', true ) ), the_title_attribute( array( 'echo' => 0 ) ) );
		?>
		</div> 	<!-- end .post-thumbnail -->
	<?php } ?>

		<?php the_content(); ?>
		<?php wp_link_pages(array('before' => '<p><strong>'.esc_attr__('Pages','Origin').':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
		<?php edit_post_link(esc_attr__('Edit this page','Origin')); ?>

	</article> <!-- end .entry-content -->

	<?php if (et_get_option('origin_integration_single_bottom') <> '' && et_get_option('origin_integrate_singlebottom_enable') == 'on') echo(et_get_option('origin_integration_single_bottom')); ?>

	<?php
		if ( et_get_option('origin_468_enable') == 'on' ){
			if ( et_get_option('origin_468_adsense') <> '' ) echo( et_get_option('origin_468_adsense') );
			else { ?>
			   <a href="<?php echo esc_url(et_get_option('origin_468_url')); ?>"><img src="<?php echo esc_attr(et_get_option('origin_468_image')); ?>" alt="468 ad" class="foursixeight" /></a>
	<?php 	}
		}
	?>

	<?php
		if ( 'on' == et_get_option('origin_show_postcomments') ) comments_template('', true);
	?>
<?php endwhile; // end of the loop. ?>

</div> <!-- #main-content -->