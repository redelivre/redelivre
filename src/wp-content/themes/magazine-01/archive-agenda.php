<?php

global $paged;
$showingPast = ($paged > 0 || $_GET['eventos'] == 'passados');
?>
<?php get_header(); ?>
    <?php get_sidebar(); ?>
    <section id="main-section" class="col-8">
		<?php if ($showingPast): ?>
			<h2 class="clearfix">
				Eventos Passados
				<a class="view-events" href="<?php echo add_query_arg('eventos', ''); ?>">Ver próximos eventos &raquo;</a>
			</h2>
		<?php else: ?>
			<h2 class="clearfix">
				Próximos eventos
				<a class="view-events" href="<?php echo add_query_arg('eventos', 'passados'); ?>">Ver eventos passados &raquo;</a>
			</h2>
		<?php endif; ?>
        <?php if (have_posts()) : ?>
			
        <?php while (have_posts()) : the_post(); ?>
            <?php html::part('loop','agenda'); ?>
        <?php endwhile; ?>
            <?php if ($wp_query->max_num_pages > 1) : ?>
                <nav id="posts-nav" class="clearfix">
                    <div class="alignleft"><?php next_posts_link(__('&laquo; Previous posts', 'magazine01')); ?></div>
                    <div class="alignright"><?php previous_posts_link(__('Next posts &raquo;', 'magazine01')); ?></div>
                </nav>
                <!-- #posts-nav -->
            <?php endif; ?>					
        <?php else : ?>
            <p><?php _e('No results found.', 'magazine01'); ?></p>              
        <?php endif; ?>
    </section>
    <!-- #main-section -->
<?php get_footer(); ?>
