<?php

/* Meta boxes */

function origin_settings(){
	add_meta_box("et_post_meta", "ET Settings", "origin_display_options", "post", "normal", "high");
	add_meta_box("et_post_meta", "ET Settings", "origin_display_options", "page", "normal", "high");
}
add_action("admin_init", "origin_settings");

function origin_display_options( $callback_args ) {
	global $post, $themename;

	$post_type = $callback_args->post_type;

	$temp_array = array();

	$temp_array = maybe_unserialize( get_post_meta( get_the_ID(), '_et_origin_settings', true ) );

	$thumbnail = isset( $temp_array['thumbnail'] ) ? $temp_array['thumbnail'] : '';

	wp_nonce_field( basename( __FILE__ ), 'et_settings_nonce' );
?>
	<div id="et_custom_settings" style="margin: 13px 0 17px 4px;">
		<p style="margin-bottom: 22px;">
			<label for="et_upload_image"><?php esc_html_e( 'Big Thumbnail', 'Origin' ); ?>: </label><br/>
			<input id="et_upload_image" type="text" size="90" name="et_upload_image" value="<?php echo esc_attr( $thumbnail ); ?>" />
			<input class="upload_image_button" type="button" value="<?php esc_attr_e( 'Upload Image', 'Origin' ); ?>" /><br/>
			<small>(<?php esc_html_e( 'enter an URL or upload an image for the big thumbnail', 'Origin' ); ?>)</small>
		</p>
	</div> <!-- #et_custom_settings -->

	<?php
}

add_action( 'save_post', 'origin_save_details', 10, 2 );
function origin_save_details( $post_id, $post ){
	global $pagenow;
	if ( 'post.php' != $pagenow ) return $post_id;

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	if ( !isset( $_POST['et_settings_nonce'] ) || ! wp_verify_nonce( $_POST['et_settings_nonce'], basename( __FILE__ ) ) )
        return $post_id;

	$temp_array = array();

	$temp_array['thumbnail'] = isset( $_POST["et_upload_image"] ) ? esc_url_raw( $_POST["et_upload_image"] ) : '';

	update_post_meta( $post_id, "_et_origin_settings", $temp_array );
}

add_action( 'admin_enqueue_scripts', 'et_origin_admin_scripts' );
function et_origin_admin_scripts( $hook ){
	if( 'post.php' != $hook ) return;

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_register_script( 'origin-upload', get_template_directory_uri() . '/js/custom_uploader.js', array( 'jquery', 'media-upload', 'thickbox' ) );
	wp_enqueue_script( 'origin-upload' );

	wp_enqueue_script( 'et-ptemplates-fwdelete', get_template_directory_uri() . '/js/delete_fwidth.js', array('jquery'), '1.1', true );
}