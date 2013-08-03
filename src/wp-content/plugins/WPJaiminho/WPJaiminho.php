<?php
/*
Plugin Name: Jaiminho
Plugin URI: http://www.ethymos.com.br
Description: O Plugin Jaiminho integra a ferramenta Jaiminho ao WordPress
Version: 0.1 beta
Author: Ethymos
Author URI: http://www.ethymos.com.br

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

// Defines
if(!defined('__DIR__')) {
    $iPos = strrpos(__FILE__, DIRECTORY_SEPARATOR);
    define("__DIR__", substr(__FILE__, 0, $iPos) . DIRECTORY_SEPARATOR);
}

define('JAIMINHO_FOLDER', dirname(plugin_basename(__FILE__)));

$jaiminho_siteurl = get_option('siteurl');
if(is_ssl()) {
	$jaiminho_siteurl = str_replace("http://", "https://", $jaiminho_siteurl);
}

$jaiminho_plugin_url = WP_CONTENT_URL;
if(is_ssl()) {
  $plugin_url_parts = parse_url($jaiminho_plugin_url);
  $site_url_parts = parse_url($jaiminho_siteurl);
  if(stristr($plugin_url_parts['host'], $site_url_parts['host']) && stristr($site_url_parts['host'], $plugin_url_parts['host'])) {
		$jaiminho_plugin_url = str_replace("http://", "https://", $jaiminho_plugin_url);
	}
}

define('JAIMINHO_URL', $jaiminho_plugin_url.'/plugins/'.JAIMINHO_FOLDER);

// End Defines


/*
 * Rotinas de instalação do plugin
 */
function jaiminho_get_config(){
	$opt = array();
	
	$opt['jaiminho_style'] = '';
	$opt['jaiminho_class'] = '';
	$opt['jaiminho_border'] = '';
	$opt['jaiminho_scrolling'] = 'no';
	$opt['jaiminho_scrollmethod'] = 1;
	$opt['jaiminho_url'] = 'http://campanha.jaiminho.com.br/';
	$opt['jaiminho_admin_url'] = 'http://campanha.jaiminho.com.br/admin/';
	$opt['jaiminho_user'] = '';
	$opt['jaiminho_pass'] = '';
	$opt['jaiminho_apikey'] = 'AIzaSyDHowXjdVc2WOEx25AnVzF_tsWBUaY6wVA';
	$opt['width'] = 960;
	$opt['height'] = 2500;
	$opt['jaiminho_sms'] = 'N';
	
	$opt_conf = get_option('jaiminho-config');
	if(!is_array($opt_conf)) $opt_conf = array();
	$opt = array_merge($opt, $opt_conf);
	if(has_filter('jaiminho_get_config'))
	{
		$opt = apply_filters('jaiminho_get_config', $opt);
	}
	return $opt;
} 

/**
*
*
*/
function jaiminho_instalacao() { 
	
}

register_activation_hook(__FILE__,'jaiminho_instalacao');

/**
* Inicialização do plugin
*
*/
function jaiminho_init(){
	add_action('admin_menu', 'jaiminho_config_menu');
}

/**
*
*/
function jaiminho_scripts(){
	wp_enqueue_script('jaiminho', JAIMINHO_URL.'/js/WPJaiminho.js',array('jquery','jquery-form'));
}

// Registra actions
add_action('admin_init','jaiminho_instalacao');
add_action('init','jaiminho_init');
add_action( 'wp_print_scripts', 'jaiminho_scripts' );

// Fim Inicialização do plugin

// Menu de configuração

