<?php
$meta = get_metadata('post', get_the_ID());
?>
<?php while ( have_posts()) : the_post(); ?>
	<?php if (has_post_thumbnail()) : ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class('event-info hasthumb clearfix'); ?>>	
			<?php the_post_thumbnail(); ?>
	<?php else: ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class('event-info clearfix'); ?>> 
	<?php endif; ?>
			
		<h1><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
		<?php if((isset($meta['_data_inicial'][0]) && $meta['_data_inicial'][0]) && (isset($meta['_data_final'][0]) && $meta['_data_final'][0])): ?>
			<p class="bottom"><span class="label">Data:</span> <?php echo date('d/m/Y', strtotime($meta['_data_inicial'][0])), ' - ', date('d/m/Y', strtotime($meta['_data_final'][0])); ?></p>
		<?php elseif(isset($meta['_data_inicial'][0]) && $meta['_data_inicial'][0]): ?>
			<p class="bottom"><span class="label">Data:</span> <?php echo date('d/m/Y', strtotime($meta['_data_inicial'][0])); ?></p>
		<?php elseif(isset($meta['_data_final'][0]) && $meta['_data_final'][0]): ?>
			<p class="bottom"><span class="label">Data:</span> <?php echo date('d/m/Y', strtotime($meta['_data_final'][0])); ?></p>
		<?php endif; ?>
		  
		<?php if(isset($meta['_onde'][0]) && $meta['_onde'][0]): ?>
			<p class="bottom"><span class="label">Local:</span> <?php echo $meta['_onde'][0]; ?></p>
		<?php endif; ?>
		
		<?php if(isset($meta['_link'][0]) && $meta['_link'][0]): ?>
			<p class="bottom"><span class="label">Site:</span> <a href="<?php echo $meta['_link'][0]; ?>"> <?php echo $meta['_link'][0]; ?></a></p>
		<?php endif; ?>
			
	</article>
	<!-- .post -->
<?php endwhile; ?>
<?php if ( $wp_query->max_num_pages > 1 ) : ?>
	<nav class="navigation">
		<div class="alignleft"><?php next_posts_link(__('&laquo; Previous posts', 'blog01')); ?></div>
		<div class="alignright"><?php previous_posts_link(__('Next posts &raquo;', 'blog01')); ?></div>
	</nav>
	<!-- #navigation -->
<?php endif; ?>	
