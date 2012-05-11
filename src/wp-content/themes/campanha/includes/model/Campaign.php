<?php

class Campaign {
    /**
     * Campaign domain
     * @var string
     */
    protected $domain;
    
    /**
     * Plan id of the campaign
     * @var int
     */
    protected $plan;
    
    /**
     * State id
     * @var int
     */
    protected $state;
    
    /**
     * City id
     * @var int
     */
    protected $city;
    
    public $errors;
    
    /**
     * Return all available campaigns.
     * 
     * @param int $user_id
     * @return array
     */
    public static function getAll($user_id = null) {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM `campaigns` ORDER BY `domain` asc");
    }
    
    public function __construct(array $data) {
        //TODO: create interface for more than one election
        $this->election_id = 1;
        
        $this->domain = $data['domain'];
        $this->plan = $data['plan'];
        $this->state = $data['state'];
        $this->city = $data['city'];
        
        $this->errors = new WP_Error;
    }
    
    /**
     * Validate data before creating a new
     * campaign.
     */
    public function validate() {
        if ($this->domainExist()) {
            $this->errors->add('domain_exist', 'Esse domínio já está cadastrado.');
        }
        
        if (!empty($this->errors->errors)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check whether the domain exist already.
     * 
     * @return bool
     */
    public function domainExist() {
        global $wpdb;
        
        $domain = $wpdb->get_var(
            $wpdb->prepare("SELECT `domain` FROM `campaigns` WHERE `domain` = %s", $this->domain));
        
        if (!is_null($domain)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Add a new campaign to the database
     * 
     * @return null
     */
    public function save() {
        global $wpdb;
     
        $currentUser = wp_get_current_user();
        
        // temporary format to store state id and city id in the same field 
        $location = $this->state . ":" . $this->city;
        
        $data = array(
            'user_id' => $currentUser->ID, 'plan_id' => $this->plan, 'blog_id' => 0,
            'election_id' => $this->election_id, 'domain' => $this->domain,
            'status' => 0, 'creation_date' => date('Y-m-d H:i:s'), 'location' => $location 
        );
        
        $wpdb->insert('campaigns', $data);
    }
}
