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
        'main'  => __('Principal', 'redelivre'),
        'sobre' => Campaign::getStrings('Sobre'),
        'info'  => __('Informações Legais', 'redelivre'),
        
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
    if (get_current_blog_id() == 1 && !is_wp_error($user) && is_array($user->roles)
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
    
    if (get_current_blog_id() == 1 && is_array($user->roles) && in_array("subscriber", $user->roles)
        && preg_match('#wp-admin/?(index.php)?$#', $_SERVER['REQUEST_URI']))
    {
        wp_redirect(campanha_redirect_to_campaign_home());
    }
}

add_action('admin_init', 'campanha_change_admin_home');

function campanha_add_manage_menu()
{
	if (user_can_create_campanha() ) // && get_current_blog_id() == 1)
	{
		require MUCAMPANHAPATH . '/custom_admin.php';
	}
	
	if(get_current_blog_id() != 1)
	{
		add_action('admin_menu', function() {
			$base_page = 'platform-settings';
	
			add_menu_page( Campaign::getStrings('MenuPlataforma'), Campaign::getStrings('MenuPlataforma'), 'manage_options', $base_page, array());
	
			add_submenu_page($base_page, __('Settings','redelivre'), __('Settings','redelivre'), 'manage_options', 'platform-settings', function(){
				require MUCAMPANHAPATH.'/admin-settings-tpl.php';
			});
		});
	}
}
add_action('init', 'campanha_add_manage_menu');

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

/**
 * Return list of plataform settings
 * @param string $id
 * @return string
 */
function getPlataformSettings($id = '')
{
	$sets = array();
	$strings = Campaign::getStrings();
	
	$sets['label'] = array();
	$sets['value'] = array();
	$sets['perm'] = array();
	$sets['options'] = array();
	$sets['type'] = array();
	
	$sets['label']['email'] = __('E-Mail de Origem', 'redelivre');
	$sets['value']['email'] = 'noreply@redelivre.org';
	//$sets['perm']['email'] = 'S';
	$sets['label']['emailReplyTo'] = __('E-Mail de Reposta', 'redelivre');
	$sets['value']['emailReplyTo'] = 'contato@redelivre.org';
	//$sets['perm']['emailReplyTo'] = 'contato@redelivre.org';
	$sets['label']['emailContato'] = __('E-Mail do formulário de contato', 'redelivre');
	$sets['value']['emailContato'] = get_option('admin_email');
	//$sets['perm']['emailContato'] = '';
	$sets['label']['emailPassword'] = __('E-Mail Password', 'redelivre');
	$sets['value']['emailPassword'] = 'redelivre';
	//$sets['perm']['emailPassword'] = 'redelivre';
	$sets['type']['emailPassword'] = 'password';
	$sets['label']['emailTipo'] = __('Tipo do servidor de E-mail', 'redelivre');
	$sets['value']['emailTipo'] = 'local';
	$sets['type']['emailTipo'] = 'dropdown';
	$sets['options']['emailTipo'] = array(
		'local' => __("envio usando o servidor local (php)", 'redelivre'),
		'gmail' => __("envio usando uma conta do gmail", 'redelivre')
	);
	//$sets['perm']['emailTipo'] = 'local';
	$sets['label']['MostrarPlanos'] = __('Deve mostrar opções de planos', 'redelivre');
	$sets['value']['MostrarPlanos'] = 'N';
	$sets['type']['MostrarPlanos'] = 'yesno';
	$sets['perm']['MostrarPlanos'] = 'S';
	$planos = array();
	foreach (Plan::getAll() as $plan)
	{
		$planos[$plan->id] = $plan->name;
	}
	$sets['label']['defaultPlan'] = __('Plano Padrão', 'redelivre');
	$sets['value']['defaultPlan'] = '1';
	$sets['type']['defaultPlan'] = 'dropdown';
	$sets['options']['defaultPlan'] = $planos;
	$sets['perm']['defaultPlan'] = 'S';
	$sets['label']['minPerm'] = __('Permissão Mínima para criação de ', 'redelivre').$strings['value']['singular'];
	$sets['value']['minPerm'] = 'subscriber';
	$sets['perm']['minPerm'] = 'S';
	$sets['options']['minPerm'] = campanha_get_editable_roles();
	$sets['type']['minPerm'] = 'dropdown';
	
	// Merge default settings com defined settings
	$sets['value'] = array_merge($sets['value'], get_option('plataform_defined_settings', array()));
	
	if($id != '')
	{
		return array_key_exists($id, $sets['value']) ? $sets['value'][$id] : '';
	}
	
	return $sets;
}

