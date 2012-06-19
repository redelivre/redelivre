<?php
$sidebar = get_option('vencedor_theme_options');
?>
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
		echo ' | Página' . max( $paged, $page );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<!--[if lte IE 7]>
	<link rel="stylesheet" type="text/css" media="all" href="<?php echo get_template_directory_uri() ?>/ie-hacks.css" />
<![endif]-->
<!--[if lt IE 9]>
	<script src="<?php get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php if ($sidebar): ?>
	<?php if ( $sidebar["sidebar_position"] == 'esquerda') : ?>
		<style>
			#content { float: right; }
		</style>
	<?php endif; ?>
<?php endif; ?>

<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>
<header id="main-header">
	<div id="branding" class="wrap clearfix">
		<h1 class="col-12"><a href="<?php echo home_url(); ?>" title="<?php bloginfo( 'name' ); ?>"><?php bloginfo( 'name' ); ?></a> - <?php global $campaign; echo $campaign->candidate_number; ?></h1>
		<p id="description" class="col-12"><?php bloginfo( 'description' ); ?></p>			
	</div>
	<!-- .wrap -->
	<div id="menubar">
		<div class="wrap clearfix">
			<?php wp_nav_menu( array( 'menu' => 'main', 'theme_location' => 'main', 'container' => '', 'menu_id' => 'main-nav', 'menu_class' => 'clearfix', 'fallback_cb' => '', 'depth' => '3',) ); ?>
			<div id="feed-link"><a href="<?php bloginfo('rss_url'); ?>" title="RSS Feed"><img src="<?php echo get_template_directory_uri(); ?>/img/feed-icon-24x24.png" /></a></div>
		</div>
		<!-- .wrap -->
	</div>
	<!-- #menubar -->
</header>
<!-- #main-header -->

