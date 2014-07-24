<div id="mobilize-edit-customize" style="display: none;">
	<h2><?php _e('Customização Mobilize', 'mobilize'); ?></h2>

	<label id="mobilize-custom-label">
		<input type="checkbox" name="mobilize-custom" id="mobilize-custom"
			<?php echo ($layout === false? '' : 'checked="checked"'); ?>>
		<?php
			_e('Usar configurações específicas da página', 'mobilize');
		?>
	</label>
	<br>

	<div id="mobilize-layout-editor">
		<h3><?php _e('Layout', 'mobilize'); ?></h3>
		<i><?php
			_e('As seções só irão funcionar se estiverem configuradas na página de '
			. 'configuração do Mobilize.', 'mobilize');
		?></i>
		<br>

		<select name="mobilize-layout-template" id="mobilize-layout-template"
			size="16" style="height: auto;">
			<option name="mobilize-description"><?php
				_e('Texto Explicativo', 'mobilize');
			?></option>
			<option name="mobilize-socialnetworks"><?php
				_e('Redes Sociais', 'mobilize');
			?></option>
			<option name="mobilize-banners"><?php
				_e('Banners', 'mobilize');
			?></option>
			<option name="mobilize-sticker"><?php
				_e('Adesive Sua Foto', 'mobilize');
			?></option>
			<option name="mobilize-contribute"><?php
				_e('Contribua', 'mobilize');
			?></option>
			<option name="mobilize-share"><?php
				_e('Enviar Para Um Amigo', 'mobilize');
			?></option>
		</select>

		<div style="display: inline-block;">
			<input type="button" id="mobilize-add" value="&gt;">
			<br>
			<input type="button" id="mobilize-del" value="&lt;">
		</div>

		<select name="mobilize-layout" size="16" id="mobilize-layout">
		<?php
			if (!is_array($layout)) {
				$layout = array();
			}
			foreach ($layout as $item)
			{
				echo "<option name=\"mobilize-$item\"></option>";
			}
		?>
		</select>

		<div style="display: inline-block;">
			<input type="button" id="mobilize-up" value="^">
			<br>
			<input type="button" id="mobilize-down" value="v">
		</div>

		<input type="hidden" id="mobilize-layout-string"
			name="mobilize-layout-string">
	</div>
</div>
