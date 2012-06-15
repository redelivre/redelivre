<?php

add_action('admin_print_scripts', function() {
    global $plugin_page;
    if (isset($plugin_page) && $plugin_page == 'payments') {
        wp_enqueue_script('jquery_validate', WPMU_PLUGIN_URL . '/js/jquery.validate.min.js', array('jquery'));
        wp_enqueue_script('payment', WPMU_PLUGIN_URL . '/js/payment.js', array('jquery', 'jquery_validate'));
        
        wp_localize_script('payment', 'payment', array( 
            'ajaxurl' => admin_url('admin-ajax.php'),
            'msg_card_min' => 'Número de cartão inválido',
            'msg_card_max' => 'Número de cartão inválido',
            'msg_card_required' => 'Número de cartão inválido',
            'cctype_required' => 'Informe a bandeira do seu cartão',
            'cctype_number' => '',
            'nome_required' => 'Insira o nome impresso no cartão',
            'car_code_length' => 'Insira um número de 3 digítos',
            'car_code_length' => 'Insira um número de 3 digítos',
            'car_code_required' => 'Insira um número de 3 digítos'
        ));
    }
});

function payment_menu() {
    // Por padrão criamos uma página exclusiva para as opções desse site
    // Mas se quiser você pode colocar ela embaixo de aparencia, opções, ou o q vc quiser. O modelo para todos os casos estão comentados abaixo
    
    $topLevelMenuLabel = 'Pagamento';
    $page_title = 'Pagamentos';
    $menu_title = 'Pagamentos';
    
    /* Top level menu */
    add_submenu_page('payments', $page_title, $menu_title, 'manage_options', 'payments', 'payment_page_callback_function');
    add_menu_page($topLevelMenuLabel, $topLevelMenuLabel, 'manage_options', 'payments', 'payment_page_callback_function');
}

function payment_page_callback_function() {
    global $campaign;
    
    $isPaid = $campaign->isPaid();
    
    global $campaign;
    
    $curryear = date('Y');
    $years = '';

    //generate year options
	for($i=0; $i < 10; $i++){
		$years .= "<option value='".$curryear."'>".$curryear."</option>\r\n";
		$curryear++;
	}
    
?>
    <div class="wrap span-20">
        
        <h2>Pagamento</h2>
        
        <?php if ($isPaid): ?>
            
            <p>Seu pagamento está confirmado e sua campanha já está ativa</p>
            <p>Quer fazer um upgrade de plano? ______________-Aguarde aqui___________</p>
            
        <?php else: ?>
            
            
            <p>Como você gostaria de fazer seu pagamento?</p>
            
            <div id="pgto-cartao" class="pagamento">
            
                <h3>Cartão de Crédito</h3>
                
                <pre>
                Cartão: 4551870000000183 (visa), 5453010000066167 (mastercard)
                Data de validade: qualquer posterior ao corrente
                Código de segurança: qualquer
                
                Valor do pedido: para simular transação autorizada, use qualquer valor em que os dois
                últimos dígitos sejam zeros. Do contrário, toda autorização será negada.
                </pre>
                
                <form class="clear" name="checkout" id="payment-checkout-form">    
                    <input type="hidden" name="action" value="process_payment_cielo" />
                    <input type="hidden" name="parcelas" id="donate_parcelas" value="1" />
                    
                    
                    <p class="clearfix">
                        <legend>Selecione a Bandeira</legend>
                        <label for='visa' id='spanvisa' ><input type='radio' id='visa' name='cctype' value='visa'> Visa</label>		
                        <label for='mastercard' id='spanmastercard'><input type='radio' id='mastercard' name='cctype' value='mastercard' > Mastercard</label>
                    </p>
                    <p class="clearfix">
                        <label>Número do Cartão</label>
                        <input type='text' value='' maxlength='16' name='card_number' title='Insira o número do cartão'  />
                    </p>
                    <p class="clearfix">    
                        <label>Nome impresso no cartão</label>
                        <input type='text' value='' name='nome_portador' maxlength='50' size='35' title='Insira o nome impresso no seu cartão'  />
                    </p>
                    <p class="clearfix">			
                        <label>Validade</label>
                            
                        <select name='expiry[month]'>
                            <option value='01'>01</option>
                            <option value='02'>02</option>
                            <option value='03'>03</option>
                            <option value='04'>04</option>
                            <option value='05'>05</option>						
                            <option value='06'>06</option>						
                            <option value='07'>07</option>					
                            <option value='08'>08</option>						
                            <option value='09'>09</option>						
                            <option value='10'>10</option>						
                            <option value='11'>11</option>																			
                            <option value='12'>12</option>																			
                        </select>
                    
                        <select name='expiry[year]'>
                            <?php echo $years; ?>
                        </select>
                    </p>
                    <p class="clearfix">
                        <label>Código de segurança</label>
                        <input type='text' size='4' value='' maxlength='3' name='card_code' title='Insira o código de segurança com 3 digítos que está impresso no verso do seu cartão' />
                    </p>
                    <p>		
                    <input type="submit" value="Efetuar pagamento!" id="donate_button" class="button" />
                    </p>
                </form>
                
                <div class="feedback clear" style="display:none;" id="payment-checkout-loading">Conectando com Cielo...</div>
                <div class="feedback clear" style="display:none;" id="payment-checkout-success">A transação foi concluída com sucesso! Você deve receber um email... NUMERO PEDIDO...</div>
                <div class="feedback clear" style="display:none;" id="payment-checkout-error">Ocorreu um erro ao processar sua transação, por favor verifique os dados ou entre em contato ??? NUMERO PEDIDO ...</div>
                
            
            </div>
            
            <div id="pgto-cartao" class="pagamento">
            
                <h3>Boleto Bancário</h3>
            
            </div>
            
            
        <?php endif; ?>
        
    </div>

<?php } 


