<?php
	error_reporting( E_NOTICE );

	function valid_email( $str )
	{
		return ( ! preg_match( "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str ) ) ? FALSE : TRUE;
	}

	if( $_POST['FormValue_Email']!='' && valid_email( $_POST['FormValue_Email'] )==TRUE )
	{			
		echo file_get_contents( base64_decode($_POST['request'])."/subscribe.php?".urlencode("FormValue_MailListIDs[]")."=".$_POST['FormValue_MailListIDs'][0]."&FormValue_Email=".urlencode($_POST['FormValue_Email'])."&ji=1" );
	}
	else {
		echo '<p class="jaiminho-error"><span>Digite um e-mail válido para receber a newsletter.</span><p>';
	}
?>