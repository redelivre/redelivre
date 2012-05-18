<?php 
/*
 * IMPORTANTE
 * substituir todos os SLUG pelo slug do projeto
 */

include dirname(__FILE__).'/includes/congelado-functions.php';
include dirname(__FILE__).'/includes/html.class.php';
include dirname(__FILE__).'/includes/utils.class.php';

$campaign = Campaign::getByBlogId($blog_id);

if (is_admin()) {
    require_once(dirname(__FILE__) . '/load_menu_options.php');
}

add_action('template_redirect', 'campanha_check_payment_status');
/**
 * Check the payment status and mark the blog
 * as private in case payment is pending.
 */
function campanha_check_payment_status() {
    global $campaign;

    $user_id = get_current_user_id();

    if (!$campaign->isPaid() && $campaign->campaignOwner->ID !== $user_id
        && !is_super_admin())
    {
        wp_redirect(wp_login_url());
    }
}

add_filter('login_message', 'campanha_login_payment_message');
/**
 * Display a message in the login page about the
 * payment.
 * 
 * @param string $message
 * @return string
 */
function campanha_login_payment_message($message) {
    global $campaign;
    
    if (!$campaign->isPaid()) {
        $message .= '<p class="message">Está campanha está visível somente para o criador pois o pagamento está pendente.</p>';
    }
    
    return $message;
}

add_action('admin_notices', 'campanha_admin_payment_message');
/**
 * Display a message in the admin panel about
 * the payment.
 */
function campanha_admin_payment_message() {
    global $campaign;
    
    if (!$campaign->isPaid()) {
        $link = '/wp-admin/admin.php?page=payments';
        echo "<div class='error'><p>Está campanha está visível somente para o criador pois o pagamento está pendente. <a href='$link'>Pague agora!</a></p></div>";
    }
}

add_action( 'after_setup_theme', 'SLUG_setup' );
function SLUG_setup() {

    load_theme_textdomain('SLUG', TEMPLATEPATH . '/languages' );



    # Custom Background
    # ------------------------------------------------------------------- #
    add_custom_background();



    # Custom Header (RETIRADO DO TWENTY ELEVEN - REFATORAR?)
    # ------------------------------------------------------------------- #
    // The default header text color
    define( 'HEADER_TEXTCOLOR', '000' );
    define( 'HEADER_BACKGROUND_COLOR', 'FF0000' );

    // The height and width of your custom header.
    // Add a filter to twentyeleven_header_image_width and twentyeleven_header_image_height to change these values.
    define( 'HEADER_IMAGE_WIDTH', apply_filters( 'twentyeleven_header_image_width', 1000 ) );
    define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'twentyeleven_header_image_height', 288 ) );

    // We'll be using post thumbnails for custom header images on posts and pages.
    // We want them to be the size of the header image that we just defined
    // Larger images will be auto-cropped to fit, smaller ones will be ignored. See header.php.
    set_post_thumbnail_size( HEADER_IMAGE_WIDTH, HEADER_IMAGE_HEIGHT, true );

    // Add Twenty Eleven's custom image sizes
    add_image_size( 'large-feature', HEADER_IMAGE_WIDTH, HEADER_IMAGE_HEIGHT, true ); // Used for large feature (header) images
    add_image_size( 'small-feature', 500, 300 ); // Used for featured posts if a large-feature doesn't exist

    // Turn on random header image rotation by default.
    add_theme_support( 'custom-header', array( 'random-default' => true ) );

    // Add a way for the custom header to be styled in the admin panel that controls
    // custom headers. See twentyeleven_admin_header_style(), below.
    add_custom_image_header( 'twentyeleven_header_style', 'twentyeleven_admin_header_style', 'twentyeleven_admin_header_image' );

    // Post Thumbnails
    add_theme_support('post-thumbnails');
}



# Twenty Eleven Header Style (RETIRADO DO TWENTY ELEVEN - REFATORAR?)
# ------------------------------------------------------------------- #
if ( ! function_exists( 'twentyeleven_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog
 *
 * @since Twenty Eleven 1.0
 */
function twentyeleven_header_style() {

    // If no custom options for text are set, let's bail
    // get_header_textcolor() options: HEADER_TEXTCOLOR is default, hide text (returns 'blank') or any hex value
    if ( HEADER_TEXTCOLOR == get_header_textcolor() )
        return;
    // If we get this far, we have custom styles. Let's do this.
    ?>
    <style type="text/css">
    <?php
        // Has the text been hidden?
        if ( 'blank' == get_header_textcolor() ) :
    ?>
        #site-title,
        #site-description {
            position: absolute !important;
            clip: rect(1px 1px 1px 1px); /* IE6, IE7 */
            clip: rect(1px, 1px, 1px, 1px);
        }
    <?php
        // If the user has set a custom color for the text use that
        else :
    ?>
        #site-title a,
        #site-description {
            color: #<?php echo get_header_textcolor(); ?> !important;
        }
    <?php endif; ?>
    </style>
    <?php
}
endif; // twentyeleven_header_style



# Twenty Eleven Header Style (RETIRADO DO TWENTY ELEVEN - REFATORAR?)
# ------------------------------------------------------------------- #
if ( ! function_exists( 'twentyeleven_admin_header_style' ) ) :
/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 *
 * Referenced via add_custom_image_header() in twentyeleven_setup().
 *
 * @since Twenty Eleven 1.0
 */
