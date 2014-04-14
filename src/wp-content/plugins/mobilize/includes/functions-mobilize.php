<?php

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * [mobilize_tpl description]
 * @return [type] [description]
 */
function mobilize_tpl()
{
    global $wp_query, $campaign;
}

add_action('template_redirect', 'mobilize_tpl');

/**
 * [mobilize_page_load_assets description]
 * @return [type] [description]
 */
function mobilize_page_load_assets()
{
	$options = Mobilize::getOption();

	wp_enqueue_style('mobilize-template-style',
			plugins_url('/mobilize/assets/css/mobilize.css', INC_MOBILIZE));
	wp_enqueue_script('mobilize-template',
			plugins_url('/mobilize/assets/js/template.js', INC_MOBILIZE));

	if (is_plugin_active('contribua/contribua.php')
			&& array_key_exists('general', $options)
			&& array_key_exists('contribua', $options['general'])
			&& $options['general']['contribua'])
	{
		Contribua::addJavascript();
		Contribua::addStylesheet();
	}
}

/**
 * [mobilize_single_template_assets description]
 * @param  [type] $single_template [description]
 * @return [type]                  [description]
 */
function mobilize_single_template_assets($single_template)
{
    if ('mobilize' == get_page_template_slug()) {
        mobilize_page_load_assets();
    }

    return $single_template;
}

add_filter('page_template', 'mobilize_single_template_assets');

/**
 * [mobilize_single_template description]
 * @param  [type] $single_template [description]
 * @return [type]                  [description]
 */
function mobilize_single_template($single_template)
{
	if ('mobilize' == get_page_template_slug())
	{
		global $post;

		$post_slug = $post->post_name;

		add_action('wp_print_scripts', function() {
			wp_enqueue_script('mobilize',
				plugins_url('/mobilize/assets/js/mobilize.js', INC_MOBILIZE));
		});

		$templateTheme = get_stylesheet_directory().'/mobilize.php';
		return file_exists($templateTheme) ?
			$templateTheme : INC_MOBILIZE.'/includes/tpl-mobilize.php';
	}

	return $single_template;
}

add_filter('page_template', 'mobilize_single_template');

/**
 * [mobilize_menu_page_stylesheets description]
 * @return [type] [description]
 */
function mobilize_menu_page_stylesheets()
{
    if (isset($_GET['page']) && $_GET['page'] === 'Mobilize') {
        wp_enqueue_style('admin-mobilize', plugins_url('/assets/css/admin-mobilize.css', INC_MOBILIZE));
    }
}

add_action('admin_menu', 'mobilize_menu_page_stylesheets');

/**
 * [mobilize_add_menu_page description]
 * @return [type] [description]
 */
function mobilize_add_menu_page() 
{
    global $capabilities;
    
    if (current_user_can('manage_options')) {
        add_menu_page('Mobilização', 'Mobilização', 'read', 'mobilize', function() {
            ///////////////////////////
            // Persistência de dados //
            ///////////////////////////

            Mobilize::saveSettings();
            Mobilize::saveRedesSociais();

            //////////////////////
            // Acesso aos dados //
            //////////////////////

            $option = Mobilize::getOption();
            $optionsRedesSociais = Mobilize::optionRedesSociais();
            
            require INC_MOBILIZE.'/includes/admin-mobilize.php';
        });

        add_submenu_page('mobilize', 'Configurações', 'Configurações', 'read', 'mobilize');

        /*add_submenu_page('mobilize', 'Ajuda', 'Ajuda', 'read', 'mobilize-ajuda', function(){
            require INC_MOBILIZE.'/includes/help-mobilize.php';
        });*/
    }
}

add_action('admin_menu', 'mobilize_add_menu_page');

/**
 * [do_mobilize_action description]
 * @return [type] [description]
 */
function do_mobilize_action() 
{
	if(!class_exists('WideImage', false)) // check if already loaded this lib https://github.com/redelivre/redelivre/issues/5
	{
		require INC_MOBILIZE.'/includes/wideimage/WideImage.php';
	}
	require INC_MOBILIZE.'/includes/smartView.php';
	require INC_MOBILIZE.'/includes/functions-mobilize.php';
	require INC_MOBILIZE.'/vendor.php';
	
	if (Mobilize::isActive('adesive')
			&& array_key_exists('photo', $_FILES)
			&& array_key_exists('error', $_FILES['photo'])
			&& $_FILES['photo']['error'] == UPLOAD_ERR_OK)
		Mobilize::adesivar('photo');
}

add_action('init', 'do_mobilize_action', 100);

