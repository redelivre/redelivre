<?php 

include('includes/theme-options.php');

// JS
function blog01_addJS() {
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); 
}
add_action('wp_print_scripts', 'blog01_addJS');

// EDITOR STYLE
add_editor_style('editor-style.css');

// LARGURA DA COLUNA DE POSTS PARA OS EMBEDS DE VÍDEOS
global $content_width;
if ( !isset( $content_width ) )
$content_width = 600;

// CUSTOM MENU
add_action( 'init', 'blog01_custom_menus' );
function blog01_custom_menus() {
	register_nav_menus( array(
		'main' => __('Top Menu', 'blog01'),
		'footer' => __('Footer Menu', 'blog01'),
	) );
}

// SIDEBARS
if(function_exists('register_sidebar')) {
	// home sidebar
	register_sidebar( array(
		'name' => 'Home Sidebar',		
		'description' => __('Sidebar', 'blog01'),
		'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
		'id'            => 'sidebar-1'
	) );
    // sidebar 
    register_sidebar( array(
		'name' =>  'Sidebar',		
		'description' => __('Sidebar', 'blog01'),
		'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
		'after_widget' => '</div></div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>',
    	'id'            => 'sidebar-2'
	) );
}

// EXCERPT MORE
function blog01_auto_excerpt_more( $more ) {
	global $post;
	return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'blog01') . '</a>';
}

add_filter( 'excerpt_more', 'blog01_auto_excerpt_more' );

// SETUP
add_action( 'after_setup_theme', 'blog01_setup' );

if (!function_exists('blog01_setup')) :

function blog01_setup() {

    load_theme_textdomain('blog01', get_template_directory() . '/languages' );
    
    // POST THUMBNAILS
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size( 200, 150, true );

    // AUTOMATIC FEED LINKS
    add_theme_support('automatic-feed-links');
    
    $args = array(
        //'default-image'          => get_template_directory_uri() . '/images/bg-default.png',
        'default-color'          => '#F1F1F1',
        //'wp-head-callback'       => '',
        //'admin-head-callback'    => '',
        //'admin-preview-callback' => ''
    );

    add_theme_support( 'custom-background', $args );
    
     // Custom Header Image
    $args = array(
    'flex-width'    => true,
    'width'         => 960,
    'flex-height'    => true,
    'height'        => 198,
    //'default-image' => get_template_directory_uri() . '/images/default-header.jpg',
    'uploads'       => true,
    'wp-head-callback' => 'blog01_custom_header',
    'default-text-color' => '000000'
    );
    add_theme_support( 'custom-header', $args );
}

endif;

if (!function_exists('blog01_custom_header')) :

function blog01_custom_header() {
    
    $custom_header = get_custom_header();
    
    
    ?>
    <style type="text/css">
                
        #branding { background: url(<?php header_image(); ?>) no-repeat; height: <?php echo $custom_header->height; ?>px;}
        <?php if ( 'blank' == get_header_textcolor() ) : ?>
			#branding a { height: <?php echo $custom_header->height; ?>px; }
			#branding a:hover { background: none !important; }    
        <?php else: ?>       
			#branding, #branding a, #branding a:hover { color: #<?php header_textcolor(); ?> !important; }
			#branding a:hover { text-decoration: none; }
			#description { filter: alpha(opacity=60); opacity: 0.6; }
        <?php endif; ?>        
    </style>
    <?php

}

endif;


// COMMENTS

if (!function_exists('blog01_comment')): 

function blog01_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;  
    ?>
    <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">        

	<p class="comment-meta alignright bottom">
	  <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'blog01'), '| ', ''); ?>          
	</p>    
	<p class="comment-meta bottom">
      <?php printf( __('By %s on %s at %s.', 'blog01'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
	  <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'blog01'); ?></em><?php endif; ?>
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
