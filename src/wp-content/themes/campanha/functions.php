<?php 
/*
 * IMPORTANTE
 * substituir todos os campanha pelo campanha do projeto
 */

include dirname(__FILE__).'/includes/congelado-functions.php';
include dirname(__FILE__).'/includes/html.class.php';
include dirname(__FILE__).'/includes/utils.class.php';

if (is_admin()) {
    require(TEMPLATEPATH . '/custom_admin.php');
}

add_action( 'after_setup_theme', 'campanha_setup' );
function campanha_setup() {

    load_theme_textdomain('campanha', TEMPLATEPATH . '/languages' );

    // POST THUMBNAILS
    add_theme_support('post-thumbnails');
    //set_post_thumbnail_size( 200, 150, true );

    //REGISTRAR AQUI TODOS OS TAMANHOS UTILIZADOS NO LAYOUT
    //add_image_size('nome',X,Y);
    //add_image_size('nome2',X,Y);

    // AUTOMATIC FEED LINKS
    add_theme_support('automatic-feed-links');

}


// REDIRECIONAMENTOS
add_filter('rewrite_rules_array', 'custom_url_rewrites', 10, 1);
function custom_url_rewrites($rules) {
    $new_rules = array(
        "cadastro/?$" => "index.php?tpl=cadastro",
    );
    
    return $new_rules + $rules;
}

add_action('template_redirect', 'template_redirect_intercept');
function template_redirect_intercept() {
    global $wp_query;

    switch ($wp_query->get('tpl')) {
        case 'cadastro':
            require(TEMPLATEPATH . '/register.php');
            die;
        default:
            break;
    }
}

function cadastro_url() {
    return home_url() . '/cadastro';
}

add_filter('query_vars', 'custom_query_vars');
function custom_query_vars($public_query_vars) {
    $public_query_vars[] = "tpl";

    return $public_query_vars;
}

function print_msgs($msg, $extra_class='', $id='') {
    if (!is_array($msg)) {
        return false;
    }

    foreach ($msg as $type => $msgs) {
        if (!$msgs) {
            continue;
        }
        
        echo "<div class='$type $extra_class' id='$id'><ul>";
        
        if (!is_array($msgs)) {
            echo "<li>$msgs</li>";
        } else {
            foreach ($msgs as $m) {
                echo "<li>$m</li>";
            }
        }
        
        echo "</ul></div>";
    }
}

// admin_bar removal
//wp_deregister_script('admin-bar');
//wp_deregister_style('admin-bar');
remove_action('wp_footer','wp_admin_bar_render',1000);
function remove_admin_bar(){
   return false;
}
add_filter( 'show_admin_bar' , 'remove_admin_bar');

// JS
add_action('wp_print_scripts', 'campanha_addJS');
function campanha_addJS() {
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); 
    wp_enqueue_script('jquery');
    wp_enqueue_script('congelado', get_stylesheet_directory_uri().'/js/congelado.js', 'jquery');
    wp_enqueue_script('campanha', get_stylesheet_directory_uri().'/js/campanha.js', 'jquery');
    
}

// CUSTOM MENU
add_action( 'init', 'campanha_custom_menus' );
function campanha_custom_menus() {
    register_nav_menus( array(
        'main' => __('Principal', 'campanha'),
        'sobre' => __('Sobre o Campanha Completa', 'campanha'),
        'info' => __('Informações Legais', 'campanha'),
        
    ) );
}

// SIDEBARS
if(function_exists('register_sidebar')) {
    // sidebar 
    register_sidebar( array(
        'name' =>  'Sidebar',
        'description' => __('Sidebar', 'campanha'),
        'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
        'after_widget' => '</div></div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ) );
}

// EXCERPT MORE

add_filter('utils_excerpt_more_link', 'campanha_utils_excerpt_more',10,2);
function campanha_utils_excerpt_more($more_link, $post){
    return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'campanha') . '</a>';
}


add_filter( 'excerpt_more', 'campanha_auto_excerpt_more' );
function campanha_auto_excerpt_more( $more ) {
    global $post;
    return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'campanha') . '</a>';
}

// COMMENTS
if (!function_exists('campanha_comment')):

    function campanha_comment($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;
        ?>
        <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">
            <p class="comment-meta alignright bottom">
                <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'campanha'), '| ', ''); ?>          
            </p>    
            <p class="comment-meta bottom">
                <?php printf( __('By %s on %s at %s.', 'campanha'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
                <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'campanha'); ?></em><?php endif; ?>
            </p>
            <?php echo get_avatar($comment, 48); ?>
            <div class="content">
                <?php comment_text(); ?>
            </div>
        </li>
        <?php
    }

endif; 

// custom admin login logo
function custom_login_logo() {
	echo '
		<link rel="stylesheet" type="text/css" media="all" href=" '.get_bloginfo( 'stylesheet_url' ).'" />
        <style type="text/css">
			
	        .login h1 a { height: 180px; background-image: url('. html::getImageUrl('logo.png') .'); }
	        .login form { padding: 26px 24px 62px; margin: 0; background: #042244; border: none; border-radius: 4px; box-shadow: 0px 0px 30px rgba(0,0,0, .3) inset, 1px 1px 0 rgba(250,250,250, .3); }
	        .login input.button-primary { padding: 0 10px; border: none; border-radius: 4px !important; font-size: 1.125em !important; font-weight: normal; }
	        .login input.button-primary:hover { color: #ffce33; }
	        .login form .input { height: 36px; margin-bottom: 24px; background: #7c9dc6; border: none; border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px; padding: 6px; font: 1em "Delicious", "Trebuchet", sans-serif; box-shadow: 0px 0px 30px rgba(0,0,0, .3) inset; color: #1b3c64; }	        
	        .login label { color: #ffce33; }
	        .login #nav, .login #backtoblog { text-shadow: 0 -1px 0 #000; }
	        .login #nav a, .login #backtoblog a { color: #ffce33 !important; }
	        .login #nav a:hover, .login #backtoblog a:hover { color: #149648 !important; }
	        #login { padding-top: 48px; width: 280px; }
	        div.updated, .login .message { background: none; border: 1px solid #c5dcfa; }
	        div.error, .login #login_error { background: none; margin-left: 0; }
	        @media screen and (max-width: 320px) { .login h1 { display: none; } #login { padding-top: 24px;} }
        </style>';
}
add_action('login_head', 'custom_login_logo');

function new_headertitle($url){
    return get_bloginfo('sitetitle');
}
add_filter('login_headertitle','new_headertitle');

function custom_login_headerurl($url) {
    return get_bloginfo('siteurl');

}
add_filter ('login_headerurl', 'custom_login_headerurl');
