<?php

class Class_Pi_Ewcl_Email{

    public $plugin_name;

    private $settings = array();

    private $active_tab;

    private $this_tab = 'email';

    private $tab_name = "Get scheduled email with CSV (attachment)";

    private $setting_key = 'pi_ewcl_email_setting';
    

    function __construct($plugin_name){
        $this->plugin_name = $plugin_name;

        $this->settings = array(
            
            array('field'=>'title', 'class'=> 'bg-primary text-light', 'class_title'=>'text-light font-weight-light h4', 'label'=>__('Automatically send customer list csv','pisol-ewcl'), 'type'=>"setting_category"),

            array('field'=>'pi_ewcl_enable_email', 'label'=>__('Send customer list in email','pisol-ewcl'),'type'=>'switch', 'default'=> 0,   'desc'=>__('You can schedule when to receive the emails','pisol-ewcl')),
            array('field'=>'pi_ewcl_email', 'label'=>__('Email id','pisol-ewcl'),'type'=>'text',   'desc'=>__('Email id that will receive the csv attachment email, you can add more then one email id separated with comma ,','pisol-ewcl')),

            array('field'=>'pi_ewcl_email_subject', 'label'=>__('Subject of the email','pisol-ewcl'),'type'=>'text',   'desc'=>__('subject of the email','pisol-ewcl'), 'pro'=>true),

            array('field'=>'pi_ewcl_email_message', 'label'=>__('Message of the email','pisol-ewcl'),'type'=>'text',   'desc'=>__('Message of the email','pisol-ewcl'), 'pro'=>true),

            array('field'=>'pi_ewcl_email_frequency', 'label'=>__('Frequency of email','pisol-ewcl'),'type'=>'select',   'desc'=>__('Email should be send daily, weekly or twice daily','pisol-ewcl'), 'value'=>array('hourly'=>__('Hourly','pisol-ewcl'), 'daily'=>__('Daily','pisol-ewcl'),'twicedaily'=>__('Twice Daily','pisol-ewcl'), 'weekly'=>__('Weekly','pisol-ewcl')), 'default'=>'twicedaily', 'pro'=>true),

            array('field'=>'pi_ewcl_include_report', 'label'=>__('Include Customer','pisol-ewcl'),'type'=>'select',   'desc'=>__('Include Registered customer csv, Guest customer csv or both the customer csv in report','pisol-ewcl'), 'value'=>array('registered'=>__('Send only registered customer detail','pisol-ewcl'), 'guest'=>__('Send only gust customer detail','pisol-ewcl'), 'both'=>__('Send registered and guest customer detail','pisol-ewcl')), 'default'=>'registered', 'pro'=>true),
            
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

        $this->pi_ewcl_enable_email = get_option('pi_ewcl_enable_email',0);
        $this->email = get_option('pi_ewcl_email');
        $this->subject = get_option('pi_ewcl_email_subject');
        $this->message = get_option('pi_ewcl_email_message');
        $this->frequency = 'twicedaily';

        add_filter( 'cron_schedules', array($this, 'cron_add_weekly') );

        $this->cron_event = 'pi_ewcl_customer_email';

        if($this->pi_ewcl_enable_email == 1 && $this->email != ""){
            add_action( $this->cron_event , array($this, 'sendEmail') );
            if ( ! wp_next_scheduled( $this->cron_event ) ) {
                wp_schedule_event( time(), $this->frequency, $this->cron_event );
            }
            $set_frequency = wp_get_schedule($this->cron_event);
            if($set_frequency != $this->frequency){
                wp_clear_scheduled_hook($this->cron_event);
                wp_schedule_event( time(), $this->frequency, $this->cron_event );
            }
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
        ?>
        <form method="post" action="options.php"  class="pisol-setting-form">
        <?php settings_fields( $this->setting_key ); ?>
        
        <?php
            foreach($this->settings as $setting){
                new pisol_class_form_ewcl($setting, $this->setting_key);
            }
        ?>
        <div class="alert alert-danger mt-2">
        FREE Version will send customer list in email Twice Daily, In PRO version you can change the FREQUENCY to <strong>Daily, Weekly, Hourly</strong>
            <br><br>
        FREE Version will send you complete list of customers in your site, where as the <strong>PRO version will send you the user registered during that particular time period</strong> selected by you (Daily, Weekly)
        </div>
        <input type="submit" class="mt-3 btn btn-primary btn-sm" value="Save Option" />
        </form>
       <?php
    }

    function save(){
        $saved_fields = get_option('pi_customer_row',array()) ;
        $saved_fields = is_array($saved_fields) ? $saved_fields : array();

        $fields = class_fields::selectedFields($saved_fields);
        
        $before = "";
        $after = "";

        $data_obj = new class_customer_data_extractor($fields, 0, 0,  $after, $before);
        $rows = $data_obj->getRows();
        $header = $data_obj->getHeader();
        $delimiter = ",";
        $filename_initial = "customer_initial";
        
        $csv_file_obj = new class_pisol_ewcl_csv_maker($header, $rows, $delimiter, $filename_initial);

        $file = $csv_file_obj->save();
        return $file;
    }    

    function sendEmail(){
        $file = $this->save();
        $email = $this->email;
        $subject = "Customer list CSV"; 
        $message = file_get_contents(plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/email_template.html'); 

       $email_obj = new class_pisol_ewcl_email($email, $subject, $message, $file);
       $email_obj->send();
    }

    function before(){  
        $next_run = wp_next_scheduled($this->cron_event);
        
        if($next_run){
            $next_run = get_date_from_gmt( date( 'Y-m-d H:i:s', $next_run ), 'Y-m-d H:i:s' );
            return ($next_run);
        }
        return "";
    }

    function after(){  
        $next_run = wp_next_scheduled($this->cron_event);
        $frequency = get_option('pi_ewcl_email_frequency','daily');
        $value = '-1 day';
        if($next_run){
            if($frequency == 'daily'){
                $value = '-1 day';
            }

            if($frequency == 'weekly'){
                $value = '-7 day';
            }

            if($frequency == 'hourly'){
                $value = '-1 hours';
            }
            $next_run = get_date_from_gmt( date( 'Y-m-d H:i:s', $next_run ), 'Y-m-d H:i:s' );
            return date('Y-m-d H:i:s', strtotime($value, strtotime($next_run)));
        }
        return "";
    }
    
    function cron_add_weekly( $schedules ) {
        // Adds once weekly to the existing schedules.
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => __( 'Once Weekly' )
        );
        return $schedules;
     }
    
}

new Class_Pi_Ewcl_Email($this->plugin_name);