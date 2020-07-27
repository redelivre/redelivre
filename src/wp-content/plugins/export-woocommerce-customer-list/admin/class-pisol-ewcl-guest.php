<?php

class Class_Pi_Ewcl_Guest{

    public $plugin_name;

    private $settings = array();

    private $active_tab;

    private $this_tab = 'guest';

    private $tab_name = "Export Guest Customer";

    private $setting_key = 'pi_ewcl_guest_setting';
    

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->settings = array(
            
            array('field'=>'pi_guest_row')
            
        );
        
        $this->tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
        $this->active_tab = $this->tab != "" ? $this->tab : 'default';

        if($this->this_tab == $this->active_tab){
            add_action($this->plugin_name.'_tab_content', array($this,'tab_content'));
        }


        add_action($this->plugin_name.'_tab', array($this,'tab'),3);

       
        $this->register_settings();

        if(PI_EWCL_DELETE_SETTING){
            $this->delete_settings();
        }

        if(isset($_GET['pi_action']) && $_GET['pi_action'] == 'download_guest_list'){
            $this->downloadGuestList();
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

    function tab_content(){
        $customer_rows = get_option('pi_guest_row',array());
       ?>
       <form action="<?php echo admin_url( 'admin.php?page='.sanitize_text_field($_GET['page']).'&tab='.$this->this_tab.'&pi_action=download_guest_list'); ?>" method="POST">

       <div id="row_title" class="row py-4 border-bottom align-items-center bg-primary text-light">
            <div class="col-12">
            <h2 class="mt-0 mb-0 text-light font-weight-light h4">Download Guest Customer</h2>
            </div>
        </div>
        <div id="row_title" class="row py-4 border-bottom">
            <div class="col-12">
            <div class="alert alert-primary">
                <h2>In Free version this will only download 30 Guest customer data at a time</h2>
            </div>
            <div class="alert alert-primary">
                Guest customer list will not be affected by the field saved in the "field in CSV" tab, "Field in CSV" tab only works on the CSV of registered customer
            </div>
            </div>
        </div>
        <div id="row_pi_ewcl_delimiter" class="row py-4 border-bottom align-items-center ">
            <div class="col-12 col-md-5">
            <label class="h6 mb-0" for="pi_ewcl_delimiter">Delimiters</label>            <br><small>How value are separated in csv</small>            </div>
            <div class="col-12 col-md-7">
                <select class="form-control" name="pi_ewcl_delimiter" id="pi_ewcl_delimiter">
                    <option value=",">,</option>
                    <option value=";">;</option>
                </select>           
            </div>
        </div>
        <div id="row_pi_ewcl_order_status " class="row py-4 border-bottom align-items-center free-version">
            <div class="col-12 col-md-5">
            <label class="h6 mb-0" for="pi_ewcl_order_status">Guest customer based on order status</label>            <br><small>Download guest customer based on the order status, use control to select multiple status, if left empty all guest will be selected</small>            </div>
            <div class="col-12 col-md-7">
                <select class="form-control" name="pi_ewcl_order_status" id="pi_ewcl_order_status" multiple>
                    <option value='pending' selected>Pending</option>
                    <option value="processing">Processing</option>
                    <option value="on-hold">On-Hold</option>
                    <option value="completed" selected>Completed</option>
                    <option value="refunded">Refunded</option>
                    <option value="failed">Failed</option>
                    <option value="cancelled">Cancelled</option>
                </select>           
            </div>
        </div>
        <div id="row_pi_ewcl_download_offset" class="row py-4 border-bottom align-items-center ">
            <div class="col-12 col-md-5">
            <label class="h6 mb-0" for="pi_ewcl_download_offset">Gust checkout done between</label>            <br><small>select the date range, If you leave this date empty it will download all the guest users in your website</small>            </div>
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
        <input type="submit" class="btn btn-primary btn-lg my-2" value="Download Guest Customer list">
        </div>
       </form>
       
       <?php
    }

    function downloadGuestList(){
        add_action('init',array($this, 'wpLoaded'));
    }

    function wpLoaded(){

        $delimiter = isset($_POST['pi_ewcl_delimiter']) ? $_POST['pi_ewcl_delimiter'] : ",";

        $after = isset($_POST['pi_ewcl_download_after_date']) ? sanitize_text_field($_POST['pi_ewcl_download_after_date']): "";

        $before = isset($_POST['pi_ewcl_download_before_date']) ? sanitize_text_field($_POST['pi_ewcl_download_before_date']) : "";

        $data_obj = new class_guest_data_extractor($after, $before);

        $rows = $data_obj->getRows();
        $header = $data_obj->getHeader();
        
        $filename_initial = "customer_initial";
        
        $csv_file_obj = new class_pisol_ewcl_csv_maker($header, $rows, $delimiter, $filename_initial);
        $csv_file_obj->download();
        
    }

    
    
}

new Class_Pi_Ewcl_Guest($this->plugin_name);