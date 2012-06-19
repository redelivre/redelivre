	<footer id="main-footer" class="wrap clearfix">					
		<?php wp_nav_menu( array( 'theme_location' => 'rodape', 'container' => false, 'menu_id' => 'footer-nav', 'menu_class' =>'col-8 clearfix', 'fallback_cb' => '', 'depth' =>'1' ) ); ?>
		<p class="creditos textright col-4">Tema Vencedor &bull; <a href="http://campanhacompleta.com.br" title="Campanha Completa"><img src="<?php echo get_template_directory_uri(); ?>/img/campanha-completa.png" alt="" /></a> &bull; <a href="http://wordpress.org"><img src="<?php echo get_template_directory_uri(); ?>/img/wp.png" alt="" /></a></p>
	</footer>
	<!-- #main-footer -->
<?php wp_footer(); ?>
</body>
</html>
