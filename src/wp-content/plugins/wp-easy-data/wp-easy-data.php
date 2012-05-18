<?php
/*
Plugin Name: Wp Easy Data
Plugin URI: 
Description: Extensible CRUD Class for simple admins
Author: hacklab
Version: 0.1 
Text Domain: wp-easy-data
*/

/*
$sampleModel = array(
 
    'fields' => array (
        array(
            'name' => 'name',
            'display_name' => 'Nome',
            'type' => 'textfield',
            'list_display' => true,
            'description' => 'nono noonono noonon',
            'default' => 'default value'
        ),
    ),
    'tableName' => 'xis',
    'adminName' => 'Xis'

);
*/


function Wp_easy_data_init() {
	
	
	
	define('H_CRUD_FOLDER', plugin_basename( dirname(__FILE__)) );
	define('H_CRUD_ABSPATH', WP_CONTENT_DIR.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );
	define('H_CRUD_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)).'/' );
	
	class Wp_easy_data {
	    /**
		* @var name of dataset
		* @access public
		*/
	    var $name;
		
		/**
		* @var table name with wp_prefix
		* @access public
		*/
		var $table;
		
		
		var $message;
		
	    var $permission;
	    var $fields;
	    var $file;
	    var $relative_path;
	    var $acceptedFields;
	    var $sortable;
	    var $pagination_current = 1;
	    var $pagination_items_per_page = 0;
	    var $pagination_items;
	    var $parent_menu;
	    var $menu_name;
	    var $external_label_index;
	    var $custom_external_label_index;
		var $belongsTo = array();
		var $hasManyAndBelongsTo = array();
		var $richEditorButtons = "bold,italic,underline,left,center,right,justify,ol,ul,indent,outdent,hr,forecolor,bgcolor,link,unlink,fontSize";
		var $items_recursive_limit = 2 ;
		var $items_recursive_reached = 0;
	    
		
		
		
		/**
		 * 
		 * @param Array $model with model see http://XXXX.com
		 * @param String $file full file path (use __FILE__ )
		 * @param Mixed $permission Permission or Capability (ex: 'manage_options' or 8 ) to access the wp-easy-data  ( see: http://codex.wordpress.org/Roles_and_Capabilities)
		 */
	   public function Wp_easy_data ($model, $file, $permission = 8) {
	        global $wpdb;
	        
	        if (!is_array($model))
	          return false;
	        
	        load_plugin_textdomain('wp-easy-data', H_CRUD_ABSPATH . 'languages/', basename(dirname(__FILE__)) . '/languages/');
	          
	        $this->name = $model['tableName'];
	        
	        $this->table = $wpdb->prefix . $this->name;
	        $this->menu_name = 'manage_' . $this->name;
	        $this->permission = $permission;
	        $this->fields = $model['fields'];
	        $this->adminName = $model['adminName'];
	        $this->file = $file;
			
            $this->external_name = $model['external_name'] ? $model['external_name'] : $this->name . '_ID';
            $this->external_field = $model['external_field'] ? $model['external_field'] : 'ID';
			
			
	        $this->relative_path = str_replace(WP_CONTENT_DIR . '/plugins/', '', $this->file);
		
	        $this->sortable = ($model['sortable']) ? true : false;
	        $this->parent_menu = ($model['parent_menu']) ? $model['parent_menu'] : false;
            $this->topMenuName = ($model['topMenuName']) ? $model['topMenuName'] : false;
	        $this->external_label_index = ($model['external_label_index']) ? $model['external_label_index'] : 0;
	        $this->custom_external_label_index = ($model['custom_external_label_index']) ? $model['custom_external_label_index'] : array();
	        
			
			
			
			$this->is_admin = false;
			
	        // is_admin - true if currently navigating on the admin page of this instance
			if(quotemeta($_GET['page']) !== FALSE)
				$this->is_admin = ((preg_match('|' . quotemeta($_GET['page']) . '|', $this->relative_path) && !$this->parent_menu)  || $_GET['page'] == $this->menu_name) ? true : false;
	        
			// Pagination
	        if ($_GET['pagination_current']) $this->pagination_current = $_GET['pagination_current'];
	        if ($_GET['pagination_items_per_page']) $this->pagination_items_per_page = $_GET['pagination_items_per_page'];
	        $this->pagination_items = $this->get_number_of_items( $this->build_search_clause($_GET['search']) );
	        
	        $this->acceptedFields = array(
	            'textfield' => array(
	                'dbtype' => 'varchar(255)',
	                'form' => '<input type="text" id="%key%" name="%key%" value="%value%" class="%class%">' 
	            ),
				'passfield' => array(
					'dbtype' => 'varchar(255)',
					'form' => '<input type="password" id="%key%" name="%key%" value="%value%" class="%class%">' 
				),
	            'textarea' => array(
	                'dbtype' => 'TEXT',
	                'form' => '<textarea id="%key%" name="%key%" class="%class%" >%value%</textarea>' 
	            ),
	            'file' => array(
	                'dbtype' => 'int(11)',
	                'form' => '<input type="file" name="%key%"  class="%class%">' 
	            ),
	            'date' => array(
	                'dbtype' => 'datetime',
	                'form' => '<input type="text" id="%key%" name="%key%" value="%value%" class="wp-easy-data-calendar %class%">'
	            ),
	            'hiddenInt' => array(
	                'dbtype' => 'int(11)',
	                'form' => '<input type="hidden" id="%key%" name="%key%" value="%value%">' 
	            ),
	            'hiddenText' => array(
	                'dbtype' => 'varchar(255)',
	                'form' => '<input type="hidden" id="%key%" name="%key%" value="%value%">' 
	            ),
                'color' => array(
                    'dbtype' => 'varchar(7)',
                    'form' => '<input maxlength="7" type="text" id="%key%" name="%key%" value="%value%" class="wp-easy-data-color %class%">' 
                ),
                'richEditor' => array(
                    'dbtype' => 'TEXT',
                    'form' => '<textarea id="%key%" name="%key%" class="wp-easy-data-richEditor %class%">%value%</textarea>' 
                ),
                'select' => array(
                    'dbtype' => 'varchar(255)',
                    'form' => '<select id="%key%" name="%key%" class="%class%">' 
                ),
                'radio' => array(
                    'dbtype' => 'varchar(255)',
                    'form' => '' 
                ),
                 'checkboxes' => array(
                    'dbtype' => 'TEXT',
                    'form' => '' 
                ),
                'bool' => array(
                    'dbtype' => 'BOOL',
                    'form' => '<input type="checkbox" value="1" id="%key%" name="%key%" class="%class%" %checked%>' 
                ),                
	        );
	        
	       
	        // Sortable
	        
	        if ($this->sortable) {
	            
	        	$this->fields[] = array(
	                'name' => 'item_order',
	                'display_name' => 'Order',
	                'type' => 'hiddenInt',
	                'list_display' => false,
	                'description' => 'Item order');
	            
	            $this->add_action('wp-easy-data-table-newColLeft-title', array(&$this, 'addSortableHeading'));
	            $this->add_action('wp-easy-data-table-newColLeft-value', array(&$this, 'addSortableForm'));
	            $this->add_action('admin_print_scripts', array(&$this, 'addSortableJS'));
	            $this->add_action('admin_print_styles', array(&$this, 'addSortableCSS'));
	            $this->add_action('wp-easy-data-tablenav', array(&$this, 'print_sortable_tablenav'));
	            $this->add_action('wp-easy-data-admin-init', array(&$this, 'sortable_save'));
	            $this->sortable_offset = ($this->pagination_items_per_page == 0) ? 1 : ($this->pagination_items_per_page * $this->pagination_current) - ($this->pagination_items_per_page -1);
	
	        }
	        
	        //add menu
	        add_action('admin_menu', array(&$this, 'add_menu'));
	        
			
			
	        //add JS
	        if ($this->is_admin) {
	            $this->database();
	            $this->add_action('admin_print_scripts', array(&$this, 'addJS'));
	            $this->add_action('admin_print_styles', array(&$this, 'addCSS'));
	        }
	        
	        // filter for accepted mime types on file upload
	        add_filter('upload_mimes', array(&$this, 'customUploadMimeTypes'));
	        
	        
	        $this->add_action('wp-easy-data-after-save', array(&$this, 'save_relations'));
	        $this->add_action('wp-easy-data-delete', array(&$this, 'delete_relations'));
	    }
	    
	    //************* Database Methods *****************//
	    
		/**
		 * This method creates the table based on the model provided
		 * This method also checks if you have changed the model and then prompts for a confirmation to change the table structure
		 */
	   private function database() {
	        global $wpdb;
	        
	        foreach ($this->fields as $key => $field) {
	        		$sql =  $this->get_field_sql($field);
		            $create .= "`{$field['name']}` $sql, ";
	        }
	        //create tables
	        mysql_query("CREATE TABLE IF NOT EXISTS {$this->table} (
	                 `ID` int(11) NOT NULL auto_increment,
	                 $create
	                 PRIMARY KEY (`ID`)) DEFAULT CHARSET=utf8");
	                
	        //check for changes in the model
	        $results = $wpdb->get_results("DESC {$this->table}", "ARRAY_A");
	        
	        $structure = array();
            $keysQ = $wpdb->get_results("SHOW KEYS IN {$this->table}", "ARRAY_A");
            $keys = array();
            foreach($keysQ as $k) {
                if ($k['Key_name'] == 'PRIMARY' || preg_match('/^fk/', $k['Key_name']) )
                    array_push($keys, $k['Column_name']);
            }
            
	        foreach ($results as $col) {
				
                if (!in_array($col['Field'], $keys))
	               $structure[] = $col['Field'];
	        }
	        
	        $fields = array();
	        foreach ($this->fields as $key => $field) {
				$fields[$key] = $field['name'];
	        }
	
	        # Lets only spend our time if there is something different
	        if ($fields !== $structure) {
	            
	            $x = array_diff($fields, $structure);
	
	            if (sizeof($x) > 0) {
	                # You have added one or more fields to the model
	                $this->changedModel->add = array();
	                foreach ($x as $key => $new) {
	                    $this->changedModel->add[] = $this->fields[$key];
	                }
	                if ($_POST['wp-easy-data-confirm-db-mod'])
	                   $this->add_fields_to_db($this->changedModel->add);
	            }
	            
	            $x = array_diff($structure, $fields);
	            if (sizeof($x) > 0) {
	                # You have removed one or more fields from the model
	                $this->changedModel->remove = array();
	                foreach ($x as $old) {
	                    $this->changedModel->remove[] = $old;
	                }
	                if ($_POST['wp-easy-data-confirm-db-mod'])
	                    $this->remove_fields_from_db($this->changedModel->remove);
	            }
	        }
	
	    }
	   
	    /**
		 * Return SQL field definition using acceptedFields matching to the field
		 * @param Array $field  the field defined on the model ( passed on contructor )
		 */ 
	    private function get_field_sql($field) {
	    	global $wpdb;
	    	$field_sql = str_replace('%field_name%', $field['name'], $this->acceptedFields[$field['type']]['dbtype']);
            $field_sql = str_replace('%related_model%', $wpdb->prefix . $field['related_model'], $field_sql);
            
            if (isset($field['default']))
                $field_sql .= ' DEFAULT "' . $field['default'] . '"';
            
            return $field_sql;
	    }
	    
		/**
		 * Add fields to the model table
		 * Used when model is changed  (table already exists), and user confirms the modification
		 * @param Array $fields new fields defined when the model has changed
		 * @see database();
		 */
	    private function add_fields_to_db($fields) {
	       foreach ($fields as $field) {
	           $sql =  $this->get_field_sql($field);
	           $sql = str_replace(', FOREIGN', ', ADD FOREIGN', $sql);
	       	   $update .= "ADD `{$field['name']}` $sql, ";
	       }
	       $update = substr($update, 0, strlen($update) - 2);
	       mysql_query("ALTER TABLE {$this->table} $update");
	       unset($this->changedModel->add);
	       if (!$this->changedModel->remove) unset($this->changedModel);
	    
	    }
	   
	   /**
	    * Remove fields to the model table
		* Used when model is changed (table already exists), and user confirms the modification
		* @param Array $fields old fields defined when the model has changed
		* @see database();
		*/ 
	   private function remove_fields_from_db($fields) {
	       foreach ($fields as $field) {
	            $update .= "DROP `$field`, ";
	       }
	       $update = substr($update, 0, strlen($update) - 2);
	       mysql_query("ALTER TABLE {$this->table} $update");
	       unset($this->changedModel->remove);
	       if (!$this->changedModel->add) unset($this->changedModel);
	    }
	   
	   
	   /**
	    * This method saves all relations of hasManyAndBelongsTo
		* Delete the old entries and add all passed by $_POST
		* Called always on action 'wp-easy-data-after-save' 
		* @param Wp_easy_dataItem $item
		* @see add_hasManyAndBelongsTo();
		*/ 
	   public function save_relations($item) {
	    	
			foreach ( $this->hasManyAndBelongsTo as $hmbt ) {
				$relational_table = $this->relation_table_name( $hmbt->obj->name ) ;
				// the function delete_relations isn't used here because has the same loop used here
				mysql_query("DELETE FROM {$relational_table} WHERE $this->external_name = $item->ID");
				if (is_array($_POST[$hmbt->obj->name])) {
					foreach ($_POST[$hmbt->obj->name] as $post) 
						mysql_query("INSERT INTO {$relational_table} ({$this->external_name}, {$hmbt->obj->external_name }) VALUES ($item->ID, $post)");
				}
			}	    	
	    }
	    
		/**
		 * This method delete all relations of hasManyAndBelongsTo
		 * Called always on action 'wp-easy-data-delete'
		 * @param Wp_easy_dataItem $item
		 * @see add_hasManyAndBelongsTo();
		 */		
	    public function delete_relations($item) {
			
            foreach ( $this->hasManyAndBelongsTo as $hmbt ) {
				$relational_table = $this->relation_table_name( $hmbt->obj->name ) ;
				mysql_query("DELETE FROM {$relational_table} WHERE $this->external_name = $item->ID");
			}
        }
        
		/**
		 * This method create a 1:N relation betwen two Wp_easy_data instances,
		 * @param Wp_easy_data &$obj The object who make relation with this instance
		 * @param String $realationship_label the label of relationship on admin interface
		 * @param String $realationship_description the description of relationship on admin interface
		 * @param Boolean $list_display verify if some data of this relationship will be displayed at list
		 * @param Int $form_position the position in form at de create/edit interface ( starts from 1, 0 to be the last)
		 */ 
		public function add_belongsTo( &$obj, $realationship_label, $realationship_description, $list_display = false, $form_position = 0 ) {
			
				
			if (is_object($obj) && isset($obj->name) && isset($obj->table)) {
				
				/*$obj->relation_{$this->name}->label = $realationship_label;
				$obj->relation_{$this->name}->description = $realationship_description;
				$obj->relation_{$this->name}->form_position = $form_position;
				$obj->relation_{$this->name}->list_display = $list_display;
				*/
				
				$this->belongsTo[$obj->name]->label = $realationship_label;
				$this->belongsTo[$obj->name]->description = $realationship_description;
				$this->belongsTo[$obj->name]->form_position = $form_position;
				$this->belongsTo[$obj->name]->list_display = $list_display;
				
				$this->belongsTo[$obj->name]->obj = $obj;
				
				#$related_field = $obj->fields[$obj->get_external_label_index($this->name)] ;
				if ($this->is_admin) {
					$sql = "ALTER TABLE $this->table ADD `{$obj->external_name}` int(11), ADD FOREIGN KEY fk_{$obj->external_name}({$obj->external_name}) REFERENCES {$obj->table}({$obj->external_field})";
					mysql_query($sql);
				}
			} else {
				return false;
			}	
		}
		
		/**
		 * This method create a N:N relation betwen two Wp_easy_data instances,
		 * Only the instance whos call this method will show the options at admin interface
		 * @param Wp_easy_data &$obj The object who make relation with this instance
		 * @param String $realationship_label the label of relationship on admin interface
		 * @param String $realationship_description the description of relationship on admin interface
		 * @param Boolean $list_display verify if some data of this relationship will be displayed at list
		 * @param Int $form_position the position in form at de create/edit interface ( starts from 1, 0 to be the last)
		 * @see delete_relations(), save_relations()
		 */ 
		function add_hasManyAndBelongsTo( &$obj, $realationship_label, $realationship_description, $list_display = false, $form_position = 0 ) {
			
			if (is_object($obj) && isset($obj->name) && isset($obj->table) ) {
				
				$this->hasManyAndBelongsTo[$obj->name]->label = $realationship_label;
				$this->hasManyAndBelongsTo[$obj->name]->description = $realationship_description;
				$this->hasManyAndBelongsTo[$obj->name]->form_position = $form_position;
				$this->hasManyAndBelongsTo[$obj->name]->list_display = $list_display;
				
				$this->hasManyAndBelongsTo[$obj->name]->obj = $obj;
				
				if ($this->is_admin) {
						
					global $wpdb;
								
					$newTable = $this->relation_table_name($obj->name);
					//DEBUG
						#var_dump($this->name, $obj->name);
						#echo "relation table $newTable <br><hr><br>";
					
					$sql ="CREATE TABLE IF NOT EXISTS $newTable (
					 ID int(11) NOT NULL auto_increment,
					 {$this->external_name} int(11),
					 {$obj->external_name} int(11),
					 FOREIGN KEY ({$this->external_name}) REFERENCES $this->table(ID),
					 FOREIGN KEY ({$obj->external_name}) REFERENCES " . $wpdb->prefix . $obj->name ."(ID),
					 PRIMARY KEY (ID)) DEFAULT CHARSET=utf8";
					 
					 mysql_query($sql);
				}
				
				
				
				$this->add_action('wp-easy-data-after-save', array(&$this, 'save_relations'));
				$this->add_action('wp-easy-data-delete', array(&$this, 'delete_relations'));
			} else {
				return false;
			}
			
			
		}
		
		
		
		
	    //************* Building Interface Methods *****************//
	    
		/**
		 * Load admins page, list (with form to insert new itens) or database uldate confirmation 		 * 
		 */
	    function admin() {
	        if ($this->changedModel) {
	            require_once('wp-easy-data-modifydb.php');
	        } else {
	            //include admin interface
	            require_once('wp-easy-data-admin.php'); 
	        }
	    }
	    
		
		/**
		 * Add link to menu in wp admin page
		 * called by hook "admin_menu"
		 */
	    function add_menu() {
			
	    	if ($this->parent_menu) {
	    		add_submenu_page(basename($this->parent_menu), $this->adminName, $this->adminName, $this->permission, $this->menu_name, array(&$this, 'admin'));
	    	} else {
                $menuName = $this->adminName;
                if ($this->topMenuName) {
                    $menuName = $this->topMenuName;
                    add_submenu_page(basename($this->file), $this->adminName, $this->adminName, $this->permission, basename($this->file), array(&$this, 'admin'));
                
                }
	    		add_menu_page($menuName, $menuName, $this->permission, basename($this->file), array(&$this, 'admin'));
	    	}
	        
	    }
	    
		/**
		 * Add all necessary JS
		 * called by hook "admin_print_scripts"
		 */
	    public function addJS() {
	        wp_enqueue_script( 'jquery_datepicker', H_CRUD_URLPATH . 'datepicker/jquery-ui-personalized-1.6rc2.min.js', array('jquery'));
	        wp_enqueue_script( 'agenda_jquery_local', H_CRUD_URLPATH . 'datepicker/ui.datepicker-pt-BR.js', array('jquery_datepicker'));
	        wp_enqueue_script( 'wp-easy-data-colorpicker', H_CRUD_URLPATH . 'colorpicker/js/colorpicker.js', array('jquery'));
	        wp_enqueue_script( 'nicedit', H_CRUD_URLPATH . 'nicEdit/nicEdit.js');
	        wp_enqueue_script( 'wp-easy-data', H_CRUD_URLPATH . 'wp-easy-data.js', array('jquery_datepicker', 'agenda_jquery_local', 'wp-easy-data-colorpicker', 'nicedit'));
	        wp_localize_script('wp-easy-data', 'wp_easy_data', array('baseUrl' => H_CRUD_URLPATH, 'richEditorButtons' => $this->richEditorButtons));
	    }
	    
		/**
		 * Add all necessary CSS
		 * called by hook "admin_print_style"
		 */
	    public function addCSS() {
            wp_enqueue_style('wp-easy-data-admin', H_CRUD_URLPATH . 'wp-easy-data.css');
	    	wp_enqueue_style('wp-easy-data-admin-date', H_CRUD_URLPATH . 'datepicker/datepicker.css');
	        wp_enqueue_style('wp-easy-data-admin-color', H_CRUD_URLPATH . 'colorpicker/css/colorpicker.css');
	    }


			
		/**
		 * Print html table row to edit or add a relation
		 * @param String $belongsTo_name Relational object name (another instance name of Wp_easy_data)
		 * @param Wp_easy_dataItem $itmEdit if is null thats means it's a new register otehrwise is editing one
		 * @see add_belongsTo();
		 */
	    function print_row_belongsTo($belongsTo_name, $itemEdit = NULL) {

			$value = '';
			
			if (is_object($itemEdit)) {
				
				if (isset($itemEdit->info->{$this->belongsTo[$belongsTo_name]->obj->external_name})) {
					$value = $itemEdit->info->{$this->belongsTo[$belongsTo_name]->obj->external_name};
				}
			}
			
			$belongsTo = $this->belongsTo[$belongsTo_name];
			
			echo '<tr class="form-field">
                        
                        <th scope="row"><label for="' . $belongsTo->obj->external_name . '">
                        ' . $belongsTo->label . ' </label></th>
                        
                        <td>';
                        
            $this->print_belongsTo_field($belongsTo_name, $value);
			
			echo '<small>
						' .  $belongsTo->description . '
					</small>
				</td>
			</tr>';
		}
		
		/**
		 * Print html table row to edit or add a relation
		 * @param String $hasManyAndBelongsTo_name Relational object name (another instance name of Wp_easy_data)
		 * @param Wp_easy_dataItem $itmEdit if is null thats means it's a new register otehrwise is editing one
		 * @see add_hasManyAndBelongsTo();
		 */
		function print_row_hasManyAndBelongsTo( $hasManyAndBelongsTo_name, $itemEdit = '' ) {
			global $wpdb;
			
			$relation_table = $this->relation_table_name($hasManyAndBelongsTo_name);
			
			$hmbt = $this->hasManyAndBelongsTo[$hasManyAndBelongsTo_name];
			
			$records = $hmbt->obj->get_items();
			
			$query = 'SELECT ' . $hmbt->obj->external_name . ' as ID FROM ' . $relation_table . ' WHERE ' . $this->external_name . '=' . $itemEdit->ID;
			$related = $wpdb->get_results($query);
	    	/*	
			$query = 'SELECT ID, ' . $field['related_field'] . ' FROM ' . $wpdb->prefix . $field['related_model'];
			
			
			if ($value)
				 $selected = $wpdb->get_col("SELECT {$field['related_model']} FROM {$field['relational_table']} WHERE $this->name = $value");
			* 
			* */
			$check = array();
			foreach ($related as $row) {
				$check[$row->ID] = true;
			}
			
			
			
			
			$field = $hmbt->obj->fields[$hmbt->obj->get_external_label_index($this->name)];
			$key = $field['name'];
			foreach ($records as $record) {
				$ops .= '<input type="checkbox" name="' . $hasManyAndBelongsTo_name  . '[]" value="' . $record->ID . '"';
				if ($check[$record->ID])
					$ops .= ' checked';
				$ops .= '>' . $record->info->$key  . '<br />';
				
				
				
			}
			
			
			echo '<tr class="form-field">
						<th scope="row"><label for="' . $hmbt->obj->external_name . '">
							' . $hmbt->label . ' </label>
						</th>
                        <td>
						<small>' .  $hmbt->description . '</small>
						
							<div class="hasMany" id="', $key, '" name="', $key, '">';
								echo $ops;
			echo '			</div>
						</td>
					</tr>';
		}
	    
	    
		
		/**
		 * ???????????????????????????
		 * @see print_row_belongsTo();
		 */
		function print_belongsTo_field($belongsTo_name, $value = '') {
			$belongsTo = $this->belongsTo[$belongsTo_name];
			$field = $belongsTo->obj->fields[$belongsTo->obj->get_external_label_index($this->name)];

			$data = $belongsTo->obj->get_items();
	
			foreach ($data as $record) {
				$ops .= '<option value="' . $record->info->{$belongsTo->obj->external_field} . '"';
				if ($value == $record->info->{$belongsTo->obj->external_field}) $ops .= ' selected';
				$ops .= '>' . $record->info->{$field['name']} . '</option>';
			}
			
			echo '<select name="', $belongsTo->obj->external_name, '" id="', $belongsTo->obj->external_name, '">';
			echo '<option value="">', __('Select', 'wp-easy-data'), '</option>';
			echo $ops;
			echo '</select>';
			
		}
	    
	    function print_field($field, $value) {
 			
			$class = ( isset($field['css_class']) ) ? $field['css_class'] . " " : " ";
				
			if( isset($field['required']) && ( $field['required'] === true || ($field['required'] == 'register' && ( !isset($_SESSION[$this->name]->ID) && !is_user_logged_in() )))) {
				$class.= "required ";				
			}	
			if(isset($field['validation'])) {
				if(is_array($field['validation'])) {
					foreach ($field['validation'] as $t)
						$class.= "$t ";
				}else {
					$class.= $field['validation']." ";
				}
			}
 
            $key = $field['name'];
	    	/*if ($field['type'] == 'belongsTo') {	    		
	    		global $wpdb;
	    		$query = 'SELECT ID, ' . $field['related_field'] . ' FROM ' . $wpdb->prefix . $field['related_model'];
	    		$records = $wpdb->get_results($query);
	    		foreach ($records as $record) {
	    			$ops .= '<option value="' . $record->ID . '"';
	    			if ($value == $record->ID) $ops .= ' selected';
	    			$ops .= '>' . $record->$field['related_field'] . '</option>';
	    		}
	    		echo '<select name="', $key, '" id="', $key, '">';
	    		echo '<option value="">', __('Select', 'wp-easy-data'), '</option>';
	    		echo $ops;
	    		echo '</select>';
	    	} elseif ($field['type'] == 'hasManyAndBelongsTo') {

	    		global $wpdb;
	    		
	    		$query = 'SELECT ID, ' . $field['related_field'] . ' FROM ' . $wpdb->prefix . $field['related_model'];
	    		$records = $wpdb->get_results($query);
	    		
	    		if ($value)
	    		     $selected = $wpdb->get_col("SELECT {$field['related_model']} FROM {$field['relational_table']} WHERE $this->name = $value");
	    		
	    		$check = array();
	    		if ($selected) {
		    		foreach ($selected as $id) {
		    			$check[$id] = true;
		    		}
	    		}
	    		
	    	    foreach ($records as $record) {
                    $ops .= '<input type="checkbox" name="' . $key . '[]" value="' . $record->ID . '"';
                    if ($check[$record->ID]) $ops .= ' checked';
                    $ops .= '>' . $record->$field['related_field'] . '<br />';
                }
                
                echo '<div class="hasMany" id="', $key, '" name="', $key, '">';
                echo $ops;
                echo '</div>';
                
	    	
	    	} else
			* 
			*/
			
            $output = str_replace('%class%', $class, $this->acceptedFields[$field['type']]['form']);
            $output = str_replace('%key%', $key, $output);
            
	    if ($field['type'] == 'select') { 
			    
                echo $output;
				
	    		$cur_sel[$value] = ' selected';
	    		foreach ($field['values'] as $n => $v) {
	    			echo '<option value="', $v, '"', $cur_sel[$v], '>', $n, '</option>';
	    		}
	    		echo '</select>';
                
            } elseif ($field['type'] == 'radio') { 
			    
                echo $output;
				
	    		$cur_sel[$value] = ' checked';
	    		
	    		foreach ($field['values'] as $n => $v) {
	    			$br = (isset($br) && $field['br']) ? '<br/>' : '';
	    			echo $br.'<label><input type="radio" name="'.$field['name'].'" style="width:auto" value="', $v, '"', $cur_sel[$v], '> ', $n, '</label>&nbsp;';
	    			
	    		}
                
            } elseif ($field['type'] == 'checkboxes') { 
			    
                echo $output;
				
                foreach ($field['values'] as $n => $v) {
	    			$checked = isset($value[$v]) ? 'checked="checked"':'';
	    			$br = (isset($br) && $field['br']) ? '<br/>' : '';
	    			echo $br.'<label><input type="checkbox" name="'.$field['name'].'['.$v.']" style="width:auto" '.$checked.'', $cur_sel[$v], '> ', $n, '</label>&nbsp;';
	    			
	    		}
                
            } elseif ($field['type'] == 'bool') {
                
                $checked = $value ? 'checked' : 'nao';
                $output = str_replace('%checked%', $checked, $output);
                echo $output;
                
            } else {
				
                if( $field['type'] == 'passfield')
					$value ='';
					
				$output = str_replace('%value%', $value, $output);
                echo $output;
                
	    	} 
	    }
	    
	    function print_bulk_actions() {
	
	    	echo '<div class="tablenav">';
		    	echo '<div class="alignleft actions">';
		    	    echo '<input type="submit" name="delete_selected" value="', __('Remove Selected', 'wp-easy-data'), '" class="button-secondary" style="margin-right: 8px;">';
			    	do_action('wp-easy-data-tablenav');
		    	echo '</div>';
		    	echo '<input type="hidden" name="search_url_pattern" value="' . add_query_arg(array('search' => '_s_', 'pagination_current' => 1)) . '">';
                echo '<input type="text" name="search" value="' . $_GET['search'] . '"><input type="button" value="' . __('Search') . '" class="button_search">';
                
		    	$paginationOptions = array(0 => '', 5 => '', 15 => '', 20 => '', 30 => '', 50 => '', 100 => '');
		    	$paginationOptions[$this->pagination_items_per_page] = 'selected';
		    	echo '<div class="tablenav-pages">';
		    	
		            _e('Items per page:', 'wp-easy-data');    
		    	    echo '<select class="pagination_select" name="paginatio_select">';
		    	        foreach ($paginationOptions as $key => $value) {
		                    $k = ($key == 0) ? __('All') : $key;
		    	        	$v = add_query_arg('pagination_items_per_page', $key);
		    	        	echo "<option value='$v' $value>$k</option>";
		    	        }
		    	    echo '</select>';
		    	
			    	if ($this->pagination_items_per_page > 0) {
			    		$d = $this->pagination_items / $this->pagination_items_per_page;
			            $pages = intval($d);
			            if ($d > $pages) $pages ++;
			            $page_links = paginate_links( array(
	                        'base' => add_query_arg( 'pagination_current', '%#%' ),
	                        'format' => '',
	                        'prev_text' => __('&laquo;'),
	                        'next_text' => __('&raquo;'),
	                        'total' => $pages,
	                        'current' => $this->pagination_current
	                    ));
			            $fim = ($this->pagination_items_per_page * $this->pagination_current);
	                    $inicio = $fim - ($this->pagination_items_per_page - 1);
	                    #$fim = ($fim > $this->pagination_items) ? '' : "- $fim";
	                    
	                    $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
						    number_format_i18n( $inicio ),
						    number_format_i18n( $fim ),
						    number_format_i18n( $this->pagination_items ),
						    $page_links
						); 
						echo $page_links_text;					
			    	}
			    	
		    	echo '</div>';
	    	echo '</div>';
	    }
	
	    
	    //************* Core Methods *****************//
	    
		function relation_table_name ($relation_name, $usePrefix = true){
			global $wpdb;
			$names = array($this->name, $relation_name);
			sort($names);
			
			return ($usePrefix ? $wpdb->prefix : '' ) . implode('_', $names);
		}
		
		function get_relation_table($relation_name, $usePrefix = true) {
			return $this->relation_table_name ($relation_name, $usePrefix);
		}

	    
	    function get_external_label_index($related_model_name) {
			return ( array_key_exists($related_model_name, $this->custom_external_label_index) ) ? $this->custom_external_label_index[$related_model_name] : $this->external_label_index;
		}
	    
        function build_search_clause($str_search) {
            $q_search = '';
            
            if ($str_search) {
                foreach ($this->fields as $field) {
                    if ($field['searchable'] !== false) {
                        $q_search .= "{$field['name']} LIKE '%" . addslashes($_GET['search']) . "%' OR ";
                    }
                }
                $q_search = preg_replace('/ OR $/', '', $q_search);
            }
            
            return $q_search;
        }
        
	    function get_number_of_items($where = null) {
	    	global $wpdb;
	    	if ($where) $where = "WHERE $where";
	        $r = $wpdb->get_var("SELECT COUNT(ID) FROM {$this->table} $where");
	        return $r;
	    }
	    
	    function get_items($where = null, $order = null, $level_limit = null) {
	        
			if ($level_limit) $this->items_recursive_limit = $level_limit;
			
			global $wpdb;
	        /*
	        foreach ($this->fields as $field) {
	        	if ($field['type'] != 'hasManyAndBelongsTo')
	               $select .= ($field['type'] == 'date') ? "date_format({$field['name']}, '%d/%m/%Y') as {$field['name']}, " : "{$field['name']}, ";
	        }       
	        $select = substr($select, 0, strlen($select) - 2);
	        */
	        if ($where) $where = ( strpos(strtolower($where), "where") === FALSE ? "WHERE " : "" ). $where;
	        if ($order) $order = "ORDER BY $order";
	        
	        if (!$order && $this->sortable)
	            $order = "ORDER BY item_order";
	            
	        if ($this->pagination_items_per_page > 0) {
	            $limit = "LIMIT {$this->pagination_items_per_page}";
	            $offset = ($this->pagination_items_per_page * $this->pagination_current) - ($this->pagination_items_per_page);
	            $offset = "OFFSET $offset";    	
	        }
	         
			$query = "SELECT {$this->table}.* FROM {$this->table} $where $order $limit $offset";
			 
			//DEBUG DEl
				#echo $query . "\n\n<br>";
				
	        $rows = $wpdb->get_results($query);
	        
			foreach($rows as &$row){
				
				$row = new Wp_easy_dataItem(&$this, (int) $row->ID, $row);
			}
	        
	        return $rows;
	    }
	    
	    function get_item($id = false) {
	        $obj = new Wp_easy_dataItem(&$this, $id);
	        return $obj;
	    }
	    
	    function add_filter($filter, $var) {
	       if ($this->is_admin) {
	            add_filter($filter, $var);
	        }
	    }
	    
	    function add_action($action, $callback, $args = false) {
	       if ($this->is_admin) {
	            add_action($action, $callback, $args);
	        }
	    }
	    
        /**
         * Check if the model has any field of the type
         * file and if this field has custom mime types allowed
         * (in the model use the key 'mimes'). If so add this mime types
         * to the Wordpress' list of allowed mime types for upload.
         * 
         * @param array $mimes the default list of allowed mime types
         * @return array $mimes the same list with the new values if any
         */
	    function customUploadMimeTypes($mimes) {
	        foreach ($this->fields as $field) {
	            if ($field['type'] == 'file' && isset($field['mimes']) && is_array($field['mimes'])) {
	                foreach ($field['mimes'] as $extensions => $mime) {
	                    $mimes[$extensions] = $mime;
	                }
	            }
	        }
	        
	        return $mimes;
	    }
	    
	    
	    //************* Sortable Methods *****************//
	    
	    function addSortableJS() {
	        wp_enqueue_script('jquery-ui-sortable');
	        wp_enqueue_script('wp-easy-data-sortable', H_CRUD_URLPATH . 'sortable/wp-easy-data-sortable.js', array('jquery-ui-sortable'));
	    }
	    
	    function addSortableCSS() {
	        wp_enqueue_style('wp-easy-data-admin-sortable', H_CRUD_URLPATH . 'sortable/wp-easy-data-sortable.css');
	    }
	    
	    function addSortableHeading() {
	        echo '<th style="width: 50px;">', __('Order', 'wp-easy-data'), '</th>';
	        echo "<input type='hidden' name='sortable_init_offset' id='sortable_init_offset' value='{$this->sortable_offset}'>";
	    }
	    
	    function addSortableForm($item) {
	        echo '<td class="drag"><img src="', H_CRUD_URLPATH, 'sortable/sortable.png" alt="', __('Drag & drop to order', 'wp-easy-data'), '" /><input type="text" size="1" name="item_order_', $item->ID, '" class="item_order" value="', $this->sortable_offset, '"></td>';
	        $this->sortable_offset ++;
	    }
	    
	    function print_sortable_tablenav() {
            if ($_GET['search'] != '') $disabled = 'disabled';
	    	echo '<input type="submit" name="save_order" value="', __('Save Order', 'wp-easy-data'), '" class="button-secondary" ', $disabled, '>';
	    }
	    
	    function sortable_save() {
		    if ($_POST['save_order']) {
			    
			    foreach ($_POST as $key => $value) {
			    	if (preg_match('/item_order_(\d+)/', $key, $res)) {
			    		$item = $this->get_item((int) $res[1]);
			    		$item->set_order($value);
			    	}
			    }
			}
	    }
	    
	}
	
	
	class Wp_easy_dataItem {
	    
	    var $ID;
	    var $info;
	    var $table;
	    
	    function Wp_easy_dataItem(&$crudObj, $id = false, $info = false ) {
	
	        global $wpdb, $recursive_limit;
	        $this->table = $crudObj->table;
	        $this->fields = $crudObj->fields;
	        $this->belongsTo = $crudObj->belongsTo;
	        //$this->hasManyAndBelongsTo = $crudObj->hasManyAndBelongsTo;
	        
	        
	        
	        if (is_int($id)) {
	        	
	            $this->ID = $id;
	            if ( $info === FALSE ) {
					// faz a query e popula as infos ($this->info)
					$this->info = $wpdb->get_row("SELECT * FROM $this->table WHERE ID = $this->ID");
					
				}else {
					$this->info = $info;	
				}
				
				foreach ($this->fields as $field)
					if($field['type'] == 'checkboxes')
						if(is_serialized($this->info->$field['name']))
							$this->info->$field['name'] = unserialize($this->info->$field['name']);
						else 
							$this->info->$field['name'] = array();
				
				foreach ($crudObj->belongsTo as $bt) {
					$obj = $bt->obj;
                    $related_object = $obj->get_items("{$obj->external_field}='" . $this->info->{$obj->external_name} . "'");
                    if (is_array($related_object))
					    $this->info->{$obj->name} = $related_object[0];			
				}
				
				
				//DEBUG
				#echo ( $crudObj->name . " hasManyAndBelongsTo.length "  . count($crudObj->hasManyAndBelongsTo));
				
				foreach ($crudObj->hasManyAndBelongsTo as $hmbt) {
					$obj = $hmbt->obj;
					
					//debug
						#var_dump($crudObj->name, $obj->name, !isset($recursive_limit->{$crudObj->relation_table_name($obj->name,false)}), $recursive_limit);
						#echo "--------------------------------------\n";
					
					if (!isset($recursive_limit->{$crudObj->relation_table_name($obj->name,false)."_".$this->ID}) ) {
						
						
						$recursive_limit->{$crudObj->relation_table_name($obj->name,false)} = 1;
						
						$rel_table = $crudObj->relation_table_name($obj->name);
						$where = "JOIN {$rel_table} ON {$obj->table}.ID = {$rel_table}.{$obj->external_name}  WHERE  {$rel_table}.{$crudObj->external_name} = {$this->ID }";
						//debug del
							//echo $where ;
						$this->info->{$obj->name}  = $obj->get_items($where);
					}
					
					
				}
				
	            
	            $this->_dateFromDb();
	        }
	    }
	
	    function save() {
	        
	        // verifica se $this->info,estão todos preenchidos e salva no banco
	        // se $this->ID for null gera um novo e retorna o id criado no banco
	        // se der algum problema retorna false
	        
	        $this->uploadFiles();
	        
	        $this->_dateToDb();
	        
	        if (!isset($this->ID)) {
                
                foreach ($this->fields as $dbField) {
                    if ($dbField['type'] != 'hasManyAndBelongsTo') {
	                	$fields .= $dbField['name'] . ', ';
	                    $values .= "'" . $this->info->$dbField['name'] . "', ";
                    }
                }
                
                foreach($this->belongsTo as $bt) {
					$fields .= $bt->obj->external_name . ', ';
	                $values .= "'" . $this->info->{$bt->obj->external_name} . "', ";
				}
                
                $fields = substr($fields, 0, strlen($fields) - 2);
                $values = substr($values, 0, strlen($values) - 2);
                
                mysql_query("INSERT INTO {$this->table} ($fields) VALUES ($values)");
                $this->ID = $this->info->ID = mysql_insert_id();

                // set item_order to its ID so it goes to the end of the line (if present)
                @mysql_query("UPDATE {$this->table} SET item_order = {$this->ID} WHERE ID = {$this->ID}");

                return $this->ID;
                
            } else {
                foreach ($this->fields as $dbField) {
                	if ($dbField['type'] != 'hasManyAndBelongsTo') 
                	   $query .= $dbField['name'] . " = '" . $this->info->$dbField['name'] . "', ";
                }
                
                foreach($this->belongsTo as $bt) {
					$query .= $bt->obj->external_name . " = '" . $this->info->{$bt->obj->external_name} . "', ";
				}
                
                $query = substr($query, 0, strlen($query) - 2);
                $query .= " WHERE ID = {$this->info->ID}";
                mysql_query("UPDATE {$this->table} SET $query");
				
				
				
                return true;
            }
	        return false;
	    }
	    
	    function uploadFiles() {
	        if (is_array($_FILES)) {
	            foreach ($_FILES as $key => $file) {
	                
                    // lets only handle uploads of files for fields of this item (sometimes we may be creating an item in some other form with uploaded files that has nothing to do with this item)
                    if ( ! $this->checkFileField($key) )
                        continue;
                    
	                if (!empty($file['name'])) {                       
	                    if ($this->ID && !empty($this->info->$key)) {
	                        wp_delete_post($this->info->$key);
	                    }
	                    
	                    $file_id = media_handle_upload($key, '');
	                    
	                    if (is_object($file_id) && get_class($file_id) == 'WP_Error') {
	                        //TODO: melhorar o tratamento desse erro
	                        echo '<br />' . $file_id->errors['upload_error'][0]; die;
	                    } else {
	                        $this->info->$key = $file_id;
	                    }
	                }
	            }
	        }
	    }
        
        function checkFileField($fieldName) {
            foreach ($this->fields as $field) {
                if ($field['name'] == $fieldName && $field['type'] == 'file')
                    return true;
            }
            return false;
        }
	    
	    function delete() {
	        // delete item files if any
            $this->_maybeDeleteFiles();

	        do_action_ref_array('wp-easy-data-delete-item', array($this));
	        mysql_query("DELETE FROM {$this->table} WHERE ID = {$this->ID}");
	        return true;
	    }
	    
	    /**
	     * Check if the item has one or more fields of the
	     * type file and remove them. Called by the delete() function.
	     * 
	     * @return void
	     */
	    function _maybeDeleteFiles() {
	        foreach ($this->fields as $field) {
	            if ($field['type'] == 'file') {
	                $fileId = $this->info->{$field['name']};
	                wp_delete_attachment($fileId);
	            }
	        }
	    }
	    
	    function delete_file($file) {
	        wp_delete_attachment($this->info->$file);
	        $this->info->$file = '';
	        $this->save(false);
	    }
	    
	    function _dateFromDb() {
	        if (!empty($this->info)) {
	            $pattern = "/(\d{4})\-(\d\d)-(\d\d)\ (\d\d)\:(\d\d)\:(\d\d)/";
	            foreach ($this->fields as $field) {
	                if ($field['type'] == 'date')
	                   $this->info->$field['name'] = preg_replace($pattern, '$3/$2/$1', $this->info->$field['name']);
	            }
	
	        }
	    }
	    
	    function _dateToDb() {
	        foreach ($this->fields as $field) {
	            if ($field['type'] == 'date') {
	                $date = explode('/', $this->info->$field['name']);
	                $this->info->$field['name'] = $date[2] . '/' . $date[1] . '/' . $date[0];
	            }
	        }
	    }
	    
	    /*************** Sortable Methods ***********************/
	    
	    function set_order($newOrder) {
	    	
	    	global $wpdb;
	    	$oldOrder = $this->info->item_order; 
	    	mysql_query('UPDATE ' . $this->table . ' SET item_order = item_order - 1 WHERE item_order > ' . $oldOrder);
	    	mysql_query('UPDATE ' . $this->table . ' SET item_order = item_order + 1 WHERE item_order >= ' .  $newOrder);
	    	mysql_query('UPDATE ' . $this->table . ' SET item_order = ' . $newOrder . ' WHERE ID = ' . $this->ID);
	    	
	    }
	
	}
}

add_action('init', 'Wp_easy_data_init', 1);

?>
