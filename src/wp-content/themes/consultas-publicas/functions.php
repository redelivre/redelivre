<?php 

include dirname(__FILE__).'/includes/congelado-functions.php';
include dirname(__FILE__).'/includes/html.class.php';
include dirname(__FILE__).'/includes/utils.class.php';

include dirname(__FILE__).'/includes/exportador-comentarios.php';
include dirname(__FILE__).'/includes/exportador-metas-sugeridas.php';

add_action( 'after_setup_theme', 'consulta_setup' );
function consulta_setup() {
    load_theme_textdomain('consulta', TEMPLATEPATH . '/languages' );
    
    // POST THUMBNAILS
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size( 230, 176, true );
    add_image_size('home-highlight', 230, 176);
    add_image_size('home-secondary-highlight', 270, 132);
    
//    REGISTRAR AQUI TODOS OS TAMANHOS UTILIZADOS NO LAYOUT
//    add_image_size('nome',X,Y);
//    add_image_size('nome2',X,Y);
    
    add_theme_support('custom-background');

    // CUSTOM IMAGE HEADER
    define('HEADER_TEXTCOLOR', '000000');
    define('HEADER_IMAGE_WIDTH', 950);
    define('HEADER_IMAGE_HEIGHT', 105);
    
    add_theme_support(
        'custom-header',
        array(
            'header-text' => true,
            'flex-width'    => true,
            'width'         => 950,
            'flex-height'    => true,
            'height'        => 105,
            'uploads'       => true,
            'wp-head-callback' => 'consulta_custom_header',
            'default-text-color' => '000000'
        )
    );
    
    function consulta_custom_header() {
        $custom_header = get_custom_header();
        ?>
        <style type="text/css">
                    
            #branding { background: url(<?php header_image(); ?>) no-repeat; height: <?php echo $custom_header->height; ?>px;}
            <?php if ( 'blank' == get_header_textcolor() ) : ?>
                #branding a { height: <?php echo $custom_header->height; ?>px; }
                #branding a:hover { background: none !important; }    
            <?php else: ?>       
                #branding, #branding a, #branding a:hover { color: #<?php header_textcolor(); ?> !important; }
                #branding a:hover { text-decoration: none; }
                #description { filter: alpha(opacity=60); opacity: 0.6; }
            <?php endif; ?>        
        </style>
        <?php
    }
    
    // AUTOMATIC FEED LINKS
    add_theme_support('automatic-feed-links');
}

// admin_bar removal
remove_action('wp_footer','wp_admin_bar_render',1000);
function remove_admin_bar(){
   return false;
}

add_filter( 'show_admin_bar' , 'remove_admin_bar');

// JS
function consulta_addJS() {
    wp_enqueue_script('scrollto', get_template_directory_uri() . '/js/jquery.scrollTo-1.4.2-min.js',array('jquery'));
    wp_enqueue_script('jquery-cookie', get_template_directory_uri() . '/js/jquery.cookie.js',array('jquery'));
    wp_enqueue_script('consulta', get_template_directory_uri() . '/js/consulta.js',array('jquery', 'scrollto', 'jquery-cookie'));
    wp_localize_script('consulta', 'consulta', array( 'ajaxurl' => admin_url('admin-ajax.php') ));
    wp_enqueue_script('hl', get_template_directory_uri() . '/js/hl.js', array('consulta'));
    
    if (get_post_type() == 'object') {
        wp_enqueue_script('evaluation', get_template_directory_uri() . '/js/evaluation.js', array('jquery'));
    }
    
    if (is_singular()) wp_enqueue_script( 'comment-reply' );
}
add_action('wp_print_scripts', 'consulta_addJS');

// paginas customizadas
add_filter('query_vars', 'consulta_custom_query_vars');
add_filter('rewrite_rules_array', 'consulta_custom_url_rewrites');
add_action('template_redirect', 'consulta_template_redirect_intercept');

function consulta_custom_query_vars($public_query_vars) {
    $public_query_vars[] = "tpl";

    return $public_query_vars;
}

function consulta_custom_url_rewrites($rules) {
    $new_rules = array(
        "novo/?$" => "index.php?tpl=novo",
    );

    return $new_rules + $rules;
}

function consulta_template_redirect_intercept() {
    global $wp_query;

    switch ($wp_query->get('tpl')) {
        case 'novo':
            $wp_query->is_home = false;
            
            require(TEMPLATEPATH . '/tpl-novo.php');
            die;
        default:
            break;
    }
}


