<?php
add_action( 'admin_enqueue_scripts', 'import_epanel_javascript' );
function import_epanel_javascript( $hook_suffix ) {
	if ( 'admin.php' == $hook_suffix && isset( $_GET['import'] ) && isset( $_GET['step'] ) && 'wordpress' == $_GET['import'] && '1' == $_GET['step'] )
		add_action( 'admin_head', 'admin_headhook' );
}

function admin_headhook(){ ?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$("p.submit").before("<p><input type='checkbox' id='importepanel' name='importepanel' value='1' style='margin-right: 5px;'><label for='importepanel'>Import epanel settings</label></p>");
		});
	</script>
<?php }

add_action('import_end','importend');
function importend(){
	global $wpdb, $shortname;

	#make custom fields image paths point to sampledata/sample_images folder
	$sample_images_postmeta = $wpdb->get_results(
		$wpdb->prepare( "SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE meta_value REGEXP %s", 'http://et_sample_images.com' )
	);
	if ( $sample_images_postmeta ) {
		foreach ( $sample_images_postmeta as $postmeta ){
			$template_dir = get_template_directory_uri();
			if ( is_multisite() ){
				switch_to_blog(1);
				$main_siteurl = site_url();
				restore_current_blog();

				$template_dir = $main_siteurl . '/wp-content/themes/' . get_template();
			}
			preg_match( '/http:\/\/et_sample_images.com\/([^.]+).jpg/', $postmeta->meta_value, $matches );
			$image_path = $matches[1];

			$local_image = preg_replace( '/http:\/\/et_sample_images.com\/([^.]+).jpg/', $template_dir . '/sampledata/sample_images/$1.jpg', $postmeta->meta_value );

			$local_image = preg_replace( '/s:55:/', 's:' . strlen( $template_dir . '/sampledata/sample_images/' . $image_path . '.jpg' ) . ':', $local_image );

			$wpdb->update( $wpdb->postmeta, array( 'meta_value' => esc_url_raw( $local_image ) ), array( 'meta_id' => $postmeta->meta_id ), array( '%s' ) );
		}
	}

	if ( !isset($_POST['importepanel']) )
		return;

	$importOptions = 'YTo3NDp7czoxNDoiRXZvbHV0aW9uX2xvZ28iO3M6MDoiIjtzOjE3OiJFdm9sdXRpb25fZmF2aWNvbiI7czowOiIiO3M6MjI6IkV2b2x1dGlvbl9jb2xvcl9zY2hlbWUiO3M6NzoiRGVmYXVsdCI7czoyMjoiRXZvbHV0aW9uX2NhdG51bV9wb3N0cyI7czoxOiI2IjtzOjI2OiJFdm9sdXRpb25fYXJjaGl2ZW51bV9wb3N0cyI7czoxOiI1IjtzOjI1OiJFdm9sdXRpb25fc2VhcmNobnVtX3Bvc3RzIjtzOjE6IjUiO3M6MjI6IkV2b2x1dGlvbl90YWdudW1fcG9zdHMiO3M6MToiNSI7czoyMToiRXZvbHV0aW9uX2RhdGVfZm9ybWF0IjtzOjY6IkYgaiwgWSI7czoxNToiRXZvbHV0aW9uX3F1b3RlIjtzOjI6Im9uIjtzOjM2OiJFdm9sdXRpb25fZGlzcGxheV9yZWNlbnR3b3JrX3NlY3Rpb24iO3M6Mjoib24iO3M6MTk6IkV2b2x1dGlvbl9xdW90ZV9vbmUiO3M6MTMzOiJMb3JlbSBpcHN1bSBkb2xvciBzaXQgYW1ldCBjb25zZWN0ZXR1ciBhZGlwaXNjaW5nIGVsaXQgZG9uZWMgZmFjaWxpc2lzIHVsdHJpIGxpYmVybyBhYyB0ZW1wdXMgZG9uZWMgZGljdHVtIG1hZ25hIHNpdCBhbWV0IGNvbnNlY3RldHVyIjtzOjIzOiJFdm9sdXRpb25faG9tZXdvcmtfZGVzYyI7czoxNDA6IlBlbGxlbnRlc3F1ZSBhZGlwaXNjaW5nIG9kaW8gZXUgbmVxdWUgZ3JhdmlkYSB2ZWhpY3VsYS4gVXQgdWx0cmljaWVzIGRpYW0gdmVsIGVzdCBjb252YWxsaXMgbm9uIGF1Y3RvciBkdWkgc2NlbGVyaXNxdWUuIFF1aXNxdWUgYXQgZXJhdCBzZW0uIjtzOjI0OiJFdm9sdXRpb25fcG9zdHNfd29ya19udW0iO3M6MToiMyI7czoyODoiRXZvbHV0aW9uX2Rpc3BsYXlfYWJvdXRfcGFnZSI7czoyOiJvbiI7czozNToiRXZvbHV0aW9uX2Rpc3BsYXlfcmVjZW50X2Jsb2dfcG9zdHMiO3M6Mjoib24iO3M6MjA6IkV2b2x1dGlvbl9hYm91dF9wYWdlIjtzOjU6IkFib3V0IjtzOjMzOiJFdm9sdXRpb25faG9tZV9yZWNlbnRibG9nX3NlY3Rpb24iO3M6NDoiQmxvZyI7czoyNDoiRXZvbHV0aW9uX3Bvc3RzX2Jsb2dfbnVtIjtzOjE6IjMiO3M6MjQ6IkV2b2x1dGlvbl9ob21lcGFnZV9wb3N0cyI7czoxOiI3IjtzOjE4OiJFdm9sdXRpb25fZmVhdHVyZWQiO3M6Mjoib24iO3M6MTk6IkV2b2x1dGlvbl9kdXBsaWNhdGUiO3M6Mjoib24iO3M6MTg6IkV2b2x1dGlvbl9mZWF0X2NhdCI7czo4OiJGZWF0dXJlZCI7czoyMjoiRXZvbHV0aW9uX2ZlYXR1cmVkX251bSI7czoxOiIzIjtzOjI2OiJFdm9sdXRpb25fc2xpZGVyX2FuaW1hdGlvbiI7czo0OiJmYWRlIjtzOjIxOiJFdm9sdXRpb25fc2xpZGVyX2F1dG8iO3M6Mjoib24iO3M6MjI6IkV2b2x1dGlvbl9zbGlkZXJfcGF1c2UiO3M6Mjoib24iO3M6MjY6IkV2b2x1dGlvbl9zbGlkZXJfYXV0b3NwZWVkIjtzOjQ6IjcwMDAiO3M6MjY6IkV2b2x1dGlvbl9lbmFibGVfZHJvcGRvd25zIjtzOjI6Im9uIjtzOjE5OiJFdm9sdXRpb25faG9tZV9saW5rIjtzOjI6Im9uIjtzOjIwOiJFdm9sdXRpb25fc29ydF9wYWdlcyI7czoxMDoicG9zdF90aXRsZSI7czoyMDoiRXZvbHV0aW9uX29yZGVyX3BhZ2UiO3M6MzoiYXNjIjtzOjI3OiJFdm9sdXRpb25fdGllcnNfc2hvd25fcGFnZXMiO3M6MToiMyI7czozNzoiRXZvbHV0aW9uX2VuYWJsZV9kcm9wZG93bnNfY2F0ZWdvcmllcyI7czoyOiJvbiI7czoyNjoiRXZvbHV0aW9uX2NhdGVnb3JpZXNfZW1wdHkiO3M6Mjoib24iO3M6MzI6IkV2b2x1dGlvbl90aWVyc19zaG93bl9jYXRlZ29yaWVzIjtzOjE6IjMiO3M6MTg6IkV2b2x1dGlvbl9zb3J0X2NhdCI7czo0OiJuYW1lIjtzOjE5OiJFdm9sdXRpb25fb3JkZXJfY2F0IjtzOjM6ImFzYyI7czoxOToiRXZvbHV0aW9uX3Bvc3RpbmZvMiI7YTo0OntpOjA7czo2OiJhdXRob3IiO2k6MTtzOjQ6ImRhdGUiO2k6MjtzOjEwOiJjYXRlZ29yaWVzIjtpOjM7czo4OiJjb21tZW50cyI7fXM6MjA6IkV2b2x1dGlvbl90aHVtYm5haWxzIjtzOjI6Im9uIjtzOjI3OiJFdm9sdXRpb25fc2hvd19wb3N0Y29tbWVudHMiO3M6Mjoib24iO3M6MTk6IkV2b2x1dGlvbl9wb3N0aW5mbzEiO2E6NDp7aTowO3M6NjoiYXV0aG9yIjtpOjE7czo0OiJkYXRlIjtpOjI7czoxMDoiY2F0ZWdvcmllcyI7aTozO3M6ODoiY29tbWVudHMiO31zOjI2OiJFdm9sdXRpb25fdGh1bWJuYWlsc19pbmRleCI7czoyOiJvbiI7czoyMjoiRXZvbHV0aW9uX2NoaWxkX2Nzc3VybCI7czowOiIiO3M6MjQ6IkV2b2x1dGlvbl9jb2xvcl9tYWluZm9udCI7czowOiIiO3M6MjQ6IkV2b2x1dGlvbl9jb2xvcl9tYWlubGluayI7czowOiIiO3M6MjQ6IkV2b2x1dGlvbl9jb2xvcl9wYWdlbGluayI7czowOiIiO3M6MzE6IkV2b2x1dGlvbl9jb2xvcl9wYWdlbGlua19hY3RpdmUiO3M6MDoiIjtzOjI0OiJFdm9sdXRpb25fY29sb3JfaGVhZGluZ3MiO3M6MDoiIjtzOjI5OiJFdm9sdXRpb25fY29sb3Jfc2lkZWJhcl9saW5rcyI7czowOiIiO3M6MjE6IkV2b2x1dGlvbl9mb290ZXJfdGV4dCI7czowOiIiO3M6Mjc6IkV2b2x1dGlvbl9jb2xvcl9mb290ZXJsaW5rcyI7czowOiIiO3M6Mjg6IkV2b2x1dGlvbl9zZW9faG9tZV90aXRsZXRleHQiO3M6MDoiIjtzOjM0OiJFdm9sdXRpb25fc2VvX2hvbWVfZGVzY3JpcHRpb250ZXh0IjtzOjA6IiI7czozMToiRXZvbHV0aW9uX3Nlb19ob21lX2tleXdvcmRzdGV4dCI7czowOiIiO3M6MjM6IkV2b2x1dGlvbl9zZW9faG9tZV90eXBlIjtzOjI3OiJCbG9nTmFtZSB8IEJsb2cgZGVzY3JpcHRpb24iO3M6Mjc6IkV2b2x1dGlvbl9zZW9faG9tZV9zZXBhcmF0ZSI7czozOiIgfCAiO3M6MzI6IkV2b2x1dGlvbl9zZW9fc2luZ2xlX2ZpZWxkX3RpdGxlIjtzOjk6InNlb190aXRsZSI7czozODoiRXZvbHV0aW9uX3Nlb19zaW5nbGVfZmllbGRfZGVzY3JpcHRpb24iO3M6MTU6InNlb19kZXNjcmlwdGlvbiI7czozNToiRXZvbHV0aW9uX3Nlb19zaW5nbGVfZmllbGRfa2V5d29yZHMiO3M6MTI6InNlb19rZXl3b3JkcyI7czoyNToiRXZvbHV0aW9uX3Nlb19zaW5nbGVfdHlwZSI7czoyMToiUG9zdCB0aXRsZSB8IEJsb2dOYW1lIjtzOjI5OiJFdm9sdXRpb25fc2VvX3NpbmdsZV9zZXBhcmF0ZSI7czozOiIgfCAiO3M6MjQ6IkV2b2x1dGlvbl9zZW9faW5kZXhfdHlwZSI7czoyNDoiQ2F0ZWdvcnkgbmFtZSB8IEJsb2dOYW1lIjtzOjI4OiJFdm9sdXRpb25fc2VvX2luZGV4X3NlcGFyYXRlIjtzOjM6IiB8ICI7czozMzoiRXZvbHV0aW9uX2ludGVncmF0ZV9oZWFkZXJfZW5hYmxlIjtzOjI6Im9uIjtzOjMxOiJFdm9sdXRpb25faW50ZWdyYXRlX2JvZHlfZW5hYmxlIjtzOjI6Im9uIjtzOjM2OiJFdm9sdXRpb25faW50ZWdyYXRlX3NpbmdsZXRvcF9lbmFibGUiO3M6Mjoib24iO3M6Mzk6IkV2b2x1dGlvbl9pbnRlZ3JhdGVfc2luZ2xlYm90dG9tX2VuYWJsZSI7czoyOiJvbiI7czoyNjoiRXZvbHV0aW9uX2ludGVncmF0aW9uX2hlYWQiO3M6MDoiIjtzOjI2OiJFdm9sdXRpb25faW50ZWdyYXRpb25fYm9keSI7czowOiIiO3M6MzI6IkV2b2x1dGlvbl9pbnRlZ3JhdGlvbl9zaW5nbGVfdG9wIjtzOjA6IiI7czozNToiRXZvbHV0aW9uX2ludGVncmF0aW9uX3NpbmdsZV9ib3R0b20iO3M6MDoiIjtzOjE5OiJFdm9sdXRpb25fNDY4X2ltYWdlIjtzOjA6IiI7czoxNzoiRXZvbHV0aW9uXzQ2OF91cmwiO3M6MDoiIjtzOjIxOiJFdm9sdXRpb25fNDY4X2Fkc2Vuc2UiO3M6MDoiIjt9';

	/*global $options;

	foreach ($options as $value) {
		if( isset( $value['id'] ) ) {
			update_option( $value['id'], $value['std'] );
		}
	}*/

	$importedOptions = unserialize(base64_decode($importOptions));

	foreach ($importedOptions as $key=>$value) {
		if ($value != '') update_option( $key, $value );
	}

	update_option( $shortname . '_use_pages', 'false' );
} ?>