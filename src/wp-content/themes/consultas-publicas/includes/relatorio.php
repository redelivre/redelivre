<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class ReportTable extends WP_List_Table {
    function __construct() {
        parent::__construct( array(
            'singular'  => __('objeto', 'consulta'),     //singular name of the listed records
            'plural'    => __('objetos', 'consulta'),    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    function column_default($item, $column_name) {
        return $item->$column_name;
    }
    
    function column_post_title($item) {
        $permalink = get_post_permalink($item->ID);
        return "<a href='{$permalink}'>{$item->post_title}</a>";
    }

    function column_user_created($item) {
        return ($item->meta_value == true) ? 'Sim' : 'Não';
    }
    
    function column_type($item) {
        $termsString = '';
        $terms = wp_get_post_terms($item->ID, 'object_type');
        
        foreach ($terms as $term) {
            $permalink = get_term_link($term);
            $termsString .= "<a href='{$permalink}'>{$term->name}</a><br />";
        }
        
        return $termsString;
    }
    
    function column_evaluation_count($item) {
        // imprime diretamente ao inves de retornar por conta do html::part()
        
        echo "{$item->evaluation_count} "; 
        
        // mostra o link para exibir o grafico somente se houver mais de uma opção de avaliação
        if ($item->evaluation_count > 0 && evaluation_count_options() > 1) {
            echo "<a class='toggle_evaluation'>(gráfico)</a>";
            html::part('evaluation-graph-admin', array('item' => $item));
        }
    }
    
    function get_columns() {
        $objectLabels = get_theme_option('object_labels');
        $taxonomyLabels = get_theme_option('taxonomy_labels');
        
        $columns = array(
            'post_title' => $objectLabels['singular_name'],
            'type' => $taxonomyLabels['singular_name'],
            'comments_count'    => __('Número de comentários'),
            'evaluation_count'  => __('Número de votos'),
            'user_created' => __('Criado por usuário?', 'consulta'),
        );
        
		if (!get_theme_option('enable_taxonomy')) {
			unset($columns['type']);
		}
		
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'post_title' => array('post_title', true), //true means its already sorted
            'comments_count' => array('comments_count', true),
            'evaluation_count' => array('evaluation_count', true),
        );
        
        return $sortable_columns;
    }
    
    function prepare_items() {
        global $wpdb;
        
        $per_page = -1;
        $this->total_votes = 0;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $query = "SELECT DISTINCT ID, post_title, meta_value FROM $wpdb->posts p, $wpdb->postmeta pm WHERE p.ID = pm.post_id AND p.post_type = 'object' AND p.post_status = 'publish' AND pm.meta_key = '_user_created'";
        
        // filtra objetos criados pelos admins ou pelos usuários
        if (isset($_REQUEST['who_created']) && $_REQUEST['who_created'] != 'all') {
            if ($_REQUEST['who_created'] == 'user_created') {
                $query .= ' AND pm.meta_value = 1';
            } else {
                $query .= ' AND (pm.meta_value IS NULL OR pm.meta_value = 0)';
            }
        }
        
        $data = $wpdb->get_results($query);
        
        foreach ($data as $key => $item) {
            $item->evaluation_count = count_votes($item->ID);
            $this->total_votes += $item->evaluation_count;
            
            $item->comments_count = wp_count_comments($item->ID)->approved;
            
            // filtra objetos por tipo
            if (isset($_REQUEST['cat']) && !has_term($_REQUEST['cat'], 'object_type', $item->ID)) {
                unset($data[$key]);
            }
        }
        
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'post_title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        $current_page = $this->get_pagenum();
        
        $total_items = count($data);
        
        if ($per_page > 0) {
            $data = array_slice($data, (($current_page-1) * $per_page), $per_page);
        }
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ) );
        
        return $this->items;
    }
    
    function extra_tablenav($which) {
        if ($which == 'top') :
            $labels = get_theme_option('taxonomy_labels');
        
            ?>
            <div class="alignleft actions">
                <?php
                if (get_theme_option('enable_taxonomy')) {
                                    
                    $labels = get_theme_option('taxonomy_labels');
                    
                    $dropdown_options = array(
                        'taxonomy' => 'object_type',
                        'show_option_all' => $labels['all_items'],
                        'hide_empty' => true,
                        'hierarchical' => 1,
                        'show_count' => 0,
                        'orderby' => 'name',
                    );
                    
                    if (isset($_REQUEST['cat'])) {
                        $dropdown_options['selected'] = $_REQUEST['cat'];
                    }
                    
                    wp_dropdown_categories($dropdown_options);
                }
                ?>
                <select class="postform" id="who_created" name="who_created">
                    <option value="all" <?php isset($_REQUEST['who_created']) ? selected('all', $_REQUEST['who_created']) : ''; ?>>Todos os objetos</option>-
                    <option value="admin_created" <?php isset($_REQUEST['who_created']) ? selected('admin_created', $_REQUEST['who_created']) : ''; ?>>Objetos criados pelos admins</option>
                    <option value="user_created" <?php isset($_REQUEST['who_created']) ? selected('user_created', $_REQUEST['who_created']) : ''; ?>>Objetos criados pelos usuários</option>
                </select>
                <?php submit_button(__('Filter'), 'button', false, false, array('id' => 'post-query-submit')); ?>
            </div>
            <?php
        endif;
    }
}

