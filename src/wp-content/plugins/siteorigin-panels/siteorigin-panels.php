<?php
/*
Plugin Name: Page Builder by SiteOrigin
Plugin URI: http://siteorigin.com/page-builder/
Description: A drag and drop, responsive page builder that simplifies building your website.
Version: 1.4.12
Author: Greg Priday
Author URI: http://siteorigin.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Donate link: http://siteorigin.com/page-builder/donate/
*/

define('SITEORIGIN_PANELS_VERSION', '1.4.12');
define('SITEORIGIN_PANELS_BASE_FILE', __FILE__);

include plugin_dir_path(__FILE__) . 'widgets/basic.php';

include plugin_dir_path(__FILE__) . 'inc/options.php';
include plugin_dir_path(__FILE__) . 'inc/revisions.php';
include plugin_dir_path(__FILE__) . 'inc/copy.php';
include plugin_dir_path(__FILE__) . 'inc/styles.php';
include plugin_dir_path(__FILE__) . 'inc/legacy.php';
include plugin_dir_path(__FILE__) . 'inc/notice.php';

if( defined('SITEORIGIN_PANELS_DEV') && SITEORIGIN_PANELS_DEV ) include plugin_dir_path(__FILE__).'inc/debug.php';

/**
 * Hook for activation of Page Builder.
 */
function siteorigin_panels_activate(){
	add_option('siteorigin_panels_initial_version', SITEORIGIN_PANELS_VERSION, '', 'no');
}
register_activation_hook(__FILE__, 'siteorigin_panels_activate');

/**
 * Initialize the Page Builder.
 */
function siteorigin_panels_init(){
	$display_settings = get_option('siteorigin_panels_display', array());
	if( isset($display_settings['bundled-widgets'] ) && !$display_settings['bundled-widgets'] ) return;

	if( !defined('SITEORIGIN_PANELS_LEGACY_WIDGETS_ACTIVE') && ( !is_admin() || basename($_SERVER["SCRIPT_FILENAME"]) != 'plugins.php') ) {
		// Include the bundled widgets if the Legacy Widgets plugin isn't active.
		include plugin_dir_path(__FILE__).'widgets/widgets.php';
	}
}
add_action('plugins_loaded', 'siteorigin_panels_init');

/**
 * Initialize the language files
 */
function siteorigin_panels_init_lang(){
	load_plugin_textdomain('siteorigin-panels', false, dirname( plugin_basename( __FILE__ ) ). '/lang/');
}
add_action('plugins_loaded', 'siteorigin_panels_init_lang');

/**
 * Add the admin menu entries
 */
function siteorigin_panels_admin_menu(){
	if( !siteorigin_panels_setting( 'home-page' ) ) return;

	add_theme_page(
		__( 'Custom Home Page Builder', 'siteorigin-panels' ),
		__( 'Home Page', 'siteorigin-panels' ),
		'edit_theme_options',
		'so_panels_home_page',
		'siteorigin_panels_render_admin_home_page'
	);
}
add_action('admin_menu', 'siteorigin_panels_admin_menu');

/**
 * Render the page used to build the custom home page.
 */
function siteorigin_panels_render_admin_home_page(){
	add_meta_box( 'so-panels-panels', __( 'Page Builder', 'siteorigin-panels' ), 'siteorigin_panels_metabox_render', 'appearance_page_so_panels_home_page', 'advanced', 'high' );
	include plugin_dir_path(__FILE__).'tpl/admin-home-page.php';
}

/**
 * Callback to register the Page Builder Metaboxes
 */
function siteorigin_panels_metaboxes() {
	foreach( siteorigin_panels_setting( 'post-types' ) as $type ){
		add_meta_box( 'so-panels-panels', __( 'Page Builder', 'siteorigin-panels' ), 'siteorigin_panels_metabox_render', $type, 'advanced', 'high' );
	}
}
add_action( 'add_meta_boxes', 'siteorigin_panels_metaboxes' );

/**
 * Save home page
 */
function siteorigin_panels_save_home_page(){
	if(!isset($_POST['_sopanels_home_nonce']) || !wp_verify_nonce($_POST['_sopanels_home_nonce'], 'save')) return;
	if ( empty($_POST['panels_js_complete']) ) return;
	if(!current_user_can('edit_theme_options')) return;

	update_option('siteorigin_panels_home_page', siteorigin_panels_get_panels_data_from_post( $_POST ) );
	update_option('siteorigin_panels_home_page_enabled', $_POST['siteorigin_panels_home_enabled'] == 'true' ? true : '');

	// If we've enabled the panels home page, change show_on_front to posts, this is required for the home page to work properly
	if( $_POST['siteorigin_panels_home_enabled'] == 'true' ) update_option( 'show_on_front', 'posts' );
}
add_action('admin_init', 'siteorigin_panels_save_home_page');

/**
 * Modify the front page template
 *
 * @param $template
 * @return string
 */
function siteorigin_panels_filter_home_template($template){
	if(
		!get_option('siteorigin_panels_home_page_enabled', siteorigin_panels_setting('home-page-default') )
		|| !siteorigin_panels_setting('home-page')
	) return $template;

	$GLOBALS['siteorigin_panels_is_panels_home'] = true;
	return locate_template(array(
		'home-panels.php',
		$template
	));
}
add_filter('home_template', 'siteorigin_panels_filter_home_template');

/**
 * If this is the main query, store that we're accessing the front page
 * @param $wp_query
 */
function siteorigin_panels_render_home_page_prepare($wp_query) {
	if ( !$wp_query->is_main_query() ) return;
	if ( !get_option('siteorigin_panels_home_page_enabled', siteorigin_panels_setting('home-page-default') ) ) return;

	$GLOBALS['siteorigin_panels_is_home'] = @ $wp_query->is_front_page();
}
add_action('pre_get_posts', 'siteorigin_panels_render_home_page_prepare');

/**
 * This fixes a rare case where pagination for a home page loop extends further than post pagination.
 */
