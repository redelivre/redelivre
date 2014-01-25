<?php
/*
Template Name: Gallery Page
*/
?>
<?php
$et_ptemplate_settings = array();
$et_ptemplate_settings = maybe_unserialize( get_post_meta(get_the_ID(),'et_ptemplate_settings',true) );

$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : (bool) $et_ptemplate_settings['et_fullwidthpage'];

$gallery_cats = isset( $et_ptemplate_settings['et_ptemplate_gallerycats'] ) ? $et_ptemplate_settings['et_ptemplate_gallerycats'] : array();
$et_ptemplate_gallery_perpage = isset( $et_ptemplate_settings['et_ptemplate_gallery_perpage'] ) ? (int) $et_ptemplate_settings['et_ptemplate_gallery_perpage'] : 12;
?>
<?php get_header(); ?>

<?php
	$et_settings = array();
	$et_settings = maybe_unserialize( get_post_meta( get_the_ID(), '_et_origin_settings', true ) );

	$big_thumbnail = isset( $et_settings['thumbnail'] ) ? $et_settings['thumbnail'] : '';

	if ( '' != $big_thumbnail ) echo '<div style="background-image: url(' . esc_url( $big_thumbnail ) . ');" id="big_thumbnail"></div>';
?>

<div id="main-content"<?php if ( '' == $big_thumbnail ) echo ' class="et-no-big-image"'; ?>>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-content clearfix' ); ?>>
		<?php
			$post_id = get_the_ID();
		?>

		<div class="main-title">
			<h1><?php the_title(); ?></h1>
		</div> <!-- .main-title -->

		<?php if ( ( has_post_thumbnail( $post_id ) || '' != get_post_meta( $post_id, 'Thumbnail', true ) ) && 'on' == et_get_option( 'origin_page_thumbnails', 'false' ) ) { ?>
			<div class="post-thumbnail">
			<?php
				if ( has_post_thumbnail( $post_id ) ) the_post_thumbnail( 'full' );
				else printf( '<img src="%1$s" alt="%2$s" />', esc_url( get_post_meta( $post_id, 'Thumbnail', true ) ), the_title_attribute( array( 'echo' => 0 ) ) );
			?>
			</div> 	<!-- end .post-thumbnail -->
		<?php } ?>

		<?php the_content(); ?>

		<div id="et_pt_gallery" class="clearfix responsive">
			<?php $gallery_query = '';
			if ( !empty($gallery_cats) ) $gallery_query = '&cat=' . implode(",", $gallery_cats);
			else echo '<!-- gallery category is not selected -->'; ?>
			<?php
				$et_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
			?>
			<?php query_posts("posts_per_page=$et_ptemplate_gallery_perpage&paged=" . $et_paged . $gallery_query); ?>
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<?php $width = 207;
				$height = 136;
				$titletext = get_the_title();

				$thumbnail = get_thumbnail($width,$height,'portfolio',$titletext,$titletext,true,'Portfolio');
				$thumb = $thumbnail["thumb"]; ?>

				<div class="et_pt_gallery_entry">
					<div class="et_pt_item_image">
						<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, 'portfolio'); ?>
						<span class="overlay"></span>

						<a class="zoom-icon fancybox" title="<?php the_title_attribute(); ?>" rel="gallery" href="<?php echo($thumbnail['fullpath']); ?>"><?php esc_html_e('Zoom in','Origin'); ?></a>
						<a class="more-icon" href="<?php the_permalink(); ?>"><?php esc_html_e('Read more','Origin'); ?></a>
					</div> <!-- end .et_pt_item_image -->
				</div> <!-- end .et_pt_gallery_entry -->

			<?php endwhile; ?>
				<div class="page-nav clearfix">
					<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); }
					else { ?>
						 <?php get_template_part('includes/navigation'); ?>
					<?php } ?>
				</div> <!-- end .entry -->
			<?php else : ?>
				<?php get_template_part('includes/no-results'); ?>
			<?php endif; wp_reset_query(); ?>
		</div> <!-- end #et_pt_gallery -->

		<?php wp_link_pages( array('before' => '<p><strong>' . esc_attr__('Pages','Origin') . ':</strong> ', 'after' => '</p>', 'next_or_number' => 'number') ); ?>
		<?php edit_post_link(esc_attr__('Edit this page','Origin')); ?>
	</article> <!-- end .entry-content -->
<?php endwhile; // end of the loop. ?>

</div> <!-- #main-content -->

<?php get_template_part('includes/copyright', 'page'); ?>

<?php get_footer(); ?>