<?php 
/*
 * IMPORTANTE
 * substituir todos os campanha pelo campanha do projeto
 */

include dirname(__FILE__).'/includes/congelado-functions.php';
include dirname(__FILE__).'/includes/html.class.php';
include dirname(__FILE__).'/includes/utils.class.php'; 

define('CAMPAIGN_LIST_URL',   'admin.php?page=campaigns&action=list');
define('CAMPAIGN_DELETE_URL', 'admin.php?page=campaigns&action=delete');
define('CAMPAIGN_EDIT_URL',   'admin.php?page=campaigns&action=edit');
define('CAMPAIGN_NEW_URL',    'admin.php?page=campaigns_new');

if (is_admin()) {
    require(MUCAMPANHAPATH . '/custom_admin.php');
}

add_action('after_setup_theme', 'campanha_setup');

function campanha_setup() {

    load_theme_textdomain('campanha', MUCAMPANHAPATH . '/languages' );

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
            require MUCAMPANHAPATH . '/register.php';
            die;
        default:
            break;
    }
}

function cadastro_url() {
    return home_url() . '/cadastro';
}

add_action('wp_ajax_campanha_get_cities_options', function() {
    $state_id = filter_input(INPUT_GET, 'uf', FILTER_SANITIZE_NUMBER_INT);
    City::printCitiesSelectBox($state_id);
});

add_filter('query_vars', 'custom_query_vars');
function custom_query_vars($public_query_vars) {
    $public_query_vars[] = "tpl";

    return $public_query_vars;
}

add_action('admin_print_styles', 'campanha_addAdminCSS');
function campanha_addAdminCSS() {
    
    wp_enqueue_style('campanhaAdmin', WPMU_PLUGIN_URL.'/campanha/css/admin.css');
}


// JS
add_action('wp_print_scripts', 'campanha_addJS');
function campanha_addJS() {
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); 
    wp_enqueue_script('jquery');
    wp_enqueue_script('congelado', WPMU_PLUGIN_URL.'/campanha/js/congelado.js', 'jquery');
    wp_enqueue_script('campanha', WPMU_PLUGIN_URL.'/campanha/js/campanha.js', 'jquery');
}

// CUSTOM MENU
add_action( 'init', 'campanha_custom_menus' );
function campanha_custom_menus() {
    register_nav_menus( array(
        'main' => __('Principal', 'campanha'),
        'sobre' => __('Sobre o Projetos', 'campanha'),
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



/**
 * After login, redirect the user to the page to administer campaigns.
 * 
 * @param string $redirect_to
 * @param string $request the url the user is coming from
 * @param Wp_Error|Wp_User $user
 */
function campanha_login_redirect($redirect_to, $request, $user) {
    if (!is_wp_error($user) && is_array($user->roles)
        && in_array("subscriber", $user->roles))
    {
        return campanha_redirect_to_campaign_home($user);
    }

    return $redirect_to;
}
add_filter('login_redirect', 'campanha_login_redirect', 10, 3);

/**
 * Subscribers never see the admin home page. Instead they
 * are redirected to the campaigns admin page.
 */
function campanha_change_admin_home() {
    $user = wp_get_current_user();
    
    if (is_array($user->roles) && in_array("subscriber", $user->roles)
        && preg_match('#wp-admin/?(index.php)?$#', $_SERVER['REQUEST_URI']))
    {
        wp_redirect(campanha_redirect_to_campaign_home());
    }
}
add_action('admin_init', 'campanha_change_admin_home');

/**
 * Return the link to the campaign admin home page for the user
 * depending whether he has campaigns or not.
 *
 * @param WP_User $user
 * @return string url to list campaigns page or create new campaign page
 */
function campanha_redirect_to_campaign_home($user = null) {
    if (!$user) {
        $user = wp_get_current_user();
    } 
    
    $campaigns = Campaign::getAll($user->ID);

    if ($campaigns) {
        return admin_url(CAMPAIGN_LIST_URL);
    } else {
        return admin_url(CAMPAIGN_NEW_URL);
    }
}
