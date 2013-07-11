<?php

/**
 * Baseado em no comments-template
 */

/**
 * HTML comment list class.
 *
 * @package WordPress
 * @uses Walker
 * @since 2.7.0
 */
class Delibera_Walker_Comment extends Walker_Comment
{
	/**
	 * @see Walker::start_el()
	 * @since 2.7.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $comment Comment data object.
	 * @param int $depth Depth of comment in reference to parents.
	 * @param array $args
	 */
	function start_el(&$output, $comment, $depth, $args)
	{
		$depth++;
		$GLOBALS['comment_depth'] = $depth;
		$args['avatar_size'] = '85';

		if ( !empty($args['callback']) ) {
			call_user_func($args['callback'], $comment, $args, $depth);
			return;
		}

		$GLOBALS['comment'] = $comment;
		
		$tipo = get_comment_meta($comment->comment_ID, "delibera_comment_tipo", true);
		$situacao = delibera_get_situacao($comment->comment_post_ID);
		
		extract($args, EXTR_SKIP);

		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
?>
		<<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="delibera-comment-<?php comment_ID() ?>">
		<?php if ( 'div' != $args['style'] ) : ?>
		<div id="delibera-div-comment-<?php comment_ID() ?>" class="delibera-comment-body">
		<?php endif; ?>
		<div id="delibera-div-comment-header-<?php comment_ID() ?>" class="delibera-comment-header">
			<div class="delibera-comment-author vcard">
			<?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['avatar_size'] ); ?>
			<?php
				$url = get_author_posts_url($comment->user_id);
				//print_r($comment);
				$autor_link = "<a href='$url' rel='external nofollow' class='url'>$comment->comment_author</a>"; 
				printf('<cite class="fn">%s</cite><span class="delibera-says"></span>', $autor_link);
			?>
			</div>
	<?php if ($comment->comment_approved == '0') : ?>
			<em class="delibera-comment-awaiting-moderation"><?php _e('Seu comentário está aguardando moderação.', 'delibera') ?></em>
			<br />
	<?php endif; ?>
	
			<div class="delibera-comment-meta commentmetadata">
				<a href="<?php echo htmlspecialchars( delibera_get_comment_link( $comment->comment_ID ) ) ?>">
					<?php
    
						$time = mysql2date( 'G', $comment->comment_date );
					    
					    $time_diff = time() - $time;
					    
					    if ( $time_diff > 0 && $time_diff < 30*24*60*60 )
					    	printf( '&nbsp;' . __( 'há %s', 'delibera' ), human_time_diff( mysql2date( 'U', $comment->comment_date, true ) ) );
					    else
					        echo '&nbsp;' .  __( 'em', 'delibera' ) . '&nbsp;' .  get_comment_date();
					
					?>
				</a>
				&nbsp;
				
				<?php
					if($situacao->slug == 'discussao' || ($situacao->slug == 'relatoria' && current_user_can('relatoria')))
					{
						delibera_edit_comment_link( __('(Edit)'),'&nbsp;&nbsp;', '' );
						delibera_delete_comment_link( __('(Delete)'),'&nbsp;&nbsp;', '' );
					}
				?>
			</div>
			<?php
			if ($situacao->slug == "discussao" || $situacao->slug == "relatoria")
			{
				$display_check = $tipo == "encaminhamento"? '' : 'style="display:none;"';  
			?>
				<span id="checkbox-encaminhamento-<?php echo $comment->comment_ID ?>" class="checkbox-encaminhamento" <?php echo $display_check; ?>><span class="encaminhamento-figura"></span><label class="encaminhamento-label"><?php _e('Encaminhamento','delibera'); ?></label></span>
			<?php
			}
			?>
		</div>

