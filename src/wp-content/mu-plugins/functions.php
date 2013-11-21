<?php

// load campaign base files
foreach (glob(WPMU_PLUGIN_DIR . '/campaign_base/*.php') as $file) {
    require_once($file);
}

// inclui os widgets
foreach (glob(WPMU_PLUGIN_DIR . '/includes/widgets/*.php') as $file) {
    require_once($file);
}


//db updates -- eventualmente podemos não incluir mais
include(WPMU_PLUGIN_DIR . '/includes/db-updates.php');

$campaign = null;

// load code used only for campaign sites (exclude main site)
if (!is_main_site()) {
    // must wait for wordpress to finish loading before loading campaign code
    add_action('init', function() {
        global $blog_id, $campaign;

        require_once(__DIR__ . '/includes/payment.php');
        require_once(__DIR__ . '/includes/admin-contact.php');

        $campaign = Campaign::getByBlogId($blog_id);
        
        require_once(__DIR__ . '/includes/graphic_material/GraphicMaterialManager.php');

        GraphicMaterialManager::setUp();

        if (is_admin()) {
            require_once(__DIR__ . '/includes/load_menu_options.php');
        }

        add_action('template_redirect',        'campanha_check_payment_status');
        add_action('template_redirect',        'campanha_check_plan_and_theme');
        add_action('template_redirect',        'campaign_base_template_redirect_intercept');
        add_action('admin_notices',            'campanha_admin_messages');
        add_action('admin_init',               'campanha_remove_menu_pages');
        add_action('load-ms-delete-site.php',  'campanha_remove_exclude_site_page_content');
        add_action('wp_dashboard_setup',       'campannha_dashboard_widget');
        add_action('load-options-general.php', 'campanha_custom_options_strings');
        add_action('wp_print_scripts',         'campanha_uservoice_js');

        add_filter('query_vars',                              'campaign_base_custom_query_vars');
        add_filter('rewrite_rules_array',                     'campaign_base_custom_url_rewrites', 10, 1);
        add_filter('login_message',                           'campanha_login_messages');
        add_filter('site_option_upload_space_check_disabled', 'campanha_unlimited_upload');

        // flush rewrite rules on first run to make pages like /materialgrafico and /mobilizacao work
        if (is_admin() && !get_option('campanha_flush_rules')) {
            update_option('campanha_flush_rules', 1);

            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    });
}

/**
 * Remove menu page to exlude site.
 */
function campanha_remove_menu_pages() {
    remove_submenu_page('tools.php', 'ms-delete-site.php');
}

/**
 * Make sure the user can't see the content of the page
 * to exclude the site.
 */
function campanha_remove_exclude_site_page_content() {
    die;
}

/**
 * Check the payment status and mark the blog
 * as private in case payment is pending.
 */
function campanha_check_payment_status() {
    global $campaign;

    $user_id = get_current_user_id();

    if (!$campaign->isPaid() && $campaign->campaignOwner->ID !== $user_id
            && !is_super_admin()) {
        wp_redirect(wp_login_url());
    }
}

/**
 * Check the campaign plan and current theme and mark
 * the blog as private in case it is using a theme that
 * is only available to other plans.
 */
function campanha_check_plan_and_theme() {
    global $campaign;

    $user_id = get_current_user_id();
    $theme = wp_get_theme();

    // if plan is "blog" and theme is not 'blog-01' or one of its child
    // mark the campaign as private
    if ($campaign->plan_id == 6 && strpos($theme->get_stylesheet(), 'blog-01') === false
        && $campaign->campaignOwner->ID !== $user_id && !is_super_admin()) {
        wp_redirect(wp_login_url());
    }
}

function campaign_base_custom_query_vars($public_query_vars) {
    $public_query_vars[] = "tpl";

    return $public_query_vars;
}

// REDIRECIONAMENTOS
function campaign_base_custom_url_rewrites($rules) {
    $new_rules = array(
        "materialgrafico/?$" => "index.php?tpl=materialgrafico",
        "contato/?$" => "index.php?tpl=contato",
    );

    return $new_rules + $rules;
}

function campaign_base_template_redirect_intercept() {
    global $wp_query, $campaign;

    switch ($wp_query->get('tpl')) {
        case 'materialgrafico':
            $wp_query->is_home = false;
            
            if (file_exists(STYLESHEETPATH . '/tpl-graphic_material.php')) { // tema filho
                require(STYLESHEETPATH . '/tpl-graphic_material.php');
            } elseif (file_exists(TEMPLATEPATH . '/tpl-graphic_material.php')) { // tema pai
                require(TEMPLATEPATH . '/tpl-graphic_material.php');
            } else {
                require(WPMU_PLUGIN_DIR . '/includes/tpl-graphic_material_list_links.php');
            }
            die;
        case 'contato':
            $wp_query->is_home = false;
            
            add_action('wp_print_scripts', function() {
                wp_enqueue_script('jquery_validate', WPMU_PLUGIN_URL . '/js/jquery.validate.min.js', array('jquery'));
                wp_enqueue_script('contato', WPMU_PLUGIN_URL . '/js/contato.js', array('jquery_validate'));
                wp_localize_script('contato', 'vars', array('ajaxurl' => admin_url('admin-ajax.php')));
            });

            // template específico dentro do tema
            if (file_exists(STYLESHEETPATH . '/tpl-contato.php')) { // tema filho
                require(STYLESHEETPATH . '/tpl-contato.php');
            } elseif (file_exists(TEMPLATEPATH . '/tpl-contato.php')) { // tema pai
                require(TEMPLATEPATH . '/tpl-contato.php');
            }
            //else { template generico ?
            //    require(WPMU_PLUGIN_DIR . '/includes/tpl-contato.php');
            //}

            die;
        default:
            break;
    }
}

/**
 * Display messages in the login page when
 * necessary.
 * 
 * @param string $message
 * @return string
 */
function campanha_login_messages($message) {
    global $campaign;
    
    $addMessage = false;
    
    // display message when campaign is not open to the public due to pending payment
    if (!$campaign->isPaid()) {
        // $message .= '<p class="message">Este projeto está visível somente para o criador pois o pagamento está pendente. <a href='$link'>Pague agora!</a></p>';
        $addMessage = true;
    }
    
    // display message when campaign is not open to the public
    // due to the use of a theme that is not allowed for the current plan
    // for now only the plan "Blog" has a limited set of themes
    $theme = wp_get_theme();
    if ($campaign->plan_id == 6 && strpos($theme->get_stylesheet(), 'blog-01') === false) {
        $addMessage = true;
    }

    if ($addMessage) {
        $message .= '<p class="message">'.Campaign::getStrings('NaoDisponivel').'</p>';
    }

    return $message;
}

/**
 * Display a messages in the admin panel
 */
function campanha_admin_messages() {
    global $campaign;

    if (!$campaign->isPaid()) {
        $link = admin_url('admin.php?page=payments');
        // echo "<div class='error'><p>Esta campanha está visível somente para o criador pois o pagamento está pendente. <a href='$link'>Pague agora!</a></p></div>";
        // temporarily remove link to payment page while it is not finished
        echo "<div class='error'><p>".Campaign::getStrings('ProjetoVisivel')."</p></div>";
    }
    
    // display message if using plan "blog" and a theme
    // other than 'blog-01'
    $theme = wp_get_theme();
    if ($campaign->plan_id == 6 && strpos($theme->get_stylesheet(), 'blog-01') === false) {
        echo "<div class='error'><p>".Campaign::getStrings('AtualizePlano')."</p></div>";
    }
    
}

/**
 * Hack to remove upload limit for premium campaings.
 * 
 * By default in Wordpress or you disable upload limit 
 * or you set a limit for all sites. This functions is a
 * workaround to allow some sites with upload limit and 
 * others without.
 * 
 * @param bool $value the value for the site option 'upload_space_check_disabled'
 * @return bool
 */
function campanha_unlimited_upload($value) {
    if (get_option('blog_upload_space') == -1) {
        $value = true;
    }

    return $value;
}

function print_msgs($msg, $extra_class = '', $id = '') {
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

/**
 * Add uservoice javascript to campaign sites
 */
function campanha_uservoice_js() {
    global $campaign;
    
    $capabilities = Capability::getByPlanId($campaign->plan_id);

    if (is_user_logged_in() && !is_super_admin() && $capabilities->support->value) {
        wp_enqueue_script('uservoice', site_url() . '/wp-content/mu-plugins/js/uservoice.js', 'jquery', false, true);
    }
}

add_action('wp_print_scripts', 'campanha_add_common_js');
/**
 * Add JS files shared by all themes.
 */
function campanha_add_common_js() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('campaign_common', site_url() . '/wp-content/mu-plugins/js/campaign_common.js', 'jquery');
    Campaign::getStrings();
}

add_action('user_register', 'campanha_disable_welcome_panel');

/**
 * Whenever a user is created set a metadata so
 * that they don't see the default WP welcome panel
 * in the dashboard.
 */
function campanha_disable_welcome_panel($userId) {
    update_user_meta($userId, 'show_welcome_panel', 0);
}

function campannha_dashboard_widget() {
    global $wp_meta_boxes;
    
    add_meta_box('campanha_dashboard_widget', 'Ajuda', function() {
        require_once(WPMU_PLUGIN_DIR . '/includes/dashboard_widget_campanha.php');
    }, 'dashboard', 'side');
}

add_action('campanha_body_header', function() {
    $redes = get_option('campanha_social_networks');
    $redes = is_array($redes) ? $redes : array();
    ?>
    
	<div id="social-bookmarks" class="alignright">
		<?php if(@$redes['facebook']): ?><a id="facebook" href="<?php echo $redes['facebook-page'] ?>" title="Facebook"></a><?php endif; ?>
		<?php if(@$redes['twitter']): ?><a id="twitter" href="<?php echo $redes['twitter'] ?>" title="Twitter"></a><?php endif; ?>
		<?php if(@$redes['google']): ?><a id="google-plus" href="<?php echo $redes['google'] ?>" title="Google+"></a><?php endif; ?>
		<?php if(@$redes['youtube']): ?><a id="youtube" href="<?php echo $redes['youtube'] ?>" title="YouTube"></a><?php endif; ?>
		<a id="rss" href="<?php echo bloginfo('url') ?>/rss" title="RSS"></a>
	</div>
    
    <?php
});

/**
 * Add javascript file to customize strings
 * in the options-general.php page.
 */
function campanha_custom_options_strings() {
    wp_enqueue_script('custom_general_options', WPMU_PLUGIN_URL . '/js/custom_general_options.js');
}



/*
 * Função chamada pelo hook phpmailer_init.
 * Usada para configurar o Wordpress para disparar
 * e-mails usando o SMTP do Gmail.
 *
 * @param PHPMailer $phpmailer object phpmailer usado pelo wordpress para disparar email
 */
function campanha_use_smtp($phpmailer)
{
	$email = getPlataformSettings('email');
	$emailrt = getPlataformSettings('emailReplyTo');
	
	$phpmailer->From	   = $email;
	$phpmailer->AddReplyTo($emailrt);
	
	
	if(getPlataformSettings('emailTipo') == 'gmail')
	{
	    $email_p = getPlataformSettings('emailPassword');
	    $email = getPlataformSettings('email');
	
	    $phpmailer->IsSMTP();
	    $phpmailer->SMTPAuth   = true;
	    $phpmailer->Port       = 465;
	    $phpmailer->SMTPSecure = 'ssl';
	    $phpmailer->Host       = 'smtp.gmail.com';
	    $phpmailer->Username   = $email;
	    $phpmailer->Password   = $email_p;
	    //$phpmailer->From       = $email;
	    //$phpmailer->FromName   = 'Rede Livre'; -- Acho que podemos deixar isso inalterado, e manter o que foi colocado quando a wp_mail() foi chamada
	}
}
add_action('phpmailer_init', 'campanha_use_smtp');


// custom admin login logo
function custom_login_logo() {
	if(is_main_site()){
	$baseUrl = WP_CONTENT_URL . '/themes/campanha/';
    echo '
		<link rel="stylesheet" type="text/css" media="all" href=" '.$baseUrl.'style.css" />
        <style type="text/css">
			
	        .login h1 a { height: 180px; background-image: url('. $baseUrl .'img/logo.png); background-size: auto; }
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
}
add_action('login_head', 'custom_login_logo');

function new_headertitle($url){
    return get_bloginfo('sitetitle');
}
add_filter('login_headertitle','new_headertitle');

function custom_login_headerurl($url) {
    return get_bloginfo('url');
}
add_filter('login_headerurl', 'custom_login_headerurl');

add_action('custom_header_options', function() {
    ?>
    
    <h3>Precisa de ajuda para criar uma imagem para o cabeçalho?</h3>
    
    <p>Se quiser, utilize nosso assistente para <a href="<?php echo admin_url('themes.php?page=graphic_material_header'); ?>">criar uma imagem de cabeçalho</a> personalizada para você.</p>
    
    
    <?php
});

function custom_menu_order($order) {
    global $submenu;
    
    if (!isset($submenu['themes.php'])) {
        return $order;
    }
    
    $menu = $submenu['themes.php'];
    
    if (!is_array($menu)) {
        return $order;
    }
    
    foreach ($menu as $k => $item) {
        $menuItem = null;
        
        if ($item[2] == 'graphic_material_header') {
            $menuItem = $submenu['themes.php'][$k];
            unset($submenu['themes.php'][$k]);
            break;
        }
    }
    
    if (isset($menuItem) && $menuItem) {
        $submenu['themes.php'][] = $menuItem;
    }
    
    return $order;
}

add_filter('menu_order', 'custom_menu_order', 20);

/*
* Filter used for correct the register URL
*/
function custom_register_redirect($content){
	if(!is_main_site()){
		//die("chamou filtro");
		unset($_REQUEST['action']);
		return "registro-de-usuario/?".http_build_query($_REQUEST);
	} else {
		return $content;
	}
}

add_filter('wp_signup_location', 'custom_register_redirect', 10, 3);

/**
* 
*
*/
function custom_register_rule(){
	
	add_rewrite_rule('registro-de-usuario(.*)', 'index.php?custom_user_register=true$matches[1]', 'top');
	//flush_rewrite_rules();
}

add_action('init', 'custom_register_rule');

/**
*
*
*/
function custom_register_query_var($query_vars){
	$query_vars[] = 'custom_user_register';
	return $query_vars;
}

add_filter('query_vars', 'custom_register_query_var');

/**
* 
*
*/
function custom_register_form(){
	if(get_query_var('custom_user_register')){
		get_header();
		$file_path = get_stylesheet_directory() . '/template-registro-de-usuario.php';
		if(file_exists($file_path)){
			include $file_path;
		} else {
			include ABSPATH . '/wp-content/mu-plugins/includes/custom-register-form.php';
		}		
		get_footer();
		exit();
	}
}

add_action('template_redirect', 'custom_register_form');

/**
*
*
*/
function custom_register_js()
{
	if(get_query_var('custom_user_register'))
	{
		wp_register_script('custom_register', network_site_url() . 'wp-content/mu-plugins/js/custom_user_register.js', array('jquery'));
		wp_enqueue_script('custom_register');
		wp_localize_script('custom_register', 'custom_register_ajax', array('ajaxurl' => home_url() . '/wp-admin/admin-ajax.php?'.http_build_query($_GET)));
		
		if(file_exists(get_stylesheet_directory() . '/custom-register-form.css') || file_exists(get_stylesheet_directory() . '/css/custom-register-form.css'))
		{
			if(file_exists(get_stylesheet_directory() . '/custom-register-form.css'))
			{
				wp_enqueue_style('custom-register-form', get_stylesheet_directory_uri() . '/custom-register-form.css');
			}
			else
			{
				wp_enqueue_style('custom-register-form', get_stylesheet_directory_uri() . '/css/custom-register-form.css');
			}
		}
		else
		{
			wp_enqueue_style('custom-register-form', site_url() . '/wp-content/mu-plugins/css/custom-register-form.css');
		}
		
	}
}

add_action('wp_enqueue_scripts', 'custom_register_js');

/**
*
*
*/
function custom_register_ajax_send(){
	$dados = array(
		'user_login' => $_POST['username'],
		'user_pass' => $_POST['password'],
		'user_email' => $_POST['email'],
		'first_name' => $_POST['realname'],
		'captcha_code'  => $_POST['captcha_code']
	);
	
	session_start();
	if(class_exists('siCaptcha', false))
	{
		global $registro_captcha;
		$registro_captcha = new siCaptcha();
		
		if(strcasecmp( $_SESSION["securimage_code_si_com"], $dados['captcha_code'] ) != 0 )
		{
			echo "<div class='erro'>".__('Wrong CAPTCHA', 'si-captcha')."</div>";
			exit();
		}
	}
	
	$usuario = wp_insert_user($dados);
	if(!is_wp_error($usuario)){
		$url = array_key_exists('redirect_to', $_REQUEST) ? $_REQUEST['redirect_to'] : '';
		echo _x('Cadastro efetuado com sucesso. <a href="' . wp_login_url($url) . '">Clique aqui</a> para fazer o login.', 'registro-de-usuario', 'campanha-completa');
		echo '<script>jQuery(".formulario-de-registro-padrao .campos").slideUp();</script>';
	} else {
		$erros = $usuario->get_error_message();
		echo "<div class='erro'>{$erros}</div>";
	}
	
	exit();
}

add_action('wp_ajax_custom_register_send', 'custom_register_ajax_send');
add_action('wp_ajax_nopriv_custom_register_send', 'custom_register_ajax_send');

/**
 * registra os headers para o registro customizado
 */
function custom_register_header()
{
	if(get_query_var('custom_user_register'))
	{
		if(class_exists('siCaptcha', false))
		{
			global $registro_captcha;
			$registro_captcha = new siCaptcha;
			$registro_captcha->si_captcha_start_session();
		}
		wp_enqueue_script('jquery_validate', WPMU_PLUGIN_URL . '/js/jquery.validate.min.js', array('jquery'));
		wp_enqueue_script('jquery_validate_messages', WPMU_PLUGIN_URL . '/js/jquery.validate.messages_pt_BR.js', array('jquery', 'jquery_validate'));
	}
}
add_action('wp_head', 'custom_register_header');

function return_after_register($url, $path, $scheme, $blog_id)
{
	if($scheme == 'login' && strpos($url, 'action=register') !== false)
	{
		$url .= "&redirect_to=".urlencode(get_permalink());
	}
	return $url;
}
add_filter('site_url', 'return_after_register', 10, 4);

function campanha_top_style()
{
	wp_enqueue_style('twitter-track-fix', WPMU_PLUGIN_URL.'/css/twitter-tracker.css');
}
add_action('wp_enqueue_scripts', 'campanha_top_style', 1);

/**
* ****************************************************************************************************************
*
* Custom lost password
*
*/

define('lost_password_page', 'custom_lost_password');

function shortcode_lost_password(){
	include ABSPATH . '/wp-content/mu-plugins/includes/custom-lost-password.php';
}

/**
*
*
*/
function lost_password_reset(){
	global $wpdb, $current_site;

	$errors = new WP_Error();

	$user_data = get_user_by( 'email', trim( $_POST['user-email'] ) );
	
	if ( empty( $user_data ) )
		$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));

	do_action('lostpassword_post');

	if ( $errors->get_error_code() )
		return $errors;

	if ( !$user_data ) {
		$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
		return $errors;
	}
	
	$user_info = $user_data;
	$user_login = $user_info->user_login;
	$user_email = $user_info->user_email;
	
	//url para onde ele encaminha a validação do link por email, é preciso alterar.
	$validate_url = get_bloginfo('url').'/'.lost_password_page;
	
	if($user_info){
				
		/*
		* Daqui para a frente eu fiz com base na função original do wordpress.
		*/
		$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
		if ( empty($key) ) {
			// Generate something random for a key...
			$key = wp_generate_password(20, false);
			do_action('retrieve_password_key', $user_login, $key);
			// Now insert the new md5 key into the db
			$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
		}
		$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
		$message .= get_bloginfo('url') . "\r\n\r\n";
		$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= '<' . $validate_url . "?action=rp&key=$key&login=" . rawurlencode($user_login) . ">\r\n";
	
		if ( is_multisite() )
			$blogname = $GLOBALS['current_site']->site_name;
		else
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
		$title = sprintf( __('[%s] Password Reset'), $blogname );
	
		$title = apply_filters('retrieve_password_title', $title);
		$message = apply_filters('retrieve_password_message', $message, $key);
	
		if ( $message && !wp_mail($user_email, $title, $message) )
			wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );
		/*
		* Fim do ctrl+c do wordpress
		*/
		
		echo _x('Foi enviada uma mensagem para seu email com uma chave de validação e os próximos passos para recuperar sua senha', 'custom-lost-password', 'redelivre');
		return true;
		
	} else {
		echo _x('Nenhum usuário encontrado com este email', 'custom-lost-password', 'campanhacommpleta');
		return false;
	}
}