function siteorigin_panels_render_home_page(){
	if (
		empty($GLOBALS['siteorigin_panels_is_home']) ||
		! is_404() ||
		! get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting('home-page-default') )
	) return;

	// This query was for the home page, but because of pagination we're getting a 404
	// Create a fake query so the home page keeps working with the post loop widget
	$paged = get_query_var('paged');
	if( empty($paged) ) return;

	query_posts(array());
	set_query_var('paged', $paged);

	// Make this query the main one
	$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
	status_header(200); // Overwrite the 404 header we set earlier.
}
add_action('template_redirect', 'siteorigin_panels_render_home_page');

/**
 * @return mixed|void Are we currently viewing the home page
 */
function siteorigin_panels_is_home(){
	$home = (is_home() && get_option( 'siteorigin_panels_home_page_enabled', siteorigin_panels_setting('home-page-default' ) ) );
	return apply_filters('siteorigin_panels_is_home', $home);
}

/**
 * Disable home page panels when we change show_on_front to something other than posts.
 *
 * @param $old
 * @param $new
 *
 * @action update_option_show_on_front
 */
function siteorigin_panels_disable_on_front_page_change($old, $new){
	if($new != 'posts'){
		// Disable panels home page
		update_option('siteorigin_panels_home_page_enabled', '');
	}
}
add_action('update_option_show_on_front', 'siteorigin_panels_disable_on_front_page_change', 10, 2);


/**
 * Check if we're currently viewing a panel.
 *
 * @param bool $can_edit Also check if the user can edit this page
 * @return bool
 */
function siteorigin_panels_is_panel($can_edit = false){
	// Check if this is a panel
	$is_panel =  ( siteorigin_panels_is_home() || ( is_singular() && get_post_meta(get_the_ID(), 'panels_data', false) != '' ) );
	return $is_panel && (!$can_edit || ( (is_singular() && current_user_can('edit_post', get_the_ID())) || ( siteorigin_panels_is_home() && current_user_can('edit_theme_options') ) ));
}

/**
 * Render a panel metabox.
 *
 * @param $post
 */
function siteorigin_panels_metabox_render( $post ) {
	include plugin_dir_path(__FILE__) . 'tpl/metabox-panels.php';
}


/**
 * Enqueue the panels admin scripts
 *
 * @action admin_print_scripts-post-new.php
 * @action admin_print_scripts-post.php
 * @action admin_print_scripts-appearance_page_so_panels_home_page
 */
function siteorigin_panels_admin_enqueue_scripts($prefix) {
	$screen = get_current_screen();

	if ( ( $screen->base == 'post' && in_array( $screen->id, siteorigin_panels_setting('post-types') ) ) || $screen->base == 'appearance_page_so_panels_home_page') {
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-button' );

		wp_enqueue_script( 'so-undomanager', plugin_dir_url(__FILE__) . 'js/undomanager.min.js', array( ), 'fb30d7f', true );
		wp_enqueue_script( 'so-panels-chosen', plugin_dir_url(__FILE__) . 'js/chosen/chosen.jquery.min.js', array( 'jquery' ), SITEORIGIN_PANELS_VERSION, true );

		wp_enqueue_script( 'so-panels-admin', plugin_dir_url(__FILE__) . 'js/panels.admin.min.js', array( 'jquery' ), SITEORIGIN_PANELS_VERSION, true );
		wp_enqueue_script( 'so-panels-admin-panels', plugin_dir_url(__FILE__) . 'js/panels.admin.panels.min.js', array( 'jquery' ), SITEORIGIN_PANELS_VERSION, true );
		wp_enqueue_script( 'so-panels-admin-grid', plugin_dir_url(__FILE__) . 'js/panels.admin.grid.min.js', array( 'jquery' ), SITEORIGIN_PANELS_VERSION, true );
		wp_enqueue_script( 'so-panels-admin-prebuilt', plugin_dir_url(__FILE__) . 'js/panels.admin.prebuilt.min.js', array( 'jquery' ), SITEORIGIN_PANELS_VERSION, true );
		wp_enqueue_script( 'so-panels-admin-tooltip', plugin_dir_url(__FILE__) . 'js/panels.admin.tooltip.min.js', array( 'jquery' ), SITEORIGIN_PANELS_VERSION, true );
		wp_enqueue_script( 'so-panels-admin-media', plugin_dir_url(__FILE__) . 'js/panels.admin.media.min.js', array( 'jquery' ), SITEORIGIN_PANELS_VERSION, true );
		wp_enqueue_script( 'so-panels-admin-styles', plugin_dir_url(__FILE__) . 'js/panels.admin.styles.min.js', array( 'jquery' ), SITEORIGIN_PANELS_VERSION, true );

		wp_localize_script( 'so-panels-admin', 'panels', array(
			'previewUrl' => wp_nonce_url(add_query_arg('siteorigin_panels_preview', 'true', get_home_url()), 'siteorigin-panels-preview'),
			'i10n' => array(
				'buttons' => array(
					'insert' => __( 'Insert', 'siteorigin-panels' ),
					'cancel' => __( 'cancel', 'siteorigin-panels' ),
					'delete' => __( 'Delete', 'siteorigin-panels' ),
					'duplicate' => __( 'Duplicate', 'siteorigin-panels' ),
					'edit' => __( 'Edit', 'siteorigin-panels' ),
					'done' => __( 'Done', 'siteorigin-panels' ),
					'undo' => __( 'Undo', 'siteorigin-panels' ),
					'add' => __( 'Add', 'siteorigin-panels' ),
				),
				'messages' => array(
					'deleteColumns' => __( 'Columns deleted', 'siteorigin-panels' ),
					'deleteWidget' => __( 'Widget deleted', 'siteorigin-panels' ),
					'confirmLayout' => __( 'Are you sure you want to load this layout? It will overwrite your current page.', 'siteorigin-panels' ),
					'editWidget' => __('Edit %s Widget', 'siteorigin-panels')
				),
			),
		) );

		$panels_data = siteorigin_panels_get_current_admin_panels_data();
		if( !empty( $panels_data['widgets'] ) ) {
			wp_localize_script( 'so-panels-admin', 'panelsData', $panels_data );
		}

		// Let themes and plugins give names and descriptions to missing widgets.
		global $wp_widget_factory;
		$missing_widgets = array();
		if ( !empty( $panels_data['widgets'] ) ) {
			foreach ( $panels_data['widgets'] as $i => $widget ) {

				// There's a chance the widget was activated by siteorigin_panels_widget_is_missing
				if ( empty( $wp_widget_factory->widgets[ $widget['info']['class'] ] ) ) {
					$missing_widgets[$widget['info']['class']] = apply_filters('siteorigin_panels_missing_widget_data', array(
						'title' => str_replace( '_', ' ', $widget['info']['class'] ),
						'description' => __('Install the missing widget', 'siteorigin-panels'),
					), $widget['info']['class']);
				}
			}
		}

		if( !empty($missing_widgets) ) {
			wp_localize_script( 'so-panels-admin', 'panelsMissingWidgets', $missing_widgets );
		}

		// Set up the row styles
		wp_localize_script( 'so-panels-admin', 'panelsStyleFields', siteorigin_panels_style_get_fields() );
		if( siteorigin_panels_style_is_using_color() ) {
			wp_enqueue_script( 'wp-color-picker');
			wp_enqueue_style( 'wp-color-picker' );
		}

		// Render all the widget forms. A lot of widgets use this as a chance to enqueue their scripts
		$original_post = isset($GLOBALS['post']) ? $GLOBALS['post'] : null; // Make sure widgets don't change the global post.
		foreach($GLOBALS['wp_widget_factory']->widgets as $class => $widget_obj){
			ob_start();
			$widget_obj->form( array() );
			ob_clean();
		}
		$GLOBALS['post'] = $original_post;

		// This gives panels a chance to enqueue scripts too, without having to check the screen ID.
		do_action( 'siteorigin_panel_enqueue_admin_scripts' );
		do_action( 'sidebar_admin_setup' );
	}
}
add_action( 'admin_print_scripts-post-new.php', 'siteorigin_panels_admin_enqueue_scripts' );
add_action( 'admin_print_scripts-post.php', 'siteorigin_panels_admin_enqueue_scripts' );
add_action( 'admin_print_scripts-appearance_page_so_panels_home_page', 'siteorigin_panels_admin_enqueue_scripts' );


