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
    
    static function register(){
        register_post_type('agenda', array(
                
                'labels' => array(
                                    'name' => _x('Evento', 'post type general name', 'sbc'),
                                    'singular_name' => _x('Evento', 'post type singular name', 'sbc'),
                                    'add_new' => _x('Adicionar novo', 'image', 'sbc'),
                                    'add_new_item' => __('Adicionar novo evento', 'sbc'),
                                    'edit_item' => __('Editar evento', 'sbc'),
                                    'new_item' => __('Novo evento', 'sbc'),
                                    'view_item' => __('Ver evento', 'sbc'),
                                    'search_items' => __('Buscar eventos', 'sbc'),
                                    'not_found' =>  __('Nenhum evento encontrado', 'sbc'),
                                    'not_found_in_trash' => __('Nenhum evento encontrado na lixeira', 'sbc'),
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
            'Dados do Evento',
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
                echo '<td><b><label for="_data_inicial">Data Inicial </label></td>';
                echo "<td><input type='text' id='_data_inicial' name='_data_inicial' value='$data_inicial' size='25' />";
                echo '<small> (ex.: 01/01/2011)</small>';
                echo '</td>';
            
            echo '</tr>';
            
            echo '<tr>';
                echo '<td><b><label for="_data_inicial">Data Final </label></td>';
                echo "<td><input type='text' id='_data_final' name='_data_final' value='$data_final' size='25' /></td>";
            
            echo '</tr>';
            
            echo '<tr>';
                echo '<td><b><label for="_horario">Horário </label></td>';
                echo "<td><input type='text' id='_horario' name='_horario' value='$horario' size='25' /></td>";
            
            echo '</tr>';
            
            echo '<tr>';
                echo '<td><b><label for="_link">Link Externo </label></td>';
                echo "<td><input type='text' id='_link' name='_link' value='$link' size='25' /></td>";
            
            echo '</tr>';
            
            echo '<tr>';
                echo '<td><b><label for="_onde">Onde</label></td>';
                echo "<td><input type='text' id='_onde' name='_onde' value='$onde' size='25' /></td>";
            
            echo '</tr>';
            
        echo '</table>';
        ?>
        <script>
        
        var opts = {
		closeText: 'Fechar',
		prevText: '&#x3c;Anterior',
		nextText: 'Pr&oacute;ximo&#x3e;',
		currentText: 'Hoje',
		monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
		'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
		monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
		'Jul','Ago','Set','Out','Nov','Dez'],
		dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','S&aacute;bado'],
		dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b'],
		dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','S&aacute;b'],
		weekHeader: 'Sm',
		dateFormat: 'dd/mm/yy',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
        
        jQuery(document).ready(function() {
            jQuery('#_data_inicial, #_data_final').datepicker( opts );
        });
        
        </script>
        <?php
        
    }

    /* When the post is saved, saves our custom data */
    function save_postdata( $post_id ) {
        
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

        // OK, we're authenticated: we need to find and save the data
        $initial_date_pt = explode('/', trim($_POST[$data_inicial]));
        $initial_date_en = $initial_date_pt[2].'-'.$initial_date_pt[1].'-'.$initial_date_pt[0];
        
        if ($_POST[$data_final]) {
            $final_date_pt = explode('/', trim($_POST[$data_final]));
            $final_date_en = $final_date_pt[2].'-'.$final_date_pt[1].'-'.$final_date_pt[0];
        }
        else 
            $final_date_en = $initial_date_en;
        
        update_post_meta($post_id, $data_inicial, date('Y-m-d h:i', strtotime($initial_date_en)));
        update_post_meta($post_id, $data_final, date('Y-m-d h:i', strtotime($final_date_en)));
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

add_action('pre_get_posts', 'sbc_agenda_query');

function sbc_agenda_query($wp_query) {
    
    if (is_admin()) return;
    
    if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'agenda' && is_post_type_archive('agenda')) {
        
        
        if (!is_array($wp_query->query_vars['meta_query'])) $wp_query->query_vars['meta_query'] = array();
        
        
        $wp_query->query_vars['orderby'] = 'meta_value';
        $wp_query->query_vars['order'] = 'ASC';
        $wp_query->query_vars['meta_key'] = '_data_inicial';
        
        if ($wp_query->query_vars['paged'] > 0 || $_GET['eventos'] == 'passados') {
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
    add_submenu_page('edit.php?post_type=agenda', 'Inserir no menu', 'Inserir link no menu', 'publish_posts', 'agenda_menu_page', 'agenda_menu_page');
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
                    'menu-item-title' => 'Agenda',
                    'menu-item-url' => home_url('/agenda'), 
                    'menu-item-status' => 'publish')
                );
                $msg = 'Entrada no menu inserida com sucesso!';
            } else {
                $msg = 'Já existe este item no menu!';
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
            
            Sua agenda de eventos pode ser acessada através do endereço <a href="<?php echo site_url('agenda'); ?>"><?php echo site_url('agenda'); ?></a>.
            
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
            <h3>Informações do Evento</h3>
            <?php
            if ($meta['_data_inicial'][0]) echo '<p class="bottom"><span class="label">Data Inicial:</span> ', date('d/m/Y', strtotime($meta['_data_inicial'][0])), '</p>';
            if ($meta['_data_final'][0]) echo '<p class="bottom"><span class="label">Data Final:</span> ', date('d/m/Y', strtotime($meta['_data_final'][0])), '</p>';
            if ($meta['_horario'][0]) echo '<p class="bottom"><span class="label">Horário:</span> ', $meta['_horario'][0], '</p>';
            if ($meta['_onde'][0]) echo '<p class="bottom"><span class="label">Local:</span> ', $meta['_onde'][0], '</p>';
            if ($meta['_link'][0]) echo '<p class="bottom"><span class="label">Site:</span> ', $meta['_link'][0], '</p>';
            ?>
        </div>
        <?php
    }
}

?>
