<?php get_header(); ?>

 <div id="primary" class="site-content row">
	 <div id="content" role="main" class="container">
		<div class="span12 miolo">
			
			<div class="sid-int span4">
				<?php get_sidebar(); ?>
			</div>
			
			<article id="post-0" class="post error no-results not-found span7">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e( 'Ops... Não encontramos a página.', 'mobilize' ); ?></h1>
				</header>

				<div class="entry-content error-search">
					<p><?php _e( 'Parece que a página que você está tentando encontrar não existe, sugerimos que faça uma nova busca!', 'mobilize' ); ?></p>
					<?php get_search_form(); ?>
				</div><!-- .entry-content -->
			</article><!-- #post-0 -->
			
	    </div>
     </div><!-- #content -->	
 </div><!-- #primary -->
 
<?php get_footer(); ?>