<?php
class Ethymos_Contents{
	/**
	*
	*
	*/
	public function __construct(){
		add_action('init', array($this, 'post_type_equipe'));
	}
	
	/**
	*
	*
	*/
	public function post_type_equipe(){
		 $labels = array(
		    'name' => 'Equipe',
		    'singular_name' => 'Membro',
		    'add_new' => 'Cadastrar membro',
		    'add_new_item' => 'Cadastrar novo membro',
		    'edit_item' => 'Editar eu membro',
		    'new_item' => 'Novo membro',
		    'all_items' => 'Todos os membros',
		    'view_item' => 'Ver membro',
		    'search_items' => 'Buscar membros',
		    'not_found' =>  'Nenhum membro encontrado',
		    'not_found_in_trash' => 'Nenhum membro encontrado na lixeira',
		    'menu_name' => 'Equipe'
		);
		
		$config = array(
		    'labels' => $labels,
		    'public' => true,
		    'publicly_queryable' => true,
		    'show_ui' => true, 
		    'show_in_menu' => true, 
		    'query_var' => true,
		    'rewrite' => array( 'slug' => 'equipe' ),
		    'capability_type' => 'post',
		    'has_archive' => true, 
		    'hierarchical' => false,
		    'menu_position' => null,
		    'supports' => array( 'title', 'editor', 'thumbnail')	
		);
		
		register_post_type('Equipe', $config);
	}


}