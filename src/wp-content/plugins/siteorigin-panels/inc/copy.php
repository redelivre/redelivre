<?php

/**
 * Filter content before we save it.
 *
 * @param $content
 * @return array|mixed|string
 * @filter content_save_pre
 */
function siteorigin_panels_content_save_pre($content){
	global $post;

	if ( !siteorigin_panels_setting('copy-content') ) return $content;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $content;
	if ( empty( $_POST['_sopanels_nonce'] ) || !wp_verify_nonce( $_POST['_sopanels_nonce'], 'save' ) ) return $content;
	if ( empty($_POST['panels_js_complete']) ) return $content;
	if ( !current_user_can( 'edit_post', $post->ID ) ) return $content;
	if ( empty( $_POST['grids'] ) || empty( $_POST['grid_cells'] ) || empty( $_POST['widgets'] ) || empty( $_POST['panel_order'] ) ) return $content;

	$data['grids'] = $_POST['grids'];
	$data['grid_cells'] = $_POST['grid_cells'];
	$data['widgets'] = $_POST['widgets'];
	$data['panel_order'] = $_POST['panel_order'];
	$data['action'] = 'siteorigin_panels_get_post_content';
	$data['post_id'] = (string) $post->ID;

	$data['widgets'] = array_map('stripslashes_deep', $data['widgets']);
	$data['_signature'] = sha1( NONCE_SALT . serialize($data) );

	// This can cause a fatal error, so handle in a separate request.
	$request = wp_remote_post( admin_url('admin-ajax.php?action=siteorigin_panels_get_post_content'), array(
		'method' => 'POST',
		'timeout' => 5,
		'redirection' => 0,
		'body' => $data
	) );

	if( !is_wp_error($request) && $request['response']['code'] == 200 && !empty($request['body']) ) $content = $request['body'];

	return $content;
}
add_filter('content_save_pre', 'siteorigin_panels_content_save_pre');

/**
 * Ajax handler to get the HTML representation of the request.
 */
function siteorigin_panels_content_save_pre_get(){
	if ( empty( $_POST['grids'] ) || empty( $_POST['grid_cells'] ) || empty( $_POST['widgets'] ) || empty( $_POST['panel_order'] ) ) exit();
	if ( empty( $_POST['_signature'] ) ) exit();

	$sig = $_POST['_signature'];
	$data = array(
		'grids' => $_POST['grids'],
		'grid_cells' => $_POST['grid_cells'],
		'widgets' => array_map('stripslashes_deep', $_POST['widgets']),
		'panel_order' => $_POST['panel_order'],
		'action' => $_POST['action'],
		'post_id' => $_POST['post_id'],
	);

	// Use the signature to secure the request.
	if( $sig != sha1( NONCE_SALT . serialize($data) ) ) exit();

	// This can cause a fatal error, so handle in a separate request.
	$panels_data = siteorigin_panels_get_panels_data_from_post( $_POST );

	$content = '';
	if( !empty($panels_data['widgets']) ) {
		// Save the panels data into post_content for SEO and search plugins
		$content = siteorigin_panels_render( $_POST['post_id'], false, $panels_data );
		$content = preg_replace(
			array(
				// Remove invisible content
				'@<head[^>]*?>.*?</head>@siu',
				'@<style[^>]*?>.*?</style>@siu',
				'@<script[^>]*?.*?</script>@siu',
				'@<object[^>]*?.*?</object>@siu',
				'@<embed[^>]*?.*?</embed>@siu',
				'@<applet[^>]*?.*?</applet>@siu',
				'@<noframes[^>]*?.*?</noframes>@siu',
				'@<noscript[^>]*?.*?</noscript>@siu',
				'@<noembed[^>]*?.*?</noembed>@siu',
			),
			array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',),
			$content
		);
		$content = strip_tags($content, '<img><h1><h2><h3><h4><h5><h6><a><p><em><strong>');
		$content = explode("\n", $content);
		$content = array_map('trim', $content);
		$content = implode("\n", $content);

		$content = preg_replace("/[\n]{2,}/", "\n\n", $content);
		$content = trim($content);
	}

	echo $content;
	exit();
}
add_action('wp_ajax_nopriv_siteorigin_panels_get_post_content', 'siteorigin_panels_content_save_pre_get');