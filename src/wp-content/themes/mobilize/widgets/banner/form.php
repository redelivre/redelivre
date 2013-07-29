<label for="<?php echo $this->get_field_id('banner'); ?>"></label>
<textarea name="<?php echo $this->get_field_name('banner'); ?>" id="<?php echo $this->get_field_id('banner'); ?>" cols="30" rows="10">
	<?php echo (isset($instance['banner'])) ? $instance['banner'] : ''; ?>
</textarea>