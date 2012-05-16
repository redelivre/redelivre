<?php get_header(); ?>
	
    <div class="wrap clearfix">
		<?php get_sidebar(); ?>
		<section id="main-section" class="col-8">			
			<?php if ( have_posts()) : while ( have_posts()) : the_post(); ?>
			
				<?php html::part('loop', 'single'); ?>

			<?php endwhile; ?>
				<?php if ( $wp_query->max_num_pages > 1 ) : ?>
					<nav id="posts-nav" class="clearfix">
						<div class="alignleft"><?php previous_post_link('%link', __('&laquo; Previous post', 'SLUG')); ?></div>
						<div class="alignright"><?php next_post_link('%link', __('Next post &raquo;', 'SLUG')); ?></div>
					</nav>
					<!-- #posts-nav -->
				<?php endif; ?>					
			<?php else : ?>
			   <p><?php _e('No results found.', 'SLUG'); ?></p>              
			<?php endif; ?>
		</section>
		<!-- #main-section -->	          
    </div>
    <!-- .wrap --> 
    
    
<?php get_footer(); ?>
