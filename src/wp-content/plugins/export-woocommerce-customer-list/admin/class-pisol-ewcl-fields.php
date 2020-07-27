<?php

class class_fields{

    public $plugin_name;

    private $settings = array();

    private $active_tab;

    private $this_tab = 'fields';

    private $tab_name = "Fields in CSV";

    private $setting_key = 'pi_ewcl_fields_setting';
    

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->settings = array(
            
            array('field'=>'pi_customer_row')
            
        );
        
        $this->tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';
        $this->sub_menu = (isset($_GET['sub_menu'])) ? sanitize_text_field($_GET['sub_menu']) : 'sub_menu_default';
        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
            add_action($this->plugin_name.'_tab_sub_menu', array($this,'tabSubMenu'));
        }


        add_action($this->plugin_name.'_tab', array($this,'tab'),2);

       
        $this->register_settings();

       

    }

    function tabSubMenu(){
        ?>
        <div class="d-flex">
            
                <a href="<?php echo admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ); ?>&sub_menu=sub_menu_default" class="col-6 py-2 align-items-center justify-content-center px-3 text-light d-flex border-left border-right border-top <?php echo ($this->sub_menu == 'sub_menu_default' ? 'bg-primary' : 'bg-secondary'); ?>"><?php _e('Select fields to add in Registered Customer CSV','pisol-ewcl'); ?></a>
           
            
                <a href="<?php echo admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab ); ?>&sub_menu=sub_menu_add_customer_extra_field" class="col-6 py-2 text-center px-3 text-light d-flex align-items-center justify-content-center  border-left border-right border-top <?php echo ($this->sub_menu == 'sub_menu_add_customer_extra_field' ? 'bg-primary' : 'bg-secondary'); ?>"><?php _e('Add Extra field, other then given by us','pisol-ewcl'); ?></a>
           
        </div>
        <?php
    }

    
    function delete_settings(){
        foreach($this->settings as $setting){
            delete_option( $setting['field'] );
        }
    }

    function register_settings(){   

        foreach($this->settings as $setting){
            register_setting( $this->setting_key, $setting['field']);
        }
    
    }

    function tab(){
        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
        ?>
        <a class=" px-3 text-light d-flex align-items-center  border-left border-right  <?php echo ($this->active_tab == $this->this_tab ? 'bg-primary' : 'bg-secondary'); ?>" href="<?php echo admin_url( 'admin.php?page='.$page.'&tab='.$this->this_tab ); ?>">
            <?php _e( $this->tab_name); ?> 
        </a>
        <?php
    }

    function tab_content(){
        if($this->sub_menu == 'sub_menu_default'){
            $this->tabContentCsvFields();
        }elseif($this->sub_menu == 'sub_menu_add_customer_extra_field'){
            $this->tabContentAddExtraFields();
        }

       
    }

    function tabContentCsvFields(){
        $customer_rows = get_option('pi_customer_row',array());
        ?>
        <form action="options.php" method="POST">
        <?php //print_r($customer_rows); ?>
        <?php settings_fields( $this->setting_key ); ?>
         <h2>You can select the field that you want in CSV, and the heading given to that field in exported document</h2>
             <div>
                 <?php $this->generateFieldsTable($customer_rows); ?>
             </div>
             
             <input type="submit" class="btn btn-primary mt-3" value="Save Fields">
         </form>
        <?php
    }

    function tabContentAddExtraFields(){
       ?>
       <div id="row_title" class="row py-4 border-bottom align-items-center bg-primary text-light mb-3">
                <div class="col-12">
                <h2 class="mt-0 mb-0 text-light font-weight-light h4"><?php _e('Add extra user meta field that you want to add to download row but are not present in our provided list, so you can add them here','pisol-ewcl'); ?></h2>
                </div>
            </div>
       <div class="row">
            
            <div class="col-12 col-md-12">
            <div class="alert alert-info text-center">
            <?php _e('This is only Available only in PRO Version','pisol-ewcl'); ?>
            </div>
            <div class="border p-2">
                <b>Step 1:</b> Get the name of the user meta field that you want to add in csv (and make sure that field is not present in our given list)<br>
                <b>Step 2:</b> Add that name on the left side form<br>
                <b>Step 3:</b> Go to the "select field to add in CSV"<br>
                <b>Step 4:</b> Your added field will appear in the list, select it and save it <br>
                <br>
                In case you want to remove that field from CSV you can unselect it, or if you want to completely remove it then remove it from this form where you addd it
            </div>
            <div class="row mt-3">
                <div class="col-12 text-left">
                    <a href="javascript:void(0);" class="btn btn-primary btn-sm " id="pi-ewcl-add-custom-meta">Add Field</a>
                </div>
            </div>
            <div id="pi-ewcl-field-container">

            </div>
            <button href="javascript:void(0);" class="mt-3 btn btn-primary btn-sm " id="pi-ewcl-add-custom-meta" disabled>Save Fields</button>
            </div>
       </div>
       <?php
    }

    function generateFieldsTable($customer_rows){
        $fields = class_fields::fields();
        ?>
        <table id="datagrid" class="table">
            <tbody>
                <tr>
                <th style="text-align: left;">
                    <label for="v_columns">Tick the columns you want</label>
                </th>
                <th style="text-align: left;">
                    <label for="v_columns_name">Column name</label>
                </th>
                </tr>
                <?php
                foreach($fields as $key => $value){
                echo '    
                    <tr>
                    <td>
                        <input name="pi_customer_row['.$key.'][enabled]" id="pi_customer_row['.$key.'][enabled]" type="checkbox" '.(isset($customer_rows[$key]['enabled']) || count($customer_rows) == 0  ? 'checked="checked"' : '').'>
                        <label for="pi_customer_row['.$key.'][enabled]">'.$key.'</label>
                    </td>
                    <td class="free-version">
                         <input type="text" name="pi_customer_row['.$key.'][label]" value="'.$value.'" class="form-control">
                    </td>
                    </tr>
                    ';
                }
                ?>
            </tbody>
        </table>
        <?php
    }
    
    static function fields(){
        $fields = array(
            'ID'=> 'ID',
            'user_login'=>'user_login',
            'user_pass'=>'user_pass',
            'user_nickname'=>'user_nickname',
            'user_email'=>'user_email',
            'user_url'=>'user_url',
            'user_registered'=>'user_registered',
            'display_name'=>'display_name',
            'first_name'=>'first_name',
            'last_name'=>'last_name',
            'user_status'=>'user_status',
            'roles'=>'roles',
            'billing_first_name'=>'billing_first_name',
            'billing_last_name'=>'billing_last_name',
            'billing_company'=>'billing_company',
            'billing_address_1'=>'billing_address_1',
            'billing_address_2'=>'billing_address_2',
            'billing_city'=>'billing_city',
            'billing_postcode'=>'billing_postcode',
            'billing_country'=>'billing_country',
            'billing_state'=>'billing_state',
            'billing_email'=>'billing_email',
            'billing_phone'=>'billing_phone',
            'shipping_first_name'=>'shipping_first_name',
            'shipping_last_name'=>'shipping_last_name',
            'shipping_company'=>'shipping_company',
            'shipping_address_1'=>'shipping_address_1',
            'shipping_address_2'=>'shipping_address_2',
            'shipping_city'=>'shipping_city',
            'shipping_postcode'=>'shipping_postcode',
            'shipping_country'=>'shipping_country',
            'shipping_state'=>'shipping_state',
        );
        return $fields;
    }

    static function selectedFields($saved_fields){
        $fields = self::fields();
        $formated_saved_fields = array();
        if(count($saved_fields) > 0 ){
            foreach($fields as $key => $value){
                if(isset($saved_fields[$key]['enabled'])){
                    $formated_saved_fields[$key] = $value;
                }elseif($key == 'ID'){
                    $formated_saved_fields[$key] = $value;
                }
            }
        }else{
            return $fields;
        }
        return $formated_saved_fields;
    }
}

new class_fields($this->plugin_name);