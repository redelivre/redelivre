<?php
/*
Plugin Name: OPML Importer
Plugin URI: http://wordpress.org/extend/plugins/opml-importer/
Description: Import links in OPML format.
Author: wordpressdotorg
Author URI: http://wordpress.org/
Version: 0.2
Stable tag: 0.2
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if ( !defined('WP_LOAD_IMPORTERS') )
	return;

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( !class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require_once $class_wp_importer;
}

/** Load WordPress Administration Bootstrap */
$parent_file = 'tools.php';
$submenu_file = 'import.php';
$title = __('Import Blogroll', 'opml-importer');

/**
 * OPML Importer
 *
 * @package WordPress
 * @subpackage Importer
 */
if ( class_exists( 'WP_Importer' ) ) {
class OPML_Import extends WP_Importer {

	function dispatch() {
		global $wpdb, $user_ID;
$step = isset( $_POST['step'] ) ? $_POST['step'] : 0;

switch ($step) {
	case 0: {
		include_once( ABSPATH . 'wp-admin/admin-header.php' );
		if ( !current_user_can('manage_links') )
			wp_die(__('Cheatin&#8217; uh?', 'opml-importer'));

		$opmltype = 'blogrolling'; // default.
?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php _e('Import your blogroll from another system', 'opml-importer') ?> </h2>
<form enctype="multipart/form-data" action="admin.php?import=opml" method="post" name="blogroll">
<?php wp_nonce_field('import-bookmarks') ?>

<p><?php _e('If a program or website you use allows you to export your links or subscriptions as OPML you may import them here.', 'opml-importer'); ?></p>
<div style="width: 70%; margin: auto; height: 8em;">
<input type="hidden" name="step" value="1" />
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo wp_max_upload_size(); ?>" />
<div style="width: 48%;" class="alignleft">
<h3><label for="opml_url"><?php _e('Specify an OPML URL:', 'opml-importer'); ?></label></h3>
<input type="text" name="opml_url" id="opml_url" size="50" class="code" style="width: 90%;" value="http://" />
</div>

<div style="width: 48%;" class="alignleft">
<h3><label for="userfile"><?php _e('Or choose from your local disk:', 'opml-importer'); ?></label></h3>
<input id="userfile" name="userfile" type="file" size="30" />
</div>

</div>

<p style="clear: both; margin-top: 1em;"><label for="cat_id"><?php _e('Now select a category you want to put these links in.', 'opml-importer') ?></label><br />
<?php _e('Category:', 'opml-importer') ?> <select name="cat_id" id="cat_id">
<?php
$categories = get_terms('link_category', array('get' => 'all'));
foreach ($categories as $category) {
?>
<option value="<?php echo $category->term_id; ?>"><?php echo esc_html(apply_filters('link_category', $category->name)); ?></option>
<?php
} // end foreach
?>
</select></p>

<p class="submit"><input type="submit" name="submit" value="<?php esc_attr_e('Import OPML File', 'opml-importer') ?>" /></p>
</form>

</div>
<?php
		break;
	} // end case 0

	case 1: {
		check_admin_referer('import-bookmarks');

		include_once( ABSPATH . 'wp-admin/admin-header.php' );
		if ( !current_user_can('manage_links') )
			wp_die(__('Cheatin&#8217; uh?', 'opml-importer'));
?>
<div class="wrap">

<h2><?php _e('Importing...', 'opml-importer') ?></h2>
<?php
		$cat_id = abs( (int) $_POST['cat_id'] );
		if ( $cat_id < 1 )
			$cat_id  = 1;

		$opml_url = $_POST['opml_url'];
		if ( isset($opml_url) && $opml_url != '' && $opml_url != 'http://' ) {
			$blogrolling = true;
		} else { // try to get the upload file.
			$overrides = array('test_form' => false, 'test_type' => false);
			$_FILES['userfile']['name'] .= '.txt';
			$file = wp_handle_upload($_FILES['userfile'], $overrides);

			if ( isset($file['error']) )
				wp_die($file['error']);

			$url = $file['url'];
			$opml_url = $file['file'];
			$blogrolling = false;
		}

		global $opml, $updated_timestamp, $all_links, $map, $names, $urls, $targets, $descriptions, $feeds;
		if ( isset($opml_url) && $opml_url != '' ) {
			if ( $blogrolling === true ) {
				$opml = wp_remote_fopen($opml_url);
			} else {
				$opml = file_get_contents($opml_url);
			}

			/** Load OPML Parser */
			include_once( ABSPATH . 'wp-admin/link-parse-opml.php' );

			$link_count = count($names);
			for ( $i = 0; $i < $link_count; $i++ ) {
				if ('Last' == substr($titles[$i], 0, 4))
					$titles[$i] = '';
				if ( 'http' == substr($titles[$i], 0, 4) )
					$titles[$i] = '';
				$link = array( 'link_url' => $urls[$i], 'link_name' => $wpdb->escape($names[$i]), 'link_category' => array($cat_id), 'link_description' => $wpdb->escape($descriptions[$i]), 'link_owner' => $user_ID, 'link_rss' => $feeds[$i]);
				wp_insert_link($link);
				echo sprintf('<p>'.__('Inserted <strong>%s</strong>', 'opml-importer').'</p>', $names[$i]);
			}
?>

<p><?php printf(__('Inserted %1$d links into category %2$s. All done! Go <a href="%3$s">manage those links</a>.', 'opml-importer'), $link_count, $cat_id, 'link-manager.php') ?></p>

<?php
} // end if got url
else
{
	echo "<p>" . __("You need to supply your OPML url. Press back on your browser and try again", 'opml-importer') . "</p>\n";
} // end else

if ( ! $blogrolling )
	do_action( 'wp_delete_file', $opml_url);
	@unlink($opml_url);
?>
</div>
<?php
		break;
	} // end case 1
} // end switch
	}

	function OPML_Import() {}
}

$opml_importer = new OPML_Import();

register_importer('opml', __('Blogroll', 'opml-importer'), __('Import links in OPML format.', 'opml-importer'), array(&$opml_importer, 'dispatch'));

} // class_exists( 'WP_Importer' )

function opml_importer_init() {
    load_plugin_textdomain( 'opml-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'opml_importer_init' );
