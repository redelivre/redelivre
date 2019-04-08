<?php
/*
Template Name: Home
*/
?>
<?php get_header(); ?>
	<section id="main-section" class="span-15 prepend-1 append-1">
		<article class="destaque-principal post">
			<h3 class="subtitulo"><?php _oi('Destaque', 'Home: Chapéu do destaque maior'); ?></h3>
            <?php echo new WidgetUniquePost('unique-post-1', 'home-highlight'); ?>
		</article>
		<!-- .destaque-principal -->
		<h3 class="subtitulo"><?php _oi('Veja também', 'Home: Chapéu dos destaques menores'); ?></h3>
		<article class="destaque post span-7 append-1"><!-- ATENÇÃO! O outro destaque não tem a classe "append-1", mas tem a classe "last"!!!! -->
			<?php echo new WidgetUniquePost('unique-post-2', 'home-secondary-highlight'); ?>
		</article>
		<article class="destaque post span-7 last">
            <?php echo new WidgetUniquePost('unique-post-3', 'home-secondary-highlight'); ?>
		</article>
		
	</section>
	<!-- #main-section -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