// CUSTOM MENU
add_action( 'init', 'consulta_custom_menus' );
function consulta_custom_menus() {
	register_nav_menus( array(
		'principal' => __('Principal', 'consulta'),
	) );
}

// SIDEBARS
if(function_exists('register_sidebar')) {
    // sidebar 
    register_sidebar( array(
		'name' =>  'Sidebar',		
		'description' => __('Sidebar', 'consulta'),
		'before_widget' => '<div id="%1$s" class="widget %2$s clearfix">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="subtitulo">',
		'after_title' => '</h3>',
	) );
	
}

// EXCERPT MORE
function consulta_auto_excerpt_more( $more ) {
	global $post;
	return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">Continue lendo &raquo;</a>';
}

add_filter( 'excerpt_more', 'consulta_auto_excerpt_more' );


// COMMENTS

if (!function_exists('consulta_comment')): 

function consulta_comment($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    
    $sugestao = get_comment_meta($comment->comment_ID, 'sugestao_alteracao', true);
    
    $autor = get_userdata($comment->user_id);
    global $wpdb;
    $level_var = $wpdb->prefix . 'user_level';
    
    if ($autor) {
        $moderador = (int) $autor->$level_var >= 6;
    }
    
    $commentClass = 'clearfix';
    if ($sugestao) {
        $commentClass = 'clearfix delegado';
    } elseif (isset($moderador)) {
        $commentClass = 'clearfix conselheiro';
    }
    
    ?>
    <li <?php comment_class($commentClass); ?> id="comment-<?php comment_ID(); ?>"  > 
		
        
		<div class="content clearfix">
		<p class="comment-meta">
           <span class="comment-author"><?php echo is_object($autor) ? $autor->display_name : ''; ?></span> | <?php echo get_comment_date() . ' às ' . get_comment_time() ; ?>
        </p>
		<?php if ($sugestao): ?>
			<h6 class="alteracao">
				<?php _oi('Sugestão de alteração para esta meta', 'Comentários: Texto que aparece acima do comentário quando este é uma sugestão de alteração'); ?>
			</h6>
        <?php endif; ?>
        
        <?php //echo get_avatar($comment, 44); ?>
        
			<?php if($comment->comment_approved == '0') : ?><br/><em><?php _oi('Seu comentário está aguardando moderação', 'Comentários: Texto que aparece para o usuário quando o seu comentário fica em moderação'); ?></em><?php endif; ?>
          <?php comment_text(); ?>          
        
         
        <p class="comment-meta">            
			<?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'consulta'), '| ', ''); ?>
        </p>
        </div>
    </li>
    <?php
}

endif; 


function consulta_post_comment ( $post_id ) {
    $sugestao_alteracao = $_POST['sugestao_alteracao'];
    if ( $sugestao_alteracao ) 
        add_comment_meta( $post_id, 'sugestao_alteracao', $sugestao_alteracao, true );
}
add_action( 'comment_post', 'consulta_post_comment', 1 );

////////////////////
/**
function print_msgs($msg, $extra_class='', $id=''){
    if (!is_array($msg)) {
        return false;
    }

    foreach($msg as $type=>$msgs) {
        if (!$msgs) {
            continue;
        }
        
        echo "<div class='$type $extra_class' id='$id'><ul>";

        if (!is_array($msgs)) {
            echo "<li>$msgs</li>";
        } else {
            foreach ($msgs as $m) {
                echo "<li>$m</li>";
            }
        }
        echo "</ul></div>";
    }
}*/


if (!function_exists('_oi')) {

    function _oi($str) {
        echo $str;
    }

    function __i($str) {
        return $str;
    }

}


#pega o numero de pessoas que comentaram em um objeto
function get_num_pessoas_comentarios($post_id) {
    
    if (!is_numeric($post_id))
        return false;
    
    global $wpdb;
    
    return $wpdb->get_var("SELECT COUNT( DISTINCT(user_id) ) FROM $wpdb->comments WHERE comment_post_ID = $post_id AND comment_approved = 1");
    

}



add_filter('the_title', 'quebra_linha_titulo_meta');

function quebra_linha_titulo_meta($title) {
    
    return str_replace('*', '<br />', $title);

}


// loga os acessos
function access_log_callback(){
    if(is_singular()){
        global $post;

        if(is_writable(ABSPATH.'/wp-content/uploads/')){
            $log_path = ABSPATH.'/wp-content/uploads/access_log/';
            $total_path = $log_path.'total/';
            
            if(!file_exists($log_path)){
                mkdir($log_path);
                mkdir($total_path);
            }
            
            $day_path = $log_path.date('Y-m-d').'/';
            
            if(!file_exists($day_path))
                mkdir($day_path);
            
            
            $filename_day = $day_path.$post->ID;
            
            $filename_total = $total_path.$post->ID;
            file_put_contents($filename_day, 1, FILE_APPEND);
            file_put_contents($filename_total, 1, FILE_APPEND);
        }
    }
}

