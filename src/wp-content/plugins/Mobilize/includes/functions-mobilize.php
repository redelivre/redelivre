<?php
/*
    =======================================================================================================
    Mobilize Functions
    =======================================================================================================
*/
function mobilize_tpl() {
    global $wp_query, $campaign;

    if($wp_query->get('tpl') == 'mobilizacao') 
    {
        $wp_query->is_home = false;
            
       // $capabilities = Capability::getByPlanId($campaign->plan_id);
        
        //if ($capabilities->mobilize->value) {
            add_action('wp_print_scripts', function() {
                wp_enqueue_script('mobilize', plugins_url('/js/mobilize.js', __FILE__));
            });

            require(apply_filters('Mobilize-template', INC_MOBILIZE.'/includes/tpl-mobilize.php'));
            die;
        //}
    }
}

function mobilize_single_template($single_template)
{
    global $post;

    $post_slug = $post->post_name;

    if($post_slug == "mobilize" and !locate_template(array('mobilize', 'mobilize.php')))
    {
        add_action('wp_print_scripts', function() {
            wp_enqueue_script('mobilize', plugins_url('/js/mobilize.js', __FILE__));
        });

        $single_template = INC_MOBILIZE.'/includes/tpl-mobilize.php';
    }

    return $single_template;
}

function mobilize_add_menu_page() {
    global $capabilities;
    
    if (current_user_can('manage_options'))
    {
      //  if (isset($capabilities->mobilize->value) && $capabilities->mobilize->value) 
        // {    
            add_menu_page('Mobilização', 'Mobilização', 'read', 'Mobilize', function() {
                require(INC_MOBILIZE . '/includes/admin-mobilize.php');
            });
        // }
    }
}

function mobilize_init() {
    $option = get_option('mobilize-options');

    if(!is_array($option) or !array_key_exists('post-created-status', $option) or $option['post-created-status'] !== true){
        global $user_ID; 

        $new_post = array(
            'post_name'     => 'mobilize', 
            'post_title'    => 'Mobilize', 
            'post_content'  => ' ', 
            'post_status'   => 'publish', 
            'post_date'     => date('Y-m-d H:i:s'), 
            'post_author'   => $user_ID, 
            'post_type'     => 'page', 
            'post_category' => array(0)
        );     

        $post_id = wp_insert_post($new_post);

        update_option('mobilize-options', array('post-created-status' => true));    
    }
}

function do_mobilize_action() {
    Mobilize::adesivar();
}



/*
    =======================================================================================================
    Mobilize Filters
    =======================================================================================================
*/
add_filter('page_template', 'mobilize_single_template');

/*
    =======================================================================================================
    Mobilize Actions
    =======================================================================================================
*/

$options = Mobilize::getOption();

function facebook_share () {
    echo '<meta property="og:image" content="'.Mobilize::getBannerURL(125).'"/>'.PHP_EOL;
    echo '<meta property="og:url" content="'.get_permalink().'"/>'.PHP_EOL;
    echo isset($options['general']['description']) && !empty($options['general']['description']) ? '<meta property="og:title" content="'.$options['general']['description'].'" />'.PHP_EOL : '<meta property="og:title" content="Nesta página, você encontra diferentes formas de mobilização e apoio." />'.PHP_EOL;
    echo '<meta property="og:type" content="blog"/>'.PHP_EOL;
}

add_action('admin_menu', 'mobilize_add_menu_page');
add_action('template_redirect', 'mobilize_tpl');
add_action('wp_head', 'facebook_share');
add_action('init', 'do_mobilize_action', 100);
add_action('init', 'mobilize_init');