// temporarily disabled until we finish implementing this feature
//add_action('admin_menu', 'payment_menu');

add_action('wp_ajax_process_payment_cielo', 'ajax_process_payment_cielo');
add_action('wp_ajax_nopriv_process_payment_cielo', 'ajax_process_payment_cielo');

function ajax_process_payment_cielo() {
    
    global $campaign, $wpdb;
    
    $numero_cartao = preg_replace('/(\d{12})/', "$1", $_POST['card_number']);
    
    
    $plan = Plan::getById($campaign->plan_id);
    $valor = $plan->price;
    
    $validade = $_POST['expiry']['year'].$_POST['expiry']['month'];
    
    $bandeira = $_POST['cctype'];
    
    $cod_seguranca = $_POST['card_code'];
    
    $nome_portador = $_POST['nome_portador'];
    
    $current_user = wp_get_current_user();
    
    $dateTime = date("Y-m-d\TH:i:s");
    
    
    
    $log = array();
    
    $log['id_transacao'] = uniqid();
    $log['date'] = $dateTime;
    $log['valor'] = $valor;
    $log['user_id'] = $current_user->ID;
    $log['campaign_id'] = $campaign->id;
    
    //geramos uma entrada na TransactionLog para termos um ID dessa transação
    $wpdb->insert('transaction_log', $log);
    
    $log['id'] = $wpdb->insert_id;
    $log['numero_pedido'] = $log['id'];
    
    $request = array(
        
            'forma-pagamento' => array(
                'bandeira' => $bandeira,
                //'parcelas' => '1'
            ),
            'dados-cartao' => array(
                'numero' => $numero_cartao,
                'validade' => $validade,
                'codigo-seguranca' => $cod_seguranca,
                'nome-portador' => $nome_portador,
            ),
            'dados-pedido' => array(
                'valor' => $valor,
                'data-hora' => $dateTime,
                'descricao' => $plan->name,
                'numero' => $log['id']
            ),
            'tid' => $log['id_transacao']
            
        );
    
    require_once('cielo/Cielo.php');
    
    $pgto = new Cielo($request);
    
    $result = $pgto->send();
    
    $log['response'] = $result['resposta'];
    
    if (true === $result['sucesso']) {
        $campaign->setStatus(1);
        $log['aprovada'] = 1;
        echo 'success';
    } else {
        
        echo 'erro';
    }
    
    $wpdb->update( 'transaction_log', $log, array('id' => $log['id']) );
    
    exit;

}
