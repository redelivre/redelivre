		</div>
		<!-- .wrap -->
		<footer id="main-footer">
			<div class="wrap clearfix">	
				<section>
					<h1>Sobre</h1>
					<?php wp_nav_menu( array( 'theme_location' => 'sobre', 'container' => '', 'menu_id' => 'sobre-menu', 'menu_class' => 'clearfix', 'fallback_cb' =>'', 'depth' => '1') ); ?>
				</section>
				<section>
					<h1>Informações Legais</h1>
					<?php wp_nav_menu( array( 'theme_location' => 'info', 'container' => '', 'menu_id' => 'info-legais', 'menu_class' => 'clearfix', 'fallback_cb' =>'', 'depth' => '1') ); ?>
				</section>
				<section>
					<h1>Contato</h1>
					<address class="vcard adr">
						<span class="tel">41 3077 4163</span><br />
						<span class="email">contato@campanhacompleta.com.br</span>
					</address>
				</section>
				<section>
					<h1>Redes Sociais</h1>
					<a id="facebook" href="#">Facebook</a>
					<a id="twitter" href="#">Twitter</a>
				</section>
			</div>
			<!-- .wrap -->
		</footer>
		<!-- #main-footer -->
	<?php wp_footer(); ?>
	</body>
</html>
