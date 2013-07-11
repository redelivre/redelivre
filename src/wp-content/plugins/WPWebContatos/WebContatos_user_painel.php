<?php

function webcontatos_get_user_campos_form_registro_template()
{
	return array(
		'novo' => true, // Se é um campo novo ou já existe no perfil de usuário
		'id' => 'no_id',
		'nome' => __('Sem Nome', 'webcontatos'),
		'registro' => true, // Necessário no formulário de registro
		'obrigatorio' => true, // Obrigatório no formulário de registro
		'Informar' => __('Favor informar', 'webcontatos'),
		'funcao_registro' => false, // opcional
		'tipo_painel' => 'Texto', // Opcional: tipo do painel (Texto, DropDown ou CheckBox)
		'dados' => false, // Opcional: Função para pegar opções ou Array com dados 
		'dados_param' => false, // Opcional: Parametros para a função e dados array("Valor" => "Label", "Valor2" => "Label2")
		'funcao_painel' => false, // Opcional: Função que gera o html no painel
		'administracao' => false, // Opcional: Se é necessário ter poderes especiais para alterar esse campo
		'capability' => '' //Opcional: (se administracao == true) Qual a capability ou false para administradores
	);
}

function webcontatos_get_user_campos_form_registro()
{
	$campos_form_registro = array
	(
		/*
		 * Exemplo: 
		 * array(
		 * 		'novo' => true, // Se é um campo novo ou já existe no perfil de usuário
		 * 		'id' => 'user_pais',
		 * 		'nome' => __('País', 'webcontatos'),
		 * 		'registro' => true, // Necessário no formulário de registro
		 * 		'obrigatorio' => true, // Obrigatório no formulário de registro
		 * 		'Informar' => __('Favor informar o país do qual deseja participar', 'direitoamoradia'),
		 * 		'funcao_registro', => 'get_tax_regiao_paises' // opcional
		 * 		'tipo_painel' => 'DropDown', // Opcional: tipo do painel (Texto, DropDown ou CheckBox)
		 * 		'dados' => 'get_paises', // Opcional: Função para pegar opções
		 * 		'dados_param' => '', // Opcional: Parametros para a função e dados array("Valor" => "Label", "Valor2" => "Label2")
		 * 		'funcao_painel' => '' // Opcional: Função que gera o html no painel
		 * 	),
		 */
		
		array(
			'novo' => true,
			'id' => 'user_webcontatos',
			'nome' => __('Pode usar o WebContatos?', 'webcontatos'),
			'registro' => false,
			'tipo_painel' => 'CheckBox',
			'dados' => array("A" => "A", "D" => "D"),
			'administracao' => true,
			'capability' => array('create_users' ,'promote_users' )
		),
		array(
			'novo' => true,
			'id' => 'grupo_webcontatos',
			'nome' => __('Grupo de permissões?', 'webcontatos'),
			'registro' => false,
			'tipo_painel' => 'DropDown',
			'dados' => 'webcontatos_get_grupos',
			'administracao' => true,
			'capability' => array('create_users' ,'promote_users' )
		),
	);
	
	/*if(function_exists('get_user_campos_form_registro'))
	{
		$get_user_campos_form_registro = get_user_campos_form_registro();
		if(is_array($get_user_campos_form_registro))
		{
			//$campos_form_registro = array_merge($campos_form_registro, $get_user_campos_form_registro);
		}
	}*/
	$campo_template = webcontatos_get_user_campos_form_registro_template();
	for($i = 0; $i < count($campos_form_registro); $i++)
	{
		$campos_form_registro[$i] = array_merge($campo_template, $campos_form_registro[$i]);
	}
	
	return $campos_form_registro;
}

function webcontatos_campos_usuario_registro()
{
	foreach (webcontatos_get_user_campos_form_registro() as $campo)
	{
		if($campo['registro'] === true)
		{
			if(array_key_exists('funcao_registro', $campo) && function_exists($campo['funcao_registro']))
			{
				call_user_func($campo['funcao_registro'], $campo);
			}
			else 
			{
				?>
				<p>
					<label><?php echo $campo['nome'] ?><br />
					<input type="text" name="<?php echo $campo['id'] ?>" id="<?php echo $campo['id'] ?>" class="input" value="<?php echo esc_attr(stripslashes($_POST[$campo['id']])); ?>" size="20" tabindex="21" /></label>
				</p>
				<?php
			}
		}
	}
}
add_action('register_form','webcontatos_campos_usuario_registro');

function webcontatos_campos_usuario_registro_check($login, $email, $errors)
{
	foreach (webcontatos_get_user_campos_form_registro() as $campo)
	{
		if($campo['registro'] === true && $campo['obrigatorio'] === true && $_POST[$campo['id']] == '')
		{
			$errors->add('empty_'.$campo['id'], $campo['Informar']);
		}
	}
	return $errors;
}
add_action('register_post','webcontatos_campos_usuario_registro_check',10,3);

