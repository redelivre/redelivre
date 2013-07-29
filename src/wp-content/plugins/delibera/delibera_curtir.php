<?php

function delibera_curtir_comment_meta($comment_id)
{
	$ncurtiram = get_comment_meta($comment_id, "delibera_numero_curtir", true);
	if($ncurtiram == false || $ncurtiram == "")
	{
		$ncurtiram = array();
		add_comment_meta($comment_id, 'delibera_numero_curtir', $ncurtiram, true);
	}
	
	$curtiram = get_comment_meta($comment_id, "delibera_curtiram", true);
	if($curtiram == false || $curtiram == "")
	{
		$curtiram = array();
		add_comment_meta($comment_id, 'delibera_curtiram', $curtiram, true);
	}
}

function delibera_curtir($ID, $type ='pauta', $user_id = false, $ip = false)
{
	if(!delibera_ja_curtiu($ID, $user_id, $ip, $type) && !(function_exists('delibera_ja_discordou') && delibera_ja_discordou($ID, $user_id, $ip, $type)) )
	{
		if($type == 'pauta')
		{
			$postID = $ID;
			$ncurtir = get_post_meta($postID, 'delibera_numero_curtir', true);
			$ncurtir++;
			update_post_meta($postID, 'delibera_numero_curtir', $ncurtir);
			$curtiram = get_post_meta($postID, 'delibera_curtiram', true);
			if(!is_array($curtiram)) $curtiram = array();
			$hora = time();
			if(!array_key_exists($hora, $curtiram)) $curtiram[$hora] = array();
			$curtiram[$hora][] = array('user' => $user_id, 'ip' => $ip);
			update_post_meta($postID, 'delibera_curtiram', $curtiram);
			return $ncurtir;
		}
		elseif($type == 'comment')
		{
			$comment_id = $ID;
			$ncurtir = intval(get_comment_meta($comment_id, 'delibera_numero_curtir', true));
			$ncurtir++;
			update_comment_meta($comment_id, 'delibera_numero_curtir', $ncurtir);
			$curtiram = get_comment_meta($comment_id, 'delibera_curtiram', true);
			if(!is_array($curtiram)) $curtiram = array();
			$hora = time();
			if(!array_key_exists($hora, $curtiram)) $curtiram[$hora] = array();
			$curtiram[$hora][] = array('user' => $user_id, 'ip' => $ip);
			update_comment_meta($comment_id, 'delibera_curtiram', $curtiram);
			return $ncurtir;
		}
	}
}

function delibera_numero_curtir($ID, $type ='pauta')
{
	if($type == 'puata')
	{
		$postID = $ID;
		$ncurtir = get_post_meta($postID, 'delibera_numero_curtir', true);
		return $ncurtir;
	}
	elseif($type == 'comment')
	{
		$comment_id = $ID;
		$ncurtir = intval(get_comment_meta($comment_id, 'delibera_numero_curtir', true));
		return $ncurtir;
	}
}

function delibera_ja_curtiu($postID, $user_id, $ip, $type)
{
	$curtiram = array();
	if($type == 'pauta')
	{
		$curtiram = get_post_meta($postID, 'delibera_curtiram', true);
	}
	else 
	{
		$curtiram = get_comment_meta($postID, 'delibera_curtiram', true);
	}
	if(!is_array($curtiram)) $curtiram = array();
	
	foreach ($curtiram as $hora => $curtiuem)
	{
		foreach ($curtiuem as $curtiu)
		{
			if(intval($user_id) == 0 && $ip == $curtiu['ip'])
			{
				return true;
			}
			elseif($user_id == $curtiu['user'])
			{
				return true;
			}
		}
	}
	return false;
}

/**
 * 
 * Gera código html/js para criação do botão curtir/concordar do sistema delibra
 * @param $ID post_ID ou comment_ID
 * @param $type 'pauta' ou 'comment'
 */
