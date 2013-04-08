<?php

class Consultas_Maracana{
	
	/**
	* Método construtor. Define os actions do wordpress
	*
	*/
	public function __construct(){
		add_shortcode('titulo', array($this, 'shortcode_titulo'));
		show_admin_bar(false);
	}	
	
	/**
	* Função utilizada para gerar o shortcode [titulo].
	* Adiciona a classe CSS referente ao titulo para estilização
	*/
	function shortcode_titulo($tts, $content = null){
		return '<span class="titulo-bloco">'. $content . '</span>';
	}
	
}

$tema_maracana = new Consultas_Maracana;


?>