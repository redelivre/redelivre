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
/* assign template to pages */

// add_action("template_redirect", 'account_page_redirect');
// function account_page_redirect() {
//    global $wp;
//   include_once( ABSPATH . 'wp-admin/includes/plugin.php' );  
//    //Set myAccount Custom Page Template 
//     if (get_the_ID()== get_option('all_agenda')) {
//            $templatefilename = 'all_agenda';
//                if (file_exists(CWEB_FS_PATH1.'public/template/all_agenda.php')) {
// 	                $return_template = CWEB_FS_PATH1.'public/template/all_agenda.php';
//                     do_account_redirect($return_template);
//                }
//        }
// }


add_filter( 'page_template', 'wp_page_template' );
    function wp_page_template( $page_template )
    {
        if (get_the_ID()== get_option('all_agenda')) {
            $page_template = CWEB_FS_PATH1.'public/template/all_agenda.php';
        }
        return $page_template;
    }



add_action( 'init', 'theme_name_scripts' );
function theme_name_scripts() {
   
    if (!is_admin()) {
        $component_css_path = CWEB_WS_PATH1 . 'public/assets/css/components.css';
            wp_enqueue_style('components', $component_css_path);
            
        $chosen = CWEB_WS_PATH1 . 'public/assets/css/chosen.css';
            wp_enqueue_style('chosen', $chosen);
        $pr_style = CWEB_WS_PATH1 . 'public/assets/css/pr_style.css';
            wp_enqueue_style('pr_style', $pr_style);
        $prism = CWEB_WS_PATH1 . 'public/assets/css/prism.css';
            wp_enqueue_style('prism', $prism);  
            
        $jquery_ui = CWEB_WS_PATH1 . 'public/assets/css/jquery-ui.css';
            wp_enqueue_style('jquery_ui', $jquery_ui);
            
        $fontawsme ='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';
            wp_enqueue_style('fontawsme', $fontawsme);     
            
         
            
        $chosen_jquery= CWEB_WS_PATH1 . 'public/assets/js/js/chosen.jquery.js';
            wp_enqueue_script('chosen_jquery', $chosen_jquery, array(), false, True); 
        $init = CWEB_WS_PATH1 . 'public/assets/js/js/init.js';
            wp_enqueue_script('init', $init, array(), false, True);  
        $prism = CWEB_WS_PATH1 . 'public/assets/js/js/prism.js';
            wp_enqueue_script('prism', $prism, array(), false, True);
            
        $jquery_ui_js = CWEB_WS_PATH1 . 'public/assets/js/js/jquery-ui.js';
            wp_enqueue_script('jquery_ui', $jquery_ui_js, array(), false, True);    
    
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

//add texonomies

// hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'create_classification_taxonomies', 0 );


// create two taxonomies, genres and writers for the post type "book"
function create_classification_taxonomies(){
  
// Add new taxonomy, NOT hierarchical (like tags)
  
    $labels = array(
    'name'                       => _x( 'classifcation', 'taxonomy general name' ),
    'singular_name'              => _x( 'classifcation', 'taxonomy singular name' ),
    'search_items'               => __( 'Search classifcation' ),
    'popular_items'              => __( 'Popular classifcation' ),
    'all_items'                  => __( 'All Classifcation' ),
    'parent_item'                => null,
    'parent_item_colon'          => null,
    'edit_item'                  => __( 'Edit Classifcation' ),
    'update_item'                => __( 'Update Classifcation' ),
    'add_new_item'               => __( 'Add New Classifcation' ),
    'new_item_name'              => __( 'New Classifcation Name' ),
    'separate_items_with_commas' => __( 'Separate classifcation with commas' ),
    'add_or_remove_items'        => __( 'Add or remove classifcation' ),
    'choose_from_most_used'      => __( 'Choose from the most used classifcation' ),
    'not_found'                  => __( 'No classifcation found.' ),
    'menu_name'                  => __( 'Classifcation' ),
  );

  $args = array(
    'hierarchical'          => true,
    'labels'                => $labels,
    'show_ui'               => true,
    'show_admin_column'     => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var'             => true,
    'rewrite'               => array( 'slug' => 'classifaction' ),
  );

  register_taxonomy( 'classifaction', 'agenda', $args );
        
        
    $labels = array(
    'name'                       => _x( 'start_at', 'taxonomy general name' ),
    'singular_name'              => _x( 'Start-At', 'taxonomy singular name' ),
    'search_items'               => __( 'Search Start-At' ),
    'popular_items'              => __( 'Popular Start-At' ),
    'all_items'                  => __( 'All Start-At' ),
    'parent_item'                => null,
    'parent_item_colon'          => null,
    'edit_item'                  => __( 'Edit Start-At' ),
    'update_item'                => __( 'Update Start-At' ),
    'add_new_item'               => __( 'Add New Start-At' ),
    'new_item_name'              => __( 'New Start-At Name' ),
    'separate_items_with_commas' => __( 'Separate Start-At with commas' ),
    'add_or_remove_items'        => __( 'Add or remove Start-At' ),
    'choose_from_most_used'      => __( 'Choose from the most used Start-At' ),
    'not_found'                  => __( 'No Start-At found.' ),
    'menu_name'                  => __( 'Start-At' ),
  );

  $args = array(
    'hierarchical'          => true,
    'labels'                => $labels,
    'show_ui'               => true,
    'show_admin_column'     => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var'             => true,
    'rewrite'               => array( 'slug' => 'start_at' ),
  );

  register_taxonomy( 'start_at', 'agenda', $args );


// add categories

    $labels = array(
    'name'                       => _x( 'Category', 'taxonomy general name' ),
    'singular_name'              => _x( 'category', 'taxonomy singular name' ),
    'search_items'               => __( 'Search categoryt' ),
    'popular_items'              => __( 'Popular category' ),
    'all_items'                  => __( 'All category' ),
    'parent_item'                => null,
    'parent_item_colon'          => null,
    'edit_item'                  => __( 'Edit category' ),
    'update_item'                => __( 'Update category' ),
    'add_new_item'               => __( 'Add New category' ),
    'new_item_name'              => __( 'New category' ),
    'separate_items_with_commas' => __( 'Separate category with commas' ),
    'add_or_remove_items'        => __( 'Add or remove category' ),
    'choose_from_most_used'      => __( 'Choose from the most used category' ),
    'not_found'                  => __( 'No Start-At found.' ),
    'menu_name'                  => __( 'Category' ),
  );

  $args = array(
    'hierarchical'          => true,
    'labels'                => $labels,
    'show_ui'               => true,
    'show_admin_column'     => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var'             => true,
    'rewrite'               => array( 'slug' => 'cat_gory' ),
  );

  register_taxonomy( 'Cat_gory', 'agenda', $args );

        
//end texonomies
}


// get event add in post

function add_event(){
    $event_img=$_POST['_event_image'];
    $event_title=$_POST['_event_title'];
    $event_lang=$_POST['_event_language'];
    $event_class=$_POST['_age_rating'];
    $event_author=$_POST['_event_author'];
    $event_content=$_POST['_event_content'];
    
    
    $event_date=$_POST['_start_date'];
    $event_start_time=$_POST['_start_time'];
    $event_end_time=$_POST['_end_time'];
    $event_price=$_POST['_price'];
    $event_address=$_POST['_address'];
    $spaceID=$_POST['_spaceID'];
    
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
    

    //check post already exis or not? and then insert agenda post 
    global $wpdb;
    $return = $wpdb->get_row( "SELECT ID FROM wp_posts WHERE post_title = '" .$event_title. "' && post_status = 'publish' && post_type = 'agenda' ", 'ARRAY_N' );
    $post_id = $return[0];
    if(empty($post_id)){
        $post_id = wp_insert_post(array (
            'post_type' => 'agenda',
            'post_title' => $event_title,
            'post_content' => $event_content,
            'post_status' => 'publish',
            'comment_status' => 'closed',   // if you prefer
            'ping_status' => 'closed',      // if you prefer
        ));
     }
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    
    // Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // Define attachment metadata
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    
    // Assign metadata to attachment
    wp_update_attachment_metadata($attach_id,$attach_data);
    set_post_thumbnail( $post_id, $attach_id );
    
    if(!empty($post_id)){
        update_post_meta($post_id, '_event_language', $event_lang);
        update_post_meta($post_id, '_event_classification', $event_class);
        update_post_meta($post_id, '_event_author', $event_author);
        update_post_meta($post_id, '_start_date', $event_date);
        update_post_meta($post_id, '_start_time', $event_start_time);
        update_post_meta($post_id, '_end_time', $event_end_time);
        update_post_meta($post_id, '_price', $event_price);
        update_post_meta($post_id, '_address', $event_address);
        update_post_meta($post_id, '_spaceID', $spaceID);
        
        wp_set_object_terms( $post_id, $event_class, 'classifaction' );
        wp_set_object_terms( $post_id, $event_start_time, 'start_at' );
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



//get address by latitude or longitude

function getaddress($lat,$lng)
  {
     $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
     $json = @file_get_contents($url);
     $data=json_decode($json);
     $status = $data->status;
     if($status=="OK")
     {
       return $data->results[0]->formatted_address;
     }
     else
     {
       return false;
     }
  }
  
//Add Post Template Files for single agenda pages
  
function get_custom_post_type_template($single_template) {
    global $post;
    global $wp;
        
    $p_type = array('agenda');
    
        if (in_array($post->post_type ,$p_type)) {
            $templatefilename = 'single-'.$post->post_type.'.php';
                if (file_exists(CWEB_FS_PATH1.'public/template/single/'.$templatefilename)) {
                    $single_template = CWEB_FS_PATH1.'public/template/single/'. $templatefilename;
                }
        }
    return $single_template;
}
add_filter( 'single_template', 'get_custom_post_type_template' );



 
function search_agenda_list(){
    if(!empty($_POST)){
        $_class_id=$_POST['_classification'];
        $_start_time_id=$_POST['_start_time'];
        $_start_date=$_POST['_start_date'];
        $_post_title=$_POST['_post_title'];
       
        $args = array(
            'post_type' => 'agenda',
            'post_status' => 'publish',
            'orderby'           => 'meta_value',
            'meta_key'          => '_start_date',
            'meta_type'         => 'DATE',
            'order'             => 'DESC',
            'posts_per_page' => -1

        );
        
        if(!empty($_post_title)):
            $args['s']=$_post_title;
        endif;
        
        $taxQuery=array();
        if(!empty($_class_id)):
            $taxQuery[]=array(
			'taxonomy' => 'classifaction',
			'field'    => 'term_id',
			'terms'    => $_class_id,
		);
        endif;
        if(!empty($_start_time_id)):
                $taxQuery[]=array(
			'taxonomy' => 'start_at',
			'field'    => 'term_id',
			'terms'    => $_start_time_id,
		);
        endif;
        
        $taxRel=array();
        if(!empty($taxQuery)):
            if(count($taxQuery)>1):
                    $taxRel=array(
                        'relation' => 'OR',$taxQuery
                    );
            else:
                $taxRel=array(
                   $taxQuery
               );
            endif;
        endif;
        
        if(!empty($taxRel)):
            $args['tax_query']=$taxRel;
        endif;
// meta query
        if(!empty($_start_date)):
            $args['meta_query']=array(
                        array(
                           'key' => '_start_date',
                           'value' => $_start_date,
                           'compare' => 'LIKE'            
                            ),
                    );        
        endif;
        $the_query = new WP_Query( $args );
        $listing_layout=get_option('listing_layout');
                if($listing_layout=='three_colums'){
                    $class='one_Third';
                    $value=3;
                }elseif ($listing_layout=='two_colums') {
                    $class='one_half';
                    $value=2;
                }else{
                    $class='';
                    $value='';
                }
        echo '<div class="row">';
                $i = 0;
                if ( $the_query->have_posts() ) {
                        while ($the_query->have_posts() ) { $the_query->the_post(); ?>
                           <div class='<?php echo $class; ?> <?php if ($i % $value == 0){echo 'first';}?>'>
                               <div class="agenda_image" id="container_hover">
                                    <?php  $agenda_img=get_the_post_thumbnail_url(); 
                                    if(!empty($agenda_img)){?>
                                       <img src="<?php echo $agenda_img; ?>"/>
                                    <?php }else{?>
                                        <img src="<?php echo CWEB_WS_PATH1;?>/public/assets/img/dummy.jpg"/>
                                    <?php } ?>
                                    <div class="overlay_custom">
                                     <div class="text_hover">
                                            <a href="<?php echo get_the_permalink(); ?>">
                                                <i class="fa fa-link" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </div> 
                                </div>
                                <div class="agenda_title">
                                    <h1><a style="color:#c0392b;" href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></h1>                      </div>
                                <div class="agenda_details">
                                    <div class="lang_details">
                                        <span class="lang_label">Date : </span>
                                        <span class="lang_value"><?php echo get_post_meta(get_the_ID(),'_start_date',true); ?></span>
                                    </div>
                                    <div class="classification_details">
                                        <span class="class_label"><?php echo get_post_meta(get_the_ID(),'_start_time', true); ?></span>
                                    </div>
<!--                                    <div class="author_details">
                                       <span class="author_label">Time : </span>
                                       <span class="author_value"> <?php echo get_post_meta(get_the_ID(),'_start_time', true); ?></span>
                                    </div>-->
                                </div>    
                            </div> 
                            
                        <?php
                               $i++;
                                   if ($i % $value == 0) {echo '</div><div class="row">';}
                                } 
                    echo '</div>';
                    /* Restore original Post Data */
                    wp_reset_postdata();
                     } else {
                          echo 'No Agenda Available.';
                    }
                 }
        die();  
    }
add_action('wp_ajax_search_agenda_list', 'search_agenda_list');
add_action("wp_ajax_nopriv_search_agenda_list", 'search_agenda_list'); 


// shortcode for highlights events

    function highlight_events($atts){
    $return='';
    $a = shortcode_atts( array(
                'category-name' => '#',
            ), $atts );
    $cat_name=$a['category-name'];
    //get cat_id by cat_name
    $cat_details=get_term_by( 'slug', $cat_name, 'Cat_gory');
    $cat_id=$cat_details->term_id;
    $return.='<div class="all_agenda">
    <div class="inner_agenda">';
            // The Query
                $args = array(
                     'post_type' => 'agenda',
                     'post_status' => 'publish',
                     'orderby'           => 'meta_value',
                     'meta_key'          => '_start_date',
                     'meta_type'         => 'DATE',
                     'order'             => 'DESC',
                     'posts_per_page' => -1,
                         'tax_query' => array(
                            array(
                                'taxonomy' => 'Cat_gory',
                                'field'    => 'term_id',
                                'terms'    => $cat_id,
                            ),
                        ),
                );

                $the_query = new WP_Query( $args );
               
                $listing_layout=get_option('listing_layout');
                if($listing_layout=='three_colums'){
                    $class='one_Third';
                    $value=3;
                }elseif ($listing_layout=='two_colums') {
                    $class='one_half';
                    $value=2;
                }else{
                    $class='one_Third';
                    $value='3';
                }
            // The Loop
                $return.='<div class="inner_rows">';
                $return.='<div class="row">';
                $i = 0;
                if ( $the_query->have_posts() ) {
                        while ($the_query->have_posts() ) { $the_query->the_post(); 
                           $return.='<div class="'.$class." ".(($i % $value == 0)?'first':'').'">
                           
                                <div class="agenda_image" id="container_hover">';
                                    $agenda_img=get_the_post_thumbnail_url(); 
                                    if(!empty($agenda_img)){
                                       $return.='<img src="'.$agenda_img.'" class="image"/>';
                                    }else{
                                       $return.='<img src="'.CWEB_WS_PATH1.'/public/assets/img/dummy.jpg" class="image"/>';
                                    }
                        $return.='<div class="overlay_custom">
                        <div class="text_hover">
                            <a href="'.get_the_permalink().'"><i class="fa fa-link" aria-hidden="true"></i></a>
                        </div>
                        </div>
                                </div>
                                <div class="agenda_title">';
                                     $return.='<h1><a style="color:#c0392b;" href="'.get_the_permalink().'">
                                       '.get_the_title().'</a>
                                        </h1>                            
                                </div>
                                <div class="agenda_details">
                                    <div class="lang_details">
                                        <span class="lang_label">Data : </span>';
                                        
                                            $euroTimestamp=strtotime(get_post_meta(get_the_ID(),'_start_date',true));
                                            $start_date= date("d/m/Y", $euroTimestamp);
                                    
                                        $return.='<span class="lang_value">'.$start_date.'</span>';
                                   $return.=' </div>';
                                   $return.='<div class="classification_details">
                                        <span class="class_label">'.get_post_meta(get_the_ID(),'_start_time', true).'</span>
                                    </div>
                                </div>
                            </div>'; 
                            $i++;
                                  if ($i % $value == 0) {
                                      echo '</div><div class="row">';
                                  }
                                } 
                    $return.='</div>';
                    /* Restore original Post Data */
                    wp_reset_postdata();
                     } else {
                          echo 'No Agenda Available.';
                    }
        $return.='</div>
  </div>  </div>    
<div class="clearfix" style="clear:both"></div>';
        return $return;
    }
add_shortcode('highlight_events','highlight_events');


//test shortcode










