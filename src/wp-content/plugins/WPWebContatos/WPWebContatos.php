<?php
/*
Plugin Name: WebContatos
Plugin URI: http://www.ethymos.com.br
Description: O Plugin WebContatos integra a ferramente WebContatos ao Wordpress
Version: 0.6 beta
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

define('WebContatos_FOLDER', dirname(plugin_basename(__FILE__)));

$webcontatos_siteurl = get_option('siteurl');
if(is_ssl()) {
	$webcontatos_siteurl = str_replace("http://", "https://", $webcontatos_siteurl);
}

$webcontatos_plugin_url = WP_CONTENT_URL;
if(is_ssl()) {
  $plugin_url_parts = parse_url($webcontatos_plugin_url);
  $site_url_parts = parse_url($webcontatos_siteurl);
  if(stristr($plugin_url_parts['host'], $site_url_parts['host']) && stristr($site_url_parts['host'], $plugin_url_parts['host'])) {
		$webcontatos_plugin_url = str_replace("http://", "https://", $webcontatos_plugin_url);
	}
}

define('WebContatos_URL', $webcontatos_plugin_url.'/plugins/'.WebContatos_FOLDER);

// End Defines

// Parse shorttag

require_once __DIR__.DIRECTORY_SEPARATOR.'WebContatos_shortcodes.php';

// End Parse shorttag

/*
 * Rotinas de instalação do plugin
 */

function webcontatos_get_config()
{
	$opt = array();
	
	$opt['webcontatos_style'] = '';
	$opt['webcontatos_class'] = '';
	$opt['webcontatos_border'] = '';
	$opt['webcontatos_scrolling'] = 'no';
	$opt['webcontatos_scrollmethod'] = 1;
	$opt['webcontatos_url'] = 'https://tecnologiassociais.com.br/ethymos/WebContatos';
	$opt['webcontatos_user'] = 'admin';
	$opt['webcontatos_pass'] = 'admin';
	$opt['webcontatos_admin_url'] = 'https://tecnologiassociais.com.br/ethymos/WebContatos';
	$opt['webcontatos_admin_user'] = 'admin';
	$opt['webcontatos_admin_pass'] = 'admin';
	$opt['webcontatos_admin_key'] = '123456';
	$opt['webcontatos_error_log'] = '0';
	$opt['webcontatos_data_atualizacao'] = date("Y-m-d H:i:s", time());
	$opt['webcontatos_user_atualizacao'] = get_current_user_id();
	$opt['width'] = 900;
	$opt['height'] = 2500;
	
	$opt_conf = get_option('webcontatos-config');
	if(!is_array($opt_conf)) $opt_conf = array();
	$opt = array_merge($opt, $opt_conf);
	if(has_filter('webcontatos_get_config'))
	{
		$opt = apply_filters('webcontatos_get_config', $opt);
	}
	return $opt;
} 

function webcontatos_instalacao() 
{ 
	
}
register_activation_hook(__FILE__,'webcontatos_instalacao');


add_action('admin_init','webcontatos_instalacao');

// Inicialização do plugin

function WebContatos_init()
{
	$opt = webcontatos_get_config();
	$user = wp_get_current_user();
	if(is_super_admin() || get_user_meta($user->ID, 'user_webcontatos', true) == 'A' || $user->user_login == $opt['webcontatos_user'])
	{
		add_action('admin_menu', 'WebContatos_config_menu');
	}
}
add_action('init','WebContatos_init');

function webcontatos_scripts()
{
	wp_enqueue_script('webcontatos', WebContatos_URL.'/js/WPWebContatos.js');
}
add_action( 'wp_print_scripts', 'webcontatos_scripts' );

if(get_current_blog_id() != 1) //TODO Painel do usuário
{
	require_once __DIR__.DIRECTORY_SEPARATOR.'WebContatos_user_painel.php';
}

// Fim Inicialização do plugin

// Menu de configuração