add_action('wp_head', 'access_log_callback');


function is_consulta_encerrada() {

    $datafinal = strtotime(get_theme_option('data_encerramento'));

    $encerrada = true;
    
    if (get_theme_option('data_encerramento') == date('Y-m-d'))
        $encerrada = false;
    
    if ($datafinal) {
        if ($datafinal > time())
            $encerrada = false;
    }
    
    return $encerrada;
    
}

add_action('customize_register', 'consulta_customize_register');
/**
 * Setup options that can be altered in the
 * theme customizer.
 * 
 * @param WP_Customize_Manager $wp_customize
 * @return null
 */
function consulta_customize_register($wp_customize) {
    $wp_customize->add_setting('consulta_theme_options[link_color]',
        array(
            'default' => '#00A0D0',
            'type' => 'option',
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Color_Control($wp_customize, 'link_color',
            array(
                'label' => __('Link color', 'consulta'),
                'section' => 'colors',
                'settings' => 'consulta_theme_options[link_color]',
            )
        )
    );

    $wp_customize->add_setting('consulta_theme_options[title_color]',
        array(
            'default' => '#006633',
            'type' => 'option',
        )
    );
    
    $wp_customize->add_control(
        new WP_Customize_Color_Control($wp_customize, 'title_color',
            array(
                'label' => __('Title color', 'consulta'),
                'section' => 'colors',
                'settings' => 'consulta_theme_options[title_color]',
            )
        )
    );
}


/**
 * Add to the header the CSS elements that can
 * be altered dinamically with the theme customizer
 */
add_action('wp_print_styles', function() {
    $options = get_option('consulta_theme_options');
    $linkColor = isset($options['link_color']) ? $options['link_color'] : '#00A0D0';
    $titleColor = isset($options['title_color']) ? $options['title_color'] : '#006633';
    ?>
    <style>
    /* Colors */

    a { color: <?php echo $linkColor; ?>; }
    #main-menu > li > a:hover, #temas-item:hover, #temas-item.active, #main-menu > li.current-menu-item > a, #main-menu > li.current-menu-ancestor > a, #main-menu > li.current-menu-parent > a, #main-menu > li.current_page_item > a, #main-menu li.active > a { background: <?php echo $linkColor; ?>; }
    .post .interaction .commenters-number { color: <?php echo $linkColor; ?>; }
    .page-link a:hover, .page-link span.current { background: <?php echo $linkColor; ?>; }
    .page-link a.prev:hover, .page-link a.next:hover { color: <?php echo $linkColor; ?>; }
    .tabs li.current { color: <?php echo $linkColor; ?>; }
    .tabs li:hover { color: <?php echo $linkColor; ?>; }
    .meta-sugerida-info { color: <?php echo $linkColor; ?>; }
    .post-content tr th, .post-content thead th { background: <?php echo $linkColor; ?>; }
    .commentlist li.delegado .content, .commentlist li.conselheiro .content { border-left: 4px solid <?php echo $linkColor; ?>; }
    #comentar, #cancel-comment-reply-link, .button-submit { background: <?php echo $linkColor; ?>; }
    .alteracao, .comment-author { color: <?php echo $linkColor; ?>; }
    .tema .interaction .commenters-number { color: <?php echo $linkColor; ?>; }
    #wp-calendar caption { background: <?php echo $linkColor; ?>; }
    #wp-calendar thead th { color: <?php echo $linkColor; ?>; }
    .blue-button { background: <?php echo $linkColor; ?>; }
    .gray-button { color: <?php echo $linkColor; ?>; }
    .post .interaction .comments-number { background-color: <?php echo $linkColor; ?>; }
    .post .interaction span.commenters-number-icon { background-color: <?php echo $linkColor; ?>; }
    .tema .interaction .comments-number { background-color: <?php echo $linkColor; ?>; }
    .tema .interaction .commenters-number span.commenters-number-icon { background-color: <?php echo $linkColor; ?>; }
    .interaction .show_evaluation span.count_object_votes_icon { background-color: <?php echo $linkColor; ?>; }
    .interaction .show_evaluation { color: <?php echo $linkColor; ?> }
    h1,h2,h3,h4,h5,h6 { color: <?php echo $titleColor; ?>; }
    #cronometro span { color: <?php echo $titleColor; ?>; }
    .post-content label { color: <?php echo $titleColor; ?>; }
    #respond label { color: <?php echo $titleColor; ?>; }
    .acao-numero { color: <?php echo $titleColor; ?>; }
    #login, #cronometro { background-color: <?php echo $titleColor; ?>; }
    .participation-button { background-color: <?php echo $titleColor; ?>; }
    #feed-link { background-color: <?php echo $titleColor; ?>;}
    .hl-lightbox-close { background-color: <?php echo $titleColor; ?>; }
    
    .evaluation_bar { background-color: <?php echo $linkColor; ?> }
    
    #new_object .clearfix > label { color: <?php echo $titleColor; ?>; }
    #new_object_submit { background: <?php echo $titleColor; ?>; }
    #new_object_submit:hover { background: <?php echo $linkColor; ?>; }
    .suggested-user-icon{ background: <?php echo $linkColor; ?>; }

    </style>
    <?php
    
}, 20);

/**
 * Return the value of the current user vote
 * for the object evaluation.
 * 
 * @param int $postId
 * @return string
 */
function get_user_vote($postId) {
    global $wpdb;

    $userVote = $wpdb->get_var(
        $wpdb->prepare("SELECT `meta_key` FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_value = %d", $postId, get_current_user_id())
    );

    return $userVote;
}

/**
 * Get all the users votes for the
 * object evaluation.
 * 
 * @param int $postId
 * @return array
 */
function get_votes($postId) {
    $votes = array();

    foreach (range(1, 5) as $i) {
        $votes[] = count(get_post_meta($postId, '_label_' . $i));
    }
    
    return $votes;
}

/**
 * Return the number of votes in an
 * object.
 * 
 * @param int $postId
 * @return int
 */
function count_votes($postId) {
    $votes = 0;

    foreach (range(1, 5) as $i) {
        $votes += count(get_post_meta($postId, '_label_' . $i));
    }
    
    return $votes;
}

/**
 * Compute user vote for object evaluation
 */
add_action('wp_ajax_object_evaluation', function() {
    $data = array();
    $userVote = filter_input(INPUT_POST, 'userVote', FILTER_SANITIZE_STRING);
    $postId = filter_input(INPUT_POST, 'postId', FILTER_SANITIZE_NUMBER_INT);
    
    // delete old vote if user already voted
    if ($userOldVote = get_user_vote($postId)) {
        delete_post_meta($postId, $userOldVote);
    }
    
    update_post_meta($postId, '_' . $userVote, get_current_user_id());
    
    global $post;
    
    $post = get_post($postId);
    
    ob_start();
    html::part('evaluation');
    $data['html'] = ob_get_clean();
    $data['count'] = count_votes($postId);
    
    die(json_encode($data));
});

function consulta_default_menu() {
    $objects_link = site_url( get_theme_option('object_url') );
    $object_ob = get_post_type_object('object');
    $objects_label = $object_ob->labels->name;
    ?>
        <ul id="main-menu" class="clearfix">
            <?php wp_list_pages('title_li='); ?>
            <li>
                <a href="<?php echo $objects_link; ?>"><?php echo $objects_label; ?></a>
            </li>
        </ul>
    <?php
    
}

function consulta_get_votes_percentage($votes) {

    if (!is_array($votes) || sizeof($votes) < 5)
        return -1;
        
    $sum = array_sum($votes); 
    
    if ($sum < 1)
        return array(0,0,0,0,0);
           
    $return = array();
    
    foreach ($votes as $k => $vote) {
        $return[$k] = number_format(($vote / $sum) * 100, 1);
    }
    
    return $return;

}

function consulta_get_votes_average($votes) {

    if (!is_array($votes) || sizeof($votes) < 5)
        return -1;
        
    $sum = array_sum($votes); 
    $value = 0;
    
    if ($sum < 1)
        return 0;
    
    $value += $votes[0] * 1;
    $value += $votes[1] * 2;
    $value += $votes[2] * 3;
    $value += $votes[3] * 4;
    $value += $votes[4] * 5;
    
    return number_format($value / $sum, 1);
    

}

function consulta_get_width_item() {

    $evaluationOptions = get_theme_option('evaluation_labels');
    
    $i = 0;
    foreach ($evaluationOptions as $key => $value) {
        if (empty($value)) break;
        $i++;
    }
    
    return 100 / $i;
    

}

function consulta_get_number_alternatives() {

    $evaluationOptions = get_theme_option('evaluation_labels');
    
    $i = 0;
    foreach ($evaluationOptions as $key => $value) {
        if (empty($value)) break;
        $i++;
    }
    
    return $i;

}

/*
 * Configurações customizadas antes de pegar os
 * posts que serão exibidos.
 * 
 * @param WP_Query $query
 * @return null
 */
function consulta_pre_get_posts($query) {
    if (is_admin()) {
        return;
    }

    if ($query->get('post_type') == 'object' && $query->is_archive) {
        // não página a listagem de objetos quando exibe apenas o título.
        if (get_theme_option('list_type') == 'title') {
            $query->set('posts_per_page', -1);
        }
        
        if ($query->is_main_query() && (get_theme_option('list_type') == 'title' || get_theme_option('list_type') == 'title_taxonomy')) {
            // não exibe objetos criados pelo usuário na listagem padrão quando exibe apenas o título ou título organizado por taxonomia
            $query->set('meta_key', '_user_created');
            $query->set('meta_value', false);
        }
        
        // permite que o admin defina a ordenação dos objetos
        $query->set('order', get_theme_option('list_order'));
        $query->set('orderby', get_theme_option('list_order_by'));
    }
}
add_action('pre_get_posts', 'consulta_pre_get_posts', 1);

add_action('consulta_show_user_link', 'consulta_show_user_link');
/**
 * Exibe no header um link para o perfil do usuário.
 * 
 * @return null
 */
function consulta_show_user_link() {
    global $current_user;

    ?>
    <div id="logged-user-name">
        <a href="<?php echo get_edit_profile_url($current_user->ID); ?>">
            <?php echo substr($current_user->display_name, 0, 38); ?>
        </a>
    </div>
    <?php
}

/**
* Ajustes para implementar no campanha
*/
$custom_header_config = array(
	'width'                  => 960,
	'height'                 => 198,
	'flex-height' 			 => true, 
	'flex-widht' 			 => true
);

add_theme_support('custom-header');
if ( ! isset( $content_width ) ) $content_width = 900;

add_filter('the_content', 'propostas_inline');
function propostas_inline($content)
{
    global $post;
    
    $resultado = array();
    
    preg_match_all("/\[propostas\](.*)\[\/propostas\]/s", $content, $resultado, PREG_PATTERN_ORDER);
 
    $propostas_parsed = '<div class="lista-propostas">' . $resultado[1][0] . '</div>';

    $resultado_itens = array();
    preg_match_all("/(?:^|\s)\#(\w+)\b/s", $propostas_parsed, $resultado_itens);
    
    foreach($resultado_itens[0] as $chave => $item_proposta) {
        
        $item_proposta = trim($item_proposta);
        $item_proposta_clear = trim($resultado_itens[1][$chave]);
        
        $meta_concordo =  get_post_meta($post->ID, $item_proposta.'-concordo');
        $numero_concordam = intval($meta_concordo[0]);
        
        $meta_naoconcordo = get_post_meta($post->ID, $item_proposta.'-naoconcordo');
        $numero_naoconcordam = intval($meta_naoconcordo[0]);
        
        $propostas_parsed = str_replace($item_proposta, 
            "<p class='opcoes-proposta'><a data-post='".$post->ID."' href='".$item_proposta."' class='proposta-concordo'>concordo</a>(<span id='".$item_proposta_clear."-proposta-concordo'>$numero_concordam</span>) " .
            "<a data-post='".$post->ID."' href='".$item_proposta."' class='proposta-naoconcordo'>não concordo</a>(<span id='".$item_proposta_clear ."-proposta-naoconcordo'>$numero_naoconcordam</span>)</p>", $propostas_parsed);
    }
    
    $content = preg_replace ("/\[propostas\](.*)\[\/propostas\]/s", $propostas_parsed, $content);    

    return $content; 
}

if ($_REQUEST['proposta_inline'] == 'true') {
    
    switch ($_REQUEST['tipo_proposta_inline']) {
        case "proposta-concordo":
            $meta = get_post_meta($_REQUEST['post_id'], $_REQUEST['item_proposta'].'-concordo');
            $numero_concordam = intval($meta[0]);
            update_post_meta($_REQUEST['post_id'], $_REQUEST['item_proposta']."-concordo", ++$numero_concordam);
            echo $numero_concordam;
            break;
        case "proposta-naoconcordo":
            $meta = get_post_meta($_REQUEST['post_id'], $_REQUEST['item_proposta'].'-naoconcordo');
            $numero_naoconcordam = intval($meta[0]);
            update_post_meta($_REQUEST['post_id'], $_REQUEST['item_proposta']."-naoconcordo", ++$numero_naoconcordam);
            echo $numero_naoconcordam;
            break;
    }
    
    die();
}