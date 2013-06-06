<?php
function delibera_seguir($ID, $type ='seguir', $user_id = false, $ip = false)
{
	if($type == 'seguir')
	{
		$postID = $ID;
		$nseguir = intval(get_post_meta($postID, 'delibera_numero_seguir', true));
		$nseguir++;
		update_post_meta($postID, 'delibera_numero_seguir', $nseguir);
		$seguiram = get_post_meta($postID, 'delibera_seguiram', true);
		if(!is_array($seguiram)) $seguiram = array();
		$hora = time();
		if(!array_key_exists($hora, $seguiram)) $seguiram[$hora] = array();
		$seguiram[$hora][] = array('user' => $user_id, 'ip' => $ip);
		update_post_meta($postID, 'delibera_seguiram', $seguiram);
		return $nseguir;
	}
	elseif($type == 'nao_seguir')
	{
		$postID = $ID;
		$nseguir = intval(get_post_meta($postID, 'delibera_numero_seguir', true));
		$nseguir--;
		update_post_meta($postID, 'delibera_numero_seguir', $nseguir);
		$seguiram = get_post_meta($postID, 'delibera_seguiram', true);
		if(!is_array($seguiram)) $seguiram = array();
		$seguiram2 = array();
		foreach ($seguiram as $hora => $segs)
		{
			foreach ($segs as $user_ip)
			{
				if($user_id != $user_ip['user'])
				{
					if(!array_key_exists($hora, $seguiram2)) $seguiram2[$hora] = array();
					$seguiram2[$hora][] = $seguiram[$hora];
				}
			}
		}
		update_post_meta($postID, 'delibera_seguiram', $seguiram2);
		return $nseguir;
	}
}

function delibera_numero_seguir($ID)
{
	$postID = $ID;
	$nseguir = get_post_meta($postID, 'delibera_numero_seguir', true);
	return $nseguir;
}

function delibera_ja_seguiu($postID, $user_id, $ip)
{
	$seguiram = get_post_meta($postID, 'delibera_seguiram', true);
	if(!is_array($seguiram)) $seguiram = array();
	foreach ($seguiram as $hora => $seguiram)
	{
		foreach ($seguiram as $seguiu)
		{
			if(array_key_exists('user', $seguiu) && $user_id == $seguiu['user'])
			{
				return true;
			}
		}
	}
	return false;
}

/**
 * 
 * Gera código html/js para criação do botão seguir do sistema delibra
 * @param $ID post_ID ou comment_ID
 * @param $type 'pauta' ou 'comment'
 */
function delibera_gerar_seguir($ID)
{
	if(is_user_logged_in())
	{
		
		global $post;
		if(is_object($ID))
		{
			$ID = $ID->ID;
		}
		
		$type = "seguir";
		$user_id = get_current_user_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		$situacao = is_object($post) ? delibera_get_situacao($post->ID) : '';
		
		$seguir = false;
		if(!delibera_ja_seguiu($ID, $user_id, $ip) && (is_object($situacao) && $situacao->slug != 'relatoria'))
		{
			$seguir = true;
		}
	
		$html = '<div id="thebuttonSeguir'.$ID.'" class="delibera_seguir" ><span id="delibera-seguir-text-'.$ID.'" class="delibera_seguir_text">'.( $seguir ? __('Seguir','delibera') : __('Seguindo','delibera')).'</span>'.($seguir ? '' : '<span id="delibera-seguir-cancel-'.$ID.'" class="delibera_seguir_cancel">&nbsp;('.__('Cancelar', 'delibera').')').'</div>';
		
		$JS = '
			<script type="text/javascript">
				var type_'.$ID.' = "'.($seguir ? "seguir" : "nao_seguir").'";
			    jQuery(function ()
			    {
					jQuery("#thebuttonSeguir'.$ID.'")
			            .click(function ()
			            	{
								jQuery.post("'.home_url( "/" ).'/wp-admin/admin-ajax.php", 
								{
										action : "delibera_seguir" ,
									    seguir_id : "'.$ID.'",
									    type : type_'.$ID.',
									    user_id: "'.$user_id.'",
									    ip: "'.$ip.'"
								},
								function(response)
								{
									var html_resp = "<span id=\"delibera-seguir-text-'.$ID.'\" class=\"delibera_seguir_text\">";
									if(type_'.$ID.' == "seguir")
									{
										html_resp += "'.__('Seguindo','delibera').'";
										type_'.$ID.' = "nao_seguir";
										html_resp += "</span>";
										html_resp += "<span id=\"delibera-seguir-cancel-'.$ID.'\" class=\"delibera_seguir_cancel\">&nbsp;('.__('Cancelar', 'delibera').')";
										 
									}
									else
									{
										html_resp += "'.__('Seguir','delibera').'";
										type_'.$ID.' = "seguir";
										jQuery("#delibera-seguir-cancel-'.$ID.'").remove();
									}
									html_resp += "</span>";
									
									jQuery("#delibera-seguir-text-'.$ID.'").replaceWith( html_resp );
								}
							);
						});
			    });
			</script>
		';
		$JS = str_replace("\n", " ", $JS);
		$JS = str_replace("\r", " ", $JS);
		return $JS.$html;
	}
	else 
	{
		$html = '<div id="thebuttonSeguir'.$ID.'" class="delibera_seguir" ><a class="delibera-seguir-login" href="';
		$html .= wp_login_url( get_post_type() == "pauta" ? get_permalink() : delibera_get_comment_link());
		$html .= '" ><span class="delibera_seguir_text">'.__('Seguir','delibera').'</span></a></div>';
		return $html;
	}
}

function delibera_seguir_callback()
{
	if(array_key_exists('seguir_id', $_POST) && array_key_exists('type', $_POST))
	{
		echo delibera_seguir($_POST['seguir_id'], $_POST['type'], $_POST['user_id'], $_POST['ip']);
	}
	die();
}
add_action('wp_ajax_delibera_seguir', 'delibera_seguir_callback');
add_action('wp_ajax_nopriv_delibera_seguir', 'delibera_seguir_callback');

function delibera_get_quem_seguiu($ID, $return = 'array')
{
	$seguiram_hora = get_post_meta($ID, 'delibera_seguiram', true);
	
	if(!is_array($seguiram_hora)) $seguiram_hora = array();
	switch($return)
	{
		case 'string':
			$ret = '';
			foreach ($seguiram_hora as $hora => $seguiram)
			{
				foreach ($seguiram as $seguiu)
				{
					if (strlen($ret) > 0) $ret .= ", ";
					$ret .= (($seguiu['user'] == false || $seguiu['user'] == 0) ? $seguiu['ip'] : get_author_name($seguiu['user']));
				}
			}
			return $ret;
		break;
		case 'ids':
			$ids = array();
			foreach ($seguiram_hora as $hora => $seguiram)
			{
				foreach ($seguiram as $seguiu)
				{
					if($seguiu['user'] != false && $seguiu['user'] != 0)
					{
						$ids[] = $seguiu['user'];
					}
				}
			}
			return $ids;
		break;
		case 'array':
		default:
			return $seguiram_hora;
		break;
	}
	
}
?>