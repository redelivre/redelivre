<?php

class Class_Pi_Ewcl_Option{

    public $plugin_name;

    private $settings = array();

    private $active_tab;

    private $this_tab = 'default';

    private $tab_name = "Export WooCommerce Customer";

    private $setting_key = 'pi_ewcl_basic_setting';
    

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->settings = array(
            
            array('field'=>'pi_customer_row')
            
        );
        
        $this->tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }


        add_action($this->plugin_name.'_tab', array($this,'tab'),1);

       
        $this->register_settings();

        if(PI_EWCL_DELETE_SETTING){
            $this->delete_settings();
        }

        if(isset($_GET['pi_action']) && $_GET['pi_action'] == 'download_customer_list'){
            $this->downloadCustomerList();
        }
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

    function get_roles() {
        global $wp_roles;
    
        $all_roles = $wp_roles->roles;
    
        return  $all_roles;
    }

    function tab_content(){
        $customer_rows = get_option('pi_customer_row',array());
        $roles = $this->get_roles();
       ?>
       <form action="<?php echo admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&pi_action=download_customer_list'); ?>" method="POST">
       <div id="row_title" class="row py-4 border-bottom align-items-center bg-primary text-light">
            <div class="col-12">
            <h2 class="mt-0 mb-0 text-light font-weight-light h4"><?php _e('Advance download option', 'pisol-ewcl') ?></h2>
            </div>
        </div>
        <div id="row_pi_ewcl_download_limit" class="row py-4 border-bottom align-items-center ">
            <div class="col-12 col-md-5">
            <label class="h6 mb-0" for="pi_ewcl_download_limit"><?php _e('Number of rows to extract', 'pisol-ewcl') ?></label>            <br><small><?php _e('Specify number of rows to extract, 0 means all rows will be extracted', 'pisol-ewcl') ?></small>            </div>
            <div class="col-12 col-md-7">
            <input type="number" class="form-control " name="pi_ewcl_download_limit" id="pi_ewcl_download_limit" value="0" min="0" step="1">            </div>
        </div>
        <div id="row_pi_ewcl_download_offset" class="row py-4 border-bottom align-items-center ">
            <div class="col-12 col-md-5">
            <label class="h6 mb-0" for="pi_ewcl_download_offset"><?php _e('Number of rows to skip', 'pisol-ewcl') ?></label>            <br><small><?php _e('Specify number of rows to skip from top', 'pisol-ewcl') ?></small>            </div>
            <div class="col-12 col-md-7">
            <input type="number" class="form-control " name="pi_ewcl_download_offset" id="pi_ewcl_download_offset" value="0" min="0" step="1">            </div>
        </div>
        <div id="row_pi_ewcl_download_offset" class="row py-4 border-bottom align-items-center free-version">
            <div class="col-12 col-md-5">
            <label class="h6 mb-0" for="pi_ewcl_download_offset"><?php _e('Delimiters', 'pisol-ewcl') ?></label>            <br><small><?php _e('How value are separated in csv', 'pisol-ewcl') ?></small>            </div>
            <div class="col-12 col-md-7">
                <select class="form-control" name="pi_ewcl_delimiter" id="pi_ewcl_delimiter">
                    <option value=",">,</option>
                    <option value=";">;</option>
                </select>           
            </div>
        </div>
        <div id="row_pi_ewcl_user_role" class="row py-4 border-bottom align-items-center free-version">
            <div class="col-12 col-md-5">
            <label class="h6 mb-0" for="pi_ewcl_user_role"><?php _e('Select the user role to download (If you leave this blank it will download WooCommerce Customer)', 'pisol-ewcl') ?></label>            <br><small><?php _e('If you want to download user with different role then select it from dropdown, To download WooCommerce customer either select "Customer" or leave the selection empty', 'pisol-ewcl') ?></small>            </div>
            <div class="col-12 col-md-7">
                <select class="form-control" name="pi_ewcl_user_role[]" id="pi_ewcl_user_role" multiple>
                <?php foreach($roles as $key => $role){ ?>
                    <option value='<?php echo $key; ?>' 
                    <?php if($key == 'customer' || $key == 'administrator'){ echo 'selected="selected"'; } ?>
                    >
                    <?php echo $role['name']; ?></option>
                <?php } ?>
                </select>           
            </div>
        </div>
        <div id="row_pi_ewcl_download_offset" class="row py-4 border-bottom align-items-center free-version">
            <div class="col-12 col-md-5">
            <label class="h6 mb-0" for="pi_ewcl_download_offset"><?php _e('Registration done between', 'pisol-ewcl') ?></label>            <br><small><?php _e('Extract users whose registration was done between this date ranges', 'pisol-ewcl') ?></small>            </div>
            <div class="col-12 col-md-3">
            <input type="text" readonly class="form-control datepicker" name="pi_ewcl_download_after_date" id="pi_ewcl_download_after_date" placeholder="After date">
            </div>
            <div class="col-12 col-md-1 text-center">
            <label>&</label>
            </div>
            <div class="col-12 col-md-3">
            <input type="text" readonly class="form-control datepicker" name="pi_ewcl_download_before_date" id="pi_ewcl_download_before_date" placeholder="Before date">
            </div>
        </div>
       <div class="text-center pt-5">
        <input type="submit" class="btn btn-primary btn-lg my-2" value="Download Customer List">
        </div>
       </form>
       
       <?php
    }

    function downloadCustomerList(){
        add_action('init',array($this, 'wpLoaded'));
    }

    function wpLoaded(){
        $saved_fields = get_option('pi_customer_row',array()) ;
        $saved_fields = is_array($saved_fields) ? $saved_fields : array();

        $limit = (int)(isset($_POST['pi_ewcl_download_limit']) && $_POST['pi_ewcl_download_limit'] >= 0) ? sanitize_text_field($_POST['pi_ewcl_download_limit']) : 0;

        $offset = (int)(isset($_POST['pi_ewcl_download_offset']) && $_POST['pi_ewcl_download_offset'] >= 0) ? sanitize_text_field($_POST['pi_ewcl_download_offset']) : 0;

        $delimiter = ",";

        $after = "";
        $before = "";

        $fields = class_fields::selectedFields($saved_fields);
        $data_obj = new class_customer_data_extractor($fields, $limit, $offset, $after, $before);
        $rows = $data_obj->getRows();
        $header = $data_obj->getHeader();
        $filename_initial = "customer_initial";
        
        $csv_file_obj = new class_pisol_ewcl_csv_maker($header, $rows, $delimiter, $filename_initial);
        $csv_file_obj->download();
    }

    
    
}

new Class_Pi_Ewcl_Option($this->plugin_name);