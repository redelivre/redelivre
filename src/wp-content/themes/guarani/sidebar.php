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
						<p><?php printf( __( 'To display your widgets here go to the <a href="%s">Widget Page</a> and drag them into the "Sidebar" box.', 'guarani' ), admin_url( 'widgets.php' ) ); ?></p>
					</aside>
				<?php endif; ?>

			<?php endif; // end sidebar widget area ?>
		</div><!-- #secondary .widget-area -->
