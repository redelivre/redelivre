<?php get_header(); ?>
    <section id="main-section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	    
            <?php if ( have_posts()) : ?>
            
                <?php get_template_part('loop', 'index'); ?>
        
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
