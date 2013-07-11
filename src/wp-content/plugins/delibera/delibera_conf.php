<?php

/**
 * Retorna todas as configurações do delibera
 * salvas no banco. Quando não houver um valor 
 * salvo no banco para determinada opções retorna o
 * valor padrão.
 * 
 * @return array
 */
function delibera_get_config() {
    $opt = array();
    
    $opt = apply_filters('delibera_get_config', $opt);
    
    $opt_conf = get_option('delibera-config', array());

    $opt = array_merge($opt, $opt_conf);
    
    return $opt;
}

function delibera_get_main_config($config = array()) {
    $opt = array();
    $opt['minimo_validacao'] = '10';
    $opt['dias_validacao'] = '5';
    $opt['dias_discussao'] = '5';
    $opt['dias_votacao'] = '5';
    $opt['representante_define_prazos'] = 'N';
    $opt['dias_novo_prazo'] = '2';
    $opt['validacao'] = 'S';
    $opt['dias_relatoria'] = '2';
    $opt['relatoria'] = 'N';
    $opt['eleicao_relator'] = 'N';
    $opt['dias_votacao_relator'] = '2';
    $opt['limitar_tamanho_comentario'] = 'N';
    $opt['numero_max_palavras_comentario'] = '50';
    $opt['plan_restriction'] = 'N';
    $opt['cabecalho_arquivo'] = __( 'Bem-vindo a plataforma de debate do ', 'delibera' ).get_bloginfo('name');
    
    return array_merge($opt, $config);
}
add_filter('delibera_get_config', 'delibera_get_main_config');

/**
 * Gera o HTML da página de configuração
 * do Delibera
 * 
 * @return null
 */
