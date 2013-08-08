</div>

<?php wp_footer(); ?>

<div id="primary" class="site-content masterfooter row">
	<div id="content" role="main" class="container">
		
		<div class="span12">
		<div class="row">	 
		
			<?php dynamic_sidebar('sidebar-footer'); ?>
		
		</div>		
	</div>
 </div>
</div>

<div class="container">
	<div class="endereco">
		<!-- aqui a do tema de colocar informações no rodapé -->
		<p class="creditos textright alignright">
		    <?php if (is_user_logged_in()): ?>
                <a class="login" href="<?php echo admin_url(); ?>">Painel de Administração</a> &bull;
            <?php else: ?>
                <a class="login" href="<?php echo wp_login_url(get_permalink()); ?>">Login</a> &bull;
            <?php endif; ?>
    	    <a href="http://campanhacompleta.com.br" title="Campanha Completa"><img src="<?php echo get_template_directory_uri(); ?>/imagens/campanha-completa.png" alt="" /></a> &bull; 
		    <a href="http://wordpress.org"><img src="<?php echo get_template_directory_uri(); ?>/imagens/wp.png" alt="" /></a>
		</p>
	</div>
	

	
</div>
</body>
</html>