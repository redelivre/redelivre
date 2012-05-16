<?php get_header(); ?>
	
    <div class="wrap clearfix">
		<?php get_sidebar(); ?>
		<section id="main-section" class="col-8">			
			<?php if ( have_posts()) : while ( have_posts()) : the_post(); ?>			
				<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
					<header>                       
						<h1><a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a></h1>
						<p><?php edit_post_link( __( 'Edit', 'SLUG' ), '', '' ); ?></p>				
					</header>
					<div class="post-content clearfix">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<nav class="page-link">' . __( 'Pages:', 'SLUG' ), 'after' => '</nav>' ) ); ?>	
					</div>
					<!-- .post-content -->
				</article>
				<!-- .page -->
			<?php endwhile; ?>				
			<?php else : ?>
			   <p><?php _e('No results found.', 'SLUG'); ?></p>              
			<?php endif; ?>
		</section>
		<!-- #main-section -->	          
    </div>
    <!-- .wrap --> 
    
    
<?php get_footer(); ?>