function webcontatos_config_menu()
{
	$base_page = 'webcontatos-gerenciar';
	add_object_page( __('Webcontatos','webcontatos'), __('WebContatos','webcontatos'), 'manage_options', $base_page, array(), WebContatos_URL."/imagens/icon.png");
	add_submenu_page($base_page, __('Pesquisar Contatos','webcontatos'), __('Pesquisar Contatos','webcontatos'), 'manage_options', $base_page, 'webcontatos_GerenciarContato' );
	add_submenu_page($base_page, __('Criar Contato','webcontatos'), __('Criar Contato','webcontatos'), 'manage_options', 'webcontatos-criar', 'webcontatos_CriarContato' );
	add_submenu_page($base_page, __('Importar Contatos','webcontatos'), __('Importar Contatos','webcontatos'), 'manage_options', 'webcontatos-importar', 'webcontatos_ImportarContato' );
	add_submenu_page($base_page, __('Exportar Contatos','webcontatos'), __('Exportar Contatos','webcontatos'), 'manage_options', 'webcontatos-exportar', 'webcontatos_ExportarContato' );
	add_submenu_page($base_page, __('Configurações do Plugin','webcontatos'),__('Configurações do Plugin','webcontatos'), 'manage_options', 'webcontatos-config', 'webcontatos_conf_page');
}

/**
 * Create a form table from an array of rows
 */
