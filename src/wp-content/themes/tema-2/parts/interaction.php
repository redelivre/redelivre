<div class="interaction clearfix">
	<span>
		<iframe id='<?php echo $uid?>' src="http://www.facebook.com/plugins/like.php?href=<?php echo get_permalink($post->ID) ?>&layout=button_count&show_faces=false&width=90&action=recommend&colorscheme=light&height=20" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:20px; margin-top:1px;" allowTransparency="true"></iframe>
	</span>
	<span>
		<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal">Tweet</a>
		<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
	</span>
	<span><g:plusone size="medium" href="<?php the_permalink(); ?>"></g:plusone></span>
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
</div>
