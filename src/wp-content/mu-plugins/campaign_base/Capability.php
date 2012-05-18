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
     * True, false or the value of the capability.
     * @var mixed
     */
    public $access;
    
    /**
     * Return all available capabilities for
     * one plan
     * 
     * @param int $plan_id
     * @return stdClass
     */
    public static function getByPlanId($plan_id) {
        global $wpdb;
        
        $capabilites = new stdClass;
        $result = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM `capabilities` WHERE `plan_id` = %d", $plan_id), ARRAY_A);
            
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
  
        if (isset($data['access'])) {
            $this->access = $data['access'];
        }
    }
}
