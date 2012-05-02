<?php
/*
Template Name: Home
*/
?>
<?php get_header(); ?>
    <section id="home-main-section" class="clearfix">
		<div class="col-1"><a class="prev" href="#">Anterior</a></div>
		<div id="janela" class="col-10">
			<div id="frase-1" class="frase">
				<h2>Monte seu site ou blog personalizado em minutos.</h2>
				<p>São várias opções de layout para sua escolha.</p>
			</div>
			<div id="frase-2" class="frase">
				<h2>Gerencie sua presença nas redes sociais em um único lugar.</h2>
				<p>Publique ao mesmo tempo em seu site e nas redes.</p>
			</div>
			<div id="frase-3" class="frase">
				<h2>Organize e compartilhe contatos entre sua equipe com segurança.</h2>
				<p>Todos contatos em um só lugar com níveis de acesso.</p>
			</div>
			<div id="frase-4" class="frase">
				<h2>Envie sua campanha por email e sms em massa.</h2>
				<p>Visualize relatórios e meça os resultados da campanha.</p>
			</div>
			<div id="frase-5" class="frase">
				<h2>Mapeie sua campanha com mapas do Google ou OpenStreet.</h2>
				<p>Explores as posibilidades dos mapas interativos.</p>
			</div>
			<div id="frase-6" class="frase">
				<h2>Monte você mesmo seus  próprios materiais gráficos.</h2>
				<p>Gerador de santinhos, colinhas e flyers.</p>
			</div>
		</div>
		<div class="col-1"><a class="next" href="#">Próximo</a></div>       
    </section>
    <!-- #main-section -->
    <hr class="col-12" />
    <section id="features" class="clearfix">
		<h3 class="sites col-2 textcenter">Site ou Blog</h3>
		<h3 class="redes col-2 textcenter">Redes Sociais</h3>
		<h3 class="contatos col-2 textcenter">Contatos</h3>
		<h3 class="email col-2 textcenter">E-mail e SMS</h3>
		<h3 class="mapas col-2 textcenter">Mapas</h3>
		<h3 class="material col-2 textcenter">Material Gráfico</h3>				
	</section>
	<hr class="col-12" />
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
	<div id="cadastre-se" class="col-12">
		<h2>Cadastre-se gratuitamente e faça um teste.</h2>
		<p>Você só paga na hora de escolher um plano e publicar seu site ou blog.</p>
		<form id="form-cadastre-se" class="clearfix" method="get" action="">
			<div class="fields">
				<label for="emailinput">email</label>
				<input id="emailinput" class="alignleft" type="email" name="email" value="" />
			</div>
			<div class="fields">
				<label for="password">senha</label>
				<input id="password" class="alignleft" type="password" name="password" />
			</div>
			<input id="register-submit" class="alignleft" type="submit" name="" value="criar conta gratuita" />
		</form>
	</div>
	<section id="destaques" class="clearfix">
		<div id="destaque-passo" class="col-4">
			<h3>Passo a Passo</h3>
			<p>Comece sua Campanha Completa antes de seus concorrentes.</p>
			<a href="#">Veja como é rápido »</a>
		</div>
		<div id="destaque-planos" class="col-4">
			<h3>Planos e Preços</h3>
			<p>Tenha sua Campanha Completa pagando a partir de <strong>R$1.300,00</strong>.</p>
			<a href="#">Compare nossos planos »</a>
		</div>
		<div id="destaque-representante" class="col-4">
			<h3>Seja um representante</h3>
			<p>Seja um representante do Campanha Completa e ganhe <strong>10%</strong> das vendas.</p>
			<a href="#">Saiba como »</a>
		</div>
	</section>
	
<?php get_footer(); ?>
