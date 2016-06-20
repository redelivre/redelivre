<?php
/*
 * Campanha
 * 
 * Criação de post type, registro de taxonomias e cadastro de meta boxes
 * 
 */



// Cria o post type 'campanha'
function campanha_create_post_type() {
	  // Campanhas
	  $labels = array(
	    'name' 				=> 'Itens de campanha',
	    'singular_name'	 	=> 'Item de campanha',
	    'search_items' 		=>  'Pesquisar itens de campanha',
	    'all_items' 		=> 'Todos os itens de campanha',
	    'parent_item' 		=> 'Item pai',
	    'parent_item_colon' => 'Item pai: ',
	    'edit_item' 		=> 'Editar item de campanha', 
	    'update_item' 		=> 'Atualizar item de campanha',
	    'add_new_item' 		=> 'Adicionar Novo Item de Campanha',
	    'new_item_name' 	=> 'Novo item de campanha',
	    'menu_name' 		=> 'Itens de campanha'
	  ); 	
	
	  register_taxonomy( 'itens-de-campanha', 'campanha', array(
	    'capabilities'      => array('edit_terms' => false,'manage_terms' => false),
	 	'hierarchical'		=> true,
	    'labels' 			=> $labels,
	    'show_ui' 			=> true,
	  	'show_in_nav_menus' => false,
	    'query_var' 		=> true,
	  	'public'			=> true,
	    'rewrite' 			=> array( 'slug' => 'itens-de-campanha' ),
	  ));
	  
	if(taxonomy_exists('itens-de-campanha'))
	{
		if(term_exists('transgenicos', 'itens-de-campanha', null) == false)
		{
			wp_insert_term('Transgênicos', 'itens-de-campanha', array(
			'description'=> 'Contrariando os planos da indústria, os transgênicos se tornaram tema de debate público. Confira aqui artigos, notícias e documentos publicados nos últimos dez anos.',
			'slug' => 'campanha-transgenicos',
			));
		}	
		if(term_exists('monitoramento-da-CTNBio', 'itens-de-campanha', null) == false)
		{
			wp_insert_term('Monitoramento da CTNBio', 'itens-de-campanha', array(
			'description'=> 'A Comissão Técnica Nacional de Biossegurança é o órgão do Governo Federal responsável pela análise das liberações de transgênicos. Confira nesta seção votos, relatórios e pareceres de especialistas que discordaram das decisões da CTNBio.',
			'slug' => 'monitoramento-ctnbio',
			));
		}	
		if(term_exists('materiais-de-campanha', 'itens-de-campanha', null) == false)
		{
			wp_insert_term('Materiais de campanha', 'itens-de-campanha', array(
			'description'=> 'Aqui você encontra materiais de comunicação e formação sobre os transgênicos e seus impactos. São todos de livre uso.',
			'slug' => 'materiais-de-campanha',
			));
		}
		if(term_exists('boletim', 'itens-de-campanha', null) == false)
		{
			wp_insert_term('Boletim', 'itens-de-campanha', array(
			'description'=> 'Desde 1999 a AS-PTA produz semanalmente o boletim "Por Um Brasil Livre de Transgênicos", que traz, a partir de um ponto de vista independente, a situação do Brasil e de outros países em relação aos organismos transgênicos. Por meio do Boletim você acompanha uma análise do que é noticiado na imprensa e ainda conhece experiências em agroecologia que mostram porque os transgênicos não são solução para a agricultura. Participe! Envie informações, divulgações de eventos e sugestões para boletim@aspta.org.br',
			'slug' => 'boletim',
			));			
		}	
		if(term_exists('gm-free-brazil', 'itens-de-campanha', null) == false)
		{
			wp_insert_term('Gm Free Brazil', 'itens-de-campanha', array(
			'description'=> 'Find here information about the ongoing situation of GMOs and biosafety politics in Brazil. Subscribe to monthly AS-PTA s Update from the GM-Free Brazil Campaign emailing boletim@aspta.org.br',
			'slug' => 'gm-free-brazil',
			));
		}
	}
	
	$args = array(
		'labels' => array(
			'name' 			=> 'Campanha',
			'singular_name' => 'Campanha',
			'add_new'		=> 'Adicionar nova',
			'add_new_item'	=> 'Adicionar nova',
			'edit_item'		=> 'Editar',
			'view_item'		=> 'Visualizar'
		),
		'menu_position'		=> 5,
		'public' 			=> true,
		'has_archive'		=> true,
		'query_var' 		=> true,
		'supports'			=> array( 'title', 'author', 'editor', 'excerpt', 'comments', 'thumbnail' ),
		'hierarchical'		=> true,
		'taxonomies'		=> array( 'itens-de-campanha', 'post_tag', 'temas-de-intervencao', 'programas', 'category' )
		
	
	);	
	
	register_post_type( 'campanha', $args );
}


add_action( 'init', 'campanha_create_post_type' );

?>