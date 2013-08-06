<?php


class Cielo {

    /* Dados para teste do Cielo */
    public $affiliation = '1006993069';
    public $affiliation_key = '25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3';
    public $test_mode = true;
    /*
     *  Cartão: 4551870000000183 (visa), 5453010000066167 (mastercard)
     *  Data de validade: qualquer posterior ao corrente
     *  Código de segurança: qualquer
     * 
     *  Valor do pedido: para simular transação autorizada, use qualquer valor em que os dois
     *  últimos dígitos sejam zeros. Do contrário, toda autorização será negada.
     * 
     * 
    */
    
    public $requisicaoCielo;

    function __construct($request) {
        
        // estrutura base de dados
        $request_structure = array(
        
            'dados-ec' => array(
                'numero' => $this->affiliation,
                'chave' => $this->affiliation_key
            ),
            'forma-pagamento' => array(
                'bandeira' => '',
                'produto' => '1', //1 = a vista, 2 = parcelado
                'parcelas' => '1'
            ),
            'dados-cartao' => array(
                'numero' => '',
                'validade' => '',
                'codigo-seguranca' => '',
                'nome-portador' => '',
            ),
            'dados-pedido' => array(
                'numero' => '',
                'valor' => '',
                'data-hora' => date("Y-m-d\TH:i:s"),
                'descricao' => ''
            ),
            'tid' => ''
            
        );
        
        // merge da estrutura base com o que veio como parametro
        $request['dados-ec'] = wp_parse_args($request['dados-ec'], $request_structure['dados-ec']);
        $request['forma-pagamento'] = wp_parse_args($request['forma-pagamento'], $request_structure['forma-pagamento']);
        $request['dados-cartao'] = wp_parse_args($request['dados-cartao'], $request_structure['dados-cartao']);
        $request['dados-pedido'] = wp_parse_args($request['dados-pedido'], $request_structure['dados-pedido']);
        $request['tid'] = isset($request['tid']) ? $request['tid'] : $request_structure['tid'];
        
        // load xml modelo
        $this->requisicaoCielo = new SimpleXMLElement( file_get_contents(dirname(__FILE__) . "/cielo_skeleton.xml") );
        
        // popula xml modelo
        $this->requisicaoCielo->{'dados-ec'}->numero = $request['dados-ec']['numero'];
        $this->requisicaoCielo->{'dados-ec'}->chave = $request['dados-ec']['chave'];
        
        $this->requisicaoCielo->{'dados-cartao'}->numero = $request['dados-cartao']['numero'];
        $this->requisicaoCielo->{'dados-cartao'}->validade = $request['dados-cartao']['validade'];
        $this->requisicaoCielo->{'dados-cartao'}->{'codigo-seguranca'} = $request['dados-cartao']['codigo-seguranca'];
        $this->requisicaoCielo->{'dados-cartao'}->{'nome-portador'} = $request['dados-cartao']['nome-portador'];
        
        $this->requisicaoCielo->{'dados-pedido'}->valor = str_replace(".","",str_replace(",","",sprintf("%0.2f",$request['dados-pedido']['valor'])));
        $this->requisicaoCielo->{'dados-pedido'}->{'data-hora'} = $request['dados-pedido']['data-hora'];
        $this->requisicaoCielo->{'dados-pedido'}->numero = $request['dados-pedido']['numero'];
        
        // Se habilitar a descrica, dá pau...
        //$this->requisicaoCielo->{'dados-pedido'}->descricao = $request['dados-pedido']['descricao'];
        
        $this->requisicaoCielo->{'forma-pagamento'}->bandeira = $request['forma-pagamento']['bandeira'];
        $this->requisicaoCielo->{'forma-pagamento'}->produto = $request['forma-pagamento']['produto'];
        $this->requisicaoCielo->{'forma-pagamento'}->parcelas = $request['forma-pagamento']['parcelas'];
        
        $this->requisicaoCielo->tid = $request['tid'];
        
        
        //TODO parcelas??
    
    }
    
