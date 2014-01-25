<?php get_header(); ?>

<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
	<?php
		$index_postinfo = et_get_option( 'origin_postinfo1' );

		$thumb = '';
		$width = (int) apply_filters( 'et_entry_image_width', 640 );
		$height = (int) apply_filters( 'et_entry_image_height', 480 );
		$classtext = '';
		$titletext = get_the_title();
		$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Entryimage' );
		$thumb = $thumbnail["thumb"];
	?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry-image' ); ?>>
			<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext ); ?>
			<div class="image-info">
				<a href="<?php the_permalink(); ?>" class="image-link"><?php _e( 'Read more', 'Origin' ); ?></a>
				<div class="title">
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php
					if ( $index_postinfo ){
						echo '<p class="meta-info">';
						et_postinfo_meta( $index_postinfo, et_get_option('origin_date_format'), esc_html__('0 comments','Origin'), esc_html__('1 comment','Origin'), '% ' . esc_html__('comments','Origin') );
						echo '</p>';
					}
				?>
				</div> <!-- .title -->
				<div class="description">
					<p><?php truncate_post( 65 ); ?></p>
				</div> <!-- .description -->
				<a href="<?php the_permalink(); ?>" class="readmore"><?php _e( 'Read more', 'Origin' ); ?><span></span></a>
			</div> <!-- .image-info -->
		</article> <!-- .entry-image -->
	<?php endwhile; ?>

	<?php get_template_part( 'includes/navigation', 'index' ); ?>
<?php else : ?>
	<?php get_template_part( 'includes/no-results', 'index' ); ?>
<?php endif; // end have_posts() check ?>

<?php get_footer(); ?>