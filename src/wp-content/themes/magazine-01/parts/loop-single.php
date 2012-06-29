<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
	<header>                       
		<h1><a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a></h1>					
		<p> 
			<time class="post-time" datetime="<?php the_time('Y-m-d'); ?>" pubdate><?php the_time( get_option('date_format') ); ?></time>
			<?php edit_post_link( __( 'Edit', 'magazine01' ), '| ', '' ); ?>
		</p>
	</header>
	<div class="post-content clearfix">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<nav class="page-link">' . __( 'Pages:', 'magazine01' ), 'after' => '</nav>' ) ); ?>		
	</div>
	<!-- .post-content -->
	<footer class="clearfix">
		<?php html::part("interaction");?>
		<p class="taxonomies">
			<span><?php _e('Categories', 'magazine01'); ?>:</span> <?php the_category(', ');?><br />
			<?php the_tags('<span>Tags:</span> ', ', '); ?>
		</p>		
	</footer>		        
	<?php comments_template(); ?>
	<!-- comentários -->
</article>
<!-- .post -->
