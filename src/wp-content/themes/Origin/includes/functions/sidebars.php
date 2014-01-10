<?php
	register_sidebar( array(
		'name' 			=> 'Sidebar',
		'id' 			=> 'sidebar-1',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' 	=> '</div> <!-- end .widget -->',
		'before_title' 	=> '<h4 class="widgettitle">',
		'after_title' 	=> '</h4>',
	) );