<?php

// includes
include('admin/maps.php');
include('admin/pins.php');
include('admin/theme.php');
include('admin/metabox.php');
include('template/ajax.php');

add_action('init', function() { 

    global $current_blog, $campaign;
    
    if (!$campaign) {
        return;
    }
    
    $capabilities = Capability::getByPlanId($campaign->plan_id);
    
    if ($current_blog->blog_id > 1 && isset($capabilities->georreferenciamento) && $capabilities->georreferenciamento->value == 1 )  { 
        mapasdevista_regiser_post_type(); 
        add_action( 'admin_menu', 'mapasdevista_admin_menu' );
        add_action( 'admin_init', 'mapasdevista_admin_init' );
    } else {
        return;
    }
    
    // activate for each blog:
    if (get_option('mapasdevista_activaded') != 4) {
        update_option('mapasdevista_activaded', 4);
        mapasdevista_set_default_settings();
        mapasdevista_flush_rules();
        mapasdevista_set_default_menu();
        include('import-default-pins.php');
    }
    
});

function mapasdevista_set_default_menu() {

    $menu = get_term_by('slug', 'main', 'nav_menu');
    if ($menu && is_object($menu) && !is_wp_error($menu)) {
        $current = get_theme_mod( 'nav_menu_locations' );
        $current['mapasdevista_top'] = $menu->term_id;
        set_theme_mod( 'nav_menu_locations', $current );
    }    
}

function mapasdevista_set_default_settings() {

    $defaults = array(
        
        'name' => 'Mapa',
        'api' => 'googlev3',
        'type' => 'road',
        'coord' => Array
            (
                'lat' => '-15.050826166796774',
                'lng' => '-54.4263372014763'
            ),

        'zoom' => 4,
        'control' => Array
            (
                'zoom' => 'large',
                'pan' => 'on',
                'map_type' => 'on'
            ),
        'logical_operator' => 'OR',
        'post_types' => array('mapa'),
        'taxonomies' => array('categoria-mapa'),
        'visibility' => 'private'
    
    );
    
    update_option('mapasdevista', $defaults);

}

function mapasdevista_flush_rules() {

    global $wp_rewrite;
    $wp_rewrite->flush_rules();

}

load_plugin_textdomain( 'mapasdevista', WP_CONTENT_DIR . '/plugins/mapasdevista/languages/', basename(dirname(__FILE__)) . '/languages/' );

add_action( 'after_setup_theme', 'mapasdevista_setup' );
if ( ! function_exists( 'mapasdevista_setup' ) ):

    function mapasdevista_setup() {

        // Post Format support. You can also use the legacy "gallery" or "asides" (note the plural) categories.
        add_theme_support( 'post-formats', array( 'gallery', 'image', 'video' , 'audio'  ) );

        // This theme uses post thumbnails
        add_theme_support( 'post-thumbnails' );


        // This theme uses wp_nav_menu() in one location.
        register_nav_menus( array(
            'mapasdevista_top' => __( 'Map Menu (top)', 'mapasdevista' ),
            'mapasdevista_side' => __( 'Map Menu (side)', 'mapasdevista' )
        ) );
        
        add_image_size('mapasdevista-thumbnail',270,203,true);

    }

endif;



function mapasdevista_admin_menu() {

    add_submenu_page('edit.php?post_type=mapa', __('Configuração do mapa', 'mapasdevista'), __('Configuração do mapa', 'mapasdevista'), 'publish_posts', 'mapasdevista_maps', 'mapasdevista_maps_page');
    //add_menu_page(__('Maps of view', 'mapasdevista'), __('Maps of view', 'mapasdevista'), 'publish_posts', 'mapasdevista_maps', 'mapasdevista_maps_page',null,30);
    add_submenu_page('edit.php?post_type=mapa', __('Layout', 'mapasdevista'), __('Layout', 'mapasdevista'), 'publish_posts', 'mapasdevista_theme_page', 'mapasdevista_theme_page');
    add_submenu_page('edit.php?post_type=mapa', __('Pins', 'mapasdevista'), __('Pins', 'mapasdevista'), 'publish_posts', 'mapasdevista_pins_page', 'mapasdevista_pins_page');

    add_submenu_page('edit.php?post_type=mapa', __('Importar Sql', 'mapasdevista'), __('Importar Sql', 'mapasdevista'), 'publish_posts', 'ImportarSql', 'mapasdevista_ImportarSql');
}


