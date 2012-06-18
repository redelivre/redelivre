<?php 

// JS
function temavencedor_addJS() {
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); 
}
add_action('wp_print_scripts', 'temavencedor_addJS');

// THEME OPTIONS
require_once ( get_template_directory() . '/theme-options.php' );

// CUSTOM MENU
add_action( 'init', 'temavencedor_custom_menus' );
function temavencedor_custom_menus() {
	register_nav_menus( array(
		'main' => __('Top Menu', 'temavencedor'),
		'rodape' => __('Footer Menu', 'temavencedor'),            
	) );
}

// SIDEBARS
if(function_exists('register_sidebar')) {
	// home sidebar
	register_sidebar( array(
		'name' => 'Home Sidebar',		
		'description' => __('Sidebar', 'temavencedor'),
		'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
    // sidebar 
    register_sidebar( array(
		'name' =>  'Sidebar',		
		'description' => __('Sidebar', 'temavencedor'),
		'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
	) );
}

// EXCERPT MORE
function temavencedor_auto_excerpt_more( $more ) {
	global $post;
	return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'temavencedor') . '</a>';
}

add_filter( 'excerpt_more', 'temavencedor_auto_excerpt_more' );

// SETUP
add_action( 'after_setup_theme', 'temavencedor_setup' );

if (!function_exists('temavencedor_setup')) :

function temavencedor_setup() {

    load_theme_textdomain('temavencedor', TEMPLATEPATH . '/languages' );
    
    // POST THUMBNAILS
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size( 200, 150, true );

    // AUTOMATIC FEED LINKS
    add_theme_support('automatic-feed-links');
    

    // CUSTOM IMAGE HEADER
    define('HEADER_TEXTCOLOR', '000000');
    define('HEADER_IMAGE_WIDTH', 980); 
    define('HEADER_IMAGE_HEIGHT', 176);
        
    add_custom_image_header( 'temavencedor_custom_header', 'temavencedor_admin_custom_header' );

    // CUSTOM BACKGROUND
    add_custom_background();
}

endif;

if (!function_exists('temavencedor_custom_header')) :

function temavencedor_custom_header() {
    ?>
    <style type="text/css">
        #branding { background: url(<?php header_image(); ?>); }
        <?php if ( 'blank' == get_header_textcolor() ) : ?>
			#branding h1, #branding p { display: none; }        
        <?php else: ?>       
			#branding, #branding a, #branding a:hover { color: #<?php header_textcolor(); ?> !important; }
			#branding a:hover { text-decoration: none; }
			#description { filter: alpha(opacity=60); opacity: 0.6; }
        <?php endif; ?>        
    </style>
    <?php

}

endif;

if (!function_exists('temavencedor_admin_custom_header')) :

function temavencedor_admin_custom_header() {
    ?><style type="text/css">
        
        #headimg {
            padding:55px 10px;
            width: 960px !important;
            height: 66px !important;
            min-height: 66px !important;
            font: 15px/22px Georgia,"Times New Roman",Times,serif;
            
            
        }
        
        #headimg h1 {
            font-family: "Trebuchet MS",Arial,sans-serif !important;
            font-size:36px;
            line-height:44px;
            font-weight:normal !important;
            margin: 0px;
            margin: 0 10px;
            
        }
        
        #headimg h1 a {
            text-decoration: none !important;
        }
        
        #headimg #desc { 
            font-style: italic; 
            font-size: 16px; 
            margin: 0 10px;
            filter: alpha(opacity=60);
            opacity: 0.6;
        }

    </style><?php

}

endif;

// COMMENTS

if (!function_exists('temavencedor_comment')): 

function temavencedor_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;  
    ?>
    <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">        

	<p class="comment-meta alignright bottom">
	  <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'temavencedor'), '| ', ''); ?>          
	</p>    
	<p class="comment-meta bottom">
      <?php printf( __('By %s on %s at %s.', 'temavencedor'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
	  <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'temavencedor'); ?></em><?php endif; ?>
	</p>
	<?php echo get_avatar($comment, 66); ?>
	<div class="content">
	  <?php comment_text(); ?>          
	</div>

    </li>
    <?php
}

endif; 
?>
