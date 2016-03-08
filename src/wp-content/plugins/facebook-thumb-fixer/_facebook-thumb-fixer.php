<?php
/*
Plugin Name: Facebook Thumb Fixer
Plugin URI: https://wordpress.org/support/plugin/facebook-thumb-fixer
Description: Fixes the problem of the missing (or wrong) thumbnail when a post is shared on Facebook and Google+.
Author: Michael Ott
Version: 1.5.1
Author URI: http://michaelott.id.au
*/

// Additional contribution by MutebiRoy for Google+ full bleed image support (http://profiles.wordpress.org/mutebiroy/)

// Add HELP link from the plugin page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'link_action_on_plugin' );
function link_action_on_plugin( $links ) {
	return array_merge(array('settings' => '<a href="' . admin_url( '/options-general.php' ) . '">' . __( 'Settings', 'domain' ) . '</a> | <a href="' . admin_url( '/options-general.php?page=facebook-thumb-fixer' ) . '">' . __( 'Help', 'domain' ) . '</a>'), $links);
}

// Include custom CSS
function admin_load_fbf_css(){
	wp_enqueue_style('stylsheet', plugins_url( '/css/ftf.css', __FILE__ ) );
	add_thickbox();
}
add_action('admin_enqueue_scripts', 'admin_load_fbf_css');

// Add field into the general settings page
$general_setting_default_fb_thumb = new general_setting_default_fb_thumb();

class general_setting_default_fb_thumb {
    function general_setting_default_fb_thumb( ) {
        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
    }
    function register_fields() {
        register_setting( 'general', 'default_fb_thumb', 'esc_attr' );
        add_settings_field('fav_color', '<label for="default_fb_thumb" id="dfb">'.__('Default Facebook Thumb' , 'default_fb_thumb' ).'</label>' , array(&$this, 'fields_html') , 'general' );
    }
    function fields_html() {
        $fbt_value = get_option( 'default_fb_thumb', '' );
		if ($fbt_value) { ?>

        <input type="text" id="default_fb_thumb" class="regular-text ltr" name="default_fb_thumb" value="<?php echo $fbt_value; ?>" />

		<?php } else { ?>

		<input type="text" id="default_fb_thumb" class="regular-text no-fb-thumb ltr" name="default_fb_thumb" value="<?php echo $fbt_value; ?>" />

		<?php } ?>

		<?php if ($fbt_value) {
		echo '<span class="ftf-tick">&#10003;</span>';
		}
		echo '<p class="description">This is the full path to your default thumb. Facebook <a href="https://developers.facebook.com/docs/sharing/best-practices#images" target="_blank">recommends</a> your image be 1200x630 or 600x315. View help topics <a href="' . admin_url( '/options-general.php?page=facebook-thumb-fixer' ) . '">here</a>.</p>';
		if ($fbt_value) {
		echo '<a href="' . $fbt_value . '?TB_iframe=true&width=600&height=550" class="thickbox" title="Your default open graph image is ' . $width . ' x ' . $height . ' pixels."><img src="' . $fbt_value . '" title="Default Facebook Thumb" class="thickbox ftf-preview" /></a>';
		echo '<p class="description">Note: The image shown above is scaled down. The real dimensions are actually ';
		list($width, $height) = @getimagesize($fbt_value); echo $width . ' x ' . $height . '.</p>';
		if ($image_width < 600 || $image_height < 315) {
			echo '<p class="ftf-warning"><strong>WARNING:</strong> Your default Facebook thumbnail image dimensions are smaller than the minimum 600 x 315 <a href="https://developers.facebook.com/docs/sharing/best-practices#images" target="_blank">recommended</a> by Facebook.</p>';
		}
		}
    }
}


// Add object type selection into the general settings page
$general_setting_object_type = new general_setting_object_type();

