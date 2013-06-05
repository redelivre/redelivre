<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" href="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize_moip/template/js/vendor/colorpicker/css/colorpicker.css'; ?>">
<link rel="stylesheet" href="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize_moip/template/css/app.css'; ?>">

<label>
	<h3 class="moip-check">
		<input type="checkbox" name="mm_checkbox_status" value="true" <?php if (Mobilize_moip::getOption('mm_checkbox_status') == 'true') { echo 'checked'; } ?>>
		&nbsp;Contribua
	</h3>
</label>
<section class="content-moip" <?php if (Mobilize_moip::getOption('mm_checkbox_status') != 'true') { echo 'style="display: none;"'; } ?>>
	<p class="section-description">
        <label>Texto explicativo desta seção para o usuário:<br/>
            <textarea name="mm_descricao"><?php echo html_entity_decode(Mobilize_moip::getOption('mm_descricao'), ENT_QUOTES, 'UTF-8'); ?></textarea>
        </label>
    </p>

	<!-- Moip Data -->
	<p>Insira abaixo os dados da sua conta Moip e a página que deverá ser retornada ao usuário após o pagamento:</p>
	<p>
		<label class="description">
			ID da carteira Moip (e-mail)<br>
			<input name="mm_carteira" type="text" size="30" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
		</label>
	</p>
	<p>
		<label class="description">
			URL para retorno<br>
			<input  name="mm_url_retorno" type="text" size="60" value="<?php echo Mobilize_moip::getOption('mm_url_retorno'); ?>">
		</label>
	</p>
	<!-- /Moip Data -->

	<p>
		<label>Associe uma cor a cada tipo de contribuição:</label>

		<div class="cores">
			<label class="description">Institucional</label>
			<div class="clear"></div>
			<div class="colorSelector1">
				<div style="background-color: <?php echo is_null(Mobilize_moip::getOption('mm_color_institucional')) ? '#44ab15' : Mobilize_moip::getOption('mm_color_institucional'); ?>; width: 70px; height: 70px;"></div>
				<input name="mm_color_institucional" type="hidden" value="<?php echo is_null(Mobilize_moip::getOption('mm_color_institucional')) ? '#44ab15' : Mobilize_moip::getOption('mm_color_institucional'); ?>">
			</div>

			<div class="clear"></div>
		</div>

		<div class="cores">
			<label class="description">Projeto</label>
			<div class="clear"></div>
			<div class="colorSelector2">
				<div style="background-color: <?php echo is_null(Mobilize_moip::getOption('mm_color_projeto')) ? '#cc1504' : Mobilize_moip::getOption('mm_color_projeto'); ?>; width: 70px; height: 70px;"></div>
				<input name="mm_color_projeto" type="hidden" value="<?php echo is_null(Mobilize_moip::getOption('mm_color_projeto')) ? '#cc1504' : Mobilize_moip::getOption('mm_color_projeto'); ?>">
			</div>
			<div class="clear"></div>
		</div>

		<div class="cores">
			<label class="description">Outros</label>
			<div class="clear"></div>
			<div class="colorSelector3">
				<div style="background-color: <?php echo is_null(Mobilize_moip::getOption('mm_color_outros')) ? '#1e1e7d' : Mobilize_moip::getOption('mm_color_outros'); ?>; width: 70px; height: 70px;"></div>
				<input name="mm_color_outros" type="hidden" value="<?php echo is_null(Mobilize_moip::getOption('mm_color_outros')) ? '#1e1e7d' : Mobilize_moip::getOption('mm_color_outros'); ?>">
			</div>
		</div>
	</p>

	<div class="clear"></div>

	<!-- Contribuições -->
	<p>Preencha as informações necessárias para gerar cada botão:</p>
	<p class="description">É possível gerar até quatro botões para contribuições insitucionais, por projeto e "outros". Também é possível aceitar livremente qualquer valor.</p>
						
	<label class="contribuicao" style="margin-top: 5px;"><input type="checkbox" name="mm_checkbox_contribuicaofixa1" value="true" <?php if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa1') == 'true') { echo 'checked'; } ?>>&nbsp;Contribuição Fixa 1</label>
	<div <?php if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa1') != 'true') { echo 'style="display: none;"'; } ?>>
		<p class="p-margin">
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa1" value="1" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa1') == '1'){ echo 'checked'; } ?>>
				Institucional
			</label>
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa1" value="2" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa1') == '2'){ echo 'checked'; } ?>>
				Por projeto
			</label>
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa1" value="3" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa1') == '3'){ echo 'checked'; } ?>>
				Outros
			</label>
		</p>
		<p>
			<label class="description">
				Descrição<br>
				<textarea cols="70" rows="5" name="mm_descricao_contribuicaofixa1"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa1'); ?></textarea>
			</label>
		</p>
		<p>
			<label class="description">
			Valor (R$)<br>
			<input class="price" type="text" size="12" name="mm_valor_contribuicaofixa1" value="<?php echo Mobilize_moip::getOption('mm_valor_contribuicaofixa1'); ?>">
			</label>
		</p>
	</div>
	
	<br>

	<label class="contribuicao"><input type="checkbox" name="mm_checkbox_contribuicaofixa2" value="true" <?php if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa2') == 'true') { echo 'checked'; } ?>>&nbsp;Contribuição Fixa 2</label>
	<div <?php if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa2') != 'true') { echo 'style="display: none;"'; } ?>>
		<p class="p-margin">
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa2" value="1" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa2') == '1'){ echo 'checked'; } ?>>
				Institucional
			</label>
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa2" value="2" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa2') == '2'){ echo 'checked'; } ?>>
				Por projeto
			</label>
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa2" value="3" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa2') == '3'){ echo 'checked'; } ?>>
				Outros
			</label>
		</p>
		<p>
			<label class="description">
				Descrição<br>
				<textarea cols="70" rows="5" name="mm_descricao_contribuicaofixa2"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa2'); ?></textarea>
			</label>
		</p>
		<p>
			<label class="description">
			Valor (R$)<br>
			<input class="price" type="text" size="12" name="mm_valor_contribuicaofixa2" value="<?php echo Mobilize_moip::getOption('mm_valor_contribuicaofixa2'); ?>">
			</label>
		</p>
	</div>

	<br>

	<label class="contribuicao"><input type="checkbox" name="mm_checkbox_contribuicaofixa3" value="true" <?php if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa3') == 'true') { echo 'checked'; } ?>>&nbsp;Contribuição Fixa 3</label>
	<div <?php if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa3') != 'true') { echo 'style="display: none;"'; } ?>>
		<p class="p-margin">
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa3" value="1" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa3') == '1'){ echo 'checked'; } ?>>
				Institucional
			</label>
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa3" value="2" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa3') == '2'){ echo 'checked'; } ?>>
				Por projeto
			</label>
			<label>
				<input type="radio" name="mm_tipo_contribuicaofixa3" value="3" <?php if (Mobilize_moip::getOption('mm_tipo_contribuicaofixa3') == '3'){ echo 'checked'; } ?>>
				Outros
			</label>
		</p>
		<p>
			<label class="description">
				Descrição<br>
				<textarea cols="70" rows="5" name="mm_descricao_contribuicaofixa3"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa3'); ?></textarea>
			</label>
		</p>
		<p>
			<label class="description">
			Valor (R$)<br>
			<input class="price" type="text" size="12" name="mm_valor_contribuicaofixa3" value="<?php echo Mobilize_moip::getOption('mm_valor_contribuicaofixa3'); ?>">
			</label>
		</p>
	</div>

	<br>

	<label class="contribuicao"><input type="checkbox" name="mm_checkbox_contribuicaolivre" value="true" <?php if (Mobilize_moip::getOption('mm_checkbox_contribuicaolivre') == 'true') { echo 'checked'; } ?>>&nbsp;Contribuição Livre</label>
	<div <?php if (Mobilize_moip::getOption('mm_checkbox_contribuicaolivre') != 'true') { echo 'style="display: none;"'; } ?>>
		<p>
			<label class="description">
				Descrição<br>
				<textarea cols="70" rows="5" name="mm_descricao_contribuicaolivre"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaolivre'); ?></textarea>
			</label>
		</p>
	</div>
	<!-- /Contribuições -->
</section>

		
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize_moip/template/js/vendor/colorpicker/js/colorpicker.js'; ?>"></script>
<script src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize_moip/template/js/vendor/price.js'; ?>"></script>
<script src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize_moip/template/js/app.js'; ?>"></script>