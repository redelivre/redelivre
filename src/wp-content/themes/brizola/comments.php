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
    <?php if ('open' == $post->comment_status) : ?>
        <h3><?php _e('Comments', 'brizola'); ?></h3>

        <h4><?php comments_number(__('No comments', 'brizola'), __('1 comment','brizola'), __('%s comments','brizola') );?> | <a href="#respond" title="Comente"><?php _e('Leave a comment &raquo;', 'brizola'); ?></a></h4>
        <ul class="commentlist" id="singlecomments">
            <?php wp_list_comments('callback=brizola_comment'); ?>
        </ul>
        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
            <nav id="comments-nav">
                <div class="alignleft"><?php previous_comments_link( __( '&laquo; Older Comments', 'brizola' ) ); ?></div>
                <div class="alignright"><?php next_comments_link( __( 'Newer Comments &raquo;', 'brizola' ) ); ?></div>
            </nav><!-- .navigation -->
        <?php endif; // check for comment navigation ?>
            
    <?php endif; ?>
    
    <!--show the form-->
    <?php if('open' == $post->comment_status) : ?>
    <div id="respond" class="clearfix">
        <h5><?php _e('Leave a comment', 'brizola'); ?></h5>
        <?php if(get_option('comment_registration') && !$user_ID) : ?>
        
        <p>
        <?php printf( __( 'You must be %sloggedin%s to post a comment.', 'brizola'), "<a href='" . get_option('siteurl') . "/wp-login.php?redirect_to=" . urlencode(get_permalink()) ."'>", "</a>" ); ?>
        </p>
        
        <?php else : ?>
        <form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="form-comentario" class="clearfix">
            <?php comment_id_fields(); ?>                      
			<textarea name="comment" id="comment" tabindex="1" onfocus="if (this.value == '<?php _e('Write your comment here.', 'brizola'); ?>') this.value = '';" onblur="if (this.value == '') {this.value = '<?php _e('Write your comment here.', 'brizola'); ?>';}"><?php _e('Write your comment here.', 'brizola'); ?></textarea>
			<?php if($user_ID) : ?>
				<p><?php _e('Logged in as', 'brizola'); ?> <a href="<?php print get_option('siteurl'); ?>/wp-admin/profile.php"><?php print $user_identity; ?></a>. <a href="<?php print get_option('siteurl'); ?>/wp-login.php?action=logout" title="Logout">Logout &raquo;</a></p>
			<?php else : ?>                
				<input type="text" name="author" id="author" onfocus="if (this.value == '<?php _e('name', 'brizola'); ?>') this.value = '';" onblur="if (this.value == '') {this.value = '<?php _e('name', 'brizola'); ?>';}"  value="<?php _e('name', 'brizola'); ?>" tabindex="2" />
				<input type="text" name="email" id="email" onfocus="if (this.value == 'email') this.value = '';" onblur="if (this.value == '') {this.value = 'email';}" value="email" tabindex="3" />
				<input type="text" name="url" id="url" value="http://" tabindex="4" />					
			<?php endif; ?>           
			<?php cancel_comment_reply_link( __('cancel', 'brizola') ); ?><input type="submit" name="comentar" id="comentar" value="<?php _e('Comment', 'brizola'); ?>" />
            <?php if(get_option("comment_moderation") == "1") : ?>
            <?php _e('All comments need to be approved', 'brizola'); ?>
            <?php endif; ?>
            <?php do_action('comment_form', $post->ID); ?>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
