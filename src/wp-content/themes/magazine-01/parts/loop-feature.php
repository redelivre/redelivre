<article id="post-<?php the_ID(); ?>" <?php post_class('home-feature clearfix');?>>
	<?php if ( has_post_thumbnail() ) : ?> 
		<?php the_post_thumbnail('home-feature'); ?>				 
		<div class="post-content has-thumbnail">
	<?php else: ?>
		<div class="post-content">
	<?php endif; ?>
		<header>                       
			<h1><a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a></h1>					
			<p>
				<time class="post-time" datetime="<?php the_time('Y-m-d'); ?>" pubdate><?php the_time( get_option('date_format') ); ?></time>
				<?php edit_post_link( __( 'Edit', 'magazine01' ), '| ', '' ); ?>
			</p>
		</header>						
		<?php the_excerpt(); ?>
		
	</div>
	
</article>
