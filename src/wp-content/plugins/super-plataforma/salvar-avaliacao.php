<?php 

require_once( '../../../wp-load.php' );

global $wpdb;

$table_avaliacao_aula = $wpdb->prefix . 'EAD_avaliacao_aula';
$aluno = $_POST["aluno"];
$aula = $_POST["aula"];
$curso = $_POST["curso"];
$avaliacao = $_POST["rating"];

$resultado = $wpdb->get_results( 'SELECT id FROM '.$table_avaliacao_aula.' WHERE id_aula = "'.$aula.'" AND id_usuario = "'.$aluno.'" ' );

if($resultado != null){
	$wpdb->update( 
		$table_avaliacao_aula, 
		array( 
			'avaliacao' => $avaliacao
		), 
		array( 'ID' => $resultado[0]->id ), 
		array( 
			'%s'
		), 
		array( '%d' ) 
	);
}else{
	$wpdb->insert( $table_avaliacao_aula, array(
			'id_curso'      => $curso,
	    	'id_aula'       => $aula, 
	    	'id_usuario'    => $aluno,
	    	'avaliacao'		=> $avaliacao
	    ),
	    array( '%s', '%s', '%s' ) 
	);
}