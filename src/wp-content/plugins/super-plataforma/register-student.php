<?php 

require_once( '../../../wp-load.php' );
require_once(plugin_dir_path( __FILE__ ) . 'functions.php');

global $current_user;

$current_user = wp_get_current_user();
$user_meta=get_userdata($current_user->ID);
$user_roles=$user_meta->roles;


if( $user_roles[0] == 'administrator' ){

	$first_name = $_POST[ 'first_name' ];
	$last_name = $_POST[ 'last_name' ];
	$email = $_POST[ 'email' ];	
	$pega_idturma = $_POST['idturma'];
	$no_email = $_POST['no_email'];
	$senha_padrao = $_POST['senha_padrao'];


	$user_id = username_exists( $email );

	if ( !$user_id and email_exists($email) == false ) {
		
		$prinome = $first_name."".$turmas;
		$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
		if($senha_padrao <> ""){ $random_password = $senha_padrao; }

		$id = wp_create_user( $email, $random_password, $email );
		update_user_meta( $id, 'first_name', $prinome);
		update_user_meta( $id, 'last_name', $last_name );



		ingressar_aluno_turma($id,$pega_idturma);

		if ($no_email <> 1) {
		$cats = get_term_by('name', 'config_01', 'configuracoes');
		$subject = get_term_meta( $cats->term_id, 'titulo_aluno' , true);
    	$content = get_term_meta( $cats->term_id, 'conteudo_aluno', true);


    	$corpo_email = $content."<p>";
    	$corpo_email .= "Usuário: ".$email."<br>";
    	$corpo_email .= " Senha: ".$random_password."<br>";

    	$headers = array('Content-Type: text/html; charset=UTF-8');
		$status = wp_mail($email, $subject, $corpo_email, $headers);
		}

		echo "Success";
		echo " $username";

	} else {
		echo "Error";
		echo " $username";
	}

}else{
	return http_response_code(401);
}