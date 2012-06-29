<?php
$meta = get_metadata('post', get_the_ID());
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('event-info clearfix');?>>
	<?php if (has_post_thumbnail()) : ?>		
		<?php the_post_thumbnail(); ?>
	<?php endif; ?>	  
	<header>                       
		<h1><a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a></h1>
	</header>
	<div class="post-content clearfix">
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
	</div>
	<!-- .post-content -->
</article>
<!-- .post -->
    			
