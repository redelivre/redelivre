<?php
namespace WPCF\woocommerce;

defined( 'ABSPATH' ) || exit;

class Templating {

    /**
     * @var mixed|void
     *
     * Get selected theme name
     */
    public $_theme;

    /**
     * @var string
     * Return theme path in wp theme
     */
    public $_theme_in_themes_path;

    /**
     * @var string
     * Return theme in crowdfunding directory
     */
    public $_theme_in_plugin_path;

    /**
     * @var string
     *
     * Return selected theme directory, whaterver it is plugin or theme directory
     */
    public $_selected_theme_path;

    /**
     * @var string
     *
     * Return selected theme file, whaterver it is plugin or theme directory
     */
    public $_selected_theme;

    public $_selected_theme_uri;

    /**
     * @var
     *
     * determine you are used which vendor
     * [woocommerce, edd, wpneo]
     */

    public $_vendor;


    /**
     * @var
     * Get vendor path in plugin directory
     */
    public $_vendor_path;


    protected static $_instance;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->_theme = get_option('wpneo_cf_selected_theme',true);

        /**
         * Set Vendor, we checking here which vendor we are using currently
         * woocommerce, edd, native
         */
        $this->_vendor = get_option('vendor_type',true);

        //Set Theme
        $is_new_theme = true;
        if( !file_exists( get_stylesheet_directory()."/wpcftemplate/{$this->_vendor}/{$this->_theme}/style.css" ) ){
            $is_new_theme = false;
        }

        $this->_theme_in_themes_path = get_stylesheet_directory()."/".( $is_new_theme ? "wpcftemplate" : "wpneotemplate" )."/{$this->_vendor}/{$this->_theme}/";
        $this->_theme_in_plugin_path = WPCF_DIR_PATH."wpcftemplate/{$this->_vendor}/{$this->_theme}/";
        $this->_vendor_path = WPCF_DIR_PATH."wpcftemplate/{$this->_vendor}/";

        $single_template_path = $this->_theme_in_themes_path."single-crowdfunding.php";
        $single_plugin_path = $this->_theme_in_plugin_path."single-crowdfunding.php";

        if (file_exists($single_template_path)){
            $this->_selected_theme_path = $this->_theme_in_themes_path;
            $this->_selected_theme = $single_template_path;
        } elseif(file_exists($single_plugin_path)) {
            $this->_selected_theme_path = $this->_theme_in_plugin_path;
            $this->_selected_theme = $single_plugin_path;
        }

        if ($this->check_theme_standard($this->_selected_theme_path)){
            if (file_exists($this->_theme_in_themes_path.'style.css')){
                $this->_selected_theme_uri = get_stylesheet_directory_uri()."/".( $is_new_theme ? "wpcftemplate" : "wpneotemplate" )."/{$this->_vendor}/{$this->_theme}/";
            }else{
                $this->_selected_theme_uri = WPCF_DIR_URL."wpcftemplate/{$this->_vendor}/{$this->_theme}/";
            }
        }else{
            if (file_exists($this->_selected_theme_path.'style.css')){
                $this->_selected_theme_uri = get_stylesheet_directory_uri()."/".( $is_new_theme ? "wpcftemplate" : "wpneotemplate" )."/{$this->_vendor}/{$this->_theme}/";
            }else{
                $this->_selected_theme_uri = WPCF_DIR_URL."/wpcftemplate/{$this->_vendor}/{$this->_theme}/";
            }
        }

        //Determine where single campaign will be load, is it WooCommerce or Wp Crowdfunding
        $single_page_template = get_option('wpneo_single_page_template');

        if (empty($single_page_template) || ($single_page_template == 'in_wp_crowdfunding') ){
            add_filter( 'template_include',         array( $this, 'template_chooser_callback' ), 99); //Get custom template for this
        }

