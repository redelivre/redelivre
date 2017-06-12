<?php
	
/**
 * Migrate old AddToAny options
 */
function addtoany_update_options() {
	
	$options_old = get_option( 'addtoany_options', array() );
	$options_new = $options_old;
	
	$old_buttons = array( 
		'share_save_256_24.gif|256|24', 'share_save_171_16.gif|171|16', 'share_save_120_16.gif|120|16',
		'share_save_256_24.png|256|24', 'share_save_171_16.png|171|16', 'share_save_120_16.png|120|16',
		'share_16_16.png|16|16', 'favicon.png|16|16',
	);
	
	// If old button enabled
	if ( ! empty( $options_old['button'] ) && in_array( $options_old['button'], $old_buttons) ) {
		// Switch to custom button URL
		$options_new['button'] = 'CUSTOM';
		$options_new['button_custom'] = 'https://static.addtoany.com/buttons/' . current( explode( '|', $options_old['button'] ) );
	}
	
	update_option( 'addtoany_options', $options_new );
	
}