    function send() {
        
        global $wpdb;
        
        //TODO: Validar campos?
        
        $connection = curl_init();
            
        if ($this->test_mode){
            curl_setopt($connection,CURLOPT_URL,"https://qasecommerce.cielo.com.br/servicos/ecommwsec.do"); // Sandbox testing
            //		exit('sandbox is true');
        }else{
            curl_setopt($connection,CURLOPT_URL,"https://ecommerce.cbmp.com.br/servicos/ecommwsec.do"); // Live
        }

        $requisicaoCieloTID = new SimpleXMLElement( file_get_contents(dirname(__FILE__) . "/cielo_skeleton_tid.xml") );
        
        $requisicaoCieloTID->{'dados-ec'}->numero = $this->requisicaoCielo->{'dados-ec'}->numero;
        $requisicaoCieloTID->{'dados-ec'}->chave = $this->requisicaoCielo->{'dados-ec'}->chave;
        
        $requisicaoCieloTID->{'forma-pagamento'}->bandeira = $this->requisicaoCielo->{'forma-pagamento'}->bandeira;
        $requisicaoCieloTID->{'forma-pagamento'}->produto = $this->requisicaoCielo->{'forma-pagamento'}->produto;
        $requisicaoCieloTID->{'forma-pagamento'}->parcelas = $this->requisicaoCielo->{'forma-pagamento'}->parcelas;
                
                
        $useragent = 'Redelivre';
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt($connection, CURLOPT_NOPROGRESS, 1); 
        curl_setopt($connection, CURLOPT_VERBOSE, 1); 
        curl_setopt($connection, CURLOPT_FOLLOWLOCATION,0); 
        curl_setopt($connection, CURLOPT_POST, 1); 
        curl_setopt($connection, CURLOPT_TIMEOUT, 30); 
        curl_setopt($connection, CURLOPT_USERAGENT, $useragent); 
        curl_setopt($connection, CURLOPT_REFERER, "https://".$_SERVER['SERVER_NAME']); 
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        
        // Requisita o TID
        curl_setopt($connection, CURLOPT_POSTFIELDS, "mensagem=".$requisicaoCieloTID->asXML());	
        
        $respostaTID = new SimpleXMLElement(curl_exec($connection));
        
        if (strlen($respostaTID->tid) != 20)
            return false;
        
        $this->requisicaoCielo->tid = $respostaTID->tid;
        
        curl_close($connection);

        $autorizacaoCon = curl_init();

        curl_setopt($autorizacaoCon, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($autorizacaoCon, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt($autorizacaoCon, CURLOPT_NOPROGRESS, 1); 
        curl_setopt($autorizacaoCon, CURLOPT_VERBOSE, 1); 
        curl_setopt($autorizacaoCon, CURLOPT_FOLLOWLOCATION,0); 
        curl_setopt($autorizacaoCon, CURLOPT_POST, 1); 
        curl_setopt($autorizacaoCon, CURLOPT_TIMEOUT, 30); 
        curl_setopt($autorizacaoCon, CURLOPT_USERAGENT, $useragent); 
        curl_setopt($autorizacaoCon, CURLOPT_REFERER, "https://".$_SERVER['SERVER_NAME']); 
        curl_setopt($autorizacaoCon, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($autorizacaoCon, CURLOPT_POSTFIELDS, "mensagem=".$this->requisicaoCielo->asXML());
        
        if ($this->test_mode){
            curl_setopt($autorizacaoCon,CURLOPT_URL,"https://qasecommerce.cielo.com.br/servicos/ecommwsec.do"); // Sandbox testing
    //		exit('sandbox is true');
        }else{
            curl_setopt($autorizacaoCon,CURLOPT_URL,"https://ecommerce.cbmp.com.br/servicos/ecommwsec.do"); // Live
        }
        
        $autorizacaoCartao = curl_exec($autorizacaoCon);
        
        curl_close($autorizacaoCon);
        
        $response = new SimpleXMLElement($autorizacaoCartao);
        
        
        
        $return = array(
            'numero_pedido' => $log->info->numero_pedido,
            'sucesso' => false,
            'resposta' => $autorizacaoCartao
        );
        
        
        if($response->autorizacao->codigo == '4'){
		
            $return['sucesso'] = true;
            
            //send email

            
        }else{
            
            // TODO:
            // Fazer alguma coisa aqui?
            
        }
        
        return $return;
        
    }



}
