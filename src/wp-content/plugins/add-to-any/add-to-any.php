<?php
/*
Plugin Name: AddToAny Share Buttons
Plugin URI: https://www.addtoany.com/
Description: Share buttons for your pages including AddToAny's universal sharing button, Facebook, Twitter, Google+, Pinterest, WhatsApp and many more.
Version: 1.7.36
Author: AddToAny
Author URI: https://www.addtoany.com/
Text Domain: add-to-any
Domain Path: /languages
*/

// Explicitly globalize to support bootstrapped WordPress
global $A2A_locale, $A2A_FOLLOW_services,
	$A2A_SHARE_SAVE_options, $A2A_SHARE_SAVE_plugin_dir, $A2A_SHARE_SAVE_plugin_url, 
	$A2A_SHARE_SAVE_services, $A2A_SHARE_SAVE_amp_icons_css;

$A2A_SHARE_SAVE_plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) );
$A2A_SHARE_SAVE_plugin_url = untrailingslashit( plugin_dir_url( __FILE__ ) );

// Set AddToAny locale (JavaScript)
$A2A_locale = ! isset ( $A2A_locale ) ? '' : $A2A_locale;
// Set plugin options
$A2A_SHARE_SAVE_options = get_option( 'addtoany_options', array() );

include_once $A2A_SHARE_SAVE_plugin_dir . '/addtoany.compat.php';
include_once $A2A_SHARE_SAVE_plugin_dir . '/addtoany.services.php';

