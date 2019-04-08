<?php $post_type_object = get_post_type_object( 'object' ); ?>
<?php get_header(); ?>	
	<section id="main-section" class="span-15 prepend-1 append-1">
        <?php if ( have_posts()) : the_post(); ?>
		
			<?php html::part('loop', 'single-meta'); ?>
			
			
            <?php comments_template('/comments-objects.php'); ?>
		    <!-- comentários -->
            
            <!--
			<nav id="posts-nav" class="clearfix">
				<span class="alignleft"><?php next_post_link('%link', __('&laquo; Previous post', 'consulta')); ?></span>
				<span class="alignright"><?php previous_post_link('%link', __('Next post &raquo;', 'consulta')); ?></span>
			</nav>
            -->
			
            <!-- #posts-nav -->					
		<?php else : ?>
		   <p><?php echo $post_type_object->labels->not_found; ?></p>             
		<?php endif; ?>
	</section>
	<!-- #main-section -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
