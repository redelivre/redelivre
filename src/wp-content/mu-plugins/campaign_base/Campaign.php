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
        
        return new Campaign($result);
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
        if ($this->valueExist('domain')) {
            $this->errorHandler->add('error', 'Este sub-domínio já está cadastrado.');
        }

        if ( empty($this->domain) || preg_match( '|^([a-zA-Z0-9-])+$|', $this->domain) === 0 ) {
            $this->errorHandler->add('error', 'O sub-domínio digitado está vazio ou inválido.');
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
        
        if (  preg_match( '|^(\d){2,5}$|', $this->candidate_number ) === 0 ) {
            $this->errorHandler->add('error', 'Número de candidato inválido.');
        }
        
        if ($this->candidateExist()) {
            $this->errorHandler->add('error', 'Uma campanha para este candidato já foi criada no sistema.');
        }
        
        if (empty($this->plan_id) || !in_array($this->plan_id, Plan::getAllIds())) {
            $this->errorHandler->add('error', 'Selecione o plano desejado.');
        }
        
        /*
        if (!in_array($this->plan_id, Plan::getAllIds())) {
            $this->errorHandler->add('error', 'O plano escolhido é inválido.');
        }
        * Tirei isso pq me parece que é pouco provável q acontela e gera um erro extra caso a pessoa deixe em branco
        * acrescentei a mesma checagem na condição acima (Leo)
        */
        
        if (empty($this->state)) {
            $this->errorHandler->add('error', 'Você precisa selecionar um estado.');
        }
        
        if (empty($this->city)) {
            $this->errorHandler->add('error', 'Você precisa selecionar uma cidade.');
        }
        
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
        
        if (empty($this->candidate_number) || empty($this->city) || empty($this->state)) {
            // all three fields above must be set to check if the candidate exist
            return false;
        }
        
        $campaign = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM `campaigns` WHERE `candidate_number` = %d AND `location` = %s",
                $this->candidate_number, "$this->state:$this->city"));
                
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
        
        do_action('Campaign-created', $data);
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
        update_blog_option($blogId, 'current_theme', 'Vencedor');
        update_blog_option($blogId, 'stylesheet', 'vencedor');
        update_blog_option($blogId, 'template', 'vencedor');
        
        // set upload limit
        $capabilities = Capability::getByPlanId($this->plan_id);
        update_blog_option($blogId, 'blog_upload_space', $capabilities->upload_limit->value);
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
            $this->createPost('page', 'Biografia', 'Edite essa página para colocar sua biografia. Se não quiser utilizar esta página você precisará removê-la do menu também.');
            $this->createPost('page', 'Propostas', 'Edite essa página para colocar suas propostas. Se não quiser utilizar esta página você precisará removê-la do menu também.');
            
            if (!is_nav_menu('main')) {
                $menu_id = wp_create_nav_menu('main');
                
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
            }
            
            // remove default page
            wp_delete_post(2, true);
            //remove default blog post
            wp_delete_post(1, true);
            
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
