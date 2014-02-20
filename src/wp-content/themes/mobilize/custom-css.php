<style type="text/css">
	
	a { color: <?php echo get_theme_mod('cor-1'); ?>; }
	a:hover { color: <?php echo get_theme_mod('cor-2'); ?> }
	
	.bg-cor-1{ background: <?php echo get_theme_mod('cor-1'); ?>; }
	.bg-cor-2{ background: <?php echo get_theme_mod('cor-2'); ?>; }
	
	.borda-cor-1{ border-color:<?php echo get_theme_mod('cor-1'); ?>; }
	.borda-cor-2{ border-color: <?php echo get_theme_mod('cor-2'); ?>; }
	
	.main-menu.bg-cor .sub-menu { background: <?php echo get_theme_mod('cor-1'); ?>; }
	
	body { background-image: url('<?php echo get_theme_mod('background-imagem'); ?>'); background-color: <?php echo get_theme_mod('background-cor'); ?> }
	#facebook, #twitter, #google-plus, #youtube, #rss { background-color:  <?php echo get_theme_mod('cor-1'); ?>; }
	#facebook:hover, #twitter:hover, #google-plus:hover, #youtube:hover, #rss:hover { background-color: <?php echo get_theme_mod('cor-2'); ?>; }

	@media only screen and (max-width: 600px) {
		ul.menu li a:active {
			background: <?php echo get_theme_mod('cor-2'); ?>;
		}

		ul.menu-tmp li a:active {
			background: <?php echo get_theme_mod('cor-2'); ?>;
		}
	}
</style>