<?php

// Dẽ um Find & Replace (CASE SENSITIVE!) em _TAXONOMY_ Pelo nome da sua taxonomia

class _TAXONOMY_ {
    const NAME = '_TAXONOMY_s';

    /**
     * slug da taxonomia: deve conter somente minúscula 
     * @var unknown_type
     */
    protected static $taxonomy;
    protected static $post_types;
    
    static function init(){
        // o slug da taxonomia
        self::$taxonomy = strtolower(__CLASS__);
        
        // Coloque aqui o slug dos post types aos quais essa taxonomia vai se aplicar
        self::$post_types = array ( 'post' );
        
        add_action( 'init', array(self::$taxonomy, 'register') ,0);

    }
    
    static function register(){
    	
        $labels = array(
            'name' => _x( '_TAXONOMY_s', 'taxonomy general name', 'SLUG' ),
            'singular_name' => _x( '_TAXONOMY_', 'taxonomy singular name', 'SLUG' ),
            'search_items' =>  __( 'Search _TAXONOMY_s', 'SLUG' ),
            'all_items' => __( 'All _TAXONOMY_s', 'SLUG' ),
            'parent_item' => __( 'Parent _TAXONOMY_', 'SLUG' ),
            'parent_item_colon' => __( 'Parent _TAXONOMY_:', 'SLUG' ),
            'edit_item' => __( 'Edit _TAXONOMY_', 'SLUG' ), 
            'update_item' => __( 'Update _TAXONOMY_', 'SLUG' ),
            'add_new_item' => __( 'Add New _TAXONOMY_', 'SLUG' ),
            'new_item_name' => __( 'New _TAXONOMY_ Name', 'SLUG' ),
        ); 	

        register_taxonomy(self::$taxonomy,self::$post_types, array(
                'hierarchical' => false,
                'labels' => $labels,
                'show_ui' => true,
                'query_var' => true,
                'rewrite' => true,
            )
        );
    }
  
}

_TAXONOMY_::init();
