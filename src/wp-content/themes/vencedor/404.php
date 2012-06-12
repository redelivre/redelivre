<?php get_header(); ?>
    <div id="section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	        
            <h3 class="pagetitle">
            <?php _e( 'Page not found', 'temauolhost' ); ?>
            </h3>
            <div class="post">
                <p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', 'temauolhost' ); ?></p>            
                <?php get_search_form(); ?>
            </div>
            <!-- .post -->
	    </div>
	    <!-- #content -->
	    <div id="aside" class="col-4 clearfix">
		<?php get_sidebar(); ?>
	    </div>
	    <!-- #aside -->       
    </div>
    <!-- #section -->
<?php get_footer(); ?>