/**
 * Enqueue the admin panel styles
 *
 * @action admin_print_styles-post-new.php
 * @action admin_print_styles-post.php
 */
function siteorigin_panels_admin_enqueue_styles() {
	$screen = get_current_screen();
	if ( in_array( $screen->id, siteorigin_panels_setting('post-types') ) || $screen->base == 'appearance_page_so_panels_home_page') {
		wp_enqueue_style( 'so-panels-admin', plugin_dir_url(__FILE__) . 'css/admin.css', array( ), SITEORIGIN_PANELS_VERSION );

		global $wp_version;
		if( version_compare( $wp_version, '3.9.beta.1', '<' ) ) {
			// Versions before 3.9 need some custom jQuery UI styling
			wp_enqueue_style( 'so-panels-admin-jquery-ui', plugin_dir_url(__FILE__) . 'css/jquery-ui.css', array(), SITEORIGIN_PANELS_VERSION );
		}
		else {
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
		}

		wp_enqueue_style( 'so-panels-chosen', plugin_dir_url(__FILE__) . 'js/chosen/chosen.css', array(), SITEORIGIN_PANELS_VERSION );
		do_action( 'siteorigin_panel_enqueue_admin_styles' );
	}
}
add_action( 'admin_print_styles-post-new.php', 'siteorigin_panels_admin_enqueue_styles' );
add_action( 'admin_print_styles-post.php', 'siteorigin_panels_admin_enqueue_styles' );
add_action( 'admin_print_styles-appearance_page_so_panels_home_page', 'siteorigin_panels_admin_enqueue_styles' );

/**
 * Add a help tab to pages with panels.
 */
function siteorigin_panels_add_help_tab($prefix) {
	$screen = get_current_screen();
	if(
		( $screen->base == 'post' && ( in_array( $screen->id, siteorigin_panels_setting( 'post-types' ) ) || $screen->id == '') )
		|| ($screen->id == 'appearance_page_so_panels_home_page')
	) {
		$screen->add_help_tab( array(
			'id' => 'panels-help-tab', //unique id for the tab
			'title' => __( 'Page Builder', 'siteorigin-panels' ), //unique visible title for the tab
			'callback' => 'siteorigin_panels_add_help_tab_content'
		) );
	}
}
add_action('load-page.php', 'siteorigin_panels_add_help_tab', 12);
add_action('load-post-new.php', 'siteorigin_panels_add_help_tab', 12);
add_action('load-appearance_page_so_panels_home_page', 'siteorigin_panels_add_help_tab', 12);

/**
 * Display the content for the help tab.
 */
function siteorigin_panels_add_help_tab_content(){
	include plugin_dir_path(__FILE__) . 'tpl/help.php';
}

/**
 * Save the panels data
 *
 * @param $post_id
 * @param $post
 *
 * @action save_post
 */
function siteorigin_panels_save_post( $post_id, $post ) {
	if ( empty( $_POST['_sopanels_nonce'] ) || !wp_verify_nonce( $_POST['_sopanels_nonce'], 'save' ) ) return;
	if ( empty($_POST['panels_js_complete']) ) return;
	if ( !current_user_can( 'edit_post', $post_id ) ) return;

	$panels_data = siteorigin_panels_get_panels_data_from_post( $_POST );
	if( function_exists('wp_slash') ) $panels_data = wp_slash($panels_data);
	update_post_meta( $post_id, 'panels_data', $panels_data );
}
add_action( 'save_post', 'siteorigin_panels_save_post', 10, 2 );

/**
 * Get the home page panels layout data.
 *
 * @return mixed|void
 */
