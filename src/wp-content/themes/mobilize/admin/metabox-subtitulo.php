<?php global $post_id; ?>
<label for="meta-subtitulo">Subtitulo</label>
<input type="text" name="meta-subtitulo" id="meta-subtitulo" value="<?php echo get_post_meta($post_id, '_subtitulo', true); ?>">