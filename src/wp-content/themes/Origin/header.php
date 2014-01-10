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
	<title><?php elegant_titles(); ?></title>
	<?php elegant_description(); ?>
	<?php elegant_keywords(); ?>
	<?php elegant_canonical(); ?>

	<?php do_action('et_head_meta'); ?>

	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<script type="text/javascript">
		document.documentElement.className = 'js';
	</script>

	<?php wp_head(); ?>

	<!--[if lt IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
		<script src="<?php echo get_template_directory_uri(); ?>/js/respond.min.js" type="text/javascript"></script>
	<![endif]-->
</head>
<body <?php body_class(); ?>>
	<div id="main-wrap" class="clearfix">
		<?php do_action('et_header_top'); ?>

		<div id="info-bg"></div>

		<div id="info-area">
			<div id="logo-area">
				<?php $logo = ( ( $user_logo = et_get_option('origin_logo') ) && '' != $user_logo ) ? $user_logo : get_template_directory_uri() . "/images/logo.png"; ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_attr( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" id="logo"/></a>
			</div> <!-- #logo-area -->

			<?php do_action('et_header_menu'); ?>

			<span id="mobile-nav"><?php esc_html_e( 'Navigation Menu', 'Origin' ); ?><span>+</span></span>

			<nav id="top-menu">
				<?php
					$menuClass = 'nav';
					$primaryNav = '';

					if ( 'on' == et_get_option( 'origin_disable_toptier' ) ) $menuClass .= ' et_disable_top_tier';

					$primaryNav = wp_nav_menu( array( 'theme_location' => 'primary-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'echo' => false ) );

					if ( '' == $primaryNav ) { ?>
						<ul class="<?php echo esc_attr( $menuClass ); ?>">
							<?php if ( 'on' == et_get_option( 'origin_home_link' ) ) { ?>
								<li <?php if ( is_home() ) echo( 'class="current_page_item"' ); ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e('Home','Origin') ?></a></li>
							<?php } ?>

							<?php show_page_menu( $menuClass, false, false ); ?>
							<?php show_categories_menu( $menuClass, false ); ?>
						</ul>
				<?php }
					else echo($primaryNav);
				?>
			</nav>

			<?php get_sidebar(); ?>
		</div> <!-- #info-area -->

		<div id="main">
			<div id="wrapper">