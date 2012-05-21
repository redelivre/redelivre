<?php

class Campaign {
    
    /**
     * id of the campaign
     * @var int
     */
    public $id;
    
    /**
     * Campaign sub domain inside the system.
     * @var string
     */
    public $domain;

    /**
     * Own domain
     * @var string
     */
    public $own_domain;
    
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
    
    /**
     * @var WP_Error
     */
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
        
        $this->id = $data['id'];
        $this->domain = $data['domain'];
        $this->own_domain = $data['own_domain'];
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
        
        // build sub-domain name
        $mainSiteDomain = preg_replace('|https?://|', '', get_site_url());
        $this->domain = 'http://' . $this->domain . '.' . $mainSiteDomain;
        
        if ($this->valueExist('domain')) {
            $this->errorHandler->add('error', 'Este sub-domínio já está cadastrado.');
        }

        if (filter_var($this->domain, FILTER_VALIDATE_URL) === false) {
            $this->errorHandler->add('error', 'O sub-domínio digitado é inválido.');
        }

        // adding 'http://' in case the user haven't because FILTER_VALIDATE_URL requires it
        if (!empty($this->own_domain) && !preg_match('|https?://|', $this->own_domain)) {
            $this->own_domain = 'http://' . $this->own_domain;
        }        

        if ($this->valueExist('own_domain')) {
            $this->errorHandler->add('error', 'Este domínio próprio já está cadastrado.');
        }

        if (!empty($this->own_domain) && filter_var($this->own_domain, FILTER_VALIDATE_URL) === false) {
            $this->errorHandler->add('error', 'O domínio próprio digitado é inválido.');
        }
        
        if ($this->valueExist('candidate_number')) {
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
     * Check whether a particular value for a
     * specified filed already exist in the database.
     * 
     * @param string which field to check
     * @return bool
     */
    protected function valueExist($field) {
        global $wpdb;
        
        // some fields are optional and can be blank
        if (empty($this->{$field})) {
            return false;
        }
        
        $value = $wpdb->get_var(
            $wpdb->prepare("SELECT `$field` FROM `campaigns` WHERE `$field` = %s", $this->{$field}));

        if (!is_null($value)) {
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
            'election_id' => $this->election_id, 'domain' => $this->domain, 'own_domain' => $this->own_domain, 'candidate_number' => $this->candidate_number,
            'status' => 0, 'creation_date' => date('Y-m-d H:i:s'), 'location' => $location
        );
        
        $wpdb->insert('campaigns', $data);
        
        if (!empty($this->own_domain)) {
            $this->alertStaff();
        }
    }
    
    /**
     * Send an e-mail to the site staff when a new
     * campaign is created with its own domain so that
     * they can configure manually configure the server
     * to respond to it.
     * 
     * @return null
     */
    protected function alertStaff() {
        $userName = $this->campaignOwner->data->user_login;
        
        $to = get_bloginfo('admin_email');
        $subject = "Uma nova campanha foi criada com o domínio próprio {$this->own_domain}";
        $message = "O usuário $userName criou uma nova campanha com o sub-domínio <a href='{$this->domain}'>{$this->domain}</a> e o domínio próprio <a href='{$this->own_domain}'>{$this->own_domain}</a>.";
        $headers = "content-type: text/html \r\n";
            
        wp_mail($to, $subject, $message, $headers);
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
        // allow search engine robots to index the campaign site
        update_blog_option($blogId, 'blog_public', 1);
        
        // set defaut campaign theme
        update_blog_option($blogId, 'allowedthemes', array('campanha_padrao' => true));
        update_blog_option($blogId, 'current_theme', 'Campanha Padrão');
        update_blog_option($blogId, 'stylesheet', 'campanha_padrao');
        update_blog_option($blogId, 'template', 'campanha_padrao');
        
        // set upload limit
        $capabilities = Capability::getByPlanId($this->plan_id);
        update_blog_option($blogId, 'blog_upload_space', $capabilities->upload_limit->value);
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
     * Convert between the int in the database and
     * the string to be displayed to the user.
     * @param int $newStatus
     * 
     * @return bool
     */
    public function setStatus($newStatus) {
        
        if ( is_numeric($newStatus) && ( intval($newStatus) == 0 || intval($newStatus) == 1 ) && $newStatus != $this->status ) {
            global $wpdb;
            
            $wpdb->update('campaigns', array('status' => $newStatus), array('id' => $this->id) );
            
        } else {
            return false;
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
