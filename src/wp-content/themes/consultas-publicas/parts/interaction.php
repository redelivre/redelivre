<div class="interaction clearfix">
	<?php if ( !is_page() ) : ?>
		<div class="comments-number" title="<?php comments_number('nenhum comentário','1 comentário','% comentários');?>"><?php comments_number('0','1','%');?></div>
		<?php if (get_post_type() == 'object'): ?>
        <div class="commenters-number" title="número de pessoas que comentaram"><span class="commenters-number-icon"></span><?php echo get_num_pessoas_comentarios($post->ID); ?></div>
        <?php endif; ?>
	<?php endif; ?>
	<div>
		<!-- AddToAny BEGIN -->
		<a class="share a2a_dd" href="http://www.addtoany.com/share_save">compartilhar</a>
		<script type="text/javascript">
		var a2a_config = a2a_config || {};
		a2a_config.prioritize = ["orkut", "google_bookmarks", "google_gmail", "hotmail", "linkedin", "yahoo_bookmarks", "yahoo_mail", "windows_live_favorites"];
		</script>
		<script type="text/javascript" src="http://static.addtoany.com/menu/locale/pt-BR.js" charset="utf-8"></script>
		<script type="text/javascript" src="http://static.addtoany.com/menu/page.js"></script>
		<!-- AddToAny END -->
	</div>
	<div>
		<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo get_permalink($post->ID) ?>&layout=button_count&show_faces=false&width=125&action=recommend&colorscheme=light&height=20" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:125px; height:20px; margin-top:1px;" allowTransparency="true"></iframe>
	</div>
	<div>
		<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal">Tweet</a>
		<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
	</div>
</div>
