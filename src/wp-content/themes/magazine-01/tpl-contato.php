<?php get_header(); ?>
		<?php get_sidebar(); ?>
		<section id="main-section" class="col-8">			
			<?php if (get_option('campanha_contact_enabled')) : ?>
				<article id="post-<?php the_ID(); ?>" class="clearfix"> 	  
					<header>                       
						<h1>Entre em contato</h1>				
					</header>
					<?php $contactText = get_option('campanha_contact_page_text'); ?>
					<p><?php echo nl2br($contactText); ?></p>
					<div class="post-content clearfix">
						<?php if (function_exists('campanha_the_contact_form')) campanha_the_contact_form(); ?>	
					</div>
					<!-- .post-content -->
				</article>
				<!-- .page -->			
			<?php else : ?>
			   <p><p>Essa página está desabilitada</p></p>              
			<?php endif; ?>
		</section>
		<!-- #main-section -->   
<?php get_footer(); ?>
