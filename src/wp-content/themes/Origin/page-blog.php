<?php
/*
Template Name: Blog Page
*/
?>
<?php
$et_ptemplate_settings = array();
$et_ptemplate_settings = maybe_unserialize( get_post_meta( get_the_ID(), 'et_ptemplate_settings', true ) );

$et_ptemplate_blogstyle = isset( $et_ptemplate_settings['et_ptemplate_blogstyle'] ) ? (bool) $et_ptemplate_settings['et_ptemplate_blogstyle'] : false;

$et_ptemplate_showthumb = isset( $et_ptemplate_settings['et_ptemplate_showthumb'] ) ? (bool) $et_ptemplate_settings['et_ptemplate_showthumb'] : false;

$blog_cats = isset( $et_ptemplate_settings['et_ptemplate_blogcats'] ) ? (array) $et_ptemplate_settings['et_ptemplate_blogcats'] : array();
$et_ptemplate_blog_perpage = isset( $et_ptemplate_settings['et_ptemplate_blog_perpage'] ) ? (int) $et_ptemplate_settings['et_ptemplate_blog_perpage'] : 10;
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

		<div id="et_pt_blog" class="responsive">
			<?php $cat_query = '';
			if ( !empty($blog_cats) ) $cat_query = '&cat=' . implode(",", $blog_cats);
			else echo '<!-- blog category is not selected -->'; ?>
			<?php
				$et_paged = is_front_page() ? get_query_var( 'page' ) : get_query_var( 'paged' );
			?>
			<?php query_posts("posts_per_page=$et_ptemplate_blog_perpage&paged=" . $et_paged . $cat_query); ?>
			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<div class="et_pt_blogentry clearfix">
					<h2 class="et_pt_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

					<p class="et_pt_blogmeta"><?php esc_html_e('Posted','Origin'); ?> <?php esc_html_e('by','Origin'); ?> <?php the_author_posts_link(); ?> <?php esc_html_e('on','Origin'); ?> <?php the_time(get_option('origin_date_format')) ?> <?php esc_html_e('in','Origin'); ?> <?php the_category(', ') ?> | <?php comments_popup_link(esc_html__('0 comments','Origin'), esc_html__('1 comment','Origin'), '% '.esc_html__('comments','Origin')); ?></p>

					<?php $thumb = '';
					$width = 184;
					$height = 184;
					$classtext = '';
					$titletext = get_the_title();

					$thumbnail = get_thumbnail($width,$height,$classtext,$titletext,$titletext);
					$thumb = $thumbnail["thumb"]; ?>

					<?php if ( $thumb <> '' && !$et_ptemplate_showthumb ) { ?>
						<div class="et_pt_thumb alignleft">
							<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
							<a href="<?php the_permalink(); ?>"><span class="overlay"></span></a>
						</div> <!-- end .thumb -->
					<?php }; ?>

					<?php if (!$et_ptemplate_blogstyle) { ?>
						<p><?php truncate_post(550);?></p>
						<a href="<?php the_permalink(); ?>" class="readmore"><span><?php esc_html_e('read more','Origin'); ?></span></a>
					<?php } else { ?>
						<?php
							global $more;
							$more = 0;
						?>
						<?php the_content(); ?>
					<?php } ?>
				</div> <!-- end .et_pt_blogentry -->

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
		</div> <!-- end #et_pt_blog -->

			<?php wp_link_pages( array('before' => '<p><strong>' . esc_attr__('Pages','Origin') . ':</strong> ', 'after' => '</p>', 'next_or_number' => 'number') ); ?>
			<?php edit_post_link(esc_attr__('Edit this page','Origin')); ?>
	</article> <!-- end .entry-content -->
<?php endwhile; // end of the loop. ?>

</div> <!-- #main-content -->

<?php get_template_part('includes/copyright', 'page'); ?>

<?php get_footer(); ?>