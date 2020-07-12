<?php

require_once( '../../../wp-load.php' );

$i = 1;

//$dados = $_POST['item'];
if(isset($_POST[ 'item' ])){
	foreach($_POST[ 'item' ] as $value){
		update_post_meta( $value, 'ordem_aula', $i );
		$i++;	
	}
}