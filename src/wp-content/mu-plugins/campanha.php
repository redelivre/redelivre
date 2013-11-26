<?php 

/*
 * IMPORTANTE
 * substituir todos os campanha pelo campanha do projeto
 */

define('MUCAMPANHAPATH', dirname(__FILE__).'/campanha');

define('CAMPAIGN_LIST_URL',   'admin.php?page=campaigns&action=list');
define('CAMPAIGN_DELETE_URL', 'admin.php?page=campaigns&action=delete');
define('CAMPAIGN_EDIT_URL',   'admin.php?page=campaigns&action=edit');
define('CAMPAIGN_NEW_URL',    'admin.php?page=campaigns_new');

if (is_admin() && get_current_blog_id() == 1) {
    require MUCAMPANHAPATH . '/custom_admin.php';
}
elseif(is_admin())
{
	add_action('admin_menu', function() {
	$base_page = 'platform-settings';
	
	add_object_page( __(Campaign::getStrings('MenuPlataforma'),'redelivre'), __(Campaign::getStrings('MenuPlataforma'),'redelivre'), 'manage_options', $base_page, array());
	
	add_submenu_page($base_page, __('Settings','redelivre'), __('Settings','redelivre'), 'manage_options', 'platform-settings', function(){
			require MUCAMPANHAPATH.'/admin-settings-tpl.php';
		});
	});
}

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

add_action('after_setup_theme', 'campanha_setup');


// REDIRECIONAMENTOS
function custom_url_rewrites($rules) {
    $new_rules = array(
        "cadastro/?$" => "index.php?tpl=cadastro",
    );
    
    return $new_rules + $rules;
}

add_filter('rewrite_rules_array', 'custom_url_rewrites', 10, 1);

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

add_action('template_redirect', 'template_redirect_intercept');

function cadastro_url() {
    return home_url() . '/cadastro';
}

add_action('wp_ajax_campanha_get_cities_options', function() {
    $state_id = filter_input(INPUT_GET, 'uf', FILTER_SANITIZE_NUMBER_INT);
    City::printCitiesSelectBox($state_id);
});

function custom_query_vars($public_query_vars) {
    $public_query_vars[] = "tpl";

    return $public_query_vars;
}

add_filter('query_vars', 'custom_query_vars');

function campanha_addAdminCSS() {
    wp_enqueue_style('campanhaAdmin', WPMU_PLUGIN_URL.'/campanha/css/admin.css');
}

add_action('admin_print_styles', 'campanha_addAdminCSS');


// JS
function campanha_addJS() {
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); 
    wp_enqueue_script('jquery');
    wp_enqueue_script('congelado', WPMU_PLUGIN_URL.'/campanha/js/congelado.js', 'jquery');
    wp_enqueue_script('campanha', WPMU_PLUGIN_URL.'/campanha/js/campanha.js', 'jquery');
}

add_action('wp_print_scripts', 'campanha_addJS');

// CUSTOM MENU
function campanha_custom_menus() {
    register_nav_menus( array(
        'main'  => __('Principal', 'campanha'),
        'sobre' => Campaign::getStrings('Sobre'),
        'info'  => __('Informações Legais', 'campanha'),
        
    ));
}

add_action( 'init', 'campanha_custom_menus');

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

function getPlataformSettings($id = '')
{
	$sets = array();
	
	$sets['label']['email'] = 'E-Mail de Origem';
	$sets['value']['email'] = 'noreply@redelivre.org';
	$sets['label']['emailReplyTo'] = 'E-Mail de Reposta';
	$sets['value']['emailReplyTo'] = 'contato@redelivre.org';
	$sets['label']['emailPassword'] = 'E-Mail Password';
	$sets['value']['emailPassword'] = 'redelivre';
	$sets['label']['emailTipo'] = 'Tipo do E-mail (local ou gmail';
	$sets['value']['emailTipo'] = 'local';
	$sets['label']['MostrarPlanos'] = 'Deve mostrar opções de planos';
	$sets['value']['MostrarPlanos'] = 'S';
	$sets['label']['defaultPlan'] = 'Plano Padrão';
	$sets['value']['defaultPlan'] = '5';
	
	// Merge default settings com defined settings
	$sets['value'] = array_merge($sets['value'], get_option('plataform_defined_settings', array()));
	
	if($id != '')
	{
		return array_key_exists($id, $sets['value']) ? $sets['value'][$id] : '';
	}
	
	return $sets;
}

function editStringsStylesheets()
{
    wp_enqueue_style('plataform-edit-strings', plugins_url('/css/plataform-edit-strings.css', MUCAMPANHAPATH));   
}

add_action('admin_enqueue_scripts', 'editStringsStylesheets');

function savePlataformSettings()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['plataform_settings_strings'])) {
        $_POST['plataform_settings_strings'] = array_merge(getPlataformSettings(), $_POST['plataform_settings_strings']);

        if (update_option('plataform_defined_settings', $_POST['plataform_settings_strings']))
        {
            echo 'Dados atualizados com sucesso!';
        }
    }
}