<?php

// Dê um Find Replace (CASE SENSITIVE!) em _POST_TYPE_ pelo nome do seu post type 

class _POST_TYPE_ {

    const NAME = '_POST_TYPE_s';
    const MENU_NAME = '_POST_TYPE_';

    // configuração dos metaboxes
    protected static $meta_cfg = array(
        'post_relacionado' => array(
            'title' => 'Banda',
            'meta_name' => '_banda_id',
            'post_type_relacionado' => 'banda'
        ),

        'video' => array(
            'title' => 'Vídeo',
            'meta_name' => '_video'
        ),
        
        'redes_sociais' => array(
            'title' => "Redes Sociais",
            'meta_name' => '_social_networks',
            'redes' => array(
                'facebook',
                'google',
                'twitter',
                'myspace',
                'flickr'
            )
        )
    );
    protected static $metaboxes_ativos = array(
        'post_relacionado',
        'redes_sociais',
        'video',
//      'posts_relacionados',
//      'videos'
    );

    /**
     * alug do post type: deve conter somente minúscula 
     * @var string
     */
    protected static $post_type;

    static function init() {
        // o slug do post type
        self::$post_type = strtolower(__CLASS__);

        add_action('init', array(self::$post_type, 'register'), 0);


        if (in_array('post_relacionado', self::$metaboxes_ativos)) {
            add_action('add_meta_boxes', array(__CLASS__, 'register_metabox_post_relacionado'), 10);
            add_action('save_post', array(__CLASS__, 'metabox_save_post_relacionado'), 10);
        }

        if (in_array('redes_sociais', self::$metaboxes_ativos)) {
            add_action('add_meta_boxes', array(__CLASS__, 'register_metabox_redes_sociais'), 10);
            add_action('save_post', array(__CLASS__, 'metabox_save_redes_sociais'), 10);
        }

        if (in_array('video', self::$metaboxes_ativos)) {
            add_action('add_meta_boxes', array(__CLASS__, 'register_metabox_video'), 10);
            add_action('save_post', array(__CLASS__, 'metabox_save_video'), 10);
        }


        // descomente se precisar de taxonomias e configure as taxonomias na funcao register_taxonomies
        //add_action( 'init', array(__CLASS__, 'register_taxonomies') ,10);
        //add_filter('menu_order', array(self::$post_type, 'change_menu_label'));
        //add_filter('custom_menu_order', array(self::$post_type, 'custom_menu_order'));
        //add_action('save_post',array(__CLASS__, 'on_save'));
    }

    static function register() {
        register_post_type(self::$post_type, array(
            'labels' => array(
                'name' => _x(self::NAME, 'post type general name', 'SLUG'),
                'singular_name' => _x('_POST_TYPE_', 'post type singular name', 'SLUG'),
                'add_new' => _x('Adicionar Novo', 'image', 'SLUG'),
                'add_new_item' => __('Adicionar novo _POST_TYPE_', 'SLUG'),
                'edit_item' => __('Editar _POST_TYPE_', 'SLUG'),
                'new_item' => __('Novo _POST_TYPE_', 'SLUG'),
                'view_item' => __('Ver _POST_TYPE_', 'SLUG'),
                'search_items' => __('Search _POST_TYPE_s', 'SLUG'),
                'not_found' => __('Nenhum _POST_TYPE_ Encontrado', 'SLUG'),
                'not_found_in_trash' => __('Nenhum _POST_TYPE_ na Lixeira', 'SLUG'),
                'parent_item_colon' => ''
            ),
            'public' => true,
            'rewrite' => array('slug' => '_POST_TYPE_'),
            'capability_type' => 'post',
            'hierarchical' => true,
            'map_meta_cap ' => true,
            'menu_position' => 6,
            'has_archive' => true, //se precisar de arquivo
            'supports' => array(
                'title',
                'editor',
                'page-attributes'
            ),
                //'taxonomies' => array('taxonomia')
                )
        );
    }