function siteorigin_panels_get_home_page_data(){
	$panels_data = get_option('siteorigin_panels_home_page', null);
	if( is_null( $panels_data ) ){
		// Load the default layout
		$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array() );
		$panels_data = !empty($layouts['default_home']) ? $layouts['default_home'] : current($layouts);
	}

	return $panels_data;
}

/**
 * Get the Page Builder data for the current admin page.
 *
 * @return array
 */
function siteorigin_panels_get_current_admin_panels_data(){
	$screen = get_current_screen();

	// Localize the panels with the panels data
	if($screen->base == 'appearance_page_so_panels_home_page'){
		$panels_data = get_option('siteorigin_panels_home_page', null);
		if( is_null( $panels_data ) ){
			// Load the default layout
			$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array() );

			$home_name = siteorigin_panels_setting('home-page-default') ? siteorigin_panels_setting('home-page-default') : 'home';
			$panels_data = !empty($layouts[$home_name]) ? $layouts[$home_name] : current($layouts);
		}
		$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, 'home');
	}
	else{
		global $post;
		$panels_data = get_post_meta( $post->ID, 'panels_data', true );
		$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, $post->ID );
	}

	if ( empty( $panels_data ) ) $panels_data = array();

	return $panels_data;
}

/**
 * Echo the CSS for the current panel
 *
 * @action init
 */
function siteorigin_panels_css() {
	if(!isset($_GET['post']) || !isset($_GET['ver'])) return;

	if($_GET['post'] == 'home') $panels_data = siteorigin_panels_get_home_page_data();
	else $panels_data = get_post_meta( $_GET['post'], 'panels_data', true );
	$post_id = $_GET['post'];

	header("Content-type: text/css");
	echo siteorigin_panels_generate_css($_GET['post'], $panels_data);
	exit();
}
add_action( 'wp_ajax_siteorigin_panels_post_css', 'siteorigin_panels_css' );
add_action( 'wp_ajax_nopriv_siteorigin_panels_post_css', 'siteorigin_panels_css' );

/**
 * Generate the CSS for the page layout.
 *
 * @param $post_id
 * @param $panels_data
 * @return string
 */
function siteorigin_panels_generate_css($post_id, $panels_data){
	// Exit if we don't have panels data
	if ( empty( $panels_data ) || empty( $panels_data['grids'] ) ) return;

	$settings = siteorigin_panels_setting();

	$panels_mobile_width = $settings['mobile-width'];
	$panels_margin_bottom = $settings['margin-bottom'];

	$css = array();
	$css[1920] = array();
	$css[ $panels_mobile_width ] = array(); // This is a mobile resolution

	// Add the grid sizing
	$ci = 0;
	foreach ( $panels_data['grids'] as $gi => $grid ) {
		$cell_count = intval( $grid['cells'] );
		for ( $i = 0; $i < $cell_count; $i++ ) {
			$cell = $panels_data['grid_cells'][$ci++];

			if ( $cell_count > 1 ) {
				$css_new = 'width:' . round( $cell['weight'] * 100, 3 ) . '%';
				if ( empty( $css[1920][$css_new] ) ) $css[1920][$css_new] = array();
				$css[1920][$css_new][] = '#pgc-' . $post_id . '-' . $gi  . '-' . $i;
			}
		}

		// Add the bottom margin to any grids that aren't the last
		if($gi != count($panels_data['grids'])-1){
			$css[1920]['margin-bottom: '.$panels_margin_bottom.'px'][] = '#pg-' . $post_id . '-' . $gi;
		}

		if ( $cell_count > 1 ) {
			if ( empty( $css[1920]['float:left'] ) ) $css[1920]['float:left'] = array();
			$css[1920]['float:left'][] = '#pg-' . $post_id . '-' . $gi . ' .panel-grid-cell';
		}

		if ( $settings['responsive'] ) {
			// Mobile Responsive
			$mobile_css = array( 'float:none', 'width:auto' );
			foreach ( $mobile_css as $c ) {
				if ( empty( $css[ $panels_mobile_width ][ $c ] ) ) $css[ $panels_mobile_width ][ $c ] = array();
				$css[ $panels_mobile_width ][ $c ][] = '#pg-' . $post_id . '-' . $gi . ' .panel-grid-cell';
			}

			for ( $i = 0; $i < $cell_count; $i++ ) {
				if ( $i != $cell_count - 1 ) {
					$css_new = 'margin-bottom:' . $panels_margin_bottom . 'px';
					if ( empty( $css[$panels_mobile_width][$css_new] ) ) $css[$panels_mobile_width][$css_new] = array();
					$css[$panels_mobile_width][$css_new][] = '#pgc-' . $post_id . '-' . $gi . '-' . $i;
				}
			}
		}
	}

	if( $settings['responsive'] ) {
		// Add CSS to prevent overflow on mobile resolution.
		$panel_grid_css = 'margin-left: 0 !important; margin-right: 0 !important;';
		$panel_grid_cell_css = 'padding: 0 !important;';
		if(empty($css[ $panels_mobile_width ][ $panel_grid_css ])) $css[ $panels_mobile_width ][ $panel_grid_css ] = array();
		if(empty($css[ $panels_mobile_width ][ $panel_grid_cell_css ])) $css[ $panels_mobile_width ][ $panel_grid_cell_css ] = array();
		$css[ $panels_mobile_width ][ $panel_grid_css ][] = '.panel-grid';
		$css[ $panels_mobile_width ][ $panel_grid_cell_css ][] = '.panel-grid-cell';
	}

	// Add the bottom margin
	$bottom_margin = 'margin-bottom: '.$panels_margin_bottom.'px';
	$bottom_margin_last = 'margin-bottom: 0 !important';
	if(empty($css[ 1920 ][ $bottom_margin ])) $css[ 1920 ][ $bottom_margin ] = array();
	if(empty($css[ 1920 ][ $bottom_margin_last ])) $css[ 1920 ][ $bottom_margin_last ] = array();
	$css[ 1920 ][ $bottom_margin ][] = '.panel-grid-cell .panel';
	$css[ 1920 ][ $bottom_margin_last ][] = '.panel-grid-cell .panel:last-child';

	// This is for the side margins
	$magin_half = $settings['margin-sides']/2;
	$side_margins = "margin: 0 -{$magin_half}px 0 -{$magin_half}px";
	$side_paddings = "padding: 0 {$magin_half}px 0 {$magin_half}px";
	if(empty($css[ 1920 ][ $side_margins ])) $css[ 1920 ][ $side_margins ] = array();
	if(empty($css[ 1920 ][ $side_paddings ])) $css[ 1920 ][ $side_paddings ] = array();
	$css[ 1920 ][ $side_margins ][] = '.panel-grid';
	$css[ 1920 ][ $side_paddings ][] = '.panel-grid-cell';

	// Filter the unprocessed CSS array
	$css = apply_filters( 'siteorigin_panels_css', $css );

	// Build the CSS
	$css_text = '';
	krsort( $css );
	foreach ( $css as $res => $def ) {
		if ( empty( $def ) ) continue;

		if ( $res < 1920 ) {
			$css_text .= '@media (max-width:' . $res . 'px)';
			$css_text .= ' { ';
		}

		foreach ( $def as $property => $selector ) {
			$selector = array_unique( $selector );
			$css_text .= implode( ' , ', $selector ) . ' { ' . $property . ' } ';
		}

		if ( $res < 1920 ) $css_text .= ' } ';
	}

	return $css_text;
}

