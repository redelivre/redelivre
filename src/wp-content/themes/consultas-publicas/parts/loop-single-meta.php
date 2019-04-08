
<article id="post-<?php the_ID(); ?>" <?php post_class('post clearfix');?>>	  
	<header>
		<?php if (get_theme_option('enable_taxonomy')): ?>
            <?php 
                $tax_obj = get_taxonomy('object_type');
            ?>
            <p class="bottom">			
                <?php echo $tax_obj->labels->name; ?>: <?php the_terms( get_the_ID() , 'object_type' ); ?>
            </p>
        <?php endif; ?>
		<h1><?php the_title();?></h1>
						
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
    <div class="evaluation_container">
        <?php html::part('evaluation')?>
    </div>
</article>
<!-- .post -->
    			
