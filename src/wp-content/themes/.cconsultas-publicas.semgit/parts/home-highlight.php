<?php

$post = $self->getPostFromPermalink($config);

if ($post) :
    ?>
    <header>
    	<p class="bottom">				
    		<?php echo date_i18n('j \d\e F', strtotime($post->post_date)); ?>
    	</p>
    	<h1><a href="<?php echo get_post_permalink($post->ID); ?>" title="<?php echo esc_attr(get_the_title($post->ID)); ?>"><?php echo get_the_title($post->ID) ?></a></h1>					
    </header>
    <div class="post-content clearfix">
    	<!-- se tiver imagem, colocar aqui com a classe ".destaque-principal-thumbnail" de 230x176 -->
        <?php if (has_post_thumbnail($post->ID)): ?>
            <?php echo get_the_post_thumbnail($post->ID, 'home-highlight', array('class' => 'destaque-principal-thumbnail')); ?>
        <?php endif; ?>
    	<div class="post-entry">
    		<?php utils::postExcerpt($post, 144, get_post_permalink($post->ID), 'Leia mais'); ?>
    	</div>	
    </div>
    <!-- .post-content -->
<?php else: ?>
    <?php if (current_user_can('edit_theme_options')): ?>
	<div class="empty-feature">
		<p>Para exibir um post aqui clique acima em "editar".</p>
	</div>
    <?php endif; ?>
<?php endif; ?>
    