<?php get_header(); ?>
    <section id="main-section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	        
            <h3 class="pagetitle">
            <?php _e( 'Page not found', 'blog01' ); ?>
            </h3>
            <article class="post">
                <p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', 'blog01' ); ?></p>            
                <?php get_search_form(); ?>
            </article>
            <!-- .post -->
	    </div>
	    <!-- #content -->
	    <aside id="sidebar" class="col-4 clearfix">
		<?php get_sidebar(); ?>
	    </aside>
	    <!-- #sidebar -->       
    </section>
    <!-- #main-section -->
<?php get_footer(); ?>
