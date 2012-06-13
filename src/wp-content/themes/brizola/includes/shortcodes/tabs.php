<?php 

// Skeleton WP (http://demos.simplethemes.com/skeleton/)

add_shortcode( 'tabgroup', 'st_tabgroup' );
function st_tabgroup( $atts, $content ){
	
	$GLOBALS['tab_count'] = 0;
	do_shortcode( $content );

	if( is_array( $GLOBALS['tabs'] ) ){
	
		foreach( $GLOBALS['tabs'] as $tab ){
			$tabs[] = '<li><a href="#'.$tab['id'].'">'.$tab['title'].'</a></li>';
			$panes[] = '<li id="'.$tab['id'].'Tab">'.$tab['content'].'</li>';
		}
	$return = "\n".'<!-- the tabs --><ul class="tabs">'.implode( "\n", $tabs ).'</ul>'."\n".'<!-- tab "panes" --><ul class="tabs-content">'.implode( "\n", $panes ).'</ul>'."\n";
	}
	return $return;
}

add_shortcode( 'tab', 'st_tab' );
function st_tab( $atts, $content ){
	extract(shortcode_atts(array(
		'title' => '%d',
		'id' => '%d'
	), $atts));

	$x = $GLOBALS['tab_count'];
	$GLOBALS['tabs'][$x] = array(
		'title' => sprintf( $title, $GLOBALS['tab_count'] ),
		'content' =>  do_shortcode($content),
		'id' =>  $id );

	$GLOBALS['tab_count']++;
}
?>
