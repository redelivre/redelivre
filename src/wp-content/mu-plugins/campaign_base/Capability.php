<?php

class Capability {
    /**
     * Capability id
     * @var int
     */
    public $id;
    
    /**
     * Plan id
     * @var int
     */
    public $plan_id;
    
    /**
     * Capability name
     * @var string
     */
    public $name;
    
    /**
     * Capability slug
     * @var string
     */
    public $slug;
    
    /**
     * The value of the capability (most of the time is true or false).
     * @var mixed
     */
    public $value;
    
    /**
     * Return all available capabilities for
     * one plan
     * 
     * @param int $plan_id
     * @return stdClass
     */
    public static function getByPlanId($plan_id, $slug = '') {
        global $wpdb;
        
        $capabilites = new stdClass;
              
        if ($slug != '') {
        	$where_add = $wpdb->prepare(" AND slug = %s ", $slug);
        } else {
            $where_add = '';
        }
        
        $query = $wpdb->prepare("SELECT * FROM `capabilities` WHERE `plan_id` = %d", $plan_id);
        $query .= $where_add;
        
        $result = $wpdb->get_results($query, ARRAY_A);
        
        foreach ($result as $entry) {
            $capabilites->{$entry['slug']} = new Capability($entry);
        }
        
        return $capabilites;
    }
    
    public function __construct(array $data) {
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        
        if (isset($data['plan_id'])) {
            $this->plan_id = $data['plan_id'];
        }
        
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        
        if (isset($data['slug'])) {
            $this->slug = $data['slug'];
        }
  
        if (isset($data['value'])) {
            $this->value = $data['value'];
        }
    }
}
