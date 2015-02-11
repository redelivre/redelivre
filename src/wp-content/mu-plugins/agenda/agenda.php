<?php
//POST TYPE DE EVENTOS DA AGENDA
class Agenda {
    const NAME = 'Evento';
    const MENU_NAME = 'Eventos';

    static function init(){
        add_action('init', array(__CLASS__, 'register') ,0);
        add_filter('menu_order', array(__CLASS__, 'change_menu_label'));
        add_filter('custom_menu_order', array(__CLASS__, 'custom_menu_order'));
        add_action('add_meta_boxes', array(__CLASS__,'add_custom_box') );
        add_action('save_post', array(__CLASS__,'save_postdata' ));
        
    }
    
    static function register()
    {
    	load_muplugin_textdomain( 'redelivre', 'agenda/languages' );
    	
        register_post_type('agenda', array(
                
                'labels' => array(
                                    'name' => _x('Evento', 'post type general name', 'redelivre'),
                                    'singular_name' => _x('Evento', 'post type singular name', 'redelivre'),
                                    'add_new' => _x('Adicionar novo', 'image', 'redelivre'),
                                    'add_new_item' => __('Adicionar novo evento', 'redelivre'),
                                    'edit_item' => __('Editar evento', 'redelivre'),
                                    'new_item' => __('Novo evento', 'redelivre'),
                                    'view_item' => __('Ver evento', 'redelivre'),
                                    'search_items' => __('Buscar eventos', 'redelivre'),
                                    'not_found' =>  __('Nenhum evento encontrado', 'redelivre'),
                                    'not_found_in_trash' => __('Nenhum evento encontrado na lixeira', 'redelivre'),
                                    'parent_item_colon' => ''
                                 ),
                 'public' => true,
                 'rewrite' => true,
                 'capability_type' => 'post',
                 'hierarchical' => false,
                 'menu_position' => 10,
                 'has_archive' => true,
                 'supports' => array(
                     	'title',
                        'editor',
                        'thumbnail',
                     	
                 ),
                 
                 
            )
        );
       
    }
    
    
    /* Adds a box to the main column on the Post and Page edit screens */
    static function add_custom_box() {
        
        add_meta_box( 
            'agenda_data',
            __('Dados do Evento', 'redelivre'),
            array(__CLASS__,'inner_custom_box_callback_function'),
            'agenda', // em que post type eles entram?
            'normal' // onde? side, normal, advanced
            //,'default' // 'high', 'core', 'default' or 'low'
            //,array('variáve' => 'valor') // variaveis que serão passadas para o callback
        );
    }

