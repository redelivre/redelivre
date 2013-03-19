<?php
if ( ! class_exists( 'Automattic_Deprecated_Notice' ) ) {
	// change these two defines based on what plugin you want deprecated notices to show for
	define( 'PLUGIN_NAME', 'Wickett Twitter Widget' );
	define( 'PLUGIN_SLUG', 'wickett-twitter-widget' );

	class Automattic_Deprecated_Notice {
		function &init() {
			// instantiate our class if it does not yet exist
			static $instance = array();

			if ( ! $instance ) {
				$instance[0] =& new Automattic_Deprecated_Notice;
			}

			return $instance[0];
		}

		function Automattic_Deprecated_Notice() {
			// this is run when the class is instantiated

			// enqueue our style and scripts
			if ( ! class_exists( 'Jetpack' ) ) {
				add_action( 'admin_print_styles', array( &$this, 'adn_admin_styles' ) );
				add_action( 'admin_print_scripts', array( &$this, 'adn_admin_scripts' ) );
				add_action( 'admin_head', array( &$this, 'echo_on_plugin_page' ) );
				add_action( 'admin_init', array( &$this, 'dismiss_upgrade_notice' ) );
			}
		}

		function adn_admin_scripts() {
			wp_enqueue_script( 'thickbox' );
		}

		function adn_admin_styles() {
			wp_enqueue_style ( 'thickbox' );
			wp_enqueue_style ( 'jetpack', plugins_url( 'jetpack.css', __FILE__ ), false, '20110824' );
		}

		function upgrade_notice() {
			static $shown = false;

			if ( $shown ) {
				return;
			}

			$is_notice_dismissed = get_option( 'adn_dismiss_notice_' . PLUGIN_SLUG );
			
			if ( $is_notice_dismissed ) {
				return;
			}
			
			if ( class_exists( 'Jetpack' ) ) {
				return;
			}

			$shown = true;

			if ( file_exists( WP_PLUGIN_DIR . '/jetpack' ) )
				$message = __( 'Enable Jetpack Now!' );
			else
				$message = __( 'Get Jetpack Now!' );

			$install_link = $this->is_plugin_available( 'jetpack', $message, 'button-primary', 'wpcom-connect' );

			if ( empty( $install_link ) ) {
				$install_link = sprintf( '<a id="wpcom-connect" class="button-primary" href="%1$s">%2$s</a>', 'http://downloads.wordpress.org/plugin/jetpack.latest-stable.zip', __( 'Get Jetpack Now!' ) );
			}
			?>

			<div id="message" class="updated jetpack-message jp-connect">
				<div class="jetpack-close-button-container">
					<a class="jetpack-close-button" href="?dismiss-jetpack-notice=yes" title="<?php _e( 'Dismiss this notice.' ); ?>"><?php _e( 'Dismiss this notice.' ); ?></a>
				</div>
				<div class="jetpack-wrap-container">
					<div class="jetpack-text-container">
						<h4>
							<?php printf( __( 'Future upgrades to %1$s will only be available in <a href="%2$s" target="_blank">Jetpack</a>. Jetpack connects your blog to the WordPress.com cloud, <a href="%3$s" target="_blank">enabling awesome features</a>.' ), PLUGIN_NAME, 'http://jetpack.me/', 'http://jetpack.me/faq/' ); ?>
						</h4>
					</div>
					<div class="jetpack-install-container">
						<p class="submit"><?php echo $install_link ?></p>
					</div>
				</div>
			</div>

			<?php
		}

		function dismiss_upgrade_notice() {	
			if ( isset( $_GET['dismiss-jetpack-notice'] ) && 'yes' == $_GET['dismiss-jetpack-notice'] ) {
				// set option
				update_option( 'adn_dismiss_notice_' . PLUGIN_SLUG, TRUE );
				wp_safe_redirect( remove_query_arg( 'dismiss-jetpack-notice' ) );
				exit;
			}
		}

		function is_plugin_available( $plugin_slug = '', $plugin_name = '', $link_class = '', $link_id = '' ) {
			if ( empty( $plugin_slug ) )
				return;

			if ( empty( $plugin_name ) )
				$plugin_name = __( 'Activate Plugin' );

			$action = '';

			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ) {
				$plugins = get_plugins( '/' . $plugin_slug );

				if ( ! empty( $plugins ) ) {
					$keys = array_keys( $plugins );
					$plugin_file = $plugin_slug . '/' . $keys[0];
					$action = '<a 	id="' . esc_attr( $link_id ) . '"
									class="' . esc_attr( $link_class ) . '"
									href="' . esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file . '&from=plugins' ), 'activate-plugin_' . $plugin_file ) ) .
									'"title="' . esc_attr__( 'Activate Plugin' ) . '"">' . esc_attr( $plugin_name ) . '</a>';
				}
			}

			if ( empty( $action ) && function_exists( 'is_main_site' ) && is_main_site() ) {
					$action = '<a 	id="' . esc_attr( $link_id ) . '"
									class="thickbox ' . esc_attr( $link_class ) . '"
									href="' . esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin_slug .
									'&from=plugins&TB_iframe=true&width=600&height=550' ) ) . '" title="' .
									esc_attr__( 'Install Plugin' ) . '">' . esc_attr( $plugin_name ) . '</a>';
			}

			return $action;
		}

		function echo_on_plugin_page() {
			if ( strpos( $_SERVER['REQUEST_URI'], 'plugins.php' ) ) {
				// if we are on the plugins page, display the notice
				$this->upgrade_notice();
			} else if ( strpos( $_SERVER['REQUEST_URI'], PLUGIN_SLUG ) ) {
				$this->upgrade_notice();
			}
		}
	} // end class
} // end class_exists