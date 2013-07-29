<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>Configurações Gerais</h2>
	
	<?php if($this->theme_options_save()) : ?>
		
		<div id="setting-error-settings_updated" class="updated settings-error"> 
			<p>
				<strong><?php echo _x('Configurações salvas.', 'resposta-theme-options', 'mobilize'); ?></strong>
			</p>
		</div>
		
	<?php endif; ?>
	<form action="#" method="post">
		<p>
			<label for=""><?php _e('Endereço', '_mobilize'); ?></label>
			<input type="text" name="endereco" value="<?php echo get_option('_mobilize_endereco'); ?>" />
		</p>
		
		<p>
			<label for=""><?php _e('Telefone', '_mobilize'); ?></label>
			<input type="text" name="telefone" value="<?php echo get_option('_mobilize_telefone'); ?>"/>
		</p>
		
		<p>
			<label for=""><?php _e('Apresentação', '_mobilize'); ?></label><br />
			<textarea name="apresentacao" id="" cols="30" rows="10"><?php echo get_option('_mobilize_apresentacao'); ?></textarea>
		</p>
		
		<input type="submit" class="button-primary" />
	</form>
</div>