<?php
/**
 * The Template for displaying all posts from the type Agenda.
 *
 * @package Mobilize
 * @since Mobilize 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content row">
		<div id="content" class="container" role="main">
			<div class="span12 miolo">
				<div class="span4 sid-int">
					<?php get_sidebar(); ?>
				</div>
		
					<div class="span7">
					<?php while ( have_posts() ) : the_post(); ?>
					
						
						<?php get_template_part( 'content', 'agenda' ); ?>
		
						
		
					<?php endwhile; // end of the loop. ?>
					</div>
				</div>
			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
    