/**
 * Prepare the panels data early so widgets can enqueue their scripts and styles for the header.
 */
function siteorigin_panels_prepare_home_content( ) {
	if( siteorigin_panels_is_home() ) {
		global $siteorigin_panels_cache;
		if(empty($siteorigin_panels_cache)) $siteorigin_panels_cache = array();
		$siteorigin_panels_cache['home'] = siteorigin_panels_render( 'home' );
	}
}
add_action('wp_enqueue_scripts', 'siteorigin_panels_prepare_home_content', 11);

/**
 * Prepare the content of the page early on so widgets can enqueue their scripts and styles
 */
function siteorigin_panels_prepare_single_post_content(){
	if( is_singular() ) {
		global $siteorigin_panels_cache;
		if( empty($siteorigin_panels_cache[ get_the_ID() ] ) ) {
			$siteorigin_panels_cache[ get_the_ID() ] = siteorigin_panels_render( get_the_ID() );
		}
	}
}
add_action('wp_enqueue_scripts', 'siteorigin_panels_prepare_single_post_content');

/**
 * Filter the content of the panel, adding all the widgets.
 *
 * @param $content
 * @return string
 *
 * @filter the_content
 */
function siteorigin_panels_filter_content( $content ) {
	global $post;

	if ( empty( $post ) ) return $content;
	if ( in_array( $post->post_type, siteorigin_panels_setting('post-types') ) ) {
		$panel_content = siteorigin_panels_render( $post->ID );

		if ( !empty( $panel_content ) ) $content = $panel_content;
	}

	return $content;
}
add_filter( 'the_content', 'siteorigin_panels_filter_content' );


/**
 * Render the panels
 *
 * @param int|string|bool $post_id The Post ID or 'home'.
 * @param bool $enqueue_css Should we also enqueue the layout CSS.
 * @param array|bool $panels_data Existing panels data. By default load from settings or post meta.
 * @return string
 */
