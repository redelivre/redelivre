<?php

class class_customer_data_extractor{

    private $fields = array();
    private $header = array();
    private $rows = array();

    function __construct($fields, $limit, $offset, $after, $before){
        $this->fields = $fields;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->after_date = $after;
        $this->before_date = $before;

        $this->header = $this->header();
        $this->rows = $this->rows();
        
    }

    function rows(){
        $users_array = $this->getUsers();
        $wp_users = $this->getWpUsers($users_array);
        $formated = array();
        foreach($wp_users as $user){
            $formated[] = $this->row($user);
        }
        return $formated ;
    }

    function getWpUsers($users_array){
        $users = array();
        foreach($users_array as $user){
            $id = $user['ID'];
            $users[] = get_user_by('id', $id);
        }
        return $users;
    }

    function getUsers(){
        global $wpdb;


        $sql = 'SELECT * FROM '.$wpdb->users.' INNER JOIN '.$wpdb->usermeta.'
        ON '.$wpdb->users.'.ID = '.$wpdb->usermeta.'.user_id
        WHERE '.$wpdb->usermeta.'.meta_key = "'.$wpdb->prefix.'capabilities"
        AND ';

        $roles_query = $this->roleQuery();

        $sql = $sql.$roles_query;

        $date_query = $this->datequery();

        $sql = $sql.$date_query;

        $sql = $sql.$this->limit();
        $sql = $sql.$this->offset();
        
        $users = $wpdb->get_results($sql, ARRAY_A);
        
        return $users;
    }

    function getRoles(){
        $roles = array('customer'); 
        return $roles;
    }

    /** 
     * if date range is given or single date is given
     */
    function datequery(){
        global $wpdb;
        $where = "";
        $after = "";
        $and = "";
        $before = "";
        if($this->after_date != ""){
            $after = ' '.$wpdb->users.'.user_registered >= "'.$this->after_date.'" ';
        } 

        if($this->before_date != ""){
            $before = ' '.$wpdb->users.'.user_registered <= "'.$this->before_date.'" ';
        } 

        if($this->after_date != "" && $this->before_date != ""){
            $and = ' AND ';
        }

        if($this->after_date == "" && $this->before_date == ""){
            return;
        }

        return ' AND ('.$after.$and.$before.')';
    }

    function limit(){
        if(isset($this->limit) && $this->limit != "" && $this->limit != 0){
            return ' LIMIT '.$this->limit;
        }else{
            return ' LIMIT 10000000000000000000';
        }
        
    }

    function offset(){
        if(isset($this->offset) && $this->offset != ""){
            return ' OFFSET '.$this->offset;
        }
        return ;
    }

    function roleQuery(){
        global $wpdb;
        $roles = $this->getRoles();
        $roles_like = array();
        foreach($roles as $role){
            $roles_like[] = $wpdb->usermeta.'.meta_value LIKE "%'.$role.'%" ';
        }
        if(count($roles_like) > 0){
            $roles_query = '('.implode(" OR ", $roles_like).')';
        }else{
            $roles_query = 0;
        }
        return $roles_query;
    }

    private function row($user){
        foreach($this->fields as $key => $value){
            $woocommerce_user[$key] =  $user->get($key);
        }
        return $woocommerce_user;
    }

    private function header(){
        foreach($this->fields as $key => $value){
            $header[] =  $value;
        }
        return $header;
    }

    function getRows(){
        return $this->rows;
    }

    function getHeader(){
        return $this->header;
    }

}