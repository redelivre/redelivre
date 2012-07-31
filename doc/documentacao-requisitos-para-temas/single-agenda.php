<?php get_header(); ?>
    <section id="main-section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	    <?php if ( have_posts()) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>
			<header>
				<h2><?php the_title();?></h2>
            </header>
		    <div class="post-content">			    
				<?php if (has_post_thumbnail()) : ?> 
					<?php the_post_thumbnail('medium'); ?>				 
				<?php endif; ?>
				<?php the_content(); ?>
				<?php the_event_box(); ?>
            </div>
		    <!-- .post-content -->
		    <footer class="post-footer clearfix">
				<?php get_template_part('interaction'); ?>
		    </footer>
		    <!-- comentários -->
		</article>
		<!-- .post -->
		<nav class="navigation" class="clearfix">
			<a href="<?php bloginfo('url'); ?>/agenda?eventos">Ver próximos eventos</a> | <a href="<?php bloginfo('url'); ?>/agenda?eventos=passados">Ver eventos passados</a>
		</nav>				
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
