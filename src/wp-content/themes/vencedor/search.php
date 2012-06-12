<?php get_header(); ?>
    <div id="section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	    
            <?php if ( have_posts()) : ?>
                
                <h3 class="pagetitle">
                <?php printf( __( 'Search Results for: %s', 'temavencedor' ), '<span>' . get_search_query() . '</span>' ); ?>
                </h3>
                
                <?php get_template_part('loop', 'search'); ?>
        
            <?php else : ?>
                <h3 class="pagetitle">
                <?php printf( __( 'Search Results for: %s', 'temavencedor' ), '<span>' . get_search_query() . '</span>' ); ?>
                </h3>
                <div class="post">
                    <p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'temavencedor' ); ?></p>
                    <?php get_search_form(); ?>
                </div>
                <!-- .post -->
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