function webcontatos_form_table($rows) {
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
 * Create a potbox widget
 */
function webcontatos_postbox($id, $title, $content) {
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
function webcontatos_conf_page()
{
	$mensagem = false;
	if ($_SERVER['REQUEST_METHOD']=='POST')
	{
		
		if (!current_user_can('manage_options')) wp_die(__('Você não pode editar as configurações do webcontatos.','webcontatos'));
		check_admin_referer('webcontatos-config');
		
		$opt = webcontatos_get_config();
		
		foreach ( array_keys($opt) as $option_name)
		{
			if (isset($_POST[$option_name]))
			{
				$opt[$option_name] = htmlspecialchars($_POST[$option_name]);
			}
		}

		if(
			isset($_POST["webcontatos_reinstall"]) &&
			$_POST['webcontatos_reinstall'] == 'S'
		)
		{
			try
			{
				include_once __DIR__.DIRECTORY_SEPARATOR.'webcontatos_reinstall.php';
			}
			catch (Exception $e)
			{
				wp_die($e->getMessage());
			}
		}
		
		$opt['webcontatos_data_atualizacao'] = date("Y-m-d H:i:s", time());
		$opt['webcontatos_user_atualizacao'] = get_current_user_id();
		
		if(has_filter('webcontatos-config'))
		{
			$opt = apply_filters('webcontatos-config', $opt);
		}
		
		if (update_option('webcontatos-config', $opt) || (isset($_POST["webcontatos_reinstall"]) && $_POST['webcontatos_reinstall'] == 'S'))
			$mensagem = __('Configurações salvas!','webcontatos');
		else
			$mensagem = __('Erro ao salvar as configurações. Verifique os valores inseridos e tente novamente!','webcontatos');
	}

	$opt = webcontatos_get_config();
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
					<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="webcontatos-config" >
					<?php if (function_exists('wp_nonce_field')) 		
						wp_nonce_field('webcontatos-config');
						$table = '
						<table class="form-table">
				        <tr valign="top">
					        <th scope="row">Mostrar Bordas</th>
					        <td>
								<input type="checkbox" name="webcontatos_border" value="1" '.(($opt['webcontatos_border'] == '1') ? 'checked="checked"' : '').' />
							</td>
				        </tr>
				        
				        <tr valign="top">
				        <th scope="row">Barra de rolagem</th>
				        <td>
							<select name="webcontatos_scrolling">
								<option value="auto" >Auto</option>
								<option value="yes" '.(($opt['webcontatos_scrolling'] == 'yes')? 'selected="selected"' : '').'>Sim</option>
								<option value="no" '.(($opt['webcontatos_scrolling'] == 'no')? 'selected="selected"' : '').'>Não</option>
							</select>
						</td>
				        </tr>
				        
				        <tr valign="top">
				        <th scope="row">Método de rolagem</th>
				        <td>
							<select name="webcontatos_scrollmethod">
								<option value="0">#1 (serviço no mesmo domínio)</option>
								<option value="1" '.(($opt['webcontatos_scrollmethod'] == '1') ? 'selected="selected"' : '' ).'>#2 (em outro domínio)</option>
							</select><br />
							<b>Info:</b>
							Método #2 esconde parcialmente as barras de rolagem, esse método deveria ser usado com junto com a opção de "Não" para barra de rolagem
						</td>
				        </tr>
				        
						<tr valign="top">
				        <th scope="row">Nome da classe do estilo </th>
				        <td><input type="text" name="webcontatos_class" style="width: 400px" value="'.$opt['webcontatos_class'].'" /></td>
				        </tr>
				         
				        <tr valign="top">
				        <th scope="row">Estilo CSS customizado</th>
				        <td>
							<textarea name="webcontatos_style" style="width: 400px; height: 70px">'.$opt['webcontatos_style'].'</textarea><br />
							<b>Info:</b>
							Não use "width" e "height" - esses valores devem ser especificados nas configurações<br />
						</td>
				        </tr>
				        
				        <tr valign="top">
				        <th scope="row">Url Base do serviço WebContatos</th>
				        <td><input type="text" name="webcontatos_url" style="width: 400px" value="'.$opt['webcontatos_url'].'" /></td>
				        </tr>	        	        
				        
				    </table>';
						$rows = array();
						$rows[] = array(
							"id" => "height",
							"label" => __('Altura','webcontatos'),
							"content" => '<input type="text" name="height" id="height" value="'.htmlspecialchars_decode($opt['height']).'"/>'
						);
						$rows[] = array(
							"id" => "width",
							"label" => __('Comprimento','webcontatos'),
							"content" => '<input type="text" name="width" id="width" value="'.htmlspecialchars_decode($opt['width']).'"/>'
						);
						if(is_super_admin())
						{
							$rows[] = array(
									"id" => "webcontatos_user",
									"label" => __('Usuário','webcontatos'),
									"content" => '<input type="text" name="webcontatos_user" id="webcontatos_user" value="'.htmlspecialchars_decode($opt['webcontatos_user']).'"/>'
							);
							$rows[] = array(
									"id" => "webcontatos_pass",
									"label" => __('Senha','webcontatos'),
									"content" => '<input type="text" name="webcontatos_pass" id="webcontatos_pass" value="'.htmlspecialchars_decode($opt['webcontatos_pass']).'"/>'
							);
							$rows[] = array(
									"id" => "webcontatos_admin_url",
									"label" => __('Endereço Administrativo','webcontatos'),
									"content" => '<input type="text" name="webcontatos_admin_url" id="webcontatos_admin_url" value="'.htmlspecialchars_decode($opt['webcontatos_admin_url']).'"/>'
							);
							$rows[] = array(
									"id" => "webcontatos_admin_user",
									"label" => __('Usuário Administrativo','webcontatos'),
									"content" => '<input type="text" name="webcontatos_admin_user" id="webcontatos_admin_user" value="'.htmlspecialchars_decode($opt['webcontatos_admin_user']).'"/>'
							);
							$id = 'webcontatos_admin_pass';
							$rows[] = array(
									"id" => $id,
									"label" => __('Senha Administrativa','webcontatos'),
									"content" => '<input type="password" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars_decode($opt[$id]).'"/>'
							);
							$id = 'webcontatos_admin_key';
							$rows[] = array(
									"id" => $id,
									"label" => __('Chave de Acesso','webcontatos'),
									"content" => '<input type="password" name="'.$id.'" id="'.$id.'" value="'.htmlspecialchars_decode($opt[$id]).'"/>'
							);
							$id = 'webcontatos_error_log';
							$rows[] = array(
									"id" => $id,
									"label" => __('Limpar Log de Erros','webcontatos'),
									"content" => '<input type="checkbox" name="'.$id.'" id="'.$id.'" value="0"/>'.
									($opt['webcontatos_error_log'] != false ? print_r($opt['webcontatos_error_log'], true) : '')
							);
						}
						$table .= webcontatos_form_table($rows);
					
						webcontatos_postbox('webcontatos-config',__('Configurações para o plugin webcontatos','webcontatos'), $table.'<div class="submit"><input type="submit" class="button-primary" name="submit" value="'.__('Salvar as configurações do webcontatos','webcontatos').'" /></form></div>');
					?>
					</form>
				</div> <!-- meta-box-sortables -->
			</div> <!-- meta-box-holder -->
		</div> <!-- postbox-container -->
	</div>
	<?php	

}

// Fim Página de configuração

// Conteúdo

function webcontatos_Campaign_created($data)
{
	$mainSiteDomain = preg_replace('|https?://|', '', get_site_url());
	
	$id = preg_replace('|https?://|', '', $data['domain']);
	
	$id = str_replace('.'.$mainSiteDomain, '', $id);
	
	$current_user = wp_get_current_user();
	
	$contatoscc_user = $current_user->user_login;
	$contatoscc_pass = uniqid();
	
	$opt = webcontatos_get_config();
	
	$blog_id = $data['blog_id'];
	switch_to_blog($blog_id);
		$webcontatos_options = get_option('webcontatos-config', array());
	restore_current_blog();
	
	if(array_key_exists('webcontatos_pass', $webcontatos_options) && $webcontatos_options['webcontatos_pass'] != 'admin')
	{
		$contatoscc_pass = $webcontatos_options['webcontatos_pass'];
	}
	
	$errors = array();
	
	try {
		$client=new SoapClient($opt['webcontatos_admin_url'].'/index.php?servicos=ServicoContatos.wsdl', array('exceptions' => true));
		$auth = $client->__soapCall('doLogin', array('nome' => $opt['webcontatos_admin_user'], 'password' => $opt['webcontatos_admin_pass']) , array(), null, $output_headers);
		$client->__soapCall('CriarSite', array('id'=>$id, 'user' => $contatoscc_user, 'pass' => $contatoscc_pass) , array(), null, $output_headers);	
	} catch (Exception $ex) {
		$errors[] = '('.$ex->faultcode.') '.$ex->faultstring.' - '.$ex->detail;
	}
	
	$webcontatos_options = webcontatos_get_config();
	$url = $opt['webcontatos_url'];
	
	$url = substr($url, 0, strrpos($url, 'WebContatos') -1);
	$url = substr($url, 0, strrpos($url, '/'));
	$url .= "/{$id}/WebContatos";
	
	$webcontatos_options['webcontatos_user'] = $contatoscc_user;
	$webcontatos_options['webcontatos_pass'] = md5($contatoscc_pass);
	//get_user_meta();
	$webcontatos_options['webcontatos_url'] = $url;
	$webcontatos_options['webcontatos_admin_url'] = '';
	$webcontatos_options['webcontatos_admin_user'] = '';
	$webcontatos_options['webcontatos_admin_pass'] = '';
	$webcontatos_options['webcontatos_admin_key'] = '';

	switch_to_blog($blog_id);
	{
		if (count($errors) > 0) {
			$webcontatos_options['webcontatos_error_log'] = $errors;
		}
		
		update_option('webcontatos-config', $webcontatos_options, false);
		activate_plugin('WPWebContatos/WPWebContatos.php');
		$user = wp_get_current_user();
		update_user_meta($user->ID, 'webcontatos_user', $contatoscc_user);
		update_user_meta($user->ID, 'webcontatos_pass', md5($contatoscc_pass));
		update_user_meta($user->ID, 'grupo_webcontatos', 6);
		update_user_meta($user->ID, 'user_webcontatos', 'A');
	}	
	restore_current_blog();
}

add_action('Campaign-created', 'webcontatos_Campaign_created', 10, 1);

// Mensagem de dashboard para notificar eventuais falhas de ativação dos plugins
function webcontatos_displayMessageWidget(){

	$opt = webcontatos_get_config();
	_e('<div class="error">ATENÇÃO! Ocorreu um erro ao ativar o recurso de gerenciamento de contatos.</div> ');
	echo __('Por favor, entre em contato com o suporte do ').get_bloginfo('name').__(' para resolver o problema no sistema de gerenaciamento de contatos.');
}

//Setup the widget
function webcontatos_setupMessageWidget()
{
	$opt = webcontatos_get_config();
	$webcontatos_error_log = $opt['webcontatos_error_log'];
	if ( $webcontatos_error_log != false ) {
		wp_add_dashboard_widget('dashboard-message', __('Mensagem do administrador','WPWebContatos'), 'webcontatos_displayMessageWidget');	
	}
}
add_action('wp_dashboard_setup', 'webcontatos_setupMessageWidget' );

function webcontatos_GenerateIFrame($params)
{
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
			}
		}
		$params = $params_keys;
	}
	$service = 'page';
	
	if(array_key_exists('service', $params))
	{
		$service = $params['service'];
	}
	
    $opt = webcontatos_get_config();
    
    $redirect = "&redirect={$service}__{$params['page']}";
    
    if($service == 'form')
    {
    	$url = "/index.php?{$service}={$params['page']}&layoutTop=false".(isset($params['opcoes']) ? "&{$params['opcoes']}" : '');
    }
    else 
	{
   		$auth = webcontatos_Auth();
   		if($auth == false || $auth == 'false')
   		{
   			$error = __('Não foi possível fazer o login automático no sistema WebContatos, favor entrar em contato com o suporte técnico', 'WebContatos');
   			webcontatos_report_error($error);
   			return $error;
   		}
		$url = "/index.php?$auth&layoutTop=false".(isset($params['opcoes']) ? "&{$params['opcoes']}" : '').$redirect;
	}
	
	$opt_url = $opt['webcontatos_url'];
	
	if($opt_url != false)
	{
		$url = $opt_url.$url; 
	}
	else
	{
		$error = __('É necessário a url do serviço WebContatos', 'WebContatos');
		webcontatos_report_error($error);
		return $error;
	}
	
    $width = isset($params['width']) ? $params['width'] : $params['page'] == 'Display' ? '100%' : $opt['width']; 
    $height = isset($params['height']) ? $params['height'] : $opt['height'];
    $x = isset($params['scrollToX']) ? $params['scrollToX'] : 0;
    $y = isset($params['scrollToY']) ? $params['scrollToY'] : 0;

    if ( strlen($width) > 0 && (strpos($width, 'px') === false && strpos($width, '%') === false))
    {
    	$width .= 'px'; 
    }
    if (strpos($height, 'px') === false and strpos($height, '%') === false)
    {
    	$height .= 'px'; 
    }
	
	if ($opt['webcontatos_scrollmethod'] == '0')
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
					'<div style="left:' . (-1 * $x) . 'px; top: ' . (-1 * $y) . 'px">';
		$scrollTo2 = '></iframe></div></div>';
		$w = (int) $width;
		$h = (int) $height;
		$width = str_replace($w, $w + $x, $width);
		$height = str_replace($h, $h + $x, $height);
	}
	
    return	$scrollTo1 .
			'<iframe class="webcontatos ' . $opt['webcontatos_class'] . '" src="' . $url . '" style="width: ' . 
			$width . '; height: ' . $height . ';' . $opt['webcontatos_style'] . ' " frameborder="' . 
			(int) $opt['webcontatos_border'] . '" scrolling="' . $opt['webcontatos_scrolling'] . '" ' . 
			$scrollTo2;
	
}

