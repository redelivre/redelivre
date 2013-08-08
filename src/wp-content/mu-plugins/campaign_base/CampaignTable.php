<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class CampaingTable extends WP_List_Table {
    function __construct() {
        parent::__construct( array(
            'singular'  => 'projeto',     //singular name of the listed records
            'plural'    => 'projetos',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    function column_default($item, $column_name) {
        return $item->$column_name;
    }
    
    function column_domain($item) {
        $actions = array();
        
        $actions['delete'] = "<a href='" . CAMPAIGN_DELETE_URL . "&id=$item->id' onclick=\"if (confirm('Você tem certeza de que deseja remover permanentemente está projeto? Não será possível desfazer essa ação e todos os dados serão perdidos.')) { return true; } return false;\">Remover</a>";
        
        if (is_super_admin()) {
            $actions['edit'] = "<a href='" . CAMPAIGN_EDIT_URL . "&id={$item->id}'>Editar</a>"; 
        } 

        $actions['admin'] = "<a href='{$item->domain}/wp-admin'>Painel</a>";
        
        //Return the title contents
        return sprintf('%1$s %2$s',
            "<a href='{$item->domain}' target='_blank'>{$item->domain}</a>",
            $this->row_actions($actions)
        );
    }
    
    function column_user($item) {
        return $item->campaignOwner->data->user_login;
    }
    
    function column_plan_id($item) {
        return Plan::getName($item->plan_id);
    }
    
    function column_status($item) {
        return $item->getStatus();
    }
    
    function get_columns() {
        $columns = array(
            'domain'     => 'Sub-domínio',
            'own_domain'    => 'Domínio próprio',
            //'candidate_number'  => 'Número do candidato',
            'plan_id'  => 'Plano',
            'status'  => 'Status',
        );

        if (is_super_admin()) {
            $columns['user'] = 'Usuário';
        }
        
        return $columns;
    }
    
    function get_sortable_columns() {
        $sortable_columns = array(
            'domain' => array('domain', true),     //true means its already sorted
            'own_domain' => array('own_domain', false),
            'candidate_number' => array('candidate_number', false)
        );
        
        if (is_super_admin()) {
            $sortable_columns['user'] = array('user', false);
        }
        
        return $sortable_columns;
    }
    
    function prepare_items() {
        //TODO: do pagination and ordering in the query and not here manipulating arrays
        
        $per_page = 25;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        if (is_super_admin()) {
            $data = Campaign::getAll();
        } else {
            $user = wp_get_current_user();
            $data = Campaign::getAll($user->ID);
        }
        
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'domain'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a->$orderby, $b->$orderby); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        $current_page = $this->get_pagenum();
        
        $total_items = count($data);
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
        
        return $this->items;
    }
    
}
