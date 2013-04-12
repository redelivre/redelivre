<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
    /*
     * Print the <title> tag based on what is being viewed.
     */
    global $page, $paged, $pageTitle;

    wp_title( '|', true, 'right' );

    // Add the blog name.
    bloginfo( 'name' );

    // Add the blog description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
        echo " | $site_description";

    // Add a page number if necessary:
    if ( $paged >= 2 || $page >= 2 )
        echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

    ?></title>
<?php wp_head(); ?>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />

<!--[if lte IE 7]>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_directory'); ?>/css/ie.css" />
<![endif]-->
<!--[if lt IE 9]>
    <script src="<?php bloginfo('stylesheet_directory'); ?>/js/iepp.1-6-2.js"></script>
<![endif]-->

<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />



</head>

<body <?php body_class(); ?>>
    <div id="overlay"></div>
        <div class="container">
            <header id="main-header" class="clearfix">
                <?php do_action('campanha_body_header'); ?>
                <div class="span-17 clearfix">
                    <div id="login">
                        <?php if (is_user_logged_in()): ?>
                            Olá, 
                            <?php do_action('consulta_show_user_link'); ?>
                            |
                            <a href="<?php echo wp_logout_url(get_bloginfo('url')) ; ?>">
                                <?php _e("Sair", "tnb"); ?>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo site_url('wp-login.php'); ?>">Log in</a>
                            <?php if (get_option('users_can_register')) : ?>
                                | <a href="<?php echo home_url('wp-login.php?action=register'); ?>">Cadastro</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php
                    
                    $datafinal = strtotime(get_theme_option('data_encerramento'));
                    
                    if ($datafinal) {
                        if ($datafinal > time()) {
                            $intervalo = $datafinal - time();
                            $dias = $intervalo / 60 / 60 / 24;
                            $dias = (int) $dias + 1;
                        } else {
                            $dias = -1;
                        }
                    }
                    ?>
                    
                    <p id="cronometro">
                        A consulta
                        <?php if ($dias > 0): ?>
                             se encerra em <span><?php echo $dias; ?></span> dia<?php if ($dias > 1) echo 's'; ?>
                        <?php elseif (get_theme_option('data_encerramento') == date('Y-m-d')): ?>
                             se encerra hoje
                        <?php else: ?>    
                            está encerrada
                        <?php endif; ?>
                    </p>
                </div>
                <form id="busca" class="span-6" role="search" method="get" action="<?php echo home_url( '/' ); ?>">
                    <input id="s" type="search" value="buscar" name="s" onfocus="if (this.value == 'buscar') this.value = '';" onblur="if (this.value == '') {this.value = 'buscar';}" />        
                </form>
                
                <div id="branding" class="clear">
                    <?php if ( 'blank' == get_header_textcolor() ) : ?>
                        <a id="header-image-link" href="<?php bloginfo( 'url' ); ?>" title="<?php bloginfo( 'name' ); ?>"></a>
                    <?php else: ?>
                        <h1><a href="<?php echo home_url(); ?>" title="<?php bloginfo( 'name' ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
                        <p id="description" class="col-12"><?php bloginfo( 'description' ); ?></p>
                    <?php endif; ?>    
                </div>
                <nav id="main-nav" class="span-22 prepend-2 last clearfix">
                    
                    
                    <?php wp_nav_menu( array( 'menu' => 'main', 'theme_location' => 'main', 'container' => '', 'menu_id' => 'main-menu', 'menu_class' => 'clearfix', 'fallback_cb' => 'consulta_default_menu', 'depth' => '3',) ); ?>
                </nav>
            </header>
            <!-- #main-header -->