/**
*
*/
function jaiminho_config_menu(){
	$opt = jaiminho_get_config();
	
	$base_page = 'jaiminho-campanha';
	if (function_exists('add_menu_page')) 
		add_object_page( __('Email/SMS','Email/SMS'), __('Email/SMS','Jaiminho'), 'manage_options', $base_page, array(), JAIMINHO_URL."/imagens/icon.png");
		add_submenu_page($base_page, __('Criar lista','jaiminho'), __('Criar lista','jaiminho'), 'manage_options', 'jaiminho-criarlista', 'jaiminho_criarlista' );
		add_submenu_page($base_page, __('Explorar listas','jaiminho'), __('Explorar listas','jaiminho'), 'manage_options', 'jaiminho-explorarlistas', 'jaiminho_explorarlistas' );
		add_submenu_page($base_page, __('Campos personalizados','jaiminho'), __('Campos personalizados','jaiminho'), 'manage_options', 'jaiminho-campospersonalizados', 'jaiminho_campospersonalizados' );
		add_submenu_page($base_page, __('Importar membros','jaiminho'), __('Importar membros','jaiminho'), 'manage_options', 'jaiminho-importarmembros', 'jaiminho_importarmembros' );
		add_submenu_page($base_page, __('Enviar emails','jaiminho'), __('Nova campanha (envio)','jaiminho'), 'manage_options', 'jaiminho-campanha', 'jaiminho_campanha' );
		add_submenu_page($base_page, __('Alterar nome do remetente padrão','jaiminho'), __('Alterar nome do remetente padrão','jaiminho'), 'manage_options', 'jaiminho-remetente', 'jaiminho_remetente' );
		add_submenu_page($base_page, __('Explorar campanhas','jaiminho'), __('Explorar campanhas','jaiminho'), 'manage_options', 'jaiminho-explorarcampanhas', 'jaiminho_explorarcampanhas' );
	
		if ($opt['jaiminho_sms'])
			add_submenu_page($base_page, __('Mensagens de celular','jaiminho'), __('Mensagens de celular','jaiminho'), 'manage_options', 'jaiminho-sms', 'jaiminho_sms' );
		
		add_submenu_page($base_page, __('Configurações do Plugin','jaiminho'),__('Configurações do Plugin','jaiminho'), 'manage_options', 'jaiminho-config', 'jaiminho_conf_page');
		if (is_super_admin()) {
			add_submenu_page($base_page, __('Listar usuários do Jaiminho','jaiminho'),__('Listar usuários do Jaiminho','jaiminho'), 'manage_options', 'jaiminho-listarusuarios', 'jaiminho_listarusuarios');
		}
	
}

/**
 * Create a form table from an array of rows
 */
function jaiminho_form_table($rows) {
	$content = '<table class="form-table">';
	foreach ($rows as $row) {
		$content .= '<tr '.(array_key_exists('row-id', $row) ? 'id="'.$row['row-id'].'"' : '' ).' '.(array_key_exists('row-style', $row) ? 'style="'.$row['row-style'].'"' : '' ).' '.(array_key_exists('row-class', $row) ? 'class="'.$row['row-class'].'"' : '' ).' ><th valign="top" scrope="row">';
		if (isset($row['id']) && $row['id'] != '')
			$content .= '<label for="'.$row['id'].'">'.$row['label'].':</label>';
		else
			$content .= $row['label'];
		if (isset($row['desc']) && $row['desc'] != '')
			$content .= '<br/><small>'.$row['desc'].'</small>';
		$content .= '</th><td valign="top">';
		$content .= $row['content'];
		$content .= '</td></tr>'; 
	}
	$content .= '</table>';
	return $content;
}

/**
 * Create a postbox widget
 */
function jaiminho_postbox($id, $title, $content) {
?>
	<div id="<?php echo $id; ?>" class="postbox">
		<div class="handlediv" title="Click to toggle"><br /></div>
		<h3 class="hndle"><span><?php echo $title; ?></span></h3>
		<div class="inside">
			<?php echo $content; ?>
		</div>
	</div>
<?php
}	

/**
 * Gera a página de configuração/Tratamento dos dados de Post
 */
