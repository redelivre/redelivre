<div class="equipe row">
	
	<?php $equipe = $this->query->equipe(); ?>
	
	<?php if($equipe->have_posts()) : while($equipe->have_posts()) : $equipe->the_post(); ?>
	
	
	<div class="person borda-cor-1 span7">
		<div class="person-img span2">
			<?php the_post_thumbnail('foto-equipe'); ?>
		</div>
							
		<div class="des-person span4">
			<h3><?php the_title(); ?></h3>
			<p><?php the_content(); ?></p>
			<?php global $post; ?>
			<?php if(get_post_meta($post->ID, '_link-facebook', true)) : ?>
				<a href="<?php echo get_post_meta($post->ID, '_link-facebook', true); ?>">facebook</a>
			<?php endif; ?>
			
			<?php if(get_post_meta($post->ID, '_link-twitter', true)) : ?>
				<a href="<?php echo get_post_meta($post->ID, '_link-twitter', true); ?>">twitter</a>
			<?php endif; ?>
		</div>						
	</div>
	
	
	
	<?php endwhile; else : ?>
		<?php _e('Não há nenhum membro da equipe cadastrado.', 'mobilize'); ?>
	<?php endif; ?>
	
	<?php wp_reset_query(); ?>
</div>
