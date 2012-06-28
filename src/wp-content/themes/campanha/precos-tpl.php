<?php
/*
Template Name: Preços
*/
?>
<?php get_header(); ?>
	<section id="precos">			
		<?php if ( have_posts()) : while ( have_posts()) : the_post(); ?>			
			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
				<header>                       
					<h1><?php the_title();?></h1>
					<p><?php edit_post_link( __( 'Edit', 'campanha' ), '', '' ); ?></p>				
				</header>
				<div class="post-content clearfix">
					<table class="clearfix">
						<thead class="clearfix">
							<th class="cel-4"></th>
							<th class="cel-2">1</th>
							<th class="cel-2">2</th>
							<th class="cel-2">3</th>
							<th class="cel-2">4</th>
							<th class="cel-2">5</th>
						</thead>
						<?php require_once(TEMPLATEPATH . '/includes/campaigns_prices.php'); ?>
					</table>
					<?php the_content(); ?>
				</div>
				<!-- .post-content -->
			</article>
			<!-- .page -->
		<?php endwhile; ?>				
		<?php else : ?>
		   <p><?php _e('No results found.', 'campanha'); ?></p>              
		<?php endif; ?>
	</section>
	<!-- #main-section -->
<?php get_footer(); ?>
