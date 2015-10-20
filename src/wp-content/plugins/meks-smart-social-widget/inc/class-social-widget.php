<?php
/*-----------------------------------------------------------------------------------*/
/*	Social Widget Class
/*-----------------------------------------------------------------------------------*/

class MKS_Social_Widget extends WP_Widget {

	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'mks_social_widget', 'description' => __('Display your social icons with this widget', 'meks') );
		$control_ops = array( 'id_base' => 'mks_social_widget' );
		parent::__construct('mks_social_widget', __('Meks Social Widget', 'meks'), $widget_ops, $control_ops );
		
		add_action( 'wp_enqueue_scripts', array($this,'enqueue_scripts'));
		add_action( 'admin_enqueue_scripts', array($this,'enqueue_admin_scripts'));

		$this->defaults = array( 
			'title' => __('Follow Me', 'meks'),
			'content' => '',
			'style' => 'square',
			'size' => 48,
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
		wp_enqueue_script( 'meks-social-widget-js', MKS_SOCIAL_WIDGET_URL.'js/main.js', array( 'jquery'), MKS_SOCIAL_WIDGET_VER );	
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
			$size_style = 'style="width: '.$instance['size'].'px; height: '.$instance['size'].'px;"'; 
			$target = 'target="'.$instance['target'].'"';
		?>
			<ul class="mks_social_widget_ul">
		  	<?php foreach($instance['social'] as $item) : ?>
		  		<li><a href="<?php echo $item['url']; ?>" class="<?php echo $item['icon'].'_ico soc_'.$instance['style'];?>" <?php echo $target; ?> <?php echo $size_style; ?>><?php echo $item['icon']; ?></a></li>
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
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'meks'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'content' ); ?>"><?php _e('Introduction text (optional)', 'meks'); ?>:</label>
			<textarea id="<?php echo $this->get_field_id( 'content' ); ?>" rows="5" name="<?php echo $this->get_field_name( 'content' ); ?>" class="widefat"><?php echo $instance['content']; ?></textarea>
		</p>
		<h4><?php _e('Options', 'meks'); ?>:</h4>
		<p>
			<label><?php _e('Icon shape', 'meks'); ?>:</label><br/>
			<input type="radio" name="<?php echo $this->get_field_name( 'style' ); ?>" value="square" <?php checked($instance['style'],'square'); ?>/>
			<label><?php _e('Square', 'meks'); ?></label><br/>
			<input type="radio" name="<?php echo $this->get_field_name( 'style' ); ?>" value="circle" <?php checked($instance['style'],'circle'); ?>/>
			<label><?php _e('Circle', 'meks'); ?></label><br/>
			<input type="radio" name="<?php echo $this->get_field_name( 'style' ); ?>" value="rounded" <?php checked($instance['style'],'rounded'); ?>/>
			<label><?php _e('Rounded corners', 'meks'); ?></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e('Icon size', 'meks'); ?>: </label>
			<input id="<?php echo $this->get_field_id( 'size' ); ?>" type="text" name="<?php echo $this->get_field_name( 'size' ); ?>" value="<?php echo absint($instance['size']); ?>" class="small-text" /> px
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'target' ); ?>"><?php _e('Open links in', 'meks'); ?>: </label>
			<select id="<?php echo $this->get_field_id( 'target' ); ?>" name="<?php echo $this->get_field_name( 'target' ); ?>">
				<option value="_blank" <?php selected('_blank',$instance['target']); ?>><?php _e('New Window', 'meks'); ?></option>
				<option value="_self" <?php selected('_self',$instance['target']); ?>><?php _e('Same Window', 'meks'); ?></option>
			</select>
		</p>
		
		<h4><?php _e('Icons', 'meks'); ?>:</h4>
		<p>
		 <ul class="mks_social_container">
		  <?php foreach($instance['social'] as $link) : ?>
			  <li>
			  	<?php $this->draw_social($this, $social_links, $link); ?>
			  </li>
			<?php endforeach; ?>
		 </ul>
	  </p>
		
		<p>
	  	<a href="#" class="mks_add_social button"><?php _e('Add Icon', 'meks'); ?></a>
	  </p>
	  
	  <div class="mks_social_clone" style="display:none">
			<?php $this->draw_social($this, $social_links); ?>
	  </div>
	  
		
		
	<?php
	}
	
	function draw_social($widget, $social_links, $selected = array('icon' => '', 'url' => '') ){ ?>
				<?php _e('Icon', 'meks'); ?>: <select type="text" name="<?php echo $widget->get_field_name('social_icon'); ?>[]" value="<?php echo $selected['icon']; ?>" style="width: 80%">
				<?php foreach($social_links as $key => $link) : ?>
					<option value="<?php echo $key; ?>" <?php selected($key,$selected['icon']); ?>><?php echo $link; ?></option>
				<?php endforeach; ?>
				</select><br />
				<?php _e('Url', 'meks'); ?>: &nbsp;&nbsp;&nbsp;<input type="text" name="<?php echo $widget->get_field_name('social_url'); ?>[]" value="<?php echo $selected['url']; ?>" style="width: 80%">
				<a href="#" class="mks_remove_social" title="<?php _e('Remove', 'meks'); ?>"><?php _e('x', 'meks'); ?></a>
	<?php }
	
	function get_social() {
		$social = array(
			'aim' => 'Aim',
			'apple' => 'Apple',
			'behance' => 'Behance',
			'blogger' => 'Blogger',
			'cargo' => 'Cargo',
			'delicious' => 'Delicious',
			'deviantart' => 'DeviantArt',
			'digg' => 'Digg',
			'dribbble' => 'Dribbble',
			'envato' => 'Envato',
			'evernote' => 'Evernote',
			'facebook' => 'Facebook',
			'flickr' => 'Flickr',
			'forrst' => 'Forrst',
			'github' => 'Github',
			'google' => 'Google',
			'googleplus' => 'GooglePlus',
			'grooveshark' => 'GrooveShark',
			'icloud' => 'Icloud',
			'instagram' => 'Instagram',
			'itunes' => 'iTunes',
			'lastfm' => 'LastFM',
			'linkedin' => 'LinkedIN',
			'myspace' => 'MySpace',
			'me2day' => 'Me2Day',
			'picasa' => 'Picasa',
			'pinterest' => 'Pinterest',
			'posterous' => 'Posterous',
			'reddit' => 'ReddIT',
			'rss' => 'Rss',
			'skype' => 'Skype',
			'spotify' => 'Spotify',
			'soundcloud' => 'Soundcloud',
			'stumbleupon' => 'StumbleUpon',
			'tumblr' => 'Tumblr',
			'twitter' => 'Twitter',
			'tencent' => 'Tencent',
			'twitch' => 'Twitch',
			'vimeo' => 'Vimeo',
			'vine' => 'Vine',
			'vk' => 'vKontakte',
			'wordpress' => 'WordPress',
			'weibo' => 'Sina Weibo',
			'xing' => 'Xing',
			'yahoo' => 'Yahoo',
			'youtube' => 'Youtube',
			'zerply' => 'Zerply',
			'fh_px' => '500px',
		);
										
		return $social;
}
}

?>