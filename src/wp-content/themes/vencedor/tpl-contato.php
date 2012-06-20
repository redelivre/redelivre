<?php get_header(); ?>
	<section id="main-section" class="wrap clearfix">
		<div id="content" class="col-8">
			<article id="post-<?php the_ID(); ?>" class="clearfix">                
				<h2>Entre em contato</h2>
				<div class="post-content">										
				    <?php if (function_exists('campanha_the_contact_form')) campanha_the_contact_form(); ?>
				</div>
				<!-- .post-content -->
				<footer class="post-footer clearfix">
					<?php get_template_part('interaction'); ?>
				</footer>
			</article>
			<!-- .post -->
		</div>
	    <!-- #content -->
		<aside id="sidebar" class="col-4 clearfix">
			<?php get_sidebar(); ?>
		</aside>
	    <!-- #sidebar -->			       
	</section>
    <!-- #main-section -->
<?php get_footer(); ?>
