
<h1>
	<?php echo _x('Recuperar minha senha', 'custom-lost-password', 'campanhacompleta'); ?>
</h1>

<?php if(!isset($_GET['action']) || $_GET['action'] == 'lostpassword') : ?>

<form>

	<p>
		<?php echo _x('Digite seu nome de usuário ou endereço de email. Você receberá um link para criar uma nova senha via email.', 'custom-lost-password', 'campanhacompleta'); ?>
	</p>
	
	<p>
		<label for="user-email"><?php _e('Digite seu email utilizado no cadastro', 'custom-lost-password', 'campanhacompleta'); ?></label><br />
		<input type="text" name="user-email" id="user-email">
	</p>
	<div class="lostpassword_form_action">
		<?php do_action('lostpassword_form'); ?>
	</div>
	<p>
		<input type="button" value="enviar" id="lost-password-send"/>
	</p>
	
	<div class="resposta-ajax">
		
	</div>
		
</form>
<?php elseif($_GET['action'] == 'rp' || $_GET['action'] == 'resetpass'): ?>
<form>
	<input type="hidden" id="login" value="<?php echo esc_attr( $_GET['login'] ); ?>" autocomplete="off" />
	<input type="hidden" id="key" value="<?php echo esc_attr( $_GET['key'] ); ?>" autocomplete="off" />

	<p>
		<label for="pass1"><?php _e('New password') ?><br />
		<input type="password" name="pass1" id="pass1" size="20" value="" autocomplete="off" /></label>
	</p>
	<p>
		<label for="pass2"><?php _e('Confirm new password') ?><br />
		<input type="password" name="pass2" id="pass2" size="20" value="" autocomplete="off" /></label>
	</p>

	<div id="pass-strength-result" class="hide-if-no-js"><?php _e('Strength indicator'); ?></div>
	<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).'); ?></p>

	<br class="clear" />
	<input type="button" value="enviar-reset" id="lost-password-send-reset"/>
</form>	
<?php endif; ?>