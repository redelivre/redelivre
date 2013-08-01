<?php get_header(); ?>

	<div id="primary" class="site-content row">
		<div id="content" role="main" class="container">
			<div class="span12 miolo">
				<div class="span4 sid-int">
					<?php get_sidebar(); ?>
				</div>
				<?php the_post(); ?>
				<div class="quem-somos span7">
					<div class="cabecalho borda-cor-1">
						<h2><?php the_title(); ?></h2>
					</div>
						
					<?php the_content(); ?>
				
				</div>
			</div>	
        </div><!-- #content -->		
	</div><!-- #primary -->

<?php get_footer(); ?>