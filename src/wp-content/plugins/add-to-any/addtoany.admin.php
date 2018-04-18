<?php

/**
 * Post options
 */
function A2A_SHARE_SAVE_add_meta_box() {
	$post_types = get_post_types( array( 'public' => true ) );
	
	$options = get_option( 'addtoany_options', array() );
	
	$title = apply_filters( 'A2A_SHARE_SAVE_meta_box_title', __( 'AddToAny', 'add-to-any' ) );
	foreach( $post_types as $post_type ) {
		if (
			// If automatic placement is enabled
			// for either floating bar
			isset( $options['floating_vertical'] ) && 'none' != $options['floating_vertical'] ||
			isset( $options['floating_horizontal'] ) && 'none' != $options['floating_horizontal'] ||
			// for standard buttons in posts
			'post' == $post_type && ( ! isset( $options['display_in_posts'] ) || $options['display_in_posts'] != '-1' ) ||
			// for standard buttons in pages
			'page' == $post_type && ( ! isset( $options['display_in_pages'] ) || $options['display_in_pages'] != '-1' ) ||
			// for standard buttons in a custom post type
			! isset( $options['display_in_cpt_' . $post_type] ) || $options['display_in_cpt_' . $post_type] != '-1'
		) {
			// Add meta box
			add_meta_box( 'A2A_SHARE_SAVE_meta', $title, 'A2A_SHARE_SAVE_meta_box_content', $post_type, 'advanced', 'high' );
		}
	}
}

function A2A_SHARE_SAVE_meta_box_content( $post ) {
	do_action( 'start_A2A_SHARE_SAVE_meta_box_content', $post );

	$disabled = get_post_meta( $post->ID, 'sharing_disabled', true ); ?>

	<p>
		<label for="enable_post_addtoany_sharing">
			<input type="checkbox" name="enable_post_addtoany_sharing" id="enable_post_addtoany_sharing" value="1"
				<?php checked( empty( $disabled ) ); 
				/* Have other known sharing checkboxes with the same option name
				 * inherit the AddToAny checkbox value on change
				 */ ?>
				onchange="if (jQuery) jQuery('input[name=&quot;enable_post_sharing&quot;]').attr('checked', jQuery(this).is(':checked'))">
			<?php _e( 'Show sharing buttons.' , 'add-to-any'); ?>
		</label>
		<input type="hidden" name="addtoany_sharing_status_hidden" value="1" />
	</p>

	<?php
	do_action( 'end_A2A_SHARE_SAVE_meta_box_content', $post );
}

function A2A_SHARE_SAVE_meta_box_save( $post_id ) {
	// If this is an autosave, this form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	// Save sharing_disabled if "Show sharing buttons" checkbox is unchecked
	if ( isset( $_POST['post_type'] ) ) {
		if ( current_user_can( 'edit_post', $post_id ) ) {
			if ( isset( $_POST['addtoany_sharing_status_hidden'] ) ) {
				if ( ! isset( $_POST['enable_post_addtoany_sharing'] ) ) {
					update_post_meta( $post_id, 'sharing_disabled', 1 );
				} else {
					delete_post_meta( $post_id, 'sharing_disabled' );
				}
			}
		}
	}

	return $post_id;
}

add_action( 'admin_init', 'A2A_SHARE_SAVE_add_meta_box' );
add_action( 'save_post', 'A2A_SHARE_SAVE_meta_box_save' );
add_action( 'edit_attachment', 'A2A_SHARE_SAVE_meta_box_save' );

/**
 * Adds feature pointers
 */
function A2A_SHARE_SAVE_enqueue_pointer_script_style( $hook_suffix ) {
	// Variable required for PHP < 5.5 because empty() only supports variables
	$options = get_option( 'addtoany_options', array() );
	
	// Return if AddToAny options have been set
	if ( ! empty( $options ) ) {
		return;
	}

	// Get array list of dismissed pointers for current user and convert it to array
	$dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

	// If any one of our pointers is not among dismissed pointers
	if (
		! in_array( 'addtoany_settings_pointer', $dismissed_pointers )
	) {
		// Enqueue pointer CSS and JS files, if needed
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );
		
		// Add footer scripts using callback function
		add_action( 'admin_print_footer_scripts', 'A2A_SHARE_SAVE_pointer_print_scripts' );
	}	
}

add_action( 'admin_enqueue_scripts', 'A2A_SHARE_SAVE_enqueue_pointer_script_style' );

function A2A_SHARE_SAVE_pointer_print_scripts() {
	$pointer_content_settings  = '<h3>AddToAny Sharing Settings</h3>';
	$pointer_content_settings .= '<p>To customize your AddToAny share buttons, click &quot;AddToAny&quot; in the Settings menu.</p>';
	
	// Get array list of dismissed pointers for current user and convert it to array
	$dismissed_pointers = explode( ',', get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
?>
	
	<script>
<?php if ( ! in_array( 'addtoany_settings_pointer', $dismissed_pointers ) ) : ?>
	jQuery(document).ready( function($) {
		$('#menu-settings').pointer({
			content:		'<?php echo $pointer_content_settings; ?>',
			position:		{
								edge:	'left', // arrow direction
								align:	'center' // vertical alignment
							},
			pointerWidth:	350,
			close:			function() {
								$.post( ajaxurl, {
										pointer: 'addtoany_settings_pointer', // pointer ID
										action: 'dismiss-wp-pointer'
								});
							}
		}).pointer('open');
	});
<?php endif; ?>
	</script>

<?php
}

function _a2a_position_in_content( $options, $option_box = false ) {
	
	if ( ! isset( $options['position'] ) ) {
		$options['position'] = 'bottom';
	}
	
	$positions = array(
		'bottom' => array(
			'selected' => ( 'bottom' == $options['position'] ) ? ' selected="selected"' : '',
			'string' => __( 'bottom', 'add-to-any' )
		),
		'top' => array(
			'selected' => ( 'top' == $options['position'] ) ? ' selected="selected"' : '',
			'string' => __( 'top', 'add-to-any' )
		),
		'both' => array(
			'selected' => ( 'both' == $options['position'] ) ? ' selected="selected"' : '',
			'string' => __( 'top &amp; bottom', 'add-to-any' )
		)
	);
	
	if ( $option_box ) {
		$html = '</label>';
		$html .= '<label>'; // Label needed to prevent checkmark toggle on SELECT click 
		$html .= '<select name="A2A_SHARE_SAVE_position">';
		$html .= '<option value="bottom"' . $positions['bottom']['selected'] . '>' . $positions['bottom']['string'] . '</option>';
		$html .= '<option value="top"' . $positions['top']['selected'] . '>' . $positions['top']['string'] . '</option>';
		$html .= '<option value="both"' . $positions['both']['selected'] . '>' . $positions['both']['string'] . '</option>';
		$html .= '</select>';
		
		return $html;
	} else {
		$html = '<span class="A2A_SHARE_SAVE_position">';
		$html .= $positions[$options['position']]['string'];
		$html .= '</span>';
		
		return $html;
	}
}

function _a2a_selected_attr( $value, $option_name, $options ) {
	if ( ! empty( $options[ $option_name  ] ) && $value === $options[ $option_name  ] ) {
		echo ' selected="selected"';
	}
}

function _a2a_valid_hex_color( $value ) {
	if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) {
		return true;
	}
	
	return false;
}