function siteorigin_panels_render( $post_id = false, $enqueue_css = true, $panels_data = false ) {
	if( empty($post_id) ) $post_id = get_the_ID();

	global $siteorigin_panels_current_post;
	$old_current_post = $siteorigin_panels_current_post;
	$siteorigin_panels_current_post = $post_id;

	// Try get the cached panel from in memory cache.
	global $siteorigin_panels_cache;
	if(!empty($siteorigin_panels_cache) && !empty($siteorigin_panels_cache[$post_id]))
		return $siteorigin_panels_cache[$post_id];

	if( empty($panels_data) ) {
		if($post_id == 'home'){
			$panels_data = get_option( 'siteorigin_panels_home_page', get_theme_mod('panels_home_page', null) );

			if( is_null($panels_data) ){
				// Load the default layout
				$layouts = apply_filters('siteorigin_panels_prebuilt_layouts', array());
				$panels_data = !empty($layouts['home']) ? $layouts['home'] : current($layouts);
			}
		}
		else{
			if ( post_password_required($post_id) ) return false;
			$panels_data = get_post_meta( $post_id, 'panels_data', true );
		}
	}

	$panels_data = apply_filters( 'siteorigin_panels_data', $panels_data, $post_id );
	if( empty( $panels_data ) || empty( $panels_data['grids'] ) ) return '';

	// Create the skeleton of the grids
	$grids = array();
	if( !empty( $panels_data['grids'] ) && !empty( $panels_data['grids'] ) ) {
		foreach ( $panels_data['grids'] as $gi => $grid ) {
			$gi = intval( $gi );
			$grids[$gi] = array();
			for ( $i = 0; $i < $grid['cells']; $i++ ) {
				$grids[$gi][$i] = array();
			}
		}
	}

	if( !empty( $panels_data['widgets'] ) && is_array($panels_data['widgets']) ){
		foreach ( $panels_data['widgets'] as $widget ) {
			$grids[intval( $widget['info']['grid'] )][intval( $widget['info']['cell'] )][] = $widget;
		}
	}

	ob_start();

	global $siteorigin_panels_inline_css;
	if(empty($siteorigin_panels_inline_css)) $siteorigin_panels_inline_css = '';

	if($enqueue_css) {
		if( siteorigin_panels_setting('inline-css') ) {
			wp_enqueue_style('siteorigin-panels-front');
			$siteorigin_panels_inline_css .= siteorigin_panels_generate_css($post_id, $panels_data);
		}
		else {
			// This is the CSS for the page layout.
			wp_enqueue_style(
				'siteorigin-panels-post-css-'.$post_id,
				add_query_arg(
					array(
						'action' => 'siteorigin_panels_post_css',
						'post' => $post_id,
						// Include this to ensure changes don't get cached by the browser
						'layout' => substr( md5( serialize( $panels_data ) ), 0, 8)
					),
					admin_url('admin-ajax.php')
				),
				array( 'siteorigin-panels-front' ),
				SITEORIGIN_PANELS_VERSION
			);
		}
	}

	foreach ( $grids as $gi => $cells ) {

		// This allows other themes and plugins to add html before the row
		echo apply_filters( 'siteorigin_panels_before_row', '', $panels_data['grids'][$gi] );

		$grid_classes = apply_filters( 'siteorigin_panels_row_classes', array('panel-grid'), $panels_data['grids'][$gi] );
		$grid_attributes = apply_filters( 'siteorigin_panels_row_attributes', array(
			'class' => implode( ' ', $grid_classes ),
			'id' => 'pg-' . $post_id . '-' . $gi
		), $panels_data['grids'][$gi] );

		echo '<div ';
		foreach ( $grid_attributes as $name => $value ) {
			echo $name.'="'.esc_attr($value).'" ';
		}
		echo '>';

		$style_attributes = array();
		if( !empty( $panels_data['grids'][$gi]['style']['class'] ) ) {
			$style_attributes['class'] = array('panel-row-style-'.$panels_data['grids'][$gi]['style']['class']);
		}

		// Themes can add their own attributes to the style wrapper
		$style_attributes = apply_filters('siteorigin_panels_row_style_attributes', $style_attributes, !empty($panels_data['grids'][$gi]['style']) ? $panels_data['grids'][$gi]['style'] : array());
		if( !empty($style_attributes) ) {
			if(empty($style_attributes['class'])) $style_attributes['class'] = array();
			$style_attributes['class'][] = 'panel-row-style';
			$style_attributes['class'] = array_unique( $style_attributes['class'] );

			echo '<div ';
			foreach ( $style_attributes as $name => $value ) {
				if(is_array($value)) {
					echo $name.'="'.esc_attr( implode( " ", array_unique( $value ) ) ).'" ';
				}
				else {
					echo $name.'="'.esc_attr($value).'" ';
				}
			}
			echo '>';
		}

		foreach ( $cells as $ci => $widgets ) {
			// Themes can add their own styles to cells
			$cell_classes = apply_filters( 'siteorigin_panels_row_cell_classes', array('panel-grid-cell'), $panels_data );
			$cell_attributes = apply_filters( 'siteorigin_panels_row_cell_attributes', array(
				'class' => implode( ' ', $cell_classes ),
				'id' => 'pgc-' . $post_id . '-' . $gi  . '-' . $ci
			), $panels_data );

			echo '<div ';
			foreach ( $cell_attributes as $name => $value ) {
				echo $name.'="'.esc_attr($value).'" ';
			}
			echo '>';

			foreach ( $widgets as $pi => $widget_info ) {
				$data = $widget_info;
				unset( $data['info'] );

				siteorigin_panels_the_widget( $widget_info['info']['class'], $data, $gi, $ci, $pi, $pi == 0, $pi == count( $widgets ) - 1, $post_id );
			}
			if ( empty( $widgets ) ) echo '&nbsp;';
			echo '</div>';
		}
		echo '</div>';

		if( !empty($style_attributes) ) {
			echo '</div>';
		}

		// This allows other themes and plugins to add html after the row
		echo apply_filters( 'siteorigin_panels_after_row', '', $panels_data['grids'][$gi] );
	}

	$html = ob_get_clean();

	// Reset the current post
	$siteorigin_panels_current_post = $old_current_post;

	return apply_filters( 'siteorigin_panels_render', $html, $post_id, !empty($post) ? $post : null );
}

/**
 * Print inline CSS in the header and footer.
 */
function siteorigin_panels_print_inline_css(){
	global $siteorigin_panels_inline_css;

	if(!empty($siteorigin_panels_inline_css)) {
		?><style type="text/css" media="all"><?php echo $siteorigin_panels_inline_css ?></style><?php
	}

	$siteorigin_panels_inline_css = '';
}
add_action('wp_head', 'siteorigin_panels_print_inline_css', 12);
add_action('wp_footer', 'siteorigin_panels_print_inline_css');

/**
 * Render the widget.
 *
 * @param string $widget The widget class name.
 * @param array $instance The widget instance
 * @param int $grid The grid number.
 * @param int $cell The cell number.
 * @param int $panel the panel number.
 * @param bool $is_first Is this the first widget in the cell.
 * @param bool $is_last Is this the last widget in the cell.
 * @param bool $post_id
 */
function siteorigin_panels_the_widget( $widget, $instance, $grid, $cell, $panel, $is_first, $is_last, $post_id = false ) {

	global $wp_widget_factory;

	// Load the widget from the widget factory and give plugins a chance to provide their own
	$the_widget = !empty($wp_widget_factory->widgets[$widget]) ? $wp_widget_factory->widgets[$widget] : false;
	$the_widget = apply_filters( 'siteorigin_panels_widget_object', $the_widget, $widget );

	if( empty($post_id) ) $post_id = get_the_ID();

	$classes = array( 'panel', 'widget' );
	if ( !empty( $the_widget ) && !empty( $the_widget->id_base ) ) $classes[] = 'widget_' . $the_widget->id_base;
	if ( $is_first ) $classes[] = 'panel-first-child';
	if ( $is_last ) $classes[] = 'panel-last-child';
	$id = 'panel-' . $post_id . '-' . $grid . '-' . $cell . '-' . $panel;

	$args = array(
		'before_widget' => '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" id="' . $id . '">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
		'widget_id' => 'widget-' . $grid . '-' . $cell . '-' . $panel
	);

	if ( !empty($the_widget) && is_a($the_widget, 'WP_Widget')  ) {
		$the_widget->widget($args , $instance );
	}
	else {
		// This gives themes a chance to display some sort of placeholder for missing widgets
		echo apply_filters('siteorigin_panels_missing_widget', '', $widget, $args , $instance);
	}
}

