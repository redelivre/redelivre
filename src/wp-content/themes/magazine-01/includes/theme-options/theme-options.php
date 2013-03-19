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
    
    $wp_customize->add_setting( 'campanha_theme_options[theme_colors][titles]', array(
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
    
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'theme_colors_titles', array(
        'label'   => 'Títulos',
        'section' => 'theme_colors',
        'settings'   => 'campanha_theme_options[theme_colors][titles]',
    ) ) );
    
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
    
        $title = isset($options['theme_colors']['titles']) ? $options['theme_colors']['titles'] : '';
        $link = isset($options['theme_colors']['link']) ? $options['theme_colors']['link'] : '';
        $active = isset($options['theme_colors']['active']) ? $options['theme_colors']['active'] : '';
        
        ?>
        <style>
        
        <?php if ($title): ?>
            /* =Fonts
            -------------------------------------------------------------- */
            h1,h2,h3,h4,h5,h6 { color: <?php echo $title; ?>; }
            
            /*post content*/
            .post-content label, .post-content .label { color: <?php echo $title; ?>; }
        <?php endif; ?>
        
        <?php if ($link): ?>
            /* =Global Elements
            -------------------------------------------------------------- */

            a { color: <?php echo $link; ?>; }
            
            /* =Home Features
            -------------------------------------------------------------- */
            .hl-nav-left { background-color: <?php echo $link; ?>; }
            .hl-nav-right { background-color: <?php echo $link; ?>; }
            
            /*paged post/comments navigation*/
            .page-link a:hover, .page-link span.current { background: <?php echo $link; ?>; }
            .page-link a.prev:hover, .page-link a.next:hover { color: <?php echo $link; ?>; }
            .post-content tr th, .post-content thead th { background: <?php echo $link; ?>; }

            /* =Menu
            -------------------------------------------------------------- */
            #main-nav a { background: <?php echo $link; ?>; }

            /* =Comments
            -------------------------------------------------------------- */
            #form-comentario #comentar, #cancel-comment-reply-link, .button-submit { background: <?php echo $link; ?>; }
            .comment-author { color: <?php echo $link; ?>; }
            
            /* =Widgets
            -------------------------------------------------------------- */
            #wp-calendar caption { background: <?php echo $link; ?>; }
            
            /* =Contato
            -------------------------------------------------------------- */
            #contact-submit {background: <?php echo $link; ?>; }
            
            /* =Mobilize
            -------------------------------------------------------------- */
            input.mobilize-button { background-color: <?php echo $link; ?>; }
        <?php endif; ?>

        <?php if ($active): ?>
            /* =Post
            -------------------------------------------------------------- */
            .post header p a, .post footer p a { color: <?php echo $active; ?>; }

            /* =Menu
            -------------------------------------------------------------- */
            #main-nav a:hover, #main-nav li.current-menu-item>a, #main-nav li.current-menu-ancestor>a, #main-nav li.current-menu-parent>a, #main-nav li.current_page_item>a { background: <?php echo $active; ?>; }

            
            /* =Comments
            -------------------------------------------------------------- */
            #form-comentario #comentar:hover, #cancel-comment-reply-link:hover, .button-submit:hover { background: <?php echo $active; ?>; }


            /* =Mobilize
            -------------------------------------------------------------- */
            input.mobilize-button:hover { background-color: <?php echo $active; ?>; }


            /* =Contato
            -------------------------------------------------------------- */
            #contact-submit:hover { background: <?php echo $active; ?>; }
        <?php endif; ?>
        </style>
        <?php
    
    }

}, 20);
