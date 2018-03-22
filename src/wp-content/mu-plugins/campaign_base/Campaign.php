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
     * Return all available campaigns for the query.
     *
     * @param int $user_id
     * @param string $queryString
     * @return array array of Campaign objects
     */
    public static function findAll($orderby, $order, $offset, $limit, $queryString = null, $user_id = null) {
    	global $wpdb;
    
    	$join = '';
    	if($orderby == 'user_login')
    	{
    		$join = " inner join ".$wpdb->prefix."users ON campaigns.user_id = ".$wpdb->prefix."users.ID";
    	}
    	
    	$paged = ' LIMIT '.$limit.' OFFSET '.$offset; 
    	
    	if(is_null($queryString))
    	{
    		if ($user_id) {
    			$query = $wpdb->prepare('FROM `campaigns` '.$join.' WHERE user_id = %d ORDER BY `'.$orderby.'` '.$order, $user_id);
    		} else if (is_super_admin()) {
    			// only super admins should be able to see all campaigns
    			$query = 'FROM `campaigns` '.$join.' ORDER BY `'.$orderby.'` '.$order;
    		}
    	}
    	else 
    	{
	    	if ($user_id) {
	    		$query = $wpdb->prepare('FROM `campaigns` '.$join.' WHERE user_id = %d AND domain like %s ORDER BY `'.$orderby.'` '.$order, $user_id, $queryString);
	    	} else if (is_super_admin()) {
	    		// only super admins should be able to see all campaigns
	    		$query = $wpdb->prepare('FROM `campaigns` '.$join.' WHERE domain like %s ORDER BY `'.$orderby.'` '.$order, $queryString);
	    	}
    	}
    
    	$count = $wpdb->get_results('SELECT count(*) '.$query, ARRAY_N);

    	$results = $wpdb->get_results('SELECT * '.$query.$paged, ARRAY_A);
    	
    	$campaigns = array();
    
    	if ($results) {
    		foreach ($results as $result) {
    			$campaigns[] = new Campaign($result);
    		}
    	}
    
    	return (object)array('itens' => $campaigns, 'count' => $count[0][0]);
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
            //throw new Exception(self::getStrings('NaoExiste'));
            return false;
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
            throw new Exception(self::getStrings('NaoEncontrado'));
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
        
        /**
         * Retirando obrigatoriedade do campo Estado e número
         * @author Jacson Passold (Ethymos)
         */
        
        /*if ($this->candidateExist()) {
            $this->errorHandler->add('error', self::getStrings('candidateExist'));
        }
        */
        
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
        
        if( !function_exists('user_can_create_campanha') || !user_can_create_campanha())
        	return false;
        
        $location = $this->formatLocation($this->state, $this->city);

        $this->blog_id = $this->createNewBlog();
        
        $data = array(
            'user_id' => $this->campaignOwner->ID, 'plan_id' => $this->plan_id, 'blog_id' => $this->blog_id,
            'election_id' => $this->election_id, 'domain' => $this->domain, 'own_domain' => $this->own_domain, 'candidate_number' => $this->candidate_number,
            'status' => 1, 'creation_date' => date('Y-m-d H:i:s'), 'location' => $location, 'observations' => $this->observations,
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
            throw new Exception(Campaign::getStrings('SemPermissao'));
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
        $subject = Campaign::getStrings('DominioProprio')." {$this->own_domain}";
        $message = "O usuário $userName ".self::getStrings('CriouNovo')." <a href='{$this->domain}'>{$this->domain}</a> e o domínio próprio <a href='{$this->own_domain}'>{$this->own_domain}</a>.";
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
        
        // allow change of blog model
        $model = get_user_meta(get_current_user_id(), 'blogmodel', true);
        
        if(empty($model))
        {
	        // set defaut campaign theme
	        update_blog_option($blogId, 'current_theme', 'Blog 01');
	        update_blog_option($blogId, 'stylesheet', 'blog-01');
	        update_blog_option($blogId, 'template', 'blog-01');
        }
        elseif(file_exists(WPMU_PLUGIN_DIR."/campaign_base/models/".basename($model).".php")) 
        {
        	include WPMU_PLUGIN_DIR."/campaign_base/models/".basename($model).".php";
        }
        
        // set upload limit
        $capabilities = Capability::getByPlanId($this->plan_id);
        update_blog_option($blogId, 'blog_upload_space', $capabilities->upload_limit->value);
        
        // enable contact page
        update_blog_option($blogId, 'projeto_contact_enabled', 'on');
        
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
        	// allow change of blog model
        	
        	$model = get_user_meta(get_current_user_id(), 'blogmodel', true);
        	
        	if(empty($model))
        	{
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
        	}
            
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
    
    public static function getStrings($id = '')
    {
    	$strings = array();

    	//CampaignTable.php
        $strings['label']['singular'] = __('Nome <b>singular</b>, em letras minúsculas, dado a cada blog dentro da rede', 'redelivre');
		$strings['value']['singular'] = __('projeto', 'redelivre');	//'singular'  => 'projeto',     //singular name of the listed records
        $strings['label']['plural'] = __('Nome <b>plural</b>, em letras minúsculas, dado a cada blog dentro da rede', 'redelivre');
    	$strings['value']['plural'] = __('projetos', 'redelivre');	//'plural'    => 'projetos',    //plural name of the listed records
        $strings['label']['remover'] = __('Alerta sobre a impossibilidade de recuperação de dados após a remoção de um blog da rede', 'redelivre');
    	$strings['value']['remover'] = __('Você tem certeza de que deseja remover permanentemente este projeto? Não será possível desfazer esta ação e todos os dados serão perdidos.', 'redelivre');

    	//Campaign.php
        $strings['label']['NaoExiste'] = __('', 'redelivre');
    	$strings['value']['NaoExiste'] = __('Não existe um projeto associado a este blog. Verifique se você não selecionou um tema de projeto para o site principal.', 'redelivre');
        $strings['label']['NaoEncontrado'] = __('Alerta sobre projeto não localizado pelo sistema', 'redelivre');
    	$strings['value']['NaoEncontrado'] = __('Desculpe, não foi possível encontrar este projeto.', 'redelivre');
        $strings['label']['candidateExist'] = __('', 'redelivre');
    	$strings['value']['candidateExist'] = __('Um projeto para este usuário já foi criado no sistema.', 'redelivre');
        $strings['label']['SemPermissao'] = __('Aviso de que o usuário não tem permissão para remover um blog da rede', 'redelivre');
    	$strings['value']['SemPermissao'] = __('Desculpe, seu usuário não tem permissão para remover este projeto.', 'redelivre');
        $strings['label']['DominioProprio'] = __('Confirmação da criação de blog com domínio próprio na rede', 'redelivre');
    	$strings['value']['DominioProprio'] = __('Um novo projeto foi criado com seu domínio próprio.', 'redelivre');
        $strings['label']['CriouNovo'] = __('Confirmação da criação de blog com domínio próprio na rede', 'redelivre');
    	$strings['value']['CriouNovo'] = __('Um novo projeto foi criado no sub-domínio.', 'redelivre');

    	// campaigns_edit.php
        $strings['label']['SemPermissaoEditar'] = __('Aviso de que o usuário não tem permissão para editar blogs da rede', 'redelivre');
    	$strings['value']['SemPermissaoEditar'] = __('Desculpe, seu usuário não tem permissão para editar projetos.', 'redelivre');

    	// campaigns_list.php
        $strings['label']['ProcurarProjeto'] = __('Texto da pesquisa por blogs na rede', 'redelivre');
    	$strings['value']['ProcurarProjeto'] = __('Procurar Projeto', 'redelivre');
        $strings['label']['NaoCriou1'] = __('', 'redelivre');
    	$strings['value']['NaoCriou1'] = __('Você ainda não criou nenhum projeto. Para isso vá para a', 'redelivre');
        $strings['label']['NaoCriou2'] = __('', 'redelivre');
    	$strings['value']['NaoCriou2'] = __('página de criação de projetos', 'redelivre');

    	// campaigns_new.php
        $strings['label']['NaoFoiPossivelCriar'] = __('Alerta sobre falha na criação de novo blog na rede', 'redelivre');
    	$strings['value']['NaoFoiPossivelCriar'] = __('Desculpe, não foi possível criar o projeto.', 'redelivre');
        $strings['label']['NovoProjeto'] = __('Texto do campo para criação de novo blog na rede', 'redelivre');
    	$strings['value']['NovoProjeto'] = __('Novo projeto', 'redelivre');

    	// functions.php
        $strings['label']['ProjetoVisivel'] = __('Aviso sobre o blog não estar visível por não ter sido ativado ainda', 'redelivre');
    	$strings['value']['ProjetoVisivel'] = __('Por enquanto, este projeto está visível somente para o criador pois ainda não foi ativado.', 'redelivre');
        $strings['label']['AtualizePlano'] = __('', 'redelivre');
    	$strings['value']['AtualizePlano'] = __('Este projeto está visível somente para o criador pois foi selecionado um tema não disponível para o seu plano. O seu plano permite o uso apenas dos temas da família \"Blog 01\". Mude o tema ou atualize o plano.', 'redelivre');
    	$strings['label']['NaoDisponivel'] = __('', 'redelivre');
    	$strings['value']['NaoDisponivel'] = __('Este projeto ainda não está disponível.', 'redelivre');
    	
    	// custom_admin.php
    	$strings['label']['MenuPrincipal'] = __('Nome dado aos blogs, no <b>plural</b>, presente no menu administrativo da raiz da rede', 'redelivre');
    	$strings['value']['MenuPrincipal'] = __('Projetos', 'redelivre');	//'plural'    => 'projetos',    //Como aparece no menu
    	$strings['label']['MenuPlataforma'] = __('Nome dado à rede na barra superior administrativa', 'redelivre');
    	$strings['value']['MenuPlataforma'] = __('Rede Livre', 'redelivre');	//'plural'    => 'projetos',    //Como aparece no menu
    	
    	//campaigns_edit.php
    	$strings['label']['AtualizadoSucesso'] = __('Resposta do sistema quando o administrador conclui a edição de um blog na rede', 'redelivre');
    	$strings['value']['AtualizadoSucesso'] = __('Projeto atualizado com sucesso!', 'redelivre');
    	$strings['label']['Editar'] = __('Texto que vem antes do nome do blog na tela de edição de cada um deles na rede', 'redelivre');
    	$strings['value']['Editar'] = __('Editar projeto:', 'redelivre');
    	
    	//campaign_list.php
    	$strings['label']['Seus'] = __('Título da página que lista os blogs da rede, visíveis de acordo com o nível de permissão do usuário', 'redelivre');
    	$strings['value']['Seus'] = __('Seus projetos', 'redelivre');
    	$strings['label']['RemovidoSucesso'] = __('Confirmação de remoção de blog na rede', 'redelivre');
    	$strings['value']['RemovidoSucesso'] = __('Projeto {domain} removido com sucesso!', 'redelivre');
    	
    	//campanha.php
    	$strings['label']['Sobre'] = __('', 'redelivre');
    	$strings['value']['Sobre'] = __('Sobre o Projeto', 'redelivre');
    	
    	// javascritps
    	
    	//campaign_common.js
    	$campaign_common_strings = array();
    	$campaign_common_strings['label']['MeusProjetos'] = __('Nome de listagem dos blogs por usuário na barra superior administrativa', 'redelivre');
    	$campaign_common_strings['value']['MeusProjetos'] = __('Meus projetos', 'redelivre');
    	$campaign_common_strings['label']['AdministrarProjetos'] = __('', 'redelivre');
    	$campaign_common_strings['value']['AdministrarProjetos'] = __('Administrar projetos', 'redelivre');

    	$opts_campaign_common = get_site_option('campanha_defined_settings_campaign_common_strings', array());
    	if(!is_array($opts_campaign_common)) $opts_campaign_common = array();
    	
    	if(array_key_exists('value', $opts_campaign_common))
    	{
    		$opts_campaign_common = $opts_campaign_common['value'];
    		update_site_option('campanha_defined_settings_campaign_common_strings', $opts_campaign_common);
    	}
    	$campaign_common_strings['value'] = array_merge($campaign_common_strings['value'], $opts_campaign_common);
    	
    	wp_localize_script('campaign_common', 'campaign_common', $campaign_common_strings);
    	//END campaign_common.js
    	//END javascritps
        
        // Merge default settings com defined settings
    	$opts = get_site_option('campanha_defined_settings_strings', array());
        if(is_array($opts) && array_key_exists('value', $opts))
        {
        	$opts = $opts['value'];
        }
        elseif(!is_array($opts))
        {
        	$opts = array();
        }
        
        $strings['value'] = array_merge($strings['value'], $opts);
        
    	if($id != '')
    	{
    		return array_key_exists($id, $strings['value']) ? $strings['value'][$id] : '';
    	}
    	
    	$strings['campaign_common'] = $campaign_common_strings;

   		return $strings; 
    }

    public static function saveDefinedSettingsStrings()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['settings_strings']) && is_array($_POST['settings_strings']))
        {
        	$strings = self::getStrings();
            $_POST['settings_strings'] = array_merge($strings['value'], $_POST['settings_strings']);

            if (update_site_option('campanha_defined_settings_strings', $_POST['settings_strings']))
            {
                echo 'Dados atualizados com sucesso!';
            }
            
            foreach ($_POST as $key => $value)
            {
            	$pkey = strpos($key, 'settings_strings');
            	if($pkey === 0 || $pkey === false) continue;
            	
            	$knowKey = substr($key, 0, $pkey);
            	if(array_key_exists($knowKey, $strings))
            	{
            		$merge = array_merge($strings[$knowKey]['value'], $_POST[$knowKey.'settings_strings']);

            		if (update_site_option('campanha_defined_settings_'.$knowKey.'_strings', $merge))
            		{
            			echo 'Dados '.$knowKey.' atualizados com sucesso!';
            		}
            	}
            }
        }
    }
}