/**
 * Add the Edit Home Page item to the admin bar.
 *
 * @param WP_Admin_Bar $admin_bar
 * @return WP_Admin_Bar
 */
function siteorigin_panels_admin_bar_menu($admin_bar){
	/**
	 * @var WP_Query $wp_query
	 */
	global $wp_query;

	if( ( $wp_query->is_home() && $wp_query->is_main_query() ) || siteorigin_panels_is_home() ){
		// Check that we support the home page
		if ( !siteorigin_panels_setting('home-page') || !current_user_can('edit_theme_options') ) return $admin_bar;
		if( !get_option('siteorigin_panels_home_page_enabled', siteorigin_panels_setting('home-page-default') ) ) return $admin_bar;

		$admin_bar->add_node(array(
			'id' => 'edit-home-page',
			'title' => __('Edit Home Page', 'siteorigin-panels'),
			'href' => admin_url('themes.php?page=so_panels_home_page')
		));
	}

	return $admin_bar;
}
add_action('admin_bar_menu', 'siteorigin_panels_admin_bar_menu', 100);

/**
 * Handles creating the preview.
 */
function siteorigin_panels_preview(){
	if(isset($_GET['siteorigin_panels_preview']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'siteorigin-panels-preview')){
		global $siteorigin_panels_is_preview;
		$siteorigin_panels_is_preview = true;
		// Set the panels home state to true
		if(empty($_POST['post_id'])) $GLOBALS['siteorigin_panels_is_panels_home'] = true;
		add_action('option_siteorigin_panels_home_page', 'siteorigin_panels_preview_load_data');
		locate_template(siteorigin_panels_setting('home-template'), true);
		exit();
	}
}
add_action('template_redirect', 'siteorigin_panels_preview');

/**
 * Is this a preview.
 *
 * @return bool
 */
function siteorigin_panels_is_preview(){
	global $siteorigin_panels_is_preview;
	return (bool) $siteorigin_panels_is_preview;
}

/**
 * Hide the admin bar for panels previews.
 *
 * @param $show
 * @return bool
 */
function siteorigin_panels_preview_adminbar($show){
	if(!$show) return false;
	return !(isset($_GET['siteorigin_panels_preview']) && wp_verify_nonce($_GET['_wpnonce'], 'siteorigin-panels-preview'));
}
add_filter('show_admin_bar', 'siteorigin_panels_preview_adminbar');

/**
 * This is a way to show previews of panels, especially for the home page.
 *
 * @param $val
 * @return array
 */
function siteorigin_panels_preview_load_data($val){
	if(isset($_GET['siteorigin_panels_preview'])){
		$val = siteorigin_panels_get_panels_data_from_post( $_POST );
	}

	return $val;
}

/**
 * Add all the necessary body classes.
 *
 * @param $classes
 * @return array
 */
function siteorigin_panels_body_class($classes){
	if(siteorigin_panels_is_panel()) $classes[] = 'siteorigin-panels';
	if(siteorigin_panels_is_home()) $classes[] = 'siteorigin-panels-home';

	if(isset($_GET['siteorigin_panels_preview']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'siteorigin-panels-preview')) {
		// This is a home page preview
		$classes[] = 'siteorigin-panels';
		$classes[] = 'siteorigin-panels-home';
	}

	return $classes;
}
add_filter('body_class', 'siteorigin_panels_body_class');

/**
 * Enqueue the required styles
 */
function siteorigin_panels_enqueue_styles(){
	wp_register_style('siteorigin-panels-front', plugin_dir_url(__FILE__) . 'css/front.css', array(), SITEORIGIN_PANELS_VERSION );
}
add_action('wp_enqueue_scripts', 'siteorigin_panels_enqueue_styles', 1);

/**
 * Add current pages as cloneable pages
 *
 * @param $layouts
 * @return mixed
 */
function siteorigin_panels_cloned_page_layouts($layouts){
	$pages = get_posts( array(
		'post_type' => 'page',
		'post_status' => array('publish', 'draft'),
		'numberposts' => 200,
	) );

	foreach($pages as $page){
		$panels_data = get_post_meta( $page->ID, 'panels_data', true );

		if( empty($panels_data) ) continue;

		$name =  empty($page->post_title) ? __('Untitled', 'siteorigin-panels') : $page->post_title;
		if($page->post_status != 'publish') $name .= ' ( ' . __('Unpublished', 'siteorigin-panels') . ' )';

		if(current_user_can('edit_post', $page->ID)) {
			$layouts['post-'.$page->ID] = wp_parse_args(
				array(
					'name' => sprintf(__('Clone Page: %s', 'siteorigin-panels'), $name )
				),
				$panels_data
			);
		}
	}

	// Include the current home page in the clone pages.
	$home_data = get_option('siteorigin_panels_home_page', null);
	if ( !empty($home_data) ) {

		$layouts['current-home-page'] = wp_parse_args(
			array(
				'name' => __('Clone: Current Home Page', 'siteorigin-panels'),
			),
			$home_data
		);
	}

	return $layouts;
}
add_filter('siteorigin_panels_prebuilt_layouts', 'siteorigin_panels_cloned_page_layouts', 20);

/**
 * Add a link to recommended plugins and widgets.
 */
function siteorigin_panels_recommended_widgets(){
	// This filter can be used to hide the recommended plugins button.
	if( ! apply_filters('siteorigin_panels_show_recommended', true) || is_multisite() ) return;

	?>
	<p id="so-panels-recommended-plugins">
		<a href="<?php echo admin_url('plugin-install.php?tab=favorites&user=siteorigin-pagebuilder') ?>" target="_blank"><?php _e('Recommended Plugins and Widgets', 'siteorigin-panels') ?></a>
		<small><?php _e('Free plugins that work well with Page Builder', 'siteorigin-panels') ?></small>
	</p>
	<?php
}
add_action('siteorigin_panels_after_widgets', 'siteorigin_panels_recommended_widgets');

/**
 * Add a filter to import panels_data meta key. This fixes serialized PHP.
 */
