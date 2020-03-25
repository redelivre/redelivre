<?php
/**
 * WebAppick License Checker
 *
 * This class will check, active and deactivate license
 * @version 1.0.0
 * @package WebAppick
 * @subpackage AppServices
 */

namespace WebAppick\AppServices;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class License
 */
class License {
	
	/**
	 * WebAppick\AppServices\Client
	 *
	 * @var Client
	 */
	protected $client;
	
	/**
	 * Flag for checking if the init method is already called.
	 * @var bool
	 */
	private $didInit = false;
	
	/**
	 * Arguments of create menu
	 *
	 * @var array
	 */
	protected $menu_args;
	
	/**
	 * `option_name` of `wp_options` table
	 *
	 * @var string
	 */
	protected $option_key;
	
	/**
	 * Error message of HTTP request
	 *
	 * @var string
	 */
	protected $error;
	
	/**
	 * Success message on form submit
	 *
	 * @var string
	 */
	protected $success;
	
	/**
	 * Corn schedule hook name
	 *
	 * @var string
	 */
	protected $schedule_hook;
	
	/**
	 * Set value for valid license
	 *
	 * @var boolean
	 */
	private $is_valid_license = null;
	/**
	 * The license data
	 * @var array {
	 *     Optional. License Data.
	 *     @type string     $key                The License Key
	 *     @type string     $status             Activation Status
	 *     @type int        $remaining          Remaining Activation
	 *     @type int        $activation_limit   Number of activation allowed for the license key
	 *     @type int        $expiry_day         Number of day remaining before the license expires
	 * }
	 */
	protected $license;
	
	/**
	 * Current User Permission for managing License
	 * @var bool
	 */
	protected $currentUserCanManage = false;
	
	/**
	 * Is Current Page is the license manage page
	 * @var bool
	 */
	protected $isLicensePage = false;
	
	/**
	 * Initialize the class
	 *
	 * @param Client $client The Client.
	 */
	public function __construct( Client $client ) {
		$this->client = $client;
		$this->option_key = 'WebAppick_' . md5( $this->client->getSlug() ) . '_manage_license';
		$this->data_key = $this->client->getSlug() . '-license';
		$this->schedule_hook = $this->client->getSlug() . '_license_check_event';
		// load the license.
		$this->getLicense();
		add_action( 'init', [ $this, 'handle_license_page_form' ], 10 );
	}
	
	/**
	 * Initialize License
	 *
	 * @return void
	 */
	public function init() {
		// check the validity and save the state.
		$this->is_valid();
		// Run hook to check license status daily.
		add_action( $this->schedule_hook, array( $this, 'check_license_status' ) );
		$this->currentUserCanManage = $this->menu_args['capability'];
		$this->isLicensePage = isset( $_GET['page'] ) && $_GET['page'] === $this->menu_args['menu_slug']; // phpcs:ignore
		add_action( 'plugin_action_links_' . $this->client->getBasename(), [ $this, 'plugin_action_links' ] );
		add_action( 'admin_notices', array( $this, '__admin_notices' ), 10 );
		// Activation/Deactivation hooks.
		$this->activation_deactivation();
		$this->didInit = true;
	}
	
	/**
	 * Expose the License Key
	 * @return void|string
	 */
	public function get_key() {
		$this->getLicense();
		return $this->license['key'];
	}
	
