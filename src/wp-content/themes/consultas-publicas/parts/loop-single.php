<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
	<header>
		<p class="bottom">			
			Por <?php the_author_posts_link(); ?> em
			<time class="post-time" datetime="<?php the_time('Y-m-d'); ?>" pubdate><?php the_time( get_option('date_format') ); ?></time>
		</p>
		<h1><a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a></h1>					
	</header>
	<div class="post-content clearfix">		
		<div class="post-entry">
			<?php the_content(); ?>
		</div>
		<?php wp_link_pages( array( 'before' => '<nav class="page-link">' . __( 'Pages:', 'consulta' ), 'after' => '</nav>' ) ); ?>	
	</div>
	<!-- .post-content -->
	<footer class="clearfix">
		<p class="taxonomies">
			<span><?php _e('Categories', 'consulta'); ?>:</span> <?php the_category(', ');?><br />
			<?php the_tags('<span>Tags:</span> ', ', '); ?>
		</p>
		<?php html::part('interaction'); ?>				
	</footer>
</article>
<!-- .post -->
    			
