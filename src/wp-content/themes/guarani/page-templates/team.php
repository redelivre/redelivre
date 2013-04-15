<?php
/**
 * Template Name: Team
 *
 * @since Guarani 1.0
 */
__( 'Team', 'guarani' );

get_header(); ?>

		<div id="primary" class="content-area">
			<div id="content" class="site-content" role="main">

				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'page' ); ?>
					
					<div class="entry-team">
						<?php
						
						$equipe = get_post_meta( $post->ID, '_guarani_team', true );	
	
						if ( $equipe ) :
							
							// Transforma em minúsculas e cria um array
							$equipe = strtolower( $equipe );
							$equipe = explode( ',', $equipe );
								
			                foreach( $equipe as $membro_equipe ) :

			                	// Verifica se o username digitado existe
			                	if ( username_exists( $membro_equipe ) ) :
				                	$membro = get_user_by( 'login', $membro_equipe ); ?>
				                	
									<div class="member-details cf">
										<div class="member-avatar author-avatar">
											<?php echo get_avatar( $membro->user_email, 128 ); ?>
										</div>
										<h3 class="member-name author-name"><?php echo $membro->display_name; ?> <span class="member-title"></span></h3>
										<?php guarani_user_social( $membro->ID ); ?>
										<?php if ( $membro->description ) : ?>
											<div class="member-description author-description">
												<p><?php echo $membro->description; ?></p>
											</div><!-- /author-description	-->
										<?php endif; ?>
									</div><!-- .team-member -->
								<?php
								endif;
							endforeach;
						endif;
						
						?>
					</div><!-- .entry-team -->

					<?php comments_template( '', true ); ?>

				<?php endwhile; // end of the loop. ?>

			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>