function twentyeleven_admin_header_style() {
?>
    <style type="text/css">
    .appearance_page_custom-header #headimg {
        border: none;
    }
    #headimg h1,
    #desc {
        font-family: "Helvetica Neue", Arial, Helvetica, "Nimbus Sans L", sans-serif;
    }
    #headimg h1 {
        margin: 0;
    }
    #headimg h1 a {
        font-size: 32px;
        line-height: 36px;
        text-decoration: none;
    }
    #desc {
        font-size: 14px;
        line-height: 23px;
        padding: 0 0 3em;
    }
    <?php
        // If the user has set a custom color for the text use that
        if ( get_header_textcolor() != HEADER_TEXTCOLOR ) :
    ?>
        #site-title a,
        #site-description {
            color: #<?php echo get_header_textcolor(); ?>;
        }
    <?php endif; ?>
    #headimg img {
        max-width: 1000px;
        height: auto;
        width: 100%;
    }
    </style>
<?php
}
endif; // twentyeleven_admin_header_style



# Twenty Eleven Admin Header Image (RETIRADO DO TWENTY ELEVEN - REFATORAR?)
# ------------------------------------------------------------------- #
if ( ! function_exists( 'twentyeleven_admin_header_image' ) ) :
/**
 * Custom header image markup displayed on the Appearance > Header admin panel.
 *
 * Referenced via add_custom_image_header() in twentyeleven_setup().
 *
 * @since Twenty Eleven 1.0
 */
function twentyeleven_admin_header_image() { ?>
    <div id="headimg">
        <?php
        if ( 'blank' == get_theme_mod( 'header_textcolor', HEADER_TEXTCOLOR ) || '' == get_theme_mod( 'header_textcolor', HEADER_TEXTCOLOR ) )
            $style = ' style="display:none;"';
        else
            $style = ' style="color:#' . get_theme_mod( 'header_textcolor', HEADER_TEXTCOLOR ) . ';"';
        ?>
        <h1><a id="name"<?php echo $style; ?> onclick="return false;" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
        <div id="desc"<?php echo $style; ?>><?php bloginfo( 'description' ); ?></div>
        <?php $header_image = get_header_image();
        if ( ! empty( $header_image ) ) : ?>
            <img src="<?php echo esc_url( $header_image ); ?>" alt="" />
        <?php endif; ?>
    </div>
<?php }
endif; // twentyeleven_admin_header_image












// admin_bar removal
//wp_deregister_script('admin-bar');
//wp_deregister_style('admin-bar');
remove_action('wp_footer','wp_admin_bar_render',1000);
function remove_admin_bar(){
   return false;
}
add_filter( 'show_admin_bar' , 'remove_admin_bar');

// JS
add_action('wp_print_scripts', 'SLUG_addJS');
function SLUG_addJS() {
    if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); 
    wp_enqueue_script('jquery');
    wp_enqueue_script('congelado', get_stylesheet_directory_uri().'/js/congelado.js', 'jquery');
    
}

// CUSTOM MENU
add_action( 'init', 'SLUG_custom_menus' );
function SLUG_custom_menus() {
    register_nav_menus( array(
        'main' => __('Menu name', 'SLUG'),
    ) );
}

// SIDEBARS
if(function_exists('register_sidebar')) {
    // sidebar 
    register_sidebar( array(
        'name' =>  'Sidebar',
        'description' => __('Sidebar', 'SLUG'),
        'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="widget-content clearfix">',
        'after_widget' => '</div></div>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ) );
}

// EXCERPT MORE

add_filter('utils_excerpt_more_link', 'SLUG_utils_excerpt_more',10,2);
function SLUG_utils_excerpt_more($more_link, $post){
    return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'SLUG') . '</a>';
}


add_filter( 'excerpt_more', 'SLUG_auto_excerpt_more' );
function SLUG_auto_excerpt_more( $more ) {
    global $post;
    return '...<br /><a class="more-link" href="'. get_permalink($post->ID) . '">' . __('Continue reading &raquo;', 'SLUG') . '</a>';
}

// COMMENTS
if (!function_exists('SLUG_comment')):

    function SLUG_comment($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;
        ?>
        <li <?php comment_class("clearfix"); ?> id="comment-<?php comment_ID(); ?>">
            <p class="comment-meta alignright bottom">
                <?php comment_reply_link(array('depth' => $depth, 'max_depth' => $args['max_depth'])) ?> <?php edit_comment_link( __('Edit', 'SLUG'), '| ', ''); ?>          
            </p>    
            <p class="comment-meta bottom">
                <?php printf( __('By %s on %s at %s.', 'SLUG'), get_comment_author_link(), get_comment_date(), get_comment_time()); ?>
                <?php if($comment->comment_approved == '0') : ?><br/><em><?php _e('Your comment is awaiting moderation.', 'SLUG'); ?></em><?php endif; ?>
            </p>
            <?php echo get_avatar($comment, 66); ?>
            <div class="content">
                <?php comment_text(); ?>
            </div>
        </li>
        <?php
    }

endif; 




// custom admin login logo
function custom_login_logo() {
	echo '
        <style type="text/css">
	        h1 a { height: 45px; width: 354px; margin-left: -12px; background-image: url('. html::getImageUrl('logo.png') .') !important; }
        </style>';
}
add_action('login_head', 'custom_login_logo');

function new_headertitle($url){
    return get_bloginfo('sitetitle');
}
add_filter('login_headertitle','new_headertitle');

function custom_login_headerurl($url) {
    return get_bloginfo('url');

}
add_filter ('login_headerurl', 'custom_login_headerurl');
