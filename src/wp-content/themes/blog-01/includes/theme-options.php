<?php


add_action( 'customize_register', 'themename_customize_register' );
function themename_customize_register($wp_customize) {
	
    $wp_customize->add_section( 'blog01_barra_lateral', array(
        'title'          => 'Barra Lateral',
        'priority'       => 35,
    ) );
    
    
    $wp_customize->add_setting( 'campanha_theme_options[sidebar_position]', array(
        'default'        => 'left',
        'type'           => 'option',
        'capability'     => 'edit_theme_options',
        'transport'      => 'postMessage'
    ) );
    
    $wp_customize->add_control( 'themename_color_scheme', array(
        'label'      => 'Posição da barra lateral',
        'section'    => 'blog01_barra_lateral',
        'settings'   => 'campanha_theme_options[sidebar_position]',
        'type'       => 'radio',
        'choices'    => array(
            'left' => 'a esquerda',
            'right' => 'a direita',
            ),
    ) );
    
    if ( $wp_customize->is_preview() && ! is_admin() )
	    add_action( 'wp_footer', 'blog01_customize_preview', 21);
    
    
}

function blog01_customize_preview() {

    ?>
	<script type="text/javascript">
	( function( $ ){
	wp.customize('campanha_theme_options[sidebar_position]',function( value ) {
		value.bind(function(to) {
            $('#content').css('float', to == 'left' ? 'right' : 'left' );
		});
	});
	} )( jQuery )
	</script>
	<?php

}
