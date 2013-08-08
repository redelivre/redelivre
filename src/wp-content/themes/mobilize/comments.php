<?php
// Do not delete these lines
    if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
        die ('Please do not load this page directly. Thanks!');
 
    if ( post_password_required() ) { ?>
        <p class="nocomments"><?php _e('Este artigo está protegido por password. Insira-a para ver os comentários.', 'mobilize'); ?></p>
    <?php
        return;
    }
?>

 
 	<div class="comm-head">
	    <h4><?php _x('Comentários', 'comentarios', 'mobilize'); ?><span class="commen"><?php comments_number('Nenhum comentário', '1 Comentário', '% Comentários' );?></span></h4>
    </div>
     <?php if ( have_comments() ) : ?>
        <ol class="commentlist">
        	<?php wp_list_comments('avatar_size=64&type=comment'); ?>
    	</ol>
 
        <?php if ($wp_query->max_num_pages > 1) : ?>
        <div class="pagination">
        <ul>
            <li class="older"><?php previous_comments_link(_x('Anteriores', 'paginacao', 'mobilize')); ?></li>
            <li class="newer"><?php next_comments_link(_x('Novos','paginacao', 'mobilize')); ?></li>
        </ul>
    </div>
    <?php endif; ?>
 
    <?php endif; ?>
 
    <?php if ( comments_open() ) : ?>
 
    	<div id="respond-comment">
            <div class="commen-1">
            	<?php _e('Deixe o seu comentário!', 'mobilize'); ?>
            	<p>
            		<?php _e('Você está logado(a) como', 'mobilize'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(); ?>" title="<?php _e('Sair desta conta &raquo;', 'mobilize'); ?>"><?php _e('Sair desta conta &raquo;', 'mobilize'); ?></a>
            	</p>
            </div>
 
            <?php comment_form(); ?>
            <p class="cancel"><?php cancel_comment_reply_link(__('Cancelar Resposta', 'comentarios', 'mobilize')); ?></p>
        </div>
     <?php else : ?>
        <h3><?php _e('Os comentários estão fechados.', 'mobize'); ?></h3>
<?php endif; ?>


