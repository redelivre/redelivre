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
    
        <h3 class="subtitulo"><?php _oi('Comentários', 'Comentários: tíulo'); ?></h3>

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
    <?php comment_form(array('id_submit' => 'comentar')); ?>
    
</div>
