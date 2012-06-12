<?php get_header(); ?>
	<div id="section" class="wrap clearfix">
		<div id="content" class="col-8">
		<?php if ( have_posts()) : the_post(); ?>
			<div id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>                
				<h2><?php the_title();?></h2>
				<div class="post-content">										
				    <?php the_content(); ?>
				    <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'temauolhost' ), 'after' => '</div>' ) ); ?>			
				</div>
				<!-- .post-content -->
				<?php comments_template(); ?>
				<!-- comentários -->
			</div>
			<!-- .post -->
			<?php else : ?>
			<p class="post"><?php _e('No results found.', 'temauolhost'); ?></p>              
		<?php endif; ?> 
		</div>
	    <!-- #content -->
		<div id="aside" class="col-4 clearfix">
			<?php get_sidebar(); ?>
		</div><!-- #aside -->			       
	</div><!-- #section -->
<?php get_footer(); ?>
