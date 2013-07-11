<?php
function get_delibera_header() {
$opt = delibera_get_config();
	?>
	
	<div id="delibera-header">
		<?php
		
		$h = ( is_post_type_archive ( 'pauta' ) ) ? 'h1' : 'h2';
		
		$delibera_header = '<' . $h . ' class="page-title"><span>';
		$delibera_header .= __( 'Sistema de Discussão', 'delibera' );
		$delibera_header .= '</span></' . $h . '>';
		
		echo $delibera_header;
		
		?>
		<div class="delibera-apresentacao">
	        <p class="delibera-boasvindas">
    	    	<?php
    	    			echo $opt['cabecalho_arquivo'];
    	    	?>
        	</p>
            <p class="delibera-participacao">
            	<a href="<?php echo get_page_link( get_page_by_slug( DELIBERA_ABOUT_PAGE )->ID ); ?>"><?php _e( 'Saiba por que e como participar', 'delibera' ); ?></a>
            </p>
        </div>
		<p class="delibera-login">
			<?php
			if ( is_user_logged_in() )
			{
				global $current_user;
				get_currentuserinfo();
				
            	printf(
            		__( 'Você está logado como <a href="%1$s" title="Ver meu perfil" class="profile">%2$s</a>. Caso deseje sair de sua conta, <a href="%3$s" title="Sair">faça o logout</a>.', 'delibera' ),
            		get_author_posts_url($current_user->ID),
            		$current_user->display_name,
            		wp_logout_url( home_url( '/' ) )
            	);
			} 		
			else
			{	
				printf(
            		__( 'Para participar, você precisa <a href="%1$s" title="Faça o login">fazer o login</a> ou <a href="%2$s" title="Registre-se" class="register">registrar-se no site</a>.', 'delibera' ), 
            		wp_login_url( get_permalink() ),
            		site_url('wp-login.php?action=register', 'login')."&lang="
            	);
            							
			}
			?>
		</p><!-- .delibera-login -->
		
		<?php if ( ! ( is_home() || is_post_type_archive( 'pauta' ) ) ) : ?>
			<p class="delibera-pagina-discussoes"><a href="<?php echo get_post_type_archive_link( 'pauta' ); ?>"><?php _e( 'Voltar à página de discussões', 'delibera' ); ?></a></p>
		<?php endif; ?>
	</div><!-- #delibera-header -->

	<?php
}
?>