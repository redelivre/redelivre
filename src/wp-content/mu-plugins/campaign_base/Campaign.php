<?php

class Campaign {
    
    /**
     * id of the campaign
     * @var int
     */
    public $id;
    
    /**
     * Id of the campaign's blog.
     * @var int
     */
    public $blog_id;
    
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
        
        $query = '';
        
        if ($user_id) {
            $query = $wpdb->prepare('SELECT * FROM `campaigns` WHERE user_id = %d ORDER BY `domain` asc', $user_id);
        } else if (is_super_admin()) {
            // only super admins should be able to see all campaigns
            $query = 'SELECT * FROM `campaigns` ORDER BY `domain` asc';
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        $campaigns = array();
        
        if ($results) {
            foreach ($results as $result) {
                $campaigns[] = new Campaign($result);
            }
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

        if (!$result) {
            throw new Exception('Não existe uma campanha associada a este blog. Verifique se você não selecionou um tema de campanha para o site principal.');
        }
        
        return self::formatData($result);
    }

    /**
     * Get a campaign by id
     * 
     * @param int $id
     * @return Campaign
     */
    public static function getById($id) {
        global $wpdb;
        
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `campaigns` WHERE id = %d", $id), ARRAY_A);

        if (!$result) {
            throw new Exception('Não foi possível encontrar a campanha.');
        }
        
        return self::formatData($result);
    }
    
    /**
     * Convert data from the database before creating
     * Campign object.
     * 
     * @param array $result campaign data as fetched from the database
     * @return Campaign a new campaign object 
     */
    protected static function formatData($result) {
        list($result['state'], $result['city']) = explode(':', $result['location']);
        unset($result['location']);
        
        return new Campaign($result);
    }

    //TODO: this function is being used when creating a new campaign and when getting
    //      an existing one. This two different behaviors should be splited into two
    //      different methods.
    public function __construct(array $data) {
        //TODO: create interface for more than one election
        $this->election_id = 1;
        
        if (isset($data['id'])) {
            $this->id = $data['id'];
        }
        
        if (isset($data['blog_id'])) {
            $this->blog_id = $data['blog_id'];
        }
        
        $this->domain = trim($data['domain']);
        $this->own_domain = trim($data['own_domain']);
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
        
        if (isset($data['observations'])) {
            $this->observations = $data['observations'];
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
        if (!isset($this->id) && (empty($this->domain) || preg_match( '|^([a-zA-Z0-9-])+$|', $this->domain) === 0)) {
            $this->errorHandler->add('error', 'O sub-domínio digitado está vazio ou inválido.');
        }
        
        // TODO: we shouldn't change the value of $this->domain on a method that is supposed to do only validation
        if (!preg_match('|^https?://|', $this->domain)) {
            $mainSiteDomain = preg_replace('|https?://|', '', get_site_url());
            $this->domain = 'http://' . $this->domain . '.' . $mainSiteDomain;
        }
        
        if ($this->valueExist('domain')) {
            $this->errorHandler->add('error', 'Este sub-domínio já está cadastrado.');
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
        
        /**
        * Retirando obrigatoriedade do campo número do candidato
        * @author Henrique Menegale (Ethymos)
        */
        
        /*
        if (preg_match('|^(\d){2,5}$|', $this->candidate_number) === 0) {
            $this->errorHandler->add('error', 'Número de candidato inválido.');
        }*/
        
        if ($this->candidateExist()) {
            $this->errorHandler->add('error', 'Uma campanha para este candidato já foi criada no sistema.');
        }
        
        if (empty($this->plan_id) || !in_array($this->plan_id, Plan::getAllIds())) {
            $this->errorHandler->add('error', 'Selecione o plano desejado.');
        }
        
        /**
         * Retirando obrigatoriedade do campo Estado
         * @author Jacson Passold (Ethymos)
         */
        
        /*
        if (empty($this->state)) {
            $this->errorHandler->add('error', 'Você precisa selecionar um estado.');
        }
        
        if (empty($this->city)) {
            $this->errorHandler->add('error', 'Você precisa selecionar uma cidade.');
        }
        */
        
        do_action('Campaign-validate', $this->errorHandler);
        
        if (!empty($this->errorHandler->errors)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check whether a campaign for this candidate
     * has already been created in the system by checking
     * the candidate number, city and state.
     */
    protected function candidateExist() {
        global $wpdb;
        
        if (empty($this->city) || empty($this->state)) {
            // all three fields above must be set to check if the candidate exist
            return false;
        }
          
        if (isset($this->id)) {
        	if($this->candidate_number){
	            $campaign = $wpdb->get_row(
	                $wpdb->prepare("SELECT * FROM `campaigns` WHERE `candidate_number` = %d AND `location` = %s AND `id` != %d",
	                $this->candidate_number, "$this->state:$this->city", $this->id));  
            } else {
	             $campaign = $wpdb->get_row(
	                $wpdb->prepare("SELECT * FROM `campaigns` WHERE `location` = %s AND `id` != %d",
	                "$this->state:$this->city", $this->id)); 
            }    
        } elseif($this->candidate_number){
	            $campaign = $wpdb->get_row(
	                $wpdb->prepare("SELECT * FROM `campaigns` WHERE `candidate_number` = %d AND `location` = %s",
	                $this->candidate_number, "$this->state:$this->city"));
        } 
        
        if (!is_null($campaign)) {
            return true;
        }
        
        return false;
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
        
        if (isset($this->id)) {
            $value = $wpdb->get_var(
                $wpdb->prepare("SELECT `$field` FROM `campaigns` WHERE `$field` = %s AND `id` != %d", $this->{$field}, $this->id));
        } else {
            $value = $wpdb->get_var(
                $wpdb->prepare("SELECT `$field` FROM `campaigns` WHERE `$field` = %s", $this->{$field}));
        }

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
         
        $location = $this->formatLocation($this->state, $this->city);

        $this->blog_id = $this->createNewBlog();
        
        $data = array(
            'user_id' => $this->campaignOwner->ID, 'plan_id' => $this->plan_id, 'blog_id' => $this->blog_id,
            'election_id' => $this->election_id, 'domain' => $this->domain, 'own_domain' => $this->own_domain, 'candidate_number' => $this->candidate_number,
            'status' => 0, 'creation_date' => date('Y-m-d H:i:s'), 'location' => $location, 'observations' => $this->observations,
        );
        
        $wpdb->insert('campaigns', $data);
        
        if (!empty($this->own_domain)) {
            $this->alertStaff();
        }
        
        do_action('Campaign-created', $data);
    }
    
    /**
     * Format location string based on $stateId and $cityId
     * 
     * @param int $stateId
     * @param int $cityId
     * @return string location string
     */
    protected function formatLocation($stateId, $cityId) {
        // temporary format to store state id and city id in the same field
        return $stateId . ':' . $cityId;
    }
    
    /**
     * Update campaign information
     * 
     * @return bool
     */
    public function update() {
        global $wpdb;

        $data = array('own_domain' => $this->own_domain, 'candidate_number' => $this->candidate_number, 'plan_id' => $this->plan_id, 'state' => $this->state,
            'city' => $this->city, 'observations' => $this->observations, 'status' => $this->status, 'blog_id' => $this->blog_id); 
        
        $data['location'] = $this->formatLocation($data['state'], $data['city']);
        unset($data['state'], $data['city']);
        
        $r = $wpdb->update('campaigns', $data, array('id' => $this->id));
        
        if (false !== $r)
            do_action('Campaign-updated', $data, $this->id);
        
        return $r;
        
    }
    
    /**
     * Delete a campaign from the database and
     * remove its associated blog.
     * 
     * @return null
     */
    public function delete() {
        global $wpdb;
        
        // only the owner or super admin can delete a campaign
        if (wp_get_current_user()->ID != $this->campaignOwner->ID && !is_super_admin()) {
            throw new Exception('Você não tem permissão para remover está campanha.');
        }
        
        $wpdb->query($wpdb->prepare("DELETE FROM `campaigns` WHERE `id` = %d", $this->id));
        
        wpmu_delete_blog($this->blog_id, true);
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
        
        if (is_wp_error($blogId)) {
            //TODO: improve error handling
            echo 'Não foi possível criar o blog!'; die;
        }

        $this->setBlogOptions($blogId);
        
        $this->createDefaultPagesAndMenu($blogId);
        
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
        update_blog_option($blogId, 'current_theme', 'Blog 01');
        update_blog_option($blogId, 'stylesheet', 'blog-01');
        update_blog_option($blogId, 'template', 'blog-01');
        
        // set upload limit
        $capabilities = Capability::getByPlanId($this->plan_id);
        update_blog_option($blogId, 'blog_upload_space', $capabilities->upload_limit->value);
        
        // enable contact page
        update_blog_option($blogId, 'campanha_contact_enabled', 'on');
        
        // enable "mobilize" menu entry option
        if ($capabilities->mobilize->value) {
            update_blog_option($blogId, 'mobilize', array('general' => array('menuItem' => true)));
        }
        
        // rename category "sem-categoria" to "noticias"
        wp_update_category(array('cat_ID' => 1, 'cat_name' => 'Notícias', 'category_nicename' => 'noticias'));
    }
    
    /**
     * Create default pages and corresponding menu entries
     * for the new blog.
     * 
     * @param int $blogId
     * @return null
     */
    protected function createDefaultPagesAndMenu($blogId) {
        if (switch_to_blog($blogId)) {
            //$this->createPost('page', 'Biografia', 'Edite essa página para colocar sua biografia. Se não quiser utilizar esta página você precisará removê-la do menu também.');
            //$this->createPost('page', 'Propostas', 'Edite essa página para colocar suas propostas. Se não quiser utilizar esta página você precisará removê-la do menu também.');
            
            if (!is_nav_menu('main')) {
                $menu_id = wp_create_nav_menu('main');

                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' => 'Capa',
                    'menu-item-url' => home_url('/'),
                    'menu-item-status' => 'publish')
                );
                /*
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' => 'Biografia',
                    'menu-item-url' => home_url('/biografia'), 
                    'menu-item-status' => 'publish')
                );
                
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' => 'Propostas',
                    'menu-item-url' => home_url('/propostas'), 
                    'menu-item-status' => 'publish')
                );
                */
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' => 'Mobilização',
                    'menu-item-url' => home_url('/mobilizacao'),
                    'menu-item-status' => 'publish')
                );
                
                wp_update_nav_menu_item($menu_id, 0, array(
                    'menu-item-title' => 'Contato',
                    'menu-item-url' => home_url('/contato'), 
                    'menu-item-status' => 'publish')
                );
                
                //TODO: couldn't make it work with as post_type so using custom menu items for now
                /*
                wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => 'Biografia',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => get_page_by_path('biografia')->ID,
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish')
                );
                
                wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => 'Propostas',
                    'menu-item-object' => 'page',
                    'menu-item-object-id' => get_page_by_path('propostas')->ID,
                    'menu-item-type' => 'post_type',
                    'menu-item-status' => 'publish')
                );*/
                
                set_theme_mod( 'nav_menu_locations', array('main' => $menu_id) );
                
            }
            
            // remove default page
            wp_delete_post(2, true);
            
            restore_current_blog();
        }
    }
    
    /**
     * Helper function to create a post or page.
     * 
     * @param string $title
     * @param string $content
     * @return null
     */
    protected function createPost($type, $title, $content) {
        $post = array(
            'post_author' => $this->campaignOwner->ID,
            'post_content' => $content,
            'post_title' => $title,
            'post_type' => $type,
            'post_status' => 'publish',
        );
        wp_insert_post($post);
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