		<?php
			if($situacao->slug == 'relatoria' && current_user_can('relatoria'))
			{
				$baseouseem = get_comment_meta($comment->comment_ID, 'delibera-baseouseem', true);
				if(strlen($baseouseem) > 0)
				{
					$baseouseem_elements = "";
					foreach (explode(',', $baseouseem) as $baseouseem_element)
					{
						$baseouseem_elements .= do_shortcode($baseouseem_element);
					}
					echo '<div id="comment-painel-baseouseem" class="comment-painel-baseouseem"><label id="painel-baseouseem-label" class="painel-baseouseem-label" >'.__('Proposta baseada em:', 'delibera').'&nbsp;</label>'.$baseouseem_elements.'</div>';
				}
			}
			comment_text();
			delibera_comment_edit_form();
			if ($tipo == "encaminhamento" && current_user_can('relatoria') && (/*$situacao->slug == "discussao" || TODO Opção de baseamento na discussão */ $situacao->slug == "relatoria"))
			{
				?>
				<div class="baseadoem-checkbox-div"><label class="baseadoem-checkbox-label"><input id="baseadoem-checkbox-<?php echo $comment->comment_ID; ?>" type="checkbox" name="baseadoem-checkbox[]" value="<?php echo $comment->comment_ID; ?>" class="baseadoem-checkbox" autocomplete="off" /><?php _e('basear-se neste encaminhamento?', 'delibera'); ?></label></div>
				<?php 
			}
			if(delibera_comments_is_open($comment->comment_post_ID))
			{
				?>
				<div class="reply">
				<?php
					if($situacao->slug == 'relatoria' && is_user_logged_in())
					{
						if($tipo == 'encaminhamento' && current_user_can('relatoria'))
						{
							edit_comment_link(__('Editar Encaminhamento', 'delibera'), '<p>', '</p>');
						}
					}
					elseif($situacao->slug != 'validacao' && is_user_logged_in())
					{			
						$args['reply_text'] = __("Responda este comentário", 'delibera');  
						comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'])));
					}
					elseif(is_user_logged_in())
					{
						/*$args['reply_text'] = __("De sua opinião", 'delibera');
						comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth'])));*/
						?>
						<div class="entry-respond">
							<a href="<?php delibera_get_comment_link();?>#respond" class="comment-reply-link"><?php _e( 'De sua opinião', 'delibera' ); ?></a>
						</div>
						<?php
					}
					else 
					{
					?>
						<div class="entry-respond">
							<a href="<?php echo wp_login_url(delibera_get_comment_link());?>#respond" class="comment-reply-link"><?php _e( 'Faça login e de sua opinião', 'delibera' ); ?></a>
						</div><!-- .entry-respond -->
					<?php
					}
				?>
				</div>
		<?php
			}
			echo delibera_gerar_curtir($comment->comment_ID, 'comment');
			echo delibera_gerar_discordar($comment->comment_ID, 'comment');
			?>
		
		<?php if ( 'div' != $args['style'] ) : ?>
		</div>
		<?php endif; ?>
<?php
	}

	

}

class Deliera_Walker_Comment_padrao extends Walker_Comment
{

	/**
	 * @see Walker::start_el()
	 * @since 2.7.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $comment Comment data object.
	 * @param int $depth Depth of comment in reference to parents.
	 * @param array $args
	 */
	function start_el(&$output, $comment, $depth, $args) {
		$depth++;
		$GLOBALS['comment_depth'] = $depth;

		if ( !empty($args['callback']) ) {
			call_user_func($args['callback'], $comment, $args, $depth);
			return;
		}

		$GLOBALS['comment'] = $comment;
		extract($args, EXTR_SKIP);

		if ( 'div' == $args['style'] ) {
			$tag = 'div';
			$add_below = 'comment';
		} else {
			$tag = 'li';
			$add_below = 'div-comment';
		}
?>
		<<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
		<?php if ( 'div' != $args['style'] ) : ?>
		<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
		<?php endif; ?>
		<div id="div-comment-header-<?php comment_ID() ?>" class="comment-header">
			<div class="comment-author vcard">
			<?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['avatar_size'] ); ?>
			<?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
			</div>
	<?php if ($comment->comment_approved == '0') : ?>
			<em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
			<br />
	<?php endif; ?>
	
			<div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
				<?php
					/* translators: 1: date, 2: time */
					printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'&nbsp;&nbsp;','' );
				?>
			</div>
		</div>
		<?php comment_text() ?>

		<?php if(comments_open($comment->comment_post_ID) && is_user_logged_in()) { ?>
		<div class="reply">
		<?php comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
		</div>
		<?php }if ( 'div' != $args['style'] ) : ?>
		</div>
		<?php endif; ?>
<?php
	}

}



?>