<?php
/**
 * A class definition responsible for processing and mapping product according to feed rules and make the feed
 *
 * @link       https://webappick.com/
 * @since      1.0.0
 *
 * @package    Woo_Feed
 * @subpackage Woo_Feed/includes
 * @author     Ohidul Islam <wahid@webappick.com>
 */
final class Woo_Feed_Message {

	/**
	 * @var Woo_Feed_Message
	 */
	protected static $instance;

	/**
	 * Holds Messages & Notices
	 *
	 * @var array
	 */
	private $messages = array();

	/**
	 * Is Output Sent Flag
	 *
	 * @var bool
	 */
	private $is_displayed = false;

	/**
	 * Get Woo_Feed_Message Singleton Instance
	 *
	 * @return Woo_Feed_Message
	 */
	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Woo_Feed_Message constructor.
	 * Initialize default messages and notices
	 *
	 * @return void
	 */
	private function __construct() {
		$this->set_saved_messages();
		$this->set_feed_edit_update_messages();
		$this->set_schedule_update_messages();
		$this->set_settings_messages();
	}

	/**
	 * Display Admin Messages & Notices
	 *
	 * @return void
	 */
	public function displayMessages() {
		$this->is_displayed = true;
		$this->display_support_links();
		$this->display_admin_messages();
	}

	/**
	 * Set Message/Notice to be displayed in admin area within WooFeed Pages.
	 *
	 * @param array $message message data.
	 * @return void
	 */
	public function setMessage( $message = array() ) {
		if ( true === $this->is_displayed ) {
			/* translators: 1: Method Name 2: Method Name */
			woo_feed_doing_it_wrong( __METHOD__, sprintf( esc_html__( '%1$s Should be called before %2$s', 'woo-feed' ), __METHOD__, 'Woo_Feed_Message::displayMessages()' ), '3.2.12' );
		}
		if ( is_array( $message ) && isset( $message['notice'], $message['type'], $message['dismissible'] ) ) {
			$this->messages[] = array(
				'notice'      => $message['notice'],
				'type'        => $message['type'],
				'dismissible' => (bool) $message['dismissible'],
			);
		}
	}

	/**
	 * Display The tob bar (support and documentation links)
	 *
	 * @return void
	 */
	private function display_support_links() {
		?>
        <br>
		<table class="wf-info-table widefat fixed">
			<tbody>
			<tr>
                <th>
                    <strong>
                        <a class="get-woo-feed-pro" href="http://bit.ly/2KIwvTt" target="_blank" aria-label="<?php esc_attr_e( 'Get Woo Feed Pro', 'woo-feed' ); ?>">
                            <img src="<?php echo esc_url( WOO_FEED_PLUGIN_URL ); ?>admin/images/get-woo-feed-pro.svg" alt="<?php esc_attr_e( 'Get Woo Feed Pro', 'woo-feed' ); ?>">
                        </a>
                    </strong>
                </th>
				<th>
					<strong>
						<a style="color:#0073aa;" href="https://webappick.helpscoutdocs.com/collection/1-woocommerce-product-feed" target="_blank" ><?php _e( 'Documentation', 'woo-feed' ); ?></a>
					</strong>
				</th>
				<th>
					<strong>
						<a style="color:#ee264a;" href="http://bit.ly/2u6giNz" target="_blank"><?php _e( 'Video Tutorials', 'woo-feed' ); ?></a>
					</strong>
				</th>
				<th>
					<strong>
						<a style="color:#0DD41E;" href="https://webappick.com/support/" target="_blank"><?php _e( 'Get Support', 'woo-feed' ); ?></a>
					</strong>
				</th>
			</tr>
			</tbody>
		</table><br>
		<?php
	}

	/**
	 * Prints Admin Messages & Notices
	 *
	 * @return void
	 */
	private function display_admin_messages() {
		if ( ! empty( $this->messages ) ) {
			foreach ( $this->messages as $notice ) {
				if ( ! isset( $notice['notice'] ) || ! isset( $notice['type'] ) ) {
					continue;
				}
				$isDismissible = isset( $notice['dismissible'] ) && true === $notice['dismissible'] ? ' is-dismissible' : '';
				printf(
					'<div class="notice notice-%1$s %1$s%3$s"><p>%2$s</p></div>',
					esc_attr( $notice['type'] ),
					wp_kses_post( $notice['notice'] ),
					esc_attr( $isDismissible )
				);
			}
		}
	}