function jaiminho_conf_page()
{
	global $blog_id;
	
	$mensagem = false;
	
	if ($_SERVER['REQUEST_METHOD']=='POST')
	{
		if (!current_user_can('manage_options')) die(__('Você não pode editar as configurações do jaiminho.','jaiminho'));
			check_admin_referer('jaiminho-config');
		
		$opt = jaiminho_get_config();
		
		if ($_POST['jaiminho_recreatecredentials']) 
		{
			$errors = array();
			
			$blog_details = get_blog_details($blog_id);
			
			$id = explode('.',$blog_details->domain);
			
			$id = $id[0];
			
			// Muda para o blog principal para pegar as configurações padrão
			switch_to_blog(1);
			
				$opt = jaiminho_get_config();
						
			restore_current_blog();
			
			$opt_contatos = get_option('webcontatos-config');
			
			if (!isset($opt_contatos['webcontatos_pass'])) {
				$opt_contatos['webcontatos_pass'] = md5(uniqid());
				
				update_option('webcontatos-config',$opt_contatos);
			}	
			
			$campaign = Campaign::getByBlogId($blog_details->blog_id);
			
			$plan_capabilities = Capability::getByPlanId($campaign->plan_id);
			
			$limite_emails = ((int)$plan_capabilities->send_messages->value * 1000);
			
			$output_headers = null;
	
			try {
				$client=new SoapClient($opt['jaiminho_url'].'/james_bridge.php?wsdl', array('exceptions' => true));
				$defaultmailinglist_id = $client->__soapCall('createadmin', array('apikeymaster' => $opt['jaiminho_apikey'], 'name' => get_blog_option($blog_details->blog_id,'blogname'), 'username' => $id,'email' => 'contato@'.$blog_details->domain, 'password' => $opt_contatos['webcontatos_pass'], 'plan' => $limite_emails) , array(), null, $output_headers);
			} catch (Exception $ex) {
				$errors[] = '('.$ex->faultcode.') '.$ex->faultstring.' - '.$ex->detail;
			}
			
			$opt['jaiminho_user'] = $id;
			$opt['jaiminho_pass'] = $opt_contatos['webcontatos_pass'];
			
			update_option('jaiminho-config', $opt);
			
			add_option( 'widget_jaiminho',
							array( 	'title' => 'Cadastre seu e-mail',
									'jaiminho_text' => 'Receba novidades da campanha',
									'jaiminho_id' => $defaultmailinglist_id));
			
			$sidebar_widget = get_option("sidebars_widgets");
			
			$sidebar_widget['sidebar-1'] = array_merge(array("jaiminho"),$sidebar_widget['sidebar-1']);
		
			update_option("sidebars_widgets",$sidebar_widget);
			
			if (count($errors) > 0) {
				update_option('jaiminho-error-log', $errors);
				
			}
			else {
				delete_option('jaiminho-error-log');
			}
		}
		
		foreach ( array_keys(jaiminho_get_config()) as $option_name )
		{
			$opt[$option_name] = htmlspecialchars($_POST[$option_name]);
		}
					
		$opt['jaiminho_data_atualizacao'] = date("Y-m-d H:i:s", time());
		$opt['jaiminho_user_atualizacao'] = get_current_user_id();
		
		
		if (isset($_POST["jaiminho_recreatecredentials"])) {
			if (get_option('jaiminho-error-log',false))
				$mensagem = "Ocorreu um erro ao recriar as credenciais do recurso de envio de emails. Consulte o suporte do Campanha Completa";
			else
				$mensagem = "Credenciais recriadas com sucessso!";	
			
		} else {
			if (update_option('jaiminho-config', $opt))
				$mensagem = __('Configurações salvas!','jaiminho');
			else
				$mensagem = __('Erro ao salvar as configurações. Verifique os valores inseridos e tente novamente!','jaiminho');	
		}
	}

	$opt = jaiminho_get_config();
	?>
	
	<div class="wrap">
		<h2>Configurações gerais</h2>
		<div class="postbox-container" style="width:80%;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
					<?php if ($mensagem) {?>
					<div id="message" class="updated">
					<?php echo $mensagem; ?>
					</div>
					<?php }?>
					<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" id="jaiminho-config" >
					<?php if (function_exists('wp_nonce_field')) 		
						wp_nonce_field('jaiminho-config');
						$table = '
						<table class="form-table">
				        <tr valign="top">
					        <th scope="row">Mostrar Bordas</th>
					        <td>
								<input type="checkbox" name="jaiminho_border" value="1" '.(($opt['jaiminho_border'] == '1') ? 'checked="checked"' : '').' />
							</td>
				        </tr>
				        
				        <tr valign="top">
				        <th scope="row">Barra de rolagem</th>
				        <td>
							<select name="jaiminho_scrolling">
								<option value="auto" >Auto</option>
								<option value="yes" '.(($opt['jaiminho_scrolling'] == 'yes')? 'selected="selected"' : '').'>Sim</option>
								<option value="no" '.(($opt['jaiminho_scrolling'] == 'no')? 'selected="selected"' : '').'>Não</option>
							</select>
						</td>
				        </tr>
				        
				        <tr valign="top">
				        <th scope="row">Método de rolagem</th>
				        <td>
							<select name="jaiminho_scrollmethod">
								<option value="0">#1 (serviço no mesmo domínio)</option>
								<option value="1" '.(($opt['jaiminho_scrollmethod'] == '1') ? 'selected="selected"' : '' ).'>#2 (em outro domínio)</option>
							</select><br />
							<b>Info:</b>
							Método #2 esconde parcialmente as barras de rolagem, esse método deveria ser usado com junto com a opção de "Não" para barra de rolagem
						</td>
				        </tr>
				        
						<tr valign="top">
				        <th scope="row">Nome da classe do estilo </th>
				        <td><input type="text" name="jaiminho_class" style="width: 400px" value="'.$opt['jaiminho_class'].'" /></td>
				        </tr>
				         
				        <tr valign="top">
				        <th scope="row">Estilo CSS customizado</th>
				        <td>
							<textarea name="jaiminho_style" style="width: 400px; height: 70px">'.$opt['jaiminho_style'].'</textarea><br />
							<b>Info:</b>
							Não use "width" e "height" - esses valores devem ser especificados nas configurações<br />
						</td>
				        </tr>
				        
				    </table>';
						$rows = array();
						$rows[] = array(
							"id" => "height",
							"label" => __('Altura','jaiminho'),
							"content" => '<input type="text" name="height" id="height" value="'.htmlspecialchars_decode($opt['height']).'"/>'
						);
						$rows[] = array(
							"id" => "width",
							"label" => __('Comprimento','jaiminho'),
							"content" => '<input type="text" name="width" id="width" value="'.htmlspecialchars_decode($opt['width']).'"/>'
						);
						if(is_super_admin())
						{
							$id = 'jaiminho_sms';
							$rows[] = array(
									"id" => $id,
									"label" => __('Acessar recursos de SMS','jaiminho'),
									"content" => '<input type="checkbox" name="'.$id.'" id="'.$id.'" value="S" '.($opt['jaiminho_sms'] ? 'checked': '').'/>'
							);
						
							$id = 'jaiminho_url';
							$rows[] = array(
									"id" => $id,
									"label" => __('Url base do serviço Jaiminho','jaiminho'),
									"content" => '<input type="text" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars_decode($opt[$id]).'"/>'
							);
						
							$id = 'jaiminho_admin_url';
							$rows[] = array(
									"id" => $id,
									"label" => __('Url base do serviço Jaiminho','jaiminho'),
									"content" => '<input type="text" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars_decode($opt[$id]).'"/>'
							);			
							
							$id = 'jaiminho_user';
							$rows[] = array(
									"id" => $id,
									"label" => __('Usuário','jaiminho'),
									"content" => '<input type="text" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars_decode($opt[$id]).'"/>'
							);
							$id = 'jaiminho_pass';
							$rows[] = array(
									"id" => $id,
									"label" => __('Senha','jaiminho'),
									"content" => '<input type="text" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars_decode($opt[$id]).'"/>'
							);
											
							$id = 'jaiminho_apikey';
							$rows[] = array(
									"id" => $id,
									"label" => __('APIKey','jaiminho'),
									"content" => '<input type="text" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars_decode($opt[$id]).'"/>'
							);
							
							
							$id = 'jaiminho_recreatecredentials';
							$content = '<input type="submit" name="'.$id.'" id="'.$id.'" value="Recriar credenciais"/>';
							$rows[] = array(
									"id" => $id,
									"label" => __('Recreate Jaiminho Credentials','jaiminho'),
									"content" => $content
							);
						}
						$table .= jaiminho_form_table($rows);
					
						jaiminho_postbox('jaiminho-config',__('Configurações para o plugin jaiminho','jaiminho'), $table.'<div class="submit"><input type="submit" class="button-primary" name="submit" value="'.__('Salvar as configurações do jaiminho','jaiminho').'" /></form></div>');
					?>
					</form>
				</div> <!-- meta-box-sortables -->
			</div> <!-- meta-box-holder -->
		</div> <!-- postbox-container -->
	</div>
	<?php	

}

