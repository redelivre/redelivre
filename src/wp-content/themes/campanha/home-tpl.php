<?php
/*
Template Name: Home
*/
?>
<?php get_header(); ?>
    <section id="home-main-section" class="clearfix">
		<div id="logo"><?php html::image('logao.png','Projetos') ?></div>
		<nav id="prev"><a>Anterior</a></nav>
		<div id="janela">
			<article id="frase-0" class="frase current">
				<h2>Organize sua campanha e mobilize o eleitor!</h2>
				<p>Com a experiência de mais de 200 candidatos atendidos</p>
			</article>
            <article id="frase-1" class="frase ">
				<h2><a href="<?php echo get_page_link(2); ?>#blogousite">Monte seu site ou blog personalizado em minutos.</a></h2>
				<p>São várias opções de layout para sua escolha.</p>
			</article>
			<article id="frase-2" class="frase">
				<h2><a href="<?php echo get_page_link(2); ?>#mobilize">Integre suas redes sociais no seu site de campanha.</a></h2>
				<p>Ferramentas de mobilização reunidas em um só lugar.</p>
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
				<p>Explore as possibilidades dos mapas interativos.</p>
			</article>
		</div>
		<nav id="next"><a>Próximo</a></nav>       
    </section>
    <!-- #main-section -->
    <hr />
    <section id="features" class="clearfix">
		<h3 class="sites textcenter frase-1 " data-frase="frase-1">Site ou Blog</h3>
		<h3 class="redes textcenter frase-2" data-frase="frase-2">Redes Sociais</h3>
		<h3 class="contatos textcenter frase-3" data-frase="frase-3">Contatos</h3>
		<h3 class="email textcenter frase-4" data-frase="frase-4">E-mail e SMS</h3>
		<h3 class="mapas textcenter frase-5" data-frase="frase-5">Mapas</h3>
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
			<p>Comece sua Projetos antes de seus concorrentes.</p>
			<a href="<?php echo get_page_link(772); ?>">Veja como é rápido »</a>
		</div>
		<div id="destaque-planos">
			<h3>Planos e Preços</h3>
			<p>Tenha sua Projetos pagando a partir de <strong>R$450,00</strong>.</p>
			<a href="<?php echo get_page_link(501); ?>">Compare nossos planos »</a>
		</div>
		<div id="destaque-representante">
			<h3>Seja um representante</h3>
			<p>Represente o Projetos e ganhe <strong>10%</strong> das vendas.</p>
			<a href="<?php echo get_page_link(156); ?>">Saiba como »</a>
		</div>
	</section>
	
<?php get_footer(); ?>