function webcontatos_report_error($error, $opt = null)
{
	if(is_null($opt))
	{
		$opt = webcontatos_get_config();
	}
	
	$opt['webcontatos_error_log'] = $error;
	update_option('webcontatos-config', $opt);
	
}

function webcontatos_Auth()
{
	$opt = webcontatos_get_config();

	$client=new SoapClient($opt['webcontatos_url'].'/index.php?servicos=ServicoContatos.wsdl');
	$user = wp_get_current_user();
	
	if(is_super_admin() || $user->user_login == $opt['webcontatos_user'])
	{
		$auth = $client->__soapCall('doLogin', array('nome' => $opt['webcontatos_user'], 'password' => $opt['webcontatos_pass']) , array(), null, $output_headers);
	}
	else
	{
		$auth = $client->__soapCall('doLogin', array('nome' => $user->user_login, 'password' => get_user_meta($user->ID, 'webcontatos_pass', true)) , array(), null, $output_headers);
	}

	if(!$auth)
	{
		return false;
	}
	$key = $client->__soapCall('AuthKey', array() , array(), null, $output_headers);

	$url = 'page=AuthByKey&authkey='.$key;

	return $url;
}

function webcontatos_GerenciarContato()
{
	echo webcontatos_GenerateIFrame('Gerenciar/GerenciarContatos');
}