/**
*
*
*/
function jaiminho_campaigncreated($data){
	$errors = array();
	
	$blog_id = $data['blog_id'];
	
	$mainSiteDomain = preg_replace('|https?://|', '', get_site_url());
	
	$id = preg_replace('|https?://|', '', $data['domain']);
	
	$id = str_replace('.'.$mainSiteDomain, '', $id);
	
	$opt = jaiminho_get_config();
	
	$opt_contatos = get_blog_option($blog_id,'webcontatos-config');
	
	$plan_capabilities = Capability::getByPlanId($data['plan_id']);
	
	$limite_emails = ((int)$plan_capabilities->send_messages->value * 1000);
	
	$output_headers = null;	
	
	try {
		$client=new SoapClient($opt['jaiminho_url'].'/james_bridge.php?wsdl', array('exceptions' => true));
		$defaultmailinglist_id = $client->__soapCall('createadmin', array('apikeymaster' => $opt['jaiminho_apikey'], 'name' => get_blog_option($blog_id,'blogname','Candidato '.$data['candidate_number']), 'username' => $id,'email' => 'contato@'.$mainSiteDomain, 'password' => $opt_contatos['webcontatos_pass'], 'plan' => $limite_emails) , array(), null, $output_headers);
	} catch (Exception $ex) {
		$errors[] = '('.$ex->faultcode.') '.$ex->faultstring.' - '.$ex->faultdetail;
	}
	
	$opt['jaiminho_user'] = $id;
	$opt['jaiminho_pass'] = $opt_contatos['webcontatos_pass'];
	
	switch_to_blog($blog_id);
		
		update_option('jaiminho-config', $opt);
		activate_plugin('WPJaiminho/WPJaiminho.php');
		if (count($errors) > 0)
		{ 
			update_option('jaiminho-error-log', $errors);
		}
		else
		{
			add_option( 'widget_jaiminho',
				array( 	'title' => 'Cadastre seu e-mail',
						'jaiminho_text' => 'Receba novidades da campanha',
						'jaiminho_id' => $defaultmailinglist_id
				)
			);
		}
		
		$sidebar_widget = get_option("sidebars_widgets");
		
		$sidebar_widget['sidebar-1'] = array_merge(array("jaiminho"),$sidebar_widget['sidebar-1']);
	
		update_option("sidebars_widgets",$sidebar_widget); 			
					
	restore_current_blog();
}

