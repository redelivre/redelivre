<?php
/**
 * Abstract class for Smush view: Abstract_Page
 *
 * @package Smush\App
 */

namespace Smush\App;

use Smush\Core\Modules\Dir;
use Smush\Core\Settings;
use WP_Smush;
use WPMUDEV_Dashboard;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Abstract_Page
 */
abstract class Abstract_Page {

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Page ID.
	 *
	 * @var false|null|string
	 */
	private $page_id = null;

	/**
	 * Meta boxes array.
	 *
	 * @var array
	 */
	protected $meta_boxes = array();

	/**
	 * Submenu tabs.
	 *
	 * @var array
	 */
	protected $tabs = array();

	/**
	 * Settings instance for faster access.
	 *
	 * @since 3.0
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Link to upgrade.
	 *
	 * @var string $upgrade_url
	 */
	protected $upgrade_url = 'https://premium.wpmudev.org/project/wp-smush-pro/';

	/**
	 * Abstract_Page constructor.
	 *
	 * @param string $slug     Page slug.
	 * @param string $title    Page title.
	 * @param bool   $parent   Does a page have a parent (will be added as a sub menu).
	 * @param bool   $nextgen  Is that a NextGen subpage.
	 */
	public function __construct( $slug, $title, $parent = false, $nextgen = false ) {
		$this->slug     = $slug;
		$this->settings = Settings::get_instance();

		if ( ! $parent ) {
			$this->page_id = add_menu_page(
				$title,
				$title,
				'manage_options',
				$this->slug,
				$parent ? array( $this, 'render' ) : null,
				$this->get_menu_icon()
			);
		} else {
			$this->page_id = add_submenu_page(
				$parent,
				$title,
				$title,
				$nextgen ? 'NextGEN Manage gallery' : 'manage_options',
				$this->slug,
				array( $this, 'render' )
			);
		}

		// No need to load these action on parent pages, as they are just placeholders for sub pages.
		if ( $parent ) {
			add_filter( 'load-' . $this->page_id, array( $this, 'on_load' ) );
			add_action( 'load-' . $this->page_id, array( $this, 'register_meta_boxes' ) );
			add_filter( 'load-' . $this->page_id, array( $this, 'add_action_hooks' ) );
		}
	}

	/**
	 * Common hooks for all screens
	 *
	 * @since 2.9.0
	 */
	public function add_action_hooks() {
		// Notices.
		add_action( 'admin_notices', array( $this, 'smush_upgrade_notice' ) );
		add_action( 'admin_notices', array( $this, 'smush_deactivated' ) );
		add_action( 'network_admin_notices', array( $this, 'smush_deactivated' ) );

		add_action( 'admin_notices', array( $this, 'smush_dash_required' ) );
		add_action( 'network_admin_notices', array( $this, 'smush_dash_required' ) );

		add_filter( 'admin_body_class', array( $this, 'smush_body_classes' ) );
		// Filter built-in wpmudev branding script.
		add_filter( 'wpmudev_whitelabel_plugin_pages', array( $this, 'builtin_wpmudev_branding' ) );
	}

	/**
	 * Return the admin menu slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Load an admin view.
	 *
	 * @param string $name  View name = file name.
	 * @param array  $args  Arguments.
	 * @param string $dir   Directory for the views. Default: views.
	 */
	public function view( $name, $args = array(), $dir = 'views' ) {
		$file    = WP_SMUSH_DIR . "app/{$dir}/{$name}.php";
		$content = '';

		if ( is_file( $file ) ) {
			ob_start();

			if ( isset( $args['id'] ) ) {
				$args['orig_id'] = $args['id'];
				$args['id']      = str_replace( '/', '-', $args['id'] );
			}
			extract( $args );

			/* @noinspection PhpIncludeInspection */
			include $file;

			$content = ob_get_clean();
		}

		echo $content;
	}

