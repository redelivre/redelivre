<?php
/**
 * WebAppick Insights
 *
 * @version 1.0.1
 * @package WebAppick
 * @subpackage AppServices
 *
 * This is a tracker class to track plugin usage based on if the customer has opted in.
 * No personal information is being tracked by this class, only general settings, active plugins, environment details
 * and admin email.
 */

namespace WebAppick\AppServices;

use Exception;
use WP_Theme;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Insights
 */
class Insights {
	
	/**
	 * The notice text
	 *
	 * @var string
	 */
	protected $notice;
	
	/**
	 * Whether to the notice or not
	 *
	 * @var boolean
	 */
	protected $show_notice = true;
	
	/**
	 * If extra data needs to be sent
	 *
	 * @var array
	 */
	protected $extra_data = array();
	
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
	 * Email Message Template For sending Support Ticket
	 * @var string
	 */
	protected $ticketTemplate = '';
	
	/**
	 * Ticket Email Recipient
	 * @var string
	 */
	protected $ticketRecipient = '';
	
	/**
	 * Response to show after support ticket submitted.
	 * @var string
	 */
	protected $supportResponse = '';
	
	/**
	 * Error Response for the support ticket
	 * @var string
	 */
	protected $supportErrorResponse = '';
	
	/**
	 * Support Page URL
	 * @var string
	 */
	protected $supportURL = '';
	
	/**
	 * Initialize the class
	 *
	 * @param Client $client    The client.
	 * @param string $name      readable name of the Plugin/Theme.
	 * @param string $file      main Plugin/Theme file path.
	 */
	public function __construct( $client, $name = null, $file = null ) {
		if ( is_string( $client ) && ! empty( $name ) && ! empty( $file ) ) {
			$client = new Client( $client, $name, $file );
		}
		
		if ( is_object( $client ) && is_a( $client, 'WebAppick\AppServices\Client' ) ) {
			$this->client = $client;
		}
	}
	
	/**
	 * Don't show the notice
	 *
	 * @return Insights
	 */
	public function hide_notice() {
		$this->show_notice = false;
		return $this;
	}
	
	/**
	 * Add extra data if needed
	 *
	 * @param array $data   Extra data.
	 *
	 * @return Insights
	 */
	public function add_extra( $data = array() ) {
		$this->extra_data = $data;
		
		return $this;
	}
	
	/**
	 * Set custom notice text
	 *
	 * @param string $text  Admin Notice Test.
	 *
	 * @return Insights
	 */
	public function notice( $text ) {
		$this->notice = $text;
		
		return $this;
	}
	
	/**
	 * Initialize insights
	 *
	 * @return void
	 */
	public function init() {
		// Env Setup.
		$projectSlug = $this->client->getSlug();
		/**
		 * Support Page URL
		 * @param string $supportURL
		 */
		$supportURL = apply_filters( "WebAppick_{$projectSlug}_Support_Page_URL" , false );
		if ( FALSE !== $supportURL ) {
			$supportURL = esc_url_raw( $supportURL, [ 'http', 'https' ] );
			if ( ! empty( $supportURL ) ) {
				$this->supportURL = $supportURL;
			}
		}
		/**
		 * Set Ticket Recipient Email
		 * @param string $ticketRecipient
		 */
		$ticketRecipient = apply_filters( "WebAppick_{$projectSlug}_Support_Ticket_Recipient_Email" , false );
		if ( FALSE !== $ticketRecipient && is_email( $ticketRecipient ) ) {
			$this->ticketRecipient = sanitize_email( $ticketRecipient );
		}
		/**
		 * Set Support Ticket Template For sending the email query.
		 * @param string $ticketTemplate
		 */
		$ticketTemplate = apply_filters( "WebAppick_{$projectSlug}_Support_Ticket_Email_Template", false );
		if ( FALSE !== $ticketTemplate ) {
			$this->ticketTemplate = $ticketTemplate;
		}
		
		// initialize.
		if ( $this->client->getType() == 'plugin' ) {
			$this->init_plugin();
		} elseif ( $this->client->getType() == 'theme' ) {
			$this->init_theme();
		}
		$this->didInit = true;
	}
	
	/**
	 * Initialize theme hooks
	 *
	 * @return void
	 */
	private function init_theme() {
		$this->init_common();
		
		add_action( 'switch_theme', [ $this, 'deactivation_cleanup' ] );
		add_action( 'switch_theme', [ $this, 'theme_deactivated' ], 12, 3 );
	}
	
	/**
	 * Initialize plugin hooks
	 *
	 * @return void
	 */
	private function init_plugin() {
		// plugin deactivate popup.
		if ( ! $this->__is_local_server() ) {
			add_action( 'plugin_action_links_' . $this->client->getBasename(), [ $this, 'plugin_action_links' ] );
			add_action( 'admin_footer', [ $this, 'deactivate_scripts' ] );
		}
		
		$this->init_common();
		
		register_activation_hook( $this->client->getFile(), [ $this, 'activate_plugin' ] );
		register_deactivation_hook( $this->client->getFile(), [ $this, 'deactivation_cleanup' ] );
	}
	
	/**
	 * Initialize common hooks
	 *
	 * @return void
	 */
	protected function init_common() {
		if ( $this->show_notice ) {
			// tracking notice.
			add_action( 'admin_notices', [ $this, 'admin_notice' ] );
		}
		add_action( 'admin_init', [ $this, 'handle_optIn_optOut' ] );
		add_action( 'removable_query_args', [ $this, 'add_removable_query_args' ], 10, 1 );
		// uninstall reason.
		add_action( 'wp_ajax_' . $this->client->getSlug() . '_submit-uninstall-reason', [ $this, 'uninstall_reason_submission' ] );
		add_action( 'wp_ajax_' . $this->client->getSlug() . '_submit-support-ticket', [ $this, 'support_ticket_submission' ] );
		// cron events.
		add_filter( 'cron_schedules', [ $this, 'add_weekly_schedule' ] );
		add_action( $this->client->getSlug() . '_tracker_send_event', [ $this, 'send_tracking_data' ] );
	}
	
