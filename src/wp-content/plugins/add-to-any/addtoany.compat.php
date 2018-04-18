<?php
	
/**
 * Load theme compatibility functions
 */
function addtoany_load_theme_compat() {
	add_action( 'loop_start', 'addtoany_excerpt_remove' );
}

add_action( 'after_setup_theme', 'addtoany_load_theme_compat', -1 );

/**
 * Remove from excerpts where buttons could be redundant or awkward
 */
function addtoany_excerpt_remove() {
	// If Twenty Sixteen theme
	if ( 'twentysixteen' == get_stylesheet() || 'twentysixteen' == get_template() ) {
		// If blog index, single, or archive page, where excerpts are used as "intros"
		if ( is_single() || is_archive() || is_home() ) {
			remove_filter( 'the_excerpt', 'A2A_SHARE_SAVE_add_to_content', 98 );
		}	
	}
}

/**
 * Load AMP (Accelerated Mobile Pages) compatibility functions
 */
add_action( 'amp_post_template_css', 'addtoany_amp_additional_css_styles' );

function addtoany_amp_additional_css_styles( $amp_template ) {
	// CSS only
	?>
	.addtoany_list a {
		padding: 0 4px;
	}
	.addtoany_list a amp-img {
		display: inline-block;
	}
	<?php
}

function addtoany_amp_icons_css( $amp_template ) {
	global $A2A_SHARE_SAVE_amp_icons_css;
	echo $A2A_SHARE_SAVE_amp_icons_css;
}

/**
 * Move buttons from WooCommerce product description to WooCommerce's sharing block
 */
add_action( 'woocommerce_share', 'addtoany_woocommerce_share', 10 );

function addtoany_woocommerce_share() {
	remove_filter( 'the_content', 'A2A_SHARE_SAVE_add_to_content', 98 );
	remove_filter( 'the_excerpt', 'A2A_SHARE_SAVE_add_to_content', 98 );
	
	$options = get_option( 'addtoany_options', array() );
	$sharing_disabled = get_post_meta( get_the_ID(), 'sharing_disabled', true );
	$sharing_disabled = apply_filters( 'addtoany_sharing_disabled', $sharing_disabled );
	$post_type = get_post_type( get_the_ID() );
	
	if ( 
		// Private post
		get_post_status( get_the_ID() ) == 'private' ||
		// Sharing disabled on post
		! empty( $sharing_disabled ) ||
		// Custom post type (usually "product") disabled
		( $post_type && isset( $options['display_in_cpt_' . $post_type] ) && $options['display_in_cpt_' . $post_type] == '-1' )
	) {
		return;
	} else {
		// If a Sharing Header is set
		if ( ! empty( $options['header'] ) ) {
			echo '<div class="addtoany_header">' . stripslashes( $options['header'] ) . '</div>';
		} else {
			$html_header = '';
		}
		
		// Display share buttons
		ADDTOANY_SHARE_SAVE_KIT();
	}
}