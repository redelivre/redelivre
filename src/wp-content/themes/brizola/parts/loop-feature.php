<article id="post-<?php the_ID(); ?>" class="clearfix">
	<?php if ( has_post_thumbnail() ) : ?> 
		<?php the_post_thumbnail(); ?>				 
	<?php endif; ?>
	<div class="post-content">
		<header>                       
			<h1><a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a></h1>					
			<p>
				<a class="comments-number" href="<?php comments_link(); ?>"title="comentários"><?php comments_number('0','1','%');?></a>
				<?php _e('By', 'SLUG'); ?> <?php the_author_posts_link(); ?> <?php _e('on', 'SLUG'); ?> 
				<time class="post-time" datetime="<?php the_time('Y-m-d'); ?>" pubdate><?php the_time( get_option('date_format') ); ?></time>
				<?php edit_post_link( __( 'Edit', 'SLUG' ), '| ', '' ); ?>
			</p>
		</header>						
		<?php the_excerpt(); ?>					
		<footer class="clearfix">	
			<p class="taxonomies">			
				<span><?php _e('Categories', 'SLUG'); ?>:</span> <?php the_category(', ');?><br />
				<?php the_tags('<span>Tags:</span> ', ', '); ?>
			</p>		
		</footer>
	</div>
</article>
