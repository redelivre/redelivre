<?php

// These tests use simpletest unit-tes suit. Please install it on your computer in order to run the tests

    if (! defined('SIMPLE_TEST')) {
        define('SIMPLE_TEST', '/usr/share/php/simpletest/');
    }
    require_once(SIMPLE_TEST . 'unit_tester.php');
    require_once(SIMPLE_TEST . 'reporter.php');
    require_once('../../../../wp-config.php');
    require_once('../../../../wp-admin/includes/media.php');
    require_once('../../../../wp-admin/includes/file.php');
    
    #require_once('../wp-easy-data-admin.php');

    class testWp_easy_data extends UnitTestCase {
        function testWp_easy_data() {
        	global $wpdb;
        	$this->table = 'testCRUDtable';
            $this->UnitTestCase('CRUD Test');
            $this->fields = array(
	            array(
	                'name' => 'name',
	                'display_name' => 'Nome',
	                'type' => 'textfield',
	                'list_display' => true,
	                'description' => 'aqui vai o nome do cara'
	            ),
	            array(
	                'name' => 'description',
	                'display_name' => 'Descrição',
	                'type' => 'textarea',
	                'list_display' => true,
	                'description' => 'aqui vai a descrição'
	            ),
	            array(
	                'name' => 'image',
	                'display_name' => 'Imagem',
	                'type' => 'file',
	                'list_display' => true,
	                'description' => 'fotinha'
	            ),
	            array(
	                'name' => 'date',
	                'display_name' => 'Data',
	                'type' => 'date',
	                'list_display' => true,
	                'description' => 'dd/mm/aaaa'
	            )
            );
        }
        
        function setUp() {
	        //create and populate table
	        $create = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}{$this->table} ( 
	           `ID` int(11) NOT NULL auto_increment, 
	           `name` varchar(255), 
	           `description` varchar(255), 
	           `image` int(11), 
	           `date` datetime, 
	           PRIMARY KEY (`ID`)) DEFAULT CHARSET=utf8";
	        
	        $populate = "INSERT INTO {$this->table} (name, description, image, date) 
	           VALUES ('nome', 'descrição', 0, '1979-10-14')";
	        
	        mysql_query($create);
	        mysql_query($populate);
	        
            $model = array(
               'fields' => $this->fields,
               'tableName' => 'testCRUDtable',
               'adminName' => 'Teste'
            );

            $this->crud = new Wp_easy_data($model, __FILE__);
	    }
	    
	    function tearDown() {
	        //remove table
	        $delete = "DROP TABLE {$this->table}";
	        #wp_delete_attachment($this->fileID);
	        
	        mysql_query($delete);
	        
	    }
        
        
        function testCreateNewCRUD() {
            global $wpdb;

        	$model = array(
               'fields' => $this->fields,
               'tableName' => 'testCRUDtable2',
               'adminName' => 'Teste'
            );
            $crud = new Wp_easy_data($model, __FILE__);
            
            $q = mysql_query("SELECT * FROM $this->table");

            $cc = 0;
            foreach ($model['fields'] as $field) {
            	$b = mysql_fetch_field($q, $cc);
            	if ($b->name == 'name') {
                    $this->assertEqual($b->type, 'string', '');            		
            	} elseif ($b->name == 'image') {
                    $this->assertEqual($b->type, 'int', '');
            	}
            	$cc ++;
            }
            $this->assertEqual($cc, 4, 'CRUD Created!');

        }
        
        function testGetItems() {
        	$items = $this->crud->get_items();
        	$row = $items[0];
        	$this->assertEqual($row->name, 'nome', 'get_items() ok!');
        }
        
        function testAddNewItemWithFile() {
            
        }
        
        function testAddNewItemWithoutFile() {
            $item = new Wp_easy_dataItem($this->fields, $this->table);
            $item->info->name = 'teste_nome';
            $item->info->description = 'teste_descri';
            $item->info->date = '22/01/1901';
            $id = $item->save();
            
            $query = mysql_query("SELECT ID, name, description, date_format(date, '%d/%m/%Y') as date FROM {$this->table} WHERE ID = $id");
            $row = mysql_fetch_array($query);
           
            $this->assertEqual('22/01/1901', $row['date']);
            $this->assertEqual('teste_nome', $row['name']);
            $this->assertEqual('teste_descri', $row['description'], 'Inserindo item ok!');
            
        }
        
        function testEditItemWithFile() {
            
        }
        
        function testEditItemWithoutFile() {
            $item = new Wp_easy_dataItem($this->fields, $this->table, 1);
            $item->info->name = 'teste_nome';
            $item->info->description = 'teste_descri';
            $item->info->date = '22/01/1901';
            $item->save();
            
            $query = mysql_query("SELECT ID, name, description, date_format(date, '%d/%m/%Y') as date FROM {$this->table} WHERE ID = 1");
            $row = mysql_fetch_array($query);
           
            $this->assertEqual('22/01/1901', $row['date']);
            $this->assertEqual('teste_nome', $row['name']);
            $this->assertEqual('teste_descri', $row['description'], 'Inserindo item ok!');
        }
        
        function testRemoveFile() {
            
        }
        
        function testDeleteItem() {
            $item = new Wp_easy_dataItem($this->fields, $this->table, 1);
            $item->delete();
            
            $query = mysql_query("SELECT ID, name, description, date_format(date, '%d/%m/%Y') as date FROM {$this->table} WHERE ID = 1");
            $row = mysql_fetch_array($query);
            
            $this->assertTrue(empty($row), 'item removido');
        }
        
    }
    
    $test = &new testWp_easy_data();
    $test->run(new HtmlReporter());
?>
