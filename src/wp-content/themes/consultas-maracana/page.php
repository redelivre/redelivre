<?php get_header(); ?>	
	<section id="main-section" class="span-15 prepend-1 append-1">
		<?php if ( have_posts()) : the_post(); ?>
		
			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
				<header>					
					<h1><?php the_title();?></h1>					
				</header>
				<div class="post-content clearfix">		
					<div class="post-entry">
						<?php the_content(); ?>
					</div>
					<?php wp_link_pages( array( 'before' => '<nav class="page-link">' . __( 'Pages:', 'consulta' ), 'after' => '</nav>' ) ); ?>	
				</div>
				<!-- .post-content -->
				<footer class="clearfix">					
					<?php html::part('interaction'); ?>				
				</footer>
			</article>
			<!-- .post -->
					
		<?php else : ?>
		   <p>Página não encontrada</p>              
		<?php endif; ?>
	</section>
	<!-- #main-section -->
	<?php get_sidebar(); ?>
<?php get_footer(); ?>
