<?php
/*
Template Name: Home
*/
?>
<?php get_header(); ?>
    <section id="home-main-section" class="clearfix">
		<div id="logo"><?php html::image('logao.png','Campanha Completa') ?></div>
		<nav id="prev"><a>Anterior</a></nav>
		<div id="janela">
			<article id="frase-1" class="frase current">
				<h2><a href="<?php echo get_page_link(2); ?>#blogousite">Monte seu site ou blog personalizado em minutos.</a></h2>
				<p>São várias opções de layout para sua escolha.</p>
			</article>
			<article id="frase-2" class="frase">
				<h2><a href="<?php echo get_page_link(2); ?>#mobilize">Gerencie sua presença nas redes sociais em um único lugar.</a></h2>
				<p>Publique ao mesmo tempo em seu site e nas redes.</p>
			</article>
			<article id="frase-3" class="frase">
				<h2><a href="<?php echo get_page_link(2); ?>#contatos">Organize e compartilhe contatos entre sua equipe com segurança.</a></h2>
				<p>Todos contatos em um só lugar com níveis de acesso.</p>
			</article>
			<article id="frase-4" class="frase">
				<h2><a href="<?php echo get_page_link(2); ?>#jaiminho">Envie sua campanha por email e sms em massa.</a></h2>
				<p>Visualize relatórios e meça os resultados da campanha.</p>
			</article>
			<article id="frase-5" class="frase">
				<h2><a href="<?php echo get_page_link(2); ?>#georeferenciamento">Mapeie sua campanha com mapas do Google ou OpenStreet.</a></h2>
				<p>Explores as posibilidades dos mapas interativos.</p>
			</article>
			<article id="frase-6" class="frase">
				<h2><a href="<?php echo get_page_link(2); ?>#materialgrafico">Monte você mesmo seus  próprios materiais gráficos.</a></h2>
				<p>Gerador de santinhos, colinhas e flyers.</p>
			</article>
		</div>
		<nav id="next"><a>Próximo</a></nav>       
    </section>
    <!-- #main-section -->
    <hr />
    <section id="features" class="clearfix">
		<h3 class="sites textcenter frase-1 active" data-frase="frase-1">Site ou Blog</h3>
		<h3 class="redes textcenter frase-2" data-frase="frase-2">Redes Sociais</h3>
		<h3 class="contatos textcenter frase-3" data-frase="frase-3">Contatos</h3>
		<h3 class="email textcenter frase-4" data-frase="frase-4">E-mail e SMS</h3>
		<h3 class="mapas textcenter frase-5" data-frase="frase-5">Mapas</h3>
		<h3 class="material textcenter frase-6" data-frase="frase-6">Material Gráfico</h3>				
	</section>
	<hr />
	<!--<form id="mailing" class="col-12" method="get" action="">
		<div class="clearfix">
			<p class="alignleft">
				<span id="feedback">Cadastre-se e fique informado sobre o lançamento.</span><br />
				<span id="feedbackpequeno">Seu email não será compartilhado.</span>
			</p>
			<input id="emailinput" class="alignleft" type="email" name="email" value="digite seu email" onfocus="if (this.value == 'digite seu email') this.value = '';" onblur="if (this.value == '') {this.value = 'digite seu email';}" />
			<input class="alignleft" type="image" id="sendemail" src="img/ok.png" />
		</div>
	</form>-->
	<div id="cadastre-se">
	    <?php if (!is_user_logged_in()): ?>
    		<h2>Cadastre-se gratuitamente e faça um teste.</h2>
    		<p>Você só paga na hora de publicar seu site ou blog.</p>
    		<?php require(TEMPLATEPATH . '/register_form.php'); ?>
		<?php endif; ?>
	</div>

	<section id="destaques" class="clearfix">
		<div id="destaque-passo">
			<h3>Passo a Passo</h3>
			<p>Comece sua Campanha Completa antes de seus concorrentes.</p>
			<a href="<?php echo get_page_link(772); ?>">Veja como é rápido »</a>
		</div>
		<div id="destaque-planos">
			<h3>Planos e Preços</h3>
			<p>Tenha sua Campanha Completa pagando a partir de <strong>R$1.300,00</strong>.</p>
			<a href="<?php echo get_page_link(501); ?>">Compare nossos planos »</a>
		</div>
		<div id="destaque-representante">
			<h3>Seja um representante</h3>
			<p>Represente o Campanha Completa e ganhe <strong>10%</strong> das vendas.</p>
			<a href="<?php echo get_page_link(156); ?>">Saiba como »</a>
		</div>
	</section>
	
<?php get_footer(); ?>