function A2A_SHARE_SAVE_init() {
	global $A2A_SHARE_SAVE_plugin_dir,
		$A2A_SHARE_SAVE_options;
	
	// Load the textdomain for translations
	load_plugin_textdomain( 'add-to-any', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	
	// Update plugin options	
	$options = $A2A_SHARE_SAVE_options;
	$old_buttons = array( 
		'share_save_256_24.gif|256|24', 'share_save_171_16.gif|171|16', 'share_save_120_16.gif|120|16',
		'share_save_256_24.png|256|24', 'share_save_171_16.png|171|16', 'share_save_120_16.png|120|16',
		'share_16_16.png|16|16', 'favicon.png|16|16',
	);
	
	// If old button enabled
	if ( ! empty( $options['button'] ) && in_array( $options['button'], $old_buttons ) ) {
		include_once $A2A_SHARE_SAVE_plugin_dir . '/addtoany.update.php';
		addtoany_update_options();
	}
}
add_filter( 'init', 'A2A_SHARE_SAVE_init' );

function A2A_SHARE_SAVE_link_vars( $args = array() ) {
	global $post;
	
	$linkname = empty( $args['linkname'] ) ? '' : $args['linkname'];
	$linkurl = empty( $args['linkurl'] ) ? '' : $args['linkurl'];
	$linkmedia = empty( $args['linkmedia'] ) ? '' : $args['linkmedia'];
	$use_current_page = isset( $args['use_current_page'] ) ? $args['use_current_page'] : false;

	// Set linkname if needed, and not a Follow kit
	if ( ! $linkname && empty( $args['is_follow'] ) ) {
		if ( $use_current_page ) {
			$linkname = is_home() || is_front_page() ? get_bloginfo( 'name' ) : rtrim( wp_title( '', false, 'right' ) );
		} elseif ( isset( $post ) ) {
			$linkname = html_entity_decode( strip_tags( get_the_title( $post->ID ) ), ENT_QUOTES, 'UTF-8' );
		} else {
			$linkname = '';
		}
	}
	
	$linkname_enc = rawurlencode( $linkname );
	
	// Set linkurl if needed, and not a Follow kit
	if ( ! $linkurl && empty( $args['is_follow'] ) ) {
		if ( $use_current_page ) {
			$linkurl = esc_url_raw( home_url( $_SERVER['REQUEST_URI'] ) );
		} elseif ( isset( $post ) ) {
			$linkurl = get_permalink( $post->ID );
		} else {
			$linkurl = '';
		}
	}
	
	$linkurl_enc = rawurlencode( $linkurl );
	
	// Set linkmedia (only applies to services that explicitly accept media; Pinterest does, most do not)
	$linkmedia_enc = ! empty( $args['linkmedia'] ) ? rawurlencode( $args['linkmedia'] ) : '';
	
	return compact( 'linkname', 'linkname_enc', 'linkurl', 'linkurl_enc', 'linkmedia', 'linkmedia_enc', 'use_current_page' );
}

// Combine ADDTOANY_SHARE_SAVE_ICONS and ADDTOANY_SHARE_SAVE_BUTTON
function ADDTOANY_SHARE_SAVE_KIT( $args = array() ) {
	$options = get_option( 'addtoany_options', array() );
	
	$args = array_merge( $args, A2A_SHARE_SAVE_link_vars( $args ) ); // linkname_enc, etc.
	
	$defaults = array(
		'output_later'     => false,
		'icon_size'        => isset( $options['icon_size'] ) ? $options['icon_size'] : '32',
		'is_follow'        => false,
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	// If universal button disabled, and not manually disabled through args
	if ( isset( $options['button'] ) && $options['button'] == 'NONE' && ! isset( $args['no_universal_button'] ) ) {
		// Pass this setting on to ADDTOANY_SHARE_SAVE_BUTTON
		// (and only via this ADDTOANY_SHARE_SAVE_KIT function because it is used for automatic placement)
		$args['no_universal_button'] = true;
	}
	
	// Custom icons enabled?
	$custom_icons = ( isset( $options['custom_icons'] ) && $options['custom_icons'] == 'url' && isset( $options['custom_icons_url'] ) ) ? true : false;
	
	$kit_additional_classes = '';
	$kit_data_media = empty( $args['linkmedia'] ) ? '' : ' data-a2a-media="' . esc_attr( $args['linkmedia'] ) . '"';
	$kit_data_title = empty( $args['linkname'] ) || $args['use_current_page'] ? '' : ' data-a2a-title="' . esc_attr( $args['linkname'] ) . '"';
	$kit_data_url = empty( $args['linkurl'] ) || $args['use_current_page'] ? '' : ' data-a2a-url="' . esc_attr( $args['linkurl'] ) . '"';
	$kit_data_scroll_show = empty( $args['scroll_show'] ) ? '' : ' data-a2a-scroll-show="' . esc_attr( $args['scroll_show'] ) . '"';
	$kit_style = '';
	
	// Add additional classNames to .a2a_kit
	if ( ! empty( $args['kit_additional_classes'] ) ) {
		// Append space and className(s)
		$kit_additional_classes .= ' ' . $args['kit_additional_classes'];
	}
	
	// Set a2a_kit_size_## class name
	if ( $custom_icons ) {
		// If vertical style (.a2a_vertical_style)
		if ( strpos( $kit_additional_classes, 'a2a_vertical_style' ) !== false ) {
			// Use width (if specified) for .a2a_kit_size_## class name to size default service counters
			$icon_size_classname = isset( $options['custom_icons_width'] ) ? ' a2a_kit_size_' . $options['custom_icons_width'] : '';
		} else {
			// Use height (if specified) for .a2a_kit_size_## class name to size default service counters
			$icon_size_classname = isset( $options['custom_icons_height'] ) ? ' a2a_kit_size_' . $options['custom_icons_height'] : '';
		}
	// a2a_kit_size_## icon size
	} else {
		$icon_size_classname = ' a2a_kit_size_' . $args['icon_size'];
	}
	
	// Add addtoany_list className unless disabled (for floating buttons)
	if ( ! isset( $args['no_addtoany_list_classname'] ) ) {
		$kit_additional_classes .= ' addtoany_list';
	}
	
	// Add style attribute if set
	if ( ! empty( $args['kit_style'] ) ) {
		$kit_style = ' style="' . esc_attr( $args['kit_style'] ) . '"';
	}
	
	if ( ! isset( $args['html_container_open'] ) ) {
		$args['html_container_open'] = '<div class="a2a_kit' . esc_attr( $icon_size_classname . $kit_additional_classes ) . '"'
			. $kit_data_url . $kit_data_title . $kit_data_media . $kit_data_scroll_show . $kit_style . '>';
		$args['is_kit'] = true;
	}
	if ( ! isset( $args['html_container_close'] ) )
		$args['html_container_close'] = "</div>";
	// Close container element in ADDTOANY_SHARE_SAVE_BUTTON, not prematurely in ADDTOANY_SHARE_SAVE_ICONS
	$html_container_close = $args['html_container_close']; // Cache for _BUTTON
	unset($args['html_container_close']); // Avoid passing to ADDTOANY_SHARE_SAVE_ICONS since set in _BUTTON
				
	if ( ! isset( $args['html_wrap_open'] ) )
		$args['html_wrap_open'] = "";
	if ( ! isset( $args['html_wrap_close'] ) )
		$args['html_wrap_close'] = "";
	
	$kit_html = ADDTOANY_SHARE_SAVE_ICONS( $args );
	
	$args['html_container_close'] = $html_container_close; // Re-set because unset above for _ICONS
	unset( $args['html_container_open'] ); // Avoid passing to ADDTOANY_SHARE_SAVE_BUTTON since set in _ICONS
	
	$kit_html .= ADDTOANY_SHARE_SAVE_BUTTON( $args );
	
	if ( true == $args['output_later'] )
		return $kit_html;
	else
		echo $kit_html;
}

function ADDTOANY_SHARE_SAVE_ICONS( $args = array() ) {
	// $args array: output_later, html_container_open, html_container_close, html_wrap_open, html_wrap_close, linkname, linkurl
	
	global $A2A_SHARE_SAVE_services,
		$A2A_FOLLOW_services,
		$A2A_SHARE_SAVE_amp_icons_css;
	
	$options = get_option( 'addtoany_options', array() );
	
	$args = array_merge( $args, A2A_SHARE_SAVE_link_vars( $args ) ); // linkname_enc, etc.
	
	$defaults = array(
		'linkname'             => '',
		'linkurl'              => '',
		'linkmedia'            => '',
		'linkname_enc'         => '',
		'linkurl_enc'          => '',
		'linkmedia_enc'        => '',
		'output_later'         => false,
		'html_container_open'  => '',
		'html_container_close' => '',
		'html_wrap_open'       => '',
		'html_wrap_close'      => '',
		'icon_size'			   => isset( $options['icon_size'] ) ? $options['icon_size'] : '32',
		'is_follow'            => false,
		'no_universal_button'  => false,
		'basic_html'           => false,
		'buttons'              => array(),
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$is_amp = function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ? true : false;
	$amp_css = '.a2a_dd img{background-color:#0166FF;}';
	
	// Large icons except for AMP endpoint
	$large_icons = $is_amp ? false : true;
	
	// Directory of either custom icons or the packaged icons
	if ( isset( $options['custom_icons'] ) && $options['custom_icons'] == 'url' && isset( $options['custom_icons_url'] ) ) {
		// Custom icons expected at a specified URL, i.e. //example.com/blog/uploads/addtoany/icons/custom/
		$icons_dir = $options['custom_icons_url'];
		$icons_type = ( isset( $options['custom_icons_type'] ) ) ? $options['custom_icons_type'] : 'png';
		$icons_width = ( isset( $options['custom_icons_width'] ) ) ? $options['custom_icons_width'] : '';
		$icons_height = ( isset( $options['custom_icons_height'] ) ) ? $options['custom_icons_height'] : '';
		$custom_icons = true;
	} else {
		// Default to local SVGs (not an option currently)
		$icons_dir = plugins_url('/icons/', __FILE__ );
		$icons_type = 'svg';
	}
	
	// If Follow kit
	if ( $args['is_follow'] ) {
		// Make available services extensible via plugins, themes (functions.php), etc.
		$services = apply_filters( 'A2A_FOLLOW_services', $A2A_FOLLOW_services );
		$service_codes = ( is_array( $services ) ) ? array_keys( $services ) : array();
		
		// Services set by "buttons" arg
		$active_services = empty( $args['buttons'] ) ? array() : array_keys( $args['buttons'] );
	// Else Share kit
	} else {
		// Make available services extensible via plugins, themes (functions.php), etc.
		$services = apply_filters( 'A2A_SHARE_SAVE_services', $A2A_SHARE_SAVE_services );
		$service_codes = ( is_array( $services ) ) ? array_keys( $services ) : array();
		
		// Include Facebook Like and Twitter Tweet etc. unless no_special_services arg is true
		if ( ! isset( $args['no_special_services'] ) || false == $args['no_special_services'] ) {
			array_unshift( $service_codes, 'facebook_like', 'twitter_tweet', 'pinterest_pin' );
		}
	
		// Use default services if services have not been selected yet
		$active_services = isset( $options['active_services'] ) ? $options['active_services'] : array( 'facebook', 'twitter', 'email' );
		// Services set by "buttons" arg? Then use "buttons" arg instead
		$active_services = empty( $args['buttons'] ) ? $active_services : $args['buttons'];
	}
	
	$ind_html = "" . $args['html_container_open'];
	
	foreach( $active_services as $active_service ) {
		
		$custom_service = false;
		
		if ( ! in_array( $active_service, $service_codes ) )
			continue;

		if ( $active_service == 'facebook_like' || $active_service == 'twitter_tweet' || $active_service == 'pinterest_pin' ) {
			$special_args = $args;
			$special_args['output_later'] = true;
			$link = ADDTOANY_SHARE_SAVE_SPECIAL( $active_service, $special_args );
		} else {
			$service = $services[ $active_service ];
			$code_name = $active_service;
			$name = $service['name'];
			
			// If Follow kit and HREF specified
			if ( $args['is_follow'] && isset( $service['href'] ) ) {
				$follow_id = $args['buttons'][ $active_service ]['id'];
				$is_url = in_array( parse_url( $follow_id, PHP_URL_SCHEME ), array( 'http', 'https' ) );
				
				// If it's a URL instead of a service ID
				if ( $is_url ) {
					// Just use the given URL instead of the URL template
					$href = $follow_id;
				} else {
					// Replace the ID placeholder in the URL template
					$href = str_replace( '${id}', $follow_id, $service['href'] );
				}
				$href = ( 'feed' == $code_name ) ? $follow_id : $href;
				
				// If icon_url is set, presume custom service
				if ( isset( $service['icon_url'] ) ) {
					$custom_service = true;
				}
			// Else if Share Kit and HREF specified, presume custom service
			} elseif ( isset( $service['href'] ) ) {
				$custom_service = true;
				$href = $service['href'];
				if ( isset( $service['href_js_esc'] ) ) {
					$href_linkurl = str_replace( "'", "\'", $args['linkurl'] );
					$href_linkname = str_replace( "'", "\'", $args['linkname'] );
				} else {
					$href_linkurl = $args['linkurl_enc'];
					$href_linkname = $args['linkname_enc'];
				}
				$href = str_replace( "A2A_LINKURL", $href_linkurl, $href );
				$href = str_replace( "A2A_LINKNAME", $href_linkname, $href );
				$href = str_replace( " ", "%20", $href );
			}
			
			// AddToAny counter enabled?
			$counter_enabled = ( ! $args['is_follow'] // Disable counters on Follow Kits
				&& in_array( $active_service, array( 'facebook', 'pinterest', 'reddit' ) )
				&& isset( $options['special_' . $active_service . '_options'] )
				&& isset( $options['special_' . $active_service . '_options']['show_count'] ) 
				&& $options['special_' . $active_service . '_options']['show_count'] == '1' 
			) ? true : false;
			
			$icon = isset( $service['icon'] ) ? $service['icon'] : 'default'; // Just the icon filename
			$icon_url = isset( $service['icon_url'] ) ? $service['icon_url'] : false;
			$icon_url = $is_amp && ! $icon_url ? 'https://static.addtoany.com/buttons/' . $icon . '.svg' : $icon_url;
			$width_attr = isset( $service['icon_width'] ) ? ' width="' . esc_attr( $service['icon_width'] ) . '"' : ' width="16"';
			$width_attr = $is_amp && ! empty( $args['icon_size'] ) ? ' width="' . esc_attr( $args['icon_size'] ) . '"' : $width_attr;
			$height_attr = isset( $service['icon_height'] ) ? ' height="' . esc_attr( $service['icon_height'] ) . '"' : ' height="16"';
			$height_attr = $is_amp && ! empty( $args['icon_size'] ) ? ' height="' . esc_attr( $args['icon_size'] ) . '"' : $height_attr;
			
			$amp_css .= $is_amp && ! empty( $service['color'] ) ? '.a2a_button_' . esc_attr( $code_name ) . ' img{background-color:#' . $service['color'] . ';}' : '';
			
			$url = isset( $href ) ? $href : 'https://www.addtoany.com/add_to/' . $code_name . '?linkurl=' . $args['linkurl_enc'] .'&amp;linkname=' . $args['linkname_enc'];
			$src = $icon_url ? $icon_url : $icons_dir . $icon . '.' . $icons_type;
			$counter = $counter_enabled ? ' a2a_counter' : '';
			$class_attr = $custom_service ? '' : ' class="a2a_button_' . esc_attr( $code_name ) . $counter . '"';
			$href_attr = $args['basic_html'] && ! isset( $href ) ? '' : ' href="' . esc_attr( $url ) . '"';
			$title_attr = $args['basic_html'] ? '' : ' title="' . esc_attr( $name ) . '"';

			if ( isset( $service['target'] ) ) {
				$target_attr = empty( $service['target'] ) ? '' : ' target="' . esc_attr( $service['target'] ) . '"';
			} elseif ( ! $args['basic_html'] ) {
				$target_attr = ' target="_blank"';
			} else {
				$target_attr = '';
			}
			
			// Use rel="noopener" for links that open in a new tab/window
			$rel_noopener = $custom_service || ! $target_attr ? '' : ' noopener';
			$rel_noopener_only = $rel_noopener || $target_attr ? ' rel="noopener"' : '';
			$rel_attr = $args['is_follow'] ? $rel_noopener_only : ' rel="nofollow' . $rel_noopener . '"'; // ($args['is_follow'] indicates a Follow Kit. 'nofollow' is for search crawlers. Different things)
			$rel_attr = $args['basic_html'] ? '' : $rel_attr;

			// Set dimension attributes if using custom icons and dimension is specified
			if ( isset( $custom_icons ) ) {
				$width_attr = ! empty( $icons_width ) ? ' width="' . $icons_width . '"' : '';
				$height_attr = ! empty( $icons_height ) ? ' height="' . $icons_height . '"' : '';
			}
			
			$link = $args['html_wrap_open'] . "<a$class_attr$href_attr$title_attr$rel_attr$target_attr>";
			$link .= ( $large_icons && ! isset( $custom_icons ) && ! $custom_service ) ? '' : '<img src="' . esc_attr( $src ) . '"' . $width_attr . $height_attr . ' alt="' . esc_attr( $name ) . '">';
			$link .= "</a>" . $args['html_wrap_close'];
		}
		
		$ind_html .= $link;
	}
	
	$ind_html .= $args['html_container_close'];
	
	if ( $is_amp ) {
		$A2A_SHARE_SAVE_amp_icons_css = $amp_css;
		add_action( 'amp_post_template_css', 'addtoany_amp_icons_css' );
	}
	
	if ( true == $args['output_later'] )
		return $ind_html;
	else
		echo $ind_html;
}

function ADDTOANY_SHARE_SAVE_BUTTON( $args = array() ) {
	
	// $args array = output_later, html_container_open, html_container_close, html_wrap_open, html_wrap_close, linkname, linkurl, no_universal_button
	
	$options = get_option( 'addtoany_options', array() );

	$args = array_merge( $args, A2A_SHARE_SAVE_link_vars( $args ) ); // linkname_enc, etc.
	
	$defaults = array(
		'linkname' => '',
		'linkurl' => '',
		'linkmedia' => '',
		'linkname_enc' => '',
		'linkurl_enc' => '',
		'linkmedia_enc' => '',
		'use_current_page' => false,
		'output_later' => false,
		'is_kit' => false,
		'html_container_open' => '',
		'html_container_close' => '',
		'html_wrap_open' => '',
		'html_wrap_close' => '',
		'html_content' => '',
		'button_additional_classes' => '',
		'icon_size'	=> isset( $options['icon_size'] ) ? $options['icon_size'] : '32',
		'no_universal_button' => false,
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	$is_feed = is_feed();
	$is_amp = function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ? true : false;
	$button_class = '';
	$button_data_media = $args['is_kit'] || empty( $args['linkmedia'] ) ? '' : ' data-a2a-media="' . esc_attr( $args['linkmedia'] ) . '"';
	$button_data_title = $args['is_kit'] || empty( $args['linkname'] ) ? '' : ' data-a2a-title="' . esc_attr( $args['linkname'] ) . '"';
	$button_data_url = $args['is_kit'] || empty( $args['linkurl'] ) ? '' : ' data-a2a-url="' . esc_attr( $args['linkurl'] ) . '"';
	$button_target = $is_amp ? ' target="_blank"' : '';
	$button_href_querystring = ($is_feed || $is_amp) ? '#url=' . $args['linkurl_enc'] . '&amp;title=' . $args['linkname_enc'] : '';
	
	// If universal button is enabled
	if ( ! $args['no_universal_button'] ) {
		
		if ( isset( $options['button'] ) && 'CUSTOM' == $options['button'] ) {
			// Custom button
			$button_src		= $options['button_custom'];
			$button_width	= '';
			$button_height	= '';
		} else if ( isset( $options['button'] ) && 'TEXT' == $options['button'] ) {
			// Text-only button
			$button_text	= stripslashes( $options[ 'button_text'] );
			// Do not display universal icon
			$button_class  .= ' addtoany_no_icon';
		} else {
			// Default AddToAny button
			if ( $is_amp ) {
				// AMP (Accelerated Mobile Page)
				$button_src    = 'https://static.addtoany.com/buttons/a2a.svg';
				$button_width  = ! empty( $args['icon_size'] ) ? ' width="' . $args['icon_size'] .'"'  : ' width="32"';
				$button_height = ! empty( $args['icon_size'] ) ? ' height="' . $args['icon_size'] .'"'  : ' height="32"';
			}
		}
		
		if ( ! empty( $html_content ) ) {
			$button = $html_content;
		} elseif ( ! empty( $button_text ) ) {
			$button = $button_text;
		} elseif ( ! empty( $button_src ) ) {
			$button	= '<img src="' . $button_src . '"' . $button_width . $button_height . ' alt="Share">';
		} else {
			$button = '';
		}
		
		// Add additional classNames to .a2a_dd
		$button_additional_classes = ! empty( $args['button_additional_classes'] ) ? ' ' . $args['button_additional_classes'] : '';
		
		if ( isset( $options['button_show_count'] ) && $options['button_show_count'] == '1' ) {
			$button_class .= ' a2a_counter';
		}
		
		$button_html = $args['html_container_open'] . $args['html_wrap_open'] . '<a class="a2a_dd' . $button_class . $button_additional_classes . ' addtoany_share_save addtoany_share" href="' 
			. esc_url( 'https://www.addtoany.com/share' .$button_href_querystring ) . '"'
			. $button_data_url . $button_data_title . $button_data_media . $button_target
			. '>' . $button . '</a>';
	
	} else {
		// Universal button disabled
		$button_html = '';
	}
	
	// Closing tags come after <script> to validate in case the container is a list element
	$button_html .= $args['html_wrap_close'] . $args['html_container_close'];
	
	if ( isset( $args['output_later'] ) && $args['output_later'] == true )
		return $button_html;
	else
		echo $button_html;
}

function ADDTOANY_SHARE_SAVE_SPECIAL( $special_service_code, $args = array() ) {
	// $args array = output_later, linkname, linkurl
	
	if ( is_feed() ) {
		return;
	}
	
	$options = get_option( 'addtoany_options', array() );
	
	$args = array_merge( $args, A2A_SHARE_SAVE_link_vars( $args ) ); // linkname_enc, etc.
	
	$special_anchor_template = '<a class="a2a_button_%1$s addtoany_special_service"%2$s></a>';
	$custom_attributes = '';
	
	if ( $special_service_code == 'facebook_like' ) {
		$custom_attributes .= ( isset( $options['special_facebook_like_options']['verb'] )
			&& 'recommend' == $options['special_facebook_like_options']['verb'] ) ? ' data-action="recommend"' : '';
		$custom_attributes .= ( isset( $options['special_facebook_like_options']['show_count'] )
			&& $options['special_facebook_like_options']['show_count'] == '1' ) ? '' : ' data-layout="button"';
		$custom_attributes .= ' data-href="' . esc_attr( $args['linkurl'] ) . '"';
		$special_html = sprintf( $special_anchor_template, $special_service_code, $custom_attributes );
	}
	
	elseif ( $special_service_code == 'twitter_tweet' ) {
		$custom_attributes .= ' data-url="' . esc_attr( $args['linkurl'] ) . '"';
		$custom_attributes .= ' data-text="' . esc_attr( $args['linkname'] ) . '"';
		$special_html = sprintf( $special_anchor_template, $special_service_code, $custom_attributes );
	}
	
	elseif ( $special_service_code == 'pinterest_pin' ) {
		$custom_attributes .= ( isset( $options['special_pinterest_pin_options']['show_count'] )
			&& $options['special_pinterest_pin_options']['show_count'] == '1' ) ? '' : ' data-pin-config="none"';
		$custom_attributes .= ' data-url="' . esc_attr( $args['linkurl'] ) . '"';
		$custom_attributes .= ( empty( $args['linkmedia'] ) ) ? '' : ' data-media="' . esc_attr( $args['linkmedia'] ) . '"';
		$special_html = sprintf( $special_anchor_template, $special_service_code, $custom_attributes );
	}
	
	if ( isset( $args['output_later'] ) && $args['output_later'] == true )
		return $special_html;
	else
		echo $special_html;
}

if ( ! function_exists( 'A2A_menu_locale' ) ) {
	function A2A_menu_locale() {
		global $A2A_locale;
		$locale = get_locale();
		if ( $locale == 'en_US' || $locale == 'en' || $A2A_locale != '' )
			return false;
			
		$A2A_locale = 'a2a_localize = {
	Share: "' . __( "Share", 'add-to-any' ) . '",
	Save: "' . __( "Save", 'add-to-any' ) . '",
	Subscribe: "' . __( "Subscribe", 'add-to-any' ) . '",
	Email: "' . __( "Email", 'add-to-any' ) . '",
	Bookmark: "' . __( "Bookmark", 'add-to-any' ) . '",
	ShowAll: "' . __( "Show all", 'add-to-any' ) . '",
	ShowLess: "' . __( "Show less", 'add-to-any' ) . '",
	FindServices: "' . __( "Find service(s)", 'add-to-any' ) . '",
	FindAnyServiceToAddTo: "' . __( "Instantly find any service to add to", 'add-to-any' ) . '",
	PoweredBy: "' . __( "Powered by", 'add-to-any' ) . '",
	ShareViaEmail: "' . __( "Share via email", 'add-to-any' ) . '",
	SubscribeViaEmail: "' . __( "Subscribe via email", 'add-to-any' ) . '",
	BookmarkInYourBrowser: "' . __( "Bookmark in your browser", 'add-to-any' ) . '",
	BookmarkInstructions: "' . __( "Press Ctrl+D or \u2318+D to bookmark this page", 'add-to-any' ) . '",
	AddToYourFavorites: "' . __( "Add to your favorites", 'add-to-any' ) . '",
	SendFromWebOrProgram: "' . __( "Send from any email address or email program", 'add-to-any' ) . '",
	EmailProgram: "' . __( "Email program", 'add-to-any' ) . '",
	More: "' . __( "More&#8230;", 'add-to-any' ) . '",
	ThanksForSharing: "' . __( "Thanks for sharing!", 'add-to-any' ) . '",
	ThanksForFollowing: "' . __( "Thanks for following!", 'add-to-any' ) . '"
};
';
		return $A2A_locale;
	}
}

function ADDTOANY_FOLLOW_KIT( $args = array() ) {
	$options = get_option( 'addtoany_options', array() );
	
	// Args are passed on to ADDTOANY_SHARE_SAVE_KIT
	$defaults = array(
		'buttons' => array(),
		'linkname' => '',
		'linkurl' => '',
		'linkname_enc' => '',
		'linkurl_enc' => '',
		'use_current_page' => false,
		'output_later' => false,
		'is_follow' => true,
		'is_kit' => true,
		'no_special_services' => true,
		'no_universal_button' => true,
		'kit_additional_classes' => '',
		'kit_style' => '',
		'icon_size'	=> isset( $options['icon_size'] ) ? $options['icon_size'] : '32',
		'services' => array(),
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	// Add a2a_follow className to Kit classes
	$args['kit_additional_classes'] = trim( $args['kit_additional_classes'] . ' a2a_follow' );
	
	// If $args['buttons']['feed']['id'] is set
	$buttons = $args['buttons'];
	if ( ! empty( $buttons['feed'] ) && ! empty( $buttons['feed']['id'] ) ) {
		$args['linkurl'] = $buttons['feed']['id'];
		$args['linkname'] = get_bloginfo( 'name' );
	}
	
	$follow_html = ADDTOANY_SHARE_SAVE_KIT( $args );
	
	if ( isset( $args['output_later'] ) && $args['output_later'] == true )
		return $follow_html;
	else
		echo $follow_html;
}

function ADDTOANY_SHARE_SAVE_FLOATING( $args = array() ) {
	$options = get_option( 'addtoany_options', array() );
	
	$floating_html = '';

	// Overridable by args below
	$vertical_type = ( isset( $options['floating_vertical'] ) && 'none' != $options['floating_vertical']
		&& ! in_array( $options['floating_vertical'], array( 'left_attached', 'right_attached' ) )
	) ? $options['floating_vertical'] : false;
	$horizontal_type = ( isset( $options['floating_horizontal'] ) && 'none' != $options['floating_horizontal'] ) ? $options['floating_horizontal'] : false;

	if ( is_singular() ) {
		// Sharing disabled for this singular post?
		$sharing_disabled = get_post_meta( get_the_ID(), 'sharing_disabled', true );
		$sharing_disabled = apply_filters( 'addtoany_sharing_disabled', $sharing_disabled );
		
		if ( ! empty( $sharing_disabled ) ) {
			// Overridable by args below
			$vertical_type   = false;
			$horizontal_type = false;
		}
	}

	// Args are passed on to ADDTOANY_SHARE_SAVE_KIT
	$defaults = array(
		'linkname' => '',
		'linkurl' => '',
		'linkname_enc' => '',
		'linkurl_enc' => '',
		'use_current_page' => true,
		'output_later' => false,
		'is_floating' => true,
		'is_kit' => true,
		'no_addtoany_list_classname' => true,
		'no_special_services' => true,
		'kit_additional_classes' => '',
		'kit_style' => '',
	);
	
	$args = wp_parse_args( $args, $defaults );
	
	// Individual floating type args can override saved options
	if ( isset( $args['vertical_type'] ) && $args['vertical_type'] === true ) {
		$vertical_type = true;
	}
	if ( isset( $args['horizontal_type'] ) && $args['horizontal_type'] === true ) {
		$horizontal_type = true;
	}

	// If either floating type is enabled
	// Expect either a string from options, or a boolean from args
	if ( $vertical_type || $horizontal_type ) {
		// Vertical type?
		if ( $vertical_type ) {
			// Prevent overwriting of $args
			$vertical_args = $args;
			
			// Top position
			$position = ( isset( $options['floating_vertical_position'] ) ) ? $options['floating_vertical_position'] . 'px' : '100px';
			// Left or right offset
			$offset = ( isset( $options['floating_vertical_offset'] ) ) ? $options['floating_vertical_offset'] . 'px' : '0px';
			// Icon size
			$vertical_args['icon_size'] = ( isset( $options['floating_vertical_icon_size'] ) ) ? $options['floating_vertical_icon_size'] : '32';
			
			// Show on scroll value from the top
			$vertical_scroll_show_top = isset( $options['floating_vertical_scroll_top'] ) && '1' == $options['floating_vertical_scroll_top']
				&& isset( $options['floating_vertical_scroll_top_pixels'] ) 
				&& is_numeric( $options['floating_vertical_scroll_top_pixels'] ) ? $options['floating_vertical_scroll_top_pixels'] : '';
			// Show on scroll value from the bottom
			$vertical_scroll_show_bottom = isset( $options['floating_vertical_scroll_bottom'] ) && '1' == $options['floating_vertical_scroll_bottom']
				&& isset( $options['floating_vertical_scroll_bottom_pixels'] ) 
				&& is_numeric( $options['floating_vertical_scroll_bottom_pixels'] ) ? $options['floating_vertical_scroll_bottom_pixels'] : '';
			// Merge values as required
			if ( ! empty( $vertical_scroll_show_bottom ) ) {
				$vertical_args['scroll_show'] = empty( $vertical_scroll_show_top ) ? '0,' . $vertical_scroll_show_bottom : $vertical_scroll_show_top . ',' . $vertical_scroll_show_bottom;
			} elseif ( ! empty( $vertical_scroll_show_top ) ) {
				$vertical_args['scroll_show'] = $vertical_scroll_show_top;
			}
		
			// Add a2a_vertical_style className to Kit classes
			$vertical_args['kit_additional_classes'] = trim( $args['kit_additional_classes'] . ' a2a_floating_style a2a_vertical_style' );
			
			// Add declarations to Kit style attribute
			if ( 'left_docked' === $vertical_type ) {
				$vertical_args['kit_style'] = 'left:' . $offset . ';top:' . $position . ';';
			} elseif ( 'right_docked' === $vertical_type ) {
				$vertical_args['kit_style'] = 'right:' . $offset . ';top:' . $position . ';';
			}
			// Background color
			if ( ! empty( $options['floating_vertical_bg'] ) && 'custom' === $options['floating_vertical_bg'] ) {
				$vertical_args['kit_style'] .= ! empty( $options['floating_vertical_bg_color'] ) ? 'background-color:' . $options['floating_vertical_bg_color'] . ';' : '';
			} else {
				$vertical_args['kit_style'] .= 'background-color:transparent;';
			}
			
			$floating_html .= ADDTOANY_SHARE_SAVE_KIT( $vertical_args );
		}
		
		// Horizontal type?
		if ( $horizontal_type ) {
			// Prevent overwriting of $args values
			$horizontal_args = $args;
			
			// Left or right position
			$position = ( isset( $options['floating_horizontal_position'] ) ) ? $options['floating_horizontal_position'] . 'px' : '0px';
			// Bottom offset
			$offset = ( isset( $options['floating_horizontal_offset'] ) ) ? $options['floating_horizontal_offset'] . 'px' : '0px';
			// Icon size
			$horizontal_args['icon_size'] = ( isset( $options['floating_horizontal_icon_size'] ) ) ? $options['floating_horizontal_icon_size'] : '32';
			// Show on scroll value from the top
			$horizontal_scroll_show_top = isset( $options['floating_horizontal_scroll_top'] ) && '1' == $options['floating_horizontal_scroll_top']
				&& isset( $options['floating_horizontal_scroll_top_pixels'] ) 
				&& is_numeric( $options['floating_horizontal_scroll_top_pixels'] ) ? $options['floating_horizontal_scroll_top_pixels'] : '';
			// Show on scroll value from the bottom
			$horizontal_scroll_show_bottom = isset( $options['floating_horizontal_scroll_bottom'] ) && '1' == $options['floating_horizontal_scroll_bottom']
				&& isset( $options['floating_horizontal_scroll_bottom_pixels'] ) 
				&& is_numeric( $options['floating_horizontal_scroll_bottom_pixels'] ) ? $options['floating_horizontal_scroll_bottom_pixels'] : '';
			// Merge values as required
			if ( ! empty( $horizontal_scroll_show_bottom ) ) {
				$horizontal_args['scroll_show'] = empty( $horizontal_scroll_show_top ) ? '0,' . $horizontal_scroll_show_bottom : $horizontal_scroll_show_top . ',' . $horizontal_scroll_show_bottom;
			} elseif ( ! empty( $horizontal_scroll_show_top ) ) {
				$horizontal_args['scroll_show'] = $horizontal_scroll_show_top;
			}

			// Add a2a_default_style className to Kit classes
			$horizontal_args['kit_additional_classes'] = trim( $args['kit_additional_classes'] . ' a2a_floating_style a2a_default_style' );
			
			// Add declarations to Kit style attribute
			if ( 'left_docked' === $horizontal_type ) {
				$horizontal_args['kit_style'] = 'bottom:' . $offset . ';left:' . $position . ';';
			} elseif ( 'right_docked' === $horizontal_type ) {
				$horizontal_args['kit_style'] = 'bottom:' . $offset . ';right:' . $position . ';';
			} elseif ( 'center_docked' === $horizontal_type ) {
				$horizontal_args['kit_style'] = 'bottom:' . $offset . ';left:50%;transform:translateX(-50%);';
			}
			// Background color
			if ( ! empty( $options['floating_horizontal_bg'] ) && 'custom' === $options['floating_horizontal_bg'] ) {
				$horizontal_args['kit_style'] .= ! empty( $options['floating_horizontal_bg_color'] ) ? 'background-color:' . $options['floating_horizontal_bg_color'] . ';' : '';
			} else {
				$horizontal_args['kit_style'] .= 'background-color:transparent;';
			}
			
			$floating_html .= ADDTOANY_SHARE_SAVE_KIT( $horizontal_args );
		}
	}
	
	if ( isset( $args['output_later'] ) && $args['output_later'] == true )
		return $floating_html;
	else
		echo $floating_html;
}


function A2A_SHARE_SAVE_head_script() {
	// Hook to disable script output
	// Example: add_filter( 'addtoany_script_disabled', '__return_true' );
	$script_disabled = apply_filters( 'addtoany_script_disabled', false );
	
	if ( is_admin() || is_feed() || $script_disabled )
		return;

	if ( is_singular() ) {
		// Sharing disabled for this singular post?
		$sharing_disabled = get_post_meta( get_the_ID(), 'sharing_disabled', true );
		$sharing_disabled = apply_filters( 'addtoany_sharing_disabled', $sharing_disabled );
	}
		
	$options = get_option( 'addtoany_options', array() );

	// Use local cache?
	$cache = ! empty( $options['cache'] ) && '1' == $options['cache'] ? true : false;
	$upload_dir = wp_upload_dir();
	$cached_file = ! empty( $upload_dir['basedir'] ) && file_exists( $upload_dir['basedir'] . '/addtoany/page.js' ) ? $upload_dir['basedir'] . '/addtoany/page.js' : false;
	$querystring = '';
	// Is page.js actually cached?
	if ( $cache && $cached_file ) {
		// Is page.js recently cached, within 2 days (172800 seconds)?
		$modified_time = filemtime( $cached_file );
		$cache = $modified_time && time() - $modified_time < 172800 ? true : false;
		// If cache is recent
		if ( $cache ) {
			// Set a "ver" parameter's value to the file's modified time for cache management
			$querystring = '?ver=' . $modified_time;
		} else {
			// Revert the cache option
			A2A_SHARE_SAVE_revert_cache();
		}
	}
	
	// Set static server
	$static_server = $cache ? $upload_dir['baseurl'] . '/addtoany' : 'https://static.addtoany.com/menu';
	
	// Icon colors
	$icon_bg = ! empty( $options['icon_bg'] ) && in_array( $options['icon_bg'], array( 'custom', 'transparent' ) ) ? $options['icon_bg'] : false;
	$icon_bg_color = 'custom' === $icon_bg && ! empty( $options['icon_bg_color'] ) ? $options['icon_bg_color'] : '';
	$icon_bg_color = 'transparent' === $icon_bg ? 'transparent' : $icon_bg_color;
	$icon_fg = ! empty( $options['icon_fg'] ) && 'custom' === $options['icon_fg'] ? true : false;
	$icon_fg_color = $icon_fg && ! empty( $options['icon_fg_color'] ) ? ',' . $options['icon_fg_color'] : '';
	// Use "unset" keyword for background if only the foreground is set
	$icon_bg_color = empty( $icon_bg_color ) && ! empty( $icon_fg_color ) ? 'unset' : $icon_bg_color;
	$icon_color = $icon_bg_color . $icon_fg_color;

	// Floating vertical relative to content
	$floating_js = '';
	if (
		isset( $options['floating_vertical'] )
		&& in_array( $options['floating_vertical'], array( 'left_attached', 'right_attached' ) )
		&& ! empty( $options['floating_vertical_attached_to'] )
		&& empty( $sharing_disabled )
	) {
		// Top position
		$floating_js_position = ( isset( $options['floating_vertical_position'] ) ) ? $options['floating_vertical_position'] . 'px' : '100px';
		// Left or right offset
		$floating_js_offset = ( isset( $options['floating_vertical_offset'] ) ) ? $options['floating_vertical_offset'] . 'px' : '0px';
		
		// Style attribute (accepts "left" attached only)
		$floating_js_kit_style = 'left_attached' === $options['floating_vertical'] ? 'margin-left:' . $floating_js_offset . ';' : '';
		$floating_js_kit_style .= 'top:' . $floating_js_position . ';';

		$floating_js = "\n"
			. 'a2a_config.callbacks.push({'
				. 'ready: function(){'
					. 'var d=document;'
					. 'function a(){'
						. 'var e=d.createElement("div");'
						. 'e.innerHTML=' . wp_json_encode( ADDTOANY_SHARE_SAVE_FLOATING( array( 
							'output_later' => true,
							'basic_html' => true,
							'kit_style' => $floating_js_kit_style,
							'vertical_type' => true,
						) ) ) . ';'
						. 'd.querySelector(' . wp_json_encode( stripslashes( $options['floating_vertical_attached_to'] ) ) . ').appendChild(e.firstChild);'
						. 'a2a.init("page");'
					. '}'
					. 'if("loading"!==d.readyState)a();else d.addEventListener("DOMContentLoaded",a,false);'
				. '}'
			. '});';
	}
	
	// Enternal script call + initial JS + set-once variables
	$additional_js = ( isset( $options['additional_js_variables'] ) ) ? $options['additional_js_variables'] : '' ;
	$script_configs = ( ( $cache ) ? "\n" . 'a2a_config.static_server="' . $static_server . '";' : '' )
		. ( $icon_color ? "\n" . 'a2a_config.icon_color="' . $icon_color . '";' : '' )
		. ( isset( $options['onclick'] ) && '1' == $options['onclick'] ? "\n" . 'a2a_config.onclick=1;' : '' )
		. ( $additional_js ? "\n" . stripslashes( $additional_js ) : '' );
	
	$javascript_header = "\n"
		. '<script data-cfasync="false">' . "\n"
		. 'window.a2a_config=window.a2a_config||{};'
		. 'a2a_config.callbacks=[];a2a_config.overlays=[];'
		. 'a2a_config.templates={};'
		. A2A_menu_locale()
		. $floating_js
		. $script_configs
		. "\n"
		. '(function(d,s,a,b){'
			. 'a=d.createElement(s);'
			. 'b=d.getElementsByTagName(s)[0];'
			. 'a.async=1;'
			. 'a.src="' . $static_server . '/page.js' . $querystring . '";'
			. 'b.parentNode.insertBefore(a,b);'
		. '})(document,"script");'		
		. "\n</script>\n";
	
	 echo $javascript_header;
}

add_action( 'wp_head', 'A2A_SHARE_SAVE_head_script' );

function A2A_SHARE_SAVE_footer_script() {
	if ( is_admin() || is_feed() )
		return;
	
	$floating_html = ADDTOANY_SHARE_SAVE_FLOATING( array( 'output_later' => true ) );
	
	echo $floating_html;
}

add_action( 'wp_footer', 'A2A_SHARE_SAVE_footer_script' );


function A2A_SHARE_SAVE_add_to_content( $content ) {
	global $wp_current_filter;
	
	// Don't add to get_the_excerpt because it's too early and strips tags (adding to the_excerpt is allowed)
	if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
		// Return early
		return $content;
	}
	
	$sharing_disabled = get_post_meta( get_the_ID(), 'sharing_disabled', true );
	$sharing_disabled = apply_filters( 'addtoany_sharing_disabled', $sharing_disabled );
	
	if ( 
		// Private post
		get_post_status( get_the_ID() ) == 'private' ||
		// Sharing disabled on post
		! empty( $sharing_disabled ) 
	) {
		// Return early
		return $content;
	}
	
	$is_feed = is_feed();
	$options = get_option( 'addtoany_options', array() );
	$post_type = get_post_type( get_the_ID() );
	
	if ( 
		( 
			// Legacy tags
			// <!--sharesave--> tag
			strpos( $content, '<!--sharesave-->' ) === false || 
			// <!--nosharesave--> tag
			strpos( $content, '<!--nosharesave-->' ) !== false
		) &&
		(
			// Posts
			// All posts
			( is_singular('post') && isset( $options['display_in_posts'] ) && $options['display_in_posts'] == '-1' ) ||
			// Front page posts		
			( is_home() && isset( $options['display_in_posts_on_front_page'] ) && $options['display_in_posts_on_front_page'] == '-1' ) ||
			// Archive page posts (Category, Tag, Author and Date pages)
			( is_archive() && isset( $options['display_in_posts_on_archive_pages'] ) && $options['display_in_posts_on_archive_pages'] == '-1' ) ||
			// Search results posts (same as Archive page posts option)
			( is_search() && isset( $options['display_in_posts_on_archive_pages'] ) && $options['display_in_posts_on_archive_pages'] == '-1' ) || 
			// Excerpt (the_excerpt is the current filter)
			( 'the_excerpt' == current_filter() && isset( $options['display_in_excerpts'] ) && $options['display_in_excerpts'] == '-1' ) ||
			// Posts in feed
			( $is_feed && isset( $options['display_in_feed'] ) && $options['display_in_feed'] == '-1' ) ||
			
			// Custom post types
			( $post_type && isset( $options['display_in_cpt_' . $post_type] ) && $options['display_in_cpt_' . $post_type] == '-1' ) ||
			
			// Pages
			// Individual pages
			( is_page() && isset( $options['display_in_pages'] ) && $options['display_in_pages'] == '-1' ) ||
			// Attachment (media) pages
			( is_attachment() && isset( $options['display_in_attachments'] ) && $options['display_in_attachments'] == '-1' ) ||
			// <!--nosharesave--> legacy tag
			( (strpos( $content, '<!--nosharesave-->') !== false ) )
		)
	) {
		// Return early
		return $content;
	}
	
	$kit_args = array(
		"output_later" => true,
		"is_kit" => ( $is_feed ) ? false : true,
	);
	
	// If a Sharing Header is set
	if ( ! empty( $options['header'] ) ) {
		$html_header = '<div class="addtoany_header">' . stripslashes( $options['header'] ) . '</div>';
	} else {
		$html_header = '';
	}
	
	if ( $is_feed ) {
		$container_wrap_open = '<p>';
		$container_wrap_close = '</p>';
		$kit_args['html_container_open'] = '';
		$kit_args['html_container_close'] = '';
		$kit_args['html_wrap_open'] = '';
		$kit_args['html_wrap_close'] = '';
	} else {
		$container_wrap_open = '<div class="addtoany_share_save_container addtoany_content %s">'; // Contains placeholder
		$container_wrap_open .= $html_header;
		$container_wrap_close = '</div>';
	}
	
	$options['position'] = isset( $options['position'] ) ? $options['position'] : 'bottom';
	
	if ($options['position'] == 'both' || $options['position'] == 'top') {
		// Prepend to content
		$content = sprintf( $container_wrap_open, 'addtoany_content_top' ) . ADDTOANY_SHARE_SAVE_KIT($kit_args) . $container_wrap_close . $content;
	}
	if ( $options['position'] == 'bottom' || $options['position'] == 'both') {
		// Append to content
		$content .= sprintf( $container_wrap_open, 'addtoany_content_bottom' ) . ADDTOANY_SHARE_SAVE_KIT($kit_args) . $container_wrap_close;
	}
	
	return $content;
}


function A2A_SHARE_SAVE_pre_get_posts( $query ) {
	if ( $query->is_main_query() ) {
		add_filter( 'the_content', 'A2A_SHARE_SAVE_add_to_content', 98 );
		add_filter( 'the_excerpt', 'A2A_SHARE_SAVE_add_to_content', 98 );
	}
}

add_action( 'pre_get_posts', 'A2A_SHARE_SAVE_pre_get_posts' );


// [addtoany url="https://www.example.com/page.html" title="Example Page"]
function A2A_SHARE_SAVE_shortcode( $attributes ) {
	$attributes = shortcode_atts( array(
		'url'     => '',
		'title'   => '',
		'media'   => '',
		'buttons' => '',
	), $attributes, 'addtoany' );
	
	$linkname =  $attributes['title'];
	$linkurl = $attributes['url'];
	$linkmedia = $attributes['media'];
	$buttons = ! empty( $attributes['buttons'] ) ? explode( ',', $attributes['buttons'] ) : array();
	
	$output_later = true;

	return '<div class="addtoany_shortcode">'
		. ADDTOANY_SHARE_SAVE_KIT( compact( 'linkname', 'linkurl', 'linkmedia', 'output_later', 'buttons' ) )
		. '</div>';
}

add_shortcode( 'addtoany', 'A2A_SHARE_SAVE_shortcode' );


function A2A_SHARE_SAVE_stylesheet() {
	global $A2A_SHARE_SAVE_options;
	
	$options = $A2A_SHARE_SAVE_options;
	
	if ( ! is_admin() ) {
		wp_enqueue_style( 'addtoany', plugins_url('/addtoany.min.css', __FILE__ ), false, '1.15' );
		
		// Prepare inline CSS
		$inline_css = '';
		
		$vertical_type = ( isset( $options['floating_vertical'] ) && 'none' != $options['floating_vertical'] ) ? $options['floating_vertical'] : false;
		$horizontal_type = ( isset( $options['floating_horizontal'] ) && 'none' != $options['floating_horizontal'] ) ? $options['floating_horizontal'] : false;
		
		// If vertical bar is enabled
		if ( $vertical_type && 
			// and respsonsiveness is enabled
			( ! isset( $options['floating_vertical_responsive'] ) || '-1' != $options['floating_vertical_responsive'] )
		) {
			// Get min-width for media query
			$vertical_max_width = ( 
				isset( $options['floating_vertical_responsive_max_width'] ) && 
				is_numeric( $options['floating_vertical_responsive_max_width'] ) 
			) ? $options['floating_vertical_responsive_max_width'] : '980';
			
			// Set media query
			$inline_css .= '@media screen and (max-width:' . $vertical_max_width . 'px){' . "\n"
				. '.a2a_floating_style.a2a_vertical_style{display:none;}' . "\n"
				. '}';
		}
		
		// If horizontal bar is enabled
		if ( $horizontal_type && 
			// and respsonsiveness is enabled
			( ! isset( $options['floating_horizontal_responsive'] ) || '-1' != $options['floating_horizontal_responsive'] )
		) {
			// Get max-width for media query
			$horizontal_min_width = ( 
				isset( $options['floating_horizontal_responsive_min_width'] ) && 
				is_numeric( $options['floating_horizontal_responsive_min_width'] ) 
			) ? $options['floating_horizontal_responsive_min_width'] : '981';
			
			// Insert newline if there is inline CSS already
			$inline_css = 0 < strlen( $inline_css ) ? $inline_css . "\n" : $inline_css;
			
			// Set media query
			$inline_css .= '@media screen and (min-width:' . $horizontal_min_width . 'px){' . "\n"
				. '.a2a_floating_style.a2a_default_style{display:none;}' . "\n"
				. '}';
		}
		
		// If additional CSS (custom CSS for AddToAny) is set
		if ( ! empty( $options['additional_css'] ) ) {
			$custom_css = stripslashes( $options['additional_css'] );
			
			// Insert newline if there is inline CSS already
			$inline_css = 0 < strlen( $inline_css ) ? $inline_css . "\n" : $inline_css;
			
			$inline_css .= $custom_css;
		}
		
		// If there is inline CSS
		if ( 0 < strlen( $inline_css ) ) {
			// Insert inline CSS
			wp_add_inline_style( 'addtoany', $inline_css );	
		}
	}
}

add_action( 'wp_enqueue_scripts', 'A2A_SHARE_SAVE_stylesheet', 20 );

function A2A_SHARE_SAVE_enqueue_script() {
	if ( wp_script_is( 'jquery', 'registered' ) ) {
		wp_enqueue_script( 'addtoany', plugins_url('/addtoany.min.js', __FILE__ ), array( 'jquery' ), '1.1' );
	}
}

add_action( 'wp_enqueue_scripts', 'A2A_SHARE_SAVE_enqueue_script' );

/**
 * Cache AddToAny
 */

function A2A_SHARE_SAVE_refresh_cache() {
	$contents = wp_remote_fopen( 'https://www.addtoany.com/ext/updater/files_list/' );
	$file_urls = explode( "\n", $contents, 20 );
	$upload_dir = wp_upload_dir();
	
	// Try to create directory if it doesn't already exist
	if ( ! wp_mkdir_p( dirname( $upload_dir['basedir'] . '/addtoany/foo' ) ) ) {
		// Handle directory creation issue
		// Revert cache option
		A2A_SHARE_SAVE_revert_cache();
	}
	
	if ( count( $file_urls ) > 0 ) {
		for ( $i = 0; $i < count( $file_urls ); $i++ ) {
			// Download files
			$file_url = trim( $file_urls[ $i ] );
			$file_name = substr( strrchr( $file_url, '/' ), 1, 99 );
			
			// Place files in uploads/addtoany directory
			$response = wp_remote_get( $file_url, array(
				'filename' => $upload_dir['basedir'] . '/addtoany/' . $file_name,
				'stream'   => true, // Required to use `filename` arg
			) );

			// Handle error
			if ( is_wp_error( $response ) ) {
				// Revert cache option
				A2A_SHARE_SAVE_revert_cache();
			}
		}
	}
}

add_action( 'addtoany_refresh_cache', 'A2A_SHARE_SAVE_refresh_cache' );

function A2A_SHARE_SAVE_schedule_cache() {
	// Unschedule if already scheduled
	A2A_SHARE_SAVE_unschedule_cache();

	// Try to schedule daily cache refreshes, running once now
	$result = wp_schedule_event( time(), 'daily', 'addtoany_refresh_cache' );

	// Revert cache option if the event didn't get scheduled
	if ( false === $result ) {
		A2A_SHARE_SAVE_revert_cache();
	}
}

function A2A_SHARE_SAVE_unschedule_cache() {
	// Unschedule if scheduled
	wp_clear_scheduled_hook( 'addtoany_refresh_cache' );
}

function A2A_SHARE_SAVE_revert_cache() {
	// Unschedule
	A2A_SHARE_SAVE_unschedule_cache();

	// Get all existing AddToAny options
	$options = get_option( 'addtoany_options', array() );

	// Revert cache option
	$options['cache'] = '-1';	
	update_option( 'addtoany_options', $options );
}

/**
 * Activation hook
 */

function addtoany_activation() {
	// Get all existing AddToAny options
	$options = get_option( 'addtoany_options', array() );
	
	// If the local cache option is enabled
	if ( isset( $options['cache'] ) && $options['cache'] == '1' ) {
		// Schedule and run the local cache refresh
		A2A_SHARE_SAVE_schedule_cache();
	}
}

register_activation_hook( __FILE__, 'addtoany_activation' );

/**
 * Deactivation hook
 */

function addtoany_deactivation() {
	// Unschedule if scheduled
	A2A_SHARE_SAVE_unschedule_cache();
}

register_deactivation_hook( __FILE__, 'addtoany_deactivation' );

/**
 * Admin Options
 */

if ( is_admin() ) {
	include_once $A2A_SHARE_SAVE_plugin_dir . '/addtoany.admin.php';
}

function A2A_SHARE_SAVE_add_menu_link() {
	$page = add_options_page(
		__( 'AddToAny Share Settings', 'add-to-any' ),
		__( 'AddToAny', 'add-to-any' ),
		'manage_options',
		'addtoany',
		'A2A_SHARE_SAVE_options_page'
	);
}

add_filter( 'admin_menu', 'A2A_SHARE_SAVE_add_menu_link' );

function A2A_SHARE_SAVE_widgets_init() {
	global $A2A_SHARE_SAVE_plugin_dir;
	
	include_once $A2A_SHARE_SAVE_plugin_dir . '/addtoany.widgets.php';
	register_widget( 'A2A_SHARE_SAVE_Widget' );
	register_widget( 'A2A_Follow_Widget' );
}

add_action( 'widgets_init', 'A2A_SHARE_SAVE_widgets_init' );

// Place in Option List on Settings > Plugins page 
function A2A_SHARE_SAVE_actlinks( $links, $file ) {
	// Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	
	if ( ! $this_plugin ) {
		$this_plugin = plugin_basename( __FILE__ );
	}
	
	if ( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=addtoany">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	
	return $links;
}

add_filter( 'plugin_action_links', 'A2A_SHARE_SAVE_actlinks', 10, 2 );
