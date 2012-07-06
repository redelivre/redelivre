<?php get_header(); ?>
		<?php get_sidebar(); ?>
		<section id="main-section" class="col-8">			
			<?php if ( have_posts()) : the_post(); ?>
				<?php $meta = get_metadata('post', $post->ID); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
					<header>                       
						<h1><?php the_title();?></h1>					
					</header>
					<div class="post-content clearfix">
						<?php if (has_post_thumbnail()) : ?> 
							<?php the_post_thumbnail('medium'); ?>				 
						<?php endif; ?>
						<?php the_content(); ?>
						<div class="event-info clear">
							<h3>Informações do Evento</h3>
							<?php
							if ($meta['_data_inicial'][0]) echo '<p class="bottom"><span class="label">Data Inicial:</span> ', date('d/m/Y', strtotime($meta['_data_inicial'][0])), '</p>';
                            if ($meta['_data_final'][0]) echo '<p class="bottom"><span class="label">Data Final:</span> ', date('d/m/Y', strtotime($meta['_data_final'][0])), '</p>';
                            if ($meta['_horario'][0]) echo '<p class="bottom"><span class="label">Horário:</span> ', $meta['_horario'][0], '</p>';
                            if ($meta['_onde'][0]) echo '<p class="bottom"><span class="label">Local:</span> ', $meta['_onde'][0], '</p>';
                            if ($meta['_link'][0]) echo '<p class="bottom"><span class="label">Site:</span> ', $meta['_link'][0], '</p>';
							?>
						</div>		
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
