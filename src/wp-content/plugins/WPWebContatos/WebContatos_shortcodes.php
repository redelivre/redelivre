<?php
function webcontatos_replace($args)
{
    $page = 'Gerenciar/GerenciarContatos';
    if(array_key_exists('page', $args))
    {
    	$page = $args['page'];
    }
    
    $params=array('page'=>$page);
    
    array_merge($params, $args);
    
    $html = webcontatos_GenerateIFrame($params);
    
	return $html; // Retornar código da representação
}
add_shortcode( 'webcontatos', 'webcontatos_replace' );

function webcontatos_formulario_replace($args)
{
	if(!is_array($args))
	{
		$args = array();
	}
	$param = $args;
	if(!array_key_exists('id', $args))
	{
		$param['opcoes'] .= '&id=1';
	}
	else
	{
		$param['opcoes'] .= '&id='.$param['id'];
	}
	if(array_key_exists('topo', $args))
	{
		$param['opcoes'] .= '&topo='.$args['topo'];
	}
	
	$param['page'] = 'Display';
	$param['service'] = 'form';
	
	$html = webcontatos_GenerateIFrame($param);

	return $html; // Retornar código da representação
}
add_shortcode( 'formulario-webcontatos', 'webcontatos_formulario_replace' );

function webcontatos_numero_incricoes_replace($args)
{
	if(!is_array($args))
	{
		$args = array();
	}
	$param = array('id' => 2);
	$param = array_merge($param, $args);
	
	$numero = webcontatos_numero_incricoes($param['id']);
	$html = '<p class="numero-inscricoes-webcontatos">'.$numero.'</p>';

	return $html; // Retornar código da representação
}
add_shortcode( 'numero-inscricoes-webcontatos', 'webcontatos_numero_incricoes_replace' );

function webcontatos_incricoes_replace($args)
{
	if(!is_array($args))
	{
		$args = array();
	}
	$param = array('id' => 2, 'offset' => 0, 'limit' => 10);
	$param = array_merge($param, $args);
	$html = "";
	$inscritos = webcontatos_incricoes($param['id'], $param['offset'], $param['limit']);
	foreach($inscritos as $inscrito)
	{
		$html .= '<p class="inscricoes-webcontatos">'.$inscrito['nome'].'</><br/>';
	}

	return $html; // Retornar código da representação
}
add_shortcode( 'inscricoes-webcontatos', 'webcontatos_incricoes_replace' );
?>