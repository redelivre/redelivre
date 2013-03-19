<?php

function delibera_discordar_comment_meta($comment_id)
{
	$ndiscordaram = get_comment_meta($comment_id, "delibera_numero_discordar", true);
	if($ndiscordaram == false || $ndiscordaram == "")
	{
		$ndiscordaram = array();
		add_comment_meta($comment_id, 'delibera_numero_discordar', $ndiscordaram, true);
	}
	
	$discordaram = get_comment_meta($comment_id, "delibera_discordaram", true);
	if($discordaram == false || $discordaram == "")
	{
		$discordaram = array();
		add_comment_meta($comment_id, 'delibera_discordaram', $discordaram, true);
	}
}

function delibera_discordar($ID, $type ='pauta', $user_id = false, $ip = false)
{
	
	if(!delibera_ja_discordou($ID, $user_id, $ip, $type) && !(function_exists('delibera_ja_curtiu') && delibera_ja_curtiu($ID, $user_id, $ip, $type)) )
	{
		if($type == 'pauta')
		{
			$postID = $ID;
			$ndiscordar = get_post_meta($postID, 'delibera_numero_discordar', true);
			$ndiscordar++;
			update_post_meta($postID, 'delibera_numero_discordar', $ndiscordar);
			$discordaram = get_post_meta($postID, 'delibera_discordaram', true);
			if(!is_array($discordaram)) $discordaram = array();
			$hora = time();
			if(!array_key_exists($hora, $discordaram)) $discordaram[$hora] = array();
			$discordaram[$hora][] = array('user' => $user_id, 'ip' => $ip);
			update_post_meta($postID, 'delibera_discordaram', $discordaram);
			return $ndiscordar;
		}
		elseif($type == 'comment')
		{
			$comment_id = $ID;
			$ndiscordar = intval(get_comment_meta($comment_id, 'delibera_numero_discordar', true));
			$ndiscordar++;
			update_comment_meta($comment_id, 'delibera_numero_discordar', $ndiscordar);
			$discordaram = get_comment_meta($comment_id, 'delibera_discordaram', true);
			if(!is_array($discordaram)) $discordaram = array();
			$hora = time();
			if(!array_key_exists($hora, $discordaram)) $discordaram[$hora] = array();
			$discordaram[$hora][] = array('user' => $user_id, 'ip' => $ip);
			update_comment_meta($comment_id, 'delibera_discordaram', $discordaram);
			return $ndiscordar;
		}
	}
}

function delibera_numero_discordar($ID, $type ='pauta')
{
	if($type == 'puata')
	{
		$postID = $ID;
		$ndiscordar = get_post_meta($postID, 'delibera_numero_discordar', true);
		return $ndiscordar;
	}
	elseif($type == 'comment')
	{
		$comment_id = $ID;
		$ndiscordar = intval(get_comment_meta($comment_id, 'delibera_numero_discordar', true));
		return $ndiscordar;
	}
}

function delibera_ja_discordou($postID, $user_id, $ip, $type)
{
	$discordaram = array();
	if($type == 'pauta')
	{
		$discordaram = get_post_meta($postID, 'delibera_discordaram', true);
	}
	else 
	{
		$discordaram = get_comment_meta($postID, 'delibera_discordaram', true);
	}
	if(!is_array($discordaram)) $discordaram = array();
	
	foreach ($discordaram as $hora => $discordouem)
	{
		foreach ($discordouem as $discordou)
		{
			if(intval($user_id) == 0 && $ip == $discordou['ip'])
			{
				return true;
			}
			elseif($user_id == $discordou['user'])
			{
				return true;
			}
		}
	}
	return false;
}

/**
 * 
 * Gera código html/js para criação do botão discordar do sistema delibra
 * @param $ID int post_ID ou comment_ID
 * @param $type string 'pauta' ou 'comment'
 */
