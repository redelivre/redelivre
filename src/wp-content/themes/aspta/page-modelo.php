<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_url' ); ?>/extensions/960/code/css/reset.css" />
<link rel="stylesheet" type="text/css" media="all" href="../../extensions/960/code/css/text.css" />
<link rel="stylesheet" type="text/css" media="all" href="../../wireframe/extensions/960/code/css/960.css" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
</head>

<body <?php body_class(); ?>>
<div id="wrapper" class="container_16">
	<div id="header">
		<div id="masthead">
			<div class="grid_6 suffix_4">
				<div id="menu-superior">
					<ul>
						<li><a href=#">Quem Somos</a></li>
						<li><a href=#">Contato</a></li>
					</ul>
				</div>
			</div>
			
			<div class="grid_6">
				<div id="feed">
					<a href=#">Assine o feed</a>
				</div>
			</div>			
			
			<hr/>
			
			<div class="grid_9">
				<div id="branding" role="banner">
				<?php //$heading_tag = ( is_home() || is_front_page() ) ? 'h1' : 'div'; 
				$heading_tag = 'h1';
				?>
				
				
				
					<<?php echo $heading_tag; ?> id="site-title">
					<span>
						<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">AS &middot PTA</a>
					</span>
					</<?php echo $heading_tag; ?>>
					
					<div id="site-description">Agricultura familiar e agroecologia</div>
				
				</div><!-- #branding -->
			</div>
				
				<div class="grid_7">
					<div id="pesquisa">
						<input type="text" value="Digite sua pesquisa" /><input type="button" value="Pesquisar" />
					</div>
				</div>
				
				<div class="clear"></div>

			
			
			<hr/>
			
			

			<div id="access" role="navigation">
			  <?php /*  Allow screen readers / text browsers to skip the navigation menu and get right to the good stuff */ ?>
				<?php /* Our navigation menu.  If one isn't filled out, wp_nav_menu falls back to wp_page_menu.  The menu assiged to the primary position is the one used.  If none is assigned, the menu with the lowest ID is used.  */ ?>
				<?php // wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' ) ); ?>
				<ul>
					<li><a href=#>Temas de intervenção</a></li>
					<li><a href=#>Programas locais</a></li>
					<li><a href=#>Biblioteca</a></li>
					<li><a href=#>Revista</a></li>
					<li><a href=#>Campanha</a></li>
				</ul>
			</div><!-- #access -->
			
			<hr/>
			
		</div><!-- #masthead -->
	</div><!-- #header -->
	<?php if ( !is_home() ) : ?>
	<div class="breadcrumbs">
		Você está aqui: <a href="#">Capa</a> &raquo; Quem somos
	</div> 
	<?php endif; ?>

	<div id="main">


<!-- /header -->

<?php get_header(); ?>



