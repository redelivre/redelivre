<?php
/*
Plugin Name: Page Builder by SiteOrigin
Plugin URI: https://siteorigin.com/page-builder/
Description: A drag and drop, responsive page builder that simplifies building your website.
Version: 2.5.8
Author: SiteOrigin
Author URI: https://siteorigin.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Donate link: http://siteorigin.com/page-builder/#donate
*/

define( 'SITEORIGIN_PANELS_VERSION', '2.5.8' );
if ( ! defined( 'SITEORIGIN_PANELS_JS_SUFFIX' ) ) {
	define( 'SITEORIGIN_PANELS_JS_SUFFIX', '.min' );
}
define( 'SITEORIGIN_PANELS_VERSION_SUFFIX', '-25' );

require_once plugin_dir_path( __FILE__ ) . 'inc/functions.php';

class SiteOrigin_Panels {

	function __construct() {
		register_activation_hook( __FILE__, array( 'SiteOrigin_Panels', 'activate' ) );

		// Register the autoloader
		spl_autoload_register( array( $this, 'autoloader' ) );

		add_action( 'plugins_loaded', array( $this, 'version_check' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100 );
		
		add_action('widgets_init', array( $this, 'widgets_init' ) );

		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'siteorigin_panels_data', array( $this, 'process_panels_data' ), 5 );
		add_filter( 'siteorigin_panels_widget_class', array( $this, 'fix_namespace_escaping' ), 5 );

		if ( is_admin() ) {
			// Setup all the admin classes
			SiteOrigin_Panels_Settings::single();
			SiteOrigin_Panels_Revisions::single();
			SiteOrigin_Panels_Admin::single();

			if( ! class_exists( 'SiteOrigin_Learn_Dialog' ) ) {
				include plugin_dir_path( __FILE__ ) . 'learn/learn.php';
			}
		}

		// Include the live editor file if we're in live editor mode.
		if ( self::is_live_editor() ) {
			SiteOrigin_Panels_Live_Editor::single();
		}
		
		SiteOrigin_Panels::renderer();
		SiteOrigin_Panels_Styles_Admin::single();

		if( siteorigin_panels_setting( 'bundled-widgets' ) && ! function_exists( 'origin_widgets_init' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'widgets/widgets.php';
		}

		SiteOrigin_Panels_Widget_Shortcode::init();

		if(
			apply_filters( 'siteorigin_panels_use_cached', siteorigin_panels_setting( 'cache-content' ) ) &&
			( siteorigin_panels_setting( 'legacy-layout' ) != 'auto' || ! self::is_legacy_browser() )
		) {
			// We can use the cached content
			SiteOrigin_Panels_Cache_Renderer::single();
			add_filter( 'the_content', array( $this, 'cached_post_content' ), 1 ); // Run early to pretend to be post_content
			add_filter( 'wp_head', array( $this, 'cached_post_css' ) );
			add_filter( 'wp_enqueue_scripts', array( $this, 'cached_post_enqueue' ) );
		}
		else {
			// We need to generate fresh post content
			add_filter( 'the_content', array( $this, 'generate_post_content' ) );
			add_filter( 'wp_enqueue_scripts', array( $this, 'generate_post_css' ) );
		}
		
		define( 'SITEORIGIN_PANELS_BASE_FILE', __FILE__ );
	}


	public static function single() {
		static $single;
		return empty( $single ) ? $single = new self() : $single;
	}
	
	/**
	 * Get an instance of the renderer
	 *
	 * @return SiteOrigin_Panels_Renderer
	 */
	public static function renderer(){
		static $renderer;
		if( empty( $renderer ) ) {
			switch( siteorigin_panels_setting( 'legacy-layout' ) ) {
				case 'always':
					$renderer = SiteOrigin_Panels_Renderer_Legacy::single();
					break;
					
				case 'never':
					$renderer = SiteOrigin_Panels_Renderer::single();
					break;
					
				default :
					$renderer = self::is_legacy_browser() ?
						SiteOrigin_Panels_Renderer_Legacy::single() :
						SiteOrigin_Panels_Renderer::single();
					break;
			}
		}
		
		return $renderer;
	}
	
	public static function is_legacy_browser(){
		$agent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		if( empty( $agent ) ) return false;
		
		return
			// IE lte 10
			( preg_match('/MSIE\s(?P<v>\d+)/i', $agent, $B) && $B['v'] <= 10 ) ||
			// Chrome lte 25
			( preg_match('/Chrome\/(?P<v>\d+)/i', $agent, $B) && $B['v'] <= 25 ) ||
			// Firefox lte 21
			( preg_match('/Firefox\/(?P<v>\d+)/i', $agent, $B) && $B['v'] <= 21 ) ||
			// Safari lte 7
			( preg_match('/Version\/(?P<v>\d+).*?Safari\/\d+/i', $agent, $B) && $B['v'] <= 6 );
	}

	/**
	 * Autoload Page Builder specific classses.
	 *
	 * @param $class
	 */
	public static function autoloader( $class ) {
		$filename = false;
		if ( strpos( $class, 'SiteOrigin_Panels_Widgets_' ) === 0 ) {
			$filename = str_replace( 'SiteOrigin_Panels_Widgets_', '', $class );
			$filename = str_replace( '_', '-', $filename );
			$filename = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $filename ) );
			$filename = plugin_dir_path( __FILE__ ) . 'inc/widgets/' . $filename . '.php';
		}
		else if ( strpos( $class, 'SiteOrigin_Panels_' ) === 0 ) {
			$filename = str_replace( array( 'SiteOrigin_Panels_', '_' ), array( '', '-' ), $class );
			$filename = plugin_dir_path( __FILE__ ) . 'inc/' . strtolower( $filename ) . '.php';
		}
		
