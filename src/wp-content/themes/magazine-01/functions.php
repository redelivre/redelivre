<?php 
/*
 * IMPORTANTE
 * substituir todos os magazine01 pelo slug do projeto
 */

include dirname(__FILE__).'/includes/congelado-functions.php';
include dirname(__FILE__).'/includes/html.class.php';
include dirname(__FILE__).'/includes/utils.class.php';
include dirname(__FILE__).'/includes/hacklab_post2home/hacklab_post2home.php';
//include dirname(__FILE__).'/includes/form.class.php';


add_action( 'after_setup_theme', 'magazine01_setup' );
function magazine01_setup() {

    load_theme_textdomain('magazine01', TEMPLATEPATH . '/languages' );

    // POST THUMBNAILS
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size( 220, 154, true );

    //REGISTRAR AQUI TODOS OS TAMANHOS UTILIZADOS NO LAYOUT
    add_image_size('home-feature',400,300);
    //add_image_size('nome2',X,Y);

    // AUTOMATIC FEED LINKS
    add_theme_support('automatic-feed-links');
    add_theme_support('post-formats', array('audio', 'video', 'gallery', 'image') );

    $args = array(
        'default-image'          => get_template_directory_uri() . '/img/bg.png',
        'default-color'          => '#FFFFFF',
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
    'wp-head-callback' => 'magazine01_custom_header',
    'admin-head-callback' => 'magazine01_admin_custom_header',
    'default-text-color' => apply_filters('default-text-color', '0033CC')
    );
    add_theme_support( 'custom-header', $args );
}


// admin_bar removal
//wp_deregister_script('admin-bar');
//wp_deregister_style('admin-bar');
//remove_action('wp_footer','wp_admin_bar_render',1000);
//function remove_admin_bar(){
//   return false;
//}
//add_filter( 'show_admin_bar' , 'remove_admin_bar');

// JS
add_action('wp_print_scripts', 'magazine01_addJS');
function magazine01_addJS() {
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); 
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-widget');
    
    wp_enqueue_script('jquery-autocomplete', get_template_directory_uri().'/js/jquery-ui-1.8.20-autocomplete.js', array('jquery-ui-widget'));
    wp_enqueue_script('congelado', get_template_directory_uri().'/js/congelado.js', 'jquery-autocomplete');
    wp_enqueue_script('magazine', get_template_directory_uri().'/js/magazine.js');
    
    wp_localize_script('congelado', 'vars', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
    
    //wp_enqueue_style('jquery-autocomplete',get_stylesheet_directory_uri().'/css/jquery-ui-1.8.20.custom.css');
}

// EDITOR STYLE
add_editor_style('editor-style.css');

// LARGURA DA COLUNA DE POSTS PARA OS EMBEDS DE VÍDEOS
global $content_width;
if ( !isset( $content_width ) )
$content_width = 600;

// CUSTOM MENU
add_action( 'init', 'magazine01_custom_menus' );
function magazine01_custom_menus() {
    register_nav_menus( array(
        'main' => 'Principal',
        'quick-links' => 'Acesso Rápido',
    ) );
}

// SIDEBARS
if(function_exists('register_sidebar')) {
    // sidebar 
    register_sidebar( array(
        'name' =>  'Sidebar',
        'description' => __('Sidebar', 'magazine01'),
        'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
        'after_widget' => '</div></div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    	'id'            => 'sidebar-1'
    ) );
        register_sidebar( array(
        'name' =>  'Home',
        'description' => __('Home', 'magazine01'),
        'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
        'after_widget' => '</div></div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
        'id'            => 'sidebar-home'
    ) );
}

// EXCERPT MORE

add_filter('utils_excerpt_more_link', 'magazine01_utils_excerpt_more',10,2);
function magazine01_utils_excerpt_more($more_link, $post){
    return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'magazine01') . '</a>';
}


add_filter( 'excerpt_more', 'magazine01_auto_excerpt_more' );
function magazine01_auto_excerpt_more( $more ) {
    global $post;
    return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'magazine01') . '</a>';
}

// SETUP
if (!function_exists('magazine01_custom_header')) :

    function magazine01_custom_header() {
        $custom_header = get_custom_header();
        
        ?>
        <style type="text/css">
            #branding { background: url(<?php header_image(); ?>) no-repeat; height: <?php echo $custom_header->height; ?>px; }
			<?php if ( 'blank' == get_header_textcolor() ) : ?>
				#branding a { height: <?php echo $custom_header->height; ?>px; }
				#branding a:hover { background: none !important; }       
			<?php else: ?>       
				#branding, #branding a, #branding a:hover { color: #<?php header_textcolor(); ?>; }
				#branding a:hover { text-decoration: none; }
				#description { filter: alpha(opacity=60); opacity: 0.6; }
			<?php endif; ?>        
        </style>
        <?php
    }

