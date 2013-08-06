<?php do_action( 'before_signup_form' ); ?>

	<?php if(!is_user_logged_in()) : ?>

	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#captcha_code").removeAttr("style");
		});
	</script>
	


<div id="primary" class="content-area">
			<div id="content" class="site-content" role="main">
			<div class="container-registro">	
				<header class="entry-header">
					<h1 class="entry-title"><?php _e('Cadastre-se', 'guarani'); ?></h1>
				</header><!-- .entry-header -->
			
			<form class="formulario-de-registro-padrao" id="registerform">
				<div class="campos">
					<p>
						<label for="custom-register-username"><?php echo _x('Nome de usuário', 'registro-de-usuario', 'campanha-completa'); ?></label> <br />
						<span class="info-campo"><?php _e('Este é o nome que será usado para efetuar o login, deve conter um mínimo de dois caracteres e somente incluir letras minúsculas e números.', 'guarani'); ?></span>
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
		</div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>


	