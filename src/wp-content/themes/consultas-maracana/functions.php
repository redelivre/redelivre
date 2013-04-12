<?php

class Consultas_Maracana{
	
	private $register_form_fields;
	
	
	/**
	* Método construtor. Define os actions do wordpress
	*
	*/
	public function __construct(){
		//inclui recursos necessários 
		add_action('wp_enqueue_scripts', array($this, 'javascript'));
		add_action('wp_enqueue_scripts', array($this, 'css'));
		
		//registra shortcodes
		add_shortcode('titulo', array($this, 'shortcode_titulo'));
		add_shortcode('formulario-registro', array($this, 'shortcode_register_form'));
		
		//actions ajax
		add_action('wp_ajax_nopriv_register_form_save', array($this, 'ajax_register_form_save'));
		add_action('wp_ajax_register_form_save', array($this, 'ajax_register_form_save'));
		
		//Define campos do formulário de registro de usuário. Sintax: label => array(type, id, name, class)
		/*
			Info config:
			type = Tipo de campo html
			wp_type = Tipo de campo para o wordpress. "user-field" para campos normais do cadastro e "meta" para campos a serem salvos na tabela usermeta
			id = id html do campo
			name = nome html do campo. Caso seja um "user-field", deve ter o mesmo nome do campo na tabela do banco. Ex.: Login = user_login
			class = Classes CSS a serem utilizadas no campo
			required = Se o campo é obrigaório ou não. 1 ou 0
		*/
		$fields_class = "campo";		
		$this->register_form_fields = array(
			'Nome de usuário' => array(
				'type' 		=> 'text',
				'wp_type'	=> 'user-field',
				'id' 		=> 'user_login',
				'name' 		=> 'user_login',
				'class' 	=> $fields_class,
				'required'  => '1'
				),
			'Email' => array(
				'type' 		=> 'text',
				'wp_type'	=> 'user-field',
				'id'		=> 'user_email',
				'name' 		=> 'user_email',
				'class' 	=> $fields_class,
				'required'  => '1'
				),
			'Senha' => array(
				'type' 		=> 'password',
				'wp_type'	=> 'user-field',
				'id' 		=> 'user_pass',
				'name' 		=> 'user_pass', 
				'class' 	=> $fields_class,
				'required'  => '1'
				
				),
			'Nome completo' => array(
				'type' 		=> 'text',
				'wp_type'	=> 'user-field',
				'id' 		=> 'first_name',
				'name' 		=> 'first_name',
				'class' 	=> $fields_class,
				'required'  => '1'
				),
			'Site' => array(
				'type' 		=> 'text',
				'wp_type'	=> 'user-field',
				'id' 		=> 'user_url',
				'name' 		=> 'user_url',
				'class' 	=> $fields_class,
				'required'  => '0'
				),
			'Participa de algum Movimento ou Organização? Qual? <em>(Caso esteja cadastrando sua entidade, ignore esta pergunta)</em>' => array(
				'type' 		=> 'text',
				'wp_type'	=> 'user-field',
				'id' 		=> 'description',
				'name'		=> 'description',
				'class' 	=> $fields_class,
				'required'  => '0'
				)
		);
	}	
	
	/**
	* Função utilizada para gerar o shortcode [titulo].
	* Adiciona a classe CSS referente ao titulo para estilização
	*/
	function shortcode_titulo($tts, $content = null){
		return '<span class="titulo-bloco">'. $content . '</span>';
	}
	
	/**
	* Retorna campos do formulário de cadastro
	*
	*/
	public function get_register_form_fields(){
		return $this->register_form_fields;
	}
	
	/**
	* Função executada no shortcode de formulário de registro
	*
	*/
	public function shortcode_register_form(){
		ob_start();
		
		include get_stylesheet_directory() . '/register-form.php';
		
		$r = ob_get_contents();
		ob_end_clean();
		
		return $r;
	}
	
	/**
	* Controla o cadastro do usuário pelo formulário de registro.
	*
	*/
	public function ajax_register_form_save(){
		parse_str($_POST['formdata'], $formdata);
		$campos = $this->get_register_form_fields();
		$erros = array();
		$userdata = array();
		$usermeta = array();
		
		//config padrão do usuario
		$userdata['role'] = 'subscriber';
		
		foreach($campos as $label => $config){
			if($config['required'] == '1' && (!$formdata[$config['name']])){
				$erros[] = "O campo <strong>{$label}</strong> é de preenchimento obrigatório.";
			} else {
				
				if($config['wp_type'] == "user-field"){
					$userdata[$config['name']] = $formdata[$config['name']];
				} else {
					$usermeta[$config['name']] = $formdata[$config['name']];
				}
			}		
		}
		
		if(!$userdata){
			$erros[] = __("Ocorreu um erro ao efetuar seu cadastro, por favor, informe um administrador do site através da página de contato", 'consultas-maraca');
		} else {
			$usuario = wp_insert_user($userdata);
					
			//checa se o cadastro foi efetuado com sucesso
			if(!is_wp_error($usuario)){
				//grava meta dados
				if($usermeta){
					foreach($usermeta as $key => $value){
						update_user_meta($usuario, $key, $value);
					}
				}
			} else { 
				foreach($usuario->errors as $error){
					$erros[] = $error[0];
				}
			}
		}
				
		if($erros){
			include get_stylesheet_directory() . '/register-form.php';
			foreach($erros as $erro){
				echo $erro . "<br />";
			}
		} else {
                        $action = site_url('wp-login.php');
                        $redirect_to = ($_POST['redirect_to'] != "" ? $_POST['redirect_to'] : site_url('/'));
                        echo <<<EOT
                            <p><h2>Obrigado!</h2>Seu cadastro foi efetuado com sucesso, agora você já pode efetuar seu login e participar:</p>
                            <form name="loginform" id="loginform" action="$action" method="post">
                                <p><label>Usuário:<br /><input type="text" name="log" id="log" value="" size="20" tabindex="1" /></label></p>
                                <p><label>Senha:<br /> <input type="password" name="pwd" id="pwd" value="" size="20" tabindex="2" /></label></p>
                                <p>
                                    <label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="3" />
                                Lembrar meus dados</label></p>
                                <p class="submit">
                                <input type="submit" class="bt-verde" name="submit" id="submit" value="Login &raquo;" tabindex="4" />
                                <input type="hidden" name="redirect_to" value="$redirect_to" />
                                </p>
                            </form>                
EOT;
		}
		
		exit();
	}
	
	/**
	*
	*
	*/
	public function javascript(){
		$path = get_stylesheet_directory_uri() . '/js';
		wp_register_script('jquery-tools', 'http://cdn.jquerytools.org/1.2.6/all/jquery.tools.min.js', array('jquery'));
		wp_register_script('maraca-functions', $path . '/maraca-functions.js', array('jquery', 'jquery-tools'));
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-tools');
		wp_enqueue_script('maraca-functions');
		wp_localize_script('maraca-functions', 'wpajax', array('url' => admin_url('admin-ajax.php')));
	}
	
	/**
	*
	*
	*/
	public function css(){
		$path = get_stylesheet_directory_uri() . '/css';
		wp_enqueue_style('jquery-tools', $path . '/jquery-tools.css');		
	}
}

$tema_maracana = new Consultas_Maracana;


?>