<?php
/**
 * Crete a meta box for the Team template page
 * 
 * @package Guarani
 * @since Guarani 1.0
 */
function guarani_team_meta_box() {
	
	// It'll only be visible when a post is defined with the page template Team
    if ( isset( $_GET['post'] ) )
    {
    	$post_id = $_GET['post'];
    	$template_file = get_post_meta( $post_id, '_wp_page_template', true );
    }

	if ( isset( $template_file ) && $template_file == 'page-templates/team.php' )
	{
		add_meta_box(
			'guarani_team',
			__( 'Team', 'guarani' ),
			'guarani_team_meta_box_callback',
			'page',
			'normal',
			'high'
		);
	}
	
}
add_action( 'add_meta_boxes', 'guarani_team_meta_box' );


/**
 * Callback para a criação da meta box "guarani_team"
 * 
 */
function guarani_team_meta_box_callback( $post ) {

    // Recebe os valores
	$meta_value = get_post_meta( $post->ID, '_guarani_team', true );
	
	// Usa o nonce para verificação
	wp_nonce_field( 'guarani-team-submit', 'guarani-team-check-nonce' );
	
	echo '<label for="guarani-team">';
	_e( 'Add your team by writing their usernames, comma-separated:', 'guarani' );
	echo '</label><br/>';
	echo '<input type="text" id="guarani-team" name="guarani-team" value="' . $meta_value . '" size="100%" />';

}


/**
 * Salva os dados inseridos nas meta boxes
 * 
 */
function guarani_save_postdata( $post_id ) {

	global $post;
	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
	  return;
	 
	if ( isset( $post->post_type ) && $post->post_type == 'revision' )
		return; 
		
	if ( isset( $post->post_type ) && $post->post_type == 'page' ) {
		if ( ! current_user_can( 'edit_page', $post_id ) )
			return;
	}
	else {
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
	}  

	if ( isset( $post->post_type ) && $post->post_type == 'page' && get_post_meta( $post_id, '_wp_page_template', true ) == 'page-templates/team.php' ){
	
		if( !isset( $_POST['guarani-team-check-nonce'] ) || !wp_verify_nonce( $_POST['guarani-team-check-nonce'], 'guarani-team-submit'  ) )
			return $post_id;
	        
    	if ( isset( $_POST['guarani-team'] ) )
	    	$meta_value = $_POST['guarani-team'];

    	if ( isset( $meta_value ) )
    		update_post_meta( $post_id, '_guarani_team', esc_attr( $meta_value ) );
	        	
	}

}

add_action( 'save_post', 'guarani_save_postdata' );
?>