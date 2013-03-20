<?php

$post = $self->getPostFromPermalink($config);

if ($post) :
    ?>
    <?php if (has_post_thumbnail($post->ID)): ?>
        <?php echo get_the_post_thumbnail($post->ID, 'home-secondary-highlight', array('class' => 'destaque-thumbnail')); ?>
    <?php endif; ?>
	<header>
		<p class="bottom">				
			<?php echo date_i18n('j \d\e F', strtotime($post->post_date)); ?>
		</p>
		<h1><a href="<?php echo get_post_permalink($post->ID); ?>" title="<?php echo esc_attr(get_the_title($post->ID)); ?>"><?php echo get_the_title($post->ID); ?></a></h1>
	</header>
	<div class="post-content clearfix">
		<div class="post-entry">
		    <?php utils::postExcerpt($post, 144, get_post_permalink($post->ID), 'Leia mais'); ?>
		</div>
	</div>
<?php else: ?>
    <?php if (current_user_can('edit_theme_options')): ?>
	<div class="empty-feature">
		<p>Para exibir um post aqui clique acima em "editar".</p>
	</div>
    <?php endif; ?>
<?php endif; ?>
