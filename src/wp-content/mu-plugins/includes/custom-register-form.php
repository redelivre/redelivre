<?php do_action( 'before_signup_form' ); ?>

	<?php if(!is_user_logged_in()) : ?>
	<style type="text/css">
		.instrucoes { float:left; width: 50%; margin: 0 60px 0 0; background: #fff; }
		.container-registro { background-color: #fff; display:block; width: 100%; padding: 30px; margin: 0 auto; }
		.container-registro .titulo { background: #404040; color: #fff; }
		.container-registro .titulo h1 { color: #fff; font-weight: normal; padding: 10px; font-size: 20px; }
		
		#custom-register-resposta { width: 100%; text-align: center; }
		#custom-register-resposta .resposta-erro,
		#custom-register-resposta .resposta-sucesso { text-align: center; clear:both; margin: 15px 0 0 0; width: 100%; }
		
		#custom-register-resposta .resposta-sucesso { background: #d8fce5; border: 1px solid #e3d89e; }
		#custom-register-resposta .resposta-erro { background: #fce8d8; border: 1px solid #e3d89e; }
		#custom-register-resposta .resposta-erro li { list-style:none; }
		#registerform { display: table; margin: 67px 0 0 0; }
		#registerform .input { width: 300px; border-radius: 5px; background: #fff; border: 1px solid #ccc; padding: 7px; }		
		#registerform { padding: 0 0 0 50px; }

				
	</style>
	
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#captcha_code").removeAttr("style");
		});
	</script>
	
	<div class="container-registro">
		
		<div class="titulo">
			<h1><?php echo _x('Cadastre-se', 'registro-de-usuario', 'campanhacompleta'); ?></h1>
		</div>
		
		<div class="instrucoes">
			<h2>Aqui vem o título do bloco de texto</h2>
			Aqui vem o conteúdo do bloco definido no administrador. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu.
			
			<hr />
		</div>
		
		
		
		<form class="formulario-de-registro-padrao" id="registerform">
			<div class="campos">
				<p>
					<label for="custom-register-username"><?php echo _x('Nome de usuário', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
					<input type="text" id="custom-register-username" class="input" minlength="2" id="user_login" />
				</p>
				
				<p>
					<label for="custom-register-realname"><?php echo _x('Nome completo', 'registro-de-usuario', 'campanha-completa'); ?></label><br />
					<input type="text" id="custom-register-realname" class="input" />
				</p>
				
				<p>
					<label for="custom-register-password"><?php echo _x('Senha', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
					<input type="password" id="custom-register-password" class="input" />
				</p>
				
				<p>
					<label for="custom-register-password-review"><?php echo _x('Repita sua senha', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
					<input type="password" id="custom-register-password-review" class="input" />
				</p>
				
				<p>
					<label for="custom-register-email"><?php echo _x('Email', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
					<input type="email" required="required" id="custom-register-email" name="custom-register-email" class="input" />
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
			
			
			
		</form>
		
		<div id="custom-register-resposta">
					
		</div>
		<?php else : ?>
			<?php echo _x('Você está logado neste momento, para efetuar um novo registro será preciso fazer <a href="' . wp_logout_url() . '">logout</a>', 'registro-de-usuario', 'campanha-completa'); ?>
		<?php endif; ?>
	</div>
<?php do_action( 'after_signup_form' ); ?>