function webcontatos_CriarContato()
{
	echo webcontatos_GenerateIFrame('Criar/NovoContato');
}

function webcontatos_ImportarContato()
{
	echo webcontatos_GenerateIFrame('Arquivos/ImportarContatos');
}

function webcontatos_ExportarContato()
{
	echo webcontatos_GenerateIFrame('Arquivos/ExportarContatos');
}

function webcontatos_enc_pass($opt)
{
	if(array_key_exists('webcontatos_pass', $_POST) && $opt['webcontatos_pass'] != md5($_POST['webcontatos_pass']) && $opt['webcontatos_pass'] != $_POST['webcontatos_pass'])
	{
		$opt['webcontatos_pass'] = md5($_POST['webcontatos_pass']);
	}
	return $opt;
}
add_filter('webcontatos-config', 'webcontatos_enc_pass');

function webcontatos_get_grupos()
{
	$opt = webcontatos_get_config();
	
	$client=new SoapClient($opt['webcontatos_url'].'/index.php?servicos=ServicoContatos.wsdl');
	$auth = $client->__soapCall('doLogin', array('nome' => $opt['webcontatos_user'], 'password' => $opt['webcontatos_pass']) , array(), null, $output_headers);
	
	if(!$auth)
	{
		return array();
	}
	$grupos = $client->__soapCall('getGrupos', array() , array(), null, $output_headers);
	
	if(is_array($grupos))
	{
		return $grupos;
	}
	
	return array();
}

