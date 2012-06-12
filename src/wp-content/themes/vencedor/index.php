<?php get_header(); ?>
    <div id="section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	    
            <?php if ( have_posts()) : ?>
            
                <?php get_template_part('loop', 'index'); ?>
        
            <?php else : ?>
                    <p class="post"><?php _e('No results found.', 'temavencedor'); ?></p>              
            <?php endif; ?>
        
	    </div>
	    <!-- #content -->
	    <div id="aside" class="col-4 clearfix">
		<?php get_sidebar(); ?>
	    </div>
	    <!-- #aside -->       
    </div>
    <!-- #section -->
<?php get_footer(); ?>
