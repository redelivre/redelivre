<?php
/*
Template Name: Sitemap Page
*/
?>
<?php
$et_ptemplate_settings = array();
$et_ptemplate_settings = maybe_unserialize( get_post_meta(get_the_ID(),'et_ptemplate_settings',true) );

$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;
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

		<div id="sitemap" class="responsive">
			<div class="sitemap-col">
				<h2><?php esc_html_e('Pages','Origin'); ?></h2>
				<ul id="sitemap-pages"><?php wp_list_pages('title_li='); ?></ul>
			</div> <!-- end .sitemap-col -->

			<div class="sitemap-col">
				<h2><?php esc_html_e('Categories','Origin'); ?></h2>
				<ul id="sitemap-categories"><?php wp_list_categories('title_li='); ?></ul>
			</div> <!-- end .sitemap-col -->

			<div class="sitemap-col<?php if (!$fullwidth) echo ' last'; ?>">
				<h2><?php esc_html_e('Tags','Origin'); ?></h2>
				<ul id="sitemap-tags">
					<?php $tags = get_tags();
					if ($tags) {
						foreach ($tags as $tag) {
							echo '<li><a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '">' . esc_html( $tag->name ) . '</a></li> ';
						}
					} ?>
				</ul>
			</div> <!-- end .sitemap-col -->

			<?php if (!$fullwidth) { ?>
				<div class="clear"></div>
			<?php } ?>

			<div class="sitemap-col<?php if ($fullwidth) echo ' last'; ?>">
				<h2><?php esc_html_e('Authors','Origin'); ?></h2>
				<ul id="sitemap-authors" ><?php wp_list_authors('show_fullname=1&optioncount=1&exclude_admin=0'); ?></ul>
			</div> <!-- end .sitemap-col -->
		</div> <!-- end #sitemap -->

		<div class="clear"></div>

		<?php wp_link_pages( array('before' => '<p><strong>' . esc_attr__('Pages','Origin') . ':</strong> ', 'after' => '</p>', 'next_or_number' => 'number') ); ?>
		<?php edit_post_link(esc_attr__('Edit this page','Origin')); ?>
	</article> <!-- end .entry-content -->
<?php endwhile; // end of the loop. ?>

</div> <!-- #main-content -->

<?php get_template_part('includes/copyright', 'page'); ?>

<?php get_footer(); ?>