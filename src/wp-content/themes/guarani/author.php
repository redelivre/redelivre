<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package guarani
 * @since guarani 1.0
 */

get_header(); ?>

		<section id="primary" class="content-area">
			<div id="content" class="site-content" role="main">

			<?php if ( have_posts() ) : the_post(); ?>

				<header class="archive-header cf">
					<div class="author-avatar">
						<?php echo get_avatar( get_the_author_meta( 'user_email' ), 128 ); ?>
					</div><!-- .author-avatar -->
					
					<h1 class="archive-title author-name vcard">
						<?php echo '<a class="url fn n" href="' . get_author_posts_url( get_the_author_meta( "ID" ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a>'; ?>
					</h1>
					
					<?php guarani_user_social( $author ); ?>
					
					<?php if ( $description = get_the_author_meta( 'description' ) ) : ?>
						<div class="archive-description author-description">
							<p><?php echo $description; ?></p>
						</div><!-- /author-description	-->
					<?php endif; ?>
					
				</header><!-- .archive-header -->

				<?php rewind_posts(); ?>
				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', get_post_format() ); ?>

				<?php endwhile; ?>

				<?php guarani_content_nav( 'nav-below' ); ?>

			<?php else : ?>

				<?php get_template_part( 'no-results', 'archive' ); ?>

			<?php endif; ?>

			</div><!-- #content .site-content -->
		</section><!-- #primary .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>