/**
 * Atualiza dados do usuário no WebContatos
 * @param WP_User $user
 * @param unknown $permissao
 * @param unknown $grupo
 * @return boolean
 */
function webcontatos_update_user($user, $pass, $permissao, $grupo)
{
	try
	{
		$opt = webcontatos_get_config();
		
		$client=new SoapClient($opt['webcontatos_url'].'/index.php?servicos=ServicoContatos.wsdl');
		$auth = $client->__soapCall('doLogin', array('nome' => $opt['webcontatos_user'], 'password' => $opt['webcontatos_pass']) , array(), null, $output_headers);
		
		if(!$auth)
		{
			return false;
		}
		
		$ret = $client->__soapCall('UpdateUsuario', array('nome'=> $user->user_login, 'pass' => $pass, 'grupo' => $grupo, 'permissao' => $permissao, 'nomeCompleto' => ($user->first_name.' '.$user->last_name)) , array(), null, $output_headers);
	}
	catch (Exception $e)
	{
		webcontatos_report_error(print_r($e, true), $opt);
	}
	
}

/**
 * 
 */
function webcontatos_recriar()
{
	if(is_super_admin())
	{
		$data = array();
		$campaign = Campaign::getByBlogId(get_current_blog_id());
		$data['domain'] =  $campaign->domain;
		$data['blog_id'] = get_current_blog_id();
		
		webcontatos_Campaign_created($data);
	}
	
}

?>
