<?php 
/** @wordpress-plugin
 * Author:            cWebco WP Plugin Team
 * Author URI:        http://www.cwebconsultants.com/
 */
/* Function for session message */
if (!function_exists('set_error_message')) {
    function set_error_message($msg,$type){
        @session_start();
        if(isset($_SESSION['error_msg'])):  
                unset($_SESSION['error_msg']);
        endif;	

        $_SESSION['error_msg']['msg']=$msg;
        $_SESSION['error_msg']['error']=$type;
        return true;
    }
}

if (!function_exists('show_error_message')) {
    function show_error_message(){
        $msg='';
        @session_start();

        if(isset($_SESSION['error_msg']) && isset($_SESSION['error_msg']['msg'])): 
            if($_SESSION['error_msg']['error']=='1'):
                    $tp='message_error';
            else:
                    $tp='message_success';
            endif;	
            $msg.='<div class="portlet light pro_mess"><div class="message center pmpro_message '.$tp.'">';
                    $msg.=$_SESSION['error_msg']['msg']; 
            $msg.='</div></div>'; 
            unset($_SESSION['error_msg']['msg']);
            unset($_SESSION['error_msg']['error']);
            unset($_SESSION['error_msg']);
        endif;	

        echo $msg;
    }
}	

if (!function_exists('pr')) {
    function pr($post){
        echo '<pre>';
            print_r($post);
        echo '</pre>';
    }
}

/* assign template to pages */

add_action("template_redirect", 'account_page_redirect');
function account_page_redirect() {
   global $wp;

   include_once( ABSPATH . 'wp-admin/includes/plugin.php' );  
   //Set myAccount Custom Page Template 
       if (get_the_ID()== get_option('all_agenda')) {
           $templatefilename = 'all_agenda';
               if (file_exists(CWEB_FS_PATH1.'public/template/all_agenda.php')) {
                   $return_template = CWEB_FS_PATH1.'public/template/all_agenda.php';
                    do_account_redirect($return_template);
               }
       }
}
if (!function_exists('do_account_redirect')) {
       //Finishing setting templates 
       function do_account_redirect($url) {
               global $post, $wp_query;

               if (have_posts()) {
                       include($url);
                       die();
               } else {
                       $wp_query->is_404 = true;
               }
       }
}

//end assignment to pages
add_action( 'init', 'theme_name_scripts' );
function theme_name_scripts() {
   
    if (!is_admin()) {
        $component_css_path = CWEB_WS_PATH1 . 'public/assets/css/components.css';
            wp_enqueue_style('components', $component_css_path);
        } 
    }
    
// force redirect
    
if (!function_exists('foreceRedirect')) {
    function foreceRedirect($filename)
    {
        if (!headers_sent())
                 header('Location: '.$filename);
        else {
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$filename.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$filename.'" />';
            echo '</noscript>';
        }
    }	
}



/*
*   Creating event custom post type
*/

function custom_post_type() {

// Set UI labels for Custom Post Type
	$labels = array(
		'name'                => _x( 'Agenda', 'Post Type General Name', 'twentythirteen' ),
		'singular_name'       => _x( 'Agenda', 'Post Type Singular Name', 'twentythirteen' ),
		'menu_name'           => __( 'Agenda', 'twentythirteen' ),
		'parent_item_colon'   => __( 'Parent Agenda', 'twentythirteen' ),
		'all_items'           => __( 'All Agenda', 'twentythirteen' ),
		'view_item'           => __( 'View Agenda', 'twentythirteen' ),
		'add_new_item'        => __( 'Add New Agenda', 'twentythirteen' ),
		'add_new'             => __( 'Add New', 'twentythirteen' ),
		'edit_item'           => __( 'Edit Agenda', 'twentythirteen' ),
		'update_item'         => __( 'Update Agenda', 'twentythirteen' ),
		'search_items'        => __( 'Search Agenda', 'twentythirteen' ),
		'not_found'           => __( 'Not Found', 'twentythirteen' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'twentythirteen' ),
	);
	
// Set other options for Custom Post Type
	
	$args = array(
		'label'               => __( 'agenda', 'twentythirteen' ),
		'description'         => __( 'Agenda news and reviews', 'twentythirteen' ),
		'labels'              => $labels,
		// Features this CPT supports in Post Editor
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
		// You can associate this CPT with a taxonomy or custom taxonomy. 
		'taxonomies'          => array( 'genres' ),
		/* A hierarchical CPT is like Pages and can have
		* Parent and child items. A non-hierarchical CPT
		* is like Posts.
		*/	
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	
	// Registering your Custom Post Type
	register_post_type( 'agenda', $args );

}

/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/

add_action( 'init', 'custom_post_type', 0 );

//end custom post type


// get event add in post

function add_event(){
    $event_img=$_POST['_event_image'];
    $event_title=$_POST['_event_title'];
    $event_lang=$_POST['_event_language'];
    $event_rating=$_POST['_age_rating'];
    $event_author=$_POST['_event_author'];
    $event_content=$_POST['_event_content'];
    
    $image_url        = $_POST['_event_image']; // Define the image URL here
    $image_name       = 'event-image.jpg';
    $upload_dir       = wp_upload_dir(); // Set upload folder
    $image_data       = file_get_contents($image_url); // Get image data
    $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
    $filename         = basename( $unique_file_name ); // Create image file name 
    
    // Check folder permission and define file location
    
    if(wp_mkdir_p( $upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }
    // Create the image  file on the server
    file_put_contents( $file, $image_data );
    
    // Check image file type
    $wp_filetype = wp_check_filetype( $filename, null );
    
    // Set attachment data
    
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    
    //insert event post 
    
    $post_id = wp_insert_post(array (
        'post_type' => 'agenda',
        'post_title' => $event_title,
        'post_content' => $event_content,
        'post_status' => 'publish',
        'comment_status' => 'closed',   // if you prefer
        'ping_status' => 'closed',      // if you prefer
    ));
    
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    
    // Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // Define attachment metadata
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    
    // Assign metadata to attachment
    wp_update_attachment_metadata($attach_id,$attach_data);
    set_post_thumbnail( $post_id, $attach_id );
    
    if(!empty($post_id)){
        add_post_meta($post_id, '_event_language', $event_lang);
        add_post_meta($post_id, '_age_rating', $event_rating);
        add_post_meta($post_id, '_event_author', $event_author);
        add_post_meta($post_id, '_event_image', $event_img);
    }
    die();  
}
add_action('wp_ajax_add_event', 'add_event');
add_action('wp_ajax_nopriv_add_event', 'add_event'); 


// delete event

function delete_event(){
     $post_id=$_POST['_del_id_'];
    wp_delete_post($post_id);
    delete_post_meta($post_id);
 die();   
}
add_action('wp_ajax_delete_event', 'delete_event');
add_action('wp_ajax_nopriv_delete_event', 'delete_event');






 




