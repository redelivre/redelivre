<?php
// load campaign base files
foreach (glob(WPMU_PLUGIN_DIR . '/campaign_base/*.php') as $file) {
    require_once($file);
}

// inclui os widgets
foreach (glob(WPMU_PLUGIN_DIR . '/includes/widgets/*.php') as $file) {
    require_once($file);
}

$campaign = null;

// load code used only for campaign sites (exclude main site)
if (!is_main_site()) {
    // must wait for wordpress to finish loading before loading campaign code
    add_action('init', function() {
    global $blog_id, $campaign;

    require_once(__DIR__ . '/includes/payment.php');
    require_once(__DIR__ . '/includes/EasyAjax.php');
    require_once(__DIR__ . '/includes/mobilize/Mobilize.php');
    require_once(__DIR__ . '/includes/admin-contact.php');

    $campaign = Campaign::getByBlogId($blog_id);
    GraphicMaterial::setUp();

    if (is_admin()) {
        require_once(__DIR__ . '/includes/load_menu_options.php');
    }

    add_action('template_redirect', 'campanha_check_payment_status');
    add_filter('query_vars', 'campaign_base_custom_query_vars');
    add_filter('rewrite_rules_array', 'campaign_base_custom_url_rewrites', 10, 1);
    add_action('template_redirect', 'campaign_base_template_redirect_intercept');
    add_filter('login_message', 'campanha_login_payment_message');
    add_action('admin_notices', 'campanha_admin_payment_message');
    add_filter('site_option_upload_space_check_disabled', 'campanha_unlimited_upload');
    add_action('admin_init', 'campanha_remove_menu_pages');
    add_action('load-ms-delete-site.php', 'campanha_remove_exclude_site_page_content');
    add_action('wp_dashboard_setup', 'campannha_dashboard_widget');
    add_action('load-options-general.php', 'campanha_custom_options_strings');

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

function campaign_base_custom_query_vars($public_query_vars) {
    $public_query_vars[] = "tpl";

    return $public_query_vars;
}

// REDIRECIONAMENTOS
function campaign_base_custom_url_rewrites($rules) {
    $new_rules = array(
        "materialgrafico/?$" => "index.php?tpl=materialgrafico",
        "mobilizacao/?$" => "index.php?tpl=mobilizacao",
        "contato/?$" => "index.php?tpl=contato",
    );

    return $new_rules + $rules;
}

function campaign_base_template_redirect_intercept() {
    global $wp_query;

    switch ($wp_query->get('tpl')) {
        case 'materialgrafico':
            require(WPMU_PLUGIN_DIR . '/includes/tpl-graphic_material_list_links.php');
            die;
        case 'mobilizacao':
            add_action('wp_print_scripts', function() {
                        wp_enqueue_script('contato', WPMU_PLUGIN_URL . '/js/mobilize.js');
                    });

            require(WPMU_PLUGIN_DIR . '/includes/tpl-mobilize.php');
            die;
        case 'contato':
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
 * Display a message in the login page about the
 * payment.
 * 
 * @param string $message
 * @return string
 */
function campanha_login_payment_message($message) {
    global $campaign;

    if (!$campaign->isPaid()) {
        // $message .= '<p class="message">Esta campanha está visível somente para o criador pois o pagamento está pendente. <a href='$link'>Pague agora!</a></p>';
        $message .= '<p class="message">Esta campanha ainda não está disponível.</p>';
    }

    return $message;
}

/**
 * Display a message in the admin panel about
 * the payment.
 */
function campanha_admin_payment_message() {
    global $campaign;

    if (!$campaign->isPaid()) {
        $link = admin_url('admin.php?page=payments');
        // echo "<div class='error'><p>Esta campanha está visível somente para o criador pois o pagamento está pendente. <a href='$link'>Pague agora!</a></p></div>";
        // temporarily remove link to payment page while it is not finished
        echo "<div class='error'><p>Esta campanha está visível somente para o criador pois o pagamento está pendente.</p></div>";
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

add_action('wp_print_scripts', 'campanha_add_common_js');

/**
 * Add JS files shared by all themes.
 */
function campanha_add_common_js() {
    if (is_user_logged_in() && !is_super_admin()) {
        wp_enqueue_script('uservoice', site_url() . '/wp-content/mu-plugins/js/uservoice.js', 'jquery', false, true);
    }

    wp_enqueue_script('jquery');
    wp_enqueue_script('campaign_common', site_url() . '/wp-content/mu-plugins/js/campaign_common.js', 'jquery');
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

function include_campanha_theme_options() {
    require_once(WPMU_PLUGIN_DIR . '/includes/theme-options.php');
}

add_action('campanha_body_header', function() {
    $redes = get_option('campanha_social_networks');
    $redes = is_array($redes) ? $redes : array();
    ?>
    
	<div id="social-bookmarks" class="alignright">
		<?php if(@$redes['facebook']): ?><a id="facebook" href="<?php echo $redes['facebook'] ?>" title="Facebook"></a><?php endif; ?>
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
 * @param object $phpmailer object phpmailer usado pelo wordpress para disparar email
 */
function campanha_use_smtp($phpmailer) {

    $email_p = 'ethymos@hacklab';
    $email = 'noreply@campanhacompleta.com.br';

    $phpmailer->IsSMTP();
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = 465;
    $phpmailer->SMTPSecure = 'ssl';
    $phpmailer->Host       = 'smtp.gmail.com';
    $phpmailer->Username   = $email;
    $phpmailer->Password   = $email_p;
    $phpmailer->Sender     = $email;
    //$phpmailer->From       = $email;
    //$phpmailer->FromName   = 'Campanha Completa'; -- Acho que podemos deixar isso inalterado, e manter o que foi colocado quando a wp_mail() foi chamada
    
}
add_action('phpmailer_init', 'campanha_use_smtp');


// custom admin login logo
function custom_login_logo() {
	$baseUrl = WP_CONTENT_URL . '/themes/campanha/';
    echo '
		<link rel="stylesheet" type="text/css" media="all" href=" '.$baseUrl.'style.css" />
        <style type="text/css">
			
	        .login h1 a { height: 180px; background-image: url('. $baseUrl .'img/logo.png); }
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
    return get_bloginfo('url');
}
add_filter('login_headerurl', 'custom_login_headerurl');

