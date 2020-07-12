<?php

require_once( '../../../wp-load.php' );

$i = 1;

//$dados = $_POST['item'];

foreach($_POST[ 'item' ] as $value){
	update_term_meta( $value, 'ordem_modulo', $i );
	$i++;	
}