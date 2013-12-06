<?php get_header(); ?>
 <div id="primary" class="site-content row">
     <div id="content" role="main" class="container">
	
	    <div class="span12 miolo">

			<div class="sid-int span4">
				<?php get_sidebar(); ?>
			</div>
			
			<div class="lista-posts span7">
				<?php if(have_posts()) : while(have_posts()) : the_post(); ?>
					<!-- Aqui vai a lista de posts normal -->
					<div class="list-post borda-cor-1">
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<?php the_excerpt(); ?>
					</div>
				
				<?php endwhile; else : ?>
					<div class="entry-content error-search">
						<p><?php _e( 'Parece que a página que você está tentando encontrar não existe, sugerimos que faça uma nova busca!', 'mobilize' ); ?></p>
						<?php get_search_form(); ?>
					</div><!-- .entry-content -->
				
				<?php endif; ?>
			</div>
		
		</div>
		
		<nav class="pagination">
			<?php if (function_exists('pagination_function')) pagination_function(); ?> 
		</nav><!-- /.pagination -->
	   	
     </div><!-- #content -->	
 </div><!-- #primary -->
	
<?php get_footer(); ?>