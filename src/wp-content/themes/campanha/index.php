<?php get_header(); ?>

<div class="wrap clearfix">
    <?php get_sidebar(); ?>
    <section id="main-section" class="col-8">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <?php html::part('loop'); ?>
            <?php endwhile; ?>
            <?php if ($wp_query->max_num_pages > 1) : ?>
                <nav id="posts-nav" class="clearfix">
                    <div class="alignleft"><?php next_posts_link(__('&laquo; Previous posts', 'campanha')); ?></div>
                    <div class="alignright"><?php previous_posts_link(__('Next posts &raquo;', 'campanha')); ?></div>
                </nav>
                <!-- #posts-nav -->
            <?php endif; ?>					
        <?php else : ?>
            <p><?php _e('No results found.', 'campanha'); ?></p>              
        <?php endif; ?>
    </section>
    <!-- #main-section -->	          
</div>


<!-- .wrap --> 

<?php get_footer(); ?>
