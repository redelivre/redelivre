<?php
/*
Template Name: Preços
*/
?>
<?php get_header(); ?>
	<section id="precos">			
		<?php if ( have_posts()) : while ( have_posts()) : the_post(); ?>			
			<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>	  
				<header>                       
					<h1><?php the_title();?></h1>
					<p><?php edit_post_link( __( 'Edit', 'campanha' ), '', '' ); ?></p>				
				</header>
				<div class="post-content clearfix">
					<?php the_content(); ?>
					<table>
						<thead>
							<th class="cel-4"></th>
							<th class="cel-2">1</th>
							<th class="cel-2">2</th>
							<th class="cel-2">3</th>
							<th class="cel-2">4</th>
						</thead>
						<tr>
							<th class="feature">Site ou Blog</th>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
						</tr>
						<tr>
							<th class="feature">Mobilização nas redes sociais</th>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
						</tr>
						<tr>
							<th class="feature">Envio de email e SMS</th>
							<td>5<span> mil envios</span></td>
							<td>10<span> mil envios</span></td>
							<td>20<span> mil envios</span></td>
							<td>50<span> mil envios</span></td>
						</tr>
						<tr>
							<th class="feature">Upload de arquivos</th>
							<td>1G</td>
							<td>2G</td>
							<td>3G</td>
							<td>ilimitado</td>
						</tr>
						<tr>
							<th class="feature">Geração de material gráfico</th>
							<td class="nao">não</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
						</tr>
						<tr>
							<th class="feature">Gerenciamento de contatos</th>
							<td class="nao">não</td>
							<td class="nao">não</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
						</tr>
						<tr>
							<th class="feature">Suporte via fórum</th>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
						</tr>
						<tr>
							<th class="feature">Suporte por e-mail</th>
							<td class="nao">não</td>
							<td class="nao">não</td>
							<td class="sim">sim</td>
							<td class="sim">sim</td>
						</tr>
						<tr class="last">
							<th class="feature">Valor anual</th>
							<td class="valor">R$1.300,00</td>
							<td class="valor">R$1.800,00</td>
							<td class="valor">R$2.500,00</td>
							<td class="valor">R$3.500,00</td>
						</tr>
					</table>
				</div>
				<!-- .post-content -->
			</article>
			<!-- .page -->
		<?php endwhile; ?>				
		<?php else : ?>
		   <p><?php _e('No results found.', 'campanha'); ?></p>              
		<?php endif; ?>
	</section>
	<!-- #main-section -->
<?php get_footer(); ?>