endif;

if (!function_exists('magazine01_admin_custom_header')) :

    function magazine01_admin_custom_header() {
        ?><style type="text/css">
        
           #headimg {
                padding:55px 0;
                width: 940px !important;
                height: 88px !important;
                min-height: 88px !important;
            }
        
            #headimg h1 {
                font-size:42px;
                line-height:66px;
                margin-bottom: 0px;          
            }
        
            #headimg h1 a {
                text-decoration: none !important;
            }
        
            #headimg #desc { 
                font-size: 16px; 
                margin: 0 10px;
                filter: alpha(opacity=60);
                opacity: 0.6;
            }

        </style><?php
    }

endif;

// COMMENTS
if (!function_exists('magazine01_comment')):

    function magazine01_comment($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;
        ?>
        <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">
            <p class="comment-meta alignright bottom">
                <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'magazine01'), '| ', ''); ?>          
            </p>    
            <p class="comment-meta bottom">
                <?php printf( __('By %s on %s at %s.', 'magazine01'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
                <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'magazine01'); ?></em><?php endif; ?>
            </p>
            <?php echo get_avatar($comment, 66); ?>
            <div class="content">
                <?php comment_text(); ?>
            </div>
        </li>
        <?php
    }

endif;

add_action('init', 'magazine01_init');
// Hook de ativação do tema
function magazine01_init() {
    global $pagenow;
    
    if ( is_admin() && isset($_GET['activated'] ) && $pagenow == 'themes.php' ) {
        global $wpdb;
        
        $createdBefore = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_blog_page_created'");
        
        if (!$createdBefore) {
            
            // Cria a página Notícias
            $page = array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => 'Notícias',
                'post_content' => 'Esta página listará os seus posts em forma de blog. Você pode renomeá-la se quiser. Qualquer conteúdo aqui será ignorado.'
            );
            
            $id = wp_insert_post($page);
            
            add_post_meta($id, '_blog_page_created', 1);
            
            // Seleciona o template de blog
            add_post_meta($id, '_wp_page_template', 'blog.php');
            
            // Adiciona um item ao menu
            $menu = wp_get_nav_menu_object('main');
            wp_update_nav_menu_item($menu->term_taxonomy_id, 0, array(
                'menu-item-title' => 'Notícias',
                'menu-item-url' => home_url('/noticias'), 
                'menu-item-status' => 'publish')
            );
        }
    }
}


function the_first_audio() {

    global $post;
    
    if (is_object($post) && isset($post->post_content)) {
    
        $audio = get_first_audio($post->post_content);
        
        if ($audio)
            print_audio_player($audio, false);
    
    }

}

function get_first_audio($content) {

    $reg = '|(http://[^ "\']+.mp3)|';
    
    $audio = false;
    
    $matches = preg_match_all($reg, $content, $m);
    
    if (is_array($m) && isset($m[0]) && is_array($m[0]) && isset($m[0][0]) ) {
        $audio = $m[0][0];
    }
    
    
    return $audio;

}

function the_first_video() {

    global $post;
    
    if (is_object($post) && isset($post->post_content)) {
    
        require_once( ABSPATH . WPINC . '/class-oembed.php' );

        $oembed = _wp_oembed_get_object();
        
        $video = get_first_video($post->post_content);
        
        if ($video)
            echo $oembed->get_html($video, 'maxwidth=220&width=220');
    
    }

}


function get_first_video($content) {
    
    require_once( ABSPATH . WPINC . '/class-oembed.php' );

    $oembed = _wp_oembed_get_object();
    
    // gets the first video in the post
    preg_match_all('|http://[^"\'\s]+|', $content, $m);
    
    $video = false;
    
    foreach ($m[0] as $match) {
        
        $found = false;
            
        foreach ($oembed->providers as $regexp => $data) {
        
            list( $providerurl, $is_regex ) = $data;
            
            if (!$is_regex)    
                $regexp = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $regexp ), '#' ) ) . '#i';
        
            if ( preg_match($regexp, $match) ) {
                $video = $match;
                $found = true;
                break;
            }   
        
        }
        
        if ($found)
            break;

    }

    return $video;

}
