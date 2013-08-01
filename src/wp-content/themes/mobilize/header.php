<!doctype html>
	<!--[if IE 6]>
	<html id="ie6" lang="pt-BR">
	<![endif]-->
	<!--[if IE 7]>
	<html id="ie7" lang="pt-BR">
	<![endif]-->
	<!--[if IE 8]>
	<html id="ie8" lang="pt-BR">
	<![endif]-->
	<!--[if !(IE 6) | !(IE 7) | !(IE 8) ]><!-->
	<html lang="pt-BR" <?php language_attributes(); ?>>
	<!--<![endif]-->
	<!--[if lt IE 9]>
	<script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
  <head>
    <title>
	    
	    <?php
	    if(is_home()){
			echo get_bloginfo('name') . ' - ' . get_bloginfo('description');
	    } else {
		    wp_title(' - ', true, 'right');
		    bloginfo('name');
	    }
	    ?>
	    
	    
    </title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- meta/rss/trackacks -->
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    
    <!-- fonte -->
    <link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,300italic,400italic,700,700italic' rel='stylesheet' type='text/css'>

    <!-- wp-head -->
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<header id="masterheader">
	    <div class="container">
	    <div class="row header">
	  
	    
	        
	        <div class="span4">
	        	<div class="brand">
	        		<?php if(get_theme_mod('logo')) : ?>
		        		<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
		            		<img src="<?php echo get_theme_mod('logo'); ?>" alt="<?php bloginfo ( 'name' ); ?>" />
		            	</a>
	            	<?php else : ?>
	            	<a href="<?php echo home_url(); ?>">
	            		<span class="header-title"><?php echo bloginfo('name'); ?></span>
	            	</a>
	            	<?php endif; ?>
	            </div>
		     </div>
		        	
		        	<div class="right offset4 span4">
			        	<div class="social">
			              <?php do_action('campanha_body_header'); ?>
			             </div> 
		              	<div class="busca">
		             		<?php get_search_form(); ?>
		             	</div>
	             	</div>
	         
	        </div>
	    </div>

	
	    <div class="menu bg-cor-1">
	        <div class="container">
	         <div class="main-menu bg-cor">
	            <?php wp_nav_menu( array( 'menu' => 'main', 'theme_location' => 'primary',  ) ); ?>
	         </div>
	        </div>
	    </div>
	</header>

	<div class="container">