    /* Prints the box content */
    static function inner_custom_box_callback_function() {
        global $post;
        
        // Use nonce for verification
        wp_nonce_field( 'save_agenda', 'agenda_noncename' );
        
        $data_inicial = get_post_meta($post->ID, '_data_inicial', true);
        if ($data_inicial) $data_inicial = date('d/m/Y', strtotime($data_inicial));
        
        $data_final = get_post_meta($post->ID, '_data_final', true);
        if ($data_final) $data_final = date('d/m/Y', strtotime($data_final));
        
        $link = get_post_meta($post->ID, '_link', true);
        $onde = get_post_meta($post->ID, '_onde', true);
        $horario = get_post_meta($post->ID, '_horario', true);
     
        // The actual fields for data entry
        
        echo '<table>';
            echo '<tr>';
                echo '<td><b><label for="_data_inicial">'.__('Data Inicial', 'redelivre').' </label></td>';
                echo "<td><input type='text' id='_data_inicial' name='_data_inicial' value='$data_inicial' size='25' />";
                echo '<small> ('.__('ex.: 01/01/2011', 'redelivre').')</small>';
                echo '</td>';
            
            echo '</tr>';
            
            echo '<tr>';
                echo '<td><b><label for="_data_inicial">'.__('Data Final', 'redelivre').' </label></td>';
                echo "<td><input type='text' id='_data_final' name='_data_final' value='$data_final' size='25' /></td>";
            
            echo '</tr>';
            
            echo '<tr>';
                echo '<td><b><label for="_horario">'.__('Horário', 'redelivre').' </label></td>';
                echo "<td><input type='text' id='_horario' name='_horario' value='$horario' size='25' /></td>";
            
            echo '</tr>';
            
            echo '<tr>';
                echo '<td><b><label for="_link">'.__('Link Externo', 'redelivre').' </label></td>';
                echo "<td><input type='text' id='_link' name='_link' value='$link' size='25' /></td>";
            
            echo '</tr>';
            
            echo '<tr>';
                echo '<td><b><label for="_onde">'.__('Onde', 'redelivre').'</label></td>';
                echo "<td><input type='text' id='_onde' name='_onde' value='$onde' size='25' /></td>";
            
            echo '</tr>';
            
        echo '</table>';
        ?>
        <script type="text/javascript">
		<!--
	        var opts = {
			closeText: "<?php _e('Fechar', 'redelivre'); ?>",
			prevText: "<?php _e('Anterior', 'redelivre'); ?>",
			nextText: "<?php _e('Próximo', 'redelivre'); ?>",
			currentText: "<?php _e('Hoje', 'redelivre'); ?>",
			monthNames: [<?php echo "'".
				__("Janeiro", "sbc")."','".
				__("Fevereiro", "sbc")."','".
				__("Março", "sbc")."','".
				__("Abril", "sbc")."','".
				__("Maio", "sbc")."','".
				__("Junho", "sbc")."','".
				__("Julho", "sbc")."','".
				__("Agosto", "sbc")."','".
				__("Setembro", "sbc")."','".
				__("Outubro", "sbc")."','".
				__("Novembro", "sbc")."','".
				__("Dezembro", "sbc")."'"; ?>
			],
			monthNamesShort: [<?php echo "'".
				__("Jan", "sbc")."','".
				__("Fev", "sbc")."','".
				__("Mar", "sbc")."','".
				__("Abr", "sbc")."','".
				__("Mai", "sbc")."','".
				__("Jun", "sbc")."','".
				__("Jul", "sbc")."','".
				__("Ago", "sbc")."','".
				__("Set", "sbc")."','".
				__("Out", "sbc")."','".
				__("Nov", "sbc")."','".
				__("Dez", "sbc")."'"
			;?>],
			dayNames: [ <?php echo "'".
				__('Domingo', "sbc")."','".
				__('Segunda-feira', "sbc")."','".
				__('Ter&ccedil;a-feira', "sbc")."','".
				__('Quarta-feira', "sbc")."','".
				__('Quinta-feira', "sbc")."','".
				__('Sexta-feira', "sbc")."','".
				__('S&aacute;bado', "sbc")."'"
			;?>],
			<?php $dayNamesShort = "'".
					__('Dom', "sbc")."','".
					__('Seg', "sbc")."','".
					__('Ter', "sbc")."','".
					__('Qua', "sbc")."','".
					__('Qui', "sbc")."','".
					__('Sex', "sbc")."','".
					__('S&aacute;b', "sbc")."'"
			;?>
			dayNamesShort: [<?php echo $dayNamesShort;?>],
			dayNamesMin: [<?php echo $dayNamesShort;?>],
			weekHeader: "<?php echo _x('Sm', 'week header', 'redelivre');?>" ,
			dateFormat: 'dd/mm/yy',
			firstDay: 0,
			isRTL: false,
			showMonthAfterYear: false,
			yearSuffix: ''};
	        
	        jQuery(document).ready(function() {
	            jQuery('#_data_inicial, #_data_final').datepicker( opts );
	        });
        
	    //-->
		</script>
        <?php
        
    }

    /* When the post is saved, saves our custom data */
    static function save_postdata( $post_id ) {
        
        $data_inicial = '_data_inicial';
        $data_final = '_data_final';
        $link = '_link';
        $onde = '_onde';
        $horario = '_horario';
        
        // not agenda post type
        if (!isset($_POST['agenda_noncename'])) {
            return;
        }
        
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if ( !wp_verify_nonce( $_POST['agenda_noncename'], 'save_agenda' ) )
            return;


        // Check permissions
        if ( 'page' == $_POST['post_type'] ) 
        {
        if ( !current_user_can( 'edit_page', $post_id ) )
            return;
        }
        else
        {
        if ( !current_user_can( 'edit_post', $post_id ) )
            return;
        }

        $final_date_en = false;
        $initial_date_en = false;
        
        // OK, we're authenticated: we need to find and save the data
        if (array_key_exists($data_inicial, $_POST) && !empty($_POST[$data_inicial]))
        {
	        $initial_date_pt = explode('/', trim($_POST[$data_inicial]));
	        $initial_date_en = $initial_date_pt[2].'-'.$initial_date_pt[1].'-'.$initial_date_pt[0];
        }
        
        if (array_key_exists($data_final, $_POST) && !empty($_POST[$data_final])) {
            $final_date_pt = explode('/', trim($_POST[$data_final]));
            $final_date_en = $final_date_pt[2].'-'.$final_date_pt[1].'-'.$final_date_pt[0];
        }
        else 
            $final_date_en = $initial_date_en;
        
        if (array_key_exists($data_inicial, $_POST) && !empty($_POST[$data_inicial]))
        {
        	update_post_meta($post_id, $data_inicial, date('Y-m-d h:i', strtotime($initial_date_en)));
        }
        if (array_key_exists($data_final, $_POST) && !empty($_POST[$data_final]))
        {
        	update_post_meta($post_id, $data_final, date('Y-m-d h:i', strtotime($final_date_en)));
        }
        update_post_meta($post_id, $link, trim($_POST[$link]));
        update_post_meta($post_id, $onde, trim($_POST[$onde]));
        update_post_meta($post_id, $horario, trim($_POST[$horario]));

        
    }
    