<!-- container -->
		<div id="container">
			<div id="content" role="main">
			
			<div class="apresentacao">
				<div class="grid_9 alpha">
					<h2><a href="#">Agricultoras da Borborema conhecem fogões ecológicos em Afogados de Ingazeira</a></h2>
					
					<p>Prosseguindo com as atividades do Projeto Agroecologia na Borborema, financiado pela Petrobras Ambiental, 23 agricultoras e três agricultores da Paraíba tiveram a oportunidade de conhecer as experiências com fogões ecológicos em Afogados de Ingazeira, Pernambuco.</p>
					<a class="button" href="#">Continue lendo</a>
				</div>
				
				<div class="grid_7 omega">
					<img src="http://placehold.it/420x240/ebebeb/dddddd&text=foto+destaque" />
					
				</div>
				
				<div class="clear"></div>  
			
			</div>
			
			<hr/>
			
			
			<?php /*
			
			<div class="noticias">
				<div class="grid_3">
					<img src="http://dummyimage.com/220x200/eee/ddd" />
				</div>
				
				<div class="grid_5">
					<h1>Um belo título de matéria</h1>
					<p>
					A primeira Conferência Nacional de Comunicação representa indiscutivelmente uma importante vitória das forças progressistas no Brasil. Especialmente a TV Globo e o jornal “O Globo” dedicaram espaços para destruir a imagem desta primeira Confecom na história do Brasil, como se não fosse possível fazer um evento democrático na área da comunicação sem a anuência destes [...]
					</p>
				</div>
				
				<div class="grid_4">
					<em>O Fórum Social São Paulo é uma iniciativa política de organizações da sociedade civil que atuam nessa região metropolitana e acreditam que “outra cidade é possível, necessária e urgente”.</em>
				</div>
				
				<div class="clear"></div>  
			
			</div>
			
			<hr/>
			
			*/ ?>
			
			<div class="grid_12">
				
				<div class="grid_4 alpha">
				<h6>Notícias</h6>
				<h6><a href="#">Mutirões valorizam a vida na Agricultura Familiar</a></h6>
				<p>O Polo Sindical e das Organizações da Borborema, juntamente com a AS-PTA, vem construindo desde 2002 um Programa de Formação em Agroecologia voltado especificamente para crianças, adolescentes e jovens filhos de agricultores familiares da região. </p>
				</div>
				
				<div class="grid_4">
				<h6>Notícias</h6>
				<h6><a href="#">Multiplicação de sementes crioulas mobiliza assentamentos no Paraná</a></h6>
				<p>As sementes crioulas fazem parte do patrimônio cultural de milhares de famílias agricultoras de norte a sul do Brasil. Entretanto, com a modernização da agricultura, sobretudo com a introdução de sementes melhoradas e transgênicas, essa riqueza se encontra cada vez mais ameaçada. </p>
				</div>
				
				<div class="grid_4 omega">
				<h6>Notícias</h6>
				<h6><a href="#">Campos de palmas nos arredores de casa incrementam a criação animal na Borborema</a></h6>
				<p>O consórcio de palma com o cultivo de árvores forrageiras e frutíferas dos arredores de casa e a criação de pequenos animais nesse mesmo espaço tem sido uma boa solução para famílias agricultoras que, em geral, não dispõem de muitas terras para exercer suas atividades produtivas. </p>
				</div>
				
				<hr/>
				
				
				<div class="grid_8 alpha">
					<div id="campanha">
						<div id="boletim-campanha">
							<h6>Campanha &ldquo;Por um Brasil livre de transgênicos&rdquo;</h6>
							<img src="http://placehold.it/150x150/ebebeb/dddddd&text=Boletim" />
							
							<div class="hentry">
								<h6><a href="#">Boletim 514 &ndash; 05 de novembro de 2010</a></h6>
								<p>Publicado novo estudo relacionando o consumo de alimentos transgênicos a problemas no fígado e nos rins</p>
							</div>
							
							<div class="clear"></div>
							
							<div id="noticias-campanha">
								<div class="grid_4 alpha">
									<h6><a href="#">Encontro Mulheres, Agroecologia e Plantas medicinais</a></h6>
									<p>Entretanto, com a modernização da agricultura, sobretudo com a introdução de sementes melhoradas e transgênicas, essa riqueza se encontra cada vez mais ameaçada. </p>
								</div>
								
								<div class="grid_4 omega">
									<h6><a href="#">Multiplicação de sementes crioulas mobiliza assentamentos no Paraná</a></h6>
									<p>As sementes crioulas fazem parte do patrimônio cultural de milhares de famílias agricultoras de norte a sul do Brasil.</p>
								</div>
							</div>
							
						</div>
						<div class="clear"></div>
					</div><!-- #campanha -->
					
				</div>
				
				<div class="grid_4 omega">
					<div id="revista">
					<h6>Revista Agriculturas</h6>
					<img src="http://placehold.it/193x253/ebebeb/dddddd&text=Revista" />
					<p><a href="#"><em>Volume 7, n° 3</em> &bull; Água nos agroecossistemas: aproveitando todas as gotas</a></p>
				</div>
				</div>
			
				<div class="clear"></div>
			</div>
			
			<div class="grid_4">
				<?php /*
				
				<div class="agenda">
					<h6>Agenda</h6>
					<h5><a href="#">Evento #1 em São Paulo</a></h5>
					<p>09/11/10 <em>São Paulo, SP</em></p>
					
					<h5><a href="#">A valorização de fatores subjetivos</a></h5>
					<p>09/11/10 <em>São Paulo, SP</em></p>
					
					<h5><a href="#">Evento #3 em São Paulo</a></h5>
					<p>09/11/10 <em>São Paulo, SP</em></p>
				</div>
				
				<hr/>
				*/ ?>
				
				<div id="boletim">
					<h6>Boletim</h6>
					<p>Receba os boletins da Campanha.<br/><br/>
					<input type="text" /><input type="button" value="Assinar" /></p>
				</div>
				
				<hr/>
				
				<div id="banners">
					<img src="http://placehold.it/200x125/ebebeb/dddddd&text=Banner+1" />
					<img src="http://placehold.it/200x125/ebebeb/dddddd&text=Banner+2" />
					<img src="http://placehold.it/200x125/ebebeb/dddddd&text=Banner+3" />
					<img src="http://placehold.it/200x125/ebebeb/dddddd&text=Banner+4" />
				</div>
				
			</div>

			<?php
			/* Run the loop to output the posts.
			 * If you want to overload this in a child theme then include a file
			 * called loop-index.php and that will be used instead.
			 */
			 //get_template_part( 'loop', 'index' );
			?>
			</div><!-- #content -->
		</div><!-- #container -->
<!-- /container -->


<!-- footer -->

</div><!-- #main -->
	<hr/>
	<div id="footer" role="contentinfo">
		<div id="colophon">

			<div id="site-info" class="grid_6 suffix_4">
				<a href="<?php echo home_url( '/' ) ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?> &bull; <?php bloginfo( 'description' ); ?>
				</a>
			</div><!-- #site-info -->

			<div id="site-generator" class="grid_6">
				<a href="<?php echo esc_url( __('http://wordpress.org/', 'twentyten') ); ?>"
						title="<?php esc_attr_e('Semantic Personal Publishing Platform', 'twentyten'); ?>" rel="generator">
					Desenvolvido pela Ethymos com WordPress
				</a>
			</div><!-- #site-generator -->
			
			<div class="clear"></div>

		</div><!-- #colophon -->
	</div><!-- #footer -->
	
</div><!-- #wrapper -->

<?php
	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */

	wp_footer();
?>
</body>
</html>


