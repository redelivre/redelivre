<?php get_header() ?>

	<div class="content">
		<div id="noticias">
		
			<?php
			query_posts( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => $destaque, 'caller_get_posts' => 1 ) );
			if ( have_posts() ) : while ( have_posts() ) : the_post();
			?>
			
			<div class="box">
				<div <?php post_class(); ?>>
					<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo strip_tags(get_the_title()); ?></a></h2>
					<div class="entry-content"><?php the_excerpt(); ?></div>
				</div>
			</div><!-- .box -->	
			
			
			<?php endwhile; endif; ?>
		</div><!-- #noticias -->		
		<div id="wrapper-transgenicos">
			<div id="transgenicos">
				<h6><a href="<?php echo get_page_link( get_page_by_title( 'Brasil livre de transgênicos e agrotóxicos' )->ID ); ?>" title="Brasil livre de transgênicos e agrotóxicos">Brasil livre de transgênicos e agrotóxicos</a></h6>
				
				<?php
				query_posts( array( 'posts_per_page' => 3, 'post_type' => 'campanha', 'caller_get_posts' => 1 ) );
				if ( have_posts() ) :
					$i = 1;
				
					while ( have_posts() ) : the_post();
				?>
				
				<?php if ( $i == 1 ) : ?>
				
				<div id="boletim-transgenicos">
					<?php
					if ( has_post_thumbnail() ) : 
						the_post_thumbnail( 'campanha' );
					else : ?>
						<img src="<?php bloginfo( 'stylesheet_directory' ); ?>/images/icone-boletim.png" alt="Boletim" />
					<?php
					endif;
					?>
					
					<div class="hentry">
						<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo strip_tags(get_the_title()); ?></a></h3>
						<?php the_excerpt(); ?>
					</div>
					
				</div>
				<?php $i++; ?>
				<?php else : ?>
				
				<div id="noticias-transgenicos">
					
					<div <?php post_class(); ?>>
						<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo strip_tags(get_the_title()); ?></a></h3>
						<?php the_excerpt(); ?>
					</div>
					
				</div><!-- #noticias-transgenicos -->
				<?php endif; ?>
				<?php endwhile; endif; ?> 
				
				<div class="jaiminho">
					<div class="jaiminho-excerpt">Assine o boletim da campanha</div>
					<form method="post" action="http://boletimtransgenicos.campanhasdemkt.net/recebeForm.php">
						<input type="hidden" name="uniqid" value="1095113343500058" />
						<input type="hidden" name="senha" value="6073f5c62f95697a09c68b2546e7c50a" />
						<input type="hidden" name="id_sender_email" value="2179" />
						<input type="hidden" name="urlredir" value="http://aspta.org.br/campanha/inscrever/" />
						<input type="hidden" name="subscribe[1597]" value="1" />
						<input class="jaiminho-text" name="email" type="text"  value="" />
	               		<input class="jaiminho-submit" type="submit" value="Cadastrar" />
					</form>
				</div><!-- .newletter -->
			</div><!-- #transgenicos -->
		</div><!-- #wrapper-transgenicos -->
				
		<div id="wrapper-revista">
			<div id="revista">
				<h6><a href="<?php echo get_page_link( get_page_by_title( 'Revista Agriculturas' )->ID ); ?>" title="Revista Agriculturas">Revista Agriculturas</a></h6>
				<?php
				query_posts( array( 'post_type' => 'revista', 'posts_per_page' => 1, 'post_parent' => 0, 'caller_get_posts' => 1 ) );
				if ( have_posts() ) : while ( have_posts() ) : the_post();
				?>
			
				<div <?php post_class(); ?>>
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_revista_thumbnail('large'); ?></a>
					<h3 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo strip_tags(get_the_title()); ?></a></h3>
				</div>
				<?php endwhile; endif; ?>
				
			</div><!-- #home-revista -->
		</div><!-- #wrapper-revista -->
	</div><!-- .content -->

<?php get_sidebar(); ?>	
<?php get_footer() ?>
