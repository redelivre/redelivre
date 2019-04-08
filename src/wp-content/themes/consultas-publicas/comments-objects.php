<?php if(!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!'); ?>
<?php if(post_password_required()) return; ?>
<?php
// add a microid to all the comments
function comment_add_microid($classes)
{
  $c_email = get_comment_author_email();
  $c_url = get_comment_author_url();
  if (!empty($c_email) && !empty($c_url)) {
    $microid = 'microid-mailto+http:sha1:' . sha1(sha1('mailto:'.$c_email).sha1($c_url));
    $classes[] = $microid;
  }
  return $classes;  
}

add_filter('comment_class','comment_add_microid');
?>

<div id="comments"> 
    <!--show the comments-->
        <h3 class="subtitulo"><?php _e('Comentários', 'consulta'); ?></h3>

        <h4>
            <?php comments_number( __i('Nenhum comentário', 'Comentários: número de comentários') , __i('1 comentário', 'Comentários: número de comentários'), __i('% comentários', 'Comentários: número de comentários') );?>
            <?php if ('open' == $post->comment_status) : ?>
             | <a href="#respond" title="Comente"><?php _oi('Deixe seu comentário', 'Comentários: título do formulário'); ?></a>
            <?php endif; ?>
        </h4>
        <ul class="commentlist" id="singlecomments">
            <?php wp_list_comments('callback=consulta_comment'); ?>
        </ul>
        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
            <nav class="page-link">
                <?php paginate_comments_links( array('prev_text' => '&laquo; anteriores', 'next_text' => 'próximos &raquo;')); ?>                
            </nav>
        <?php endif; // check for comment navigation ?>
            
   
    
    <!--show the form-->
    <?php if('open' == $post->comment_status && !is_consulta_encerrada()) : ?>
    <div id="respond" class="clearfix">
        <h5><?php _oi('Deixe seu comentário', 'Comentários: título do formulário'); ?></h5>
        <?php if( !is_user_logged_in()) : ?>
            
            <p>
            
            <?php printf( __( 'Você precisa fazer <a href="%s">login</a> para publicar um comentário.', 'consulta' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ) ) ) ); ?> 
            
            </p>
        
        <?php else : ?>
        <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="form-comentario" class="clearfix">
            <?php comment_id_fields(); ?>

            <?php if($user_ID) : ?>
				<p>Conectado como <a href="<?php print get_option('siteurl'); ?>/wp-admin/profile.php"><?php print $user_identity; ?></a>. <a href="<?php print get_option('siteurl'); ?>/wp-login.php?action=logout" title="Logout">Logout &raquo;</a></p>
			
			<?php endif; ?>                     
			<textarea name="comment" id="comment" tabindex="1" onfocus="if (this.value == 'Insira seu comentário aqui.') this.value = '';" onblur="if (this.value == '') {this.value = 'Insira seu comentário aqui.';}">Insira seu comentário aqui.</textarea>			           

            <?php cancel_comment_reply_link('cancelar'); ?><input type="submit" name="comentar" id="comentar" class="submit-button" value="<?php _e('Comentar', 'consulta'); ?>" />
            <?php if(get_option("comment_moderation") == "1") : ?>
            <?php _e('Todos os comentários são moderados', 'consulta'); ?>
            <?php endif; ?>
            <?php do_action('comment_form', $post->ID); ?>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
