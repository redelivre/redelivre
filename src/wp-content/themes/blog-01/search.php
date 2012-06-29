<?php get_header(); ?>
    <section id="main-section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	    
            <?php if ( have_posts()) : ?>
                
                <h3 class="pagetitle">
                <?php printf( __( 'Search Results for: %s', 'blog01' ), '<span>' . get_search_query() . '</span>' ); ?>
                </h3>
                
                <?php get_template_part('loop'); ?>
        
            <?php else : ?>
                <h3 class="pagetitle">
                <?php printf( __( 'Search Results for: %s', 'blog01' ), '<span>' . get_search_query() . '</span>' ); ?>
                </h3>
                <div class="post">
                    <p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'blog01' ); ?></p>
                    <?php get_search_form(); ?>
                </div>
                <!-- .post -->
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
