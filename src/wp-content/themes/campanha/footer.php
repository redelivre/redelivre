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
						<span class="email">contato@redelivre.org.br</span>
					</address>
				</section>
				<section>
					<h1>Redes Sociais</h1>
					<a id="facebook" href="https://www.facebook.com/CampanhaCompleta" target="_blank">Facebook</a>
					<a id="twitter" href="https://twitter.com/#!/campanhacomplet" target="_blank">Twitter</a>
				</section>
			</div>
			<!-- .wrap -->
		</footer>
		<!-- #main-footer -->
	<?php wp_footer(); ?>
	</body>
</html>