/**
 * [mobilize_instalacao description]
 * @return [type] [description]
 */
function mobilize_instalacao() 
{
	if (is_multisite()) {
		flush_rewrite_rules();
	}
}

register_activation_hook(__FILE__, 'mobilize_instalacao');

/**
 * [mobilize_template_contribua description]
 * @return [type] [description]
 */
function mobilize_template_contribua()
{
	$options = Mobilize::getOption();
	ob_start();
	if (is_plugin_active('contribua/contribua.php')
			&& array_key_exists('general', $options)
			&& array_key_exists('contribua', $options['general'])
			&& $options['general']['contribua'])
	{
		$padding = array_key_exists('general', $options)
			&& array_key_exists('espacamento_lateral', $options['general']) ?
			$options['general']['espacamento_lateral'] : '';
		require INC_MOBILIZE . '/views/contribua.php';
	}

	return ob_get_clean();
}

/**
 * [mobilize_template_chamada description]
 * @return [type] [description]
 */
function mobilize_template_chamada()
{
    $options = Mobilize::getOption();

    $smartView = new smartView(INC_MOBILIZE.'/views/chamada.php');
    $smartView->padding      = isset($options['general']['espacamento_lateral']) ? $options['general']['espacamento_lateral'] : '';
    $smartView->chamadaTitle = !empty($options['general']['title']) ? $options['general']['title'] : 'Apoie este projeto';
    $smartView->chamadaDescription = isset($options['general']['description']) ? $options['general']['description'] : (isset($option['general']['ocultarexplicacao']) ? '' : 'Nesta página, você encontra diferentes formas de mobilização e apoio.');
    return $smartView->display();
}

/**
 * [mobilize_template_banners description]
 * @return [type] [description]
 */
function mobilize_template_banners()
{
    if (Mobilize::isActive('banners') && Mobilize::getBannerURL(250) != '') {
        $options = Mobilize::getOption();

        $smartView = new smartView(INC_MOBILIZE.'/views/banners.php');
        $smartView->padding           = isset($options['general']['espacamento_lateral']) ? $options['general']['espacamento_lateral'] : '';
        $smartView->bannerTitle = __('Banners', 'mobilize');
        $smartView->bannerDescription = $options['banners']['description'];
        $smartView->bannerCode250 = htmlentities('<a href="' . get_bloginfo('url') . '/mobilize"><img src="' . Mobilize::getBannerURL(250) . '" /></a>');
        $smartView->bannerCode200 = htmlentities('<a href="' . get_bloginfo('url') . '/mobilize"><img src="' . Mobilize::getBannerURL(200) . '" /></a>');
        $smartView->bannerCode125 = htmlentities('<a href="' . get_bloginfo('url') . '/mobilize"><img src="' . Mobilize::getBannerURL(125) . '" /></a>');

        $smartView->bannerURL250 = Mobilize::getBannerURL(250);
        $smartView->bannerURL200 = Mobilize::getBannerURL(200);
        $smartView->bannerURL125 = Mobilize::getBannerURL(125);

        $smartView->bannerPermaLink = get_permalink();

        return $smartView->display();
    }
}

/**
 * [mobilize_template_social description]
 * @return [type] [description]
 */
function mobilize_template_social()
{
    if (Mobilize::isActive('redes')) {
        $options = Mobilize::getOption();
        $optionsRedesSociais = Mobilize::optionRedesSociais();

        $smartView = new smartView(INC_MOBILIZE.'/views/redes-sociais.php');
        $smartView->padding           = isset($options['general']['espacamento_lateral']) ? $options['general']['espacamento_lateral'] : '';
        $smartView->socialTitle = __('Redes sociais', 'mobilize');
        $smartView->socialDescription = $options['redes']['description'];

        if (!is_null($optionsRedesSociais['redes_facebook_page']) && !empty($optionsRedesSociais['redes_facebook_page'])) {
            $smartView->socialFacebook = '<a class="mobilize-button mobilize-facebook" target="_blank" href="'.$optionsRedesSociais['redes_facebook_page'].'">Facebook</a>';
        } 

        if (!is_null($optionsRedesSociais['redes_twitter']) && !empty($optionsRedesSociais['redes_twitter'])) {
            $smartView->socialTwitter = '<a class="mobilize-button mobilize-twitter" target="_blank" href="'.$optionsRedesSociais['redes_twitter'].'">Twitter</a>';
        } 

        if (!is_null($optionsRedesSociais['redes_google']) && !empty($optionsRedesSociais['redes_google'])) {
            $smartView->socialGoogle = '<a class="mobilize-button mobilize-google" target="_blank" href="'.$optionsRedesSociais['redes_google'].'">Google +</a>';
        } 
            
        if (!is_null($optionsRedesSociais['redes_youtube']) && !empty($optionsRedesSociais['redes_youtube'])) {
            $smartView->socialYoutube = '<a class="mobilize-button mobilize-youtube" target="_blank" href="'.$optionsRedesSociais['redes_youtube'].'">Youtube</a>';
        } 

        return $smartView->display();
    }
}

