<?php global $post_id; ?>
<p>
	<label for="">Link facebook</label>
	<input type="text" name="meta-link-facebook" id="meta-link-facebook" value="<?php echo get_post_meta($post_id, '_link-facebook', true); ?>" />
</p>

<p>
	<label for="">Link twitter</label>
	<input type="text" name="meta-link-twitter" id="meta-link-twitter" value="<?php echo get_post_meta($post_id, '_link-twitter', true); ?>" />
</p>