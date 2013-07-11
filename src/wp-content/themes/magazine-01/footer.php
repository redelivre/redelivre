	<footer id="main-footer" class="clear clearfix">
		<div class="alignleft textleft creditos"><?php echo nl2br(get_option('campanha_contact_footer')); ?></div>
		<p class="creditos textright alignright">
		    <?php if (is_user_logged_in()): ?>
                <a class="login" href="<?php echo admin_url(); ?>">Painel de Administração</a> &bull;
            <?php else: ?>
                <a class="login" href="<?php echo wp_login_url(get_permalink()); ?>">Login</a> &bull;
            <?php endif; ?>
    	    <a href="http://campanhacompleta.com.br" title="Campanha Completa"><img src="<?php bloginfo( 'template_url' ); ?>/img/campanha-completa.png" alt="" /></a> &bull; 
		    <a href="http://wordpress.org"><img src="<?php bloginfo( 'template_url' ); ?>/img/wp.png" alt="" /></a>
		</p>
	</footer>
	<!-- #main-footer -->
</div>
<!-- .wrap --> 
<?php wp_footer(); ?>
</body>
</html>