	/**
	 * Display Admin Notices
	 * @return void
	 */
	public function __admin_notices() {
		if ( ! current_user_can( $this->currentUserCanManage ) ) return;
		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL === true ) {
			$host = wp_parse_url( $this->__getLicenceAPI(), PHP_URL_HOST );
			if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || ( defined( 'WP_ACCESSIBLE_HOSTS' ) && false === stristr( WP_ACCESSIBLE_HOSTS, $host ) ) ) {
		?>
			<div class="notice notice-error">
				<p><?php
					printf(
						/* translators: 1: Warning in bold tag, 2: This plugin name, 3: API Host Name, 4: WP_ACCESSIBLE_HOSTS constant */
						esc_html__( '%1$s You\'re blocking external requests which means you won\'t be able to get %2$s updates. Please add %3$s to %4$s.', 'webappick' ),
						'<b>'. esc_html__( 'Warning!', 'webappick' ).'</b>',
						esc_html( $this->client->getName() ),
						'<strong>' . esc_html( $host ) . '</strong>',
						'<code>WP_ACCESSIBLE_HOSTS</code>'
					);
				?></p>
			</div>
		<?php
			}
		}
		if ( ! $this->isLicensePage && ! $this->is_valid() ) {
		?>
		<div class="notice notice-error">
			<p><?php
				printf(
					/* translators: 1: This plugin name, 2: Plugin/Theme, 3: Activation Page URL, 4: This Plugin Name */
					esc_html__( 'The %1$s API Key has not been activated, so the %2$s is inactive! %3$s to activate %4$s.', 'webappick' ),
					'<strong>' . esc_attr( $this->client->getName() ) . '</strong>',
					esc_attr( $this->client->getType() ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=' . $this->menu_args['menu_slug'] ) ) . '">'. esc_html__( 'Click here', 'woo-feed' ) .'</a>',
					'<strong>' . esc_attr( $this->client->getName() ) . '</strong>'
				);
			?></p>
		</div>
		<?php
		}
		if ( ! empty( $this->error ) ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $this->error; // phpcs:ignore xss ok ?></p>
			</div>
			<?php
		}
		if ( ! empty( $this->success ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo $this->success; // phpcs:ignore xss ok ?></p>
			</div>
			<?php
		}
	}
	
	/**
	 * Setup plugin action link to the license page
	 * @param array $links plugin action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		if ( ! empty( $this->menu_args['menu_slug'] ) && ! empty( $this->menu_args['menu_title'] ) ) {
			/** @noinspection HtmlUnknownTarget */
			$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . $this->menu_args['menu_slug'] ) ), esc_html( $this->menu_args['menu_title'] ) );
		}
		return $links;
	}
	
	/**
	 * Check license
	 * @return array
	 */
	public function check() {
		return $this->send_request( 'status', $this->license );
	}
	
	/**
	 * Check Plugin Update
	 * @return array
	 */
	public function check_update() {
		return $this->send_request( 'update', $this->license );
	}
	
	/**
	 * Get Plugin data
	 * @return array {
     *     Plugin Information
     *     @type bool $success                      API response status
     *     @type string $api_call_execution_time    API Man (Rest Response) Execution Time
     *     @type array $data {
     *         Plugin Data ( API Man.)
     *         @type array $package {
	 *             @type int $product_id           API Man Product ID
     *         }
     *         @type array $info {
     *             @type string $id                     Plugin Id
	 *             @type string $name                   Plugin Name
	 *             @type string $author                 Author Name
	 *             @type string $author_profile         Author Profile URL
	 *             @type string $slug                   Plugin Slug
	 *             @type string $plugin                 Plugin main file path
	 *             @type string $new_version            New Version String
	 *             @type string $url                    Plugin URL
	 *             @type string $package                Plugin update download URL
	 *             @type string $icons                  Plugin Icons
	 *             @type string $banners                Plugin Banners
	 *             @type string $banner_rtl             RTL Version of Plugin Banners
	 *             @type string $upgrade_notice         Upgrade Notice
	 *             @type string $requires               Minimum WordPress Version
	 *             @type string $requires_php           Minimum PHP Version
	 *             @type string $tested                 Tested upto WordPress Version
	 *             @type array $compatibility           Compatibility information (API Man sends string)
	 *             @type array $contributors            Plugin Contributors List (if available)
	 *             @type array $ratings                 Plugin Rating (if available)
	 *             @type float $num_ratings             Plugin Rating (if available)
	 *             @type string $last_updated           Last updated Date
	 *             @type string $homepage               Plugin Home Page URL
     *             @type array $sections {
     *                 Plugin Description Sections
     *                 @type string $description        Plugin Description
	 *                 @type string $changelog          Change LOG
     *             }
	 *             @type mixed $author_block_count
	 *             @type mixed $author_block_rating
     *         }
     *     }
     * }
	 */
	public function get_information() {
		return $this->send_request( 'information', $this->license );
	}
	
	/**
	 * Active a license
	 * @param array $license license data.
	 * @return array
	 */
	public function activate( $license ) {
		return $this->send_request( 'activate', $license );
	}
	
	/**
	 * Deactivate current license
	 * @return array
	 */
	public function deactivate() {
		return $this->send_request( 'deactivate', $this->license );
	}
	
	/**
	 * Send common request
	 *
	 * @param string $action    request action.
	 * @param array $license    license data.
	 *
	 * @return array
	 */
	protected function send_request( $action, $license = [] ) {
		// WC-AM Valid Actions and response data types.
		$actions = [
			'activate'          => 'json',
			'deactivate'        => 'json',
			'status'            => 'json',
			'information'       => 'json',
			'update'            => 'json',
			'plugininformation' => 'serialize', // serialize option doesn't provide success status.
			'pluginupdatecheck' => 'serialize',
		];
		if ( ! in_array( $action, array_keys( $actions ) ) ) {
			return [
				'success' => false,
				'error'   => esc_html__( 'Invalid Request Action.', 'webappick' ),
			];
		}
		// parse license data
		$license = wp_parse_args( $license, $this->getLicense() );
		if ( empty( $license['key'] ) || empty( $license['instance'] ) ) {
			return [
				'success' => false,
				'error'   => esc_html__( 'Invalid/Empty License Data.', 'webappick' ),
			];
		}
		if ( empty( $this->client->getProjectId() ) && empty( $this->client->getName() ) ) {
			return [
				'success' => false,
				'error'   => esc_html__( 'A valid project name/id is required.', 'webappick' ),
			];
		}
		$params = [
			'object'       => str_ireplace( array( 'http://', 'https://' ), '', home_url() ),
			'api_key'      => $license['key'],
			'version'      => $this->client->getProjectVersion(),
			'instance'     => $license['instance'],
			'product_id'   => $this->client->getName(),
			'plugin_name'  => $this->client->getBasename(),
			'wc_am_action' => $action,
		];
		$this->setAPI_URL();
		$response = $this->client->send_request( $params, '', true );
		$this->restoreAPI_URL();
		if ( ! is_wp_error( $response ) ) {
			$response = wp_remote_retrieve_body( $response );
			if ( 'json' == $actions[ $action ] ) {
				$response = json_decode( $response, true );
			} else {
				$response = maybe_unserialize( $response );
				// @TODO check wc-am error ..
				return $response;
			}
			if ( empty( $response ) || ! isset( $response['success'] ) ) {
				return [
					'success' => false,
					'error'   => esc_html__( 'Unknown error occurred, Please try again.', 'webappick' ),
				];
			}
			if ( ! $response['success'] ) {
				$response = [
					'success' => false,
					'error'   => isset( $response['error'] ) ? sanitize_text_field( $response['error'] ) : esc_html__( 'Unknown error occurred in API server.', 'webappick' ),
					'code'    => isset( $response['code'] ) ? sanitize_text_field( $response['code'] ) : 'UNKNOWN',
				];
			}
			return $response;
		} else {
			return [
				'success' => false,
				'error'   => $response->get_error_message(),
			];
		}
	}
	
	/**
	 * License API URL
	 * @return string
	 */
	public function __getLicenceAPI() {
		return 'https://webappick.com/?wc-api=wc-am-api';
	}
	
	/**
	 * Filter api url for licensing api
	 * @return void
	 */
	private function setAPI_URL() {
		add_filter( $this->client->getSlug() . '_WebAppick_API_URL', [ $this, '__getLicenceAPI' ], 10 );
	}
	
	/**
	 * Remove filter for changing wpi url
	 * @see License::setAPI_URL()
	 * @return void
	 */
	private function restoreAPI_URL() {
		remove_filter( $this->client->getSlug() . '_WebAppick_API_URL', [ $this, '__getLicenceAPI' ], 10 );
	}
	
	/**
	 * Add settings page for license
	 *
	 * @param array $args settings for rendering the menu.
	 *
	 * @return void
	 */
	public function add_settings_page( $args = array() ) {
		if ( $this->didInit ) {
			_doing_it_wrong( __METHOD__, sprintf( '<code>%s</code> Should be called before License::init()', __METHOD__ ), '1.0.1' );
			return;
		}
		$defaults = [
			'type'        => 'menu', // Can be: menu, options, submenu.
			'page_title'  => esc_html__( 'Manage License', 'webappick' ),
			'menu_title'  => esc_html__( 'Manage License', 'webappick' ),
			'capability'  => 'manage_options',
			'menu_slug'   => $this->client->getSlug() . '-manage-license',
			'icon_url'    => '',
			'position'    => null,
			'parent_slug' => '',
		];
		$this->menu_args = wp_parse_args( $args, $defaults );
		if ( ! in_array( $this->menu_args['type'], [ 'menu', 'options', 'submenu' ] ) ) {
			if ( empty( $this->menu_args['parent_slug'] ) ) {
				$this->menu_args['type'] = 'menu';
			}
		}
		if ( 'submenu' == $this->menu_args['type'] && empty( $this->menu_args['parent_slug'] ) ) {
			$this->menu_args['type'] = 'options';
		}
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 999 );
	}
	
	/**
	 * Admin Menu hook
	 *
	 * @return void
	 */
	public function admin_menu() {
		switch ( $this->menu_args['type'] ) {
			case 'submenu':
				$this->add_submenu_page();
				break;
			case 'options':
				$this->add_options_page();
				break;
			case 'menu':
			default:
				$this->add_menu_page();
				break;
		}
	}
	
	/**
	 * License menu output
	 */
	public function menu_output() {
		$this->licenses_style();
		$action = ( isset( $this->license['status'] ) && 'active' == $this->license['status'] ) ? 'deactivate' : 'activate';
		?>
		<div class="wrap webappick-license-settings-wrapper">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'License Settings', 'webappick' ); ?></h1>
			<hr class="wp-header-end">
			<?php do_action( 'before_webappick_' . $this->client->getSlug() . '_license_section' ); ?>
			<div class="webappick-license-settings webappick-license-section">
				<?php $this->show_license_page_card_header(); ?>
				<div class="webappick-license-details">
					<?php if ( 'activate' == $action ) { ?>
					<p><?php
						/* translators: %s: This Plugin Name */
						printf( esc_html__( 'Active %s by your license key to get professional support and automatic update from your WordPress dashboard.', 'webappick' ), '<strong>' . esc_html( $this->client->getName() ) . '</strong>' );
					?></p>
					<?php } ?>
					<form method="post" action="<?php $this->formActionUrl(); ?>" novalidate="novalidate" spellcheck="false" autocomplete="off">
						<?php wp_nonce_field( $this->data_key ); ?>
						<input type="hidden" name="<?php echo esc_attr( $this->data_key ); ?>[_action]" value="<?php echo esc_attr( $action ); ?>">
						<div class="license-input-fields">
							<div class="license-input-key">
								<svg enable-background="new 0 0 512 512" version="1.1" viewBox="0 0 512 512" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m463.75 48.251c-64.336-64.336-169.01-64.335-233.35 1e-3 -43.945 43.945-59.209 108.71-40.181 167.46l-185.82 185.82c-2.813 2.813-4.395 6.621-4.395 10.606v84.858c0 8.291 6.709 15 15 15h84.858c3.984 0 7.793-1.582 10.605-4.395l21.211-21.226c3.237-3.237 4.819-7.778 4.292-12.334l-2.637-22.793 31.582-2.974c7.178-0.674 12.847-6.343 13.521-13.521l2.974-31.582 22.793 2.651c4.233 0.571 8.496-0.85 11.704-3.691 3.193-2.856 5.024-6.929 5.024-11.206v-27.929h27.422c3.984 0 7.793-1.582 10.605-4.395l38.467-37.958c58.74 19.043 122.38 4.929 166.33-39.046 64.336-64.335 64.336-169.01 0-233.35zm-42.435 106.07c-17.549 17.549-46.084 17.549-63.633 0s-17.549-46.084 0-63.633 46.084-17.549 63.633 0 17.548 46.084 0 63.633z"/>
                                </svg>
								<label for="license_key" class="screen-reader-text"><?php esc_html_e( 'License Key', 'webappick' ); ?></label>
								<input class="regular-text" id="license_key" type="text"
								value="<?php echo esc_attr( $this->get_input_license_value( $action, $this->license ) ); ?>"
								placeholder="<?php esc_attr_e( 'Enter your license key to activate', 'webappick' ); ?>"
								name="<?php echo esc_attr( $this->data_key ); ?>[license_key]"<?php readonly( ( 'deactivate' == $action ), true, true ); ?>
								autocomplete="off">
							</div>
							<button type="submit" name="<?php echo esc_attr( $this->data_key); ?>[submit]" class="<?php printf( '%s-button', esc_attr( $action ) );?>"><?php
								'activate' == $action ? esc_html_e( 'Activate License', 'webappick' ) : esc_html_e( 'Deactivate License', 'webappick' );
							?></button>
							<a href="http://webappick.com/my-account/api-keys/" class="button button-primary button-hero" style="margin-left: 20px;font-size: 17px;line-height: 2.5;" target="_blank"><?php esc_html_e( 'Manage License', 'webappick' ); ?></a>
						</div>
					</form>
					<?php $this->show_active_license_info(); ?>
				</div>
			</div> <!-- /.webappick-license-settings -->
			<?php do_action( 'after_webappick_' . $this->client->getSlug() . '_license_section' ); ?>
		</div>
		<?php
	}
	
	/**
	 * License form submit
	 * @return void
	 */
	public function handle_license_page_form() {
		if ( isset( $_POST[ $this->data_key ], $_POST[ $this->data_key ]['_action'] ) ) {
			check_admin_referer( $this->data_key );
			switch ( $_POST[ $this->data_key ]['_action'] ) {
				case 'activate':
					$this->activate_client_license( array_map( 'sanitize_text_field', $_POST[ $this->data_key ] ) );
					break;
				case 'deactivate':
					$this->deactivate_client_license();
					break;
				default:
					break;
			}
		}
	}
	
	/**
	 * Check license status on schedule.
	 * Check and update license status on db
	 * @return void
	 */
	public function check_license_status() {
		// get current license data.
		$license = $this->getLicense();
		if ( $license ) {
			// check license.
			$response = $this->check();
			if ( isset( $response['success'], $response['status_check'] ) && $response['success'] ) {
				// update license status.
				$license = wp_parse_args(
					[
						'status'      => 'active' == $response['status_check'] ? 'active' : 'inactive',
						'remaining'   => isset( $response['data'], $response['data']['activations_remaining'] ) ? $response['data']['activations_remaining'] : 0,
						'activations' => isset( $response['data'], $response['data']['total_activations'] ) ? $response['data']['total_activations'] : 0,
						'limit'       => isset( $response['data'], $response['data']['total_activations_purchased'] ) ? $response['data']['total_activations_purchased'] : 0,
						'unlimited'   => isset( $response['data'], $response['data']['unlimited_activations'] ) ? $response['data']['unlimited_activations'] : false,
						'expiry_date' => 0, // wc-am doesn't sent remaining date.
					],
					$license
				);
			} else {
				// Don't reset the key.
				// keep it, if the user renew subscription update the status and reactivate the plugin.
				$license = wp_parse_args(
					[
						'status'      => 'inactive',
						'remaining'   => 0,
						'activations' => 0,
						'limit'       => 0,
						'unlimited'   => false,
						'expiry_date' => 0, // wc-am doesn't sent remaining date.
					],
					$license
				);
			}
			// update the license state & and save in db.
			$this->setLicense( $license );
		}
	}
	
	/**
	 * Check this is a valid license.
	 * @return bool
	 */
	public function is_valid() {
		if ( null !== $this->is_valid_license ) {
			return $this->is_valid_license;
		}
		// load the license if already not loaded.
		$this->getLicense();
		if ( isset( $this->license['status'] ) && 'active' == $this->license['status'] ) {
			$this->is_valid_license = true;
		} else {
			$this->is_valid_license = false;
		}
		
		return $this->is_valid_license;
	}
	
	/**
	 * Read WooCommerce API Manager Data, Convert to new license format and save in db.
	 * @param bool $override override current settings.
	 * @return bool
	 */
	public function migrate_license_from_wc_am( $override = false ) {
		// phpcs:disable
		/*// WC AM data structure.
		[
			'_data'                => [ 'api_key', 'activation_email', ], // api key & email
			'_product_id'          => '', // product title or name
			'_instance'            => '', // instance key unique id
			'_activated'           => '', // activation status => Activated|Deactivated
			'_deactivate_checkbox' => '', // deactivation check box state > On|Off
		];*/
		// phpcs:enable
		// check if already migrated || override.
		if ( 1 == get_option( $this->option_key . '_wc_am_migrated', false ) && ! $override ) {
			return false;
		}
		// is already migrated.
		// api manager data prefix.
		$wcAmPrefix = str_ireplace( array( ' ', '_', '&', '?' ), '_', strtolower( $this->client->getName() ) );
		$license = [
			'key'      => '',
			'status'   => 'deactivate', // activate.
			'instance' => '', // max len 190.
		];
		// get key.
		$data = get_option( $wcAmPrefix . '_data', false );
		if ( $data && isset( $data['api_key'] ) ) {
			$license['key'] = $data['api_key'];
		}
		// instance id.
		$data = get_option( $wcAmPrefix . '_instance', false );
		if ( $data ) {
			$license['instance'] = $data;
		}
		// activation status.
		$data = get_option( $wcAmPrefix . '_activated', false );
		if ( $data ) {
			$license['status'] = strtolower( $data ) === 'activated' ? 'active' : 'inactive'; // Deactivated.
		}
		$this->setLicense( $license );
		$this->check_license_status();
		update_option( $this->option_key . '_wc_am_migrated', 1, false );
		return true;
	}
	
	/**
	 * Styles for licenses page
	 */
	private function licenses_style() {
		?>
		<!--suppress CssUnusedSymbol -->
		<style>
			.webappick-license-settings *{-webkit-box-sizing:border-box;box-sizing:border-box}
			.webappick-license-settings{margin-top:20px;background-color:#fff;-webkit-box-shadow:0 3px 10px rgba(16,16,16,.05);box-shadow:0 3px 10px rgba(16,16,16,.05)}
			.webappick-license-section{width:100%;min-height:1px;-webkit-box-sizing:border-box;box-sizing:border-box}
			.webappick-license-title{background-color:#f8fafb;border-bottom:2px solid #eaeaea;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;padding:10px 20px}
			.webappick-license-title svg{width:30px;height:30px;fill:#0082bf}
			.webappick-license-title span{font-size:17px;color:#444;margin-left:10px}
			.webappick-license-details{padding:20px}
			.webappick-license-details p{font-size:15px;margin:0 0 20px 0}
			.license-input-key{position:relative;-webkit-box-flex:0;-ms-flex:0 0 72%;flex:0 0 72%;max-width:72%}
			.license-input-key input{background-color:#f9f9f9;padding:10px 15px 10px 48px;border:1px solid #e8e5e5;border-radius:3px;height:45px;font-size:16px;color:#71777d;width:100%;-webkit-box-shadow:0 0 0 transparent;box-shadow:0 0 0 transparent}
			.license-input-key input:focus{outline:0 none;border:1px solid #e8e5e5;-webkit-box-shadow:0 0 0 transparent;box-shadow:0 0 0 transparent}
			.license-input-key svg{width:22px;height:22px;fill:#0082bf;position:absolute;left:14px;top:13px}
			.license-input-fields{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;margin:20px 0;max-width:850px;width:100%}
			.license-input-fields button{margin-left:20px;color:#fff;font-size:17px;padding:8px;height:46px;background-color:#0082bf;border-radius:3px;cursor:pointer;-webkit-box-flex:0;-ms-flex:0 0 25%;flex:0 0 25%;max-width:25%;border:1px solid #0082bf}
			.license-input-fields button.deactivate-button{background-color:#e40055;border-color:#e40055}
			.license-input-fields button:focus{outline:0 none}
			.active-license-info{display:-webkit-box;display:-ms-flexbox;display:flex}
			.single-license-info{margin-right:40px}
			.single-license-info:last-child{margin-right:0}
			.single-license-info h3{font-size:15px;margin:0 0 12px 0;display:inline-block}
			.single-license-info p{margin:0 0 0 5px;font-size:15px;font-weight:500;display:inline-block}
			.single-license-info p.active{color:#047167}
			.single-license-info p.inactive{color:#e40055}
		</style>
		<?php
	}
	
	/**
	 * Show active license information
	 * @return void
	 */
	private function show_active_license_info() {
		$status      = ( isset( $this->license['status'] ) && 'active' === $this->license['status']  ) ? 'active' : 'inactive';
		$limit       = isset( $this->license['limit'] ) ? $this->license['limit'] : 0;
		$activations = isset( $this->license['activations'] ) ? $this->license['activations'] : 0;
		$remaining   = isset( $this->license['remaining'] ) ? $this->license['remaining'] : 0;
		$unlimited   = isset( $this->license['unlimited'] ) ? $this->license['unlimited'] : false;
		?>
		<div class="active-license-info">
			<div class="single-license-info">
				<h3><?php esc_html_e( 'Status:', 'webappick' ); ?></h3>
				<p class="<?php echo esc_attr( $status ); ?>"><?php 'active' == $status ? esc_html_e( 'Active', 'webappick' ) : esc_html_e( 'Inactive', 'webappick' ); ?></p>
			</div>
			<?php if ( false !== $unlimited ) { ?>
			<div class="single-license-info">
				<h3><?php esc_html_e( 'Activation Limit:', 'webappick' ); ?></h3>
				<p class="active"><?php esc_html_e( 'Unlimited', 'webappick' ); ?></p>
			</div>
			<div class="single-license-info">
				<h3><?php esc_html_e( 'Total Activation:', 'webappick' ); ?></h3>
				<p class="active"><?php echo esc_attr( $activations ); ?></p>
			</div>
			<?php } else { ?>
				<div class="single-license-info">
					<h3><?php esc_html_e( 'Activation Remaining:', 'webappick' ); ?></h3>
					<p class="<?php echo $remaining ? 'active' : 'inactive'; ?>"><?php
						if ( 'active' == $status ) {
							/* translators: 1: Remaining activation, 2: Total activation */
							printf( esc_html__( '%1$d out of %2$d', 'webappick' ), esc_attr( $remaining ), esc_attr( $limit ) );
						} else {
							esc_html_e( 'N/A', 'webappick' );
						}
					?></p>
				</div>
			<?php } ?>
			<div class="single-license-info">
				<h3><?php esc_html_e( 'Automatic Update:', 'webappick' ); ?></h3>
				<p class="<?php echo esc_attr( $status ); ?>"><?php 'active' == $status ? esc_html_e( 'Enabled', 'webappick' ) : esc_html_e( 'Disabled', 'webappick' ); ?></p>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Card header
	 * @return void
	 */
	private function show_license_page_card_header() {
		?>
		<div class="webappick-license-title">
			<svg enable-background="new 0 0 299.995 299.995" version="1.1" viewBox="0 0 300 300" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
                <path d="m150 161.48c-8.613 0-15.598 6.982-15.598 15.598 0 5.776 3.149 10.807 7.817 13.505v17.341h15.562v-17.341c4.668-2.697 7.817-7.729 7.817-13.505 0-8.616-6.984-15.598-15.598-15.598z"/>
				<path d="m150 85.849c-13.111 0-23.775 10.665-23.775 23.775v25.319h47.548v-25.319c-1e-3 -13.108-10.665-23.775-23.773-23.775z"/>
				<path d="m150 1e-3c-82.839 0-150 67.158-150 150 0 82.837 67.156 150 150 150s150-67.161 150-150c0-82.839-67.161-150-150-150zm46.09 227.12h-92.173c-9.734 0-17.626-7.892-17.626-17.629v-56.919c0-8.491 6.007-15.582 14.003-17.25v-25.697c0-27.409 22.3-49.711 49.711-49.711 27.409 0 49.709 22.3 49.709 49.711v25.697c7.993 1.673 14 8.759 14 17.25v56.919h2e-3c0 9.736-7.892 17.629-17.626 17.629z"/>
            </svg>
			<span><?php esc_html_e( 'Activate License', 'webappick' ); ?></span>
		</div>
		<?php
	}
	
	/**
	 * Active client license
	 * @param array $postData Sanitized Form $_POST Data.
	 * @return void
	 */
	private function activate_client_license( $postData ) {
		if ( empty( $postData['license_key'] ) ) {
			$this->error = esc_html__('The license key field is required.', 'webappick' );
			return;
		}
		
		$license = (array) $this->getLicense();
		// check if it's a change request.
		$updateKey = ( isset( $this->license['key'] ) && $postData['license_key'] === $this->license['key'] ) ? true : false;
		if ( $updateKey ) {
			$deactivate = $this->deactivate(); // deactivate first.
			if ( ! $deactivate['success'] ) {
				$check = $this->check(); // check api status.
				if ( $check['success'] && 'inactive' !== $check['status_check'] ) {
					$this->error = $deactivate['error'] ? $deactivate['error'] : esc_html__( 'Unknown error occurred.', 'webappick' );
					return;
				}
			}
		}
		$license['key'] = $postData['license_key'];
		if ( empty( $license['instance'] ) ) $license['instance'] = $this->generateInstanceId();
		$response = $this->activate( $license );
		if ( ! $response['success'] ) {
			$this->error = $response['error'] ? $response['error'] : esc_html__( 'Unknown error occurred.', 'webappick' );
			return;
		}
		// Don't reset the key.
		// keep it, if the user renew subscription update the status and reactivate the plugin.
		$license = array_merge(
			$license,
			[
				'status'      => isset( $response['activated'] ) && $response['activated'] ? 'active' : 'inactive',
				'remaining'   => isset( $response['data'], $response['data']['activations_remaining'] ) ? $response['data']['activations_remaining'] : 0,
				'activations' => isset( $response['data'], $response['data']['total_activations'] ) ? $response['data']['total_activations'] : 0,
				'limit'       => isset( $response['data'], $response['data']['total_activations_purchased'] ) ? $response['data']['total_activations_purchased'] : 0,
				'unlimited'   => isset( $response['data'], $response['data']['unlimited_activations'] ) ? $response['data']['unlimited_activations'] : false,
				'expiry_date' => 0, // wc-am doesn't sent remaining date.
			]
		);
		$this->setLicense( $license );
		if ( ! $updateKey ) {
			$this->success = esc_html__( 'License activated successfully.', 'webappick' );
		} else {
			$this->success = esc_html__( 'License Updated successfully.', 'webappick' );
		}
	}
	
	/**
	 * deactivate client license.
	 * @return void
	 */
	private function deactivate_client_license() {
		if ( ! isset( $this->license['key'] ) || empty( $this->license['key'] ) ) {
			$this->error = esc_html__( 'License key not found.', 'webappick' );
		} else {
			$response = $this->deactivate();
			if ( ! $response['success'] ) {
				// check api status.
				$check = $this->check();
				if ( $check['success'] && 'inactive' !== $check['status_check'] ) {
					$this->error = $response['error'] ? $response['error'] : esc_html__( 'Unknown error occurred.', 'webappick' );
				}
			}
		}
		// keep the instance key for reference.
		$this->setLicense( [ 'instance' => $this->license['instance'] ] );
		$this->success = esc_html__( 'License deactivated successfully.', 'webappick' );
	}
	
	/**
	 * Add license menu page.
	 * @return void
	 */
	private function add_menu_page() {
		add_menu_page(
			esc_html( $this->menu_args['page_title'] ),
			esc_html( $this->menu_args['menu_title'] ),
			$this->menu_args['capability'],
			$this->menu_args['menu_slug'],
			array( $this, 'menu_output' ),
			$this->menu_args['icon_url'],
			$this->menu_args['position']
		);
	}
	
	/**
	 * Add submenu page.
	 * @return void
	 */
	private function add_submenu_page() {
		add_submenu_page(
			$this->menu_args['parent_slug'],
			esc_html( $this->menu_args['page_title'] ),
			esc_html( $this->menu_args['menu_title'] ),
			$this->menu_args['capability'],
			$this->menu_args['menu_slug'],
			array( $this, 'menu_output' )
		);
	}
	
	/**
	 * Add submenu page.
	 * @return void
	 */
	private function add_options_page() {
		add_options_page(
			esc_html( $this->menu_args['page_title'] ),
			esc_html( $this->menu_args['menu_title'] ),
			$this->menu_args['capability'],
			$this->menu_args['menu_slug'],
			array( $this, 'menu_output' )
		);
	}
	
	/**
	 * Schedule daily license checker event
	 */
	public function schedule_cron_event() {
		if ( ! wp_next_scheduled( $this->schedule_hook ) ) {
			wp_schedule_event( time(), 'daily', $this->schedule_hook );
			wp_schedule_single_event( time() + 20, $this->schedule_hook );
		}
	}
	
	/**
	 * Clear any scheduled hook.
	 * @return void
	 */
	public function clear_scheduler() {
		wp_clear_scheduled_hook( $this->schedule_hook );
	}
	
	/**
	 * Register Activation And Deactivation Hooks.
	 * @return void
	 */
	private function activation_deactivation() {
		switch ( $this->client->getType() ) {
			case 'plugin':
				register_activation_hook( $this->client->getBasename(), array( $this, 'schedule_cron_event' ) );
				register_deactivation_hook( $this->client->getBasename(), array( $this, 'project_deactivation' ) );
				add_action( 'activated_plugin', array( $this, 'redirect_to_license_page' ), 999, 2 );
				break;
			case 'theme':
				add_action( 'switch_theme', array( $this, 'project_deactivation' ), 10 );
				add_action( 'after_switch_theme', array( $this, 'schedule_cron_event' ), 10 );
				add_action( 'after_switch_theme', array( $this, 'redirect_to_license_page' ), 999, 2 );
				break;
		}
	}
	
	/**
	 * Project Deactivation Callback.
	 * @return void
	 */
	public function project_deactivation() {
		$this->clear_scheduler();
		$this->getLicense();
		$this->deactivate_client_license();
	}
	
	/**
	 * Redirect to the license activation page after plugin/theme is activated.
	 * @TODO make option for the plugin/theme (which is using this lib) can alter this method with their custom function. 
	 * @param string        $param1         Plugin: base file|Theme: old theme name.
	 * @param bool|WP_Theme $param2  Plugin: network wide activation status|Theme: WP_Theme instance of the old theme.
	 * @return void
	 */
	public function redirect_to_license_page( $param1, $param2 ) {
		$canRedirect = false;
		if ( 'plugin' == $this->client->getType() ) {
			$canRedirect = ( $param1 == $this->client->getBasename() );
		}
		if ( 'theme' == $this->client->getType() ) {
			$canRedirect = ( ! get_option( 'theme_switched_via_customizer' ) );
		}
		if ( $canRedirect ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . $this->menu_args['menu_slug'] ) );
			die();
		}
	}
	
	/**
	 * Form action URL
	 * @return void
	 */
	private function formActionUrl() {
		global $plugin_page;
		if( ! isset( $_SERVER['SCRIPT_NAME'], $plugin_page ) ) return; // phpcs:ignore
		echo esc_url(
			add_query_arg(
				array( 'page' => sanitize_text_field( $plugin_page ) ), // phpcs:ignore
				admin_url( basename( sanitize_text_field( $_SERVER['SCRIPT_NAME'] ) ) )
			)
		);
	}
	
	/**
	 * Get input license key
	 * @param  string $action   current license action.
	 * @param  array  $license  license data.
	 * @return string
	 */
	private function get_input_license_value( $action, $license ) {
		// phpcs:disable
		// if ( 'deactivate' != $action ) return '';
		// $key_length = strlen( $license['key'] );
		// return str_pad( substr( $license['key'], 0, $key_length / 2 ), $key_length, '*' );
		// phpcs:enable
		return isset( $license['key'] ) ? $license['key'] : '';
	}
	
	/**
	 * get Plugin/Theme License
	 * @return array {
	 *     Optional. License Data.
	 *     @type string     $key                The License Key
	 *     @type string     $status             Activation Status
	 *     @type int        $remaining          Remaining Activation
	 *     @type int        $activation_limit   Number of activation allowed for the license key
	 *     @type int        $expiry_day         Number of day remaining before the license expires
	 * }
	 */
	private function getLicense() {
		if ( null !== $this->license ) {
			return $this->license;
		}
		$this->license = get_option( $this->option_key, false );
		// initialize blank inactive license data.
		if ( false === $this->license ) {
			$this->setLicense();
		}
		return $this->license;
	}
	
	/**
	 * Update License Data
	 * call this method without license data will deactivate the license (set empty data)
	 * @param array $license {
	 *     Optional. License Data.
	 *     @type string     $key                The License Key
	 *     @type string     $status             Activation Status
	 *     @type int        $remaining          Remaining Activation
	 *     @type int        $activation_limit   Number of activation allowed for the license key
	 *     @type int        $expiry_day         Number of day remaining before the license expires
	 * }
	 * @return bool     False if value was not updated and true if value was updated.
	 */
	private function setLicense( $license = [] ) {
		$this->license = $this->parse_license_data( $license );
		// update in db.
		return update_option( $this->option_key, $this->license, false );
	}
	
	/**
	 * Parse License data.
	 * @param array $data license data.
	 *
	 * @return array
	 */
	private function parse_license_data( $data = [] ) {
		$defaults = [
			'key'         => '',            // license key.
			'status'      => 'inactive',    // current status.
			'instance'    => '',            // instance unique id.
			'remaining'   => 0,             // remaining activation.
			'activations' => 0,             // total activation.
			'limit'       => 0,             // activation limit.
			'unlimited'   => false,         // is unlimited activation.
			'expiry_date' => 0,             // expires set this to a unix timestamp.
		];
		// parse
		$data    = wp_parse_args( $data, $defaults );
		$license = array();
		// sanitize data.
		$license['key']         = sanitize_text_field( $data['key'] );
		$license['status']      = strtolower( $data['status'] ) === 'active' ? 'active' : 'inactive';
		$license['instance']    = sanitize_text_field( $data['instance'] );
		$license['remaining']   = absint( $data['remaining'] );
		$license['activations'] = absint( $data['activations'] );
		$license['limit']       = absint( $data['limit'] );
		$license['unlimited']   = (bool) $data['unlimited'];
		$license['expiry_date'] = absint( $data['expiry_date'] );
		return $license;
	}
	
	/**
	 * Generate a random Instance ID
	 * @return string
	 */
	private function generateInstanceId() {
		$id = false;
		if ( function_exists( 'wp_generate_password' ) ) {
			$id = wp_generate_password( 12, false );
			if ( 12 !== strlen( $id ) ) {
				$id = false;
			}
		}
		if ( ! $id ) {
			$id = md5( uniqid( wp_rand( 100, 100000 ), true ) );
		}
		return $id;
	}
}
// End of file License.php.