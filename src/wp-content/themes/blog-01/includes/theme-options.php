<?php


add_action( 'customize_register', 'themename_customize_register' );
function themename_customize_register($wp_customize) {
	
    $wp_customize->add_section( 'blog01_barra_lateral', array(
        'title'          => 'Barra Lateral',
        'priority'       => 35,
    ) );
    
    $wp_customize->add_section( 'theme_colors', array(
        'title'          => 'Cores do Tema',
        'priority'       => 35,
    ) );
    
    
    $wp_customize->add_setting( 'campanha_theme_options[sidebar_position]', array(
        'default'        => 'left',
        'type'           => 'option',
        'capability'     => 'edit_theme_options',
        'transport'      => 'postMessage'
    ) );
    
    $wp_customize->add_setting( 'campanha_theme_options[theme_colors][brilho]', array(
        'default'        => '',
        'type'           => 'option',
        'capability'     => 'edit_theme_options',
        //'transport'      => 'postMessage'
    ) );
    
    $wp_customize->add_setting( 'campanha_theme_options[theme_colors][link]', array(
        'default'        => '',
        'type'           => 'option',
        'capability'     => 'edit_theme_options',
        //'transport'      => 'postMessage'
    ) );
    
    $wp_customize->add_setting( 'campanha_theme_options[theme_colors][active]', array(
        'default'        => '',
        'type'           => 'option',
        'capability'     => 'edit_theme_options',
        //'transport'      => 'postMessage'
    ) );
    
    $wp_customize->add_setting( 'campanha_theme_options[use_theme_colors]', array(
        'default'        => false,
        'type'           => 'option',
        'capability'     => 'edit_theme_options',
        //'transport'      => 'postMessage'
    ) );
    
    $wp_customize->add_control( 'use_theme_colors', array(
        'label'      => 'Posição da barra lateral',
        'section'    => 'blog01_barra_lateral',
        'settings'   => 'campanha_theme_options[sidebar_position]',
        'type'       => 'radio',
        'choices'    => array(
            'left' => 'a esquerda',
            'right' => 'a direita',
            ),
    ) );
    
    $wp_customize->add_control( 'themename_color_scheme', array(
        'label'      => 'Usar cores personalizadas',
        'section'    => 'theme_colors',
        'settings'   => 'campanha_theme_options[use_theme_colors]',
        'type'       => 'checkbox'
    ) );
    
    
    
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'theme_colors_link', array(
        'label'   => 'Links',
        'section' => 'theme_colors',
        'settings'   => 'campanha_theme_options[theme_colors][link]',
    ) ) );
    
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'theme_colors_active', array(
        'label'   => 'Links ativos',
        'section' => 'theme_colors',
        'settings'   => 'campanha_theme_options[theme_colors][active]',
    ) ) );
    
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'theme_colors_brilho', array(
        'label'   => 'Brilho',
        'section' => 'theme_colors',
        'settings'   => 'campanha_theme_options[theme_colors][brilho]',
    ) ) );
    
    if ( $wp_customize->is_preview() && ! is_admin() )
	    add_action( 'wp_footer', 'blog01_customize_preview', 21);
    
    
}

function blog01_customize_preview() {

    ?>
	<script type="text/javascript">
	( function( $ ){
	wp.customize('campanha_theme_options[sidebar_position]',function( value ) {
		value.bind(function(to) {
            $('#main-sidebar').css('float', to );
		});
	});
	} )( jQuery )
	</script>
	<?php

}

add_action('wp_print_styles', function() {

    $options = get_option('campanha_theme_options');
    
    if (isset($options['use_theme_colors']) && $options['use_theme_colors'] == true) {
    
        $brilho = isset($options['theme_colors']['brilho']) ? $options['theme_colors']['brilho'] : '';
        $link = isset($options['theme_colors']['link']) ? $options['theme_colors']['link'] : '';
        $active = isset($options['theme_colors']['active']) ? $options['theme_colors']['active'] : '';
        
        ?>
        <style>
        
        a { color: <?php echo $link; ?>; text-decoration: none; }
        a:active, a:hover {	color: <?php echo $active; ?>; }



        /* =Menu
        -------------------------------------------------------------- */
        #menubar { background: <?php echo $link; ?>; border-bottom: 1px solid <?php echo $active; ?>; }
        #main-nav li { border-right: 1px solid <?php echo $brilho; ?>; background: <?php echo $link; ?>; }
        #main-nav .sub-menu li { border-bottom: 1px solid <?php echo $brilho; ?>; }
        #main-nav ul { border-top: 1px solid <?php echo $brilho; ?>; }
        #main-nav li:first-child a { border-left: 1px solid <?php echo $brilho; ?>; }
        #main-nav a { border-right: 1px solid <?php echo $active; ?>; border-bottom: 1px solid <?php echo $active; ?>; }
        #main-nav a:hover, #main-nav li.current-menu-item>a, #main-nav li.current-menu-ancestor>a, #main-nav li.current-menu-parent>a, #main-nav li.current_page_item>a { background: <?php echo $active; ?>; }
        #main-nav ul a { border-left: 1px solid <?php echo $brilho; ?>; }
        #feed-link { border-left: 1px solid <?php echo $active; ?>; }
        #feed-link a { border-right: 1px solid <?php echo $brilho; ?>; border-left: 1px solid <?php echo $brilho; ?>; }
        #feed-link img { border-right: 1px solid <?php echo $active; ?>; border-bottom: 1px solid <?php echo $active; ?>; }


        /* =Post
        -------------------------------------------------------------- */
        .post-meta a { border-bottom: 1px dotted <?php echo $link; ?>; }
        .post-meta a:hover { border-bottom: 1px solid <?php echo $active; ?>; }


        /* =Comments
        -------------------------------------------------------------- */
        #form-comentario #comentar, #cancel-comment-reply-link { background: <?php echo $link; ?>; border: 1px solid <?php echo $brilho; ?>; border-bottom: 1px solid <?php echo $active; ?>; border-right: 1px solid <?php echo $active; ?>; }
        #form-comentario #comentar:hover, #cancel-comment-reply-link:hover { background: <?php echo $active; ?>; }

        /* =Mobilize
        -------------------------------------------------------------- */
        .mobilize-button, #contact-submit { background-color: <?php echo $link; ?>; border: 1px solid <?php echo $brilho; ?>; border-bottom: 1px solid <?php echo $active; ?>; border-right: 1px solid <?php echo $active; ?>; }
        .mobilize-button:hover, #contact-submit:hover { background-color: <?php echo $active; ?>; }
        </style>
        <?php
    
    }

}, 20);
