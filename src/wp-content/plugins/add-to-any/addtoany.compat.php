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
	// only CSS here please...
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