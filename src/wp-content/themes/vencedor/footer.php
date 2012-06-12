	<div id="footer" class="wrap clearfix">					
		<?php wp_nav_menu( array( 'theme_location' => 'rodape', 'container' => false, 'menu_id' => 'footer-nav', 'menu_class' =>'col-8 clearfix', 'fallback_cb' => '', 'depth' =>'1' ) ); ?>
		<p class="creditos textright col-4">Tema Vencedor &bull; <a href="http://campanhacompleta.com.br" title="Campanha Completa">Campanha Completa</a> &bull; <a href="http://wordpress.org"><img src="<?php bloginfo( 'stylesheet_directory' ); ?>/img/wp.png" alt="" /></a></p>
	</div>
	<!-- #footer -->
<?php wp_footer(); ?>
</body>
</html>
