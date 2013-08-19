
<h1>
	<?php echo _x('Recuperar minha senha', 'custom-lost-password', 'campanhacompleta'); ?>
</h1>

<?php if(!isset($_GET['action'])) : ?>

<form>

	<p>
		<?php _x('Digite seu nome de usuário ou endereço de email. Você receberá um link para criar uma nova senha via email.', 'custom-lost-password', 'campanhacompleta'); ?>
	</p>
	
	<p>
		<label for="user-email"><?php _e('Digite seu email utilizado no cadastro', 'custom-lost-password', 'campanhacompleta'); ?></label><br />
		<input type="text" name="user-email" id="user-email">
	</p>
	<?php do_action('lostpassword_form'); ?>
	<p>
		<input type="button" value="enviar" id="lost-password-send"/>
	</p>
	
	<div class="resposta-ajax">
		
	</div>
		
</form>
<?php elseif($_GET['action'] == 'rp'): ?>
	
	

<?php endif; ?>