function webcontatos_register_extra_fields($user_id, $password="", $meta=array())
{
	$userdata['ID'] = $user_id;
	
	foreach (webcontatos_get_user_campos_form_registro() as $campo)
	{
		if(array_key_exists($campo['id'], $_POST))
		{
			if($campo[novo] == true)
			{
				update_usermeta( $user_id, $campo['id'], $_POST[$campo['id']]);
			}
			else 
			{
				$userdata[$campo['id']] = $_POST[$campo['id']];
			}
		}
	}
	
	wp_update_user($userdata);
	/*$order = get_user_option("meta-box-order_pauta", $user_id);
	$order['side'] = 'submitdiv,idiomadiv,tagsdiv-post_tag,temadiv,regiaodiv,pauta_meta';
	update_user_option($user_id, "meta-box-order_pauta", $order, true);*/
}
add_action('user_register', 'webcontatos_register_extra_fields');

function webcontatos_extra_profile_fields( $user ) 
{
	$campos = webcontatos_get_user_campos_form_registro();
	if($campos > 0)
	{
		?>
			<h3><?php _e('Webcontatos', 'webcontatos')?></h3>
		
			<table class="webcontatos-user-form-table">
		<?php
		foreach ($campos as $campo)
		{
			if($campo['novo'] == true)
			{ 
				$contactmethods[] = $campo['nome'];
				?>
					<tr>
						<td><label for="<?php echo $campo['id'] ?>"><?php echo $campo['nome'] ?></label></td>
						<td>
							<?php
							if(is_string($campo['funcao_painel']) && function_exists($campo['funcao_painel']) )
							{
								call_user_func($campo['funcao_painel'], $campo);
							}
							else 
							{
								switch($campo['tipo_painel'])
								{
									case 'DropDown':
										$valores = array();
										if(is_array($campo['dados']) && count($campo['dados']) > 0)
										{
											$valores = $campo['dados'];
										}
										elseif(is_string($campo['dados']) && function_exists($campo['dados']))
										{
											$param = $campo['dados_param'] ? $campo['dados_param'] : array();
											$ret = call_user_func($campo['dados'], $param);
											if(is_array($ret) && count($ret) > 0)
											{
												$valores = $ret;
											}
										}
										?>
										<select name="<?php echo $campo['id'] ?>" id="<?php echo $campo['id'] ?>" class="regular-dropdown" >
										<?php
										foreach ($valores as $valor => $desc)
										{
											?>
											<option value="<?php echo $valor; ?>" <?php echo strtolower(esc_attr( get_user_meta( $user->ID, $campo['id'], true ))) === strtolower($valor) ? 'selected="selected"' : ""; ?> class="regular-checkbox-value" ><?php echo $desc;?></option>
											<?php
										}
										?>
										</select><br />
										<?php
									break;
									case 'CheckBox':
										$valor = true;
										if(is_array($campo['dados']) && count($campo['dados']) > 0)
										{
											$keys = array_keys($campo['dados']);
											$valor = $keys[0];
										}
										elseif(is_string($campo['dados']) && function_exists($campo['dados']))
										{
											$param = $campo['dados_param'] ? $campo['dados_param'] : array();
											$ret = call_user_func($campo['dados'], $param);
											if(is_array($ret) && count($ret) > 0)
											{
												$keys = array_keys($ret);
												$valor = $keys[0] == 0 ? $ret[0] : $keys[0];
											}
											elseif (is_string($ret))
											{
												$valor = $ret;
											}
										}
										?>
										<input type="checkbox" name="<?php echo $campo['id'] ?>" id="<?php echo $campo['id'] ?>" value="<?php echo $valor; ?>" <?php echo strtolower(esc_attr( get_user_meta( $user->ID, $campo['id'], true ))) === strtolower($valor) ? 'checked="checked"' : ""; ?> class="regular-checkbox" /><br />
										
										<?php
									break;
									case 'Texto':
									default:
										?>
										<input type="text" name="<?php echo $campo['id'] ?>" id="<?php echo $campo['id'] ?>" value="<?php echo esc_attr( get_user_meta( $user->ID,$campo['id'], true ) ); ?>" class="regular-text" /><br />
										<?php
									break;
								}
							}
							?>
						</td>
					</tr>
				<?php
			}
		}
		?>
			</table>
		<?php
	}
}

add_action( 'show_user_profile', 'webcontatos_extra_profile_fields' );
add_action( 'edit_user_profile', 'webcontatos_extra_profile_fields' );

add_action('admin-init', 'webcontatos_extra_profile_fields');