function mapasdevista_ImportarSql()
{
	ini_set("memory_limit", "2048M");
	set_time_limit(0);
	
	global $wpdb;
	
	$query = "SELECT * FROM mapacompleto;";
	
	//$wpdb->query($query);
	
	$rows = $wpdb->get_results($query);
	
	foreach ($rows as $row)
	{
		$post = array(
				'post_author'    => 1, //The user ID number of the author.
				'post_content'   => $row->text,
				'post_title'     => $row->proj, //The title of your post.
				'post_type'      => 'mapa',
				'post_status'	 => 'publish'
		);
		
		$post_id = wp_insert_post($post);
		
		if( is_int($post_id) )
		{
			$location = array();
			$location['lat'] = floatval(sprintf("%f", $row->lat));
			$location['lon'] = floatval(sprintf("%f", $row->lon));
			
			if($location['lat'] !== floatval(0) && $location['lon'] !== floatval(0))
			{
				update_post_meta($post_id, '_mpv_location', $location);
			}
			else
			{
				delete_post_meta($post_id, '_mpv_location');
			}
			
			$pin_id = substr($row->icon, 4);
			
			$pin_id = intval(sprintf("%d", $pin_id));
			
			if($pin_id > 0)
			{
				update_post_meta($post_id, '_mpv_pin', $pin_id);
			}
			
			delete_post_meta($post_id, '_mpv_inmap');
			delete_post_meta($post_id, '_mpv_in_img_map');
			add_post_meta($post_id, "_mpv_inmap", 1);
			
		}
		
	}
	
	//echo '<pre>'.var_dump($rows).'</pre>';
	
}


function mapasdevista_admin_init() {
    
    
    
    global $pagenow;
    
    if( ($pagenow === "post.php" || $pagenow === "post-new.php" || (isset($_GET['page']) && $_GET['page'] === "mapasdevista_maps")) ) {
        // api do google maps versao 3 direto 
        $googleapikey = get_mapasdevista_theme_option('google_key');
        $googleapikey = $googleapikey ? "&key=$googleapikey" : '';
        wp_enqueue_script('google-maps-v3', 'http://maps.google.com/maps/api/js?sensor=false' . $googleapikey);

        wp_enqueue_script('openlayers', 'http://openlayers.org/api/OpenLayers.js');

        wp_enqueue_script('mapstraction', mapasdevista_get_baseurl() . '/js/mxn/mxn-min.js' );
        wp_enqueue_script('mapstraction-core', mapasdevista_get_baseurl() . '/js/mxn/mxn.core-min.js');
        wp_enqueue_script('mapstraction-googlev3', mapasdevista_get_baseurl() . '/js/mxn/mxn.googlev3.core-min.js');
        wp_enqueue_script('mapstraction-openlayers', mapasdevista_get_baseurl() . '/js/mxn/mxn.openlayers.core-min.js');
    }
    
    if (isset($_GET['page']) && $_GET['page'] === "mapasdevista_theme_page") {
        
        wp_enqueue_script('jcolorpicker', mapasdevista_get_baseurl() . '/admin/colorpicker/js/colorpicker.js', array('jquery') );
        wp_enqueue_style('colorpicker', mapasdevista_get_baseurl() . '/admin/colorpicker/css/colorpicker.css' );
        wp_enqueue_script('mapasdevista_theme_options', mapasdevista_get_baseurl() . '/admin/mapasdevista_theme_options.js', array('jquery', 'jcolorpicker') );
    
    }

    if($pagenow === "post.php" || $pagenow === "post-new.php") {
        wp_enqueue_script('metabox', mapasdevista_get_baseurl() . '/admin/metabox.js' );
    } elseif(isset($_GET['page']) && $_GET['page'] === 'mapasdevista_pins_page') {
        wp_enqueue_script('metabox', mapasdevista_get_baseurl() . '/admin/pins.js' );
    }


    wp_enqueue_style('mapasdevista-admin', mapasdevista_get_baseurl('template_directory') . '/admin/admin.css');
}

