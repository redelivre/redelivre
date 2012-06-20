<?php
function webcontatos_replace($matches)
{	
    $param = array(); // TODO Tratar Parametros
    
    $html = webcontatos_GenerateIFrame($param);
    
	return $html; // Retornar código da representação
}
add_shortcode( 'webcontatos', 'webcontatos_replace' );

function webcontatos_formulario_replace($matches)
{
	$param = array(); // TODO Tratar Parametros

	$html = webcontatos_GenerateIFrame($param);

	return $html; // Retornar código da representação
}
add_shortcode( 'formulario-webcontatos', 'webcontatos_formulario_replace' );
?>