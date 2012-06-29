<?php get_header(); ?>
	<section id="main-section" class="wrap clearfix">
		<div id="content" class="col-8">
		<?php if ( have_posts()) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>                
				<h2><?php the_title();?></h2>
				<div class="post-content">										
				    <?php the_content(); ?>
				    <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'blog01' ), 'after' => '</div>' ) ); ?>			
				</div>
				<!-- .post-content -->
				<footer class="post-footer clearfix">
					<?php get_template_part('interaction'); ?>
				</footer>
				<?php comments_template(); ?>
				<!-- comentários -->
			</article>
			<!-- .post -->
			<?php else : ?>
			<p class="post"><?php _e('No results found.', 'blog01'); ?></p>              
		<?php endif; ?> 
		</div>
	    <!-- #content -->
		<aside id="sidebar" class="col-4 clearfix">
			<?php get_sidebar(); ?>
		</aside>
	    <!-- #sidebar -->			       
	</section>
    <!-- #main-section -->
<?php get_footer(); ?>