        add_action( 'wpcf_select_theme',    array( $this, 'selected_theme_callback') ); //Generate a dropdown for theme
        add_action( 'admin_notices',            array( $this, 'theme_noticed_callback') );
        add_action( 'init',                     array( $this, 'require_theme_resources') );
        add_action( 'wp_enqueue_scripts',       array( $this, 'load_theme_css_callback' ) );
        //add_action( 'template_redirect',        array( $this, 'theme_redirect_callback') ); //Template Redirect
    }

    public function template_chooser_callback($template){
        global $post, $woocommerce;

        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        //Check is single page
        if (is_single()) {
            //Check is woocommerce activate
            if (function_exists('wc_get_product')) {
                $product = wc_get_product($post_id);
                if ($post_type === 'product') {
                    if ($product->get_type() === 'crowdfunding') {
                        if (file_exists($this->_selected_theme)) {
                            $template = $this->_selected_theme;
                        }
                    }
                }
            }
        }
        return $template;
    }

    /**
     * @param string $theme_name
     * @return mixed
     *
     *
     * Get theme info from comment block
     * Info have to give in theme/index.php
     */
    public function get_theme_info($theme_name = ''){
        if ( ! $theme_name)
            $theme_name = $this->_selected_theme_path.'index.php';

        $docComments = array_filter(
            token_get_all( file_get_contents( $theme_name ) ), array($this, 'return_t_doc_comment')
        );
        $fileDocComment = array_shift( $docComments );
        $comment_all = $fileDocComment[1];
        //$result = preg_replace("/[^a-zA-Z0-9]+/", "", $fileDocComment[1]);
        $get_comment_by_line = explode("\n",$comment_all);

        $comments = array();
        if (count($get_comment_by_line) > 0){
            foreach($get_comment_by_line as $value){
                $get_comment = preg_replace("/[^a-zA-Z0-9:. ]+/", "", $value);
                $get_comment = trim($get_comment);
                if ( ! empty($get_comment)) {
                    $get_comment = explode(':', $get_comment);
                    $get_comment = array(
                        trim(strtolower(str_replace(' ', '_', $get_comment[0]))) => trim($get_comment[1])
                    );
                    $comments[] = $get_comment;
                }
            }
        }
        return call_user_func_array('array_merge', $comments);
    }

    public function return_t_doc_comment($entry){
        return $entry[0] == T_DOC_COMMENT;
    }
    /**
     * Theme standard
     * These file required for develop a wpneo crowdfunding theme
     */
    public function theme_standard_check(){
        $theme_standard = array(
            'index.php',
            'style.css',
            'wpneo-listing.php',
            'single-crowdfunding.php',
            'wpneo-functions.php'
        );

        return $theme_standard;
    }

    /**
     * Show theme error notice in admin panel
     */
    public function theme_noticed_callback(){
        $theme_standard = $this->theme_standard_check();

        $themes_dir = $this->wpcf_select_themes_dir();
        $html = "";

        if (count($themes_dir) > 0) {
            foreach ($themes_dir as $k => $v) {
                $theme_info = $this->get_theme_info($this->_vendor_path . $v . '/index.php');

                $files = array_slice(scandir($this->_vendor_path . $v), 2);
                $missing_files = array_diff($theme_standard, $files);

                if (count($missing_files) > 0){
                    $html .= '<div class="notice notice-error"><p>';
                    $html .= __('Error crowdfunding theme: ', 'wp-crowdfunding'). $theme_info['theme_name']. '<br />';

                    foreach($missing_files as $file){
                        $html .= "<strong>{$file}</strong> missing<br />";
                    }
                    $html .= '</p></div>';
                }
            }
        }
        echo $html;
    }

    /**
     * @param string $directory
     * @param string $theme_name
     * @return bool
     *
     * Check theme standard
     */
    public function check_theme_standard($directory = '', $theme_name =''){
        $theme_standard = $this->theme_standard_check();
        $files = array_slice(scandir($directory), 2);
        $missing_files = array_diff($theme_standard, $files);

        if (count($missing_files) > 0){
            return false;
        }

        return true;
    }

    /**
     * Generate select option html for theme
     */
    public function selected_theme_callback(){
        $themes_dir = $this->wpneo_cf_select_themes_dir();
        $html = '';

        $html .='<table class="form-table">';
            $html .='<tbody>';
            $html .='<tr>';
                $html .='<th scope="row"><label>'.__('Select a Theme for Single and Listing Pages', 'wp-crowdfunding').'</label></th>';
                $html .='<td>';
                    $html .= '<select name="wpneo_cf_selected_theme">';
                        if (count($themes_dir) > 0){
                            $html .= '<option value="">'.__('Select a theme', 'wp-crowdfunding').'</option>';
                            foreach($themes_dir as $k => $v) {
                                $selected   = ($this->_theme == $v) ? 'selected' : '';
                                $theme_info = $this->get_theme_info($this->_vendor_path.$v.'/index.php');
                                $is_theme   = $this->check_theme_standard($this->_vendor_path.$v);
                                if ($is_theme){
                                    $html .= '<option value="' . $v . '" ' . $selected . '>' . $theme_info['theme_name'] . '</option>';
                                }
                            }
                        }else{
                            $html .= '<option value="">'.__('You have no valid theme', 'wp-crowdfunding').'</option>';
                        }
                    $html .= '</select>';
                $html .= '</td>';
            $html .='</tr>';
            $html .='</tbody>';
        $html .='</table>';
        echo $html;
    }

    /**
     * @return array
     *
     * @return all theme directory from selected vendor
     */
    public function wpcf_select_themes_dir(){
        $theme_dirs = array_filter(glob(WPCF_DIR_PATH.'wpcftemplate/woocommerce/*'), 'is_dir');
        $get_dir = array();
        if (count($theme_dirs) > 0) {
            foreach ($theme_dirs as $key => $value) {
                $get_dir[] = str_replace(dirname($value).'/', '', $value);
            }
        }
        return $get_dir;
    }

    /**
     * Include wpneo theme functions with wordpress core
     */
    public function require_theme_resources(){
        $is_valid_theme = $this->check_theme_standard($this->_selected_theme_path);
        if ($is_valid_theme) {
            include_once $this->_selected_theme_path . 'wpneo-functions.php';
        }else{
            include_once $this->_theme_in_plugin_path . 'wpneo-functions.php';
        }
    }

    /**
     * Include wpneo theme CSS in frontend
     */
    public function load_theme_css_callback(){
        $is_valid_theme = $this->check_theme_standard($this->_selected_theme_path);
        if ($is_valid_theme) {
            if (file_exists($this->_theme_in_themes_path.'style.css')){
                wp_enqueue_style('wpcf_style', $this->_selected_theme_uri.'style.css',array(), WPCF_VERSION);
            }else{
                wp_enqueue_style('wpcf_style', $this->_selected_theme_uri.'style.css',array(), WPCF_VERSION);
            }
        }else{
            wp_enqueue_style('wpcf_style', $this->_selected_theme_uri.'style.css',array(), WPCF_VERSION);
        }
    }


    /**
     * Template Redirect
     */

    public function theme_redirect_callback() {
        $listing_id = get_option('wpneo_listing_page_id','');
        $form_id = get_option('wpneo_form_page_id','');
        $registration_id = get_option('wpneo_registration_page_id','');
        $dashboard_id = get_option('wpneo_crowdfunding_dashboard_page_id','');

        if( is_page() ){
            if( ($listing_id != '') && ($listing_id != '0') ){
                $slug1 = get_post($listing_id)->post_name;
                $this->template_load( $slug1 );
            }
            if( ($form_id != '') && ($form_id != '0') ){
                $slug2 = get_post($form_id)->post_name;
                $this->template_load( $slug2 );
            }
            if( ($registration_id != '') && ($registration_id != '0') ){
                $slug3 = get_post($registration_id)->post_name;
                $this->template_load( $slug3 );
            }
            if( ($dashboard_id != '') && ($dashboard_id != '0') ){
                $slug4 = get_post($dashboard_id)->post_name;
                $this->template_load( $slug4 );
            }
        }

        if(!is_page()){
            if(is_single()){
                if(function_exists('is_product')){
                    if( is_product() ){
                        $var = get_option( 'wpneo_single_page_id');
                        if($var=='true'){
                            $_product = wc_get_product( get_the_ID() );
                            if( $_product->is_type( 'crowdfunding' ) ){
                                remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
                            }
                        }
                    }
                }
            }
        }
    }

    public function template_load( $slug ){
        global $wp;
        if (! empty($wp->query_vars["pagename"])) {
            if ($wp->query_vars["pagename"] == $slug) {
                $return_template = dirname($this->_selected_theme) . '/page-fullwidth.php';
                $this->do_theme_redirect($return_template);
            }
        }
    }

    public function do_theme_redirect($url) {
        global $post, $wp_query;
        if (have_posts()) {
            include($url);
            die();
        } else {
            $wp_query->is_404 = true;
        }
    }

}