add_action('Campaign-created', 'jaiminho_campaigncreated', 15, 1);

// Fim Página de configuração

/*
* Mensagem de dashboard para notificar eventuais falhas de ativação dos plugins
*
*/
function jaiminho_displayMessageWidget(){
	_e('<div class="error">ATENÇÃO! Ocorreu um erro ao ativar o recurso de envio de emails.</div>');
	_e('Por favor, entre em contato com o suporte do Campanha Completa para resolver o problema no sistema de envio de emails.');
}

/*
* Setup the widget
*
*/
function jaiminho_setupMessageWidget(){
	if (get_option('jaiminho-error-log',false)) {
		wp_add_dashboard_widget('dashboard-message', __('Mensagem do administrador','WPWebContatos'), 'jaiminho_displayMessageWidget');	
	}
}
add_action('wp_dashboard_setup', 'jaiminho_setupMessageWidget' );

/**
*
*
*/
function jaiminho_campaignupdated($data){
	$plan_capabilities = Capability::getByPlanId($data['plan_id']);
		
	switch_to_blog($data['blog_id']);
	
	$opt = jaiminho_get_config();
	
	$limite_emails = ((int)$plan_capabilities->send_messages->value * 1000);
	
	try {
		$output_headers = null;	
		$client=new SoapClient($opt['jaiminho_url'].'/james_bridge.php?wsdl', array('exceptions' => true));
		$resultado = $client->__soapCall('changelimits', array('apikeymaster' => $opt['jaiminho_apikey'], 'plan' => $limite_emails, 'username' => $opt['jaiminho_user']) , array(), null, $output_headers);		
	} catch (Exception $ex) {
		wp_die('('.$ex->faultcode.') '.$ex->faultstring.' - '.$ex->detail);
	}
	
	restore_current_blog();
	
	return $resultado;
}

