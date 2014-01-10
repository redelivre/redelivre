<?php if ( ! function_exists( 'et_custom_comments_display' ) ) :
function et_custom_comments_display($comment, $args, $depth) {
	$GLOBALS['comment'] = $comment; ?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment-body clearfix">
			<div class="comment_avatar">
				<?php echo get_avatar( $comment, $size='60' ); ?>
			</div> <!-- end .comment_avatar -->

			<div class="comment_postinfo">
				<?php printf('<span class="fn">%s</span>', get_comment_author_link()) ?>
				<span class="comment_date">
					<?php
						/* translators: 1: date, 2: time */
						printf( __( '%1$s', 'Origin' ), get_comment_date() );
					?>
				</span>
				<?php edit_comment_link( esc_html__( '(Edit)', 'Origin' ), ' ' ); ?>
			</div> <!-- end .comment_postinfo -->

			<div class="comment_area">
				<?php if ($comment->comment_approved == '0') : ?>
					<em class="moderation"><?php esc_html_e('Your comment is awaiting moderation.','Origin') ?></em>
					<br />
				<?php endif; ?>

				<div class="comment-content clearfix">
					<?php comment_text() ?>

					<?php
						$et_comment_reply_link = get_comment_reply_link( array_merge( $args, array('reply_text' => esc_attr__('Reply','Origin'),'depth' => $depth, 'max_depth' => $args['max_depth'])) );
						if ( $et_comment_reply_link ) echo '<div class="reply-container">' . $et_comment_reply_link . '</div>';
					?>
				</div> <!-- end comment-content-->
			</div> <!-- end comment_area-->
		</article> <!-- end comment-body -->
<?php }
endif;