	/**
	 * Send tracking data to WebAppick server
	 *
	 * @param boolean $override override current settings.
	 *
	 * @return void
	 */
	public function send_tracking_data( $override = false ) {
		// skip on AJAX Requests.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( ! $this->is_tracking_allowed() && ! $override ) {
			return;
		}
		// Send a maximum of once per week.
		$last_send = $this->__get_last_send();
		/**
		 * Tracking interval
		 *
		 * @param string $interval A valid date/time string
		 *
		 * @see strtotime()
		 * @link https://www.php.net/manual/en/function.strtotime.php
		 */
		$trackingInterval = apply_filters( $this->client->getSlug() . '_tracking_interval', '-1 week' );
		try {
			$intervalCheck = strtotime( $trackingInterval );
		} catch ( Exception $e ) {
			// fallback to default 1 week if filter returned unusable data.
			$intervalCheck = strtotime( '-1 week' );
		}
		if ( $last_send && $last_send > $intervalCheck && ! $override ) {
			return;
		}
		$this->client->send_request( $this->get_tracking_data(), 'track' );
		update_option( $this->client->getSlug() . '_tracking_last_send', time(), false );
	}
	
	/**
	 * Get the tracking data points
	 *
	 * @return array
	 */
	protected function get_tracking_data() {
		$all_plugins  = $this->__get_all_plugins();
		$admin_user   = $this->__get_admin();
		$admin_emails = [ get_option( 'admin_email' ), $admin_user->user_email ];
		$admin_emails = array_filter( $admin_emails );
		$admin_emails = array_unique( $admin_emails );
		$data         = [
			'version'          => $this->client->getProjectVersion(),
			'url'              => esc_url( home_url() ),
			'site'             => $this->__get_site_name(),
			'admin_email'      => implode( ',', $admin_emails ),
			'first_name'       => $admin_user->first_name ? $admin_user->first_name : $admin_user->display_name,
			'last_name'        => $admin_user->last_name,
			'plugin'           => $this->client->getName(),
			'hash'             => $this->client->getHash(),
			'server'           => $this->__get_server_info(),
			'wp'               => $this->__get_wp_info(),
			'active_plugins'   => $all_plugins['active_plugins'],
			'inactive_plugins' => $all_plugins['inactive_plugins'],
			'ip_address'       => $this->__get_user_ip_address(),
			'theme'            => get_stylesheet(),
		];
		// for child classes.
		$extra = $this->get_extra_data();
		if ( ! empty( $extra ) ) {
			$data['extra'] = $extra;
		}
		
		return apply_filters( $this->client->getSlug() . '_tracker_data', $data );
	}
	
	/**
	 * If a child class wants to send extra data
	 *
	 * @return mixed
	 */
	protected function get_extra_data() {
		return $this->extra_data;
	}
	
	/**
	 * Explain the user which data we collect
	 *
	 * @return array
	 */
	protected function data_we_collect() {
		$data = [
			esc_html__( 'Server environment details (php, mysql, server, WordPress versions).', 'webappick' ),
		];
		$data = apply_filters( $this->client->getSlug() . '_what_tracked', $data );
		
		return $data;
	}
	
	/**
	 * Get the message array of what data being collected
	 * @return array
	 */
	public function get_data_collection_description() {
		return $this->data_we_collect();
	}
	
	/**
	 * Get Site SuperAdmin
	 * Returns Empty WP_User instance if fails
	 * @return WP_User
	 */
	private function __get_admin() {
		$admins = get_users(
			[
				'role'    => 'administrator',
				'orderby' => 'ID',
				'order'   => 'ASC',
				'number'  => 1,
				'paged'   => 1,
			]
		);
		
		return ( is_array( $admins ) && ! empty( $admins ) ) ? $admins[0] : new WP_User();
	}
	
	/**
	 * Check if the user has opted into tracking
	 *
	 * @return bool
	 */
	public function is_tracking_allowed() {
		return 'yes' == get_option( $this->client->getSlug() . '_allow_tracking', 'no' );
	}
	
	/**
	 * Get the last time a tracking was sent
	 *
	 * @return false|int
	 */
	private function __get_last_send() {
		return get_option( $this->client->getSlug() . '_tracking_last_send', false );
	}
	
	/**
	 * Check if the notice has been dismissed or enabled
	 *
	 * @return boolean
	 */
	private function __notice_dismissed() {
		$hide_notice = get_option( $this->client->getSlug() . '_tracking_notice', 'no' );
		if ( 'hide' == $hide_notice ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if the current server is localhost
	 *
	 * @return boolean
	 */
	public function __is_local_server() {
		// phpcs:disable
		return apply_filters( 'WebAppick_is_local', in_array( $_SERVER['REMOTE_ADDR'], [ '127.0.0.1', '::1' ] ) );
		// phpcs:enable
	}
	
	/**
	 * Schedule the event weekly
	 *
	 * @return void
	 */
	private function __schedule_event() {
		$hook_name = $this->client->getSlug() . '_tracker_send_event';
		if ( ! wp_next_scheduled( $hook_name ) ) {
			wp_schedule_event( time(), 'weekly', $hook_name );
		}
	}
	
	/**
	 * Clear any scheduled hook
	 *
	 * @return void
	 */
	private function __clear_schedule_event() {
		wp_clear_scheduled_hook( $this->client->getSlug() . '_tracker_send_event' );
	}
	
	/**
	 * Display the admin notice to users that have not opted-in or out
	 *
	 * @return void
	 */
	public function admin_notice() {
		if ( $this->__notice_dismissed() ) {
			return;
		}
		
		if ( $this->is_tracking_allowed() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		// don't show tracking if a local server.
		if ( ! $this->__is_local_server() ) {
			
			if ( empty( $this->notice ) ) {
				$notice = sprintf(
					apply_filters(
						$this->client->getSlug() . '_tracking_default_notice_message',
						/* translators: 1: plugin name */
						esc_html__( 'Want to help make %1$s even more awesome? Allow %1$s to collect non-sensitive diagnostic data and usage information.', 'webappick' )
					),
					'<strong>' . esc_html( $this->client->getName() ) . '</strong>'
				);
			} else {
				$notice = $this->notice;
			}
			
			$notice .= ' (<a class="' . $this->client->getSlug() . '-insights-data-we-collect" href="#">' . esc_html__( 'what we collect', 'webappick' ) . '</a>)';
			$notice .= '<p class="description" style="display:none;">' . implode( ', ', $this->data_we_collect() ) . '. ' . esc_html__( 'No sensitive data is tracked.', 'webappick' ) . '</p>';
			echo '<div class="updated"><p>';
			echo $notice; // phpcs:ignore xss ok
			echo '</p><p class="submit">';
			echo '&nbsp;<a href="' . esc_url( $this->get_opt_out_url() ) . '" class="button button-secondary">' . esc_html__( 'No thanks', 'webappick' ) . '</a>';
			echo '&nbsp;<a href="' . esc_url( $this->get_opt_in_url() ) . '" class="button button-primary">' . esc_html__( 'Allow', 'webappick' ) . '</a>';
			echo '</p></div>';
			echo "<script type='text/javascript'>jQuery('." . esc_attr( $this->client->getSlug() ) . "-insights-data-we-collect').on('click', function(e) {
                    e.preventDefault();
                    jQuery(this).parents('.updated').find('p.description').slideToggle('fast');
            });</script>";
		}
	}
	
	/**
	 * Tracking Opt In URL
	 * @return string
	 */
	public function get_opt_in_url() {
		return add_query_arg(
			[
				$this->client->getSlug() . '_tracker_optIn' => 'true',
				'_wpnonce' => wp_create_nonce( $this->client->getSlug() . '_insight_action' ),
			]
		);
	}
	
	/**
	 * Tracking Opt Out URL
	 * @return string
	 */
	public function get_opt_out_url() {
		return add_query_arg(
			[
				$this->client->getSlug() . '_tracker_optOut' => 'true',
				'_wpnonce' => wp_create_nonce( $this->client->getSlug() . '_insight_action' ),
			]
		);
	}
	
	/**
	 * handle the optIn/optOut
	 *
	 * @return void
	 */
	public function handle_optIn_optOut() {
		if ( isset( $_REQUEST['_wpnonce'] ) && ( isset( $_GET[ $this->client->getSlug() . '_tracker_optIn' ] ) || isset( $_GET[ $this->client->getSlug() . '_tracker_optOut' ] ) ) ) {
			check_admin_referer( $this->client->getSlug() . '_insight_action' );
			if ( isset( $_GET[ $this->client->getSlug() . '_tracker_optIn' ] ) && 'true' == $_GET[ $this->client->getSlug() . '_tracker_optIn' ] ) {
				$this->optIn();
				wp_safe_redirect( remove_query_arg( $this->client->getSlug() . '_tracker_optIn' ) );
				exit;
			}
			if ( isset( $_GET[ $this->client->getSlug() . '_tracker_optOut' ] ) && 'true' == $_GET[ $this->client->getSlug() . '_tracker_optOut' ] ) {
				$this->optOut();
				wp_safe_redirect( remove_query_arg( $this->client->getSlug() . '_tracker_optOut' ) );
				exit;
			}
		}
	}
	
	/**
	 * Add query vars to removable query args array
	 *
	 * @param array $removable_query_args   array of removable args.
	 *
	 * @return array
	 */
	public function add_removable_query_args( $removable_query_args ) {
		return array_merge(
			$removable_query_args,
			[ $this->client->getSlug() . '_tracker_optIn', $this->client->getSlug() . '_tracker_optOut', '_wpnonce' ]
		);
	}
	
	/**
	 * Tracking optIn
	 *
	 * @param bool $override optional. set send tracking data override setting, ignore last send datetime setting if true.
	 *
	 * @return void
	 * @see Insights::send_tracking_data()
	 */
	public function optIn( $override = false ) {
		update_option( $this->client->getSlug() . '_allow_tracking', 'yes', false );
		update_option( $this->client->getSlug() . '_tracking_notice', 'hide', false );
		$this->__clear_schedule_event();
		$this->__schedule_event();
		$this->send_tracking_data( $override );
	}
	
	/**
	 * optOut from tracking
	 *
	 * @return void
	 */
	public function optOut() {
		update_option( $this->client->getSlug() . '_allow_tracking', 'no', false );
		update_option( $this->client->getSlug() . '_tracking_notice', 'hide', false );
		$this->__clear_schedule_event();
	}
	
	/**
	 * Get the number of post counts
	 *
	 * @param string $post_type PostType name to get count for.
	 *
	 * @return integer
	 */
	public function get_post_count( $post_type ) {
		global $wpdb;
		// phpcs:disable
		return (int) $wpdb->get_var(
				$wpdb->prepare(
						"SELECT count(ID) FROM $wpdb->posts WHERE post_type = %s and post_status = 'publish'",
						$post_type
				)
		);
		// phpcs:enable
	}
	
	/**
	 * Get server related info.
	 *
	 * @return array
	 */
	private function __get_server_info() {
		global $wpdb;
		$server_data = [
			'software'             => ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) ? sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ) : 'N/A',
			'php_version'          => ( function_exists( 'phpversion' ) ) ? phpversion() : 'N/A',
			'mysql_version'        => $wpdb->db_version(),
			'php_execution_time'   => @ini_get( 'max_execution_time' ), // phpcs:ignore
			'php_max_upload_size'  => size_format( wp_max_upload_size() ),
			'php_default_timezone' => date_default_timezone_get(),
			'php_soap'             => class_exists( 'SoapClient' ) ? 'Yes' : 'No',
			'php_fsockopen'        => function_exists( 'fsockopen' ) ? 'Yes' : 'No',
			'php_curl'             => function_exists( 'curl_init' ) ? 'Yes' : 'No',
			'php_ftp'              => function_exists( 'ftp_connect' ) ? 'Yes' : 'No',
			'php_sftp'             => function_exists( 'ssh2_connect' ) ? 'Yes' : 'No',
		];
		
		return $server_data;
	}
	
	/**
	 * Get WordPress related data.
	 *
	 * @return array
	 */
	private function __get_wp_info() {
		$wp_data = [
			'memory_limit' => WP_MEMORY_LIMIT,
			'debug_mode'   => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No',
			'locale'       => get_locale(),
			'version'      => get_bloginfo( 'version' ),
			'multisite'    => is_multisite() ? 'Yes' : 'No',
		];
		
		return $wp_data;
	}
	
	/**
	 * Get the list of active and inactive plugins
	 * @return array
	 */
	private function __get_all_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}
		$plugins             = get_plugins();
		$active_plugins      = [];
		$active_plugins_keys = get_option( 'active_plugins', [] );
		foreach ( $plugins as $k => $v ) {
			// Take care of formatting the data how we want it.
			$formatted = [
				'name'       => isset( $v['Name'] ) ? wp_strip_all_tags( $v['Name'] ) : '',
				'version'    => isset( $v['Version'] ) ? wp_strip_all_tags( $v['Version'] ) : 'N/A',
				'author'     => isset( $v['Author'] ) ? wp_strip_all_tags( $v['Author'] ) : 'N/A',
				'network'    => isset( $v['Network'] ) ? wp_strip_all_tags( $v['Network'] ) : 'N/A',
				'plugin_uri' => isset( $v['PluginURI'] ) ? wp_strip_all_tags( $v['PluginURI'] ) : 'N/A',
			];
			if ( in_array( $k, $active_plugins_keys ) ) {
				unset( $plugins[ $k ] ); // Remove active plugins from list so we can show active and inactive separately0
				$active_plugins[ $k ] = $formatted;
			} else {
				$plugins[ $k ] = $formatted;
			}
		}
		
		return [
			'active_plugins'   => $active_plugins,
			'inactive_plugins' => $plugins,
		];
	}
	
	/**
	 * Get user totals based on user role.
	 *
	 * @return array
	 */
	public function __get_user_counts() {
		$user_count          = [];
		$user_count_data     = count_users();
		$user_count['total'] = $user_count_data['total_users'];
		// Get user count based on user role.
		foreach ( $user_count_data['avail_roles'] as $role => $count ) {
			$user_count[ $role ] = $count;
		}
		
		return $user_count;
	}
	
	/**
	 * Add weekly cron schedule
	 *
	 * @param array $schedules Cron Schedules.
	 *
	 * @return array
	 */
	public function add_weekly_schedule( $schedules ) {
		$schedules['weekly'] = [
			'interval' => DAY_IN_SECONDS * 7,
			'display'  => __( 'Once Weekly', 'webappick' ),
		];
		
		return $schedules;
	}
	
	/**
	 * Plugin activation hook
	 *
	 * @return void
	 */
	public function activate_plugin() {
		$allowed = get_option( $this->client->getSlug() . '_allow_tracking', 'no' );
		// if it wasn't allowed before, do nothing.
		if ( 'yes' !== $allowed ) {
			return;
		}
		// re-schedule and delete the last sent time so we could force send again.
		wp_schedule_event( time(), 'weekly', $this->client->getSlug() . '_tracker_send_event' );
		delete_option( $this->client->getSlug() . '_tracking_last_send' );
		$this->send_tracking_data( true );
	}
	
	/**
	 * Clear our options upon deactivation
	 *
	 * @return void
	 */
	public function deactivation_cleanup() {
		$this->__clear_schedule_event();
		if ( 'theme' == $this->client->getType() ) {
			delete_option( $this->client->getSlug() . '_tracking_last_send' );
			delete_option( $this->client->getSlug() . '_allow_tracking' );
		}
		delete_option( $this->client->getSlug() . '_tracking_notice' );
	}
	
	/**
	 * Hook into action links and modify the deactivate link
	 *
	 * @param array $links Plugin Action Links.
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		
		if ( array_key_exists( 'deactivate', $links ) ) {
			$links['deactivate'] = str_replace( '<a', '<a class="' . $this->client->getSlug() . '-deactivate-link"', $links['deactivate'] );
		}
		
		return $links;
	}
	
	/**
	 * Deactivation reasons
	 * @return array
	 */
	private function __get_uninstall_reasons() {
		
		$reasons = [
			[
				'id'          => 'could-not-understand',
				'text'        => esc_html__( 'I couldn\'t understand how to make it work', 'webappick' ),
				'type'        => 'textarea',
				'placeholder' => esc_html__( 'Would you like us to assist you?', 'webappick' ),
			],
			[
				'id'          => 'found-better-plugin',
				'text'        => esc_html__( 'I found a better plugin', 'webappick' ),
				'type'        => 'text',
				'placeholder' => esc_html__( 'Which plugin?', 'webappick' ),
			],
			[
				'id'          => 'not-have-that-feature',
				'text'        => esc_html__( 'The plugin is great, but I need specific feature that you don\'t support', 'webappick' ),
				'type'        => 'textarea',
				'placeholder' => esc_html__( 'Could you tell us more about that feature?', 'webappick' ),
			],
			[
				'id'          => 'is-not-working',
				'text'        => esc_html__( 'The plugin is not working', 'webappick' ),
				'type'        => 'textarea',
				'placeholder' => esc_html__( 'Could you tell us a bit more whats not working?', 'webappick' ),
			],
			[
				'id'          => 'looking-for-other',
				'text'        => esc_html__( 'It\'s not what I was looking for', 'webappick' ),
				'type'        => '',
				'placeholder' => '',
			],
			[
				'id'          => 'did-not-work-as-expected',
				'text'        => esc_html__( 'The plugin didn\'t work as expected', 'webappick' ),
				'type'        => 'textarea',
				'placeholder' => esc_html__( 'What did you expect?', 'webappick' ),
			],
			[
				'id'          => 'debugging',
				'text'        => esc_html__( 'Temporary deactivation for debugging', 'webappick' ),
				'type'        => '',
				'placeholder' => '',
			],
			[
				'id'          => 'other',
				'text'        => esc_html__( 'Other', 'webappick' ),
				'type'        => 'textarea',
				'placeholder' => esc_html__( 'Could you tell us a bit more?', 'webappick' ),
			],
		];
		$extra = apply_filters( $this->client->getSlug() . '_extra_uninstall_reasons', [], $reasons );
		if ( is_array( $extra ) && ! empty( $extra ) ) {
			// extract the last (other) reason and add after extras.
			$other = array_pop( $reasons );
			$reasons = array_merge( $reasons, $extra, [ $other ] );
		}
		return $reasons;
	}
	
	/**
	 * Plugin deactivation uninstall reason submission
	 *
	 * @return void
	 */
	public function uninstall_reason_submission() {
		check_ajax_referer( $this->client->getSlug() . '_insight_action' );
		if ( ! isset( $_POST['reason_id'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid Request', 'webappick' ) );
			wp_die();
		}
		$current_user = wp_get_current_user();
		global $wpdb;
		// @TODO remove deprecated data after server update
		$data = [
			'hash'          => $this->client->getHash(),
			'reason_id'     => isset( $_REQUEST['reason_id'] ) && ! empty( $_REQUEST['reason_id'] ) ? sanitize_text_field( $_REQUEST['reason_id'] ) : '',
			'reason_info'   => isset( $_REQUEST['reason_info'] ) ? trim( sanitize_textarea_field( $_REQUEST['reason_info'] ) ) : '',
			'plugin'        => $this->client->getName(),
			'site'          => $this->__get_site_name(),
			'url'           => esc_url( home_url() ),
			'admin_email'   => get_option( 'admin_email' ),
			'user_email'    => $current_user->user_email,
			'user_name'     => $current_user->display_name,
			// deprecated.
			'first_name'    => ( ! empty( $current_user->first_name ) ) ? $current_user->first_name : $current_user->display_name,
			'last_name'     => $current_user->last_name,
			'server'        => $this->__get_server_info(),
			'software'      => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ) : 'Generic', // deprecated, using $data['server'] for wp info.
			'php_version'   => phpversion(), // deprecated, using $data['server'] for wp info.
			'mysql_version' => $wpdb->db_version(), // deprecated, using $data['server'] for wp info.
			'wp'            => $this->__get_wp_info(),
			'wp_version'    => get_bloginfo( 'version' ), // deprecated, using $data['wp'] for wp info.
			'locale'        => get_locale(), // deprecated, using $data['wp'] for wp info.
			'multisite'     => is_multisite() ? 'Yes' : 'No', // deprecated, using $data['wp'] for wp info.
			'ip_address'    => $this->__get_user_ip_address(),
			'version'       => $this->client->getProjectVersion(),
		];
		// Add extra data.
		$extra = $this->get_extra_data();
		if ( ! empty( $extra ) ) {
			$data['extra'] = $extra;
		}
		$this->client->send_request( $data, 'reason' );
		wp_send_json_success();
		wp_die();
	}
	
	/**
	 * Handle Support Ticket Submission
	 * @return void
	 */
	public function support_ticket_submission() {
		check_ajax_referer( $this->client->getSlug() . '_insight_action' );
		if ( empty( $this->ticketTemplate ) || empty( $this->ticketRecipient ) || empty( $this->supportURL ) ) {
			wp_send_json_error(
				sprintf(
					'<p class="mui-error">%s<br>%s</p>',
					esc_html__( 'Something Went Wrong.', 'webappick' ),
					esc_html__( 'Please try again after sometime.', 'webappick' )
				)
			);
			wp_die();
		}
		if (
			isset( $_REQUEST['name'], $_REQUEST['email'], $_REQUEST['subject'], $_REQUEST['website'], $_REQUEST['message'] ) &&
			(
				! empty( sanitize_text_field( $_REQUEST['name'] ) ) &&
				! empty( sanitize_email( $_REQUEST['email'] ) ) &&
				! empty( sanitize_text_field( $_REQUEST['subject'] ) ) &&
				! empty( sanitize_text_field( $_REQUEST['website'] ) ) &&
				! empty( sanitize_text_field( $_REQUEST['message'] ) )
			)
		) {
			$headers = [
				'Content-Type: text/html; charset=UTF-8',
				sprintf(
				    'From: %s <%s>',
					sanitize_text_field( $_REQUEST['name'] ),
					sanitize_email( $_REQUEST['email'] )
				),
				sprintf(
    				'Reply-To: %s <%s>',
					sanitize_text_field( $_REQUEST['name'] ),
					sanitize_text_field( $_REQUEST['email'] )
				),
			];
			
			foreach ( $_REQUEST as $k => $v ) {
				$sanitizer = 'sanitize_text_field';
				if ( 'email' == $k ) {
					$sanitizer = 'sanitize_email';
				}
				if ( 'website' == $k ) {
					$sanitizer = 'esc_url';
				}
				$v                    = call_user_func_array( $sanitizer, [ $v ] );
				$_REQUEST[ $k ]       = $v; // phpcs: sanitize ok.
				$k                    = '__' . strtoupper( $k ) . '__';
				$this->ticketTemplate = str_replace( [ $k ], [ $v ], $this->ticketTemplate );
			}
			$projectSlug = $this->client->getSlug();
			$isSent = wp_mail( $this->ticketRecipient, sanitize_text_field( $_REQUEST['subject'] ), sprintf( '<div>%s</div>', $this->ticketTemplate ), $headers );// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
			if ( $isSent ) {
				/**
				 * Set Ajax Success Response for Support Ticket Submission
				 * @param string $supportResponse
				 * @param array $_REQUEST
				 */
				$supportResponse = apply_filters( "WebAppick_{$projectSlug}_Support_Request_Ajax_Success_Response" , false, $_REQUEST );
				if ( false !== $supportResponse ) {
					$this->supportResponse = $supportResponse;
				} else {
					$this->supportResponse = sprintf(
						'<h3>%s</h3>',
						esc_html__( 'Thank you -- Support Ticket Submitted.', 'webappick' )
					);
				}
				wp_send_json_success( $this->supportResponse );
				wp_die();
			} else {
				/**
				 * Set Support Ticket Ajax Error Response.
				 * @param string $supportErrorResponse
				 * @param array $_REQUEST
				 */
				$supportErrorResponse = apply_filters( "WebAppick_{$projectSlug}_Support_Request_Ajax_Error_Response" , false, $_REQUEST );
				if ( false !== $supportErrorResponse ) {
					$this->supportErrorResponse = $supportErrorResponse;
				} else {
					$this->supportErrorResponse = sprintf(
						'<div class="mui-error"><p>%s</p></div>',
						esc_html__( 'Something Went Wrong. Please Try Again After Sometime.', 'webappick' )
					);
				}
				wp_send_json_error( $this->supportErrorResponse );
			}
		} else {
			wp_send_json_error( sprintf( '<p class="mui-error">%s</p>', esc_html__( 'Missing Required Fields.', 'webappick' ) ) );
		}
		wp_die();
	}
	
	/**
	 * Handle the plugin deactivation feedback
	 *
	 * @return void
	 */
	public function deactivate_scripts() {
		global $pagenow;
		if ( 'plugins.php' !== $pagenow ) {
			return;
		}
		$reasons     = $this->__get_uninstall_reasons();
		$admin_user  = $this->__get_admin();
		$displayName = ( ! empty( $admin_user->first_name ) && ! empty( $admin_user->last_name ) ) ? $admin_user->first_name . ' ' . $admin_user->last_name : $admin_user->display_name;
		$showSupportTicket = ( ! empty( $this->ticketTemplate ) && ! empty( $this->ticketRecipient ) && ! empty( $this->supportURL ) );
		?>
		<div class="wapk-dr-modal" id="<?php echo esc_attr( $this->client->getSlug() ); ?>-wapk-dr-modal" aria-label="<?php /* translators: 1: Plugin Name */ printf( esc_attr__( '&ldquo;%s&rdquo; Uninstall Confirmation', 'webappick' ), esc_attr( $this->client->getName() ) ); ?>" role="dialog" aria-modal="true">
			<?php if ( $showSupportTicket ) { ?>
			<div class="wapk-dr-modal-wrap support" style="display: none;">
				<div class="wapk-dr-modal-header">
					<h3><?php esc_html_e( 'Submit Support Ticket.', 'webappick' ); ?></h3>
					<a href="javascript:void 0;" class="wapk-dr-modal-close" aria-label="<?php esc_attr_e( 'Close', 'webappick' ); ?>">
						<!--suppress HtmlUnknownAttribute -->
						<svg class="" focusable="false" viewBox="0 0 24 24" aria-hidden="true" role="presentation">
							<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
						</svg>
					</a>
				</div>
				<div class="wapk-dr-modal-body">
					<div class="wapk-row mui col-2 col-left">
						<label for="wapk-support-name" class="<?php echo ! empty( $displayName ) ? 'shrink' : ''; ?>"><?php esc_html_e( 'Name', 'webappick' ); ?></label>
						<div class="wapk-form-control">
							<input type="text" name="name" id="wapk-support-name" value="<?php echo esc_attr( $displayName ); ?>" required>
						</div>
					</div>
					<div class="wapk-row mui col-2 col-right">
						<label for="wapk-support-email" class="shrink"><?php esc_html_e( 'Email', 'webappick' ); ?></label>
						<div class="wapk-form-control">
							<input type="email" name="email" id="wapk-support-email" value="<?php echo esc_attr( $admin_user->user_email ); ?>" required>
						</div>
					</div>
					<div class="clear"></div>
					<div class="wapk-row mui col-2 col-left">
						<label for="wapk-support-subject"><?php esc_html_e( 'Subject', 'webappick' ); ?></label>
						<div class="wapk-form-control">
							<input type="text" name="subject" id="wapk-support-subject" required>
						</div>
					</div>
					<div class="wapk-row mui col-2 col-right">
						<label for="wapk-support-website" class="shrink"><?php esc_html_e( 'Website', 'webappick' ); ?></label>
						<div class="wapk-form-control">
							<input type="url" name="website" id="wapk-support-website" value="<?php echo esc_url( site_url() ); ?>" required>
						</div>
					</div>
					<div class="clear"></div>
					<div class="wapk-row mui">
						<label for="wapk-support-message"><?php esc_html_e( 'Message', 'webappick' ); ?></label>
						<div class="wapk-form-control">
							<textarea id="wapk-support-message" name='message' rows="11" required></textarea>
						</div>
					</div>
					<div class="response">
						<div class="wrapper"></div>
					</div>
				</div>
				<div class="wapk-dr-modal-footer">
					<button class="button button-primary send-ticket"><?php esc_html_e( 'Send Message', 'webappick' ); ?></button>
					<button class="button button-secondary close-ticket"><?php esc_html_e( 'Cancel', 'webappick' ); ?></button>
				</div>
			</div>
			<?php } ?>
			<div class="wapk-dr-modal-wrap reason">
				<div class="wapk-dr-modal-header">
					<h3><?php esc_html_e( 'If you have a moment, please let us know why you are deactivating:', 'webappick' ); ?></h3>
					<a href="javascript:void 0;" class="wapk-dr-modal-close" aria-label="<?php esc_attr_e( 'Close', 'webappick' ); ?>">
						<!--suppress HtmlUnknownAttribute -->
						<svg class="" focusable="false" viewBox="0 0 24 24" aria-hidden="true" role="presentation">
							<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
						</svg>
					</a>
				</div>
				<div class="wapk-dr-modal-body">
					<ul class="reasons">
						<?php foreach ( $reasons as $reason ) { ?>
							<li data-type="<?php echo esc_attr( $reason['type'] ); ?>" data-placeholder="<?php echo esc_attr( $reason['placeholder'] ); ?>">
								<label><input type="radio" name="selected-reason" value="<?php echo esc_attr( $reason['id'] ); ?>"> <?php echo esc_html( $reason['text'] ); ?></label>
							</li>
						<?php } ?>
					</ul>
					<div class="response" style="<?php echo ( $showSupportTicket ) ? 'display: block;' : ''; ?>">
						<div class="wrapper">
						<?php if ( $showSupportTicket ) { ?>
							<p><?php esc_html_e( 'In trouble? Please submit a support request.', 'webappick' ); ?></p>
							<p>
								<a href="#" class="button button-secondary not-interested"><?php esc_html_e( 'Not Interested', 'webappick' ); ?></a>
								<button class="button button-primary open-ticket-form"><?php esc_html_e( 'Open Support Ticket', 'webappick' ); ?></button>
							</p>
						<?php } ?>
						</div>
					</div>
				</div>
				<div class="wapk-dr-modal-footer">
					<a href="#" class="button button-link dont-bother-me disabled"><?php esc_html_e( 'I rather wouldn\'t say', 'webappick' ); ?></a>
					<button class="button button-secondary deactivate disabled"><?php esc_html_e( 'Submit & Deactivate', 'webappick' ); ?></button>
					<button class="button button-primary modal-close disabled"><?php esc_html_e( 'Cancel', 'webappick' ); ?></button>
				</div>
			</div>
		</div>
		<!--suppress CssUnusedSymbol, CssInvalidPseudoSelector, CssFloatPxLength -->
		<style type="text/css">
			.wapk-dr-modal, .wapk-dr-modal * { box-sizing: border-box; }
			.wapk-dr-modal { position: fixed; z-index: 99999; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0, 0, 0, 0.5); display: none; }
			.wapk-dr-modal.modal-active { display: block; }
			.wapk-dr-modal strong, .wapk-dr-modal b { font-weight: bold; }
			.wapk-dr-modal-wrap { width: 475px; position: relative; margin: 10% auto; background: #fff; }
			.wapk-dr-modal-wrap { position: absolute; display: block; top: 0; left: 0; right: 0; /*bottom: 0;*/ z-index: 99; }
			.wapk-dr-modal-wrap.support { z-index: 999; margin: 6% auto; }
			.wapk-dr-modal-wrap .response { position: absolute; display: none; background: rgba(0, 150, 136, 0.68); top: 0; left: 0; right: 0; bottom: 0; overflow: hidden; }
			.wapk-dr-modal .response.show { display: block; }
			.wapk-dr-modal-wrap .response .wrapper { display: flex; align-items: center; justify-content: center; width: calc(100% - 40px); height: calc(100% - 40px); flex-flow: column; padding: 40px 40px; margin: 20px 20px; text-align: center; background: #FFF; }
			.wapk-dr-modal .reason .response .wrapper { width: calc(100% - 80px); height: calc(100% - 80px); flex-flow: column; padding: 40px 40px; margin: 40px; }
			.wapk-dr-modal .button .dashicons { margin: 4px 0; }
			.wapk-dr-modal-header { border-bottom: 1px solid #eee; padding: 8px 40px 8px 20px; position: relative; display: block; width: 100%; float: left; }
			.wapk-dr-modal-header h3 { line-height: 150%; margin: 0; }
			.wapk-dr-modal-close { position: absolute; top: 50%; right: 10px; transform: translateY(-50%); width: 20px; height: 20px; line-height: 0; }
			.wapk-dr-modal-close svg { font-size: 20px; display: inline-block; width: 1em; height: 1em; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
			.wapk-dr-modal-body { padding: 5px 20px 20px 20px; position: relative; display: block; width: 100%; float: left; box-sizing: border-box; }
			.wapk-dr-modal-body .reason-input { margin-top: 5px; margin-left: 20px; }
			.wapk-dr-modal-footer { border-top: 1px solid #eee; padding: 12px 20px; text-align: right; position: relative; display: block; width: 100%; float: left; }
			.wapk-dr-modal-footer a, .wapk-dr-modal-footer button { vertical-align: middle; }
			.support .wapk-dr-modal-footer { text-align: left; }
			.support .wapk-dr-modal-footer button { float: right; margin-left: 10px; }
			.wapk-dr-modal .wapk-row { position: relative; width: 100%; display: block; box-sizing: border-box; float: left; margin: 4px auto; }
			.mui { border: 0; margin: 0; display: inline-flex; padding: 0; min-width: 0; flex-direction: column; vertical-align: top; }
			.wapk-dr-modal .wapk-row.col-2 { width: calc(50% - 16px); }
			.wapk-dr-modal .wapk-row.col-3 { width: calc(calc(100% / 3) - 16px); }
			.wapk-dr-modal .wapk-row.col-left { margin-right: 8px; }
			.wapk-dr-modal .wapk-row.col-center { margin-left: 8px; margin-right: 8px; }
			.wapk-dr-modal .wapk-row.col-right { margin-left: 8px; }
			.wapk-dr-modal .mui .wapk-form-control { cursor: text; display: inline-flex; position: relative; font-size: 1rem; box-sizing: border-box; align-items: center; line-height: 1.1875em; width: 100%; }
			.wapk-dr-modal .mui label { color: rgba(0, 0, 0, 0.54); padding: 0; font-size: 1rem; font-weight: 400; line-height: 1; letter-spacing: 0.00938em; display: block; transform-origin: top left; top: 0; left: 0; position: absolute; transform: translate(0, 24px) scale(1); transition: color 200ms cubic-bezier(0.0, 0, 0.2, 1) 0ms, transform 200ms cubic-bezier(0.0, 0, 0.2, 1) 0ms; }
			.wapk-dr-modal .mui label.focused { color: #007cba; }
			p:not(.helper-text).mui-error, div:not(.helper-text).mui-error, .wapk-dr-modal .mui label.mui-error { color: #f44336; }
			p:not(.helper-text).mui-error, div:not(.helper-text).mui-error { padding: 5px 10px; border: 1px solid #f44336; font-weight: bold; }
			.wapk-dr-modal .mui label.shrink { transform: translate(0, 1.5px) scale(0.75); transform-origin: top left; }
			.wapk-dr-modal .mui label + .wapk-form-control { margin-top: 16px; }
			.wapk-dr-modal .mui .wapk-form-control:before { left: 0; right: 0; bottom: 0; content: "\00a0"; position: absolute; transition: border-bottom-color 200ms cubic-bezier(0.4, 0, 0.2, 1) 0ms; border-bottom: 1px solid rgba(0, 0, 0, 0.42); pointer-events: none; }
			.wapk-dr-modal .mui .wapk-form-control:hover:not(.disabled):before { border-bottom: 2px solid rgba(0, 0, 0, 0.87); }
			.wapk-dr-modal .mui .wapk-form-control:after { left: 0; right: 0; bottom: 0; content: ""; position: absolute; transform: scaleX(0); transition: transform 200ms cubic-bezier(0.0, 0, 0.2, 1) 0ms; border-bottom: 2px solid #007cba; pointer-events: none; }
			.wapk-dr-modal .mui .wapk-form-control.focused:after { transform: scaleX(1); }
			.wapk-dr-modal .mui .wapk-form-control.mui-error:after { transform: scaleX(1); border-bottom-color: #f44336; }
			.wapk-dr-modal .mui .wapk-form-control input,
			.wapk-dr-modal .mui .wapk-form-control textarea { font: inherit; color: currentColor; width: 100%; border: 0; height: 1.1875em; min-height: auto; margin: 0; display: block; padding: 6px 0 7px; min-width: 0; background: none; box-sizing: content-box; -webkit-tap-highlight-color: transparent; }
			.wapk-dr-modal .mui .wapk-form-control input { animation-name: mui-keyframes-auto-fill-cancel; }
			.wapk-dr-modal .mui .wapk-form-control input:-moz-autofill,
			.wapk-dr-modal .mui .wapk-form-control input:-webkit-autofill { animation-name: mui-keyframes-auto-fill; animation-duration: 5000s; }
			@-webkit-keyframes mui-keyframes-auto-fill {}
			@-webkit-keyframes mui-keyframes-auto-fill-cancel {}
			.wapk-dr-modal .mui .wapk-form-control textarea { height: auto; resize: none; padding: 0; }
			.wapk-dr-modal .mui .wapk-form-control input::-webkit-search-decoration,
			.wapk-dr-modal .mui .wapk-form-control textarea::-webkit-search-decoration { -webkit-appearance: none; }
			.wapk-dr-modal .mui .wapk-form-control input::-webkit-input-placeholder,
			.wapk-dr-modal .mui .wapk-form-control textarea::-webkit-input-placeholder { color: currentColor; opacity: 0.42; transition: opacity 200ms cubic-bezier(0.4, 0, 0.2, 1) 0ms; }
			.wapk-dr-modal .mui .wapk-form-control input::-moz-placeholder,
			.wapk-dr-modal .mui .wapk-form-control textarea::-moz-placeholder { color: currentColor; opacity: 0.42; transition: opacity 200ms cubic-bezier(0.4, 0, 0.2, 1) 0ms; }
			.wapk-dr-modal .mui .wapk-form-control input:-ms-input-placeholder,
			.wapk-dr-modal .mui .wapk-form-control textarea:-ms-input-placeholder { color: currentColor; opacity: 0.42; transition: opacity 200ms cubic-bezier(0.4, 0, 0.2, 1) 0ms; }
			.wapk-dr-modal .mui .wapk-form-control input::-ms-input-placeholder,
			.wapk-dr-modal .mui .wapk-form-control textarea::-ms-input-placeholder { color: currentColor; opacity: 0.42; transition: opacity 200ms cubic-bezier(0.4, 0, 0.2, 1) 0ms; }
			.wapk-dr-modal .mui .wapk-form-control input:focus,
			.wapk-dr-modal .mui .wapk-form-control input:invalid,
			.wapk-dr-modal .mui .wapk-form-control textarea:focus,
			.wapk-dr-modal .mui .wapk-form-control textarea:invalid { outline: 0; box-shadow: none; }
			.wapk-dr-modal .mui .helper-text { color: rgba(0, 0, 0, 0.54); margin: 8px 0 0 0; font-size: 0.75rem; min-height: 1em; text-align: left; font-weight: 400; line-height: 1em; letter-spacing: 0.03333em; }
			.wapk-dr-modal .mui .helper-text.contained { margin: 8px 14px 0; }
			.wapk-dr-modal .mui .helper-text.mui-error { color: #f44336; }
			.wapk-dr-modal .button.disabled, .wapk-dr-modal button.disabled { cursor: not-allowed !important; }
			/*.wapk-dr-modal .wapk-row input, .wapk-dr-modal .wapk-row textarea { width: calc( 100% - 10px ); margin: 0 5px; display: block; vertical-align: middle; box-sizing: border-box; float: left; }*/
		</style>
		<!--suppress ES6ConvertVarToLetConst, JSUnresolvedVariable -->
		<script type="text/javascript">
            (function ($) {
                $(function () {
                    /**
                     * Ajax Helper For Submitting Deactivation Reasons
                     * @param {Object} data
                     * @param {*|jQuery|HTMLElement} buttonElem
                     * @param {String|Function} cb
                     * @returns {*|jQuery}
                     * @private
                     */
                    function _ajax(data, buttonElem, cb) {
                        if (buttonElem.hasClass('disabled')) return;
                        buttonElem.attr('data-label', buttonElem.text());
                        return $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: $.fn.extend( {}, {
                                action: '<?php echo esc_attr( $this->client->getSlug() ); ?>_submit-uninstall-reason',
                                _wpnonce: '<?php echo esc_attr( wp_create_nonce( $this->client->getSlug() . '_insight_action' ) ); ?>'
                            }, data ), // add default action if action is empty.
                            beforeSend: function () {
                                buttonElem.addClass("disabled");
                                buttonElem.text('<?php esc_html_e( 'Processing...', 'webappick' ); ?>');
                            },
                            complete: function (event, xhr, options) {
                                if ('string' === typeof cb) {
                                    window.location.href = cb;
                                } else if ('function' === typeof cb) {
                                    cb({event: event, xhr: xhr, options: options});
                                }
                            }
                        });
                    }
                    // Variables.
                    var modal = $('#<?php echo esc_attr( $this->client->getSlug() ); ?>-wapk-dr-modal'),
                        deactivateLink = '',
	                    reason = modal.find('.reason'),
	                    support = modal.find('.support'),
                        supportResponse = support.find('.response'),
                        reasonResponse = reason.find('.response'),
                        mui = $('.mui input, .mui textarea, .mui select'),
                        // sendButton = modal.find('.send-ticket'),
                        validMessage = false,
                        preventDefault = function ( e ) { e && e.preventDefault() },
                        responseButtons = modal.find('.reason .wapk-dr-modal-footer .button'),
	                    supportURL = '<?php echo esc_url( $this->supportURL ); ?>',
                        closeModal = function (e) {
                            preventDefault(e);
                            var buttons = modal.find('.button');
                            modal.removeClass('modal-active');
                            // modal.find('.wapk-dr-modal-wrap').show();
                            supportResponse.hide().find('.wrapper').html('');
                            reasonResponse.show();
                            support.hide();
                            // enable buttons and restore original labels
                            buttons.removeClass('disabled');
                            responseButtons.addClass('disabled');
                            buttons.each(function () {
                                var self = $(this), label = self.attr('data-label');
                                if (label) self.text(label);
                            });
                            modal.find('input[type="radio"]').prop('checked', false );
                            $('.reason-input').remove();
                        },
                        checkMessageValidity = function (e) {
                            // e.target.checkValidity();
                            var target = e && e.target ? e.target : this;
                            var self = $(this), currentMui = self.closest('.mui'),
                                label = currentMui.find('label'),
                                control = currentMui.find('.wapk-form-control');
                            if (target.checkValidity()) {
                                if (label.hasClass('mui-error')) label.removeClass('mui-error');
                                if (control.hasClass('mui-error')) control.removeClass('mui-error');
                                currentMui.find('p.helper-text').hide().remove();
                                validMessage = true;
                            }
                        },
                        resetTicketForm = function( clearValues, clearAll ) {
                            $('p.helper-text.mui-error').remove();
                            $('.mui-error').removeClass('mui-error');
                            if( clearValues ) {
                                if( clearAll ) mui.val('');
                                else modal.find('#wapk-support-message,#wapk-support-subject').val('');
                            }
                        };
                    // The MUI
	                {
                        // any input el except radio, checkbox and select
                        mui
                            .not('select')
                            .not('[type="checkbox"]')
                            .not('[type="radio"]')
                            .on('focus', function () {
                                var self = $(this), currentMui = self.closest('.mui'),
                                    label = currentMui.find('label'),
                                    control = currentMui.find('.wapk-form-control');
                                control.addClass('focused');
                                label.addClass('focused');
                                label.addClass('shrink');
                            })
                            .on('blur', function () {
                                var self = $(this), currentMui = self.closest('.mui'),
                                    label = currentMui.find('label'),
                                    control = currentMui.find('.wapk-form-control');
                                control.removeClass('focused');
                                label.removeClass('focused');
                                if (self.val() === '') {
                                    label.removeClass('shrink');
                                }
                            });
                        // any input el in mui
                        mui
                            .on('blur', checkMessageValidity)
                            .on('invalid', function (e) {
                                preventDefault(e);
                                var self = $(this), currentMui = self.closest('.mui'),
                                    label = currentMui.find('label'),
                                    control = currentMui.find('.wapk-form-control');
                                currentMui.find('p.helper-text').remove();
                                validMessage = false;
                                if (!label.hasClass('mui-error')) label.addClass('mui-error');
                                if (!control.hasClass('mui-error')) control.addClass('mui-error');
                                control.after('<p class="helper-text mui-error">' + e.target.validationMessage + '</p>');
                            });
	                }
                    // the clicker
                    $('#the-list').on('click', 'a.<?php echo esc_attr( $this->client->getSlug() ); ?>-deactivate-link', function (e) {
                        preventDefault(e);
                        modal.addClass('modal-active');
                        deactivateLink = $(this).attr('href');
                        modal.find('a.dont-bother-me').attr('href', deactivateLink).css('float', 'left');
                    });
                    // The Modal
                    modal
	                    .on( 'click', '.not-interested', function(e) {
                            preventDefault(e);
                            $( this ).closest('.response').slideUp( function() {
                                responseButtons.removeClass('disabled');
                            } );
	                    } )
	                    .on( 'click', '.open-ticket-form', function(e){
                            preventDefault(e);
                            support.slideDown();
                            resetTicketForm( true );
	                    } )
                        .on( 'click', '.close-ticket', function (e) {
                            preventDefault(e);
                            support.slideUp();
                            //if( ! reasonResponse.is(':visible') ) responseButtons.removeClass('disabled');
                        } )
                        .on( 'click', '.modal-close, .wapk-dr-modal-close', closeModal )
                        .on( 'click', 'input[type="radio"]', function () {
                            modal.find('.reason-input').remove();
                            var parent = $(this).parents('li:first'),
                                inputType = parent.data('type'),
                                inputPlaceholder = parent.data('placeholder'),
                                reasonInputHtml = '<div class="reason-input">' + (('text' === inputType) ? '<input type="text" size="40" />' : '<textarea rows="5" cols="45"></textarea>') + '</div>';
                            if (inputType !== '') {
                                parent.append($(reasonInputHtml));
                                parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();
                            }
                        } )
                        .on( 'click', '.dont-bother-me', function (e) {
                            preventDefault(e);
                            _ajax({
                                reason_id: 'no-comment',
                                reason_info: 'I rather wouldn\'t say'
                            }, $(this), deactivateLink);
                        } )
                        .on( 'click', '.deactivate', function (e) {
                            preventDefault(e);
                            var $radio = $('input[type="radio"]:checked', modal),
                                $selected_reason = $radio.parents('li:first'),
                                $input = $selected_reason.find('textarea, input[type="text"]');
                            _ajax({
                                reason_id: (0 === $radio.length) ? 'none' : $radio.val(),
                                reason_info: (0 !== $input.length) ? $input.val().trim() : ''
                            }, $(this), deactivateLink);
                        } )
                        .on( 'click', '.send-ticket', function (e) {
                            preventDefault(e);
                            mui.each(checkMessageValidity);
                            if (!validMessage) return;
                            var buttonElem = $(this),
	                            __BTN_TEXT__ = buttonElem.text(),
	                            data = {action: '<?php echo esc_attr( $this->client->getSlug() ); ?>_submit-support-ticket',};
                            mui.each( function () { data[$(this).attr('name')] = $(this).val() } );
                            _ajax(data, $(this), function (jqXhr) {
                                var response = jqXhr.event.responseJSON;
                                if ( response.hasOwnProperty('data') ) {
                                    supportResponse.find('.wrapper').html(response.data);
                                    supportResponse.show();
                                }
                                if( response.success ) {
                                    modal.find('#wapk-support-message,#wapk-support-subject').val('');
                                } else {
                                    setTimeout( function() {
                                        window.open( supportURL, '_blank' );
                                        // supportResponse.slideUp();
                                        buttonElem.hasClass('disabled') && buttonElem.removeClass('disabled');
                                    }, 5000 );
                                }
                                buttonElem.text(__BTN_TEXT__);
                            });
                        });
                });
            }(jQuery));
		</script>
		<?php
	}
	
	/**
	 * Run after theme deactivated
	 *
	 * @param string   $new_name    New Theme Name.
	 * @param WP_Theme $new_theme   New Theme WP_Theme Object.
	 * @param WP_Theme $old_theme   Old Theme WP_Theme Object.
	 *
	 * @return void
	 */
	public function theme_deactivated( $new_name, $new_theme, $old_theme ) {
		// Make sure this is WebAppick theme.
		if ( $old_theme->get_template() == $this->client->getSlug() ) {
			$current_user = wp_get_current_user();
			/** @noinspection PhpUndefinedFieldInspection */
			$data = [
				'hash'        => $this->client->getHash(),
				'reason_id'   => 'none',
				'reason_info' => wp_json_encode(
					[
						'new_theme' => [
							'name'         => $new_name,
							'version'      => $new_theme->version,
							'parent_theme' => $new_name->parent_theme,
							'author'       => $new_name->parent_theme,
						],
					]
				),
				'site'        => $this->__get_site_name(),
				'url'         => esc_url( home_url() ),
				'admin_email' => get_option( 'admin_email' ),
				'user_email'  => $current_user->user_email,
				'first_name'  => $current_user->first_name,
				'last_name'   => $current_user->last_name,
				'server'      => $this->__get_server_info(),
				'wp'          => $this->__get_wp_info(),
				'ip_address'  => $this->__get_user_ip_address(),
				'version'     => $this->client->getProjectVersion(),
			];
			$this->client->send_request( $data, 'reason' );
		}
	}
	
	/**
	 * Get user IP Address
	 * @return string
	 */
	private function __get_user_ip_address() {
		$response = wp_safe_remote_get( 'https://icanhazip.com/' );
		if ( is_wp_error( $response ) ) {
			return '';
		}
		$ip = trim( wp_remote_retrieve_body( $response ) );
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return '';
		}
		
		return $ip;
	}
	
	/**
	 * Get site name
	 * @return string
	 */
	private function __get_site_name() {
		$site_name = get_bloginfo( 'name' );
		if ( empty( $site_name ) ) {
			$site_name = get_bloginfo( 'description' );
			$site_name = wp_trim_words( $site_name, 3, '' );
		}
		if ( empty( $site_name ) ) {
			$site_name = get_bloginfo( 'url' );
		}
		
		return $site_name;
	}
}
// End of file Insights.php.