add_action('Campaign-updated', 'jaiminho_campaignupdated', 10, 1);

/**
*
*
*/
function jaiminho_GenerateIFrame($params){
	if(is_string($params))
	{
		$params = explode(',', $params);
	}
	if(!array_key_exists('page', $params) && isset($params[0])) // sem keys
	{
		$params_keys = array();
		foreach ( $params as $key => $value )
		{
			switch ( $key )
			{
				case 0:
					$params_keys['page'] = $value; 
				break;
				case 1:
					$params_keys['opcoes'] = $value; 
				break;
				case 2:
					$params_keys['width'] = $value; 
				break;
				case 3:
					$params_keys['height'] = $value; 
				break;
				case 4:
					$params_keys['scrollToX'] = $value; 
				break;
				case 5:
					$params_keys['scrollToY'] = $value; 
				break;
				case 6:
					$params_keys['auth'] = $value; 
				break;
			}
		}
		$params = $params_keys;
	}
	
    $opt = jaiminho_get_config();
   	
	if($opt['jaiminho_admin_url'] == false || $opt['jaiminho_url'] == false)
	{
		wp_die('É necessário a url do serviço jaiminho');
	}
	
	$authorize = "?apikeysession=".jaiminho_auth();
	
	$url = $opt['jaiminho_admin_url']."/".$params['page'].$authorize;
	
    $width = isset($params['width']) ? $params['width'] : $opt['width'];
    $height = isset($params['height']) ? $params['height'] : $opt['height'];
    $x = isset($params['scrollToX']) ? $params['scrollToX'] : 0;
    $y = isset($params['scrollToY']) ? $params['scrollToY'] : 0;

    if (strpos($width, 'px') === false and strpos($width, '%') === false)
    {
    	$width .= 'px'; 
    }
    if (strpos($height, 'px') === false and strpos($height, '%') === false)
    {
    	$height .= 'px'; 
    }
	
	if ($opt['jaiminho_scrollmethod'] == '0')
	{ 
		$scrollTo1 = '';
		$scrollTo2 = 'onload="scro11me(this)"></iframe>' .
					'<script type="text/javascript">' .
					'function scro11me(f){f.contentWindow.scrollTo(' . $x . ',' . $y . '); }' .
					'</script>';
	}
	else
	{		
		$scrollTo1 = '<div style="position:relative; overflow: hidden; width: ' . $width . '; height: ' . $height . '">' .
					'<div style="position:absolute; left:' . (-1 * $x) . 'px; top: ' . (-1 * $y) . 'px">';
		$scrollTo2 = '></iframe></div></div>';
		$w = (int) $width;
		$h = (int) $height;
		$width = str_replace($w, $w + $x, $width);
		$height = str_replace($h, $h + $x, $height);
	}
	
    return	$scrollTo1 .
			'<iframe class="jaiminho ' . $opt['jaiminho_class'] . '" src="' . $url . '" style="width: ' . 
			$width . '; height: ' . $height . ';' . $opt['jaiminho_style'] . ' " frameborder="' . 
			(int) $opt['jaiminho_border'] . '" scrolling="' . $opt['jaiminho_scrolling'] . '" ' . 
			$scrollTo2;
	
}