class general_setting_object_type {
    function general_setting_object_type( ) {
        add_filter( 'admin_init' , array( &$this , 'register_object_type' ) );
    }
    function register_object_type() {
        register_setting( 'general', 'homepage_object_type', 'esc_attr' );
        add_settings_field('object_type', '<label for="homepage_object_type">'.__('Homepage Object Type' , 'homepage_object_type' ).'</label>' , array(&$this, 'ot_fields_html') , 'general' );
    }
    function ot_fields_html() { ?>

        <?php	// Object Types
				// TODO: additional fields for specific object types (commented out below).
		?>
		<select value="homepage_object_type" name="homepage_object_type"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "") { echo " class='no-object-type'"; } ?>>
        	<option></option>
            <option value="article"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "article") { echo " selected"; } ?>>article</option>
            <option value="book"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "book") { echo " selected"; } ?>>book</option>
            <option value="books.author"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "books.author") { echo " selected"; } ?>>books.author</option>
            <!--option value="books.book"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "books.book") { echo " selected"; } ?>>books.book</option-->
            <!--option value="books.genre"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "books.genre") { echo " selected"; } ?>>books.genre</option-->
            <!--option value="business.business"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "business.business") { echo " selected"; } ?>>business.business</option-->
            <option value="fitness.course"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "fitness.course") { echo " selected"; } ?>>fitness.course</option>
            <option value="fitness.unit"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "fitness.unit") { echo " selected"; } ?>>fitness.unit</option>
            <option value="music.album"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "music.album") { echo " selected"; } ?>>music.album</option>
            <option value="music.playlist"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "music.playlist") { echo " selected"; } ?>>music.playlist</option>
            <option value="music.radio_station"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "music.radio_station") { echo " selected"; } ?>>music.radio_station</option>
            <option value="music.song"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "music.song") { echo " selected"; } ?>>music.song</option>
            <option value="object"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "object") { echo " selected"; } ?>>object</option>
            <!--option value="place"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "place") { echo " selected"; } ?>>place</option-->
            <option value="product"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "product") { echo " selected"; } ?>>product</option>
            <option value="product.group"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "product.group") { echo " selected"; } ?>>product.group</option>
            <!--option value="product.item"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "product.item") { echo " selected"; } ?>>product.item</option-->
            <option value="profile"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "profile") { echo " selected"; } ?>>profile</option>
            <!--option value="restaurant.menu"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "restaurant.menu") { echo " selected"; } ?>>restaurant.menu</option-->
            <!--option value="restaurant.menu_item"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "restaurant.menu_item") { echo " selected"; } ?>>restaurant.menu_item</option-->
            <!--option value="restaurant.menu_section"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "restaurant.menu_section") { echo " selected"; } ?>>restaurant.menu_section</option-->
            <option value="restaurant.restaurant"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "restaurant.restaurant") { echo " selected"; } ?>>restaurant.restaurant</option>
            <option value="video.episode"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "video.episode") { echo " selected"; } ?>>video.episode</option>
            <option value="video.movie"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "video.movie") { echo " selected"; } ?>>video.movie</option>
            <option value="video.other"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "video.other") { echo " selected"; } ?>>video.other</option>
            <option value="video.tv_show"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "video.tv_show") { echo " selected"; } ?>>video.tv_show</option>
            <option value="website"<?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "website") { echo " selected"; } ?>>website</option>
		</select>
        <p>Learn about Object Types <a href="https://developers.facebook.com/docs/reference/opengraph" target="_blank">here</a>.</p>
        <?php $hpot = get_option( 'homepage_object_type', ''); if($hpot == "") { ?>
        	<p class="howto"><strong>Note:</strong> When no selction is made, the Object Type for your home page will be 'website'.</p>

			<?php require("inc-preview.php"); ?>

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

        <?php	// Object Types
				// TODO: additional fields for specific object types (commented out below).
		?>
		<select value="ftf_open_type_field" name="ftf_open_type_field" style="width:100%;">
        	<option></option>
            <option value="article"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "article") { echo " selected"; } ?>>article</option>
            <option value="book"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "book") { echo " selected"; } ?>>book</option>
            <option value="books.author"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "books.author") { echo " selected"; } ?>>books.author</option>
            <!--option value="books.book"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "books.book") { echo " selected"; } ?>>books.book</option-->
            <!--option value="books.genre"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "books.genre") { echo " selected"; } ?>>books.genre</option-->
            <!--option value="business.business"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "business.business") { echo " selected"; } ?>>business.business</option-->
            <option value="fitness.course"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "fitness.course") { echo " selected"; } ?>>fitness.course</option>
            <option value="fitness.unit"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "fitness.unit") { echo " selected"; } ?>>fitness.unit</option>
            <option value="music.album"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "music.album") { echo " selected"; } ?>>music.album</option>
            <option value="music.playlist"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "music.playlist") { echo " selected"; } ?>>music.playlist</option>
            <option value="music.radio_station"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "music.radio_station") { echo " selected"; } ?>>music.radio_station</option>
            <option value="music.song"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "music.song") { echo " selected"; } ?>>music.song</option>
            <option value="object"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "object") { echo " selected"; } ?>>object</option>
            <!--option value="place"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "place") { echo " selected"; } ?>>place</option-->
            <option value="product"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "product") { echo " selected"; } ?>>product</option>
            <option value="product.group"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "product.group") { echo " selected"; } ?>>product.group</option>
            <!--option value="product.item"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "product.item") { echo " selected"; } ?>>product.item</option-->
            <option value="profile"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "profile") { echo " selected"; } ?>>profile</option>
            <!--option value="restaurant.menu"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "restaurant.menu") { echo " selected"; } ?>>restaurant.menu</option-->
            <!--option value="restaurant.menu_item"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "restaurant.menu_item") { echo " selected"; } ?>>restaurant.menu_item</option-->
            <!--option value="restaurant.menu_section"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "restaurant.menu_section") { echo " selected"; } ?>>restaurant.menu_section</option-->
            <option value="restaurant.restaurant"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "restaurant.restaurant") { echo " selected"; } ?>>restaurant.restaurant</option>
            <option value="video.episode"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "video.episode") { echo " selected"; } ?>>video.episode</option>
            <option value="video.movie"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "video.movie") { echo " selected"; } ?>>video.movie</option>
            <option value="video.other"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "video.other") { echo " selected"; } ?>>video.other</option>
            <option value="video.tv_show"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "video.tv_show") { echo " selected"; } ?>>video.tv_show</option>
            <option value="website"<?php $ot = get_post_meta($post->ID, "ftf_open_type", TRUE); if($ot == "website") { echo " selected"; } ?>>website</option>
		</select>
        <p>Learn about Object Types <a href="https://developers.facebook.com/docs/reference/opengraph" target="_blank">here</a>.</p>
        <p class="howto"><strong>Note: </strong>If no selction is made, the Object Type for this post/page will be 'article'.</p>

		<?php require("inc-preview.php"); ?>

