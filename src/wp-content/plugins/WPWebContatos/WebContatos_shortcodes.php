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
		$param['opcoes'] = '&id=1';
	}
	else
	{
		$param['opcoes'] = '&id='.$param['id'];
	}
	$param['page'] = 'Display';
	$param['service'] = 'form';
	
	$html = webcontatos_GenerateIFrame($param);

	return $html; // Retornar código da representação
}
add_shortcode( 'formulario-webcontatos', 'webcontatos_formulario_replace' );
?>