<?php
/**
 * Template Name: Custom Archive
 *
 * Displays most of the site content, including recent posts, date archives, categories
 * and post tags 
 *
 * @package Guarani
 * @since Guarani 1.0
 */

get_header(); ?>

		<div id="primary" class="content-area">
			<div id="content" class="site-content" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'page' ); ?>
					
					<div class="entry-archive">
						<div class="archive-item archive-recent-posts">
							<h3 class="icon-archive"><?php _ex( 'Recent posts', 'Custom Archive Template', 'guarani' ); ?></h3>
							<ul>
							<?php
							$recent_posts = wp_get_recent_posts( array( 'posts_per_page' => 5, 'post_status' => 'publish' ) );
							foreach( $recent_posts as $recent ) : ?>
								<li>
									<?php if ( ( $comments_numer = get_comments_number( $recent['ID'] ) ) > '0' ) : ?>
									<span class="archive-comments-number icon-comment"><?php echo get_comments_number( $recent['ID'] ); ?></span>
									<?php endif; ?>
									<a href="<?php echo get_permalink( $recent['ID'] ); ?>"><?php echo $recent['post_title']; ?></a>
								</li>
							<?php
							endforeach;
							?>
							</ul>
						</div>
						
						<div class="archive-general">
							<div class="archive-item archive-categories">
								<h3 class="icon-folder"><?php _ex( 'Categories', 'Custom Archive Template', 'guarani' ); ?></h3>
								<ul><?php wp_list_categories('title_li='); ?></ul>
							</div>
							
							<div class="archive-item archive-monthly">
								<h3 class="icon-folder"><?php _ex( 'By type', 'Custom Archive Template', 'guarani' ); ?></h3>
								<?php
								// Get all terms from 'post-format' taxonomy
								$post_format_terms = get_terms( 'post_format' );
	
								if ( $post_format_terms ) :
								?>
								<ul>
									<?php								
									foreach( $post_format_terms as $term ) :
										// Removes the 'post-format-' part from the slug
										$post_format_slug = substr( $term->slug, 12 );
										$post_format_name = $term->name;
										$post_format_link = get_post_format_link( $post_format_slug );
										$post_format_count = $term->count;	
									?>
										<li><a href="<?php echo $post_format_link; ?>"><?php echo $post_format_name; ?></a></li>
									<?php
									endforeach; 
									?>
								</ul>
								<?php endif; ?>
							</div>
							
							
							<div class="archive-item archive-monthly">
								<h3 class="icon-calendar"><?php _ex( 'By month', 'Custom Archive Template', 'guarani' ); ?></h3>
								<ul><?php wp_get_archives( array( 'type' => 'monthly', 'limit' => 12 ) ); ?></ul>
							</div>
							
							<div class="archive-item archive-yearly">
								<h3 class="icon-calendar"><?php _ex( 'By year', 'Custom Archive Template', 'guarani' ); ?></h3>
								<ul><?php wp_get_archives( array( 'type' => 'yearly' ) ); ?></ul>
							</div>
							
							<div class="archive-item archive-tags">
								<h3 class="icon-tags"><?php _ex( 'Post tags', 'Custom Archive Template', 'guarani' ); ?></h3>
								<?php wp_tag_cloud( array( 'format' => 'list', 'unit' => 'em', 'largest' => 1, 'smallest' => 1, 'orderby' => 'count', 'order' => 'DESC' ) ); ?>
							</div>
						</div>
						
					</div><!-- .entry-archive -->

					<?php comments_template( '', true ); ?>

				<?php endwhile; // end of the loop. ?>

			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>