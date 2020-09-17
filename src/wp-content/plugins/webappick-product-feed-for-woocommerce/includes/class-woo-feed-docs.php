<?php
/**
 * Feed Docs Page Renderer
 *
 * @version 1.0.0
 * @package WooFeed
 * @since 3.1.36
 */
if ( ! function_exists( 'add_action' ) ) {
	die();
}
if ( ! class_exists( 'WooFeedDocs' ) ) {
	class WooFeedDocs {
		/**
		 * Singleton instance holder
		 *
		 * @var WooFeedDocs
		 */
		private static $instance;

		/**
		 * Get Class Instance
		 *
		 * @return WooFeedDocs
		 */
		public static function getInstance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			add_filter( 'removable_query_args', array( $this, 'filter_removable_query_args' ), 10, 1 );
		}

		/**
		 * Render Docs Page
		 *
		 * @return void
		 * @see Woo_Feed_Admin::load_admin_pages()
		 */
		function woo_feed_docs() {
			$faqs = $this->__get_feed_help();
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			$current_screen = get_current_screen();
			?>
			<div class="wrap wapk-admin wapk-feed-docs">
				<div class="wapk-section">
					<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
					<?php if ( ! empty( $faqs ) ) { ?>
					<?php
					foreach ( $faqs as $faq ) {
							$boxId = ( isset( $faq->id ) ) ? $faq->id : sanitize_title( $faq->title );
							if ( ! isset( $faq->icon ) ) $faq->icon = 'dashicons-admin-generic';
							?>
					<div id="<?php echo esc_attr( $boxId ); ?>" class="postbox <?php echo esc_attr( postbox_classes( $boxId, $current_screen->id ) ); ?>">
						<button type="button" class="handlediv" aria-expanded="true">
							<span class="screen-reader-text">
                            <?php
							/* translators: %s: FAQ Title */
							printf( esc_html__( 'Toggle panel: %s', 'woo-feed' ), esc_html( $faq->title ) )
							?>
                            </span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h2 class="hndle">
							<span class="<?php printf( '%s%s', ( strpos( $faq->icon, 'dashicons' ) !== false ) ? 'dashicons ' : '', esc_attr( $faq->icon ) ); ?>" aria-hidden="true"></span>
							<span><?php echo esc_html( $faq->title ); ?></span>
						</h2>
						<div class="inside">
							<div class="main">
								<ul>
									<?php
									foreach ( $faq->questions as $qa ) {
										if ( ! isset( $qa->icon ) ) $qa->icon = 'dashicons-media-text';
									?>
									<li>
										<span class="<?php printf( '%s%s', ( strpos( $qa->icon, 'dashicons' ) !== false ) ? 'dashicons ' : '', esc_attr( $qa->icon ) ); ?>" aria-hidden="true"></span>
										<a href="<?php echo esc_url( $qa->link ); ?>" target="_blank"><?php echo esc_html( $qa->title ); ?></a>
									</li>
									<?php } ?>
								</ul>
							</div>
						</div>
					</div>
									<?php } ?>
					<?php } else { ?>
					<div class="notice notice-warning">
						<p>
                        <?php
							printf(
							/* translators: %s: Reload Button */
								esc_html__( 'There\'s some problem loading the docs. Please Click %s To Fetch Again.', 'woo-feed' ),
								sprintf(
									'<a href="%s">%s</a>',
									esc_url( admin_url( 'admin.php?page=webappick-feed-docs&reload=1&_nonce=' . wp_create_nonce( 'webappick-feed-docs' ) ) ),
									esc_html__( 'Here', 'woo-feed' )
								)
							);
						?>
                        </p>
					</div>
					<?php } ?>
				</div>
				<div class="clear"></div>
				<div class="wapk-section wapk-feed-cta">
					<div class="wapk-cta">
						<div class="wapk-cta-icon">
							<span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
						</div>
						<div class="wapk-cta-content">
							<h2><?php _e( 'Still need help?', 'woo-feed' ); ?></h2>
							<p><?php _e( 'Have we not answered your question?<br>Don\'t worry, you can contact us for more information...', 'woo-feed' ); ?></p>
						</div>
						<div class="wapk-cta-action">
							<a href="https://webappick.com/my-account/contact-support/" class="wapk-button wapk-button-primary wapk-button-hero" rel="noopener" target="_blank"><?php _e( 'Get Support', 'woo-feed' ); ?></a>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Get Docs Data
		 *
		 * @return array
		 */
		private function __get_feed_help() {
			// force fetch docs json.
			if ( isset( $_GET['reload'], $_GET['_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_GET['_nonce'] ), 'webappick-feed-docs' ) ) {
				$help_docs = false;
			} else {
				$help_docs = get_transient( 'webappick_feed_help_docs' );
			}
			if ( false === $help_docs ) {
				// bitbucket cache-control: max-age=900 (15 minutes)
				$help_url  = 'https://api.bitbucket.org/2.0/snippets/woofeed/jLRxxB/files/woo-feed-docs.json';
				$response  = wp_safe_remote_get( $help_url, array( 'timeout' => 15 ) ); // phpcs:ignore
				$help_docs = wp_remote_retrieve_body( $response );
				if ( is_wp_error( $response ) || 200 != $response['response']['code'] ) {
					$help_docs = '[]';
				}
				set_transient( 'webappick_feed_help_docs', $help_docs, 12 * HOUR_IN_SECONDS );
			}
			$help_docs = json_decode( trim( $help_docs ) );

			return $help_docs;
		}

		/**
		 * Add items to removable query args array
		 *
		 * @param array $removable_query_args
		 *
		 * @return array
		 */
		function filter_removable_query_args( $removable_query_args ) {
			global $pagenow, $plugin_page;
			if ( 'admin.php' === $pagenow && 'webappick-feed-docs' === $plugin_page ) {
				$removable_query_args = array_merge( $removable_query_args, array( 'reload', '_nonce' ) );
			}

			return $removable_query_args;
		}
	}
}
// End of file class-woo-feed-docs.php
