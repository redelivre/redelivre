<?php

global $paged;
$showingPast = ($paged > 0 || $_GET['eventos'] == 'passados');
?>
<?php get_header(); ?>
    <section id="main-section" class="wrap clearfix">
	    <div id="content" class="col-8">
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
	    
            <?php if ( have_posts()) : ?>
            
                <?php get_template_part('loop', 'agenda'); ?>
			
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
