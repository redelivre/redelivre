
<h1>
	<?php echo _x('Recuperar minha senha', 'custom-lost-password', 'redelivre'); ?>
</h1>

<?php if(!isset($_GET['action'])) : ?>

<form>

	<p>
		<?php echo _x('Digite seu nome de usuário ou endereço de email. Você receberá um link para criar uma nova senha via email.', 'custom-lost-password', 'redelivre'); ?>
	</p>
	
	<p>
		<label for="user-email"><?php _e('Digite seu email utilizado no cadastro', 'custom-lost-password', 'redelivre'); ?></label><br />
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
<?php elseif($_GET['action'] == 'rp'): ?>
	
	

<?php endif; ?>