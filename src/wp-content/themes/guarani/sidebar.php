<?php
/**
 * The Sidebar containing the main widget areas.
 *
 * @package guarani
 * @since guarani 1.0
 */
?>
		<div id="secondary" class="widget-area" role="complementary">
			<?php do_action( 'before_sidebar' ); ?>
			<?php if ( ! dynamic_sidebar( 'sidebar-main' ) ) : ?>

				<?php if ( current_user_can( 'publish_posts' ) ): ?>
					<aside class="empty-feature widget">
						<p>Para exibir widgets aqui acesse o <a href="<?php echo admin_url( 'widgets.php' ); ?>">painel de administração</a> e arraste widgets para o box "Sidebar".</p>
					</aside>
				<?php endif; ?>

			<?php endif; // end sidebar widget area ?>
		</div><!-- #secondary .widget-area -->
