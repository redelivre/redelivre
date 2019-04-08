<?php

$post_type_object = get_post_type_object( 'object' );

get_header();

?>

<section id="main-section" class="span-15 prepend-1 append-1">
    <h2><?php echo $post_type_object->labels->name; ?></h2>

    <?php html::part('add_new_object'); ?>
    
    <?php if (is_tax('object_type')) : ?>
        <?php
        
        $termDiscription = term_description( '', get_query_var( 'taxonomy' ) );
        
        if ($termDiscription != '') : ?>
            <div class="ementa-do-tema">
                <h1><?php wp_title("",true); ?></h1>
                <div class="append-bottom"></div>
                <?php echo $termDiscription; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <?php echo get_theme_option('object_list_intro'); ?>
    <?php endif; ?>    

    <section class="tema">
        <ul>
            <?php if (have_posts()) :
                while (have_posts()) :
                    the_post(); ?>
                    <li>
                        <div class="interaction clearfix">
                            <h1>
                                <?php if (get_post_meta($post->ID, '_user_created', true)) :?>
                                    <div class="suggested-user-icon"><img src="<?php bloginfo('template_directory') ?>/img/star.png" title="Sugestão do usuário" alt="Sugestão do usuário" /></div>
                                <?php endif; ?>

                                <a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>"><?php the_title();?></a>
                                
                                <?php if ( current_user_can('manage_options') && file_exists(WP_CONTENT_DIR . '/uploads/access_log/total/' . $post->ID)): ?>
                                <small><?php echo filesize(WP_CONTENT_DIR . '/uploads/access_log/total/' . $post->ID); ?> acessos</small>
                                <?php endif; ?>
                            </h1>

                            <div class="clear"></div>
                            
                            <div class="comments-number" title="<?php comments_number('nenhum comentário','1 comentário','% comentários');?>"><?php comments_number('0','1','%');?></div>
                            <div class="commenters-number" title="<?php _e('número de pessoas que comentaram', 'consulta'); ?>"><span class="commenters-number-icon"></span><?php echo get_num_pessoas_comentarios($post->ID); ?></div>
                        </div>
                        <?php the_content(); ?>
                    </li>
                <?php endwhile; ?> 
                
                <?php global $wp_query; if ( $wp_query->max_num_pages > 1 ) : ?>
                    <nav id="posts-nav" class="clearfix">
                        <span class="alignleft"><?php previous_posts_link(__('Anteriores','consulta')); ?></span>
                        <span class="alignright"><?php echo next_posts_link(__('Próximos','consulta')); ?></span>
                    </nav>
                    <!-- #posts-nav -->
                <?php endif; ?>
            <?php else : ?>
               <p><?php echo $post_type_object->labels->not_found; ?></p>
            <?php endif; ?>
        </ul>
    </section>    
</section>
<!-- #main-section -->
    
<?php get_sidebar(); ?>
<?php get_footer(); ?>
