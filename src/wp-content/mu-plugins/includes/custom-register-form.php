<?php do_action( 'before_signup_form' ); //echo '<pre>';var_dump($_REQUEST);echo '</pre>'; ?>

	<?php if(!is_user_logged_in()) : ?>
	<form id="form-registro" class="formulario-de-registro-padrao">
		<div class="campos">
			<p>
				<label for=""><?php echo _x('Nome de usuário', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
				<input type="text" id="custom-register-username" minlength="2" />
			</p>
			
			<p>
				<label for=""><?php echo _x('Nome real', 'registro-de-usuario', 'campanha-completa'); ?></label><br />
				<input type="text" id="custom-register-realname" />
			</p>
			
			<p>
				<label for=""><?php echo _x('Senha', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
				<input type="password" id="custom-register-password" />
			</p>
			
			<p>
				<label for=""><?php echo _x('Repita sua senha', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
				<input type="password" id="custom-register-password-review" />
			</p>
			
			<p>
				<label for=""><?php echo _x('Email', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
				<input type="email" required="required" id="custom-register-email" name="custom-register-email" />
			</p>
			
			<?php
				if(class_exists('siCaptcha', false))
				{
					global $registro_captcha; 
					$registro_captcha->si_captcha_register_form();
				}
			?>
			<input type="button" value="enviar" id="custom-register-send" />	
			

		</div>	
		<div id="custom-register-resposta">
				
		</div>
	</form>
	
	<?php else : ?>
		<?php echo _x('Você está logado neste momento, para efetuar um novo registro será preciso fazer <a href="' . wp_logout_url() . '">logout</a>', 'registro-de-usuario', 'campanha-completa'); ?>
	<?php endif; ?>
	
<?php do_action( 'after_signup_form' ); ?>