    static function register_taxonomies() {
        // se for usar, descomentar //'taxonomies' => array('taxonomia') do post type (logo acima)

        $labels = array(
            'name' => _x('Taxonomias', 'taxonomy general name', 'SLUG'),
            'singular_name' => _x('Taxonomia', 'taxonomy singular name', 'SLUG'),
            'search_items' => __('Search Taxonomias', 'SLUG'),
            'all_items' => __('All Taxonomias', 'SLUG'),
            'parent_item' => __('Parent Taxonomia', 'SLUG'),
            'parent_item_colon' => __('Parent Taxonomia:', 'SLUG'),
            'edit_item' => __('Edit Taxonomia', 'SLUG'),
            'update_item' => __('Update Taxonomia', 'SLUG'),
            'add_new_item' => __('Add New Taxonomia', 'SLUG'),
            'new_item_name' => __('New Taxonomia Name', 'SLUG'),
        );

        register_taxonomy('taxonomia', self::$post_type, array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => true,
                )
        );
    }

    static function change_menu_label($stuff) {
        global $menu, $submenu;
        foreach ($menu as $i => $mi) {
            if ($mi[0] == self::NAME) {
                $menu[$i][0] = self::MENU_NAME;
            }
        }
        return $stuff;
    }

    static function custom_menu_order() {
        return true;
    }

    /**
     * Chamado pelo hook save_post
     * @param int $post_id
     * @param object $post
     */
    static function on_save($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;
        
        global $post;
        
        if ($post->post_type == self::$post_type) {
            // faça algo com o post 
        }
    }
    
/**
 *  ================= METABOXES PRONTOS ================= *
 */
   

    
// ========================================= VIDEO ============================================= //
    
    static function register_metabox_video() {
        $titulo = self::$meta_cfg['video']['title'];

        add_meta_box(
                'metabox_video', 
                $titulo, 
                array(__CLASS__, 'metabox_video'), 
                self::$post_type, // em que post type eles entram?
                'normal' // onde? side, normal, advanced
                //,'default' // 'high', 'core', 'default' or 'low'
                //,array('variáve' => 'valor') // variaveis que serão passadas para o callback
        );
    }

    static function metabox_video() {
        global $post;

        // Post type do relacionamento
        $meta_name = self::$meta_cfg['video']['meta_name'];
        
        // Use nonce for verification
        wp_nonce_field("save_metabox_video_" . __CLASS__, "metabox_video_noncename_" . __CLASS__);

        $meta = get_post_meta($post->ID, $meta_name, true);
        ?>

        <p><label>Vídeo URL: <input type="text" name="<?php echo $meta_name ?>" value="<?php echo $meta ?>" /></label></p>
        
        <?php
    }

    static function metabox_save_video($post_id) {
        $meta_name = self::$meta_cfg['video']['meta_name'];
        
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if (!wp_verify_nonce($_POST['metabox_video_noncename_' . __CLASS__], 'save_metabox_video_' . __CLASS__))
            return;


        // Check permissions
        if ($_POST['post_type'] != self::$post_type) {
            return;
        }

        if (!current_user_can('edit_post', $post_id))
            return;

        // OK, we're authenticated: we need to find and save the data

        update_post_meta($post_id, $meta_name, $_POST[$meta_name]);
    }
    
    
    


    
    
    
    