/**
*
*
*/
function lost_password_javascript()
{
	if(get_query_var(lost_password_page))
	{
		wp_register_script('custom_lost_password', network_site_url() . 'wp-content/mu-plugins/js/custom_lost_password.js', array('jquery'));
		
		wp_enqueue_script('custom_lost_password');
		
		//url para ajax
		wp_localize_script('custom_lost_password', 'custom_lost_password_ajax', array('url' => home_url() . '/wp-admin/admin-ajax.php'));
		
		if(file_exists(get_stylesheet_directory() . '/custom-lost-password.css') || file_exists(get_stylesheet_directory() . '/css/custom-lost-password.css'))
		{
			if(file_exists(get_stylesheet_directory() . '/custom-lost-password.css'))
			{
				wp_enqueue_style('custom-lost-password', get_stylesheet_directory_uri() . '/custom-lost-password.css');
			}
			else
			{
				wp_enqueue_style('custom-lost-password', get_stylesheet_directory_uri() . '/css/custom-lost-password.css');
			}
		}
		else
		{
			wp_enqueue_style('custom-lost-password', site_url() . '/wp-content/mu-plugins/css/custom-lost-password.css');
		}
	}
	
}

add_action('wp_enqueue_scripts', 'lost_password_javascript');

/**
*
*
*/
function lost_password_ajax_send(){
	lost_password_reset();
	exit();
}

