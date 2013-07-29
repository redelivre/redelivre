<?php
/**
 * @package guarani
 * @since guarani 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
	<figure class="entry-image">
		<?php the_post_thumbnail( 'highlight-single' ); ?>
		<?php if ( $thumb_caption = get_post( get_post_thumbnail_id() )->post_excerpt ) : ?>
			<figcaption><?php echo $thumb_caption; ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php endif; ?>
	
	<header class="entry-header">
		<h1 class="entry-title">
			<a href="<?php the_permalink(); ?>" title="<?php printf( __('Read, comment and share &ldquo;%s&rdquo;', 'f451'), the_title_attribute('echo=0') ); ?>" rel="bookmark">
	        	<?php the_title(); ?>
	        </a>
		</h1>
		<div class="entry-meta">
			<ul class="entry-agenda">
				<?php if ( $date_start = get_post_meta( $post->ID, '_data_inicial', true ) ) : ?>
				<li class="agenda-date">
					<div class="ico-calendar"></div>
					<?php
					$date_end = get_post_meta( $post->ID, '_data_final', true );
					if ( $date_end && $date_end != $date_start ) :
						/* translators: Initial & final date for the event */
						printf(
							'%1$s to %2$s',
							date( get_option( 'date_format' ), strtotime( $date_start ) ),
							date( get_option( 'date_format' ), strtotime( $date_end ) )
						);
					else :
						echo date( get_option( 'date_format' ), strtotime( $date_start ) );
					endif;
					?>
				</li>
				<?php endif; ?>
				
				<?php if ( $time = get_post_meta( $post->ID, '_horario', true ) ) : ?>
				<li class="agenda-time"><div class="ico-clock"></div><?php echo $time; ?></li>
				<?php endif; ?>
				
				<?php if ( $location = get_post_meta( $post->ID, '_onde', true ) ) : ?>
				<li class="agenda-location"><div class="ico-location"></div><?php echo $location; ?></li>
				<?php endif; ?>
				
				<?php if ( $link = get_post_meta( $post->ID, '_link', true ) ) : ?>
				<li class="agenda-link"><div class="ico-more"></div><a href="<?php echo esc_url( $link ); ?>"><?php _ex( 'More info', 'Agenda', 'guarani' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'guarani' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

	<footer class="entry-meta">
		<?php edit_post_link( __( 'Edit', 'guarani' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-meta -->
</article><!-- #post-<?php the_ID(); ?> -->
