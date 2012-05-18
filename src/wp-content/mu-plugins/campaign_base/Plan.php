<?php

class Plan {
    
    
    /**
     * Plan id
     * @var int
     */
    public $id;
    
    /**
     * Election id
     * @var int
     */
    public $election_id;
    
    /**
     * Plan name
     * @var string
     */
    public $name;
    
    /**
     * Plan price
     * @var int
     */
    public $price;
    
    /**
     * Return all available plans
     * 
     * @return array
     */
    public static function getAll() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM `plans`");
    }
    
    /**
     * Return all available plan's ids
     * 
     * @return array
     */
    public static function getAllIds() {
        global $wpdb;
        return $wpdb->get_col("SELECT id FROM `plans`");
    }
    
    /**
     * Return plan name
     * @param int $planId
     * @return string
     */
    public static function getName($planId) {
        global $wpdb;
        $name = $wpdb->get_var(
            $wpdb->prepare("SELECT `name` FROM `plans` WHERE `id` = %d", $planId));
            
        return $name;
    }
    
    /**
     * Get a plan by id
     * 
     * @param int $id
     * @return Plan
     */
    public static function getById($id) {
        global $wpdb;
        
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `plans` WHERE id = %d", $id), ARRAY_A);
        
        return new Plan($result);
    }
    
    public function __construct(array $data) {
        

        if (isset($data['election_id'])) {
            $this->election_id = $data['election_id'];
        }
        
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
  
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        
        if (isset($data['price'])) {
            $this->price = $data['price'];
        }
        
    }
    
}