/**
 * [mobilize_template_adesive description]
 * @return [type] [description]
 */
function mobilize_template_adesive()
{
    if (Mobilize::isActive('adesive') && Mobilize::getAdesiveURL()) {
        $options = Mobilize::getOption();

        $smartView = new SmartView(INC_MOBILIZE.'/views/adesive.php');
        $smartView->padding            = isset($options['general']['espacamento_lateral']) ? $options['general']['espacamento_lateral'] : '';
        $smartView->adesiveTitle = __('Adesive sua foto!', 'mobilize');
        $smartView->adesiveDescription = $options['adesive']['description'];
        $smartView->baseURL = get_bloginfo('url');
        $smartView->adesiveURL = Mobilize::getAdesiveURL();

        return $smartView->display();
    }
}

/**
 * [mobilize_template_enviar description]
 * @return [type] [description]
 */
function mobilize_template_enviar()
{
    if (Mobilize::isActive('envie')) {
        $options = Mobilize::getOption();

        $smartView = new SmartView(INC_MOBILIZE.'/views/enviar.php');
        $smartView->padding           = isset($options['general']['espacamento_lateral']) ? $options['general']['espacamento_lateral'] : '';
        $smartView->enviarTitle = __('Envie para um amigo!', 'mobilize');
        $smartView->enviarDescription = $options['envie']['description'];
				$smartView->enviarEmailCorpo  = $options['envie']['message'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
						$senderName = array_key_exists('sender-name', $_POST) ?
							$_POST['sender-name'] : '';
						$senderEmail = array_key_exists('sender-email', $_POST) ?
							$_POST['sender-email'] : '';
						$recipients = array_key_exists('recipient-email', $_POST) ?
							$_POST['recipient-email'] : '';
						$message = array_key_exists('sender-message', $_POST) ?
							$_POST['sender-message'] : '';

            $smartView->enviarCampoNome = $senderName;
            $smartView->enviarCampoEmail = $senderEmail;
            $smartView->enviarCampoDestinos = $recipients;
            $smartView->enviarCampoMensagem = $message;
            $smartView->enviarMessage =
							Mobilize::enviarEmails($senderName, $senderEmail,
									$recipients, $message) ?
							__('Mensagem enviada!', 'mobilize') :
							__('Houve um erro ao enviar sua mensagem, tente novamente!',
									'mobilize');
        }

        return $smartView->display();
    }
}

/**
 * [mobilize_shortag_social description]
 * @return [type] [description]
 */
function mobilize_shortag_social()
{
    mobilize_page_load_assets();
    return mobilize_template_social();
}

add_shortcode('mobilize-social', 'mobilize_shortag_social');

/**
 * [mobilize_shortag_adesive description]
 * @return [type] [description]
 */
function mobilize_shortag_adesive()
{
    mobilize_page_load_assets();
    return mobilize_template_adesive();
}

add_shortcode('mobilize-adesive', 'mobilize_shortag_adesive');

/**
 * [mobilize_shortag_enviar description]
 * @return [type] [description]
 */
function mobilize_shortag_enviar()
{
    mobilize_page_load_assets();
    return mobilize_template_enviar();
}

add_shortcode('mobilize-enviar', 'mobilize_shortag_enviar');

/**
 * [mobilize_shortag_banners description]
 * @return [type] [description]
 */
function mobilize_shortag_banners()
{
    mobilize_page_load_assets();
    return mobilize_template_banners();
}

add_shortcode('mobilize-banners', 'mobilize_shortag_banners');

/**
 * [mobilize_shortag_banners description]
 * @return [type] [description]
 */
function mobilize_shortag()
{
    mobilize_page_load_assets();

    $storeTemplatesArray = array(
        mobilize_template_contribua(),
        mobilize_template_social(), 
        mobilize_template_banners(), 
        mobilize_template_adesive(), 
        mobilize_template_enviar()
    );

    return implode("\n", $storeTemplatesArray);
}

add_shortcode('mobilize', 'mobilize_shortag');