function A2A_SHARE_SAVE_options_page() {

	global $A2A_SHARE_SAVE_plugin_url,
		$A2A_SHARE_SAVE_services;
	
	// Require admin privs
	if ( ! current_user_can( 'manage_options' ) )
		return false;
	
	$new_options = array();
	
	$namespace = 'A2A_SHARE_SAVE_';
	
	// Make available services extensible via plugins, themes (functions.php), etc.
	$A2A_SHARE_SAVE_services = apply_filters( 'A2A_SHARE_SAVE_services', $A2A_SHARE_SAVE_services );
	
	// Which tab is selected?
	$possible_screens = array( 'default', 'floating' );
	$current_screen = ( isset( $_GET['action'] ) && in_array( $_GET['action'], $possible_screens ) ) ? $_GET['action'] : 'default';
	
	if ( isset( $_POST['Submit'] ) ) {
		
		// Nonce verification 
		check_admin_referer( 'add-to-any-update-options' );
		
		if ( 'floating' == $current_screen ) {
			// Floating options screen
			
			$possible_floating_values = array( 'left_docked', 'right_docked', 'none' );
			
			$new_options['floating_vertical'] = ( in_array( $_POST['A2A_SHARE_SAVE_floating_vertical'], $possible_floating_values ) ) ? $_POST['A2A_SHARE_SAVE_floating_vertical'] : 'none';
			$new_options['floating_horizontal'] = ( in_array( $_POST['A2A_SHARE_SAVE_floating_horizontal'], $possible_floating_values ) ) ? $_POST['A2A_SHARE_SAVE_floating_horizontal'] : 'none';
			
			$new_options['floating_horizontal_position'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_position'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_horizontal_position'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_position'] : '0';
			
			$new_options['floating_horizontal_offset'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_offset'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_horizontal_offset'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_offset'] : '0';
			
			
			$new_options['floating_horizontal_responsive'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive'] ) && 
				'1' == $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive']
			) ? '1' : '-1';
			
			$new_options['floating_horizontal_responsive_min_width'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive_min_width'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive_min_width'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_responsive_min_width'] : '981';
			
			$new_options['floating_horizontal_icon_size'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_horizontal_icon_size'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_horizontal_icon_size'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_icon_size'] : '32';
			
			$new_options['floating_horizontal_bg'] = ! empty( $_POST['A2A_SHARE_SAVE_floating_horizontal_bg'] ) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_bg'] : 'transparent';
			$new_options['floating_horizontal_bg_color'] = _a2a_valid_hex_color( $_POST['A2A_SHARE_SAVE_floating_horizontal_bg_color'] ) ? $_POST['A2A_SHARE_SAVE_floating_horizontal_bg_color'] : '#ffffff';
			
			$new_options['floating_vertical_position'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_position'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_vertical_position'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_vertical_position'] : '100';
			
			$new_options['floating_vertical_offset'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_offset'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_vertical_offset'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_vertical_offset'] : '0';
			
			$new_options['floating_vertical_responsive'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_responsive'] ) && 
				'1' == $_POST['A2A_SHARE_SAVE_floating_vertical_responsive']
			) ? '1' : '-1';
			
			$new_options['floating_vertical_responsive_max_width'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_responsive_max_width'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_vertical_responsive_max_width'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_vertical_responsive_max_width'] : '980';
			
			$new_options['floating_vertical_icon_size'] = ( 
				isset( $_POST['A2A_SHARE_SAVE_floating_vertical_icon_size'] ) && 
				is_numeric( $_POST['A2A_SHARE_SAVE_floating_vertical_icon_size'] ) 
			) ? $_POST['A2A_SHARE_SAVE_floating_vertical_icon_size'] : '32';
			
			$new_options['floating_vertical_bg'] = ! empty( $_POST['A2A_SHARE_SAVE_floating_vertical_bg'] ) ? $_POST['A2A_SHARE_SAVE_floating_vertical_bg'] : 'transparent';
			$new_options['floating_vertical_bg_color'] = _a2a_valid_hex_color( $_POST['A2A_SHARE_SAVE_floating_vertical_bg_color'] ) ? $_POST['A2A_SHARE_SAVE_floating_vertical_bg_color'] : '#ffffff';
			
		} else {
			// Standard options screen
			
			$new_options['position'] = ( isset( $_POST['A2A_SHARE_SAVE_position'] ) ) ? $_POST['A2A_SHARE_SAVE_position'] : 'bottom';
			$new_options['display_in_posts_on_front_page'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_posts_on_front_page'] ) && $_POST['A2A_SHARE_SAVE_display_in_posts_on_front_page'] == '1' ) ? '1' : '-1';
			$new_options['display_in_posts_on_archive_pages'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_posts_on_archive_pages'] ) && $_POST['A2A_SHARE_SAVE_display_in_posts_on_archive_pages'] == '1' ) ? '1' : '-1';
			$new_options['display_in_excerpts'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_excerpts'] ) && $_POST['A2A_SHARE_SAVE_display_in_excerpts'] == '1' ) ? '1' : '-1';
			$new_options['display_in_posts'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_posts'] ) && $_POST['A2A_SHARE_SAVE_display_in_posts'] == '1' ) ? '1' : '-1';
			$new_options['display_in_pages'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_pages'] ) && $_POST['A2A_SHARE_SAVE_display_in_pages'] == '1' ) ? '1' : '-1';
			$new_options['display_in_attachments'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_attachments'] ) && $_POST['A2A_SHARE_SAVE_display_in_attachments'] == '1' ) ? '1' : '-1';
			$new_options['display_in_feed'] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_feed'] ) && $_POST['A2A_SHARE_SAVE_display_in_feed'] == '1' ) ? '1' : '-1';
			$new_options['onclick'] = ( isset( $_POST['A2A_SHARE_SAVE_onclick'] ) && $_POST['A2A_SHARE_SAVE_onclick'] == '1' ) ? '1' : '-1';
			$new_options['icon_size'] = ( ! empty( $_POST['A2A_SHARE_SAVE_icon_size'] ) && is_numeric( $_POST['A2A_SHARE_SAVE_icon_size'] ) ) ? $_POST['A2A_SHARE_SAVE_icon_size'] : '32';
			$new_options['icon_bg'] = ( ! empty( $_POST['A2A_SHARE_SAVE_icon_bg'] ) ) ? $_POST['A2A_SHARE_SAVE_icon_bg'] : 'original';
			$new_options['icon_bg_color'] = _a2a_valid_hex_color( $_POST['A2A_SHARE_SAVE_icon_bg_color'] ) ? $_POST['A2A_SHARE_SAVE_icon_bg_color'] : '#2a2a2a';
			$new_options['icon_fg'] = ( ! empty( $_POST['A2A_SHARE_SAVE_icon_fg'] ) ) ? $_POST['A2A_SHARE_SAVE_icon_fg'] : 'original';
			$new_options['icon_fg_color'] = _a2a_valid_hex_color( $_POST['A2A_SHARE_SAVE_icon_fg_color'] ) ? $_POST['A2A_SHARE_SAVE_icon_fg_color'] : '#ffffff';
			$new_options['button'] = ( isset( $_POST['A2A_SHARE_SAVE_button'] ) ) ? $_POST['A2A_SHARE_SAVE_button'] : '';
			$new_options['button_custom'] = ( isset( $_POST['A2A_SHARE_SAVE_button_custom'] ) ) ? $_POST['A2A_SHARE_SAVE_button_custom'] : '';
			$new_options['button_show_count'] = ( isset( $_POST['A2A_SHARE_SAVE_button_show_count'] ) && $_POST['A2A_SHARE_SAVE_button_show_count'] == '1' ) ? '1' : '-1';
			$new_options['header'] = ( isset( $_POST['A2A_SHARE_SAVE_header'] ) ) ? $_POST['A2A_SHARE_SAVE_header'] : '';
			$new_options['additional_js_variables'] = ( isset( $_POST['A2A_SHARE_SAVE_additional_js_variables'] ) ) ? trim( $_POST['A2A_SHARE_SAVE_additional_js_variables'] ) : '';
			$new_options['additional_css'] = ( isset( $_POST['A2A_SHARE_SAVE_additional_css'] ) ) ? trim( $_POST['A2A_SHARE_SAVE_additional_css'] ) : '';
			$new_options['custom_icons'] = ( isset( $_POST['A2A_SHARE_SAVE_custom_icons'] ) && $_POST['A2A_SHARE_SAVE_custom_icons'] == 'url' ) ? 'url' : '-1';
			$new_options['custom_icons_url'] = ( isset( $_POST['A2A_SHARE_SAVE_custom_icons_url'] ) ) ? trailingslashit( $_POST['A2A_SHARE_SAVE_custom_icons_url'] ) : '';
			$new_options['custom_icons_type'] = ( isset( $_POST['A2A_SHARE_SAVE_custom_icons_type'] ) ) ? $_POST['A2A_SHARE_SAVE_custom_icons_type'] : 'png';
			$new_options['custom_icons_width'] = ( isset( $_POST['A2A_SHARE_SAVE_custom_icons_width'] ) ) ? $_POST['A2A_SHARE_SAVE_custom_icons_width'] : '';
			$new_options['custom_icons_height'] = ( isset( $_POST['A2A_SHARE_SAVE_custom_icons_height'] ) ) ? $_POST['A2A_SHARE_SAVE_custom_icons_height'] : '';
			$new_options['cache'] = ( isset( $_POST['A2A_SHARE_SAVE_cache'] ) && $_POST['A2A_SHARE_SAVE_cache'] == '1' ) ? '1' : '-1';
			
			$custom_post_types = array_values( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) );
			foreach ( $custom_post_types as $custom_post_type_obj ) {
				$placement_name = $custom_post_type_obj->name;
				$new_options['display_in_cpt_' . $placement_name] = ( isset( $_POST['A2A_SHARE_SAVE_display_in_cpt_' . $placement_name] ) 
					&& $_POST['A2A_SHARE_SAVE_display_in_cpt_' . $placement_name] == '1' ) ? '1' : '-1';
			}
			
			// Schedule cache refresh?
			if ( isset( $_POST['A2A_SHARE_SAVE_cache'] ) && $_POST['A2A_SHARE_SAVE_cache'] == '1' ) {
				A2A_SHARE_SAVE_schedule_cache();
				A2A_SHARE_SAVE_refresh_cache();
			} else {
				A2A_SHARE_SAVE_unschedule_cache();
			}
			
			// Store desired text for text-only:
			$new_options['button_text'] = ( trim( $_POST['A2A_SHARE_SAVE_button_text'] ) != '' ) ? $_POST['A2A_SHARE_SAVE_button_text'] : __('Share','add-to-any');
				
			// Store chosen individual services to make active
			$active_services = array();
			if ( ! isset( $_POST['A2A_SHARE_SAVE_active_services'] ) )
				$_POST['A2A_SHARE_SAVE_active_services'] = array();
			foreach ( $_POST['A2A_SHARE_SAVE_active_services'] as $dummy=>$sitename ) {
				$service = substr( $sitename, 7 );
				$active_services[] = $service;
				
				// AddToAny counter enabled?
				if ( in_array( $service, array( 'facebook', 'pinterest', 'linkedin', 'reddit' ) ) ) {
					$new_options['special_' . $service . '_options'] = array(
						'show_count' => ( ( isset( $_POST['addtoany_' . $service . '_show_count'] ) && $_POST['addtoany_' . $service . '_show_count'] == '1') ? '1' : '-1' )
					);
				}
			}
				
			$new_options['active_services'] = $active_services;
			
			// Store special service options
			$new_options['special_facebook_like_options'] = array(
				'verb' => ( ( isset( $_POST['addtoany_facebook_like_verb'] ) && $_POST['addtoany_facebook_like_verb'] == 'recommend') ? 'recommend' : 'like' )
			);
			$new_options['special_twitter_tweet_options'] = array(
				'show_count' => '-1' // Twitter doesn't provide counts anymore
			);
			$new_options['special_google_plusone_options'] = array(
				'show_count' => '-1' // Google+ doesn't provide counts anymore
			);
			$new_options['special_google_plus_share_options'] = array(
				'show_count' => '-1' // Google+ doesn't provide counts anymore
			);
			$new_options['special_pinterest_pin_options'] = array(
				'show_count' => ( ( isset( $_POST['addtoany_pinterest_pin_show_count'] ) && $_POST['addtoany_pinterest_pin_show_count'] == '1' ) ? '1' : '-1' )
			);
			
		}		
		
		// Get all existing AddToAny options
		$existing_options = get_option( 'addtoany_options', array() );
		
		// Merge $new_options into $existing_options to retain AddToAny options from all other screens/tabs
		if ( $existing_options ) {
			$new_options = array_merge( $existing_options, $new_options );
		}
		
		update_option( 'addtoany_options', $new_options );
		
		?>
		<div class="updated"><p><?php _e( 'Settings saved.' ); ?></p></div>
		<?php
		
	} else if ( isset( $_POST['Reset'] ) ) {
		// Nonce verification 
		check_admin_referer( 'add-to-any-update-options' );
		
		delete_option( 'addtoany_options' );
	}

	$options = stripslashes_deep( get_option( 'addtoany_options', array() ) );
	
	?>
	
	<div class="wrap">
	
	<h1><?php _e( 'AddToAny Share Settings', 'add-to-any' ); ?></h1>
	
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'options-general.php?page=addtoany' ); ?>" class="nav-tab<?php if ( 'default' == $current_screen ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Standard' ); ?></a>
		<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'floating' ), admin_url( 'options-general.php?page=addtoany' ) ) ); ?>" class="nav-tab<?php if ( 'floating' == $current_screen ) echo ' nav-tab-active'; ?>"><?php esc_html_e( 'Floating' ); ?></a>
	</h2>

	<form id="addtoany_admin_form" method="post" action="">
	
	<?php wp_nonce_field('add-to-any-update-options'); ?>

		<table class="form-table">
		
		<?php if ( 'default' == $current_screen ) : ?>
			<tr valign="top">
			<th scope="row"><?php _e("Icon Style", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input class="small-text" name="A2A_SHARE_SAVE_icon_size" type="number" max="300" min="10" maxlength="3" step="2" oninput="if(this.value.length > 3) this.value=this.value.slice(0, 3)" placeholder="32" value="<?php echo ! empty( $options['icon_size'] ) ? esc_attr( $options['icon_size'] ) : '32'; ?>"> pixels</label>
				<br>
				<label>
					<select class="addtoany_icon_color" name="A2A_SHARE_SAVE_icon_bg">
						<option value="original"<?php _a2a_selected_attr('original', 'icon_bg', $options); ?>>Original</option>
						<option value="transparent"<?php _a2a_selected_attr('transparent', 'icon_bg', $options); ?>>Transparent</option>
						<option value="custom"<?php _a2a_selected_attr('custom', 'icon_bg', $options); ?>>Custom&#8230;</option>
					</select>
					background
				</label>
				<div class="color-field-container"><input name="A2A_SHARE_SAVE_icon_bg_color" class="color-field" type="text" value="<?php echo ! empty( $options['icon_bg_color'] ) ? esc_attr( $options['icon_bg_color'] ) : '#2a2a2a'; ?>" data-default-color="#2a2a2a"></div>
				<br>
				<label>
					<select class="addtoany_icon_color" name="A2A_SHARE_SAVE_icon_fg">
						<option value="original"<?php _a2a_selected_attr('original', 'icon_fg', $options); ?>>Original</option>
						<option value="transparent" disabled="disabled">Transparent</option>
						<option value="custom"<?php _a2a_selected_attr('custom', 'icon_fg', $options); ?>>Custom&#8230;</option>
					</select>
					foreground
				</label>
				<div class="color-field-container"><input name="A2A_SHARE_SAVE_icon_fg_color" class="color-field" type="text" value="<?php echo ! empty( $options['icon_fg_color'] ) ? esc_attr( $options['icon_fg_color'] ) : '#ffffff'; ?>" data-default-color="#ffffff"></div>
			</fieldset></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e("Share Buttons", 'add-to-any'); ?></th>
			<td><fieldset>
				<ul id="addtoany_services_sortable" class="addtoany_admin_list addtoany_override">
					<li class="dummy"><img src="<?php echo $A2A_SHARE_SAVE_plugin_url; ?>/icons/transparent.gif" width="32" height="32" alt="" /></li>
				</ul>
				<p id="addtoany_services_info"><?php _e("Choose the services you want below. &nbsp;Click a chosen service again to remove. &nbsp;Reorder services by dragging and dropping as they appear above.", 'add-to-any'); ?></p>
				<ul id="addtoany_services_selectable" class="addtoany_admin_list">
				<?php
					// Show all services
					foreach ($A2A_SHARE_SAVE_services as $service_safe_name=>$site) { 
						if ( isset( $site['href'] ) )
							$custom_service = true;
						else
							$custom_service = false;
						
						if ( ! isset( $site['icon'] ) )
							$site['icon'] = 'default';
							
						$special_service = ( in_array( $service_safe_name, array( 'facebook', 'pinterest', 'linkedin', 'reddit' ) ) ) 
							? ' class="addtoany_special_service"' : '';
					?>
						<li data-addtoany-icon-name="<?php echo $site['icon']; ?>"<?php echo $special_service; ?> id="a2a_wp_<?php echo $service_safe_name; ?>" title="<?php echo $site['name']; ?>">
							<img src="<?php echo (isset($site['icon_url'])) ? $site['icon_url'] : $A2A_SHARE_SAVE_plugin_url.'/icons/'.$site['icon'].'.svg'; ?>" width="<?php echo (isset($site['icon_width'])) ? $site['icon_width'] : '24'; ?>" height="<?php echo (isset($site['icon_height'])) ? $site['icon_height'] : '24'; ?>"<?php if ( isset( $site['color'] ) ) : ?> style="background-color:#<?php echo $site['color']; endif; ?>"><?php echo $site['name']; ?>
						</li>
				<?php
					} ?>
					<li style="clear:left" id="a2a_wp_facebook_like" class="addtoany_special_service addtoany_3p_button" title="Facebook Like button">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url.'/icons/facebook_like_2x.png'; ?>" width="101" height="40" alt="Facebook Like" />
					</li>
					<li id="a2a_wp_twitter_tweet" class="addtoany_special_service addtoany_3p_button" title="Twitter Tweet button">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url.'/icons/twitter_tweet_2x.png'; ?>" width="122" height="40" alt="Twitter Tweet" />
					</li>
					<li id="a2a_wp_google_plusone" class="addtoany_special_service addtoany_3p_button" title="Google +1 button">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url.'/icons/google_plusone_2x.png'; ?>" width="64" height="40" alt="Google +1" />
					</li>
					<li id="a2a_wp_google_plus_share" class="addtoany_special_service addtoany_3p_button" title="Google+ Share button">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url.'/icons/google_plus_share_2x.png'; ?>" width="113" height="40" alt="Google+ Share" />
					</li>
					<li id="a2a_wp_pinterest_pin" class="addtoany_special_service addtoany_3p_button" title="Pinterest Pin It button">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url.'/icons/pinterest_pin_2x.png'; ?>" width="80" height="40" alt="Pinterest Pin It" />
					</li>
				</ul>
				<div id="addtoany_services_tip">
					<p style="line-height:0">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url; ?>/icons/instagram.svg" width="24" height="24" style="margin-right:8px">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url; ?>/icons/youtube.svg" width="24" height="24" style="margin-right:8px">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url; ?>/icons/snapchat.svg" width="24" height="24">
					</p>
					<p>You can setup Instagram, YouTube, Snapchat, and other buttons in an AddToAny Follow widget.</p><p>Add the &quot;AddToAny Follow&quot; widget in <a href="customize.php?autofocus[panel]=widgets&amp;return=options-general.php%3Fpage%3Daddtoany">Customize</a> or <a href="widgets.php">Widgets</a>.</p>
				</div>
			</fieldset></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e("Universal Button", 'add-to-any'); ?></th>
			<td><fieldset>
				<div class="addtoany_icon_size_large">
					<label class="addtoany_override a2a_kit_size_32">
						<input name="A2A_SHARE_SAVE_button" value="A2A_SVG_32" type="radio"<?php if ( ! isset( $options['button'] ) || 'A2A_SVG_32' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
						<img src="<?php echo $A2A_SHARE_SAVE_plugin_url.'/icons/a2a.svg'; ?>" width="32" height="32" alt="AddToAny" onclick="this.parentNode.firstChild.checked=true" />
					</label>
					<br>
				</div>
				
				<label>
					<input name="A2A_SHARE_SAVE_button" value="CUSTOM" id="A2A_SHARE_SAVE_button_is_custom" type="radio"<?php if ( isset( $options['button'] ) && 'CUSTOM' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
					<span style="margin:0 9px;vertical-align:middle"><?php _e("Image URL"); ?>:</span>
				</label>
				<input name="A2A_SHARE_SAVE_button_custom" type="text" class="code" size="50" onclick="document.getElementById('A2A_SHARE_SAVE_button_is_custom').checked=true" style="vertical-align:middle" value="<?php if ( isset( $options['button_custom'] ) ) esc_attr_e( $options['button_custom'] ); ?>" />
				<br>
				<label>
					<input name="A2A_SHARE_SAVE_button" value="TEXT" id="A2A_SHARE_SAVE_button_is_text" type="radio"<?php if ( isset( $options['button'] ) && 'TEXT' == $options['button'] ) echo ' checked="checked"'; ?> style="margin:9px 0;vertical-align:middle">
					<span style="margin:0 9px;vertical-align:middle"><?php _e("Text only"); ?>:</span>
				</label>
				<input name="A2A_SHARE_SAVE_button_text" type="text" class="code" size="50" onclick="document.getElementById('A2A_SHARE_SAVE_button_is_text').checked=true" style="vertical-align:middle;width:150px" value="<?php echo ( isset( $options['button_text'] ) && trim( '' != $options['button_text'] ) ) ? esc_attr( $options['button_text'] ) : __('Share','add-to-any'); ?>" />
				<br>
				<label>
					<input name="A2A_SHARE_SAVE_button" value="NONE" type="radio"<?php if ( isset( $options['button'] ) && 'NONE' == $options['button'] ) echo ' checked="checked"'; ?> onclick="return confirm('<?php _e('This option will disable universal sharing. Are you sure you want to disable universal sharing?', 'add-to-any' ) ?>')" style="margin:9px 0;vertical-align:middle">
					<span style="margin:0 9px;vertical-align:middle"><?php _e("None"); ?></span>
				</label>
				<br>
				<label>
					<input id="A2A_SHARE_SAVE_button_show_count" name="A2A_SHARE_SAVE_button_show_count" type="checkbox"<?php 
						if ( isset( $options['button_show_count'] ) && $options['button_show_count'] == '1' ) echo ' checked="checked"'; ?> value="1">
					<span style="margin-left:5px">Show count</span>
				</label>
				
			</fieldset></td>
			</tr>

			<tr valign="top">
			<th scope="row"><?php _e('Sharing Header', 'add-to-any'); ?></th>
			<td><fieldset id="addtoany_extra_section_sharing_header" class="addtoany_extra_section<?php if ( ! empty( $options['header'] ) ) echo ' addtoany_show_extra'; ?>" role="region">
				<label>
					<input name="A2A_SHARE_SAVE_header" type="text" class="code" placeholder="<?php esc_attr_e( 'Share this:' ); ?>" size="50" value="<?php if ( isset( $options['header'] ) ) esc_attr_e( $options['header'] ); ?>" />
				</label>
			</fieldset></td>
			</tr>
			
			<tr valign="top">
			<th scope="row"><?php _e('Placement', 'add-to-any'); ?></th>
			<td><fieldset>
				<label>
					<input id="A2A_SHARE_SAVE_display_in_posts" name="A2A_SHARE_SAVE_display_in_posts" type="checkbox"<?php 
						if ( ! isset( $options['display_in_posts'] ) || $options['display_in_posts'] != '-1' ) echo ' checked="checked"'; ?> value="1"/>
					<?php printf(__('Display at the %s of posts', 'add-to-any'), _a2a_position_in_content( $options, true )); ?>
				</label>
				<br/>
				<label>
					&nbsp; &nbsp; &nbsp; <input class="A2A_SHARE_SAVE_child_of_display_in_posts" name="A2A_SHARE_SAVE_display_in_posts_on_front_page" type="checkbox"<?php 
						if ( ! isset( $options['display_in_posts_on_front_page'] ) || $options['display_in_posts_on_front_page'] != '-1' ) echo ' checked="checked"';
						if ( isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) echo ' disabled="disabled"';
						?> value="1"/>
					<?php printf(__('Display at the %s of posts on the front page', 'add-to-any'), _a2a_position_in_content( $options )); ?>
				</label>
				<br/>
				<label>
					&nbsp; &nbsp; &nbsp; <input class="A2A_SHARE_SAVE_child_of_display_in_posts" name="A2A_SHARE_SAVE_display_in_posts_on_archive_pages" type="checkbox"<?php 
						if ( ! isset( $options['display_in_posts_on_archive_pages'] ) || $options['display_in_posts_on_archive_pages'] != '-1' ) echo ' checked="checked"';
						if ( isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) echo ' disabled="disabled"';
						?> value="1"/>
					<?php printf(__('Display at the %s of posts on archive pages', 'add-to-any'), _a2a_position_in_content( $options )); ?>
				</label>
				<br/>
				<label>
					&nbsp; &nbsp; &nbsp; <input class="A2A_SHARE_SAVE_child_of_display_in_posts" name="A2A_SHARE_SAVE_display_in_feed" type="checkbox"<?php 
						if ( ! isset( $options['display_in_feed'] ) || $options['display_in_feed'] != '-1' ) echo ' checked="checked"'; 
						if ( isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) echo ' disabled="disabled"';
						?> value="1"/>
					<?php printf(__('Display at the %s of posts in the feed', 'add-to-any'), _a2a_position_in_content( $options )); ?>
				</label>
				<br/>
				<label>
					<input name="A2A_SHARE_SAVE_display_in_excerpts" type="checkbox"<?php 
						if ( ! isset( $options['display_in_excerpts'] ) || $options['display_in_excerpts'] != '-1' ) echo ' checked="checked"';
						?> value="1"/>
					<?php printf(__('Display at the %s of excerpts', 'add-to-any'), _a2a_position_in_content( $options, false )); ?>
				</label>
				<br/>
				<label>
					<input name="A2A_SHARE_SAVE_display_in_pages" type="checkbox"<?php if ( ! isset( $options['display_in_pages'] ) || $options['display_in_pages'] != '-1' ) echo ' checked="checked"'; ?> value="1"/>
					<?php printf(__('Display at the %s of pages', 'add-to-any'), _a2a_position_in_content( $options, false )); ?>
				</label>
				<br/>
				<label>
					<input name="A2A_SHARE_SAVE_display_in_attachments" type="checkbox"<?php 
						if ( ! isset( $options['display_in_attachments'] ) || $options['display_in_attachments'] != '-1' ) echo ' checked="checked"';
						?> value="1"/>
					<?php printf(__('Display at the %s of media pages', 'add-to-any'), _a2a_position_in_content( $options, false )); ?>
				</label>
				
			<?php 
				$custom_post_types = array_values( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) );
				foreach ( $custom_post_types as $custom_post_type_obj ) :
					$placement_label = $custom_post_type_obj->labels->name;
					$placement_name = $custom_post_type_obj->name;
			?>
				<br/>
				<label>
					<input name="A2A_SHARE_SAVE_display_in_cpt_<?php echo $placement_name; ?>" type="checkbox"<?php if ( ! isset( $options['display_in_cpt_' . $placement_name] ) || $options['display_in_cpt_' . $placement_name] != '-1' ) echo ' checked="checked"'; ?> value="1"/>
					<?php printf(__('Display at the %s of %s', 'add-to-any'), _a2a_position_in_content( $options, false ), esc_html( $placement_label ) ); ?>
				</label>
			<?php endforeach; ?>
				
				<br/><br/>
				<div class="setting-description">
					<?php _e("See <a href=\"widgets.php\" title=\"Theme Widgets\">Widgets</a> and <a href=\"options-general.php?page=addtoany&action=floating\" title=\"AddToAny Floating Share Buttons\">Floating</a> for additional placement options. For advanced placement, see <a href=\"https://wordpress.org/plugins/add-to-any/faq/\">the FAQs</a>.", 'add-to-any'); ?>
				</div>
			</fieldset></td>
			</tr>

			<tr valign="top">
			<th scope="row"><?php _e('Menu Options', 'add-to-any'); ?></th>
			<td><fieldset id="addtoany_extra_section_menu_options" class="addtoany_extra_section" role="region">
				<label>
					<input name="A2A_SHARE_SAVE_onclick" type="checkbox"<?php if ( isset( $options['onclick'] ) && $options['onclick'] == '1' ) echo ' checked="checked"'; ?> value="1"/>
					<?php _e('Only show the universal share menu when the user <em>clicks</em> the universal share button', 'add-to-any'); ?>
				</label>
			</fieldset></td>
			</tr>

			<tr valign="top">
			<th scope="row"><?php _e('Additional JavaScript', 'add-to-any'); ?></th>
			<td><fieldset id="addtoany_extra_section_additional_javascript" class="addtoany_extra_section" role="region">
				<label for="A2A_SHARE_SAVE_additional_js_variables">
					<p><?php _e('Below you can add special JavaScript code for AddToAny.', 'add-to-any'); ?>
					<?php _e("Advanced users should explore AddToAny's <a href=\"https://www.addtoany.com/buttons/customize/wordpress\" target=\"_blank\">additional options</a>.", 'add-to-any'); ?></p>
				</label>
				<p>
					<textarea name="A2A_SHARE_SAVE_additional_js_variables" id="A2A_SHARE_SAVE_additional_js_variables" class="code" style="width: 98%; font-size: 12px;" rows="6" cols="50"><?php if ( isset( $options['additional_js_variables'] ) ) echo esc_textarea( $options['additional_js_variables'] ); ?></textarea>
				</p>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Additional CSS', 'add-to-any'); ?></th>
			<td><fieldset id="addtoany_extra_section_additional_css" class="addtoany_extra_section" role="region">
				<label for="A2A_SHARE_SAVE_additional_css">
					<p><?php _e('Below you can add special CSS code for AddToAny.', 'add-to-any'); ?>
					<?php _e("Advanced users should explore AddToAny's <a href=\"https://www.addtoany.com/buttons/customize/wordpress\" target=\"_blank\">additional options</a>.", 'add-to-any'); ?></p>
				</label>
				<p>
					<textarea name="A2A_SHARE_SAVE_additional_css" id="A2A_SHARE_SAVE_additional_css" class="code" style="width: 98%; font-size: 12px;" rows="6" cols="50"><?php if ( isset( $options['additional_css'] ) ) echo esc_textarea( $options['additional_css'] ); ?></textarea>
				</p>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Advanced Options', 'add-to-any'); ?></th>
			<td><fieldset id="addtoany_extra_section_advanced_options" class="addtoany_extra_section" role="region">
				<label for="A2A_SHARE_SAVE_custom_icons">
					<input name="A2A_SHARE_SAVE_custom_icons" id="A2A_SHARE_SAVE_custom_icons" type="checkbox"<?php if ( isset( $options['custom_icons'] ) && $options['custom_icons'] == 'url' ) echo ' checked="checked"'; ?> value="url"/>
				<?php _e('Use custom icons. URL:', 'add-to-any'); ?>
				</label>
				<input name="A2A_SHARE_SAVE_custom_icons_url" type="text" class="code" size="50" style="vertical-align:middle" placeholder="//example.com/blog/uploads/addtoany/icons/custom/" value="<?php if ( isset( $options['custom_icons_url'] ) ) esc_attr_e( $options['custom_icons_url'] ); ?>" />
				<br/>
				<label for="A2A_SHARE_SAVE_custom_icons_type"><?php _e('Filename extension', 'add-to-any'); ?></label>
				<input name="A2A_SHARE_SAVE_custom_icons_type" type="text" class="code" size="5" maxlength="4" placeholder="png" value="<?php if ( isset( $options['custom_icons_type'] ) ) esc_attr_e( $options['custom_icons_type'] ); else echo 'png'; ?>" />
				<label for="A2A_SHARE_SAVE_custom_icons_width"><?php _e('Width'); ?></label>
				<input name="A2A_SHARE_SAVE_custom_icons_width" type="number" max="300" min="10" maxlength="3" step="2" oninput="if(this.value.length > 3) this.value=this.value.slice(0, 3)" id="A2A_SHARE_SAVE_custom_icons_width" value="<?php if ( isset( $options['custom_icons_width'] ) ) esc_attr_e( $options['custom_icons_width'] ); ?>" class="small-text" />
				<label for="A2A_SHARE_SAVE_custom_icons_height"><?php _e('Height'); ?></label>
				<input name="A2A_SHARE_SAVE_custom_icons_height" type="number" max="300" min="10" maxlength="3" step="2" oninput="if(this.value.length > 3) this.value=this.value.slice(0, 3)" id="A2A_SHARE_SAVE_custom_icons_height" value="<?php if ( isset( $options['custom_icons_height'] ) ) esc_attr_e( $options['custom_icons_height'] ); ?>" class="small-text" />
				<p class="description">
					<?php _e("Specify the URL of the directory containing your custom icons. For example, a URL of <code>//example.com/blog/uploads/addtoany/icons/custom/</code> containing <code>facebook.png</code> and <code>twitter.png</code>. Be sure that custom icon filenames match the icon filenames in <code>plugins/add-to-any/icons</code>. For AddToAny's Universal Button, select Image URL and specify the URL of your AddToAny universal share icon (<a href=\"#\" onclick=\"document.getElementsByName('A2A_SHARE_SAVE_button_custom')[0].focus();return false\">above</a>).", 'add-to-any'); ?>
				</p>
				<br/>
				<label for="A2A_SHARE_SAVE_cache">
					<input name="A2A_SHARE_SAVE_cache" id="A2A_SHARE_SAVE_cache" type="checkbox"<?php if ( isset( $options['cache'] ) && $options['cache'] == '1' ) echo ' checked="checked"'; ?> value="1"/>
				<?php _e('Cache AddToAny locally with daily cache updates', 'add-to-any'); ?>
				</label>
				<p class="description">
					<?php _e("Most sites should not use this option. By default, AddToAny loads asynchronously and most efficiently. Since many visitors will have AddToAny cached in their browser already, serving AddToAny locally from your site will be slower for those visitors. If local caching is enabled, be sure to set far future cache/expires headers for image files in your <code>uploads/addtoany</code> directory.", 'add-to-any'); ?>
				</p>
			</fieldset></td>
			</tr>
		<?php endif; ?>
		
		</table>		
		
		<?php if ( 'floating' == $current_screen ) : ?>
		
		<p><?php _e('AddToAny &quot;floating&quot; share buttons stay in a fixed position even when the user scrolls.', 'add-to-any'); ?></p>
		<p><?php _e('Large icons from your currently selected buttons are displayed in your floating bar(s). 3rd party buttons (Like, Tweet, etc.) are not displayed.', 'add-to-any'); ?></p>
		
		<h3><?php _e('Vertical Buttons', 'add-to-any'); ?></h3>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e("Placement", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_vertical" value="left_docked"<?php if ( isset( $options['floating_vertical'] ) && 'left_docked' == $options['floating_vertical'] ) echo ' checked="checked"'; ?>> <?php _e('Left docked', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_vertical" value="right_docked"<?php if ( isset( $options['floating_vertical'] ) && 'right_docked' == $options['floating_vertical'] ) echo ' checked="checked"'; ?>> <?php _e('Right docked', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_vertical" value="none"<?php if ( ! isset( $options['floating_vertical'] ) || 'none' == $options['floating_vertical'] ) echo ' checked="checked"'; ?>> <?php _e('None', 'add-to-any'); ?></label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Responsiveness", 'add-to-any'); ?></th>
			<td><fieldset>
				<label>
					<input id="A2A_SHARE_SAVE_floating_vertical_responsive" name="A2A_SHARE_SAVE_floating_vertical_responsive" type="checkbox"<?php 
						if ( ! isset( $options['floating_vertical_responsive'] ) || $options['floating_vertical_responsive'] != '-1' ) echo ' checked="checked"'; ?> value="1" />
					Only display when screen is larger than <input name="A2A_SHARE_SAVE_floating_vertical_responsive_max_width" type="number" value="<?php if ( isset( $options['floating_vertical_responsive_max_width'] ) ) esc_attr_e( $options['floating_vertical_responsive_max_width'] ); else echo '980'; ?>" class="small-text" /> pixels wide
				</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Position", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_vertical_position" type="number" value="<?php if ( isset( $options['floating_vertical_position'] ) ) esc_attr_e( $options['floating_vertical_position'] ); else echo '100'; ?>" class="small-text" /> pixels from top</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Offset", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_vertical_offset" type="number" value="<?php if ( isset( $options['floating_vertical_offset'] ) ) esc_attr_e( $options['floating_vertical_offset'] ); else echo '0'; ?>" class="small-text" /> pixels from left or right</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Icon Size", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_vertical_icon_size" type="number" max="300" min="10" maxlength="3" step="2" oninput="if(this.value.length > 3) this.value=this.value.slice(0, 3)" placeholder="32" value="<?php if ( isset( $options['floating_vertical_icon_size'] ) ) esc_attr_e( $options['floating_vertical_icon_size'] ); else echo '32'; ?>" class="small-text"> pixels</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Background', 'add-to-any'); ?></th>
			<td><fieldset>
				<label>
					<select class="addtoany_icon_color" name="A2A_SHARE_SAVE_floating_vertical_bg">
						<option value="transparent"<?php _a2a_selected_attr('transparent', 'floating_vertical_bg', $options); ?>>Transparent</option>
						<option value="custom"<?php _a2a_selected_attr('custom', 'floating_vertical_bg', $options); ?>>Custom&#8230;</option>
					</select>
				</label>
				<div class="color-field-container"><input name="A2A_SHARE_SAVE_floating_vertical_bg_color" class="color-field" type="text" value="<?php echo ! empty( $options['floating_vertical_bg_color'] ) ? esc_attr( $options['floating_vertical_bg_color'] ) : '#ffffff'; ?>" data-default-color="#ffffff"></div>
			</fieldset></td>
			</tr>
		</table>
			
		<h3><?php _e('Horizontal Buttons', 'add-to-any'); ?></h3>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e("Placement", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_horizontal" value="left_docked"<?php if ( isset( $options['floating_horizontal'] ) && 'left_docked' == $options['floating_horizontal'] ) echo ' checked="checked"'; ?>> <?php _e('Left docked', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_horizontal" value="right_docked"<?php if ( isset( $options['floating_horizontal'] ) && 'right_docked' == $options['floating_horizontal'] ) echo ' checked="checked"'; ?>> <?php _e('Right docked', 'add-to-any'); ?></label>
				<br>
				<label><input type="radio" name="A2A_SHARE_SAVE_floating_horizontal" value="none"<?php if ( ! isset( $options['floating_horizontal'] ) || 'none' == $options['floating_horizontal'] ) echo ' checked="checked"'; ?>> <?php _e('None', 'add-to-any'); ?></label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Responsiveness", 'add-to-any'); ?></th>
			<td><fieldset>
				<label>
					<input id="A2A_SHARE_SAVE_floating_horizontal_responsive" name="A2A_SHARE_SAVE_floating_horizontal_responsive" type="checkbox"<?php 
						if ( ! isset( $options['floating_horizontal_responsive'] ) || $options['floating_horizontal_responsive'] != '-1' ) echo ' checked="checked"'; ?> value="1" />
					Only display when screen is smaller than <input name="A2A_SHARE_SAVE_floating_horizontal_responsive_min_width" type="number" value="<?php if ( isset( $options['floating_horizontal_responsive_min_width'] ) ) esc_attr_e( $options['floating_horizontal_responsive_min_width'] ); else echo '981'; ?>" class="small-text" /> pixels wide
				</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Position", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_horizontal_position" type="number" value="<?php if ( isset( $options['floating_horizontal_position'] ) ) esc_attr_e( $options['floating_horizontal_position'] ); else echo '0'; ?>" class="small-text" /> pixels from left or right</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Offset", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_horizontal_offset" type="number" value="<?php if ( isset( $options['floating_horizontal_offset'] ) ) esc_attr_e( $options['floating_horizontal_offset'] ); else echo '0'; ?>" class="small-text" /> pixels from bottom</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e("Icon Size", 'add-to-any'); ?></th>
			<td><fieldset>
				<label><input name="A2A_SHARE_SAVE_floating_horizontal_icon_size" type="number" max="300" min="10" maxlength="3" step="2" oninput="if(this.value.length > 3) this.value=this.value.slice(0, 3)" placeholder="32" value="<?php if ( isset( $options['floating_horizontal_icon_size'] ) ) esc_attr_e( $options['floating_horizontal_icon_size'] ); else echo '32'; ?>" class="small-text"> pixels</label>
			</fieldset></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e('Background', 'add-to-any'); ?></th>
			<td><fieldset>
				<label>
					<select class="addtoany_icon_color" name="A2A_SHARE_SAVE_floating_horizontal_bg">
						<option value="transparent"<?php _a2a_selected_attr('transparent', 'floating_horizontal_bg', $options); ?>>Transparent</option>
						<option value="custom"<?php _a2a_selected_attr('custom', 'floating_horizontal_bg', $options); ?>>Custom&#8230;</option>
					</select>
				</label>
				<div class="color-field-container"><input name="A2A_SHARE_SAVE_floating_horizontal_bg_color" class="color-field" type="text" value="<?php echo ! empty( $options['floating_horizontal_bg_color'] ) ? esc_attr( $options['floating_horizontal_bg_color'] ) : '#ffffff'; ?>" data-default-color="#ffffff"></div>
			</fieldset></td>
			</tr>
		</table>
		
		<?php endif; ?>
		
		</table>
		
		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'add-to-any' ) ?>" />
			<input id="A2A_SHARE_SAVE_reset_options" type="submit" name="Reset" onclick="return confirm('<?php _e('Are you sure you want to delete all AddToAny options?', 'add-to-any' ) ?>')" value="<?php _e('Reset', 'add-to-any' ) ?>" />
		</p>
	
	</form>
	
	<h2><?php _e('Like this plugin?','add-to-any'); ?></h2>
	<p><?php _e('<a href="https://wordpress.org/support/plugin/add-to-any/reviews/#new-post" target="_blank">Give it a 5 star rating</a> on WordPress.org.','add-to-any'); ?></p>
	<p><?php _e('<a href="https://www.addtoany.com/share#title=WordPress%20Share%20Plugin%20by%20AddToAny.com&amp;url=https%3A%2F%2Fwordpress.org%2Fplugins%2Fadd-to-any%2F">Share it</a> and follow <a href="https://www.addtoany.com/">AddToAny</a> on <a href="https://www.facebook.com/AddToAny" target="_blank">Facebook</a> &amp; <a href="https://twitter.com/AddToAny" target="_blank">Twitter</a>.','add-to-any'); ?></p>
	
	<h2><?php _e('Need support?','add-to-any'); ?></h2>
	<p><?php _e('See the <a href="https://wordpress.org/plugins/add-to-any/faq/">FAQs</a>.','add-to-any'); ?></p>
	<p><?php _e('Search the <a href="https://wordpress.org/support/plugin/add-to-any">support forums</a>.','add-to-any'); ?></p>
	</div>
	
	<script src="http<?php if ( is_ssl() ) echo 's'; ?>://static.addtoany.com/menu/page.js"></script>
	<script>
	if ( window.a2a && a2a.svg_css ) a2a.svg_css();
	jQuery(document).ready( function() { if ( ! window.a2a) jQuery('<div class="error"><p><strong>Something is preventing AddToAny from loading. Try disabling content blockers such as ad-blocking add-ons, or try another web browser.</strong></p></div>').insertBefore('.nav-tab-wrapper:eq(0)'); });	
	</script>

<?php

}

// Admin page header
function A2A_SHARE_SAVE_admin_head() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'addtoany' ) {
		
		$options = get_option( 'addtoany_options', array() );
		
	?>
	<script type="text/javascript"><!--
	jQuery(document).ready(function(){
		
		// Add color picker
		jQuery('.color-field').wpColorPicker();
		
		function show_color_picker_for_custom(arg_1){
			var $this = jQuery(this);
			var $colorFieldParent = $this.parent().next('div').first();
			
			if ('custom' === $this.val()) {
				// If first argument is a number, indicating $.each() is the caller
				if (typeof arg_1 === 'number') {
					$colorFieldParent.fadeIn('fast');
				} else {
					$colorFieldParent.slideDown('fast');
				}
			} else {
				$colorFieldParent.hide();
			}
		}
		
		// Show color picker when "Custom" color is selected
		jQuery('select.addtoany_icon_color').bind('change click', show_color_picker_for_custom).each(show_color_picker_for_custom);
		
		// Toggle child options of 'Display in posts'
		jQuery('#A2A_SHARE_SAVE_display_in_posts').bind('change click', function(e){
			if (jQuery(this).is(':checked'))
				jQuery('.A2A_SHARE_SAVE_child_of_display_in_posts').attr('checked', true).attr('disabled', false);
			else 
				jQuery('.A2A_SHARE_SAVE_child_of_display_in_posts').attr('checked', false).attr('disabled', true);
		});
		
		// Update button position labels/values universally in Placement section 
		jQuery('select[name="A2A_SHARE_SAVE_position"]').bind('change click', function(e){
			var $this = jQuery(this);
			jQuery('select[name="A2A_SHARE_SAVE_position"]').not($this).val($this.val());
			
			jQuery('.A2A_SHARE_SAVE_position').html($this.find('option:selected').html());
		});
	
		var to_input = function(this_sortable){
			// Clear any previous hidden inputs for storing chosen services
			// and special service options
			jQuery('input.addtoany_hidden_options').remove();
			
			var services_array = jQuery(this_sortable).sortable('toArray'),
				services_size = services_array.length;
			if (services_size < 1) return;
			
			for (var i=0, service_name, show_count_value, fb_verb_value; i < services_size; i++) {
				if(services_array[i]!='') { // Exclude dummy icon
					jQuery('#addtoany_admin_form').append('<input class="addtoany_hidden_options" name="A2A_SHARE_SAVE_active_services[]" type="hidden" value="'+services_array[i]+'"/>');
					
					// Special service options?
					service_name = services_array[i].substr(7);
					if (service_name == 'facebook_like' || service_name == 'twitter_tweet' || service_name == 'pinterest_pin') {
						if (service_name == 'pinterest_pin') {
							show_count_value = (jQuery('#' + services_array[i] + '_show_count').is(':checked')) ? '1' : '-1' ;
							jQuery('#addtoany_admin_form').append('<input class="addtoany_hidden_options" name="addtoany_' + service_name + '_show_count" type="hidden" value="' + show_count_value + '"/>');
						}
						
						if (service_name == 'facebook_like') {
							fb_verb_value = (jQuery('#' + services_array[i] + '_verb').val() == 'recommend') ? 'recommend' : 'like';
							jQuery('#addtoany_admin_form').append('<input class="addtoany_hidden_options" name="addtoany_' + service_name + '_verb" type="hidden" value="' + fb_verb_value + '"/>');
						}
					// AddToAny counters
					} else if ( jQuery.inArray( service_name, ['facebook', 'pinterest', 'linkedin', 'reddit'] ) > -1 ) {
						show_count_value = (jQuery('#' + services_array[i] + '_show_count').is(':checked')) ? '1' : '-1' ;
						jQuery('#addtoany_admin_form').append('<input class="addtoany_hidden_options" name="addtoany_' + service_name + '_show_count" type="hidden" value="' + show_count_value + '"/>');
					}
				}
			}
		};
	
		jQuery('#addtoany_services_sortable').sortable({
			forcePlaceholderSize: true,
			items: 'li:not(#addtoany_show_services, .dummy)',
			placeholder: 'ui-sortable-placeholder',
			opacity: .6,
			tolerance: 'pointer',
			update: function(){to_input(this)}
		});
		
		// Service click = move to sortable list
		var moveToSortableList = function(){
			var configurable_html = '',
				this_service = jQuery(this),
				this_service_name = this_service.attr('id').substr(7),
				this_service_is_special = this_service.hasClass('addtoany_special_service'),
				this_service_is_3p = this_service.hasClass('addtoany_3p_button'),
				checked = '',
				special_options_html = '';
			
			if (jQuery('#addtoany_services_sortable li').not('.dummy').length == 0)
				jQuery('#addtoany_services_sortable').find('.dummy').hide();
				
			if (this_service_is_special) {
				if ('facebook_like' == this_service_name) {
					if (service_options[this_service_name] && service_options[this_service_name].verb)
						checked = ' selected="selected"';
					special_options_html = '<select id="' + this_service.attr('id') + '_verb" name="' + this_service.attr('id') + '_verb">'
						+ '<option value="like">Like</option>'
						+ '<option' + checked + ' value="recommend">Recommend</option>'
						+ '</select>';
				} else {
					// Common "Show count" for facebook, pinterest, pinterest_pin, etc.
					if (service_options[this_service_name] && service_options[this_service_name].show_count) {
						checked = ' checked="checked"';
					}
					special_options_html = '<label><input' + checked + ' id="' + this_service.attr('id') + '_show_count" name="' + this_service.attr('id') + '_show_count" type="checkbox" value="1"> Show count</label>';
				}
				
				if (special_options_html.length > 0) {
					configurable_html = '<span class="down_arrow"></span><br style="clear:both"/><div class="special_options">' + special_options_html + '</div>';
				}
			}
			
			var new_service = this_service.toggleClass('addtoany_selected')
					.unbind('click', moveToSortableList)
					.bind('click', moveToSelectableList)
					.clone();
			
			new_service.data('a2a_32_icon_html', this_service.find('img').clone().attr('alt', this_service.attr('title')).wrap('<p>').parent().html() + configurable_html);
				
			new_service.html( new_service.data('a2a_32_icon_html') )
				.click(function(){
					jQuery(this).not('.addtoany_special_service_options_selected').find('.special_options').slideDown('fast').parent().addClass('addtoany_special_service_options_selected');
				})
				.hide()
				.insertBefore('#addtoany_services_sortable .dummy')
				.fadeIn('fast');
			
			this_service.attr( 'id', 'old_'+this_service.attr('id') );
		};
		
		// Service click again = move back to selectable list
		var moveToSelectableList = function(){
			jQuery(this).toggleClass('addtoany_selected')
			.unbind('click', moveToSelectableList)
			.bind('click', moveToSortableList);
	
			jQuery( '#'+jQuery(this).attr('id').substr(4).replace(/\./, '\\.') )
			.hide('fast', function(){
				jQuery(this).remove();
			});
			
			
			if( jQuery('#addtoany_services_sortable li').not('.dummy').length==1 )
				jQuery('#addtoany_services_sortable').find('.dummy').show();
			
			jQuery(this).attr('id', jQuery(this).attr('id').substr(4));
		};
		
		// Service click = move to sortable list
		jQuery('#addtoany_services_selectable li').bind('click', moveToSortableList);
		
		// Form submit = get sortable list
		jQuery('#addtoany_admin_form').submit(function(){to_input('#addtoany_services_sortable')});
		
		// Auto-select active services
		<?php
		$admin_services_saved = isset( $_POST['A2A_SHARE_SAVE_active_services'] ) && isset( $_POST['Submit'] );
		
		if ( $admin_services_saved ) {
			$active_services = $_POST['A2A_SHARE_SAVE_active_services'];
		} elseif ( ! $admin_services_saved && isset( $options['active_services'] ) ) {
			$active_services = $options['active_services'];
		} else {
			// Use default services if options have not been set yet (and no services were just saved in the form)
			$active_services = array( 'facebook', 'twitter', 'google_plus' );
		}
		
		$active_services_last = end($active_services);
		if($admin_services_saved)
			$active_services_last = substr($active_services_last, 7); // Remove a2a_wp_
		$active_services_quoted = '';
		$counters_enabled_js = '';
		foreach ($active_services as $service) {
			if ( $admin_services_saved )
				$service = substr( $service, 7 ); // Remove a2a_wp_
			$active_services_quoted .= json_encode( $service );
			if ( $service != $active_services_last )
				$active_services_quoted .= ',';
			
			// AddToAny counter enabled?
			if ( in_array( $service, array( 'facebook', 'pinterest', 'linkedin', 'reddit' ) ) ) {
				if ( isset( $_POST['addtoany_' . $service . '_show_count'] ) && $_POST['addtoany_' . $service . '_show_count'] == '1'
					|| ! isset( $_POST['addtoany_' . $service . '_show_count'] )
					&& isset( $options['special_' . $service . '_options'] )
					&& isset( $options['special_' . $service . '_options']['show_count'] ) 
					&& $options['special_' . $service . '_options']['show_count'] == '1' 
				) {
					$counters_enabled_js .= 'service_options.' . $service . ' = {show_count: 1};';
				}
			}
		}
		?>
		var services = [<?php echo $active_services_quoted; ?>],
			service_options = {};
		
		<?php		
		// Special service options (enabled counters) if any
		echo $counters_enabled_js;
		
		if ( isset( $_POST['addtoany_facebook_like_verb'] ) && $_POST['addtoany_facebook_like_verb'] == 'recommend'
			|| ! isset( $_POST['addtoany_facebook_like_verb'] ) 
			&& isset( $options['special_facebook_like_options'] ) && $options['special_facebook_like_options']['verb'] == 'recommend' ) {
			?>service_options.facebook_like = {verb: 'recommend'};<?php
		}
		if ( isset( $_POST['addtoany_pinterest_pin_show_count'] ) && $_POST['addtoany_pinterest_pin_show_count'] == '1'
			|| ! isset( $_POST['addtoany_pinterest_pin_show_count'] )
			&& isset( $options['special_pinterest_pin_options'] ) && $options['special_pinterest_pin_options']['show_count'] == '1' ) {
			?>service_options.pinterest_pin = {show_count: 1};<?php
		}
		?>
		
		jQuery.each(services, function(i, val){
			jQuery('#a2a_wp_'+val).click();
		});
		
		// Add/Remove Services button
		jQuery('#addtoany_services_sortable .dummy:first').after('<li id="addtoany_show_services"><?php _e('Add/Remove Services', 'add-to-any'); ?> &#187;</li>');
		jQuery('#addtoany_show_services').click(function(e) {
			jQuery('#addtoany_services_selectable, #addtoany_services_info, #addtoany_services_tip').slideDown('fast');
			jQuery(this).fadeOut('fast');
		});
		
		// Hide each 'extra' section if it is not .addtoany_show_extra, or it does not have a textarea with a value
		jQuery('.addtoany_extra_section:not(.addtoany_show_extra, :has(textarea:not(:empty)))').hide()
			// Keep it accessible
			.attr('aria-expanded', 'false')
			.attr('tabindex', '-1')
			.each(function(index) { 
				// Insert an accessible 'show section' button
				jQuery(this).after('<fieldset><button class="addtoany_show_more_button button" type="button" aria-controls="' + jQuery(this).attr('id') + '"><span class="dashicons dashicons-arrow-down"></span></button></fieldset>');
			});
		// Handle click on 'show section' button
		jQuery('.addtoany_extra_section').next('fieldset').find('button').click(function(e) {
			jQuery(this).hide('fast');
			jQuery(this).parent().prev('fieldset').slideDown('fast')
				.attr('aria-expanded', 'true')
				.focus();
		});
	});
	--></script>

	<style>
	.color-field-container,
	.CodeMirror-hints{display:none;}
	
	.ui-sortable-placeholder{background-color:transparent;border:1px dashed #CCC !important;}
	.addtoany_admin_list{list-style:none;padding:0;margin:0;}
	.addtoany_admin_list li{border-radius:6px;}
	
	#addtoany_services_selectable{clear:left;display:none;}
	#addtoany_services_selectable li{cursor:pointer;float:left;width:150px;font-size:12px;line-height:24px;margin:0;padding:6px;border:1px solid transparent;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;}
	#addtoany_services_selectable li:hover, #addtoany_services_selectable li.addtoany_selected{border:1px solid #CCC;background-color:#FFF;}
	#addtoany_services_selectable li.addtoany_selected:hover{border-color:#F00;}
	#addtoany_services_selectable li:active{border:1px solid #000;}
	#addtoany_services_selectable img{border-radius:4px;margin:0 6px;width:24px;height:24px;border:0;vertical-align:top;}
	#addtoany_services_selectable .addtoany_3p_button{padding:8px 6px 4px;}
	#addtoany_services_selectable .addtoany_3p_button img{border-radius:0;width:auto;height:20px;}
	
	#addtoany_services_sortable li, #addtoany_services_sortable li.dummy:hover{cursor:move;float:left;padding:14px 10px;border:1px solid transparent;}
	#addtoany_services_sortable li:hover{border:1px solid #CCC;background-color:#FFF;}
	#addtoany_services_sortable li.dummy, #addtoany_services_sortable li.dummy:hover{cursor:auto;background-color:transparent;}
	#addtoany_services_sortable img{width:32px;height:32px;border:0;border-radius:4px;vertical-align:middle;}
	#addtoany_services_sortable .addtoany_3p_button img{width:auto;height:20px;float:left;}
	#addtoany_services_sortable .addtoany_special_service {position: relative;}
	#addtoany_services_sortable .addtoany_special_service span.down_arrow{background:url(<?php echo admin_url( '/images/arrows.png' ); ?>) no-repeat 2px 9px;bottom: -6px;left: 50%;margin:0 0 0 -10px;position:absolute;height:29px;width:14px;}
	#addtoany_services_sortable .addtoany_special_service div.special_options{display:none;font-size:11px;margin-top:9px;}
	#addtoany_services_sortable .addtoany_special_service_options_selected{border:1px solid #CCC;background-color:#FFF;}
	#addtoany_services_sortable .addtoany_special_service_options_selected span.down_arrow{display:none;}
	
	li#addtoany_show_services{border:1px solid #DFDFDF;background-color:#FFF;cursor:pointer;line-height:32px;margin-left:9px;}
	li#addtoany_show_services:hover{border:1px solid #CCC;}
	#addtoany_services_info, #addtoany_services_tip{clear:left;display:none;margin:12px;padding:10px 0;}
	#addtoany_services_tip{padding:20px 0 0;}
	#addtoany_services_tip img{border-radius:4px;background-color:#444;}
	
	/* No outline during ARIA focus */
	.addtoany_extra_section {
		outline: 0;
	}
	/* Adjust position of arrow icon on show more button */
	.addtoany_show_more_button .dashicons {
		position: relative;
		right: 1px;
		top: 2px;
	}
	@media screen and (max-width: 782px) {
		.addtoany_show_more_button .dashicons {
			top: 0px;
		}
	}
	
	.a2a_kit_size_32.addtoany_override .a2a_svg,
	.a2a_kit_size_32.addtoany_override img { 
		border-radius: 4px;
		display:inline-block;
		height: 32px;
		vertical-align:middle;
		width: 32px;
	}
	.a2a_kit_size_32.addtoany_override img {
		background-color: #0166FF;
		margin-left: 9px;
	}
	
	#A2A_SHARE_SAVE_reset_options{color:red;margin-left: 15px;}
	</style>
<?php

	}
}

add_filter( 'admin_head', 'A2A_SHARE_SAVE_admin_head' );

function addtoany_admin_scripts( $current_admin_page ) {
	if ( 'settings_page_addtoany' !== $current_admin_page ) {
		return;
	}
	
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-color-picker' );
	
	// If current screen is the default tab and WordPress >= 4.9
	if ( empty( $_GET['action'] ) && function_exists( 'wp_enqueue_code_editor' ) ) {
		// Additional JavaScript editor.
		// Enqueue code editor and settings for manipulating JavaScript.
		$settings = wp_enqueue_code_editor( array(
			'type' => 'text/javascript',
			'jshint' => array(
				'globals' => array( 'a2a_config' => true ),
				'quotmark' => false,
				'undef' => false,
				'unused' => false,
			),
			'codemirror' => array( 'lineNumbers' => false ),
		) );
		
		// If user hasn't disabled CodeMirror.
		if ( false !== $settings ) {
			wp_add_inline_script(
				'code-editor',
				sprintf(
					'jQuery( function() { var wpCodeEditor = wp.codeEditor.initialize( "A2A_SHARE_SAVE_additional_js_variables", %s ); window.wpa2aCodeEditorJS = wpCodeEditor.codemirror; } );',
					wp_json_encode( $settings )
				)
			);
			
			// Additional CSS editor.
			// Enqueue code editor and settings for manipulating CSS.
			$settings = wp_enqueue_code_editor( array(
				'type' => 'text/css',
				'codemirror' => array( 'lineNumbers' => false ),
			) );
			
			wp_add_inline_script(
				'code-editor',
				sprintf(
					'jQuery( function() { var wpCodeEditor = wp.codeEditor.initialize( "A2A_SHARE_SAVE_additional_css", %s ); window.wpa2aCodeEditorCSS = wpCodeEditor.codemirror; } );',
					wp_json_encode( $settings )
				)
			);
		}
	}
}

add_action( 'admin_enqueue_scripts', 'addtoany_admin_scripts' );