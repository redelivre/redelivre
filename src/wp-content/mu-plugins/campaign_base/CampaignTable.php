<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

add_action('admin_enqueue_scripts', array('CampaingTable', 'JS'));

class CampaingTable extends WP_List_Table {
    function __construct() {
        parent::__construct( array(
            'singular'  => Campaign::getStrings('singular'),     //singular name of the listed records
            'plural'    => Campaign::getStrings('plural'),    //plural name of the listed records
            'ajax'      => true,        //does this table support ajax?
            'screen'	=> 'campaigns'
        ) );
    }
    
    function column_default($item, $column_name) {
        return $item->$column_name;
    }
    
    function JS()
    {
    	$data = array(
    		'ajax_url' => admin_url('admin-ajax.php'),
    	);
    	 
    	wp_enqueue_script('CampaingTableJS', WPMU_PLUGIN_URL.'/campaign_base/js/CampaingTableJS.js', array('jquery'));
    	wp_localize_script('CampaingTableJS', 'CampaingTable', $data);
    }
    
    function column_domain($item) {
        $actions = array();
        
        $actions['delete'] = "<a href='" . CAMPAIGN_DELETE_URL . "&id=$item->id' onclick=\"if (confirm('".Campaign::getStrings('remover')."')) { return true; } return false;\">Remover</a>";
        
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
            $sortable_columns['user'] = array('user_login', false);
        }
        
        return $sortable_columns;
    }
    
    function prepare_items()
    {
    	$orderby = 'domain' ;
    	if(array_key_exists('orderby', $_REQUEST) && $_REQUEST['orderby'] != '')
    	{
    		$orderby = $_REQUEST['orderby'];
    	}
    	$order = 'asc';
    	if(array_key_exists('order', $_REQUEST) && $_REQUEST['order'] != '')
    	{
    		$order = $_REQUEST['order'];
    	}
    	$domain = null;
    	
    	if(array_key_exists('s', $_REQUEST) && $_REQUEST['s'] != '')
    	{
    		$domain = "http://%".$_REQUEST['s']."%";
    	}
    	
        $per_page = 25;
        
        $current_page = $this->get_pagenum();
        
        $offset = ($current_page-1) * $per_page;
        $limit = $current_page * $per_page;
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        if (is_super_admin()) {
            $data = Campaign::findAll($orderby, $order,$offset,$limit, $domain);
        } else {
            $user = wp_get_current_user();
            $data = Campaign::findAll($orderby, $order,$offset,$limit, $domain, $user->ID);
        }
        
        $total_items = $data->count;
        
        $this->items = $data->itens;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
        
        return $this->items;
    }
}
