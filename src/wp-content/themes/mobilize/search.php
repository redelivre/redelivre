<?php get_header(); ?>

 <div id="primary" class="site-content row">
    <div id="content" role="main" class="container">
		<div class="span12 miolo">

			<div class="sid-int span4">
				<?php get_sidebar(); ?>
			</div>
			
			<div class="lista-posts span7">
			
			<div class="search-cab borda-cor-1">
				<h1>Resultados da busca para: <?php echo wp_strip_all_tags($s); ?></h1>
			</div>
			
				<?php if(have_posts()) : while(have_posts()) : the_post(); ?>
					<!-- Aqui vai a lista de posts normal -->
					<div class="list-post borda-cor-1">
						<h2><?php the_title(); ?></h2>
						<?php the_excerpt(); ?>
					</div>
				
				<?php endwhile; else : ?>
					<!--  Aqui vai o que aparece quando ele não encontra nenhum post -->
					<h3>Nenhum arquivo encontrado.</h3>
				
				<?php endif; ?>
			</div>
		</div>
    </div><!-- #content -->	
 </div><!-- #primary -->
	
	
	
<?php get_footer(); ?>