/* Page Template redirect */

function mapasdevista_regiser_post_type() {

    register_post_type('mapa', array(
        'labels' => array(
            'name' => 'Itens do Mapa',
            'singular_name' => 'Item do Mapa',
            'add_new' => 'Novo Item',
            'add_new_item' => 'Adicionar novo Item no mapa',
            'edit_item' => 'Editar',
            'new_item' => 'Novo item do mapa',
            'view_item' => 'Ver item do mapa',
            'search_items' => 'Search Buscar item do mapa',
            'not_found' => 'Nenhum Item no mapa',
            'not_found_in_trash' => 'Nenhum item do mapa na Lixeira',
            'parent_item_colon' => ''
        ),
        'public' => true,
        'rewrite' => array('slug' => 'mapa'),
        'capability_type' => 'post',
        'hierarchical' => false,
        'map_meta_cap ' => true,
        //'menu_position' => 6,
        'has_archive' => false, //se precisar de arquivo
        'supports' => array(
            'title',
            'editor',
            'post-formats',
            'thumbnail'
        ),
           
        )
    );
    
    // Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
        'name' => 'Categorias',
        'singular_name' => 'Categoria',
        'search_items' =>  'Buscar categorias',
        'all_items' => 'Todas as categorias',
        'parent_item' => 'Categoria mãe',
        'parent_item_colon' => 'Categoria mãe:',
        'edit_item' => 'Editar categoria', 
        'update_item' => 'Atualizar categoria',
        'add_new_item' => 'Adicionar nova categoria',
        'new_item_name' => 'Nome da nova categoria',
        'menu_name' => 'Categorias',
    ); 	

    register_taxonomy('categoria-mapa',array('mapa'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        //'rewrite' => false,
    ));

}

//add_post_type_support( 'mapa', array('post-formats', 'post-thumbnails') );

function mapasdevista_base_custom_query_vars($public_query_vars) {
    $public_query_vars[] = "mapa-tpl";

    return $public_query_vars;
}

// REDIRECIONAMENTOS
function mapasdevista_base_custom_url_rewrites($rules) {
    $new_rules = array(
        "mapa/?$" => "index.php?mapa-tpl=mapa",
    );

    return $new_rules + $rules;
}



function mapasdevista_page_template_redirect() {
    global $wp_query;
    
    $mapinfo = get_option('mapasdevista', true);


    
    if ($wp_query->get('mapa-tpl')  ) {
        if ( $mapinfo['visibility'] == 'public' || current_user_can('edit_posts')) {
            mapasdevista_get_template('template/main-template');
            exit;
        }
        else
            $wp_query->is_404 = true;
        
    }
}

add_filter('query_vars', 'mapasdevista_base_custom_query_vars');
add_filter('rewrite_rules_array', 'mapasdevista_base_custom_url_rewrites', 10, 1);
add_action('template_redirect', 'mapasdevista_page_template_redirect');

function mapasdevista_get_template($file, $context = null, $load = true) {
    
    $templates = array();
	if ( !is_null($context) )
		$templates[] = "{$file}-{$context}.php";

	$templates[] = "{$file}.php";
    
    if (preg_match('|/wp-content/themes/|', __FILE__)) {
        $found = locate_template($templates, $load, false);
    } else {
        $f = is_null($context) || empty($context) || strlen($context) == 0 ? $file : $file . '-'. $context ;
        $file = $file . '.php';
        $f = $f . '.php';
        
        if (
            file_exists(TEMPLATEPATH . '/' . $f) ||
            file_exists(STYLESHEETPATH . '/' . $f) ||
            file_exists(TEMPLATEPATH . '/' . $file) ||
            file_exists(STYLESHEETPATH . '/' . $file) 
            ) {
            $found = locate_template($templates, $load, false);
        } else {
            $f = WP_CONTENT_DIR . '/plugins/mapasdevista/' . $f;
            if ($load)
                include $f;
            else
                $found = $f;
        }
            
    }
    
    return $found;
    
}