function delibera_gerar_discordar($ID, $type ='pauta')
{
	global $post;
	
	$situacoes_validas = array('validacao' => false, 'discussao' => true, 'emvotacao' => false, 'comresolucao' => true);
	
	$postID = 0;
	if(is_object($ID))
	{
		if($type == 'post' || $type == 'pauta')
		{
			$ID = $ID->ID;
			$postID = $ID;
		}
		else
		{
			$postID = $ID->comment_post_ID;
			$ID = $ID->comment_ID;
		}
	}
	
	$ndiscordou = intval($type == 'pauta' || $type == 'post' ? get_post_meta($ID, 'delibera_numero_discordar', true) : get_comment_meta($ID, 'delibera_numero_discordar', true));
	$situacao = delibera_get_situacao($postID);
	
	if(is_user_logged_in())
	{
		$user_id = get_current_user_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if(
			!delibera_ja_discordou($ID, $user_id, $ip, $type) && // Ainda não curitu
			(is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas)) && $situacoes_validas[$situacao->slug] &&// é uma situação válida
			!(function_exists('delibera_ja_curtiu') && delibera_ja_curtiu($ID, $user_id, $ip, $type)) // não discordou
		)
		{
			$html = '<div id="thebuttonDiscordo'.$type.$ID.'" class="delibera_unlike" ><span class="delibera_unlike_text">'.__('Discordo','delibera').'</span>';
			$html .= ( $ndiscordou > 0 ? '<span class="delibera-unlike-count" >'."$ndiscordou ".($ndiscordou > 1 ? __('discordaram','delibera') : __('discordou','delibera')).'</span>' : '').'</div>';
			
			$JS = '
				<script type="text/javascript">
				    jQuery(function ()
				    {
				        jQuery("#thebuttonDiscordo'.$type.$ID.'")
				            .click(function ()
				            {
								jQuery.post("'.home_url( "/" ).'/wp-admin/admin-ajax.php", 
									{
										action : "delibera_discordar" ,
									    like_id : "'.$ID.'",
									    type : "'.$type.'",
									    user_id: "'.$user_id.'",
									    ip: "'.$ip.'"
									},
									function(response)
									{
										var html_resp = "<div id=\"thebuttonDiscordo'.$type.$ID.'\" class=\"delibera_unlike\" ><span class=\"delibera_unlike_reposta\">";
										html_resp += response;
										html_resp += " "+(response > 1 ? "'.__('discordaram','delibera').'" : "'.__('discordou','delibera').'");
										html_resp += "</span></div>";
										
										jQuery("#thebuttonDiscordo'.$type.$ID.'").replaceWith( html_resp );
										if(jQuery(".delibera_like").length) jQuery(".delibera_like").hide();
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
			$html = '<div id="thebuttonDiscordo'.$type.$ID.'" class="delibera_unlike" ><span class="delibera_unlike_reposta">'."$ndiscordou ".($ndiscordou > 1 ? __('discordaram','delibera') : __('discordou','delibera')).'</span></div>';
			return $ndiscordou > 0 ? $html : '';
		}
	}
	else 
	{
		$html = '<div id="thebuttonDiscordo'.$type.$ID.'" class="delibera_unlike" >';
		if(is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas) && $situacoes_validas[$situacao->slug]) // é uma situação válida
		{
			$html .= '<a class="delibera-unlike-login" href="';
			$html .= wp_login_url( $type == "pauta" ? get_permalink() : delibera_get_comment_link());
			$html .= '" ><span class="delibera_unlike_text">'.__('Discordo','delibera').'</span></a>';
		}
		$html .= ( $ndiscordou > 0 ? '<span class="delibera-unlike-count" >'."$ndiscordou ".($ndiscordou > 1 ? __('discordaram','delibera') : __('discordou','delibera')).'</span>' : '').'</div>';
		return $html;
	}
}

function delibera_discordar_callback()
{
	if(array_key_exists('like_id', $_POST) && array_key_exists('type', $_POST))
	{
		echo delibera_discordar($_POST['like_id'], $_POST['type'], $_POST['user_id'], $_POST['ip']);
	}
	die();
}
add_action('wp_ajax_delibera_discordar', 'delibera_discordar_callback');
add_action('wp_ajax_nopriv_delibera_discordar', 'delibera_discordar_callback');

function delibera_get_quem_discordou($ID, $type = 'pauta', $return = 'array')
{
	$discordaram = array();
	if($type == 'pauta')
	{
		$discordaram = get_post_meta($ID, 'delibera_discordaram', true);
	}
	else 
	{
		$discordaram = get_comment_meta($ID, 'delibera_discordaram', true);
	}
	if(!is_array($discordaram)) $discordaram = array();
	switch($return)
	{
		case 'string':
			$ret = '';
			foreach ($discordaram as $hora => $discordouem)
			{
				foreach ($discordouem as $discordou)
				{
					if (strlen($ret) > 0) $ret .= ", ";
					$ret .= (($discordou['user'] == false || $discordou['user'] == 0) ? $discordou['ip'] : get_author_name($discordou['user']));
				}
			}
			return $ret;
		break;
		case 'array':
		default:
			return $discordaram;
		break;
	}
	
}
?>