<?php get_header(); ?>	
	<section id="main-section" class="span-15 prepend-1 append-1">
	<h1 class="subtitulo"><?php wp_title(); ?></h1>
		<?php if ( have_posts()) : while ( have_posts()) : the_post(); ?>
		
			<?php html::part('loop', 'index'); ?>

		<?php endwhile; ?>
		
			<?php global $wp_query; if ( $wp_query->max_num_pages > 1 ) : ?>
				<nav id="posts-nav" class="clearfix">
					<span class="alignleft"><?php echo next_posts_link(__i('&laquo; Artigos anteriores', 'blog: link para artigos anteriores')); ?></span>
					<span class="alignright"><?php echo previous_posts_link(__i('Próximos artigos &raquo;', 'blog: link para próximos artigos')); ?></span>
				</nav>
				<!-- #posts-nav -->
			<?php endif; ?>					
		<?php else : ?>
		   <p>Nenhum post encontrada</p>              
		<?php endif; ?>
	</section>
	<!-- #main-section -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
