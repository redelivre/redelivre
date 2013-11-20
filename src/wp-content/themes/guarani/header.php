<?php
/**
 * The Header
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package Guarani
 * @since Guarani 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<!--[if lt IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
		<![endif]-->
		
		<?php wp_head(); ?>
	</head>
	
	<body <?php body_class(); ?>>
	<!--[if lt IE 7]>
	<p class="browse-happy">
	 	<?php _e( 'You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.', 'guarani' ); ?>
	</p>
	<![endif]-->
	<?php $guarani_front_content_bg_color = get_option('guarani_front_content_bg_color'); ?>
	<div class="site-wrapper hfeed" <?php echo 'style="background-color:'.$guarani_front_content_bg_color.'" ' ?> >
		<?php do_action( 'before' ); ?>
		<header id="masthead" class="site-header cf" role="banner">
		
			<?php get_search_form(); ?>
			
			<div class="branding">
				<?php
				// Get the current color scheme 
				$color_scheme = get_theme_mod( 'guarani_color_scheme' );
				
				// Check if there's a custom logo
				$logo = get_theme_mod( 'guarani_logo' );
				$logo_uri = get_template_directory_uri() . '/images/schemes/logo-undefined.png';
				if( $logo )
				{
					$logo_uri =  $logo; 
				}
				elseif (isset($color_scheme) && $color_scheme != "")
				{
					$logo = get_template_directory_uri() . '/images/schemes/logo-' . $color_scheme . '.png';
					
					$logo_uri = $logo;
				}
				
				?>
				<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
					 <img class="site-logo" src="<?php echo $logo_uri; ?>" alt="Logo <?php bloginfo ( 'name' ); ?>" />
				</a>
				
				<h1 class="site-title">
					<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
					   	<?php bloginfo( 'name' ); ?>
					</a>
				</h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
			</div>
			
			
	
			<nav role="navigation" class="site-navigation main-navigation">
				<h1 class="assistive-text"><?php _e( 'Menu', 'guarani' ); ?></h1>
				<div class="clearfix"></div>
				<div class="assistive-text skip-link"><a href="#content" title="<?php esc_attr_e( 'Skip to content', 'guarani' ); ?>"><?php _e( 'Skip to content', 'guarani' ); ?></a></div>
				<?php wp_nav_menu( array( 'menu' => 'main', 'theme_location' => 'primary' ) ); ?>
			</nav><!-- .site-navigation .main-navigation -->
		</header><!-- #masthead .site-header -->
	
		<section id="main" class="main cf">