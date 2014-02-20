<?php
class Ethymos_Query{
	
	public $categoria_destaques;
		
	/**
	*
	*
	*/
	public function __construct(){
		$this->categoria_destaques = get_term_by('name', 'Destaques', 'category')->term_id;
	}
	/**
	* Query para os destaques
	*
	*/
	public function destaques($limite = 3){
		$query = new WP_Query(array(
			'posts_per_page' => $limite,
			'tax_query' => array(array(
				'taxonomy' => 'category',
				'field' => 'id',
				'terms' => $this->categoria_destaques
			))
		)); 
		
		return $query;
	}
	
	/**
	* Consulta ara o slider da capa
	*
	*/
	public function slider($limite = 5){
		$query = new WP_Query(array(
			'post_type' => 'post',
			'posts_per_page' => $limite,
			'meta_key' => '_home',
			'meta_value' => '1'
		));

		return $query;
	}
	
	/**
	* Consulta post type equipe
	*
	*/
	public function equipe($limite = 50){
		$query = new WP_Query(array(
			'post_type' => 'equipe', 
			'posts_per_page' => $limite,
		));
			
		return $query;
	}
}

?>