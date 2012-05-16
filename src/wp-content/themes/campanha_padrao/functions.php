<?php 
/*
 * IMPORTANTE
 * substituir todos os SLUG pelo slug do projeto
 */

include dirname(__FILE__).'/includes/congelado-functions.php';
include dirname(__FILE__).'/includes/html.class.php';
include dirname(__FILE__).'/includes/utils.class.php';

$campaign = Campaign::getByBlogId($blog_id);

add_action('template_redirect', 'campanha_check_payment_status');
/**
 * Check the payment status and mark the blog
 * as private in case payment is pending.
 */
function campanha_check_payment_status() {
    global $campaign;

    $user_id = get_current_user_id();

    if (!$campaign->isPaid() && $campaign->campaignOwner->ID !== $user_id
        && !is_super_admin())
    {
        wp_redirect(wp_login_url());
    }
}

add_filter('login_message', 'campanha_login_payment_message');
/**
 * Display a message in the login page about the
 * payment.
 * 
 * @param string $message
 * @return string
 */
function campanha_login_payment_message($message) {
    global $campaign;
    
    if (!$campaign->isPaid()) {
        $message .= '<p class="message">Está campanha está visível somente para o criador pois o pagamento está pendente.</p>';
    }
    
    return $message;
}

add_action('admin_notices', 'campanha_admin_payment_message');
/**
 * Display a message in the admin panel about
 * the payment.
 */
function campanha_admin_payment_message() {
    global $campaign;
    
    if (!$campaign->isPaid()) {
        $link = '/wp-admin/admin.php?page=payments';
        echo "<div class='error'><p>Está campanha está visível somente para o criador pois o pagamento está pendente. <a href='$link'>Pague agora!</a></p></div>";
    }
}

add_action( 'after_setup_theme', 'SLUG_setup' );
function SLUG_setup() {

    load_theme_textdomain('SLUG', TEMPLATEPATH . '/languages' );

    // POST THUMBNAILS
    add_theme_support('post-thumbnails');
    //set_post_thumbnail_size( 200, 150, true );

    //REGISTRAR AQUI TODOS OS TAMANHOS UTILIZADOS NO LAYOUT
    //add_image_size('nome',X,Y);
    //add_image_size('nome2',X,Y);

    // AUTOMATIC FEED LINKS
    add_theme_support('automatic-feed-links');

    // CUSTOM IMAGE HEADER
    define('HEADER_TEXTCOLOR', '000000');
    define('HEADER_IMAGE_WIDTH', 980); 
    define('HEADER_IMAGE_HEIGHT', 176);

    add_custom_image_header( 'SLUG_custom_header', 'SLUG_admin_custom_header' );

    register_default_headers( array(
        'Mundo' => array(
            'url' => '%s/img/headers/image001.jpg',
            'thumbnail_url' => '%s/img/headers/image001-thumbnail.jpg',
        ),
        'Árvores' => array(
            'url' => '%s/img/headers/image002.jpg',
            'thumbnail_url' => '%s/img/headers/image002-thumbnail.jpg',
            'description' => 'barco'
        ),
        'Caminho' => array(
            'url' => '%s/img/headers/image003.jpg',
            'thumbnail_url' => '%s/img/headers/image003-thumbnail.jpg',
        ),
    ) );

    // CUSTOM BACKGROUND
    add_custom_background();
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
add_action('wp_print_scripts', 'SLUG_addJS');
function SLUG_addJS() {
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); 
    wp_enqueue_script('jquery');
    wp_enqueue_script('congelado', get_stylesheet_directory_uri().'/js/congelado.js', 'jquery');
    
}

// CUSTOM MENU
add_action( 'init', 'SLUG_custom_menus' );
function SLUG_custom_menus() {
    register_nav_menus( array(
        'main' => __('Menu name', 'SLUG'),
    ) );
}

// SIDEBARS
if(function_exists('register_sidebar')) {
    // sidebar 
    register_sidebar( array(
        'name' =>  'Sidebar',
        'description' => __('Sidebar', 'SLUG'),
        'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
        'after_widget' => '</div></div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ) );
}

// EXCERPT MORE

add_filter('utils_excerpt_more_link', 'SLUG_utils_excerpt_more',10,2);
function SLUG_utils_excerpt_more($more_link, $post){
    return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'SLUG') . '</a>';
}


add_filter( 'excerpt_more', 'SLUG_auto_excerpt_more' );
function SLUG_auto_excerpt_more( $more ) {
    global $post;
    return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'SLUG') . '</a>';
}

// SETUP
if (!function_exists('SLUG_custom_header')) :

    function SLUG_custom_header() {
        ?><style type="text/css">
            #branding {
                background: url(<?php header_image(); ?>);
            }
                
            #branding, #branding a, #branding a:hover {
                color: #<?php header_textcolor(); ?> !important;
            }
            #branding a:hover {
                text-decoration: none; 
            }
            #description { 
                filter: alpha(opacity=60);
                opacity: 0.6;
            }
        
        </style><?php

    }

endif;

if (!function_exists('SLUG_admin_custom_header')) :

    function SLUG_admin_custom_header() {
        ?><style type="text/css">
        
           #headimg {
                padding:55px 10px;
                width: 960px !important;
                height: 66px !important;
                min-height: 66px !important;
            }
        
            #headimg h1 {
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
if (!function_exists('SLUG_comment')):

    function SLUG_comment($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;
        ?>
        <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">
            <p class="comment-meta alignright bottom">
                <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'SLUG'), '| ', ''); ?>          
            </p>    
            <p class="comment-meta bottom">
                <?php printf( __('By %s on %s at %s.', 'SLUG'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
                <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'SLUG'); ?></em><?php endif; ?>
            </p>
            <?php echo get_avatar($comment, 66); ?>
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
        <style type="text/css">
	        h1 a { height: 45px; width: 354px; margin-left: -12px; background-image: url('. html::getImageUrl('logo.png') .') !important; }
        </style>';
}
add_action('login_head', 'custom_login_logo');

function new_headertitle($url){
    return get_bloginfo('sitetitle');
}
add_filter('login_headertitle','new_headertitle');

function custom_login_headerurl($url) {
    return get_bloginfo('url');

}
add_filter ('login_headerurl', 'custom_login_headerurl');