function siteorigin_panels_wp_import_post_meta($post_meta){
	foreach($post_meta as $i => $meta) {
		if($meta['key'] == 'panels_data') {
			$value = $meta['value'];
			$value = preg_replace("/[\r\n]/", "<<<br>>>", $value);
			$value = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $value);
			$value = unserialize($value);
			$value = array_map('siteorigin_panels_wp_import_post_meta_map', $value);

			$post_meta[$i]['value'] = $value;
		}
	}

	return $post_meta;
}
add_filter('wp_import_post_meta', 'siteorigin_panels_wp_import_post_meta');

/**
 * A callback that replaces temporary break tag with actual line breaks.
 *
 * @param $val
 * @return array|mixed
 */
function siteorigin_panels_wp_import_post_meta_map($val) {
	if(is_string($val)) return str_replace('<<<br>>>', "\n", $val);
	else return array_map('siteorigin_panels_wp_import_post_meta_map', $val);
}

/**
 * Admin ajax handler for loading a prebuilt layout.
 */
function siteorigin_panels_ajax_action_prebuilt(){
	// Get any layouts that the current user could edit.
	$layouts = apply_filters( 'siteorigin_panels_prebuilt_layouts', array() );

	if(empty($_GET['layout'])) exit();
	if(empty($layouts[$_GET['layout']])) exit();

	header('content-type: application/json');

	$layout = !empty($layouts[$_GET['layout']]) ? $layouts[$_GET['layout']] : array();
	$layout = apply_filters('siteorigin_panels_prebuilt_layout', $layout);

	echo json_encode($layout);
	exit();
}
add_action('wp_ajax_so_panels_prebuilt', 'siteorigin_panels_ajax_action_prebuilt');

/**
 * Display a widget form with the provided data
 */
function siteorigin_panels_ajax_widget_form(){
	$request = array_map('stripslashes_deep', $_REQUEST);
	if( empty( $request['widget'] ) ) exit();

	$widget = $request['widget'];
	$instance = !empty($request['instance']) ? json_decode( $request['instance'], true ) : array();

	$form = siteorigin_panels_render_form( $widget, $instance, $_REQUEST['raw'] );
	$form = apply_filters('siteorigin_panels_ajax_widget_form', $form, $widget, $instance);

	echo $form;
	exit();
}
add_action('wp_ajax_so_panels_widget_form', 'siteorigin_panels_ajax_widget_form');

/**
 * Render a form with all the Page Builder specific fields
 *
 * @param string $widget The class of the widget
 * @param array $instance Widget values
 * @param bool $raw
 * @return mixed|string The form
 */
function siteorigin_panels_render_form($widget, $instance = array(), $raw = false){
	global $wp_widget_factory;

	// This is a chance for plugins to replace missing widgets
	$the_widget = !empty($wp_widget_factory->widgets[$widget]) ? $wp_widget_factory->widgets[$widget] : false;
	$the_widget = apply_filters( 'siteorigin_panels_widget_object', $the_widget, $widget );

	if ( empty($the_widget) || !is_a( $the_widget, 'WP_Widget' ) ) {
		// This widget is missing, so show a missing widgets form.
		$form = '<div class="panels-missing-widget-form"><p>' .
			__( 'This widget is not available, please install the missing plugin.', 'siteorigin-panels' ) .
			'</p></div>';

		// Allow other themes and plugins to change the missing widget form
		return apply_filters('siteorigin_panels_missing_widget_form', $form, $widget, $instance);
	}

	if( $raw ) $instance = $the_widget->update($instance, $instance);

	$the_widget->id = 'temp';
	$the_widget->number = '{$id}';

	ob_start();
	$the_widget->form($instance);
	$form = ob_get_clean();

	// Convert the widget field naming into ones that Page Builder uses
	$exp = preg_quote( $the_widget->get_field_name('____') );
	$exp = str_replace('____', '(.*?)', $exp);
	$form = preg_replace( '/'.$exp.'/', 'widgets[{$id}][$1]', $form );

	$form = apply_filters('siteorigin_panels_widget_form', $form, $widget, $instance);

	// Add all the information fields
	return $form;
}

/**
 * Convert form post data into more efficient panels data.
 *
 * @param $form_post
 * @return array
 */
function siteorigin_panels_get_panels_data_from_post($form_post){
	$panels_data = array();
	$panels_data['widgets'] = array_values( stripslashes_deep( isset( $form_post['widgets'] ) ? $form_post['widgets'] : array() ) );

	if ( empty( $panels_data['widgets'] ) ) return array();

	foreach ( $panels_data['widgets'] as $i => $widget ) {

		$info = $widget['info'];
		$widget = json_decode($widget['data'], true);

		if ( class_exists( $info['class'] ) ) {
			$the_widget = new $info['class'];

			if ( method_exists( $the_widget, 'update' ) && !empty($info['raw']) ) {
				$widget = $the_widget->update( $widget, $widget );
			}

			unset($info['raw']);
		}

		$widget['info'] = $info;
		$panels_data['widgets'][$i] = $widget;

	}

	$panels_data['grids'] = array_values( stripslashes_deep( isset( $form_post['grids'] ) ? $form_post['grids'] : array() ) );
	$panels_data['grid_cells'] = array_values( stripslashes_deep( isset( $form_post['grid_cells'] ) ? $form_post['grid_cells'] : array() ) );

	return apply_filters('siteorigin_panels_panels_data_from_post', $panels_data);
}

/**
 * Add action links to the plugin list for Page Builder.
 *
 * @param $links
 * @return array
 */
function siteorigin_panels_plugin_action_links($links) {
	$links[] = '<a href="http://siteorigin.com/threads/plugin-page-builder/">' . __('Support Forum', 'siteorigin-panels') . '</a>';
	$links[] = '<a href="http://siteorigin.com/page-builder/#newsletter">' . __('Newsletter', 'siteorigin-panels') . '</a>';
	return $links;
}
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'siteorigin_panels_plugin_action_links');