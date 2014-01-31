<?php get_header(); ?>
  <div class="container miolo">
  
	  <div class="span4 sid-int">
	  	<?php get_sidebar(); ?>
	  </div>
	  
	  <div class="span7">
		<?php if (get_option('campanha_contact_enabled')) : ?>
		    	<section id="main-section" class="wrap clearfix">
		    		<div id="content" class="col-8">
		    			<article id="post-<?php the_ID(); ?>" class="clearfix">                
		    				<!-- customizado por thalita -->
		    				<h2>Contato</h2>
		    				<h4>Fale conosco!</h4>
		    				
		    				<div class="contact borda-cor-1">
								<?php if (get_theme_mod('show-phone-number')): ?>
									<p>
										<span><?php _e('Telefone:', '_mobilize'); ?></span>
										<?php echo esc_html(get_theme_mod('phone-number')); ?>
									</p>
								<?php endif; ?>
								<?php if (get_theme_mod('show-email')): ?>
									<p>
										<span><?php _e('E-mail:', '_mobilize'); ?></span>
										<?php echo esc_html(get_theme_mod('email')); ?>
									</p>
								<?php endif; ?>
								<?php if (get_theme_mod('show-address')): ?>
									<p>
										<span><?php _e('Endereço:', '_mobilize'); ?></span>
										<br>
										<pre><?php echo esc_html(get_theme_mod('address')); ?></pre>
									</p>
								<?php endif; ?>
		    				</div>
		    				
		    				<div class="contact-form borda-cor-1">
			    				<?php $contactText = get_option('campanha_contact_page_text'); ?>
			    				<p><?php echo nl2br($contactText); ?></p>
			    				<div class="post-content">										
			    				    <?php if (function_exists('campanha_the_contact_form')) campanha_the_contact_form(); ?>
			    				</div>
			    			 </div>	
			    				<h3>Sua mensagem é muito importante para nós. Obrigada!</h3>
		    				
		    				<!-- .post-content -->
		    				<footer class="post-footer clearfix">
		    					<?php get_template_part('interaction'); ?>
		    				</footer>
		    			</article>
		    			<!-- .post -->
		    		</div>
		    	    <!-- #content -->
		    		<aside id="sidebar" class="col-4 clearfix">
		    			<?php get_sidebar(); ?>
		    		</aside>
		    	    <!-- #sidebar -->			       
		    	</section>
		        <!-- #main-section -->
		    <?php else : ?>
		        <section id="main-section" class="wrap clearfix">
		            <div id="content" class="col-8">
		                <p>Essa p�gina est� desabilitada</p>
		            </div>
		        </section>
		    <?php endif; ?>
	  </div>
  </div>  
	    
	    
<?php get_footer(); ?>
