<?php
global $post;
$old_post = $post;

if ((isset($config['widget_action']) && $config['widget_action'] == 'cat') || (empty($config['widget_action']) && isset($config['cat']) && !empty($config['cat']))) {
    $showing = 'cat';
    $post = $self->getLastPostFromCat($config);
} else {
    $showing = 'post';
    $post = $self->getPostFromPermalink($config);
}

if ($post):
			?>
	<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>
		<?php if ( get_post_format() != 'gallery' && get_post_format() != 'video' && has_post_thumbnail() ) : ?> 
			<?php the_post_thumbnail(); ?>
		<?php elseif (get_post_format() == 'gallery'): ?>
            <?php
            $images = get_children( array( 
                'post_parent' => $post->ID,
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'numberposts' => -1 ) );
            ?>
            <section id="entry-gallery-<?php the_ID(); ?>" class="clearfix slideshow entry-gallery">
            <?php foreach( $images as $image) : ?>
                <?php echo wp_get_attachment_image($image->ID, 'post-thumbnail'); ?>
            <?php endforeach; ?>
            </section>
        <?php elseif (get_post_format() == 'video'): ?>
            
            <?php the_first_video(); ?>
            
		<?php endif; ?>
        
			<header>                       
				<h1><a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a></h1>					
				<p>
					<time class="post-time" datetime="<?php the_time('Y-m-d'); ?>" pubdate><?php the_time( get_option('date_format') ); ?></time>
					<?php edit_post_link( __( 'Edit', 'magazine01' ), '| ', '' ); ?>
				</p>
			</header>
		<div class="post-content">						
            <?php if (get_post_format() == 'audio'): ?>
                <?php the_first_audio(); ?>
            <?php elseif (get_post_format() != 'video' && get_post_format() != 'gallery') : ?>
                <p><?php echo utils::getPostExcerpt($post, 144); ?></p>
            <?php endif; ?>
		</div>
        <?php if ($showing != 'post'): ?>
		<footer class="clearfix">	
			<p class="taxonomies">
                <?php $curCat = get_category($config['cat']); $curCatLink = get_category_link($config['cat']); ?>
                <span>Ver mais <a href="<?php echo $curCatLink; ?>"><?php echo $curCat->name; ?></a></span>
			</p>
		</footer>
        <?php endif; ?>
	</article>

<?php else: ?>
    <?php if (current_user_can('edit_theme_options')): ?>
	<div class="empty-feature">
		<p>Para exibir um post aqui clique acima em "editar".</p>
	</div>
    <?php endif; ?>
<?php
endif;
$post = $old_post;


?>
