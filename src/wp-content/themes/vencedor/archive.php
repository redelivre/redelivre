<?php get_header(); ?>
    <div id="section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	    
            <?php if ( have_posts()) : ?>
                
                <h3 class="pagetitle">
                <?php if ( is_day() ) : ?>
                    <?php printf( __( 'Daily Archives: <span>%s</span>', 'temauolhost' ), get_the_date() ); ?>
                <?php elseif ( is_month() ) : ?>
                    <?php printf( __( 'Monthly Archives: <span>%s</span>', 'temauolhost' ), get_the_date('F Y') ); ?>
                <?php elseif ( is_year() ) : ?>
                    <?php printf( __( 'Yearly Archives: <span>%s</span>', 'temauolhost' ), get_the_date('Y') ); ?>
                <?php elseif (is_category() || is_tag() || is_tax() ) : ?>
                    <?php $term = $wp_query->get_queried_object(); ?>
                    <?php printf( __( 'Archives for "%s" ', 'temauolhost' ), $term->name ); ?>
                <?php elseif (is_author()): ?>
                    <?php $author = $wp_query->get_queried_object(); ?>
                    <?php printf( __( 'Posts by %s', 'temauolhost' ), $author->display_name ); ?>
                <?php else : ?>
                    <?php _e( 'Blog Archives', 'temauolhost' ); ?>
                <?php endif; ?>
                </h3>
                
                <?php get_template_part('loop', 'archive'); ?>
        
            <?php else : ?>
                <p class="post"><?php _e('No results found.', 'temauolhost'); ?></p>              
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
