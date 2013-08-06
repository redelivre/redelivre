<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        <meta name="viewport" content="initial-scale=1.0,width=device-width" />
        <title><?php
            /* Print the <title> tag based on what is being viewed. */
            global $page, $paged;

            wp_title( '|', true, 'right' );

            // Add the blog name.
            bloginfo( 'name' );

            // Add the blog description for the home/front page.
            $site_description = get_bloginfo( 'description', 'display' );
            if ( $site_description && ( is_home() || is_front_page() ) )
                echo " | $site_description";

            // Add a page number if necessary:
            if ( $paged >= 2 || $page >= 2 )
                echo ' | ' . sprintf( __( 'Page %s', 'campanha' ), max( $paged, $page ) );
        ?></title>

        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
        <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />        
        <link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory') ?>/css/print.css" type="text/css" media="print">
        <!--[if lt IE 8]>
			<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory') ?>/css/ie.css" type="text/css" media="screen,projection">
		<![endif]-->
        <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
        <script src="<?php echo get_template_directory_uri(); ?>/js/respond.min.js" type="text/javascript"></script>
        <![endif]-->
        <?php wp_head(); ?>
        
    </head>

    <body <?php body_class(); ?>>
		<div class="wrap clearfix">
			<header id="main-header" class="clearfix">
				<h1><a href="<?php bloginfo( 'url' ); ?>" title="<?php bloginfo( 'name' ); ?>"><?php html::image('logo.png', 'Projetos') ?></a></h1>	
				<nav id="main-nav" class="clearfix">
				    <?php if (is_user_logged_in()): ?>
					    <a class="login" href="<?php echo admin_url(); ?>">admin</a>
					<?php else: ?>
					    <a class="login" href="<?php echo wp_login_url(get_permalink()); ?>">login</a>
					<?php endif; ?>
					<?php wp_nav_menu( array( 'theme_location' => 'main', 'container' => '', 'menu_id' => 'main-menu', 'menu_class' => 'clearfix', 'fallback_cb' =>'', 'depth' => '1') ); ?>
				</nav>
				<!-- #main-nav -->
			</header>
			<!-- #main-header -->
			