		if ( ! empty( $filename ) && file_exists( $filename ) ) {
			include $filename;
		}
	}

	public static function activate() {
		add_option( 'siteorigin_panels_initial_version', SITEORIGIN_PANELS_VERSION, '', 'no' );
	}

	/**
	 * Initialize SiteOrigin Page Builder
	 *
	 * @action plugins_loaded
	 */
	public function init() {
		if (
			! is_admin() &&
			siteorigin_panels_setting( 'sidebars-emulator' ) &&
			( ! get_option( 'permalink_structure' ) || get_option( 'rewrite_rules' ) )
		) {
			// Initialize the sidebars emulator
			SiteOrigin_Panels_Sidebars_Emulator::single();
		}

		// Initialize the language
		load_plugin_textdomain( 'siteorigin-panels', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		// Initialize all the extra classes
		SiteOrigin_Panels_Home::single();

		// Check if we need to initialize the admin class.
		if ( is_admin() ) {
			SiteOrigin_Panels_Admin::single();
		}
	}

	/**
	 * @return mixed|void Are we currently viewing the home page
	 */
	public static function is_home() {
		$home = ( is_front_page() && is_page() && get_option( 'show_on_front' ) == 'page' && get_option( 'page_on_front' ) == get_the_ID() && get_post_meta( get_the_ID(), 'panels_data' ) );

		return apply_filters( 'siteorigin_panels_is_home', $home );
	}

	/**
	 * Check if we're currently viewing a page builder page.
	 *
	 * @param bool $can_edit Also check if the user can edit this page
	 *
	 * @return bool
	 */
	public static function is_panel( $can_edit = false ) {
		// Check if this is a panel
		$is_panel = ( siteorigin_panels_is_home() || ( is_singular() && get_post_meta( get_the_ID(), 'panels_data', false ) ) );

		return $is_panel && ( ! $can_edit || ( ( is_singular() && current_user_can( 'edit_post', get_the_ID() ) ) || ( siteorigin_panels_is_home() && current_user_can( 'edit_theme_options' ) ) ) );
	}

	/**
	 * Check if we're in the Live Editor in the frontend.
	 *
	 * @return bool
	 */
	static function is_live_editor(){
		return ! empty( $_GET['siteorigin_panels_live_editor'] );
	}

	public static function preview_url() {
		global $post, $wp_post_types;

		if (
			empty( $post ) ||
			empty( $wp_post_types ) ||
			empty( $wp_post_types[ $post->post_type ] ) ||
			! $wp_post_types[ $post->post_type ]->public
		) {
			$preview_url = add_query_arg(
				'siteorigin_panels_live_editor',
				'true',
				admin_url( 'admin-ajax.php?action=so_panels_live_editor_preview' )
			);
			$preview_url = wp_nonce_url( $preview_url, 'live-editor-preview', '_panelsnonce' );
		} else {
			$preview_url = add_query_arg( 'siteorigin_panels_live_editor', 'true', set_url_scheme( get_permalink() ) );
		}

		return $preview_url;
	}

	/**
	 * Get the Page Builder data for the home page.
	 *
	 * @return bool|mixed
	 */
	public function get_home_page_data() {
		$page_id = get_option( 'page_on_front' );
		if ( empty( $page_id ) ) {
			$page_id = get_option( 'siteorigin_panels_home_page_id' );
		}
		if ( empty( $page_id ) ) {
			return false;
		}

		$panels_data = get_post_meta( $page_id, 'panels_data', true );
		if ( is_null( $panels_data ) ) {
			// Load the default layout
			$layouts     = apply_filters( 'siteorigin_panels_prebuilt_layouts', array() );
			$panels_data = ! empty( $layouts['default_home'] ) ? $layouts['default_home'] : current( $layouts );
		}

		return $panels_data;
	}

	/**
	 * Generate post content for the current post.
	 *
	 * @param $content
	 *
	 * @return string
	 *
	 * @filter the_content
	 */
	public function generate_post_content( $content ) {
		global $post;
		if ( empty( $post ) && ! in_the_loop() ) {
			return $content;
		}

		if ( ! apply_filters( 'siteorigin_panels_filter_content_enabled', true ) ) {
			return $content;
		}

		// Check if this post has panels_data
		if ( get_post_meta( $post->ID, 'panels_data', true ) ) {
			$panel_content = SiteOrigin_Panels::renderer()->render(
				get_the_ID(),
				// Add CSS if this is not the main single post, this is handled by add_single_css
				get_the_ID() !== get_queried_object_id()
			);

			if ( ! empty( $panel_content ) ) {
				$content = $panel_content;

				if ( ! is_singular() ) {
					// This is an archive page, so try strip out anything after the more text

					if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
						$content = explode( $matches[0], $content, 2 );
						$content = $content[0];
						$content = force_balance_tags( $content );
						if ( ! empty( $matches[1] ) && ! empty( $more_link_text ) ) {
							$more_link_text = strip_tags( wp_kses_no_null( trim( $matches[1] ) ) );
						} else {
							$more_link_text = __( 'Read More', 'siteorigin-panels' );
						}

						$more_link = apply_filters( 'the_content_more_link', ' <a href="' . get_permalink() . "#more-{$post->ID}\" class=\"more-link\">$more_link_text</a>", $more_link_text );
						$content .= '<p>' . $more_link . '</p>';
					}
				}
			}
		}

		return $content;
	}
	
	/**
	 * Generate CSS for the current post
	 */
	public function generate_post_css() {
		if( is_singular() && get_post_meta( get_the_ID(), 'panels_data', true ) ) {
			$renderer = SiteOrigin_Panels::renderer();
			$renderer->add_inline_css( get_the_ID(), $renderer->generate_css( get_the_ID() ) );
		}
	}
	
	/**
	 * Get cached post content for the current post
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function cached_post_content( $content ){
		if( post_password_required( get_the_ID() ) ) {
			// Don't use cache for password protected
			return $this->generate_post_content( $content );
		}
		
		if (
			! in_the_loop() ||
			! apply_filters( 'siteorigin_panels_filter_content_enabled', true ) ||
			! get_post_meta( get_the_ID(), 'panels_data', true )
		) {
			return $content;
		}
		
		$cache = SiteOrigin_Panels_Cache_Renderer::single();
		$html = $cache->get( 'html', get_the_ID() );
		
		return $html;
	}
	
	/**
	 * Add cached CSS for the current post
	 */
	public function cached_post_css(){
		if( post_password_required( get_the_ID() ) ) {
			// Don't use cache for password protected
			return $this->generate_post_css();
		}
		
		if( is_singular() && get_post_meta( get_the_ID(), 'panels_data', true ) ) {
			$cache = SiteOrigin_Panels_Cache_Renderer::single();
			$css = $cache->get( 'css', get_the_ID() );
			SiteOrigin_Panels::renderer()->add_inline_css( get_the_ID(), $css );
		}
	}

	public function cached_post_enqueue(){
		wp_enqueue_style( 'siteorigin-panels-front' );
		wp_enqueue_script( 'siteorigin-panels-front-styles' );
	}

	/**
	 * Add all the necessary body classes.
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	function body_class( $classes ) {
		if( self::is_panel() ) {
			$classes[] = 'siteorigin-panels';
			$classes[] = 'siteorigin-panels-before-js';

			add_action( 'wp_footer', array( $this, 'strip_before_js' ), 99 );
		}
		if( self::is_home() ) $classes[] = 'siteorigin-panels-home';
		if( self::is_live_editor() ) $classes[] = 'siteorigin-panels-live-editor';

		return $classes;
	}

	/**
	 * Add the Edit Home Page item to the admin bar.
	 *
	 * @param WP_Admin_Bar $admin_bar
	 *
	 * @return WP_Admin_Bar
	 */
	function admin_bar_menu( $admin_bar ) {
		// Add the edit home page link
		if (
			siteorigin_panels_setting( 'home-page' ) &&
			current_user_can( 'edit_theme_options' ) &&
			( is_home() || is_front_page() )
		) {
			if ( ( is_page() && get_post_meta( get_the_ID(), 'panels_data', true ) !== '' ) || ! is_page() ) {
				$admin_bar->add_node( array(
					'id'    => 'edit-home-page',
					'title' => __( 'Edit Home Page', 'siteorigin-panels' ),
					'href'  => admin_url( 'themes.php?page=so_panels_home_page' )
				) );

				if ( is_page() ) {
					// Remove the standard edit button
					$admin_bar->remove_node( 'edit' );
				}
			}
		}

		// Add a Live Edit link if this is a Page Builder page that the user can edit
		if (
			siteorigin_panels_setting( 'live-editor-quick-link' ) &&
			is_singular() &&
			current_user_can( 'edit_post', get_the_ID() ) &&
			get_post_meta( get_the_ID(), 'panels_data', true )
		) {
			$admin_bar->add_node( array(
				'id'    => 'so_live_editor',
				'title' => __( 'Live Editor', 'siteorigin-panels' ),
				'href'  => add_query_arg( 'so_live_editor', 1, get_edit_post_link( get_the_ID() ) ),
				'meta'  => array(
					'class' => 'live-edit-page'
				)
			) );

			add_action( 'wp_enqueue_scripts', array( $this, 'live_edit_link_style' ) );
		}

		return $admin_bar;
	}
	
	function widgets_init(){
		register_widget( 'SiteOrigin_Panels_Widgets_PostContent' );
		register_widget( 'SiteOrigin_Panels_Widgets_PostLoop' );
		register_widget( 'SiteOrigin_Panels_Widgets_Layout' );
	}

	function live_edit_link_style() {
		if ( is_singular() && current_user_can( 'edit_post', get_the_ID() ) && get_post_meta( get_the_ID(), 'panels_data', true ) ) {
			// Add the style for the eye icon before the Live Editor link
			$css = '#wpadminbar #wp-admin-bar-so_live_editor > .ab-item:before {
			    content: "\f177";
			    top: 2px;
			}';
			wp_add_inline_style( 'siteorigin-panels-front', $css );
		}
	}

	/**
	 * Process panels data to make sure everything is properly formatted
	 *
	 * @param array $panels_data
	 *
	 * @return array
	 */
	function process_panels_data( $panels_data ) {

		// Process all widgets to make sure that panels_info is properly represented
		if ( ! empty( $panels_data['widgets'] ) && is_array( $panels_data['widgets'] ) ) {

			$last_gi = 0;
			$last_ci = 0;
			$last_wi = 0;

			foreach ( $panels_data['widgets'] as &$widget ) {
				// Transfer legacy content
				if ( empty( $widget['panels_info'] ) && ! empty( $widget['info'] ) ) {
					$widget['panels_info'] = $widget['info'];
					unset( $widget['info'] );
				}

				// Filter the widgets to add indexes
				if ( $widget['panels_info']['grid'] != $last_gi ) {
					$last_gi = $widget['panels_info']['grid'];
					$last_ci = $widget['panels_info']['cell'];
					$last_wi = 0;
				} elseif ( $widget['panels_info']['cell'] != $last_ci ) {
					$last_ci = $widget['panels_info']['cell'];
					$last_wi = 0;
				}
				$widget['panels_info']['cell_index'] = $last_wi ++;
			}

			foreach ( $panels_data['grids'] as &$grid ) {
				if ( ! empty( $grid['style'] ) && is_string( $grid['style'] ) ) {
					$grid['style'] = array();
				}
			}
		}

		return $panels_data;
	}
	
	/**
	 * Fix class names that have been incorrectly escaped
	 *
	 * @param $class
	 *
	 * @return mixed
	 */
	public function fix_namespace_escaping( $class ){
		return preg_replace( '/\\\\+/', '\\', $class );
	}

	public static function front_css_url(){
		return self::renderer()->front_css_url();
	}

	/**
	 * Trigger a siteorigin_panels_version_changed action if the version has changed
	 */
	public function version_check(){
		$active_version = get_option( 'siteorigin_panels_active_version', false );
		if( empty( $active_version ) || $active_version !== SITEORIGIN_PANELS_VERSION ) {
			do_action( 'siteorigin_panels_version_changed' );
			update_option( 'siteorigin_panels_active_version', SITEORIGIN_PANELS_VERSION );
		}
	}

	static function display_learn_button() {
		return siteorigin_panels_setting( 'display-learn' ) &&
		       apply_filters( 'siteorigin_panels_learn', true );
	}

	/**
	 * Script that removes the siteorigin-panels-before-js class from the body.
	 */
	public function strip_before_js(){
		?><script type="text/javascript">document.body.className = document.body.className.replace("siteorigin-panels-before-js","");</script><?php
	}
	
	/**
	 * Should we display premium addon messages
	 *
	 * @return bool
	 */
	public static function display_premium_teaser(){
		return siteorigin_panels_setting( 'display-teaser' ) &&
			   apply_filters( 'siteorigin_premium_upgrade_teaser', true ) &&
			   ! defined( 'SITEORIGIN_PREMIUM_VERSION' );
	}
	
	/**
	 * Get the premium upgrade URL
	 *
	 * @return string
	 */
	public static function premium_url() {
		$ref = apply_filters( 'siteorigin_premium_affiliate_id', '' );
		$url = 'https://siteorigin.com/downloads/premium/?featured_plugin=siteorigin-panels';
		if( $ref ) {
			$url = add_query_arg( 'ref', urlencode( $ref ), $url );
		}
		return $url;
	}
}

SiteOrigin_Panels::single();
