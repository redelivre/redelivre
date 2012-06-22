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
        $message .= '<p class="message">Esta campanha está visível somente para o criador pois o pagamento está pendente.</p>';
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
        //echo "<div class='error'><p>Esta campanha está visível somente para o criador pois o pagamento está pendente. <a href='$link'>Pague agora!</a></p></div>";
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
    <div class="wrap clearfix">
        <div id="social-bookmarks" class="alignright">
            <?php if(@$redes['facebook']): ?><a id="facebook" href="<?php echo $redes['facebook'] ?>" title="Facebook"></a><?php endif; ?>
            <?php if(@$redes['twitter']): ?><a id="twitter" href="<?php echo $redes['twitter'] ?>" title="Twitter"></a><?php endif; ?>
            <?php if(@$redes['google']): ?><a id="google-plus" href="<?php echo $redes['google'] ?>" title="Google+"></a><?php endif; ?>
            <?php if(@$redes['youtube']): ?><a id="youtube" href="<?php echo $redes['youtube'] ?>" title="YouTube"></a><?php endif; ?>
            <a id="rss" href="<?php echo bloginfo('url') ?>/rss" title="RSS"></a>
        </div>
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