	/**
	 * Set Messages From DB
	 *
	 * @return void
	 */
	private function set_saved_messages() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['wpf_message'] ) && ! empty( $_GET['wpf_message'] ) ) {
			$message = get_option( 'wpf_message' );
			$type    = sanitize_text_field( $_GET['wpf_message'] );
			delete_option( 'wpf_message' ); // empty message cache.
			if ( ! empty( $message ) ) {
				if ( is_array( $message ) ) {
					foreach ( $message as $m ) {
						$m = isset( $m['message'] ) ? $m['message'] : $m;
						$t = isset( $m['type'] ) ? $m['type'] : $type;
						$this->setMessage(
							array(
								'notice'      => esc_html( $m ),
								'type'        => $t,
								'dismissible' => true,
							)
						);
					}
				} else {
					$this->setMessage(
						array(
							'notice'      => esc_html( $message ),
							'type'        => $type,
							'dismissible' => true,
						)
					);
				}
			}
			$dir = get_option( 'WPF_DIRECTORY_PERMISSION_CHECK', false );
			if ( $dir ) {
				$this->setMessage(
					array(
						'notice'      => esc_html( $dir ),
						'type'        => 'error',
						'dismissible' => true,
					)
				);
				delete_option( 'WPF_DIRECTORY_PERMISSION_CHECK' ); // empty message cache.
			}
		}
		// phpcs:enable
	}

	/**
	 * Set Feed Edit/Update Messages
	 *
	 * @return void
	 */
	private function set_feed_edit_update_messages() {
		global $plugin_page;
    	// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( 'webappick-manage-feeds' == $plugin_page ) {
			if ( ( isset( $_GET['feed_created'] ) || isset( $_GET['feed_updated'] ) || isset( $_GET['feed_imported'] ) ) && isset( $_GET['feed_name'] ) ) {
				if ( isset( $_GET['feed_created'] ) ) {
					$this->setMessage(
						array(
							'notice'      => esc_html__( 'Feed Config Created Successfully.', 'woo-feed' ),
							'type'        => 'success',
							'dismissible' => true,
						)
					);
				}
				if ( isset( $_GET['feed_updated'] ) ) {
					$this->setMessage(
						array(
							'notice'      => esc_html__( 'Feed Config Updated Successfully.', 'woo-feed' ),
							'type'        => 'updated',
							'dismissible' => true,
						)
					);
				}
				if ( isset( $_GET['feed_imported'] ) ) {
					$this->setMessage(
						array(
							'notice'      => esc_html__( 'Feed Config Successfully Imported.', 'woo-feed' ),
							'type'        => 'updated',
							'dismissible' => true,
						)
					);
				}
			}
			// get updated link.
			if ( isset( $_GET['link'] ) && ! empty( $_GET['link'] ) ) {
				$link = filter_input( INPUT_GET, 'link', FILTER_VALIDATE_URL );
				/**
				 * @TODO use session/cookies/localstorage or transient api for this message
				 * @see settings_errors()
				 */
				if ( isset( $link ) && ! empty( $link ) ) {
					/** @noinspection HtmlUnknownTarget */
					$link = sprintf( '<a href="%1$s" target="_blank">%1$s</a>', esc_url( $link ) );
					/* translators: Feed URL */
					$notice = sprintf( esc_html__( 'Feed Generated Successfully. Feed URL: %s', 'woo-feed' ), $link );
					$notice = '<b style="color: #008779;">' . $notice . '</b>';
					if ( isset( $_GET['cat'] ) && 'no' == $_GET['cat'] ) {
						$notice .= sprintf( '<br/><br/><b style="color: #f49242;">%s</b>', esc_html__( 'Warning:', 'woo-feed' ) );
						$link    = 'https://webappick.helpscoutdocs.com/article/19-how-to-map-store-category-with-merchant-category';
						/** @noinspection HtmlUnknownTarget */
						$link    = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $link ), esc_html__( 'Learn more...', 'woo-feed' ) );
						$notice .= sprintf( '<ul><li>%s %s</li></ul>', esc_html__( 'Google Product category is not selected. Your Google Ads CPC rate will be high. Add proper Google Product Category to each product & reduce CPC rate.', 'woo-feed' ), $link );
					}
					$this->setMessage(
						array(
							'notice'      => $notice,
							'type'        => 'updated',
							'dismissible' => true,
						)
					);
				}
			}
		}
	    // phpcs:enable
	}

	/**
	 * Set Schedule Update Response Message
	 *
	 * @return void
	 */
	private function set_schedule_update_messages() {
		global $plugin_page;
	    // phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( 'webappick-manage-feeds' == $plugin_page ) {
			if ( isset( $_GET['schedule_updated'] ) && ! empty( $_GET['schedule_updated'] ) ) {
				if ( 1 == $_GET['schedule_updated'] ) {
					$this->setMessage(
						array(
							'notice'      => esc_html__( 'Feed auto update interval updated.', 'woo-feed' ),
							'type'        => 'updated',
							'dismissible' => true,
						)
					);
				}
				if ( 2 == $_GET['schedule_updated'] ) {
					$this->setMessage(
						array(
							'notice'      => esc_html__( 'Unable to save auto update interval.', 'woo-feed' ),
							'type'        => 'warning',
							'dismissible' => true,
						)
					);
				}
				if ( 3 == $_GET['schedule_updated'] ) {
					$this->setMessage(
						array(
							'notice'      => esc_html__( 'Invalid interval value.', 'woo-feed' ),
							'type'        => 'error',
							'dismissible' => true,
						)
					);
				}
				if ( 4 == $_GET['schedule_updated'] ) {
					$this->setMessage(
						array(
							'notice'      => esc_html__( 'Invalid request.', 'woo-feed' ),
							'type'        => 'error',
							'dismissible' => true,
						)
					);
				}
			}
		}
	    // phpcs:enable
	}

	/**
	 * Set Settings Notices
	 *
	 * @return void
	 */
	private function set_settings_messages() {
		global $plugin_page;
	    // phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( 'webappick-feed-settings' == $plugin_page ) {
			if ( isset( $_GET['settings_updated'] ) && '1' == $_GET['settings_updated'] ) {
				$this->setMessage(
					array(
						'notice'      => esc_html__( 'Settings Updated.', 'woo-feed' ),
						'type'        => 'updated',
						'dismissible' => true,
					)
				);
			}
		}
	    // phpcs:enable
	}

	/**
	 * Define private clone method to disallow cloning
	 */
	private function __clone() {
		// cloning isn't allowed.
	}
}

/**
 * Get Woo_Feed_message Instance
 *
 * @return Woo_Feed_Message
 */
function WPFFWMessage() {
	return Woo_Feed_Message::getInstance();
}
