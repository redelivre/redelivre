<?php
/*
Plugin Name: Transaction Log
*/
function transaction_log_init() {
    
	$model1 = array(

		'fields' => array (
            array(
				'name' => 'date',
				'display_name' => 'Data',
				'type' => 'date',
				'list_display' => true,
				'description' => 'Data do pagamento',
			),
            
            array(
				'name' => 'valor',
				'display_name' => 'Valor',
				'type' => 'textfield',
				'list_display' => true,
				'description' => 'Valor do pagamento',
			),
            array(
				'name' => 'user_id',
				'display_name' => 'ID do usuário',
				'type' => 'textfield',
				'list_display' => true,
				'description' => 'ID do usuário',
			),
            array(
				'name' => 'campaign_id',
				'display_name' => 'ID da campanha',
				'type' => 'textfield',
				'list_display' => true,
				'description' => 'ID da campanha',
			),
			array(
				'name' => 'id_transacao',
				'display_name' => 'ID da transação',
				'type' => 'textfield',
				'list_display' => false,
				'description' => 'ID da transaçao com a cielo',
			),
			array(
				'name' => 'numero_pedido',
				'display_name' => 'Número do Pedido',
				'type' => 'textfield',
				'list_display' => true,
				'description' => 'Número do Pedido',
			),
            array(
				'name' => 'response',
				'display_name' => 'Resposta',
				'type' => 'textfield',
				'list_display' => true,
				'description' => 'Resposta enviada pela cielo',
			),
			
			array(
				'name' => 'aprovada',
				'display_name' => 'Aprovada',
				'type' => 'bool',
				'list_display' => true,
				'default' => 1,
				'description' => 'A transação foi aprovada pela Cielo?',
			),
			

		),
		'tableName' => 'transaction_log',
		'adminName' => 'Log de transações',

	);


	global $transaction_log;

    if (class_exists('Wp_easy_data')) {
	    $transaction_log = new Wp_easy_data($model1, __FILE__);
        
    }
	
}

add_action('init', 'transaction_log_init', 2);
