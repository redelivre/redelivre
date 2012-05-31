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
        <meta name="viewport" content="width=device-width,initial-scale=1.0" />
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
                echo ' | ' . sprintf( __( 'Page %s', 'twentyeleven' ), max( $paged, $page ) );
        ?></title>

        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
        <link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory') ?>/css/print.css" type="text/css" media="print">
        <!--[if lt IE 8]><link rel="stylesheet" href="<?php bloginfo( 'stylesheet_directory') ?>/css/ie.css" type="text/css" media="screen,projection"><![endif]-->
        <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
        <![endif]-->
        <?php wp_head(); ?>
        
    </head>

    <body <?php body_class(); ?>>

        <div id="main-menu-search-social-bookmarks" class="container clearfix">
            <?php wp_nav_menu(array("theme_location" => "menu_1", "container" => "nav", "container_class" => "main_menu_1 span-15 clearfix", "depth" => 1)) ?>

            <div id="search-and-social-bookmarks" class="span-9 last">
                <div id="social-bookmarks" class="alignright">
                    <a id="facebook" href="" title="Facebook"></a>
                    <a id="twitter" href="" title="Twitter"></a>
                    <a id="youtube" href="" title="YouTube"></a>
                    <a id="rss" href="" title="RSS"></a>
                </div>
                <form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="alignright">
                    <input type="text" name="s" id="s" placeholder="<?php esc_attr_e( 'Buscar no site', 'memoriasdoesporte' ); ?>" />
                    <input type="image" src="<?php echo html::getImageUrl("search.png"); ?>" />
                </form>
            </div>
        </div>
        
        <header id="branding" class="container clearfix" role="banner">
            <hgroup>
                <h1 id="site-title"><span><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></span></h1>
                <h2 id="site-description"><?php bloginfo( 'description' ); ?></h2>
                <h3>69171</h3>
            </hgroup>

            <?php
                // Check to see if the header image has been removed
                $header_image = get_header_image();
                if ( ! empty( $header_image ) ) :
            ?>
            <div id="branding-image">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php
                        // The header image
                        // Check if this is a post or page, if it has a thumbnail, and if it's a big one
                        if ( is_singular() &&
                                has_post_thumbnail( $post->ID ) &&
                                ( /* $src, $width, $height */ $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( HEADER_IMAGE_WIDTH, HEADER_IMAGE_WIDTH ) ) ) &&
                                $image[1] >= HEADER_IMAGE_WIDTH ) :
                            // Houston, we have a new header image!
                            echo get_the_post_thumbnail( $post->ID, 'post-thumbnail' );
                        else : ?>
                        <img src="<?php header_image(); ?>" width="<?php echo HEADER_IMAGE_WIDTH; ?>" height="<?php echo HEADER_IMAGE_HEIGHT; ?>" alt="" />
                    <?php endif; // end check for featured image or standard header ?>
                </a>
            </div>
            <?php endif; // end check for removed header image ?>       

            <?php wp_nav_menu(array("theme_location" => "menu_2", "container" => "nav", "container_class" => "main_menu_2 container clearfix", "depth" => 3)) ?>
        </header><!-- #branding -->