/**
*
*
*/
function jaiminho_auth(){
	$opt = jaiminho_get_config();
	$output_headers = null;

	try {
		$client=new SoapClient($opt['jaiminho_url'].'/james_bridge.php?wsdl',array('exceptions' => true));
		$auth = $client->__soapCall('auth', array('username' => $opt['jaiminho_user'], 'password' => $opt['jaiminho_pass']) , array(), null, $output_headers);	
	} catch (Exception $ex) {
		print_r($ex);
		die('<br/>Erro de login!');
	}

	return $auth;
}

/**
*
*
*/
function jaiminho_closesession(){
	echo '<html>' .
			'<body>'
	;
		
	echo jaiminho_GenerateIFrame(array('page' => 'logout.php', 'width' => 0, 'height' => 0, 'auth' => false ));
	
	echo 	'<script type="text/javascript">' .
			'	window.onload = function ()' .
			'		{' .
			'		window.location = "' . ((is_ssl()) ? 'https://' : 'http://') .$_SERVER['HTTP_HOST'].str_replace('?'.$_SERVER['QUERY_STRING'],'',$_SERVER['REQUEST_URI']).'";'.
			'		} 	' .
			'</script>' .
			'</body>' .
		'</html>';
	
	flush();
	exit();
}

add_action('wp_logout','jaiminho_closesession');


/**
 * Cria o formulário do Jaminho
 *
 * @param int $jaminho_id O ID da campanha
 * @param string $mensagem Opcional. A mensagem que será apresentada dentro do input de texto
 * @param string $origem Opcional. A origem
 */
function jaiminho( $jaiminho_id, $mensagem = '' ) { ?>

	<form id="jaiminho-form" method="post" action="<?php echo JAIMINHO_URL; ?>/jaiminho-request.php" >				
	    <input type="hidden" name="FormValue_MailListIDs[]" value="<?php echo $jaiminho_id; ?>" />					
		<?php if ( !empty( $mensagem ) ) : ?>
			<input type="text" class="jaiminho-text" name="FormValue_Email" value="<?php echo $mensagem; ?>" onblur="if (this.value == '') {this.value = '<?php echo $mensagem; ?>'};" onfocus="if(this.value == '<?php echo $mensagem; ?>') {this.value = ''};" />
		<?php else : ?>
			<input type="text" class="jaiminho-text" name="FormValue_Email" value="" />
		<?php endif;?>
							
		<?php
			$opt = get_option('jaiminho-config');
		?>
		<input type="hidden" name="request" value="<?php echo base64_encode($opt['jaiminho_url']); ?>"/>
										
		<input type="submit" class="jaiminho-submit" name="Subscribe" value="Cadastrar" />	
	</form>	
	<span id="jaiminho-message"></span>
	<div id="jaiminho-output"></div>
	<?php
	 	
}

/*
 * Inclui o widget após o carregamento dos plugins ativos
 */
function jaiminho_widget() {
	require_once( 'jaiminho-widget.php' );
}

add_action( 'plugins_loaded', 'jaiminho_widget' );


/*
 * Registra o widget
 */
function jaiminho_register_widget() {
	register_widget( 'Widget_Jaiminho' );
}
	
