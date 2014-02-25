<label for="<?php echo $this->get_field_id('descricao'); ?>">Descrição</label>
<textarea name="<?php echo $this->get_field_name('descricao'); ?>" id="<?php echo $this->get_field_id('descricao'); ?>" cols="30" rows="10">
	<?php echo $instance['descricao']; ?>
</textarea>