function mapasdevista_get_baseurl() {
    
    if (preg_match('|[\\\/]wp-content[\\\/]themes[\\\/]|', __FILE__))
        return get_bloginfo('template_directory') . '/';
    else
        return plugins_url('mapasdevista') . '/';
}


// COMMENTS

if (!function_exists('mapasdevista_comment')): 

function mapasdevista_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;  
    ?>
    <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">        

        <p class="comment-meta alignright bottom">
          <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'mapasdevista'), '| ', ''); ?>          
        </p>
        <div class="comment-entry clearfix">
            <div class="alignleft"><?php echo get_avatar($comment, 66); ?></div>
            <p class="comment-meta bottom">
              <?php printf( __('By <strong>%s</strong> on <strong>%s</strong> at <strong>%s</strong>.', 'mapasdevista'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
              <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'mapasdevista'); ?></em><?php endif; ?>
            </p>
            <?php comment_text(); ?>
        </div>

    </li>
    <?php
}

endif; 


// IMAGES
function mapasdevista_get_image($name) {
    return mapasdevista_get_baseurl() . '/img/' . $name;
}

function mapasdevista_image($name, $params = null) {
    $extra = '';

    if(is_array($params)) {
        foreach($params as $param=>$value){
            $extra.= " $param=\"$value\" ";		
        }
    }

    echo '<img src="', mapasdevista_get_image($name), '" ', $extra ,' />';
}




/**
 * 
 * @global WP_Query $MAPASDEVISTA_POSTS_RCACHE
 * @param int $page_id
 * @param array $mapinfo
 * @param array $postsArgs
 * @return WP_Query 
 */
function mapasdevista_get_posts($page_id, $mapinfo, $postsArgs = array()){
    global $MAPASDEVISTA_POSTS_RCACHE;
    
    if(is_object($MAPASDEVISTA_POSTS_RCACHE) && get_class($MAPASDEVISTA_POSTS_RCACHE) === 'WP_Query'){
        
        $MAPASDEVISTA_POSTS_RCACHE->rewind_posts();
        return $MAPASDEVISTA_POSTS_RCACHE;
    }else{
        
        if ($mapinfo['api'] == 'image') {
            
            $postsArgs += array(
                    'posts_per_page'     => -1,
                    'orderby'         => 'post_date',
                    'order'           => 'DESC',
                    'meta_key'        => '_mpv_in_img_map',
                    'meta_value'      => $page_id,
                    'post_type'       => $mapinfo['post_types'],
                    'ignore_sticky_posts' => true
                );


        } else {

            $postsArgs += array(
                        'posts_per_page'     => -1,
                        'orderby'         => 'post_date',
                        'order'           => 'DESC',
                        'meta_key'        => '_mpv_inmap',
                        'meta_value'      => $page_id,
                        'post_type'       => $mapinfo['post_types'],
                        'ignore_sticky_posts' => true
                    );
        }

        if (isset($_GET['mapasdevista_search']) && $_GET['mapasdevista_search'] != '')
            $postsArgs['s'] = $_GET['mapasdevista_search'];
        
        $MAPASDEVISTA_POSTS_RCACHE = new WP_Query($postsArgs); 
        
        return $MAPASDEVISTA_POSTS_RCACHE;
    }
}

add_filter('the_content', 'mapasdevista_gallery_filter');
function mapasdevista_gallery_filter($content){
    return str_replace('[gallery]', '[gallery link="file"]', $content);
}
