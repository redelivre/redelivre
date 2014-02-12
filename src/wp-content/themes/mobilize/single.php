<?php get_header(); ?>

	<div id="primary" class="site-content row">
		<div id="content" role="main" class="container">
			<div class="span12 miolo">
				<div class="span4 sid-int">
					<?php get_sidebar(); ?>
				</div>
				<?php if (have_posts()) : while(have_posts()) : ?>
				<?php the_post(); ?>
				<div class="quem-somos span7">
					<div class="cabecalho borda-cor-1">
						<h1><?php the_title(); ?></h1>
						<p><?php _e('Publicado em', '_mobilize'); ?> <?php the_date() ?> <?php _e('por', '_mobilize'); ?> <?php the_author()?></p>
					</div>
						
					<?php the_content(); ?>
					
					<p><?php the_tags(); ?></p>
					<div class="row">
						<div class="compartilhamento span7">
							<p class="borda-cor-1">
								<?php _e('Compartilhe', '_mobilize'); ?>
								<span><?php _e('este post nas redes sociais', '_mobilize'); ?></span>
							</p>
							
							<a class="share share-twitter span2" title="<?php _e( 'Twitter', '_mobilize' ); ?>" href="http://twitter.com/intent/tweet?original_referer=<?php the_permalink(); ?>&text=<?php echo $post->post_title; ?>&url=<?php echo $post_permalink; ?>" rel="nofollow" target="_blank"><span><?php _e( 'Twitter', '_mobilize' ); ?></span></a>
				    		<a class="share share-facebook span2" title="<?php _e( 'Facebook', '_mobilize' ); ?>" href="https://www.facebook.com/sharer.php?u=<?php the_permalink() ?>" rel="nofollow" target="_blank"><span><?php _e( 'Facebook', '_mobilize' ); ?></span></a>
				    		<a class="share share-googleplus span2" title="<?php _e( 'Google+', '_mobilize' ); ?>" href="https://plus.google.com/share?url=<?php the_permalink(); ?>" rel="nofollow" target="_blank"><span><?php _e( 'Google+', '_mobilize' ); ?></span></a>
						</div>
						
						<div class="comments span7">
							<?php comments_template(); ?>
						</div>
						
					</div>
				</div>
				<?php endwhile; endif; ?>
			</div>	
				
        </div><!-- #content -->	
	</div><!-- #primary -->
<?php get_footer(); ?>