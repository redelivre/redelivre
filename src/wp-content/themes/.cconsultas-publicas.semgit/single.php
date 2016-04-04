<?php get_header(); ?>	
	<section id="main-section" class="span-15 prepend-1 append-1">
		<?php if ( have_posts()) : the_post(); ?>
		
			<?php html::part('loop', 'single'); ?>
			<?php comments_template(); ?>
		    <!-- comentários -->

			<nav id="posts-nav" class="clearfix">
				<span class="alignleft"><?php next_post_link('%link', __i('&laquo; Artigo anterior', 'blog: link artigo anterior')); ?></span>
				<span class="alignright"><?php previous_post_link('%link', __i('Próximo artigo &raquo;', 'blog: link próximo artigo')); ?></span>
			</nav>
			<!-- #posts-nav -->					
		<?php else : ?>
		   <p>Nenhum post encontrado</p>              
		<?php endif; ?>
	</section>
	<!-- #main-section -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
