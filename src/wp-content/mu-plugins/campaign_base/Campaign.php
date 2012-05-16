<?php

class Campaign {
    /**
     * Campaign domain
     * @var string
     */
    public $domain;
    
    /**
     * Plan id of the campaign
     * @var int
     */
    public $plan_id;
    
    /**
     * State id
     * @var int
     */
    public $state;
    
    /**
     * City id
     * @var int
     */
    public $city;
    
    public $errorHandler;
    
    /**
     * Return all available campaigns.
     * 
     * @param int $user_id
     * @return array array of Campaign objects
     */
    public static function getAll($user_id = null) {
        global $wpdb;
        
        if ($user_id) {
            $query = $wpdb->prepare('SELECT * FROM `campaigns` WHERE user_id = %d ORDER BY `domain` asc', $user_id);
        } else {
            $query = 'SELECT * FROM `campaigns` ORDER BY `domain` asc';
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        $campaigns = array();
        
        foreach ($results as $result) {
            $campaigns[] = new Campaign($result);
        }
        
        return $campaigns;
    }
    
    /**
     * Get a campaign by blog_id
     * 
     * @param int $blog_id
     * @return Campaign
     */
    public static function getByBlogId($blog_id) {
        global $wpdb;
        
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `campaigns` WHERE blog_id = %d", $blog_id), ARRAY_A);
        
        return new Campaign($result);
    }

    //TODO: this function is being used when creating a new campaign and when getting
    //      an existing one. This two different behaviors should be splited into two
    //      different methods.    
    public function __construct(array $data) {
        //TODO: create interface for more than one election
        $this->election_id = 1;
        
        $this->domain = $data['domain'];
        $this->plan_id = $data['plan_id'];
        $this->candidate_number = $data['candidate_number'];
        
        if (isset($data['state'])) {
            $this->state = $data['state'];
        }
        
        if (isset($data['city'])) {
            $this->city = $data['city'];
        }
  
        if (isset($data['creation_date'])) {
            $this->creation_date = $data['creation_date'];
        }
        
        if (isset($data['status'])) {
            $this->status = $data['status'];
        }
        
        if (isset($data['user_id'])) {
            $this->campaignOwner = get_userdata($data['user_id']);
        } else {
            $this->campaignOwner = wp_get_current_user();
        }
        
        $this->errorHandler = new WP_Error;
    }
    
    /**
     * Validate data before creating a new
     * campaign.
     */
    public function validate() {
        if (empty($this->domain)) {
            $this->errorHandler->add('error', 'O campo domínio não pode estar vazio.');
        }
        
        if ($this->domainExist()) {
            $this->errorHandler->add('error', 'Esse domínio já está cadastrado.');
        }

        // adding 'http://' in case the use haven't because FILTER_VALIDATE_URL requires it
        if (strpos($this->domain, 'http://') === false) {
            $this->domain = 'http://' . $this->domain;
        }        

        if (filter_var($this->domain, FILTER_VALIDATE_URL) === false) {
            $this->errorHandler->add('error', 'O domínio digitado é inválido.');
        }
        
        if ($this->candidateExist()) {
            $this->errorHandler->add('error', 'Uma campanha para este candidato já foi criada no sistema.');
        }
        
        if (empty($this->plan_id)) {
            $this->errorHandler->add('error', 'Você precisa selecionar um plano.');
        }
        
        if (!in_array($this->plan_id, Plan::getAllIds())) {
            $this->errorHandler->add('error', 'O plano escolhido é inválido.');
        }
        
        if (empty($this->state)) {
            $this->errorHandler->add('error', 'Você precisa selecionar um estado.');
        }
        
        if (empty($this->city)) {
            $this->errorHandler->add('error', 'Você precisa selecionar uma cidade.');
        }
        
        if (!empty($this->errorHandler->errors)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check whether the candidate number
     * already exist.
     * 
     * @return bool
     */
    protected function candidateExist() {
        global $wpdb;
        
        $candidate_number = $wpdb->get_var(
            $wpdb->prepare("SELECT `candidate_number` FROM `campaigns` WHERE `candidate_number` = %d", $this->candidate_number));
            
        if (!is_null($candidate_number)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check whether the domain already exist.
     * 
     * @return bool
     */
    protected function domainExist() {
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
    public function create() {
        global $wpdb;
     
        // temporary format to store state id and city id in the same field 
        $location = $this->state . ":" . $this->city;

        $blogId = $this->createNewBlog();
        
        $data = array(
            'user_id' => $this->campaignOwner->ID, 'plan_id' => $this->plan_id, 'blog_id' => $blogId,
            'election_id' => $this->election_id, 'domain' => $this->domain, 'candidate_number' => $this->candidate_number,
            'status' => 0, 'creation_date' => date('Y-m-d H:i:s'), 'location' => $location 
        );
        
        $wpdb->insert('campaigns', $data);
    }
    
    /**
     * Create a new blog associated with the
     * new campaign
     * 
     * @return int created blog id
     */
    protected function createNewBlog() {
        // set here only to avoid a warning in wpmu_create_blog()
        $meta['public'] = false;
        
        $domain = str_replace('http://', '', $this->domain);
        
        $blogId = wpmu_create_blog($domain, '/', $domain, $this->campaignOwner->ID, $meta);
        
        $this->setBlogOptions($blogId);
        
        if (is_wp_error($blogId)) {
            //TODO: improve error handling
            echo 'Não foi possível criar o blog!'; die;
        }
        
        return $blogId;
    }
    
    /**
     * Set options for a new blog created when a new
     * campaign is created.
     * 
     * @param int $blogId
     * @return null
     */
    protected function setBlogOptions($blogId) {
        update_blog_option($blogId, 'blog_public', 1);
        update_blog_option($blogId, 'allowedthemes', array('campanha_padrao' => true));
        update_blog_option($blogId, 'current_theme', 'Campanha Padrão');
    }
    
    /**
     * Convert between the int in the database and
     * the string to be displayed to the user.
     * 
     * @return string
     */
    public function getStatus() {
        switch ($this->status) {
            case 0:
                return 'Pagamento pendente';
            case 1:
                return 'Ativo';
            default:
                throw new Exception('Campo status não definido ou com valor inválido');
        }
    }
    
    /**
     * Check whether the campaign has been paid or
     * not.
     * 
     * @return bool
     */
    public function isPaid() {
        switch ($this->status) {
            case 0:
                return false;
            case 1:
                return true;
            default:
                throw new Exception('Campo status não definido ou com valor inválido');
        }
    }
}