<?php  }


    function save_data($post_id){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( !wp_verify_nonce( $_POST['ftf_open_type__nounce'], plugin_basename( __FILE__ ) ) )
            return;

        // Check permissions
        if ( 'page' == $_POST['post_type'] ){
            if ( !current_user_can( 'edit_page', $post_id ) )
                return;
        }else{
            if ( !current_user_can( 'edit_post', $post_id ) )
                return;
        }
        $data = $_POST['ftf_open_type_field'];
        update_post_meta($post_id, 'ftf_open_type', $data, get_post_meta($post_id, 'ftf_open_type', TRUE));
        return $data;
    }
}
$ftf_otmeta = new ftf_otmeta;


// Add page into the SETTINGS menu
add_action( 'admin_menu', 'ftfixer_menu' );
function ftfixer_menu() {
	$icon_path = get_option('siteurl').'/wp-content/plugins/'.basename(dirname(__FILE__)).'/images/facebook-admin.png';
	add_menu_page( __( 'Facebook Thumb' ), __( 'Facebook Thumb' ), 'manage_options', 'facebook-thumb-fixer', 'myfbft_plugin_options' ,$icon_path);
}
function myfbft_plugin_options() {
	if ( !current_user_can( 'read' ) )  { // This help page is accessible to anyone
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
} ?>

<div class="task-rocket">
    <h3>Introducing Task Rocket</h3>
    <p style="color:#fff; text-align:center;">Task Rocket is a simple front-end task management tool built on Wordpress.</p>
    <p><a href="http://taskrocket.info/" target="_blank">Take it for a test flight</a></p>
</div>

<div class="wrap ftf-wrap">
    <h2>Facebook Thumb Fixer</h2>
	<?php
    $fbt_value = get_option( 'default_fb_thumb', '' );
    if ($fbt_value) {
	list($width, $height) = @getimagesize($fbt_value); ?>
    <p class="ftf-good">Well done! You have a default Facebook thumbnail set. This will be used when a page or post you share doesn't already have a featured image. You can change the default image <a href="<?php echo get_admin_url(); ?>/options-general.php#dfb">here</a>.</p>
    <a href="<?php echo $fbt_value; ?>?TB_iframe=true&width=600&height=550" class="thickbox" title="Your default open graph image is <?php echo $width . " x " . $height; ?> pixels.">
    <img src="<?php echo $fbt_value; ?>" alt="" class="ftf-preview thickbox" /></a>
    <p class="description">Note: Facebook <a href="https://developers.facebook.com/docs/sharing/best-practices#images" target="_blank">recommends</a> your image be 1200x630 or 600x315. Your image (show here scaled down) is <?php echo $width . " x " . $height; ?>.</p>
	<?php
	if ($image_width < 600 || $image_height < 315) {
		echo '<p class="ftf-warning"><strong>WARNING:</strong> Although you do have a default Facebook thumbnail, the dimensions are smaller than the minimum 600 x 315 <a href="https://developers.facebook.com/docs/sharing/best-practices#images" target="_blank">recommended</a> by Facebook.</p>';
	}
	?>
    <?php } else { ?>
    <p class="ftf-bad">You currently do not have a Default Facebook Thumbnail set. Set one <a href="<?php echo get_admin_url(); ?>/options-general.php#dfb">here</a>. Facebook <a href="https://developers.facebook.com/docs/sharing/best-practices#images" target="_blank">recommends</a> your image be 1200x630 or 600x315.</p>
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
    <div class="ftf-help">
    <h3 class="ftf-header">Help Topics</h3>
    	<p class="topic">What does this plug-in do?</p>
        <div class="help-answer">
        	<p>This plug-in will place the appropriate  <a href="http://ogp.me/" target="_blank">Open Graph</a> meta properties into the &lt;head&gt; of your web pages, so that when someone links to your page on Facebook (or any other service that utilises the Open Graph protocol) the correct thumbnail and other information will show.</p>
            <p>The thumbnail is derived from the featured image of your post (or page).</p>
        	<p>If your post does not have a featured image, then the default thumbnail will take over.</p>
        	<p>If someone links to your home page (which traditionally doesn't have a featured image) then the default image is used.</p>
        </div>

        <p class="topic">How does it work?</p>
        <div class="help-answer">
        	<p>Whenever anyone posts a website link to Facebook, the Facebook system searches the source code for the <a href="https://developers.facebook.com/docs/concepts/opengraph/" target="_blank">Open Graph</a>  meta properties. If they are not found, then it will instead pull (several) images (if any) contained within the web page. If there are multiple images pulled then the user can select one of the many presented (though the one they choose might not be the one you are happy to have as the primary image shown on Facebook, hence this plug-in).</p>
    		<p>This plug-in gets around that problem but taking  that choice away from the Facebook user, so only the thumbnail you want is displayed. This is also good in a situation where - for example - many different Facebook users share your web page,  you can trust the same thumbnail will always be used (the thumbnail might be of your brand for example).</p>
        </div>

        <p class="topic">How do I specify Object Types?</p>
        <div class="help-answer">
        	<h4>Posts and Pages</h4>
            <p>On each page or post you edit there is an 'Open Graph Object Type' meta box. Simply make a section from there to specify what Object Type the page or post is. Example: If the it's an article, then choose 'article'. If it's a product, choose 'product'. To help you decide what Object Type to choose, go <a href="https://developers.facebook.com/docs/reference/opengraph" target="_blank">here</a> to learn the differences between them all.</p>
            <p><strong>Note: </strong>If no selection is made for posts or pages then the Object Type will be 'article'.</p>
            <h4>Homepage</h4>
            <p>To specify what Object Type your homepage is, go to the Wordpress<strong> Settings -&gt; General</strong> page <a href="<?php echo get_admin_url(); ?>options-general.php">here</a> and make a selection from the 'Homepage Object Type' field.</p>
            <p><strong>Note: </strong>If no selection is made for the homepage then the Object Type will be 'webpage'.</p>
        </div>

        <p class="topic">How do I customise the description?</p>
        <div class="help-answer">
            <p>By default the description is derived from the content, but if you want to customise it then simply add content into the excerpt field.</p>
        </div>

        <p class="topic">How can I test a post/page without sharing it on Facebook first?</p>
        <div class="help-answer">
            <p>Run the URL through the <a href='http://developers.facebook.com/tools/debug' target='_blank'>Facebook debugger</a> tool and examine the information that is returned.</p>
        </div>

        <p class="topic">What if you don't use Featured Images on your pages or posts?</p>
        <div class="help-answer">
        	<p>If you don't have featured images attached to your posts, then you can still use this plug-in just to show a default thumbnail on Facebook (as opposed to no thumbnail). This plug-in has been engineered so that if you don't use a featured image on posts then a default thumbnail is used instead. You can set a <strong>Default Facebook Thumb</strong> in the Wordpress<strong> Settings -&gt; General</strong> page <a href="<?php echo get_admin_url(); ?>options-general.php">here</a>.</p>
        </div>

        <p class="topic">Conflicts with other plug-ins</p>
        <div class="help-answer">
            <p>Any other plug-in that inserts the open graph properties into the &lt;head&gt; of your website may cause a conflict and failure to work properly.</p>
            <p>To test if you have a conflict, simply view the source code of your home page and search for any instances of <strong>og:</strong> within. Typically a plug-in will output the meta tags into it's own group. For this plug-in, they will be directly below the comment <strong>&lt;!--/ Facebook Thumb Fixer Open Graph /--&gt;</strong> but other plug-ins will output differently.</p>
            <p>The only solution to resolve a conflict is to disable one of the plug-ins.</p>
        </div>

        <p class="topic">What if some posts use featured images and some don't?</p>
        <div class="help-answer">
            <p>The <strong>Default Facebook Thumb</strong> is used  to fall back on in the event your post doesn't have a featured image. Ideally the <strong>Default Facebook Thumb</strong> image would be of your logo or brand. You can set a <strong>Default Facebook Thumb</strong> in the Wordpress <strong>Settings -&gt; General</strong> page <a href="<?php echo get_admin_url(); ?>options-general.php">here</a>.</p>
        </div>

        <p class="topic">Occasionally it doesn't work. What's going on?</p>
        <div class="help-answer">
            <p>Blame Facebook for that. Even though their system searches for the og:image meta property, for different reasons (outside the control of this plug-in) sometimes  the thumbnail doesn't load on the Facebook post.</p>
            <p>But don't fret, it's easy to get around. Using the official <a href='http://developers.facebook.com/tools/debug' target='_blank'>Facebook debugger</a> tool, paste in the URL of your post, hit the <strong>Debug</strong> button and see if the image thumb loads (scroll down to <strong>Object Properties</strong>). If it doesn't try hitting the <strong>Fetch new scrape information</strong> button.</p>
        </div>

        <p class="topic">The settings page isn't showing the dimensions of my image. What's up with that?</p>
        <div class="help-answer">
            <p>This is extremely rare, and won't prevent the plug-in from doing it's job. But the most likely cause is a server side setting in php.ini that needs to be changed: <code>allow_url_fopen = On</code></p>
        </div>

        <p class="topic">Where can I get support and discuss this plug-in?</p>
        <div class="help-answer">
        	<p>There are two places to discuss this plug-in and reach out to me for support: <a href="http://www.thatwebguyblog.com/post/facebook-thumb-fixer-for-wordpress/" target="_blank">my blog</a> or on the <a href="https://wordpress.org/support/plugin/facebook-thumb-fixer">Wordpress plug-in page</a>.</p>
        </div>

    </div>

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

			 // If not the homepage
			if ( !is_home() ) {

				// If there is a post image...
				if (has_post_thumbnail()) {
				// Set '$featuredimg' variable for the featured image.
				$featuredimg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), "Full");
				$ftf_description = get_the_excerpt();
				global $post;
				$ot = get_post_meta($post->ID, 'ftf_open_type', true);
				if($ot == "") { $default = "article"; } else $default = get_post_meta($post->ID, 'ftf_open_type', true);
				$ftf_head = '
				<!--/ Facebook Thumb Fixer Open Graph /-->
				<meta property="og:type" content="'. $default . '" />
				<meta property="og:url" content="' . get_permalink() . '" />
				<meta property="og:title" content="' . wp_kses_data(get_the_title($post->ID)) . '" />
				<meta property="og:description" content="' . wp_kses($ftf_description, array ()) . '" />
				<meta property="og:site_name" content="' . wp_kses_data(get_bloginfo('name')) . '" />
				<meta property="og:image" content="' . $featuredimg[0] . '" />

				<meta itemscope itemtype="'. $default . '" />
				<meta itemprop="description" content="' . wp_kses($ftf_description, array ()) . '" />
				<meta itemprop="image" content="' . $featuredimg[0] . '" />
				';
				} //...otherwise, if there is no post image.
				else {
				$ftf_description = get_the_excerpt();
				global $post;
				$ot = get_post_meta($post->ID, 'ftf_open_type', true);
				if($ot == "") { $default = "article"; } else $default = get_post_meta($post->ID, 'ftf_open_type', true);
				$ftf_head = '
				<!--/ Facebook Thumb Fixer Open Graph /-->
				<meta property="og:type" content="'. $default . '" />
				<meta property="og:url" content="' . get_permalink() . '" />
				<meta property="og:title" content="' . wp_kses_data(get_the_title($post->ID)) . '" />
				<meta property="og:description" content="' . wp_kses($ftf_description, array ()) . '" />
				<meta property="og:site_name" content="' . wp_kses_data(get_bloginfo('name')) . '" />
				<meta property="og:image" content="' . get_option('default_fb_thumb') . '" />

				<meta itemscope itemtype="'. $default . '" />
				<meta itemprop="description" content="' . wp_kses($ftf_description, array ()) . '" />
				<meta itemprop="image" content="' . get_option('default_fb_thumb') . '" />
				';
				}
				} //...otherwise, it must be the homepage so do this:
				else {
				$ftf_name = get_bloginfo('name');
				$ftf_description = get_bloginfo('description');
				$ot = get_option( 'homepage_object_type', '');
				if($ot == "") { $default = "website"; } else $default = get_option( 'homepage_object_type', '');
				$ftf_head = '
				<!--/ Facebook Thumb Fixer Open Graph /-->
				<meta property="og:type" content="' . $default . '" />
				<meta property="og:url" content="' . get_option('home') . '" />
				<meta property="og:title" content="' . wp_kses($ftf_name, array ()) . '" />
				<meta property="og:description" content="' . wp_kses_data($ftf_description, array ()) . '" />
				<meta property="og:site_name" content="' . wp_kses($ftf_name, array ()) . '" />
				<meta property="og:image" content="' . get_option('default_fb_thumb') . '" />

				<meta itemscope itemtype="'. $default . '" />
				<meta itemprop="description" content="' . wp_kses($ftf_description, array ()) . '" />
				<meta itemprop="image" content="' . get_option('default_fb_thumb') . '" />
				';
			}
		}
  	} // Otherwie, if BuddyPress is NOT active...
	else if ( !is_plugin_active( 'buddypress/bp-loader.php' ) ) {

		// If not the homepage
		global $post;
		if ( !is_home() ) {

			// If there is a post image...
			if (has_post_thumbnail()) {
			// Set '$featuredimg' variable for the featured image.
			$featuredimg = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), "Full");
			$ftf_description = get_the_excerpt();
			global $post;
			$ot = get_post_meta($post->ID, 'ftf_open_type', true);
			if($ot == "") { $default = "article"; } else $default = get_post_meta($post->ID, 'ftf_open_type', true);
			$ftf_head = '
			<!--/ Facebook Thumb Fixer Open Graph /-->
			<meta property="og:type" content="'. $default . '" />
			<meta property="og:url" content="' . get_permalink() . '" />
			<meta property="og:title" content="' . wp_kses_data(get_the_title($post->ID)) . '" />
			<meta property="og:description" content="' . wp_kses($ftf_description, array ()) . '" />
			<meta property="og:site_name" content="' . wp_kses_data(get_bloginfo('name')) . '" />
			<meta property="og:image" content="' . $featuredimg[0] . '" />

			<meta itemscope itemtype="'. $default . '" />
			<meta itemprop="description" content="' . wp_kses($ftf_description, array ()) . '" />
			<meta itemprop="image" content="' . $featuredimg[0] . '" />
			';
			} //...otherwise, if there is no post image.
			else {
			$ftf_description = get_the_excerpt();
			global $post;
			$ot = get_post_meta($post->ID, 'ftf_open_type', true);
			if($ot == "") { $default = "article"; } else $default = get_post_meta($post->ID, 'ftf_open_type', true);
			$ftf_head = '
			<!--/ Facebook Thumb Fixer Open Graph /-->
			<meta property="og:type" content="'. $default . '" />
			<meta property="og:url" content="' . get_permalink() . '" />
			<meta property="og:title" content="' . wp_kses_data(get_the_title($post->ID)) . '" />
			<meta property="og:description" content="' . wp_kses($ftf_description, array ()) . '" />
			<meta property="og:site_name" content="' . wp_kses_data(get_bloginfo('name')) . '" />
			<meta property="og:image" content="' . get_option('default_fb_thumb') . '" />

			<meta itemscope itemtype="'. $default . '" />
			<meta itemprop="description" content="' . wp_kses($ftf_description, array ()) . '" />
			<meta itemprop="image" content="' . get_option('default_fb_thumb') . '" />
			';
			}
			} //...otherwise, it must be the homepage so do this:
			else {
			$ftf_name = get_bloginfo('name');
			$ftf_description = get_bloginfo('description');
			$ot = get_option( 'homepage_object_type', '');
			if($ot == "") { $default = "website"; } else $default = get_option( 'homepage_object_type', '');
			$ftf_head = '
			<!--/ Facebook Thumb Fixer Open Graph /-->
			<meta property="og:type" content="' . $default . '" />
			<meta property="og:url" content="' . get_option('home') . '" />
			<meta property="og:title" content="' . wp_kses($ftf_name, array ()) . '" />
			<meta property="og:description" content="' . wp_kses_data($ftf_description, array ()) . '" />
			<meta property="og:site_name" content="' . wp_kses($ftf_name, array ()) . '" />
			<meta property="og:image" content="' . get_option('default_fb_thumb') . '" />

			<meta itemscope itemtype="'. $default . '" />
			<meta itemprop="description" content="' . wp_kses($ftf_description, array ()) . '" />
			<meta itemprop="image" content="' . get_option('default_fb_thumb') . '" />
			';
		}
	}
  echo $ftf_head;
  print "\n";
}