	/**
	 * Shows Notice for free users, displays a discount coupon
	 */
	public function smush_upgrade_notice() {
		// Return, If a pro user, or not super admin, or don't have the admin privileges.
		if ( WP_Smush::is_pro() || ! current_user_can( 'edit_others_posts' ) || ! is_super_admin() ) {
			return;
		}

		// Return if notice is already dismissed.
		if ( get_site_option( WP_SMUSH_PREFIX . 'hide_upgrade_notice' ) ) {
			return;
		}

		$core = WP_Smush::get_instance()->core();

		$install_type = get_site_option( 'wp-smush-install-type', false );

		if ( ! $install_type ) {
			$install_type = $core->smushed_count > 0 ? 'existing' : 'new';
			update_site_option( 'wp-smush-install-type', $install_type );
		}

		// Prepare notice.
		if ( 'new' === $install_type ) {
			$notice_heading = __( 'Thanks for installing Smush. We hope you like it!', 'wp-smushit' );
			$notice_content = __( 'And hey, if you do, you can join WPMU DEV for a free trial and get access to even more features!', 'wp-smushit' );
			$button_content = __( 'Try Smush Pro Free', 'wp-smushit' );
		} else {
			$notice_heading = __( 'Thanks for updating Smush!', 'wp-smushit' );
			$notice_content = __( 'Did you know she has secret super powers? Yes, she can super-smush images for double the savings, store original images, bulk smush thousands of images in one go, and serve \'em up in a next-gen format(WebP) with one-click via her blazing-fast CDN. Get started with a free WPMU DEV trial to access these advanced features.', 'wp-smushit' );
			$button_content = __( 'Try Smush Pro Free', 'wp-smushit' );
		}

		$upgrade_url = add_query_arg(
			array(
				'utm_source'   => 'smush',
				'utm_medium'   => 'plugin',
				'utm_campaign' => 'smush_dashboard_upgrade_notice',
			),
			$this->upgrade_url
		);
		?>
		<div class="notice smush-notice" style="display: none;">
			<div class="smush-notice-logo"><span></span></div>
			<div class="smush-notice-message<?php echo 'new' === $install_type ? ' wp-smush-fresh' : ' wp-smush-existing'; ?>">
				<strong><?php echo esc_html( $notice_heading ); ?></strong>
				<?php echo esc_html( $notice_content ); ?>
			</div>
			<div class="smush-notice-cta">
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="smush-notice-act button-primary" target="_blank">
					<?php echo esc_html( $button_content ); ?>
				</a>
				<button class="smush-notice-dismiss smush-dismiss-welcome" data-msg="<?php esc_html_e( 'Saving', 'wp-smushit' ); ?>">
					<?php esc_html_e( 'Dismiss', 'wp-smushit' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Display a admin notice about plugin deactivation.
	 */
	public function smush_deactivated() {
		// Display only in backend for administrators.
		if ( ! is_admin() || ! is_super_admin() || ! get_site_option( 'smush_deactivated' ) ) {
			return;
		}
		?>
		<div class="updated">
			<p>
				<?php esc_html_e( 'Smush Free was deactivated. You have Smush Pro active!', 'wp-smushit' ); ?>
			</p>
		</div>
		<?php
		delete_site_option( 'smush_deactivated' );
	}

	/**
	 * Show notice when Smush Pro is installed only with a key.
	 */
	public function smush_dash_required() {
		if ( WP_Smush::is_pro() || ! is_super_admin() || ( class_exists( 'WPMUDEV_Dashboard' ) && WPMUDEV_Dashboard::$api->has_key() ) ) {
			return;
		}

		// Do not show on free versions of the plugin.
		if ( false !== strpos( WP_SMUSH_DIR, 'wp-smushit' ) ) {
			return;
		}

		$function = is_multisite() ? 'network_admin_url' : 'admin_url';

		$url = wp_nonce_url(
			$function( 'update.php?action=install-plugin&plugin=install_wpmudev_dash' ),
			'install-plugin_install_wpmudev_dash'
		);
		?>
		<div class="notice smush-notice">
			<div class="smush-notice-logo"><span></span></div>
			<div class="smush-notice-message">
				<?php esc_html_e( 'Smush Pro requires the WPMU DEV Dashboard plugin to unlock pro features. Please make sure you have installed, activated and logged into the Dashboard.', 'wp-smushit' ); ?>
			</div>
			<div class="smush-notice-cta">
				<?php if ( class_exists( 'WPMUDEV_Dashboard' ) && ! WPMUDEV_Dashboard::$api->has_key() ) : ?>
					<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=wpmudev' ) ); ?>" class="smush-notice-act button-primary" target="_blank">
						<?php esc_html_e( 'Log In', 'wp-smushit' ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( $url ); ?>" class="smush-notice-act button-primary">
						<?php esc_html_e( 'Install Plugin', 'wp-smushit' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add Share UI Class.
	 *
	 * @param string $classes  Classes string.
	 *
	 * @return string
	 */
	public function smush_body_classes( $classes ) {
		// Exit if function doesn't exists.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $classes;
		}

		$current_screen = get_current_screen();

		// If not on plugin page.
		if ( ! in_array( $current_screen->id, Admin::$plugin_pages, true ) ) {
			return $classes;
		}

		// Remove old wpmud class from body of smush page to avoid style conflict.
		$classes = str_replace( 'wpmud ', '', $classes );

		$classes .= ' ' . WP_SHARED_UI_VERSION;

		return $classes;
	}

	/**
	 * Allows to register meta boxes for the page.
	 *
	 * @since 2.9.0
	 */
	public function register_meta_boxes() {}

	/**
	 * Add meta box.
	 *
	 * @param string   $id               Meta box ID.
	 * @param string   $title            Meta box title.
	 * @param callable $callback         Callback for meta box content.
	 * @param callable $callback_header  Callback for meta box header.
	 * @param callable $callback_footer  Callback for meta box footer.
	 * @param string   $context          Meta box context.
	 * @param array    $args             Arguments.
	 */
	public function add_meta_box( $id, $title, $callback = null, $callback_header = null, $callback_footer = null, $context = 'main', $args = array() ) {
		$default_args = array(
			'box_class'         => 'sui-box',
			'box_header_class'  => 'sui-box-header',
			'box_content_class' => 'sui-box-body',
			'box_footer_class'  => 'sui-box-footer',
		);

		$args = wp_parse_args( $args, $default_args );

		if ( ! isset( $this->meta_boxes[ $this->slug ] ) ) {
			$this->meta_boxes[ $this->slug ] = array();
		}

		if ( ! isset( $this->meta_boxes[ $this->slug ][ $context ] ) ) {
			$this->meta_boxes[ $this->slug ][ $context ] = array();
		}

		if ( ! isset( $this->meta_boxes[ $this->slug ][ $context ] ) ) {
			$this->meta_boxes[ $this->slug ][ $context ] = array();
		}

		$meta_box = array(
			'id'              => $id,
			'title'           => $title,
			'callback'        => $callback,
			'callback_header' => $callback_header,
			'callback_footer' => $callback_footer,
			'args'            => $args,
		);

		if ( $meta_box ) {
			$this->meta_boxes[ $this->slug ][ $context ][ $id ] = $meta_box;
		}
	}

	/**
	 * Render the page
	 */
	public function render() {
		// Shared UI wrapper with accessible color option.
		$classes = $this->settings->get( 'accessible_colors' ) ? 'sui-wrap sui-color-accessible' : 'sui-wrap';
		echo '<div class="' . esc_attr( $classes ) . '">';

		// Load page header.
		$this->render_page_header();
		$this->add_update_dialog();

		$hide_quick_setup = false !== get_option( 'skip-smush-setup' );

		// Show configure screen for only a new installation and for only network admins.
		if ( ( ! is_multisite() && ! $hide_quick_setup ) || ( is_multisite() && ! is_network_admin() && ! $this->settings->is_network_enabled() && ! $hide_quick_setup ) ) {
			$this->view( 'onboarding', array(), 'modals' );
			$this->view( 'checking-files', array(), 'modals' );
		}

		$this->render_inner_content();

		// Nonce field.
		wp_nonce_field( 'save_wp_smush_options', 'wp_smush_options_nonce', '' );

		// Close shared ui wrapper.
		echo '</div>';
	}

	/**
	 * Show an update dialog.
	 *
	 * @since 3.3.2
	 */
	private function add_update_dialog() {
		$show_modal = get_site_transient( 'wp-smush-update-modal' );
		if ( ! $show_modal ) {
			return;
		}

		delete_site_transient( 'wp-smush-update-modal' );

		$this->view( 'resizing-update', array(), 'modals' );
		?>
		<script>
			window.addEventListener('load', function() {
				SUI.dialogs['resizing-update'].show();
			});
		</script>
		<?php
	}

	/**
	 * Get the current screen tab
	 *
	 * @return string
	 */
	public function get_current_tab() {
		$tabs = $this->get_tabs();
		$view = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_STRING );

		if ( array_key_exists( $view, $tabs ) ) {
			return $view;
		}

		if ( empty( $tabs ) ) {
			return false;
		}

		reset( $tabs );
		return key( $tabs );
	}

	/**
	 * Display tabs navigation
	 */
	public function show_tabs() {
		$this->view(
			'tabs',
			array(
				'tabs' => $this->get_tabs(),
			)
		);
	}

	/**
	 * Get a tab URL
	 *
	 * @param string $tab  Tab ID.
	 *
	 * @return string
	 */
	public function get_tab_url( $tab ) {
		$tabs = $this->get_tabs();
		if ( ! isset( $tabs[ $tab ] ) ) {
			return '';
		}

		if ( is_multisite() && is_network_admin() ) {
			return network_admin_url( 'admin.php?page=' . $this->slug . '&view=' . $tab );
		} else {
			return admin_url( 'admin.php?page=' . $this->slug . '&view=' . $tab );
		}
	}

	/**
	 * Get the list of tabs for this screen
	 *
	 * @return array
	 */
	protected function get_tabs() {
		return apply_filters( 'wp_smush_admin_page_tabs_' . $this->slug, $this->tabs );
	}

	/**
	 * Render inner content.
	 */
	protected function render_inner_content() {
		$this->view( $this->slug . '-page' );
	}

	/**
	 * Render meta box.
	 *
	 * @param string $context  Meta box context. Default: main.
	 */
	protected function do_meta_boxes( $context = 'main' ) {
		if ( empty( $this->meta_boxes[ $this->slug ][ $context ] ) ) {
			return;
		}

		do_action_ref_array( 'wp_smush_admin_do_meta_boxes_' . $this->slug, array( &$this ) );

		foreach ( $this->meta_boxes[ $this->slug ][ $context ] as $id => $box ) {
			$args = array(
				'title'           => $box['title'],
				'id'              => $id,
				'callback'        => $box['callback'],
				'callback_header' => $box['callback_header'],
				'callback_footer' => $box['callback_footer'],
				'args'            => $box['args'],
			);
			$this->view( 'meta-box', $args );
		}
	}

	/**
	 * Check if view exists.
	 *
	 * @param string $name  View name = file name.
	 *
	 * @return bool
	 */
	protected function view_exists( $name ) {
		$file = WP_SMUSH_DIR . "app/views/{$name}.php";
		return is_file( $file );
	}

	/**
	 * Smush icon svg image
	 *
	 * @return string
	 */
	private function get_menu_icon() {
		ob_start();
		?>
		<svg width="16px" height="16px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			<g transform="translate(-12.000000, -428.000000)" style="fill: #83878b;">
				<path d="M26.9310561,432.026782 C27.2629305,432.598346 27.5228884,433.217017 27.7109375,433.882812 C27.9036468,434.565108 28,435.27083 28,436 C28,437.104172 27.7916687,438.14062 27.375,439.109375 C26.9479145,440.07813 26.3750036,440.924476 25.65625,441.648438 C24.9374964,442.372399 24.0937548,442.942706 23.125,443.359375 C22.1562452,443.78646 21.1197972,444 20.015625,444 L26.9310562,432.026782 L26.9310561,432.026782 Z M26.9310561,432.026782 C26.9228316,432.012617 26.9145629,431.998482 26.90625,431.984375 L26.9375,432.015625 L26.9310562,432.026782 L26.9310561,432.026782 Z M16.625,433.171875 L23.375,433.171875 L20,439.03125 L16.625,433.171875 Z M14.046875,430.671875 L14.046875,430.65625 C14.4114602,430.249998 14.8177061,429.88021 15.265625,429.546875 C15.7031272,429.223957 16.1744766,428.945314 16.6796875,428.710938 C17.1848984,428.476561 17.7187472,428.296876 18.28125,428.171875 C18.8333361,428.046874 19.406247,427.984375 20,427.984375 C20.593753,427.984375 21.1666639,428.046874 21.71875,428.171875 C22.2812528,428.296876 22.8151016,428.476561 23.3203125,428.710938 C23.8255234,428.945314 24.3020811,429.223957 24.75,429.546875 C25.1875022,429.88021 25.5937481,430.255206 25.96875,430.671875 L14.046875,430.671875 Z M13.0625,432.03125 L19.984375,444 C18.8802028,444 17.8437548,443.78646 16.875,443.359375 C15.9062452,442.942706 15.0625036,442.372399 14.34375,441.648438 C13.6249964,440.924476 13.0572937,440.07813 12.640625,439.109375 C12.2239563,438.14062 12.015625,437.104172 12.015625,436 C12.015625,435.27083 12.1067699,434.567712 12.2890625,433.890625 C12.4713551,433.213538 12.729165,432.593753 13.0625,432.03125 Z" id="icon-smush"></path>
			</g>
		</svg>
		<?php
		$svg = ob_get_clean();

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Prints out the page header for bulk smush page.
	 *
	 * @return void
	 */
	private function render_page_header() {
		$current_screen = get_current_screen();
		?>

		<div class="sui-header wp-smush-page-header">
			<h1 class="sui-header-title"><?php esc_html_e( 'DASHBOARD', 'wp-smushit' ); ?></h1>
			<div class="sui-actions-right">
				<?php if ( ! is_network_admin() && ( 'bulk' === $this->get_current_tab() || 'gallery_page_wp-smush-nextgen-bulk' === $this->page_id ) ) : ?>
					<?php $data_type = 'gallery_page_wp-smush-nextgen-bulk' === $current_screen->id ? 'nextgen' : 'media'; ?>
					<button class="sui-button wp-smush-scan" data-tooltip="<?php esc_attr_e( 'Lets you check if any images can be further optimized. Useful after changing settings.', 'wp-smushit' ); ?>" data-type="<?php echo esc_attr( $data_type ); ?>">
						<i class="sui-icon-update" aria-hidden="true"></i>
						<?php esc_html_e( 'Re-Check Images', 'wp-smushit' ); ?>
					</button>
				<?php endif; ?>
				<?php if ( ! apply_filters( 'wpmudev_branding_hide_doc_link', false ) ) : ?>
					<?php
					$doc = 'https://premium.wpmudev.org/project/wp-smush-pro/#wpmud-hg-project-documentation';
					if ( WP_Smush::is_pro() ) {
						$doc = 'https://premium.wpmudev.org/docs/wpmu-dev-plugins/smush/?utm_source=smush&utm_medium=plugin&utm_campaign=smush_pluginlist_docs';
					}
					?>
					<a href="<?php echo esc_url( $doc ); ?>>" class="sui-button sui-button-ghost" target="_blank">
						<i class="sui-icon-academy" aria-hidden="true"></i> <?php esc_html_e( 'Documentation', 'wp-smushit' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="sui-notice sui-notice-top sui-hidden" id="wp-smush-ajax-notice"></div>

		<?php
		// User API check, and display a message if not valid.
		WP_Smush::get_instance()->admin()->get_user_validation_message();

		// Re-check images notice.
		$this->get_recheck_message();

		// Check and show missing directory smush table error only on main site.
		if ( Dir::should_continue() ) {
			$this->show_table_error();
		}

		// Check for any stored API message and show it.
		$this->show_api_message();

		$this->settings_updated();

		do_action( 'wp_smush_header_notices' );
	}

	/**
	 * Get re-check notice after settings update.
	 */
	private function get_recheck_message() {
		// Return if not multisite, or on network settings page, Netowrkwide settings is disabled.
		if ( ! is_multisite() || is_network_admin() || ! Settings::can_access( 'bulk' ) ) {
			return;
		}

		// Check the last settings stored in db.
		$run_recheck = $this->settings->get_setting( WP_SMUSH_PREFIX . 'run_recheck', false );

		// If not same, display notice.
		if ( ! $run_recheck ) {
			return;
		}
		?>
		<div class="sui-notice sui-notice-success wp-smush-re-check-message">
			<p><?php esc_html_e( 'Smush settings were updated, performing a quick scan to check if any of the images need to be Smushed again.', 'wp-smushit' ); ?></p>
			<span class="sui-notice-dismiss"><a href="#"><?php esc_html_e( 'Dismiss', 'wp-smushit' ); ?></a></span>
		</div>
		<?php
	}

	/**
	 * Display a admin notice on smush screen if the custom table wasn't created
	 */
	private function show_table_error() {
		$current_screen = get_current_screen();
		if ( 'toplevel_page_smush' !== $current_screen->id && 'toplevel_page_smush-network' !== $current_screen->id ) {
			return;
		}

		if ( ! Dir::table_exist() ) { // Display a notice.
			?>
			<div class="sui-notice sui-notice-warning missing_table">
				<p>
					<?php esc_html_e( 'Directory smushing requires custom tables and it seems there was an error creating tables. For help, please contact our team on the support forums', 'wp-smushit' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Display a stored API message.
	 */
	private function show_api_message() {
		// Do not show message for any other users.
		if ( ! is_network_admin() && ! is_super_admin() ) {
			return;
		}

		$api_message = get_site_option( WP_SMUSH_PREFIX . 'api_message', array() );
		$api_message = current( $api_message );

		// Return if the API message is not set or user dismissed it earlier.
		if ( empty( $api_message ) || ! is_array( $api_message ) || 'show' !== $api_message['status'] ) {
			return;
		}

		$message      = empty( $api_message['message'] ) ? '' : $api_message['message'];
		$message_type = ( is_array( $api_message ) && ! empty( $api_message['type'] ) ) ? $api_message['type'] : 'info';
		$type_class   = 'warning' === $message_type ? 'sui-notice-warning' : 'sui-notice-info';
		?>

		<div class="sui-notice wp-smush-api-message <?php echo esc_attr( $type_class ); ?>">
			<p><?php echo wp_kses_post( $message ); ?></p>
			<span class="sui-notice-dismiss">
				<a href="#"><?php esc_html_e( 'Dismiss', 'wp-smushit' ); ?></a>
			</span>
		</div>
		<?php
	}

	/**
	 * Displays a admin notice for settings update.
	 */
	private function settings_updated() {
		// Check if network-wide settings are enabled, do not show settings updated message.
		if ( is_multisite() && ! is_network_admin() && ! Settings::can_access( 'bulk' ) ) {
			return;
		}

		// Show settings saved message.
		if ( ! get_option( WP_SMUSH_PREFIX . 'settings_updated' ) ) {
			return;
		}

		$core = WP_Smush::get_instance()->core();

		// Default message.
		$message = esc_html__( 'Your settings have been updated!', 'wp-smushit' );
		// Notice class.
		$message_class = ' sui-notice-success';


		if ( 'cdn' === $this->get_current_tab() ) {
			$cdn = $this->settings->get_setting( WP_SMUSH_PREFIX . 'cdn_status' );
			if ( isset( $cdn->cdn_enabling ) && $cdn->cdn_enabling ) {
				$message = esc_html__( 'Your settings have been saved and changes are now propagating to the CDN. Changes can take up to 30 minutes to take effect but your images will continue to be served in the mean time, please be patient.', 'wp-smushit' );
			}
		}

		// Additional message if we got work to do!
		$resmush_count = is_array( $core->resmush_ids ) && count( $core->resmush_ids ) > 0;
		$smush_count   = is_array( $core->remaining_count ) && $core->remaining_count > 0;

		if ( $smush_count || $resmush_count ) {
			$message_class = ' sui-notice-warning';
			// Show link to bulk smush tab from other tabs.
			$bulk_smush_link = 'bulk' === $this->get_current_tab() ? '<a href="#" class="wp-smush-trigger-bulk">' : '<a href="' . $this->get_page_url() . '">';
			/* translators: %1$s - <a>, %2$s - </a> */
			$message .= ' ' . sprintf( esc_html__( 'You have images that need smushing. %1$sBulk smush now!%2$s', 'wp-smushit' ), $bulk_smush_link, '</a>' );
		}

		$this->view(
			'notice',
			array(
				'classes' => $message_class,
				'message' => $message,
			),
			'common'
		);

		// Remove the option.
		$this->settings->delete_setting( WP_SMUSH_PREFIX . 'settings_updated' );
	}

	/**
	 * Add more pages to builtin wpmudev branding.
	 *
	 * @since 3.0
	 *
	 * @param array $plugin_pages  Nextgen pages is not introduced in built in wpmudev branding.
	 *
	 * @return array
	 */
	public function builtin_wpmudev_branding( $plugin_pages ) {
		$plugin_pages['gallery_page_wp-smush-nextgen-bulk'] = array(
			'wpmudev_whitelabel_sui_plugins_branding',
			'wpmudev_whitelabel_sui_plugins_footer',
			'wpmudev_whitelabel_sui_plugins_doc_links',
		);

		return $plugin_pages;
	}

	/**
	 * Check if the page should be rendered.
	 *
	 * @since 3.2.2
	 *
	 * @return bool
	 */
	public function should_render() {
		// Render all pages on single site installs.
		if ( ! is_multisite() ) {
			return true;
		}

		$access = get_site_option( WP_SMUSH_PREFIX . 'networkwide' );

		if ( ! $access || 'directory' === $this->get_current_tab() ) {
			return is_network_admin() ? true : false;
		}

		if ( '1' === $access ) {
			return is_network_admin() ? false : true;
		}

		if ( is_array( $access ) ) {
			if ( is_network_admin() && ! in_array( $this->get_current_tab(), $access, true ) ) {
				return true;
			}

			if ( ! is_network_admin() && in_array( $this->get_current_tab(), $access, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return this menu page URL
	 *
	 * @since 3.5.0
	 *
	 * @return string
	 */
	public function get_page_url() {
		if ( is_multisite() && is_network_admin() ) {
			global $_parent_pages;

			if ( isset( $_parent_pages[ $this->slug ] ) ) {
				$parent_slug = $_parent_pages[ $this->slug ];
				if ( $parent_slug && ! isset( $_parent_pages[ $parent_slug ] ) ) {
					$url = network_admin_url( add_query_arg( 'page', $this->slug, $parent_slug ) );
				} else {
					$url = network_admin_url( 'admin.php?page=' . $this->slug );
				}
			} else {
				$url = '';
			}

			$url = esc_url( $url );

			return $url;
		} else {
			return menu_page_url( $this->slug, false );
		}
	}

}
