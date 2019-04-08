<article id="post-<?php the_ID(); ?>" <?php post_class('post clearfix');?>>	  
	<header>
		<h1><a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a></h1>					
	</header>
	<div class="post-content clearfix">		
		<div class="post-entry">
			<?php the_content(); ?>
		</div>	
	</div>
	<!-- .post-content -->
	<footer class="clearfix">		
		<?php html::part('interaction'); ?>				
	</footer>
</article>
<!-- .post -->
    			
