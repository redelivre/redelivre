<?php get_header(); ?>
		<?php get_sidebar(); ?>
		<section id="main-section" class="col-8">			
			<?php if ( have_posts()) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
					<header>                       
						<h1><?php the_title();?></h1>					
					</header>
					<div class="post-content clearfix">
						<?php if (has_post_thumbnail()) : ?> 
							<?php the_post_thumbnail('medium'); ?>				 
						<?php endif; ?>
						<?php the_content(); ?>
						<?php the_event_box(); ?>
					</div>
					<!-- .post-content -->
					<footer class="clearfix">
						<?php html::part("interaction");?>							
					</footer>
				</article>
				<!-- .post -->
				<nav id="posts-nav" class="clearfix">
					<a href="<?php bloginfo('url'); ?>/agenda?eventos">Ver próximos eventos</a> | <a href="<?php bloginfo('url'); ?>/agenda?eventos=passados">Ver eventos passados</a>
				</nav>		
			<?php else : ?>
			   <p><?php _e('No results found.', 'magazine01'); ?></p>              
			<?php endif; ?>
		</section>
		<!-- #main-section -->
<?php get_footer(); ?>
