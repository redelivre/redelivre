<?php
	/*
    	Plugin Name: Mobilize Contribua
	    Plugin URI: http://www.ethymos.com.br
	    Description: 
	    Author: Ethymos
	    Version: 1.0
	    Author URI: 
	    Text Domain:
	    Domain Path:
	 */

	define('MOIP_PATH_FILE', __FILE__);

	class Mobilize_moip
	{	
		const TEXTO_DESCRITIVO_PADRAO_MOIP = 'Selecine ou insira um valor para sua contribuição. Ao clicar no botão, você será direcionado à área de pagamento.';
		
		public static function getOption($index) {
			$options = get_option('mobilize_options');
			return isset($options[$index]) ? $options[$index] : NULL;
		}

		public function init(){
			require_once dirname(MOIP_PATH_FILE).'/template/index.php';
		}

		public static function save_mobilize_moip_settings() {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$mm_checkbox_status = trim(mysql_real_escape_string($_POST['mm_checkbox_status']));

				$_POST['mm_checkbox_contribuicaofixa1'] = isset($_POST['mm_checkbox_contribuicaofixa1']) ? $_POST['mm_checkbox_contribuicaofixa1'] : '';
				$_POST['mm_checkbox_contribuicaofixa2'] = isset($_POST['mm_checkbox_contribuicaofixa2']) ? $_POST['mm_checkbox_contribuicaofixa2'] : '';
				$_POST['mm_checkbox_contribuicaofixa3'] = isset($_POST['mm_checkbox_contribuicaofixa3']) ? $_POST['mm_checkbox_contribuicaofixa3'] : '';
				$_POST['mm_checkbox_contribuicaolivre'] = isset($_POST['mm_checkbox_contribuicaolivre']) ? $_POST['mm_checkbox_contribuicaolivre'] : '';

				$_POST['mm_tipo_contribuicaofixa1'] = isset($_POST['mm_tipo_contribuicaofixa1']) ? $_POST['mm_tipo_contribuicaofixa1'] : '';
				$_POST['mm_tipo_contribuicaofixa2'] = isset($_POST['mm_tipo_contribuicaofixa2']) ? $_POST['mm_tipo_contribuicaofixa2'] : '';
				$_POST['mm_tipo_contribuicaofixa3'] = isset($_POST['mm_tipo_contribuicaofixa3']) ? $_POST['mm_tipo_contribuicaofixa3'] : '';

				$mm_descricao                   = strip_tags(trim($_POST['mm_descricao']));
				$mm_carteira                    = strip_tags(trim($_POST['mm_carteira']));
				$mm_url_retorno                 = strip_tags(trim($_POST['mm_url_retorno']));
				$mm_color_institucional         = strip_tags(trim($_POST['mm_color_institucional']));
				$mm_color_projeto               = strip_tags(trim($_POST['mm_color_projeto']));
				$mm_color_outros                = strip_tags(trim($_POST['mm_color_outros']));
				$mm_checkbox_contribuicaofixa1  = strip_tags(trim($_POST['mm_checkbox_contribuicaofixa1']));
				$mm_tipo_contribuicaofixa1      = strip_tags(trim($_POST['mm_tipo_contribuicaofixa1']));
				$mm_descricao_contribuicaofixa1 = strip_tags(trim($_POST['mm_descricao_contribuicaofixa1']));
				$mm_valor_contribuicaofixa1     = strip_tags(trim($_POST['mm_valor_contribuicaofixa1']));
				$mm_checkbox_contribuicaofixa2  = strip_tags(trim($_POST['mm_checkbox_contribuicaofixa2']));
				$mm_tipo_contribuicaofixa2      = strip_tags(trim($_POST['mm_tipo_contribuicaofixa2']));
				$mm_descricao_contribuicaofixa2 = strip_tags(trim($_POST['mm_descricao_contribuicaofixa2']));
				$mm_valor_contribuicaofixa2     = strip_tags(trim($_POST['mm_valor_contribuicaofixa2']));
				$mm_checkbox_contribuicaofixa3  = strip_tags(trim($_POST['mm_checkbox_contribuicaofixa3']));
				$mm_tipo_contribuicaofixa3      = strip_tags(trim($_POST['mm_tipo_contribuicaofixa3']));
				$mm_descricao_contribuicaofixa3 = strip_tags(trim($_POST['mm_descricao_contribuicaofixa3']));
				$mm_valor_contribuicaofixa3     = strip_tags(trim($_POST['mm_valor_contribuicaofixa3']));
				$mm_checkbox_contribuicaolivre  = strip_tags(trim($_POST['mm_checkbox_contribuicaolivre']));
				$mm_descricao_contribuicaolivre = strip_tags(trim($_POST['mm_descricao_contribuicaolivre']));

				update_option('mobilize_options', array(
					'mm_checkbox_status'             => $mm_checkbox_status,
					'mm_descricao'                   => $mm_descricao,
					'mm_carteira'                    => $mm_carteira,
					'mm_url_retorno'                 => $mm_url_retorno,
					'mm_color_institucional'         => $mm_color_institucional,
					'mm_color_projeto'               => $mm_color_projeto,
					'mm_color_outros'                => $mm_color_outros,
					'mm_checkbox_contribuicaofixa1'  => $mm_checkbox_contribuicaofixa1,
					'mm_tipo_contribuicaofixa1'      => $mm_tipo_contribuicaofixa1,
					'mm_descricao_contribuicaofixa1' => $mm_descricao_contribuicaofixa1,
					'mm_valor_contribuicaofixa1'     => $mm_valor_contribuicaofixa1,
					'mm_checkbox_contribuicaofixa2'  => $mm_checkbox_contribuicaofixa2,
					'mm_tipo_contribuicaofixa2'      => $mm_tipo_contribuicaofixa2,
					'mm_descricao_contribuicaofixa2' => $mm_descricao_contribuicaofixa2,
					'mm_valor_contribuicaofixa2'     => $mm_valor_contribuicaofixa2,
					'mm_checkbox_contribuicaofixa3'  => $mm_checkbox_contribuicaofixa3,
					'mm_tipo_contribuicaofixa3'      => $mm_tipo_contribuicaofixa3,
					'mm_descricao_contribuicaofixa3' => $mm_descricao_contribuicaofixa3,
					'mm_valor_contribuicaofixa3'     => $mm_valor_contribuicaofixa3,
					'mm_checkbox_contribuicaolivre'  => $mm_checkbox_contribuicaolivre,
					'mm_descricao_contribuicaolivre' => $mm_descricao_contribuicaolivre
				));
			}
		}
	}

	$m_options = get_option('mobilize_options');
	add_action('mobilize-admin-page', array('Mobilize_moip', 'init'));
?>