function delibera_conf_page()
{
    $mensagem = '';

    if ($_SERVER['REQUEST_METHOD']=='POST') {
        $opt = delibera_get_config();
        
        if (!current_user_can('manage_options')) {
            die(__('Você não pode editar as configurações do delibera.','delibera'));
        }
        
        check_admin_referer('delibera-config');
        	
        foreach (array_keys(delibera_get_main_config()) as $option_name) {
            if (isset($_POST[$option_name])) {
                $opt[$option_name] = htmlspecialchars($_POST[$option_name]);
            } else {
                $opt[$option_name] = "N";
            }
        }

        if (isset($_POST["delibera_reinstall"]) && $_POST['delibera_reinstall'] == 'S') {
            try {
                include_once __DIR__.DIRECTORY_SEPARATOR.'delibera_reinstall.php';
            } catch (Exception $e) {
                wp_die($e->getMessage());
            }
        }

        if (update_option('delibera-config', $opt) || (isset($_POST["delibera_reinstall"]) && $_POST['delibera_reinstall'] == 'S'))
            $mensagem = __('Configurações salvas!','delibera');
        else
            $mensagem = __('Erro ao salvar as configurações. Verifique os valores inseridos e tente novamente!','delibera');
    }

    $opt = delibera_get_config();
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
			<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" id="delibera-config" >
			<?php if (function_exists('wp_nonce_field')) 		
					wp_nonce_field('delibera-config');
						
				$rows = array();
				if(is_multisite() && get_current_blog_id() == 1)
				{
					$rows[] = array(
						"id" => "plan_restriction",
						"label" => __('Sistema de planos de pagamento ativo?', 'delibera'),
						"content" => '<input type="checkbox" name="plan_restriction" id="plan_restriction" value="S" '. ( htmlspecialchars_decode($opt['plan_restriction']) == "S" ? "checked='checked'" : "" ).'/>',
					);
				}
				$rows[] = array(
					"id" => "representante_define_prazos",
					"label" => __('Representante define prazos?', 'delibera'),
					"content" => '<input type="checkbox" name="representante_define_prazos" id="representante_define_prazos" value="S" '. ( htmlspecialchars_decode($opt['representante_define_prazos']) == "S" ? "checked='checked'" : "" ).'/>',
				);
				$rows[] = array(
					"id" => "validacao",
					"label" => __('É necessário validação das pautas?', 'delibera'),
					"content" => '<input type="checkbox" name="validacao" id="validacao" value="S" '.(htmlspecialchars_decode($opt['validacao']) == 'S' ? 'checked="checked"' : '').' />'
				);
				$rows[] = array(
					"id" => "minimo_validacao",
					"label" => __('Mínimo de adesões para pauta:', 'delibera'),
					"content" => '<input type="text" name="minimo_validacao" id="minimo_validacao" value="'.htmlspecialchars_decode($opt['minimo_validacao']).'"/>'
				);
				
				$rows[] = array(
					"id" => "dias_validacao",
					"label" => __('Dias para validação da pauta:', 'delibera'),
					"content" => '<input type="text" name="dias_validacao" id="dias_validacao" value="'.htmlspecialchars_decode($opt['dias_validacao']).'"/>'
				);
				
				$rows[] = array(
					"id" => "dias_discussao",
					"label" => __('Dias para discussão da pauta:', 'delibera'),
					"content" => '<input type="text" name="dias_discussao" id="dias_discussao" value="'.htmlspecialchars_decode($opt['dias_discussao']).'"/>'
				);
				
				$rows[] = array(
					"id" => "dias_votacao",
					"label" => __('Dias para votação de encaminhamentos:', 'delibera'),
					"content" => '<input type="text" name="dias_votacao" id="dias_votacao" value="'.htmlspecialchars_decode($opt['dias_votacao']).'"/>'
				);
				
				$rows[] = array(
					"id" => "dias_novo_prazo",
					"label" => __('Dias para novo prazo:', 'delibera'),
					"content" => '<input type="text" name="dias_novo_prazo" id="dias_novo_prazo" value="'.htmlspecialchars_decode($opt['dias_novo_prazo']).'"/>'
				);
				$rows[] = array(
					"id" => "relatoria",
					"label" => __('Necessário relatoria da discussão das pautas?', 'delibera'),
					"content" => '<input type="checkbox" id="relatoria" name="relatoria" value="S" '.(htmlspecialchars_decode($opt['relatoria']) == 'S' ? 'checked="checked"' : '').' />'
				);
				$rows[] = array(
					"id" => "dias_relatoria",
					"label" => __('Prazo para relatoria:', 'delibera'),
					"content" => '<input type="text" name="dias_relatoria" id="dias_relatoria" value="'.htmlspecialchars_decode($opt['dias_relatoria']).'"/>'
				);
				/*$rows[] = array(
					"id" => "eleicao_relator",
					"label" => __('Necessário eleição de relator?', 'delibera'),
					"content" => '<input type="checkbox" name="eleicao_relator" value="S" '.(htmlspecialchars_decode($opt['eleicao_relator']) == 'S' ? 'checked="checked"' : '').' />'
				);
				$rows[] = array(
					"id" => "dias_votacao_relator",
					"label" => __('Prazo para eleição de relator:', 'delibera'),
					"content" => '<input type="text" name="dias_votacao_relator" id="dias_votacao_relator" value="'.htmlspecialchars_decode($opt['dias_votacao_relator']).'"/>'
				);*/
				$rows[] = array(
					"id" => "limitar_tamanho_comentario",
					"label" => __('Necessário limitar o tamanho do comentário visível?', 'delibera'),
					"content" => '<input type="checkbox" name="limitar_tamanho_comentario" id="limitar_tamanho_comentario" value="S" '.(htmlspecialchars_decode($opt['limitar_tamanho_comentario']) == 'S' ? 'checked="checked"' : '').' />'
				);
				$rows[] = array(
					"id" => "numero_max_palavras_comentario",
					"label" => __('Número máximo de caracteres por comentário:', 'delibera'),
					"content" => '<input type="text" name="numero_max_palavras_comentario" id="numero_max_palavras_comentario" value="'.htmlspecialchars_decode($opt['numero_max_palavras_comentario']).'"/>'
				);
				$rows[] = array(
					"id" => "cabecalho_arquivo",
					"label" => __('Título da página de listagem de pautas e da página de uma pauta:', 'delibera'),
					"content" => '<input type="text" name="cabecalho_arquivo" id="cabecalho_arquivo" value="'.htmlspecialchars_decode($opt['cabecalho_arquivo']).'"/>'
				);
				$table = delibera_form_table($rows);
				if(has_filter('delibera_config_form'))
				{
					$table = apply_filters('delibera_config_form', $table, $opt);
				}
				echo $table.'<div class="submit"><input type="submit" class="button-primary" name="submit" value="'.__('Save Changes').'" /></form></div>';
			?>
				
				</form>
			</div> <!-- meta-box-sortables -->
		</div> <!-- meta-box-holder -->
	</div> <!-- postbox-container -->

	<?php do_action('delibera_config_page_extra');?>
	
</div>

<?php	

}