add_action('wp_ajax_custom_lost_password_send', 'lost_password_ajax_send');
add_action('wp_ajax_nopriv_custom_lost_password_send', 'lost_password_ajax_send');

/**
*
*
*/
function lost_password_validate()
{
	
}

/***************************************************************************************************************/


add_shortcode('lost-password-form', 'shortcode_lost_password');

function custom_lostpassword_url($url)
{
	return bloginfo('url').'/'.lost_password_page;	
}
add_filter('lostpassword_url', 'custom_lostpassword_url');

/**
 *
 *
 */
function custom_lost_password_query_var($query_vars){
	$query_vars[] = lost_password_page;
	return $query_vars;
}

add_filter('query_vars', 'custom_lost_password_query_var');

/**
 *
 *
 */
function custom_lost_password_form()
{
	if(
		get_query_var(lost_password_page) || 
		(
			basename($_SERVER['PHP_SELF']) === 'admin-ajax.php' &&
			array_key_exists('action', $_POST) &&
			$_POST['action'] == 'resetpass'
		)
	)
	{
		nocache_headers();
		if( 
			(array_key_exists('action', $_GET) && $_GET['action'] == 'rp') ||
			(array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'resetpass')
		)
		{
			// baseado no wp-login code line: 458
			$user = custom_lost_password_check_password_reset_key($_REQUEST['key'], $_REQUEST['login']);
		
			if ( is_wp_error($user) )
			{
				wp_redirect( site_url(lost_password_page.'/?action=lostpassword&error=invalidkey') );
				exit;
			}
		
			$errors = new WP_Error();
		
			if ( isset($_POST['pass1']) && $_POST['pass1'] != $_POST['pass2'] )
				$errors->add( 'password_reset_mismatch', __( 'The passwords do not match.' ) );
		
			do_action( 'validate_password_reset', $errors, $user );
		
			if ( ( ! $errors->get_error_code() ) && isset( $_POST['pass1'] ) && !empty( $_POST['pass1'] ) ) {
				custom_lost_password_reset_password($user, $_POST['pass1']);
				echo  '<p class="message reset-pass">' . __( 'Your password has been reset.' ) . ' <a href="' . esc_url( wp_login_url() ) . '">' . __( 'Log in' ) . '</a></p>';
				die();
			}
			if($errors->get_error_code())
			{
				echo implode('<br/>', $errors->get_error_messages());
			}
		
			wp_enqueue_script('utils');
			wp_enqueue_script('user-profile');
			//end wp-login code
		}
		get_header();
		$file_path = get_stylesheet_directory() . '/template-custom-lost-password.php';
		if(file_exists($file_path))
		{
			include $file_path;
		}
		else
		{
			include ABSPATH . '/wp-content/mu-plugins/includes/custom-lost-password.php';
		}
		get_footer();
		exit();
	}
}
add_action('template_redirect', 'custom_lost_password_form');
add_action('wp_ajax_resetpass', 'custom_lost_password_form');
add_action('wp_ajax_nopriv_resetpass', 'custom_lost_password_form');

/**
 *
 *
 */
function custom_lost_password_rule(){

	add_rewrite_rule(lost_password_page.'(.*)', 'index.php?'.lost_password_page.'=true$matches[1]', 'top');
	//flush_rewrite_rules();
}

add_action('init', 'custom_lost_password_rule');