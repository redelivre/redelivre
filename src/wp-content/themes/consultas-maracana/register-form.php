<div class="register-form-container">
	<form class="register-form" id="formulario-cadastro">
		<?php $campos = $this->get_register_form_fields(); ?>
		<?php foreach($campos as $label => $config) : ?>
			<p>
				<label for="<?php echo $name; ?>"><?php echo $label; ?></label>
				<input type="<?php echo $config['type']; ?>" class="<?php echo $config['class']; ?>" name="<?php echo $config['name']; ?>" id="<?php echo $config['id']; ?>" />
			</p>
		<?php endforeach; ?>
                <input type="hidden" name="redirect_to" value="<?php echo $_SERVER['HTTP_REFERER']; ?>"/>
		<input type="button" class="bt-verde bt-enviar" value="Cadastrar" /><span id="status-ajax"></span>
	</form>
</div>