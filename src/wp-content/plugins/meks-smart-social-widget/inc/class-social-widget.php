<?php
/*-----------------------------------------------------------------------------------*/
/*	Social Widget Class
/*-----------------------------------------------------------------------------------*/

class MKS_Social_Widget extends WP_Widget {

	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'mks_social_widget', 'description' => __('Display your social icons with this widget', 'meks-smart-social-widget') );
		$control_ops = array( 'id_base' => 'mks_social_widget' );
		parent::__construct('mks_social_widget', __('Meks Social Widget', 'meks-smart-social-widget'), $widget_ops, $control_ops );
		
		add_action( 'wp_enqueue_scripts', array($this,'enqueue_scripts'));
		add_action( 'admin_enqueue_scripts', array($this,'enqueue_admin_scripts'));

		$this->defaults = array( 
			'title' => __('Follow Me', 'meks-smart-social-widget'),
			'content' => '',
			'style' => 'square',
			'size' => 48,
			'font_size' => 16,
			'target' => '_blank',
			'social' => array()
		);

		//Allow themes or plugins to modify default parameters
		$this->defaults = apply_filters('mks_social_widget_modify_defaults', $this->defaults);
		
	}

	function enqueue_scripts(){
 		wp_register_style( 'meks-social-widget', MKS_SOCIAL_WIDGET_URL.'css/style.css', false, MKS_SOCIAL_WIDGET_VER );
    	wp_enqueue_style( 'meks-social-widget' );
  	}
  
  	function enqueue_admin_scripts(){
		wp_enqueue_script( 'meks-social-widget-js', MKS_SOCIAL_WIDGET_URL.'js/main.js', array( 'jquery', 'jquery-ui-sortable' ), MKS_SOCIAL_WIDGET_VER );
		wp_enqueue_style( 'mks-social-widget-css', MKS_SOCIAL_WIDGET_URL . 'css/admin.css', false, MKS_SOCIAL_WIDGET_VER );
  	}
  
	function widget( $args, $instance ) {
		
		extract( $args );

		$instance = wp_parse_args( (array) $instance, $this->defaults );
		
		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;

		if ( !empty($title) ) {
			echo $before_title . $title . $after_title;
		}
		?>
		
		<?php if(!empty($instance['content'])) : ?>
			<?php echo wpautop($instance['content']);?>
		<?php endif; ?>
		
		<?php if(!empty($instance['social'])): ?>
		<?php 
			$size_style = 'style="width: '.esc_attr($instance['size']).'px; height: '.esc_attr($instance['size']).'px; font-size: '.esc_attr($instance['font_size']).'px;"'; 
			$target = 'target="'.esc_attr($instance['target']).'"';
		?>
			<ul class="mks_social_widget_ul">
		  	<?php foreach($instance['social'] as $item) : ?>
		  		<li><a href="<?php echo $item['url']; ?>" title="<?php echo esc_attr($this->get_social_title($item['icon'])); ?>" class="<?php echo esc_attr($item['icon'].'_ico soc_'.$instance['style']); ?>" <?php echo $target; ?> <?php echo $size_style; ?>><span><?php echo $item['icon']; ?></span></a></li>
		  	<?php endforeach; ?>
		  </ul>
		<?php endif; ?>


		<?php
		echo $after_widget;
	}
	

	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['content'] = $new_instance['content'];
		$instance['style'] = $new_instance['style'];
		$instance['size'] = absint($new_instance['size']);
		$instance['font_size'] = absint($new_instance['font_size']);
		$instance['target'] = $new_instance['target'];
		$instance['social'] = array();
		if(!empty($new_instance['social_icon'])){
			$protocols = wp_allowed_protocols();
			$protocols[] = 'skype'; //allow skype call protocol
			for($i=0; $i < (count($new_instance['social_icon']) - 1); $i++){
					$temp = array('icon' => $new_instance['social_icon'][$i], 'url' => esc_url($new_instance['social_url'][$i], $protocols));
					$instance['social'][] = $temp;
			}
		}
		return $instance;
	}

	function form( $instance ) {
		
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$social_links = $this->get_social();
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'meks-smart-social-widget'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'content' ); ?>"><?php _e('Introduction text (optional)', 'meks-smart-social-widget'); ?>:</label>
			<textarea id="<?php echo $this->get_field_id( 'content' ); ?>" rows="5" name="<?php echo $this->get_field_name( 'content' ); ?>" class="widefat"><?php echo $instance['content']; ?></textarea>
		</p>
		
		<p>
			<span class="mks-option-label mks-option-fl"><?php _e('Icon shape', 'meks-smart-social-widget'); ?>:</span><br/>
			<div class="mks-option-radio-wrapper">
				
			<label class="mks-option-radio"><input type="radio" name="<?php echo $this->get_field_name( 'style' ); ?>" value="square" <?php checked($instance['style'],'square'); ?>/><?php _e('Square', 'meks-smart-social-widget'); ?></label><br/>
			<label class="mks-option-radio"><input type="radio" name="<?php echo $this->get_field_name( 'style' ); ?>" value="circle" <?php checked($instance['style'],'circle'); ?>/><?php _e('Circle', 'meks-smart-social-widget'); ?></label><br/>
			<label class="mks-option-radio"><input type="radio" name="<?php echo $this->get_field_name( 'style' ); ?>" value="rounded" <?php checked($instance['style'],'rounded'); ?>/><?php _e('Rounded corners', 'meks-smart-social-widget'); ?></label>
			</div>
		</p>
		
		<p>
			<label class="mks-option-label" for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e('Icon size', 'meks-smart-social-widget'); ?>: </label>
			<input id="<?php echo $this->get_field_id( 'size' ); ?>" type="text" name="<?php echo $this->get_field_name( 'size' ); ?>" value="<?php echo absint($instance['size']); ?>" class="small-text" /> px
		</p>

		
		<p>
			<label class="mks-option-label" for="<?php echo $this->get_field_id( 'font_size' ); ?>"><?php _e('Icon font size', 'meks-smart-social-widget'); ?>: </label>
			<input id="<?php echo $this->get_field_id( 'font_size' ); ?>" type="text" name="<?php echo $this->get_field_name( 'font_size' ); ?>" value="<?php echo absint($instance['font_size']); ?>" class="small-text" /> px
		</p>

		<p>
			<label class="mks-option-label" for="<?php echo $this->get_field_id( 'target' ); ?>"><?php _e('Open links in', 'meks-smart-social-widget'); ?>: </label>
			<select id="<?php echo $this->get_field_id( 'target' ); ?>" name="<?php echo $this->get_field_name( 'target' ); ?>">
				<option value="_blank" <?php selected('_blank',$instance['target']); ?>><?php _e('New Window', 'meks-smart-social-widget'); ?></option>
				<option value="_self" <?php selected('_self',$instance['target']); ?>><?php _e('Same Window', 'meks-smart-social-widget'); ?></option>
			</select>
		</p>
		
		<h4 class="mks-icons-title"><?php _e('Icons', 'meks-smart-social-widget'); ?>:</h4>
		
		<ul class="mks_social_container mks-social-sortable">
		  <?php foreach($instance['social'] as $link) : ?>
			  <li>
			  	<?php $this->draw_social($this, $social_links, $link); ?>
			  </li>
			<?php endforeach; ?>
		</ul>
	  
		
		<p>
	  	<a href="#" class="mks_add_social button"><?php _e('Add Icon', 'meks-smart-social-widget'); ?></a>
	  </p>
	  
	  <div class="mks_social_clone" style="display:none">
			<?php $this->draw_social($this, $social_links); ?>
	  </div>
	  
		
		
	<?php
	}
	
	function draw_social($widget, $social_links, $selected = array('icon' => '', 'url' => '') ){ ?>

				<label class="mks-sw-icon"><?php _e('Icon', 'meks-smart-social-widget'); ?> :</label>
				<select type="text" name="<?php echo $widget->get_field_name('social_icon'); ?>[]" value="<?php echo $selected['icon']; ?>" style="width: 82%">
					<?php foreach($social_links as $key => $link) : ?>
						<option value="<?php echo $key; ?>" <?php selected($key,$selected['icon']); ?>><?php echo $link; ?></option>
					<?php endforeach; ?>
				</select>

				<label class="mks-sw-icon"><?php _e('Url', 'meks-smart-social-widget'); ?> :</label>
				<input type="text" name="<?php echo $widget->get_field_name('social_url'); ?>[]" value="<?php echo $selected['url']; ?>" style="width: 82%">

				
				<span class="mks-remove-social dashicons dashicons-no-alt"></span>
			
	<?php }


	protected function get_social_title( $social_name ) {
		$items = $this->get_social();
		return $items[$social_name];
	}
	
	function get_social() {
		$social = array(
			'aim' => 'Aim',
			'airbnb' => 'Airbnb',
			'amazon' => 'Amazon',
			'amplement' => 'Amplement',
			'android' => 'Android',
			'angellist' => 'Angellist',
			'apple' => 'Apple',
			'baidu' => 'Baidu',
			'bandcamp' => 'Bandcamp',
			'bebo' => 'Bebo',
			'behance' => 'Behance',
			'blogger' => 'Blogger',
			'buffer' => 'Buffer',
			'cargo' => 'Cargo',
			'coderwall' => 'Coderwall',
			'dailymotion' => 'Dailymotion',
			'deezer' => 'Deezer',
			'delicious' => 'Delicious',
			'deviantart' => 'Deviantart',
			'digg' => 'Digg',
			'disqus' => 'Disqus',
			'douban' => 'Douban',
			'draugiem' => 'Draugiem',
			'dribbble' => 'Dribbble',
			'ebay' => 'Ebay',
			'eight-tracks' => 'Eight Tracks',
			'ello' => 'Ello',
			'endomondo' => 'Endomondo',
			'envato' => 'Envato',
			'evernote' => 'Evernote',
			'facebook' => 'Facebook',
			'feedburner' => 'Feedburner',
			'fh_px'=> '500px',
			'filmweb' => 'Filmweb',
			'flattr' => 'Flattr',
			'flickr' => 'Flickr',
			'forrst' => 'Forrst',
			'foursquare' => 'Foursquare',
			'friendfeed' => 'Friendfeed',
			'github' => 'Github',
			'goodreads' => 'Goodreads',
			'google' => 'Google',
			'google-play' => 'Google Play',
			'google-plus' => 'Google Plus',
			'grooveshark' => 'Grooveshark',
			'houzz' => 'Houzz',
			'icloud' => 'iCloud',
			'icq' => 'Icq',
			'identica' => 'Identica',
			'imdb' => 'Imdb',
			'instagram' => 'Instagram',
			'istock' => 'Istock',
			'itunes' => 'Itunes',
			'lanyrd' => 'Lanyrd',
			'lastfm' => 'Lastfm',
			'linkedin' => 'Linkedin',
			'mail' => 'Mail',
			'me2day' => 'Me2Day',
			'medium' => 'Medium',
			'meetup' => 'Meetup',
			'mixcloud' => 'Mixcloud',
			'model-mayhem' => 'Model Mayhem',
			'mozilla-persona' => 'Mozilla Persona',
			'mumble' => 'Mumble',
			'myspace' => 'Myspace',
			'newsvine' => 'Newsvine',
			'odnoklassniki' => 'Odnoklassniki',
			'openid' => 'Openid',
			'outlook' => 'Outlook',
			'patreon' => 'Patreon',
			'paypal' => 'Paypal',
			'periscope' => 'Periscope',
			'picasa' => 'Picasa',
			'pinterest' => 'Pinterest',
			'play-store' => 'Play Store',
			'playstation' => 'Playstation',
			'pocket'=> 'Pocket',
			'posterous' => 'Posterous',
			'qq'=> 'Qq',
			'quora'=> 'Quora',
			'raidcall'=> 'Raidcall',
			'ravelry'=> 'Ravelry',
			'reddit'=> 'Reddit',
			'renren'=> 'Renren',
			'resident-advisor' => 'Resident Advisor',
			'rss'=> 'RSS',
			'sharethis'=> 'Sharethis',
			'skype'=> 'Skype',
			'slideshare'=> 'Slideshare',
			'smugmug'=> 'Smugmug',
			'snapchat'=> 'Snapchat',
			'sociconapp' => 'App NET',
			'soundcloud'=> 'Soundcloud',
			'spotify'=> 'Spotify',
			'stackexchange'=> 'Stackexchange',
			'stackoverflow'=> 'Stackoverflow',
			'stayfriends'=> 'Stayfriends',
			'steam'=> 'Steam',
			'storehouse'=> 'Storehouse',
			'stumbleupon'=> 'Stumbleupon',
			'swarm'=> 'Swarm',
			'teamspeak'=> 'Teamspeak',
			'teamviewer'=> 'Teamviewer',
			'technorati'=> 'Technorati',
			'telegram' => 'Telegram',
			'tencent' => 'TenCent',
			'tripadvisor'=> 'Tripadvisor',
			'tripit'=> 'Tripit',
			'triplej'=> 'Triplej',
			'tumblr'=> 'Tumblr',
			'twitch'=> 'Twitch',
			'twitter'=> 'Twitter',
			'ventrilo'=> 'Ventrilo',
			'viadeo'=> 'Viadeo',
			'viber'=> 'Viber',
			'vimeo'=> 'Vimeo',
			'vine'=> 'Vine',
			'vk'=> 'Vk',
			'weibo'=> 'Weibo',
			'whatsapp'=> 'Whatsapp',
			'wikipedia'=> 'Wikipedia',
			'windows'=> 'Windows',
			'wordpress'=> 'WordPress',
			'wykop'=> 'Wykop',
			'xbox'=> 'Xbox',
			'xing'=> 'Xing',
			'yahoo'=> 'Yahoo',
			'yammer'=> 'Yammer',
			'yandex'=> 'Yandex',
			'yelp'=> 'Yelp',
			'younow'=> 'Younow',
			'youtube'=> 'Youtube',
			'zerply'=> 'Zerply',
			'zomato'=> 'Zomato',
			'zynga'=> 'Zynga'
		);
										
		return $social;
	}
}

?>