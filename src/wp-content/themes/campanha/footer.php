		</div>
		<!-- .wrap -->
		<footer id="main-footer">
			<div class="wrap clearfix">	
				<div class="col-3">
					<h1>Sobre o Campanha Completa</h1>
					<?php wp_nav_menu( array( 'theme_location' => 'sobre', 'container' => '', 'menu_id' => 'sobre-menu', 'menu_class' => 'clearfix', 'fallback_cb' =>'', 'depth' => '1') ); ?>
				</div>
				<div class="col-3">
					<h1>Informações Legais</h1>
					<?php wp_nav_menu( array( 'theme_location' => 'info', 'container' => '', 'menu_id' => 'info-legais', 'menu_class' => 'clearfix', 'fallback_cb' =>'', 'depth' => '1') ); ?>
				</div>
				<div class="col-3">
					<h1>Contato</h1>
					<address class="vcard adr">
						<span class="tel">41 3077 4163</span>
						<span class="email">contato@campanhacompleta.com.br</span>
					</address>
				</div>
				<div class="col-3">
					<h1>Redes Sociais</h1>
					<a id="facebook" href="#">Facebook</a>
					<a id="twitter" href="#">Twitter</a>
				</div>
			</div>
			<!-- .wrap -->
		</footer>
		<!-- #main-footer -->
	<?php wp_footer(); ?>
	</body>
</html>