function campanha_get_editable_roles()
{
	// $this->security_check();
	global $wp_roles;

	if(! isset($wp_roles))
		$wp_roles = new WP_Roles();

	$all_roles = $wp_roles->get_names();
	$editable_roles = apply_filters('editable_roles', $all_roles);

	return $all_roles;
}

function editStringsStylesheets()
{
    wp_enqueue_style('plataform-edit-strings', plugins_url('/css/plataform-edit-strings.css', MUCAMPANHAPATH));   
}

add_action('admin_enqueue_scripts', 'editStringsStylesheets');

function savePlataformSettings($sets = false)
{
	$set_values = array();
	if($sets && is_array($sets) && array_key_exists('value', $sets))
	{
		$set_values = $sets['value'];
	}
	elseif (is_array($sets))
	{
		$set_values = $sets;
		$sets = getPlataformSettings();
	}
	else 
	{
		$sets = getPlataformSettings();
		$set_values = $sets['value'];
	}
	
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['plataform_settings_strings']))
    {
        $_POST['plataform_settings_strings'] = array_merge($set_values, $_POST['plataform_settings_strings']);
        foreach ($_POST['plataform_settings_strings'] as $set => $value)
        {
        	$value = sanitize_text_field($value);
        	if(empty($value))
        	{
	        	if(array_key_exists($set, $sets['type']) && $sets['type'][$set] == 'password') // do not save not changed password
	        	{
	        		$_POST['plataform_settings_strings'][$set] = $sets['value'][$set];
	        		continue;
	        	}
        	}
        	$_POST['plataform_settings_strings'][$set] = $value;
        }
        
        if (update_option('plataform_defined_settings', $_POST['plataform_settings_strings']))
        {
            echo 'Dados atualizados com sucesso!';
        }
    }
}

function campanha_add_user_to_root()
{
	if(is_super_admin())
	{
		// Fix Users not in blogs root 
		global $wpdb;
		$query = "SELECT ID FROM {$wpdb->users}";
		$users = $wpdb->get_results( $query, ARRAY_A );
		foreach($users as $user) {
			add_user_to_blog('1', $user['ID'], 'subscriber');
		}
	}
}
//add_action( 'init', 'campanha_add_user_to_root', 1, 2); // TODO exec when need and not always

function campanha_new_user_to_root($user_id)
{
	add_user_to_blog('1', $user_id, 'subscriber');
}
add_action( 'wpmu_new_user', 'campanha_new_user_to_root', 10, 2);

function user_can_create_campanha()
{
	if(is_super_admin()) return true;

	//TODO need to get it from current roles config not from a fixed array
	$roles_array = array(
		"subscriber" => 0,
		"contributor" => 1,
		"author" => 2,
		"editor" => 3,
		"administrator" => 4
	);

	$get_users_obj = get_users(
			array(
				'blog_id' => 1,
				'search' => get_current_user_id()
			)
			);
	if(
			is_array($get_users_obj) &&
			count($get_users_obj) > 0 &&
			is_array($get_users_obj[0]->roles) &&
			count($get_users_obj[0]->roles) > 0
			)
	{
		$user_role = $get_users_obj[0]->roles[0];
		$minPerm = getPlataformSettings('minPerm');

		if(!array_key_exists($minPerm, $roles_array))
		{
			throw new Exception(__('Minimum permition not found on default array!', 'redelivre'));
		}

		if($roles_array[$minPerm] == 0)
		{
			return true;
		}

		if(
				array_key_exists($user_role, $roles_array) &&
				array_key_exists($minPerm, $roles_array) &&
				$roles_array[$user_role] >= $roles_array[$minPerm]
				)
		{
			return true;
		}
	}
	return false;
}