add_action('admin_menu', 'relatorio_menu');
function relatorio_menu() {
    add_submenu_page('theme_options', 'Relatório', 'Relatório', 'manage_options', 'relatorio', 'relatorio_page_callback_function');
}

function relatorio_page_callback_function() {
    global $wpdb;
    
    wp_enqueue_script('object-report', get_template_directory_uri() . '/js/object-report.js');
    wp_enqueue_style('evaluation', get_template_directory_uri() . '/css/evaluation.css');
    wp_enqueue_style('object-report', get_template_directory_uri() . '/css/object-report.css');
    
    $reportTable = new ReportTable;
    
    $totalObjects = $wpdb->get_var("SELECT count(distinct(id)) FROM $wpdb->posts p, $wpdb->postmeta pm WHERE p.ID = pm.post_id AND p.post_status = 'publish' AND p.post_type = 'object' AND pm.meta_key = '_user_created' AND pm.meta_value != 1");
    
    if (get_theme_option('allow_suggested')) {
        $totalSuggestedObjects = $wpdb->get_var("SELECT count(*) FROM $wpdb->posts p, $wpdb->postmeta pm WHERE p.ID = pm.post_id AND p.post_status = 'publish' AND p.post_type = 'object' AND pm.meta_key = '_user_created' AND pm.meta_value = 1");
    }
    
    $totalComments = wp_count_comments()->approved;
    
    ?>
    
    <div class="wrap span-20">
        <h2><?php _e('Relatório', 'consulta'); ?></h2>

        <p><?php _e('A tabela abaixo lista todos os objetos desta consulta com o número de comentários e o resultado da avaliação quantitativa.', 'consulta'); ?></p>
        
        <?php $reportTable->prepare_items(); ?>
        <p><?php printf(__('Objetos criados pela equipe: %d', 'consulta'), $totalObjects); ?></p>
    
        <?php if (get_theme_option('allow_suggested')) : ?>
            <p><?php printf(__('Objetos criados pelos usuários: %d', 'consulta'), $totalSuggestedObjects); ?></p>
        <?php endif; ?>
        
        <p><?php printf(__('Total de comentários: %d', 'consulta'), $totalComments); ?></p>
        
        <p><?php printf(__('Total de votos: %d', 'consulta'), $reportTable->total_votes); ?></p>
        
        <form id="posts-filter" action="" method="get">
            <input type="hidden" name="page" value="relatorio" />
            <?php $reportTable->display(); ?>
        </form>
    </div>
    <?php 
}