// ========================================= REDES SOCIAIS ============================================= //
    
    static function register_metabox_redes_sociais() {
        $titulo = self::$meta_cfg['redes_sociais']['title'];

        add_meta_box(
                'metabox_redes_sociais', 
                $titulo, 
                array(__CLASS__, 'metabox_redes_sociais'), 
                self::$post_type, // em que post type eles entram?
                'normal' // onde? side, normal, advanced
                //,'default' // 'high', 'core', 'default' or 'low'
                //,array('variáve' => 'valor') // variaveis que serão passadas para o callback
        );
    }

    static function metabox_redes_sociais() {
        global $post;

        // Post type do relacionamento
        $meta_name = self::$meta_cfg['redes_sociais']['meta_name'];
        
        // Use nonce for verification
        wp_nonce_field("save_metabox_redes_sociais_" . __CLASS__, "metabox_redes_sociais_noncename_" . __CLASS__);

        $meta = get_post_meta($post->ID, $meta_name, true);
        ?>

        <?php if(in_array('facebook', self::$meta_cfg['redes_sociais']['redes'])): ?>
            <p><label>Facebook: <input type="text" name="<?php echo $meta_name ?>[facebook]" value="<?php echo isset($meta['facebook']) ? $meta['facebook'] : ''?>" /></label></p>
        <?php endif; ?>
        
        <?php if(in_array('google', self::$meta_cfg['redes_sociais']['redes'])): ?>
            <p><label>Google +: <input type="text" name="<?php echo $meta_name ?>[google]" value="<?php echo isset($meta['google']) ? $meta['google'] : ''?>" /></label></p>
        <?php endif; ?>
            
        <?php if(in_array('twitter', self::$meta_cfg['redes_sociais']['redes'])): ?>
            <p><label>Twitter: <input type="text" name="<?php echo $meta_name ?>[twitter]" value="<?php echo isset($meta['twitter']) ? $meta['twitter'] : ''?>" /> (nome do usuário sem "@")</label></p>
        <?php endif; ?>
            
        <?php if(in_array('myspace', self::$meta_cfg['redes_sociais']['redes'])): ?>
            <p><label>MySpace: <input type="text" name="<?php echo $meta_name ?>[myspace]" value="<?php echo isset($meta['myspace']) ? $meta['myspace'] : ''?>" /></label></p>
        <?php endif; ?>
            
        <?php if(in_array('flickr', self::$meta_cfg['redes_sociais']['redes'])): ?>
            <p><label>Flickr: <input type="text" name="<?php echo $meta_name ?>[flickr]" value="<?php echo isset($meta['flickr']) ? $meta['flickr'] : ''?>" /></label></p>
        <?php endif; ?>

        <?php
    }

    static function metabox_save_redes_sociais($post_id) {
        $meta_name = self::$meta_cfg['redes_sociais']['meta_name'];
        
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if (!wp_verify_nonce($_POST['metabox_redes_sociais_noncename_' . __CLASS__], 'save_metabox_redes_sociais_' . __CLASS__))
            return;


        // Check permissions
        if ($_POST['post_type'] != self::$post_type) {
            return;
        }

        if (!current_user_can('edit_post', $post_id))
            return;

        // OK, we're authenticated: we need to find and save the data

        update_post_meta($post_id, $meta_name, $_POST[$meta_name]);
    }
    
    
    


    
    
    
    


// ========================================= POST RELACIONADO ============================================= //
    
    static function register_metabox_post_relacionado() {
        $titulo = self::$meta_cfg['post_relacionado']['title'];

        add_meta_box(
                'metabox_post_relacionado', 
                $titulo, 
                array(__CLASS__, 'metabox_post_relacionado'), 
                self::$post_type, // em que post type eles entram?
                'normal' // onde? side, normal, advanced
                //,'default' // 'high', 'core', 'default' or 'low'
                //,array('variáve' => 'valor') // variaveis que serão passadas para o callback
        );
    }

    static function metabox_post_relacionado() {
        global $post;
        
        // Post type do relacionamento
        $post_type_relacionado = self::$meta_cfg['post_relacionado']['post_type_relacionado'];
        $meta_name = self::$meta_cfg['post_relacionado']['meta_name'];
        
        // Use nonce for verification
        wp_nonce_field("save_metabox_post_relacionado_" . __CLASS__, "metabox_post_relacionado_noncename_" . __CLASS__);

        

        $__post_id = get_post_meta($post->ID, $meta_name, true);

        $__posts = get_posts("numberposts=-1&post_type={$post_type_relacionado}");
        ?>
        <label> selecione o <?php echo $post_type_relacionado; ?> : 
            <select name="<?php echo $meta_name ?>">
                <option value=""></option>
        <?php foreach ($__posts as $__post): ?>
                    <option value="<?php echo $__post->ID; ?>" <?php if ($__post->ID == $__post_id) echo 'selected="selected"' ?>><?php echo $__post->post_title; ?></option>
        <?php endforeach; ?>
            </select>
        </label>
        <?php
    }

    static function metabox_save_post_relacionado($post_id) {
        $post_type_relacionado = self::$meta_cfg['post_relacionado']['post_type_relacionado'];
        $meta_name = self::$meta_cfg['post_relacionado']['meta_name'];
        
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        
        if (!wp_verify_nonce($_POST['metabox_post_relacionado_noncename_' . __CLASS__], 'save_metabox_post_relacionado_' . __CLASS__))
            return;

        
        // Check permissions
        if ($_POST['post_type'] != self::$post_type) {
            return;
        }

        if (!current_user_can('edit_post', $post_id))
            return;


        // OK, we're authenticated: we need to find and save the data

        
        
        update_post_meta($post_id, $meta_name, trim($_POST[$meta_name]));
    }

}

_POST_TYPE_::init();