    static function change_menu_label($stuff) {
        global $menu,$submenu;
        foreach ($menu as $i=>$mi){
            if($mi[0] == self::NAME){
                $menu[$i][0] = self::MENU_NAME;
            }
        }
        return $stuff;
    }
    
    static function custom_menu_order() {
        return true;
    }

}

Agenda::init();

add_action('pre_get_posts', 'campanha_agenda_query');
function campanha_agenda_query($wp_query) {
    
    if (is_admin()) return;
    
    if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'agenda' && is_post_type_archive('agenda')) {
        
        
        if (!isset($wp_query->query_vars['meta_query']) || !is_array($wp_query->query_vars['meta_query'])) {
            $wp_query->query_vars['meta_query'] = array();
        }
        
        $wp_query->query_vars['orderby'] = 'meta_value';
        $wp_query->query_vars['order'] = 'ASC';
        $wp_query->query_vars['meta_key'] = '_data_inicial';
        
        if ($wp_query->query_vars['paged'] > 0 || (isset($_GET['eventos']) && $_GET['eventos'] == 'passados')) {
            array_push($wp_query->query_vars['meta_query'],
                array(
                    'key' => '_data_final',
                    'value' => date('Y-m-d'),
                    'compare' => '<=',
                    'type' => 'DATETIME'
                )
            );

        } else {
            $wp_query->query_vars['posts_per_page'] = -1;
            array_push($wp_query->query_vars['meta_query'],
                array(
                    'key' => '_data_final',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATETIME'
                )
            );
        }
    }
}


add_action('admin_init', function() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('ui-lightness', WPMU_PLUGIN_URL . '/css/ui-lightness/jquery-ui-1.8.21.custom.css');
});

add_action('admin_menu', function() {
    add_submenu_page('edit.php?post_type=agenda', __('Inserir no menu', 'redelivre'), __('Inserir link no menu', 'redelivre'), 'publish_posts', 'agenda_menu_page', 'agenda_menu_page');
});

function agenda_menu_page() {

    ?>
    
    <?php if( isset($_GET['action']) && $_GET['action'] == 'add_menu_item' ): ?>
            
        <?php 
        
        $menu = wp_get_nav_menu_object('main');
        $items = wp_get_nav_menu_items('main');
        $menuItem = null;
        
        if ($menu) {
            
            foreach ($items as $item) {
                if ($item->url == home_url('/agenda')) {
                    $menuItem = $item;
                }
            }
        
            if (!$menuItem) {
                wp_update_nav_menu_item($menu->term_taxonomy_id, 0, array(
                    'menu-item-title' => __('Agenda', 'redelivre'),
                    'menu-item-url' => home_url('/agenda'), 
                    'menu-item-status' => 'publish')
                );
                $msg = __('Entrada no menu inserida com sucesso!', 'redelivre');
            } else {
                $msg = __('Já existe este item no menu!', 'redelivre');
            }
        }
        
        ?>
        
        <div class="updated">
        <p>
        <?php echo $msg; ?>
        </p>
        </div>
   
    <?php endif; ?>
    
    <div class="wrap">
        
        
            <p>
            
            <?php _e('Sua agenda de eventos pode ser acessada através do endereço', 'redelivre')." ";?><a href="<?php echo site_url('agenda'); ?>"><?php echo site_url('agenda'); ?></a>.
            
            <input type="button" name="create_menu_item" value="Inserir item no menu" onClick="document.location = '<?php echo add_query_arg('action', 'add_menu_item'); ?>';" />
            
            </p>
        
        
    </div>
    <?php

}

function the_event_box() {
        
    $meta = get_metadata('post', get_the_ID());
    
    if (is_array($meta) && !empty($meta)) {
        ?>
        <div class="event-info clear">
            <h3><?php _e("Informações do Evento", "sbc"); ?></h3>
            <?php
            if (array_key_exists('_data_inicial', $meta)) echo '<p class="bottom"><span class="label">'.__('Data Inicial', "sbc").':</span> ', date('d/m/Y', strtotime($meta['_data_inicial'][0])), '</p>';
            if (array_key_exists('_data_final', $meta)) echo '<p class="bottom"><span class="label">'.__('Data Final', "sbc").':</span> ', date('d/m/Y', strtotime($meta['_data_final'][0])), '</p>';
            if ($meta['_horario'][0]) echo '<p class="bottom"><span class="label">'.__('Horário', "sbc").':</span> ', $meta['_horario'][0], '</p>';
            if ($meta['_onde'][0]) echo '<p class="bottom"><span class="label">'.__('Local', "sbc").':</span> ', $meta['_onde'][0], '</p>';
            if ($meta['_link'][0]) echo '<p class="bottom"><span class="label">'.__('Site', "sbc").':</span> ', $meta['_link'][0], '</p>';
            ?>
        </div>
        <?php
    }
}

?>