function webcontatos_profile_update($user_id)
{
	/*$campos = webcontatos_get_user_campos_form_registro();
	foreach ($campos as $campo)
	{
		switch($campo['tipo_painel'])
		{
			case 'CheckBox':
				$valor = false;
				if(array_key_exists($campo['id'], $_POST))
				{
					$valor = $_POST[$campo['id']];
				}
				else 
				{
					if(is_array($campo['dados']) && count($campo['dados']) == 2)
					{
						$keys = array_keys($campo['dados']);
						$valor = $keys[1];
					}
					elseif(is_string($campo['dados']) && function_exists($campo['dados']))
					{
						$param = $campo['dados_param'] ? $campo['dados_param'] : array();
						$ret = call_user_func($campo['dados'], $param);
						if(is_array($ret) && count($ret) == 2)
						{
							$keys = array_keys($ret);
							$valor = $keys[0] == 0 ? $ret[1] : $keys[1];
						}
					}
				}
				update_user_meta($user_id, $campo['id'], $valor);
			break;
			default:
				if(array_key_exists($campo['id'], $_POST))
				{
					update_user_meta($user_id, $campo['id'], $_POST[$campo['id']]);
				}
			break;
		}
	}*/
	webcontatos_user_panel_update($user_id);
}

add_action('profile_update', 'webcontatos_profile_update');

function webcontatos_user_panel_add()
{
	$can = ( current_user_can( 'create_users' ) || current_user_can( 'promote_users' ) );
	
	if (strpos($_SERVER["REQUEST_URI"], '/wp-admin/user-new.php') !== false )
	{
		$grupos = webcontatos_get_grupos();
		$options = '';
		foreach ($grupos as $key => $grupo)
		{
			$options .= '<option class="regular-checkbox-value" value="'.$key.'">'.$grupo.'</option>';
		}
		$JS = '
		<script type="text/javascript">
		 		jQuery(".form-table").each(function () {jQuery("tr:last", this).after(\''.
		 		'<tr>'.
		 			'<td>'.
		 				'<label for="user_webcontatos">Pode usar o WebContatos?</label>'.
		 			'</td>'.
		 			'<td>'.
		 				'<input type="checkbox" '.($can?"":'disabled="disabled"').' class="regular-checkbox" value="A" id="user_webcontatos" name="user_webcontatos"><br>'.
		 			'</td>'.
		 		'</tr>'.
		 		'<tr>'.
	 				'<td>'.
	 					'<label for="grupo_webcontatos">Grupo de permissões?</label>'.
	 				'</td>'.
	 				'<td>'.
	 					'<select class="regular-dropdown" id="grupo_webcontatos" name="grupo_webcontatos" '.($can?"":'disabled="disabled"').' >'.
	 						$options.
	 					'</select><br>'.
	 				'</td>'.
	 			'</tr>'.
				'\');});
		</script>
		';
		echo $JS;
	}
}
add_action('admin_footer', 'webcontatos_user_panel_add');

function webcontatos_user_panel_post($location, $status)
{
	if ($_SERVER["REQUEST_URI"] == '/wp-admin/user-new.php' && current_user_can('promote_users'))
	{
		if ($_SERVER['REQUEST_METHOD']=='POST' && (
			strpos($location, 'update=add') !== false ||
			strpos($location, 'update=addnoconfirmation') !== false ||
			strpos($location, 'update=newuserconfimation') !== false
		))
		{
			//check_admin_referer( 'add-user', '_wpnonce_add-user' );
			
			$user = get_user_by('email',$_POST['email']);
			webcontatos_user_panel_update($user);
		}
	}
	return $location;
}

add_filter('wp_redirect','webcontatos_user_panel_post', 10, 2);

/**
 * 
 * @param WP_User $user
 */
function webcontatos_user_panel_update($user)
{
	if ( ! current_user_can( 'create_users' ) && ! current_user_can( 'promote_users' ) )
		return;
	$opt = webcontatos_get_config();
	if($user->user_login == $opt['webcontatos_user']) return;
	
	$perm = "D";
	if(array_key_exists('user_webcontatos', $_POST))
	{
		$perm = $_POST['user_webcontatos'];
	}
	if(array_key_exists('grupo_webcontatos', $_POST))
	{
		if(!is_object($user)) $user = get_user_by('id', $user);
		if($user !== false)
		{
			update_user_meta($user->ID, 'grupo_webcontatos', $_POST['grupo_webcontatos']);
			update_user_meta($user->ID, 'user_webcontatos', $perm);
			//update_user_meta($user->ID. 'webcontatos_pass', $_POST['webcontatos_pass']); // TODO Campo para Senha
			$pass = uniqid();
			update_user_meta($user->ID, 'webcontatos_pass', md5($pass));
			webcontatos_update_user($user, $pass, $perm, $_POST['grupo_webcontatos']);
		}
	}
}

?>
