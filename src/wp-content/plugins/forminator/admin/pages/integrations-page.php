<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Integrations_Page
 *
 * @since 1.1
 */
class Forminator_Integrations_Page extends Forminator_Admin_Page {

	/**
	 * Addon list as array
	 *
	 * @var array
	 */
	public $addons_list = array();

	/**
	 * @var array
	 */
	public $addons_list_grouped_by_connected = array();

	public $addon_nonce = '';

	private $addon_page = array();

	public static $addon_nonce_page_action = 'forminator_addon_nonce_page';

	/**
	 * Executed Action before render the page
	 *
	 * @since 1.1
	 * @since 1.2 add extra section for addon
	 */
	public function before_render() {
		// cleanup addons on integrations page
		Forminator_Addon_Loader::get_instance()->cleanup_activated_addons();

		$this->addons_list                      = forminator_get_registered_addons_list();
		$this->addons_list_grouped_by_connected = forminator_get_registered_addons_grouped_by_connected();

		Forminator_Addon_Admin_Ajax::get_instance()->generate_nonce();
		$this->addon_nonce = Forminator_Addon_Admin_Ajax::get_instance()->get_nonce();
		add_filter( 'forminator_data', array( $this, 'add_addons_js_data' ) );

		$this->validate_addon_page();
	}

	public function add_addons_js_data( $data ) {
		if ( Forminator::is_addons_feature_enabled() ) {
			$data['addons']      = forminator_get_registered_addons_list();
			$data['addon_nonce'] = $this->addon_nonce;
		}

		return $data;
	}

	/**
	 * Render custom output of addon when validated
	 *
	 * @since 1.2
	 */
	protected function render_page_content() {
		if ( ! empty( $this->addon_page ) ) {
			// html output here are expected
			echo $this->addon_page['output'];// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			parent::render_page_content();
		}

	}

	/**
	 * Render Addon title as header on addon page
	 *
	 * @since 1.2
	 */
	public function render_header() {
		if ( ! empty( $this->addon_page ) ) {
			?>
			<header class="sui-header">
				<h1 class="sui-header-title"><?php echo esc_html( $this->addon_page['title'] ); ?></h1>
				<div class="sui-actions-right">
					<?php if ( forminator_is_show_documentation_link() ) : ?>
						<a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/forminator/#integrations" target="_blank" class="sui-button sui-button-ghost">
							<i class="sui-icon-academy"></i> <?php esc_html_e( 'View Documentation', Forminator::DOMAIN ); ?>
						</a>
					<?php endif; ?>
				</div>
			</header>
			<?php
		} else {
			parent::render_header();
		}
	}

	/**
	 * Nonce generation for addon page
	 *
	 * @since 1.2
	 * @return string
	 */
	public static function get_addon_page_nonce() {
		return wp_create_nonce( self::$addon_nonce_page_action );
	}

	/**
	 * Validate required query arg for displaying addon page
	 *
	 * @since 1.2
	 *
	 * @return bool
	 */
	public function validate_addon_page() {
		if ( isset( $_GET['nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['nonce'], self::$addon_nonce_page_action ) ) {
				return false;
			}
		}
		$query_args = $_GET;
		// main component
		/**
		 * - slug : addon slug (eg. trello)
		 * - nonce: nonce validation
		 * - section: callback
		 */
		if ( isset( $query_args['nonce'] ) ) {
			unset( $query_args['nonce'] );
		}
		if ( ! isset( $query_args['slug'] ) ) {
			return false;
		}
		$slug = $query_args['slug'];
		unset( $query_args['slug'] );
		$addon = forminator_get_addon( $slug );
		if ( ! $addon ) {
			return false;
		}

		if ( ! isset( $query_args['section'] ) ) {
			return false;
		}
		$section = $query_args['section'];
		unset( $query_args['section'] );
		$callback = $addon->get_integration_section_callback( $section );// returned callback must be an array

		if ( ! is_array( $callback ) ) {
			return false;
		}

		if ( ! is_callable( $callback ) ) {
			return false;
		}

		forminator_maybe_attach_addon_hook( $addon );

		$output = call_user_func( $callback, $query_args );

		$this->addon_page = array(
			'title'  => $addon->get_title(),
			'output' => $output,
		);

		return true;
	}
}
