<?php

class Pi_Ewcl_Menu{

    public $plugin_name;
    public $menu;
    
    function __construct($plugin_name , $version){
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action( 'admin_menu', array($this,'plugin_menu') );
        add_action($this->plugin_name.'_promotion', array($this,'promotion'));
    }

    function plugin_menu(){
        
        $this->menu = add_submenu_page(
            'tools.php',
            __( 'Export Customer'),
            __( 'Export Customer'),
            'manage_options',
            'pisol-ewcl-notification',
            array($this, 'menu_option_page')
        );

        add_action("load-".$this->menu, array($this,"bootstrap_style"));
 
    }

    public function bootstrap_style() {
        wp_enqueue_style( 'jquery-ui',  plugin_dir_url( __FILE__ ).'css/jquery-ui.css');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pisol-ewcl-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name."_bootstrap", plugin_dir_url( __FILE__ ) . 'css/bootstrap.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css');
        wp_enqueue_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.min.js', array( 'jquery' ), '1.0.4' );

        $js= '
        jQuery(function($){
        $("#pi_ewcl_order_status, #pi_ewcl_user_role").selectWoo({
			placeholder: \'Select Order Status\'
        });
        });
        ';
        wp_add_inline_script('selectWoo', $js, 'after');
	}

    function menu_option_page(){
        ?>
        <div class="bootstrap-wrapper">
        <div class="container mt-2">
            <div class="row">
                    <div class="col-12">
                        <div class='bg-dark'>
                        <div class="row">
                            <div class="col-12 col-sm-2 py-2">
                                    <a href="https://www.piwebsolution.com/" target="_blank"><img class="img-fluid ml-2" src="<?php echo plugin_dir_url( __FILE__ ); ?>img/pi-web-solution.png"></a>
                            </div>
                            <div class="col-12 col-sm-10 d-flex small text-center">
                                <?php do_action($this->plugin_name.'_tab'); ?>
                                <!--<a class=" px-3 text-light d-flex align-items-center  border-left border-right  bg-info " href="https://www.piwebsolution.com/documentation-for-live-sales-notifications-for-woocommerce-plugin/">
                                    Documentation
                                </a>-->
                            </div>
                        </div>
                        </div>
                    </div>
            </div>
            <?php do_action($this->plugin_name.'_tab_sub_menu'); ?>
            <div class="row">
                <div class="col-12">
                <div class="bg-light border pl-3 pr-3 pb-3 pt-0">
                    <div class="row">
                        <div class="col">
                        <?php do_action($this->plugin_name.'_tab_content'); ?>
                        </div>
                        <?php do_action($this->plugin_name.'_promotion'); ?>
                    </div>
                </div>
                </div>
            </div>
        </div>
        </div>
        <?php
    }

    function promotion(){
        ?>
         <div class="col-12 col-sm-12 col-md-4 pt-3">
                <div class="bg-dark text-light text-center mb-3">
                    <a href="<?php echo PI_EWCL_BUY_URL; ?>" target="_blank">
                    <?php  new pisol_promotion("pi_ewcl_installation_date"); ?>
                    </a>
                </div>
            <div class="bg-dark p-3 text-light text-center mb-3">
                <h2 class="text-light font-weight-light "><span>Get Pro<br><h1 class="h2 font-weight-bold text-light my-1"><?php echo PI_EWCL_PRICE; ?></h1><strong class="text-primary">LIMITED</strong> PERIOD OFFER<br>  Buy Now !!</span></h2>
                <div class="inside">
                    PRO version offers more features like<br><br>
                    <ul class="text-left">
					    <li class="border-top py-1 font-weight-light h6"><strong>Export gust customer</strong> list</li>
                        <li class="border-top py-1 font-weight-light h6">Filter guest customer list based on the <strong>Order Status</strong></li>
                        <li class="border-top py-1 font-weight-light h6">Modify the <strong>label of the CSV columns</strong> (and save them so you can directly export csv in your external software)</li>
                        <li class="border-top py-1 font-weight-light h6">Download users based on <strong>registration date</strong></li>
                        <li class="border-top py-1 font-weight-light h6">Download customers based on registration done between a certain <strong>date range</strong></li>
                        <li class="border-top py-1 font-weight-light h6">Get customer list in an email attachment, on set frequency <strong>(Hourly, Twice Daily, Daily, Weekly)</strong></li>
                        <li class="border-top py-1 font-weight-light h6">Get the list of users <strong>registered in the set interval</strong> in an email (Hourly, Daily, Twice Daily, Weekly)</li>
                        <li class="border-top py-1 font-weight-light h6">Download <strong>extra user-related date</strong> (Extra date is one that is added by plugin other than WooCommerce or WordPress)</li>
                        <li class="border-top py-1 font-weight-light h6">Select if you want to receive Registered customer, Guest customer or both customer record in email</li>
                    </ul>
                    <a class="btn btn-light" href="<?php echo PI_EWCL_BUY_URL; ?>" target="_blank">Click to Buy Now</a>
                </div>
               </div>

            </div>
        <?php
    }

    function isWeekend() {
        return (date('N', strtotime(date('Y/m/d'))) >= 6);
    }

}