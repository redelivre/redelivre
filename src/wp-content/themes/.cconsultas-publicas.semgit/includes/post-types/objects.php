<?php
class ObjectPostType {

    static function init(){
        add_action('init', array(__CLASS__, 'register'), 0);
        add_action('init', array(__CLASS__, 'register_taxonomies'), 0);
        add_action('save_post', array(__CLASS__, 'savePost'));
    }
    
    static function get_default_labels() {
        return array(
                    'name' => __('Objetos', 'consulta'),
                    'singular_name' => __('Objeto', 'consulta'),
                    'add_new' => __('Adicionar novo', 'consulta'),
                    'add_new_item' => __('Adicionar novo objeto', 'consulta'),
                    'edit_item' => __('Editar objeto', 'consulta'),
                    'new_item' => __('Novo objeto', 'consulta'),
                    'view_item' => __('Ver objeto', 'consulta'),
                    'search_items' => __('Buscar objetos', 'consulta'),
                    'not_found' =>  __('Nenhum objeto encontrado', 'consulta'),
                    'not_found_in_trash' => __('Nenhum objeto na lixeira', 'consulta'),
                 );
    }

    static function register(){
        register_post_type('object', array(
                 'labels' => wp_parse_args(get_theme_option('object_labels'), self::get_default_labels()),
                 'public' => true,
                 'rewrite' => array('slug' => get_theme_option('object_url')),
                 'capability_type' => 'post',
                 'hierarchical' => false,
                 'map_meta_cap' => true,
                 'menu_position' => 6,
                 'has_archive' => true,
                 'supports' => array(
                     	'title',
                     	'editor',
                     	'excerpt',
                     	'comments',
                 ),
            )
        );
    }
    
    static function get_taxonomy_default_labels() {
        return array(
            'name' => __('Tipos de objeto', 'consulta'),
            'singular_name' => __('Tipo de objeto', 'consulta'),
            'search_items' =>  __('Buscar tipos', 'consulta'),
            'all_items' => __('Todos os tipos', 'consulta'),
            'parent_item' => __('Tipo pai', 'consulta'),
            'parent_item_colon' => __('Tipo pai:', 'consulta'),
            'edit_item' => __('Editar tipo', 'consulta'),
            'update_item' => __('Atualizar tipo', 'consulta'),
            'add_new_item' => __('Adicionar novo tipo', 'consulta'),
            'new_item_name' => __('Nome do novo tipo', 'consulta'),
        ); 	
    }
    
    /**
     * Retorna o valor de um label. Se não uma chave é passada
     * retorna o valor de todos os labels.
     * 
     * @param null|string $key
     * @return array|string
     */    
    static function get_taxonomy_label($key = null) {
        $labels = wp_parse_args(get_theme_option('taxonomy_labels'), self::get_taxonomy_default_labels());
        
        if (is_null($key)) {
            return $labels;
        } else if (isset($labels[$key])) {
            return $labels[$key];
        } else {
            throw Exception("Chave $key não existe.");
        }
    }
    
    static function register_taxonomies(){
        $post_types = array('object');
        
        if (get_theme_option('enable_taxonomy')) {
            
            register_taxonomy('object_type', $post_types, array(
                    'hierarchical' => true,
                    'labels' => self::get_taxonomy_label(),
                    'show_ui' => true,
                    'query_var' => true,
                    'rewrite' => array('slug' => get_theme_option('taxonomy_url')),
                )
            );
            
        }
    }
    
    /**
     * Chamado sempre que um post é salvo
     * 
     * @return null
     */
    static function savePost($postId) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if ((isset($_POST['post_type']) && $_POST['post_type'] != 'object') || !is_admin()) {
            return;
        }
        
        update_post_meta($postId, '_user_created', false);
    }
}

ObjectPostType::init();

add_action('restrict_manage_posts', 'consulta_restrict_listings');
/**
 * Na listagem de objetos no admin adiciona uma opção para exibir
 * somente os objetos criados pelos admins ou então os objetos criados
 * pelos demais usuários.
 * 
 * @return null
 */
function consulta_restrict_listings() {
    global $typenow;
    global $wp_query;
     
    if ($typenow == 'object' && get_theme_option('allow_suggested')) {
        ?>
        <select class="postform" id="who_created" name="who_created">
            <option value="all" <?php isset($_REQUEST['who_created']) ? selected('all', $_REQUEST['who_created']) : ''; ?>>Todos os objetos</option>
            <option value="admin_created" <?php isset($_REQUEST['who_created']) ? selected('admin_created', $_REQUEST['who_created']) : ''; ?>>Objetos criados pelos admins</option>
            <option value="user_created" <?php isset($_REQUEST['who_created']) ? selected('user_created', $_REQUEST['who_created']) : ''; ?>>Objetos criados pelos usuários</option>
        </select>
        <?php
   }
}

add_action('pre_get_posts', 'consulta_filter_by_user_created');
/**
 * Na listagem de objetos no admin permite exibir somente
 * objetos criados pelos admins ou somente objetos criados pelos
 * demais usuários.
 * 
 * @param unknown $query
 * @return null
 */
function consulta_filter_by_user_created($query) {
    global $pagenow, $typenow;
    
    if ($pagenow == 'edit.php' && $typenow == 'object' && isset($_REQUEST['who_created']) && $_REQUEST['who_created'] != 'all') {
        if ($_REQUEST['who_created'] == 'user_created') {
            $user_created = true;
        } else {
            $user_created = false;
        }

        $query->set('meta_key', '_user_created');
        $query->set('meta_value', $user_created);
    }
}