add_action( 'init', 'jaiminho_register_widget', 1 );

/**
*
*
*/
function jaiminho_criarlista(){
	echo jaiminho_GenerateIFrame('list_add.php');
}

/**
*
*
*/
function jaiminho_explorarlistas(){
	echo jaiminho_GenerateIFrame('list_browse.php');
}

/**
*
*
*/
function jaiminho_campospersonalizados(){
	echo jaiminho_GenerateIFrame('field_browse.php');
}

/**
*
*
*/
function jaiminho_importarmembros(){
	echo jaiminho_GenerateIFrame('member_import.php');
}

/**
*
*
*/
function jaiminho_campanha(){
	echo jaiminho_GenerateIFrame('campaign_new.php');
}

/**
*
*
*/
function jaiminho_remetente(){
	if ($_SERVER['REQUEST_METHOD']=='POST')
	{
		$opt = jaiminho_get_config();
		
		try {
			$output_headers = null;	
			$client=new SoapClient($opt['jaiminho_url'].'/james_bridge.php?wsdl', array('exceptions' => true));
			$alterado = $client->__soapCall('changename', array('apikeymaster' => $opt['jaiminho_apikey'], 'username' => $opt['jaiminho_user'], 'newname' => $_POST['jaiminho_nomeremetente']) , array(), null, $output_headers);		
		} catch (Exception $ex) {
			wp_die('('.$ex->faultcode.') '.$ex->faultstring.' - '.$ex->detail);
		}
		
		if ($alterado) 
			$mensagem = 'Nome de remetente alterado com sucesso!';
		else 
			$mensagem = 'Ocorreu um erro ao alterar o nome de remetente!';
		
	}

	$opt = jaiminho_get_config();
	
	try {
		$output_headers = null;	
		$client=new SoapClient($opt['jaiminho_url'].'/james_bridge.php?wsdl', array('exceptions' => true));
		$nome = $client->__soapCall('getdefaultname', array('apikeymaster' => $opt['jaiminho_apikey'], 'username' => $opt['jaiminho_user']) , array(), null, $output_headers);		
	} catch (Exception $ex) {
		wp_die('('.$ex->faultcode.') '.$ex->faultstring.' - '.$ex->detail);
	}
	
	?>
	<div class="wrap">
		<h2>Remetente</h2>
		<div class="postbox-container" style="width:80%;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
					<?php if ($mensagem) {?>
					<div id="message" class="updated">
					<?php echo $mensagem; ?>
					</div>
					<?php }?>
					<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" id="jaiminho-remetente" >
					<?php if (function_exists('wp_nonce_field')) 		
						wp_nonce_field('jaiminho-config');
						
						$rows = array();
						$id = 'jaiminho_nomeremetente';
						$rows[] = array(
								"id" => $id,
								"label" => __('Nome do remetente padrão','jaiminho'),
								"content" => '<input type="text" name="'.$id.'" id="'.$id.'" value="'.$nome.'"/>'
						);
						
						$table .= jaiminho_form_table($rows);
					
						jaiminho_postbox('jaiminho-remetente',__('Alterar o nome do remetente padrão','jaiminho'), $table.'<div class="submit"><input type="submit" class="button-primary" name="submit" value="'.__('Salvar as configurações','jaiminho').'" /></form></div>');
					?>
					</form>
				</div> <!-- meta-box-sortables -->
			</div> <!-- meta-box-holder -->
		</div> <!-- postbox-container -->
	</div>
<?php
}

/**
*
*
*/
function jaiminho_explorarcampanhas(){
	echo jaiminho_GenerateIFrame('campaign_browse.php');
}

/**
*
*
*/
function jaiminho_sms(){
	echo jaiminho_GenerateIFrame('plg0007_index.php');
}

/**
*
*
*/
function jaiminho_listarusuarios(){
	echo jaiminho_GenerateIFrame('settings_admins.php');
}

?>