<?php
/*
Plugin Name: Facebook Thumb Fixer
Plugin URI: https://wordpress.org/support/plugin/facebook-thumb-fixer
Description: Control how your thumbnails are viewed when a post is shared on Facebook, Twitter and Google+.
Author: Michael Ott
Version: 1.7.5
Author URI: http://michaelott.id.au
Text Domain: facebook-thumb-fixer
Domain Path: /languages/
*/

// Look for translation file.
function load_fbtf_textdomain() {
    load_plugin_textdomain( 'facebook-thumb-fixer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'load_fbtf_textdomain' );

// Add HELP link from the plugin page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'link_action_on_plugin' );
function link_action_on_plugin( $links ) {
	return array_merge(array('settings' => '<a href="' . admin_url( '/options-general.php' ) . '">' . __( 'Settings', 'facebook-thumb-fixer' ) . '</a> | <a href="' . admin_url( '/options-general.php?page=facebook-thumb-fixer' ) . '">' . __( 'Help', 'facebook-thumb-fixer' ) . '</a>'), $links);
}

// Include custom CSS
function admin_load_fbf_css(){
	wp_enqueue_style('stylsheet', plugins_url( '/css/ftf.css', __FILE__ ) );
	add_thickbox();
}
add_action('admin_enqueue_scripts', 'admin_load_fbf_css');

// Show message upon plugin activation
register_activation_hook( __FILE__, 'ftf_admin_notice_activation_hook' );
 
// Runs only when the plugin is activated
function ftf_admin_notice_activation_hook() {
 
    /* Create transient data */
    set_transient( 'ftf-admin-notice', true, 1000 );
}

/* Add admin notice */
add_action( 'admin_notices', 'ftf_admin_notice' );
 
// Admin Notice on Activation
function ftf_admin_notice(){
 
    /* Check transient, if available display notice */
    if( get_transient( 'ftf-admin-notice' ) ){
        ?>
        <div class="updated notice is-dismissible ir-admin-message">
            <?php $presentation_options_url = admin_url() . 'options-general.php#dfb'; ?>
            <p><?php printf( __( "Awesome! Don't forget to set a default facebook thumbnail  <a href='%s'>here</a>.", "facebook-thumb-fixer" ), $presentation_options_url); ?></p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'ftf-admin-notice' );
    }
}

// Add image path field into the general settings page
$setting_default_fb_thumb = new general_setting_default_fb_thumb();
class general_setting_default_fb_thumb {
    function general_setting_default_fb_thumb( ) {
        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
    }
    function register_fields() {
        register_setting( 'general', 'default_fb_thumb', 'esc_attr' );
        add_settings_field('dft', '<label for="default_fb_thumb" id="dfb">' . __('Default Facebook Thumb' , 'facebook-thumb-fixer' ) . '</label>' , array(&$this, 'fields_html') , 'general' );
    }
    function fields_html() {
		$fbt_value 			= get_option( 'default_fb_thumb');
		$fb_URL 	  		= 'https://developers.facebook.com/docs/sharing/best-practices#images';
		$settings_URL 		= admin_url( '/options-general.php?page=facebook-thumb-fixer' );
		$home_image_ID 		= attachment_url_to_postid( $fbt_value ); 									// Get the ID of the default image
		$image_attributes	= wp_get_attachment_image_src( $attachment_id = $home_image_ID, 'full' ); 	// Get the image attributes of the default image
		$width				= $image_attributes[1];														// Get the width
		$height				= $image_attributes[2];														// Get the height
		?>

		<input id="default_fb_thumb" name="default_fb_thumb" type="text" value="<?php if($fbt_value) { esc_attr_e( $fbt_value ); } ?>" />
    	<input id="default_fb_thumb_button" class="upload-button button" name="default_fb_thumb_button" type="text" value="<?php _e( 'Browse', 'facebook-thumb-fixer' ); ?>" />
		<script>
			// Media uploader
			jQuery(document).ready(function($) {
			var _custom_media = true,
			_orig_send_attachment = wp.media.editor.send.attachment;

			$('.upload-button').click(function(e) {
				var send_attachment_bkp = wp.media.editor.send.attachment;
				var button = $(this);
				var id = button.attr('id').replace('_button', '');
				_custom_media = true;
				wp.media.editor.send.attachment = function(props, attachment){
					if ( _custom_media ) {
						$("#"+id).val(attachment.url);
					} else {
						return _orig_send_attachment.apply( this, [props, attachment] );
					};
				}

				wp.media.editor.open(button);
				return false;
			});

			$('.add_media').on('click', function(){
				_custom_media = false;
			});
		});
		</script>
		<?php wp_enqueue_media(); ?>

		<p class="description">
			<?php echo sprintf( __( 'Browse to the preferred Facebook image for your homepage. Facebook <a href="%1$s" target="_blank">recommends</a> your image be 1200 x 630 or 600 x 315.', 'facebook-thumb-fixer' ), $fb_URL) ?>
		</p>
		
		<?php if ($fbt_value) { ?>

		<a href="<?php echo $fbt_value; ?>?TB_iframe=true&width=600&height=550" class="thickbox">
		<img src="<?php echo $fbt_value; ?>" class="thickbox ftf-preview" /></a>

		<?php if ($width < 600 || $height < 315) { ?>
			<p class="ftf-warning">
				<?php echo sprintf( __( '<strong>Oops! </strong>Your default Facebook image is smaller than the minimum 600 x 315 <a href="%1$s" target="_blank">recommended</a> by Facebook.', 'facebook-thumb-fixer' ), $fb_URL) ?>
			</p>
		<?php } else { ?>
			<p class="description good">
				<span>&#10004</span> <?php echo sprintf( __( 'Your default Facebook image has dimensions of at least 600 x 315 (actual dimensions are %1$s x %2$s).', 'facebook-thumb-fixer' ), $width, $height) ?>
			</p>
		<?php }
		}
    }
}

// Add Facebook App ID field into the general settings page
$general_setting_fb_app_ID = new general_setting_fb_app_ID();
class general_setting_fb_app_ID {
    function general_setting_fb_app_ID( ) {
        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
    }
    function register_fields() {
        register_setting( 'general', 'fb_app_ID', 'esc_attr' );
        add_settings_field('faid', '<label for="fb_app_ID" id="fb_app_ID">' . __('Facebook App ID' , 'facebook-thumb-fixer' ).'</label>' , array(&$this, 'fb_app_ID_field') , 'general' );
    }
    function fb_app_ID_field() {
	$fbaid_value = get_option( 'fb_app_ID', '' ); ?>
        
    <input type="text" id="fb_app_ID" class="regular-text ltr" name="fb_app_ID" value="<?php echo $fbaid_value; ?>" />
	<?php $fb_app_ID_URL = 'https://developers.facebook.com/apps/'; ?>
	<p class="description"><?php echo sprintf( __( 'Find your Facebook App ID <a href="%1$s" target="_blank">here</a>.', 'facebook-thumb-fixer' ), $fb_app_ID_URL); ?></p>
	
	<?php }
}


// Add object type selection into the general settings page
$general_setting_object_type = new general_setting_object_type();
class general_setting_object_type {
    function general_setting_object_type( ) {
        add_filter( 'admin_init' , array( &$this , 'register_object_type' ) );
    }
    function register_object_type() {
        register_setting( 'general', 'homepage_object_type', 'esc_attr' );
        add_settings_field('object_type', '<label for="homepage_object_type">' . __('Homepage Object Type' , 'facebook-thumb-fixer' ) . '</label>' , array(&$this, 'ot_fields_html') , 'general' );
    }
    function ot_fields_html() { ?>

        <?php
			 $hpot = get_option( 'homepage_object_type', '');
		?>
		<select value="homepage_object_type" name="homepage_object_type"<?php if($hpot == "") { echo " class='no-object-type'"; } ?>>
        	<option></option>
            <option value="article"<?php if($hpot == "article") { echo " selected"; } ?>>article</option>
            <option value="book"<?php if($hpot == "book") { echo " selected"; } ?>>book</option>
            <option value="books.author"<?php if($hpot == "books.author") { echo " selected"; } ?>>books.author</option>
			<option value="business.business"<?php if($hpot == "business.business") { echo " selected"; } ?>>business.business</option>
            <option value="fitness.course"<?php if($hpot == "fitness.course") { echo " selected"; } ?>>fitness.course</option>
            <option value="fitness.unit"<?php if($hpot == "fitness.unit") { echo " selected"; } ?>>fitness.unit</option>
            <option value="music.album"<?php if($hpot == "music.album") { echo " selected"; } ?>>music.album</option>
            <option value="music.playlist"<?php if($hpot == "music.playlist") { echo " selected"; } ?>>music.playlist</option>
            <option value="music.radio_station"<?php if($hpot == "music.radio_station") { echo " selected"; } ?>>music.radio_station</option>
            <option value="music.song"<?php if($hpot == "music.song") { echo " selected"; } ?>>music.song</option>
            <option value="place"<?php if($hpot == "place") { echo " selected"; } ?>>place</option>
            <option value="product"<?php if($hpot == "product") { echo " selected"; } ?>>product</option>
            <option value="product.group"<?php if($hpot == "product.group") { echo " selected"; } ?>>product.group</option>
            <option value="profile"<?php if($hpot == "profile") { echo " selected"; } ?>>profile</option>
            <option value="restaurant.restaurant"<?php if($hpot == "restaurant.restaurant") { echo " selected"; } ?>>restaurant.restaurant</option>
            <option value="video.episode"<?php if($hpot == "video.episode") { echo " selected"; } ?>>video.episode</option>
            <option value="video.movie"<?php if($hpot == "video.movie") { echo " selected"; } ?>>video.movie</option>
            <option value="video.other"<?php if($hpot == "video.other") { echo " selected"; } ?>>video.other</option>
            <option value="video.tv_show"<?php if($hpot == "video.tv_show") { echo " selected"; } ?>>video.tv_show</option>
            <option value="website"<?php if($hpot == "website") { echo " selected"; } ?>>website</option>
		</select>

		<?php $fb_object_types_URL = 'https://developers.facebook.com/docs/reference/opengraph'; ?>
		<p><?php echo sprintf( __( 'Learn about Object Types <a href="%1$s" target="_blank">here</a>.', 'facebook-thumb-fixer' ), $fb_object_types_URL); ?></p>
    
		<?php 
			$fbt_value = get_option( 'default_fb_thumb'); 
			list($width, $height) = @getimagesize($fbt_value);
			if($fbt_value && ($width >= 600 || $height >= 315)) { ?>
			<?php include(locate_template(plugin_basename( __FILE__ ))) . 'home-preview.php'; ?>
        <?php } else {  ?>
			<p class="howto"><?php _e( '<strong>Note: </strong>If no selection is made, the Object Type for your home page will be "website".', 'facebook-thumb-fixer' ); ?></p>
		<?php } ?>

<?php }
}

// Add metabox for Object Types
class ftf_otmeta {

    var $plugin_dir;
    var $plugin_url;

    function  __construct() {

        add_action( 'add_meta_boxes', array( $this, 'ftf_open_type_post_meta_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'ftf_open_type_page_meta_box' ) );
        add_action( 'save_post', array($this, 'save_data') );
    }

	// Add the meta box to the POSTS sidebar
    function ftf_open_type_post_meta_box(){
        add_meta_box(
             'object_type'
            ,'Facebook Thumb Fixer'
            ,array( &$this, 'meta_box_content' )
            ,'post'
            ,'side'
            ,'default'
        );
    }

	// Add the meta box to the PAGES sidebar
    function ftf_open_type_page_meta_box(){
        add_meta_box(
             'object_type'
            ,'Facebook Thumb Fixer'
            ,array( &$this, 'meta_box_content' )
            ,'page'
            ,'side'
            ,'default'
        );
    }

    function meta_box_content(){
        global $post;
        // Use nonce for verification
        wp_nonce_field( plugin_basename( __FILE__ ), 'ftf_open_type__nounce' ); ?>

        <?php
			$ot  	= get_post_meta($post->ID, "ftf_open_type", TRUE);
			$dog 	= get_post_meta($post->ID, "disable_open_graph", TRUE);
			$current_screen = get_current_screen();

			if ($current_screen ->id === 'post') {
				$post_type_label = 'post';
			} else if ($current_screen ->id === 'page') {
				$post_type_label = 'page';
			} else {
				$post_type_label = '';
			}
		?>
		<p><strong>Object Type</strong></p>
		<select name="ftf_open_type_field" style="width:100%;">
        	<option></option>
            <option value="article"<?php if($ot == "article") { echo " selected"; } ?>>article</option>
            <option value="book"<?php if($ot == "book") { echo " selected"; } ?>>book</option>
            <option value="books.author"<?php if($ot == "books.author") { echo " selected"; } ?>>books.author</option>
			<option value="business.business"<?php if($ot == "business.business") { echo " selected"; } ?>>business.business</option>
            <option value="fitness.course"<?php if($ot == "fitness.course") { echo " selected"; } ?>>fitness.course</option>
            <option value="fitness.unit"<?php if($ot == "fitness.unit") { echo " selected"; } ?>>fitness.unit</option>
            <option value="music.album"<?php if($ot == "music.album") { echo " selected"; } ?>>music.album</option>
            <option value="music.playlist"<?php if($ot == "music.playlist") { echo " selected"; } ?>>music.playlist</option>
            <option value="music.radio_station"<?php if($ot == "music.radio_station") { echo " selected"; } ?>>music.radio_station</option>
            <option value="music.song"<?php if($ot == "music.song") { echo " selected"; } ?>>music.song</option>
            <option value="place"<?php if($ot == "place") { echo " selected"; } ?>>place</option>
            <option value="product"<?php if($ot == "product") { echo " selected"; } ?>>product</option>
            <option value="product.group"<?php if($ot == "product.group") { echo " selected"; } ?>>product.group</option>
            <option value="profile"<?php if($ot == "profile") { echo " selected"; } ?>>profile</option>
            <option value="restaurant.restaurant"<?php if($ot == "restaurant.restaurant") { echo " selected"; } ?>>restaurant.restaurant</option>
            <option value="video.episode"<?php if($ot == "video.episode") { echo " selected"; } ?>>video.episode</option>
            <option value="video.movie"<?php if($ot == "video.movie") { echo " selected"; } ?>>video.movie</option>
            <option value="video.other"<?php if($ot == "video.other") { echo " selected"; } ?>>video.other</option>
            <option value="video.tv_show"<?php if($ot == "video.tv_show") { echo " selected"; } ?>>video.tv_show</option>
            <option value="website"<?php if($ot == "website") { echo " selected"; } ?>>website</option>
		</select>

		<?php $fb_object_types_URL = 'https://developers.facebook.com/docs/reference/opengraph/'; ?>
		<p><?php echo sprintf( __( 'If no selection is made, the Object Type for this %1$s will be "article". Learn about Object Types <a href="%2$s" target="_blank">here</a>.', 'facebook-thumb-fixer' ), $post_type_label, $fb_object_types_URL); ?></p>
	
	
		<div class="preview-container <?php if($dog == "1") { echo 'hide'; } ?>">
		<?php include(locate_template(plugin_basename( __FILE__ ))) . 'post-preview.php'; ?>
		</div>

		<p class="disabled-container <?php if($dog !== "1") { echo 'hide'; } ?>"><?php _e( 'Preview and debugging are not possible when open graph tags are disabled.', 'facebook-thumb-fixer' ); ?></p>

		<p>
			<input type="checkbox" name="disable_open_graph" class="disable_open_graph" value="1" <?php if($dog == "1") { echo " checked"; } ?>>
			<label for="disable_open_graph"><?php echo sprintf( __( "Disable for this %s", "facebook-thumb-fixer" ), $post_type_label); ?></label>
		</p>

		<script type="text/javascript">
			jQuery(".disable_open_graph").click(function() {
				if(jQuery(this).is(":checked")) {
					jQuery(".preview-container").fadeOut();
					jQuery(".disabled-container").fadeIn();
				} else {
					jQuery(".preview-container").fadeIn();
					jQuery(".disabled-container").fadeOut();
				}
			});
		</script>

<?php  }


    function save_data($post_id) {
		
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( !wp_verify_nonce( $_POST['ftf_open_type__nounce'], plugin_basename( __FILE__ ) ) )
            return;

        // Check permissions
        if ( 'page' == $_POST['post_type'] ){
            if ( !current_user_can( 'edit_page', $post_id ) )
                return;
        } else {
            if ( !current_user_can( 'edit_post', $post_id ) )
                return;
        }
		
        $ftf_open_type_field_data = $_POST['ftf_open_type_field'];
        update_post_meta($post_id, 'ftf_open_type', $ftf_open_type_field_data, $ot);
        //return $ftf_open_type_field_data;

		$disable_open_graph_data = $_POST['disable_open_graph'];
		update_post_meta($post_id, 'disable_open_graph', $disable_open_graph_data, $dog);
		//return $disable_open_graph_data;
    }

}
$ftf_otmeta = new ftf_otmeta;

// Add page into the SETTINGS menu
add_action( 'admin_menu', 'ftfixer_menu' );
function ftfixer_menu() {
	$icon_path = plugins_url('images/', __FILE__ ) . 'facebook-admin.png';
	add_menu_page( __( 'FB Thumb Fixer' ), __( 'FB Thumb Fixer' ), 'manage_options', 'facebook-thumb-fixer', 'myfbft_plugin_options' ,$icon_path);
}
function myfbft_plugin_options() {
	if ( !current_user_can( 'read' ) )  { // This help page is accessible to anyone
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'facebook-thumb-fixer' ) );
} ?>

<a href="https://taskrocket.info/?source=ftf" target="_blank" class="task-rocket">Try</a></p>

<div class="wrap ftf-wrap">
    <h2>Facebook Thumb Fixer</h2>
	<?php
    $fbt_value = get_option( 'default_fb_thumb');
    if ($fbt_value) {
	list($width, $height) = @getimagesize($fbt_value); ?>

	<?php $settings_URL = get_admin_url() . 'options-general.php#dfb'; ?>
	<p class="ftf-good"><?php echo sprintf( __( 'Well done! You have a default Facebook thumbnail set. You can change it any time <a href="%1$s">here</a>.', 'facebook-thumb-fixer' ), $settings_URL); ?></p>
    
	<h2><?php _e( 'Homepage Preview', 'facebook-thumb-fixer' ); ?></h2>
	<p><?php _e( 'This is an approximate preview of your homepage when shared on Facebook:', 'facebook-thumb-fixer' ); ?></p>

	<div class="ftf-live-home-preview">
		<img src="<?php echo plugins_url('images/', __FILE__ ) . 'preview-top.png'; ?>" />

		<div class="ftf-preview-details">

			<div class="overflow home-thumb-image">
				<img src="<?php if($fbt_value) { esc_attr_e( $fbt_value ); } ?>" />
			</div>

			<h1><?php echo get_bloginfo( 'name' ); ?></h1>

			<p>
				<?php
					$description = get_bloginfo( 'description' );
					if ( $description ) {
						$excerpt_chars = substr($description, 0, 150);
						echo strip_tags($excerpt_chars);
					}
				?>
			</p>
			<span class="ftf-domain"><?php echo $_SERVER['SERVER_NAME']; ?></span>
		</div>
	</div>
	
	<?php 
		$fbt_value 			= get_option( 'default_fb_thumb');
		$fb_URL 	  		= 'https://developers.facebook.com/docs/sharing/best-practices#images';
		$home_image_ID 		= attachment_url_to_postid( $fbt_value ); 									// Get the ID of the default image
		$image_attributes	= wp_get_attachment_image_src( $attachment_id = $home_image_ID, 'full' ); 	// Get the image attributes of the default image
		$width				= $image_attributes[1];														// Get the width
		$height				= $image_attributes[2];														// Get the height
	?>
	<p class="description">
		<?php echo sprintf( __( '<strong>Note: </strong>Facebook <a href="%1$s" target="_blank">recommends</a> your image be 1200 x 630 or 600 x 315.', 'facebook-thumb-fixer' ), $fb_URL); ?>
		<?php if ($width >= 600 && $height >= 315) { ?>
		<?php echo sprintf( __( 'Your image (shown here scaled down) appears to be good at %1$s x %2$s.', 'facebook-thumb-fixer' ), $width, $height); ?>
		<?php } ?>
	</p>

	<?php
	if ($width < 600 || $height < 315) { ?>
		<p class="ftf-warning">
			<?php echo sprintf( __('<strong>Oops! </strong>Although you do have a default Facebook thumbnail, the dimensions are smaller than the minimum 600 x 315 <a href="%1$s" target="_blank">recommended</a> by Facebook.', 'facebook-thumb-fixer' ), $fb_URL); ?>
		</p>
	<?php } 
	} else { 
		$settings_URL = get_admin_url() . 'options-general.php#dfb';
	?>

		<p class="ftf-bad"><?php echo sprintf( __( 'You currently do not have a Default Facebook Thumbnail set. Set one <a href="%1$s">here</a>.', 'facebook-thumb-fixer' ), $settings_URL); ?></p>

    <?php } ?>

    <script>
	// Toggle support answers
		jQuery(function($) {
		//hide the all of these elements
		$(".help-answer").hide();
		//toggle the componenet with the H2
		$(".topic").click(function(){
			$(this).next(".help-answer").slideToggle(150);
		});

		$(document).ready(function () {
			$('.topic').click(function () {
				$(this).toggleClass('open-help');
			});
		});

	});
    </script>
    <h3><?php _e( 'Where can I get support?', 'facebook-thumb-fixer' ); ?></h3>
	<?php $support_URL = 'https://wordpress.org/support/plugin/facebook-thumb-fixer'; ?>
	<p><?php echo sprintf( __( 'Reach for support at the <a href="%1$s" target="_blank">Wordpress plug-in repo</a>.', 'facebook-thumb-fixer' ), $support_URL) ?></p>

</div>

<?php }
add_action('wp_head', 'fbfixhead');
function fbfixhead() {

	 // Required for is_plugin_active to work.
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	 // If BuddyPress is active
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {			

		// If not on a BuddyPress members page
		if (!bp_current_component('members')) {
			require('output-logic.php');
		}
		
  	} 
	
	// Otherwie, if BuddyPress is NOT active...
	else if ( !is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		require('output-logic.php');
	}
		
	echo $ftf_head;
	print "\n";
	$fbaid_value = get_option('fb_app_ID');
	if (!empty($fbaid_value)) { ?>
	<meta property="fb:app_id" content="<?php echo get_option('fb_app_ID'); ?>" />
	<?php }
	print "\n";
}