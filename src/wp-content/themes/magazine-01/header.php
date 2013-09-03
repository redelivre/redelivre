<?php
$sidebar = get_option('campanha_theme_options');
?>
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
        <meta name="viewport" content="width=device-width" />
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
                echo ' | Página' . max( $paged, $page );
        ?></title>

        <link rel="profile" href="http://gmpg.org/xfn/11" />
        <link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
        <!--[if lt IE 8]><link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/ie-hacks.css" type="text/css" media="screen,projection"><![endif]-->
        <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
        <![endif]-->
        <?php if ($sidebar): ?>
			<?php if ( $sidebar["sidebar_position"] == 'right') : ?>
				<style>
					#main-sidebar { float: right; }
				</style>
			<?php endif; ?>
		<?php endif; ?>

        <?php wp_head(); ?>
        
    </head>

    <body <?php body_class(); ?>>
		<div class="wrap clearfix">
        <header id="main-header" class="clearfix">

        
			<div class="col-12 clearfix">
				<?php wp_nav_menu( array( 'theme_location' => 'quick-links', 'container' => '', 'menu_id' => 'quick-links', 'menu_class' => 'clearfix alignleft', 'depth' => 1, 'fallback_cb' =>'') ); ?>
				<?php do_action('campanha_body_header'); ?>
			</div>        
            <div id="branding" class="clear clearfix">
				<?php if ( 'blank' == get_header_textcolor() ) : ?>
					<a id="header-image-link" href="<?php bloginfo( 'url' ); ?>" title="<?php bloginfo( 'name' ); ?>"></a>
					
                <?php else: ?>
					<h1 class="col-12"><a href="<?php bloginfo( 'url' ); ?>" title="<?php bloginfo( 'name' ); ?>"><?php bloginfo( 'name' ); ?> - <?php global $campaign; if(is_object($campaign)) echo $campaign->candidate_number; ?></a></h1>
					<p id="description" class="col-12"><?php bloginfo( 'description' ); ?></p>
                <?php endif; ?>
            </div>
            <!-- .wrap -->            
			<?php wp_nav_menu( array( 'menu' => 'main', 'theme_location' => 'main', 'container' => '', 'menu_id' => 'main-nav', 'menu_class' => 'clearfix', 'fallback_cb' => '', 'depth' => '3',) ); ?>
        </header>
        <!-- #main-header -->
		
