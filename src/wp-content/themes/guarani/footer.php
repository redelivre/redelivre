<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package Guarani
 * @since Guarani 1.0
 */
?>

		</section><!-- #main .site-main -->
		
		<?php if ( is_active_sidebar( 'sidebar-footer' ) ) : ?>
			<section id="tertiary" class="sidebar-container cf" role="complementary">
				<div class="widget-area">
					<?php dynamic_sidebar( 'sidebar-footer' ); ?>
				</div>
			</section><!-- #tertiary -->
		<?php
		else:
			if ( current_user_can( 'publish_posts' ) ): ?>
				<div class="empty-feature widget">
					<p><?php printf( __( 'To display your widgets here go to the <a href="%s">Widget Page</a> and drag them into the "Footer Widget Area".', 'guarani' ), admin_url( 'widgets.php' ) ); ?></p>
				</div>
			<?php
			endif;
		endif; ?>
	
		<footer id="colophon" class="site-footer cf" role="contentinfo">
			<div class="site-info">
				<?php do_action( 'guarani_credits' ); ?>
				<a href="<?php site_url(); ?>" class="site-url"><?php bloginfo( 'name' ); ?></a><br>
				<?php if ( $contact_info = get_option('campanha_contact_footer' ) ) : ?>
				<div class="site-contact-info">
					<?php echo nl2br( $contact_info ); ?>
				</div><!-- .site-contact-info -->
				<?php endif; ?>
			</div><!-- .site-info -->

	    	<?php do_action( 'campanha_body_header' ); ?>
		</footer><!-- #colophon .site-footer -->
		
	</div><!-- .site-wrapper .hfeed .site -->
	
	 <div class="guarani-credits cf" >
          <!-- <a href="http://redelivre.org.br/" title="<?php _e( 'Redelivre', 'guarani' ); ?>" class="icon-campanha-completa"><img src="<?php echo get_template_directory_uri() . '/images/icon-campanha-completa.png'; ?>" alt="<?php _e( 'Redelivre logo', 'guarani' ); ?>" /><span class="assistive-text"><?php _e( 'Redelivre', 'guarani' ); ?></span></a>-->
		<a href="http://wordpress.org/" title="<?php esc_attr_e( 'Proudly powered by WordPress', 'guarani' ); ?>" class="icon-wordpress" rel="generator"><img src="<?php echo get_template_directory_uri() . '/images/icon-wordpress.png'; ?>" alt="<?php _e( 'WordPress logo', 'guarani' ); ?>" /><span class="assistive-text"><?php _e( 'Proudly powered by WordPress', 'guarani' ); ?></span></a>
	</div> <!-- .guarani-credits -->
	
	<?php wp_footer(); ?>
	
	</body>
</html>