function delibera_gerar_curtir($ID, $type ='pauta')
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
	
	$ncurtiu = intval($type == 'pauta' || $type == 'post' ? get_post_meta($ID, 'delibera_numero_curtir', true) : get_comment_meta($ID, 'delibera_numero_curtir', true));
	$situacao = delibera_get_situacao($postID);
	
	if(is_user_logged_in())
	{
		$user_id = get_current_user_id();
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if(
			!delibera_ja_curtiu($ID, $user_id, $ip, $type) && // Ainda não curitu
			(is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas)) && $situacoes_validas[$situacao->slug] && // é uma situação válida
			!(function_exists('delibera_ja_discordou') && delibera_ja_discordou($ID, $user_id, $ip, $type)) // não discordou
		)
		{
			$html = '<div id="thebutton'.$type.$ID.'" class="delibera_like" ><span class="delibera_like_text">'.__('Concordo','delibera').'</span>';
			$html .= ( $ncurtiu > 0 ? '<span class="delibera-like-count" >'."$ncurtiu ".($ncurtiu > 1 ? __('concordaram','delibera') : __('concordou','delibera')).'</span>' : '').'</div>';
			
			$JS = '
				<script type="text/javascript">
				    jQuery(function ()
				    {
				        jQuery("#thebutton'.$type.$ID.'")
				            .click(function ()
				            {
								jQuery.post("'.home_url( "/" ).'/wp-admin/admin-ajax.php", 
									{
										action : "delibera_curtir" ,
									    like_id : "'.$ID.'",
									    type : "'.$type.'",
									    user_id: "'.$user_id.'",
									    ip: "'.$ip.'"
									},
									function(response)
									{
										var html_resp = "<div id=\"thebutton'.$type.$ID.'\" class=\"delibera_like\" ><span class=\"delibera_like_reposta\">";
										html_resp += response;
										html_resp += " "+(response > 1 ? "'.__('concordaram','delibera').'" : "'.__('concordou','delibera').'");
										html_resp += "</span></div>";
										
										jQuery("#thebutton'.$type.$ID.'").replaceWith( html_resp );
										if(jQuery(".delibera_like").length) jQuery(".delibera_unlike").hide();
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
			$html = '<div id="thebutton'.$type.$ID.'" class="delibera_like" ><span class="delibera_like_reposta">'."$ncurtiu ".($ncurtiu > 1 ? __('concordaram','delibera') : __('concordou','delibera')).'</span></div>';
			return $ncurtiu > 0 ? $html : '';
		}
	}
	else 
	{
		$html = '<div id="thebutton'.$type.$ID.'" class="delibera_like" >';
		if(is_object($situacao) && array_key_exists($situacao->slug, $situacoes_validas) && $situacoes_validas[$situacao->slug]) // é uma situação válida
		{
			$html .= '<a class="delibera-like-login" href="';
			$html .= wp_login_url( $type == "pauta" ? get_permalink() : delibera_get_comment_link());
			$html .= '" ><span class="delibera_like_text">'.__('Concordo','delibera').'</span></a>';
		}
		$html .= ( $ncurtiu > 0 ? '<span class="delibera-like-count" >'."$ncurtiu ".($ncurtiu > 1 ? __('concordaram','delibera') : __('concordou','delibera')).'</span>' : '').'</div>';
		return $html;
	}
}

function delibera_curtir_callback()
{
	if(array_key_exists('like_id', $_POST) && array_key_exists('type', $_POST))
	{
		echo delibera_curtir($_POST['like_id'], $_POST['type'], $_POST['user_id'], $_POST['ip']);
	}
	die();
}
add_action('wp_ajax_delibera_curtir', 'delibera_curtir_callback');
add_action('wp_ajax_nopriv_delibera_curtir', 'delibera_curtir_callback');

function delibera_get_quem_curtiu($ID, $type = 'pauta', $return = 'array')
{
	$curtiram = array();
	if($type == 'pauta')
	{
		$curtiram = get_post_meta($ID, 'delibera_curtiram', true);
	}
	else 
	{
		$curtiram = get_comment_meta($ID, 'delibera_curtiram', true);
	}
	if(!is_array($curtiram)) $curtiram = array();
	switch($return)
	{
		case 'string':
			$ret = '';
			foreach ($curtiram as $hora => $curtiuem)
			{
				foreach ($curtiuem as $curtiu)
				{
					if (strlen($ret) > 0) $ret .= ", ";
					$ret .= (($curtiu['user'] == false || $curtiu['user'] == 0) ? $curtiu['ip'] : get_author_name($curtiu['user']));
				}
			}
			return $ret;
		break;
		case 'array':
		default:
			return $curtiram;
		break;
	}
	
}
?>