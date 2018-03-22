<?php
/*-----------------------------------------------------------------------------------*/
/*	Ads Widget Class
/*-----------------------------------------------------------------------------------*/

class MKS_Ads_Widget extends WP_Widget { 

	var $defaults;

	function __construct() {
		$widget_ops = array( 'classname' => 'mks_ads_widget', 'description' => __('You can place advertisement links with images here', 'meks-easy-ads-widget') );
		$control_ops = array( 'id_base' => 'mks_ads_widget' );
		parent::__construct( 'mks_ads_widget', __('Meks Ads Widget', 'meks-easy-ads-widget'), $widget_ops, $control_ops );

		add_action( 'wp_enqueue_scripts', array($this,'enqueue_scripts'));
		add_action( 'admin_enqueue_scripts', array($this,'enqueue_admin_scripts'));

		
		$this->defaults = array( 
				'title' => __('Advertisement', 'meks-easy-ads-widget'),
				'size' => 'large',
				'num_per_view' => 1,
				'rotate' => 0,
				'randomize' => 0,
				'ad_width' => '',
				'ad_height' => '',
				'ads' => array(),
				'nofollow' => 0,
				'speed' => 5,
				'no_target_blank' => 0
		);



		//Allow themes or plugins to modify default parameters
		$this->defaults = apply_filters('mks_ads_widget_modify_defaults', $this->defaults);
		
	}
  
	function enqueue_scripts(){
    	wp_register_style( 'meks-ads-widget', MKS_ADS_WIDGET_URL.'css/style.css', false, MKS_ADS_WIDGET_VER );
    	wp_enqueue_style( 'meks-ads-widget' );
    }
  
  	function enqueue_admin_scripts(){
  		wp_enqueue_media();
  		wp_enqueue_style( 'meks-ads-widget-admin', MKS_ADS_WIDGET_URL.'css/admin-style.css', false, MKS_ADS_WIDGET_VER );
		wp_enqueue_script( 'meks-ads-widget-js', MKS_ADS_WIDGET_URL.'js/main.js', array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-tabs'), MKS_ADS_WIDGET_VER );	
  	}
  
	
	function widget( $args, $instance ) {
		
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		extract( $args );

		$title = apply_filters('widget_title', $instance['title'] );
		
		echo $before_widget;	
		if ( !empty($title) ) {
			echo $before_title . $title . $after_title;
		}
		?>
			
		<?php if(!empty($instance['ads'])) : ?>
			
			<?php

				if($instance['randomize']){
					shuffle($instance['ads']);
				}
				if(!$instance['rotate']){
					$instance['ads'] = array_slice($instance['ads'],0,$instance['num_per_view']);
				}

				$show_ind = 0;

				if( $instance['size'] == 'custom' ){
					$height = $instance['ad_height'] ? $instance['ad_height'].'px' : 'auto';
					$ad_size = 'style="width:'.$instance['ad_width'].'px; height:'.$height.';" width="'.$instance['ad_width'].'"  height="'.$instance['ad_height'].'"';
				} else if($instance['size'] == 'large'){
					$ad_size = 'style="width:300px; height:250px;" width="300"  height="250"';
				} else if($instance['size'] == 'small'){
					$ad_size = 'style="width:125px; height:125px;" width="125"  height="125"';
				}

				$nofollow = $instance['nofollow'] ? 'rel="nofollow"' : '';
				$target = $instance['no_target_blank'] ? '' : 'target="_blank"';

			?>
			
			
			<ul class="mks_adswidget_ul <?php echo $instance['size'];?>">
	     		<?php foreach($instance['ads'] as $ind => $ad) : ?>
	     			<?php $ad['type'] = !isset( $ad['type'] ) ? 'image' : $ad['type']; ?>
	     			<?php if( $ad['type'] === 'image' && !empty($ad['link']) && !empty($ad['img']) ) : ?>
			     		<li data-showind="<?php echo $show_ind; ?>">
			     			<a href="<?php echo esc_url($ad['link']);?>" <?php echo $target; ?> <?php echo $nofollow; ?>>
			     				<img src="<?php echo esc_url($ad['img']);?>" alt="<?php echo esc_attr(basename($ad['img'])); ?>" <?php echo $ad_size; ?>/>
			     			</a>
			     		</li>
		     		<?php else: ?>
			     		<?php if( $ad['type'] === 'code' && !empty( $ad['code'] ) ) : ?>
			     			<li data-showind="<?php echo $show_ind; ?>">
				     			<div <?php echo $ad_size; ?>>
				     				<?php echo do_shortcode($ad['code']); ?>	
				     			</div>
				     		</li>
			     		<?php endif; ?>
		     		<?php endif; ?>
	     		<?php 
	     			if( !(($ind+1) % $instance['num_per_view'])){
	     				$show_ind++;
	     			}
	     		?>
	     		<?php endforeach; ?>
	    	</ul>
	    
	    <?php 
	    
	    	if(count($instance['ads']) % $instance['num_per_view']){
	    		$show_ind++;
	    	}
	    
	    ?>
	  
	  	<?php if($instance['rotate']) : 
	   		$widget_id = $this->id;
	  		$slide_func_id = str_replace("-","",$this->id);
	  	 	$li_ind = 'li_ind_'.$slide_func_id;
	  	?>

		  	<script type="text/javascript">
				/* <![CDATA[ */
				var <?php echo $li_ind; ?> = 0;
				(function($) {
				  
				  $(document).ready(function(){
				  	slide_ads_<?php echo $slide_func_id; ?>();
				  });
	   	     
				})(jQuery);
				
				function slide_ads_<?php echo str_replace("-","",$this->id); ?>(){
					
					jQuery("#<?php echo $widget_id; ?> ul li").hide();
					jQuery("#<?php echo $widget_id; ?> ul li[data-showind='"+<?php echo $li_ind; ?>+"']").fadeIn(300);
					<?php echo $li_ind; ?>++;
					
					if(<?php echo $li_ind; ?> > <?php echo ($show_ind - 1);?>){
					 <?php echo $li_ind; ?> = 0;
					}
					
					//alert(<?php echo $li_ind; ?>);
					
				 	setTimeout('slide_ads_<?php echo $slide_func_id; ?>()', <?php echo absint( $instance['speed'] * 1000 ); ?> );
				}
				/* ]]> */
			</script>
			
	 	<?php endif; ?>
	  
    	<?php endif; ?>

		<?php
		
		echo $after_widget;
	}

	
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['size'] = $new_instance['size'];
		$instance['num_per_view'] = absint($new_instance['num_per_view']);
		$instance['rotate'] = isset($new_instance['rotate']) ? 1 : 0;
		$instance['randomize'] = isset($new_instance['randomize']) ? 1 : 0;
		$instance['nofollow'] = isset($new_instance['nofollow']) ? 1 : 0;
		$instance['no_target_blank'] = isset($new_instance['no_target_blank']) ? 1 : 0;
		$instance['speed'] = absint($new_instance['speed']);
		$instance['ad_width'] = absint($new_instance['ad_width']);
		$instance['ad_height'] = absint($new_instance['ad_height']);
		$instance['ads'] = array();

		if(!empty($new_instance['ad_img']) && !empty($new_instance['ad_link']) && !empty($new_instance['ad_code']) ){
			for($i=0; $i < (count($new_instance['ad_img']) - 1); $i++){
					$ad = array();
					$ad['link'] = !empty($new_instance['ad_link']) ? esc_url( $new_instance['ad_link'][$i] ) : '';
					$ad['img'] = !empty($new_instance['ad_img']) ? esc_url( $new_instance['ad_img'][$i] ) : '';
					$ad['code'] = !empty($new_instance['ad_img']) ? $new_instance['ad_code'][$i] : '';
					$ad['type'] = !empty($new_instance['ad_type']) ? $new_instance['ad_type'][$i] : '';
					$instance['ads'][] = $ad;
			}	
		}
		
		return $instance;
	}

	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, $this->defaults ); ?>
			
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'meks-easy-ads-widget'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		
		<h4><?php _e('Ads Size', 'meks-easy-ads-widget'); ?>:</h4>
		<p>
			<input type="radio" name="<?php echo $this->get_field_name( 'size' ); ?>" class="mks-ad-size" value="small" <?php checked($instance['size'],'small'); ?>/>
			<label><?php _e('Small (125x125 px)', 'meks-easy-ads-widget'); ?></label><br/>
			<input type="radio" name="<?php echo $this->get_field_name( 'size' ); ?>" class="mks-ad-size" value="large" <?php checked($instance['size'],'large'); ?>/>
			<label><?php _e('Large (300x250 px)', 'meks-easy-ads-widget'); ?></label><br/>
			<input type="radio" name="<?php echo $this->get_field_name( 'size' ); ?>" class="mks-ad-size" value="custom" <?php checked($instance['size'],'custom'); ?>/>
			<label><?php _e('Custom', 'meks-easy-ads-widget'); ?></label>
		</p>
		<?php 
			$custom_display = $instance['size'] == 'custom' ? 'display:block;' : 'display:none'; 
		?>
		<p style="<?php echo $custom_display; ?>">
			<?php _e('Width', 'meks-easy-ads-widget'); ?>: 
			<input id="<?php echo $this->get_field_id( 'ad_width' ); ?>" type="text" name="<?php echo $this->get_field_name( 'ad_width' ); ?>" value="<?php echo absint($instance['ad_width']); ?>" class="small-text" />px
			<?php _e('Height', 'meks-easy-ads-widget'); ?>:
			<input id="<?php echo $this->get_field_id( 'ad_height' ); ?>" type="text" name="<?php echo $this->get_field_name( 'ad_height' ); ?>" value="<?php echo absint($instance['ad_height']); ?>" class="small-text" />px
	  </p>
		
	  <h4><?php _e('Options', 'meks-easy-ads-widget'); ?>:</h4>
		<p>
			<input id="<?php echo $this->get_field_id( 'rotate' ); ?>" class="mks-ad-rotate" type="checkbox" name="<?php echo $this->get_field_name( 'rotate' ); ?>" value="1" <?php checked(1,$instance['rotate']);?> />
			<label for="<?php echo $this->get_field_id( 'rotate' ); ?>"><?php _e('Rotate (slide) Ads', 'meks-easy-ads-widget'); ?>? </label>
	  	</p>

	  	<?php $speed_display = !empty($instance['rotate']) ? 'display:block;' : 'display:none'; ?>
		<p style="<?php echo esc_attr( $speed_display ); ?>">
			<?php _e('Rotation speed', 'meks-easy-ads-widget'); ?>: 
			<input id="<?php echo $this->get_field_id( 'speed' ); ?>" type="text" name="<?php echo $this->get_field_name( 'speed' ); ?>" value="<?php echo absint($instance['speed']); ?>" class="small-text" />
			<small class="howto"><?php _e('Number of seconds between ads rotation', 'meks-easy-ads-widget'); ?></small>
	  	</p>
		
		<p>
			<input id="<?php echo $this->get_field_id( 'randomize' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'randomize' ); ?>" value="1" <?php checked(1,$instance['randomize']);?> />
			<label for="<?php echo $this->get_field_id( 'randomize' ); ?>"><?php _e('Randomize Ads', 'meks-easy-ads-widget'); ?>? </label>
	  	</p>

	  	<p>
			<input id="<?php echo $this->get_field_id( 'nofollow' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'nofollow' ); ?>" value="1" <?php checked(1,$instance['nofollow']);?> />
			<label for="<?php echo $this->get_field_id( 'nofollow' ); ?>"><?php _e('Add "nofollow" to ad links', 'meks-easy-ads-widget'); ?>? </label>
	  	</p>

	  	<p>
			<input id="<?php echo $this->get_field_id( 'no_target_blank' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'no_target_blank' ); ?>" value="1" <?php checked(1,$instance['no_target_blank']);?> />
			<label for="<?php echo $this->get_field_id( 'no_target_blank' ); ?>"><?php _e('Do not open links in new window', 'meks-easy-ads-widget'); ?>? </label>
	  	</p>
		  
		<p>
			<label for="<?php echo $this->get_field_id( 'num_per_view' ); ?>"><?php _e('Number of Ads per view', 'meks-easy-ads-widget'); ?>: </label>
			<input id="<?php echo $this->get_field_id( 'num_per_view' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num_per_view' ); ?>" value="<?php echo absint($instance['num_per_view']); ?>" class="small-text" />
		  <small class="howto"><?php _e('Means how many ads to display per page load or slide', 'meks-easy-ads-widget'); ?></small>
		</p>
		
		
	  <h4><?php _e('Ads', 'meks-easy-ads-widget'); ?>:</h4>
	  <p>
		  <ul class="mks-ads-container mks-ads-sortable">
		  <?php foreach( $instance['ads'] as $ad ) : ?>
		  	<?php $this->generate_ad_field( $ad ); ?>
		  <?php endforeach; ?>
		 </ul>
	  </p>
	  
	  <p>
	  	<a href="#" class="mks-ads-add button"><?php _e('Add New', 'meks-easy-ads-widget'); ?></a>
	  </p>
	  
		<div class="mks-ads-clone" style="display:none">
			<?php $this->generate_ad_field(); ?>
	  </div>
	  
	<?php
	}

	function generate_ad_field( $ad = array() ){

		$ad = wp_parse_args( $ad, array('link' => '', 'img' => '', 'code' => '', 'type' => 'image' ) );
		$tab_1 = $ad['type'] === 'image' ? "active" : ''; 
		$tab_2 = $ad['type'] === 'code' ? "active" : '';
		?>

		<li>
			<span class="mks-remove-ad dashicons dashicons-no-alt"></span>
			<ul class="mks-tabs">
			    <li class="mks-tab-link <?php echo $tab_1; ?>" data-tab="tab-1">Image</li>
			    <li class="mks-tab-link <?php echo $tab_2; ?>" data-tab="tab-2">Code</li>
			</ul>
			<div class="mks-tabs-wrapper">
				<div id="tab-1" class="mks-tab-content <?php echo $tab_1; ?>" data-type="image">
					<label><?php _e('Link URL', 'meks-easy-ads-widget'); ?>:</label>
					<input type="text" name="<?php echo $this->get_field_name( 'ad_link' ); ?>[]" value="<?php echo esc_url($ad['link']); ?>" class="widefat" />
					<label><?php _e('Image URL', 'meks-easy-ads-widget'); ?>:</label>
					<input type="text" name="<?php echo $this->get_field_name( 'ad_img' ); ?>[]" value="<?php echo esc_url($ad['img']); ?>" class="mks-ads-field-width"/>
					<a href="#" class="mks-ads-select-image-btn button"><?php _e('Select image', 'meks-easy-ads-widget'); ?></a>
					<small class="howto"><?php _e('Specify URLs to your image and link', 'meks-easy-ads-widget'); ?></small>
				</div>
				<div id="tab-2" class="mks-tab-content <?php echo $tab_2; ?>" data-type="code">	
					<label class="mks-ads-code-label"><?php _e('Ad Code', 'meks-easy-ads-widget'); ?>:</label>
					<textarea name="<?php echo $this->get_field_name( 'ad_code' ); ?>[]" rows="4" cols="50" class="widefat"><?php echo $ad['code']; ?></textarea>
					<small class="howto"><?php _e('Paste your ad code here. Note: Scripts, HTML and shortcodes are supported.', 'meks-easy-ads-widget'); ?></small>
				</div>
				<input type="hidden" id="tab-type" type="text" name="<?php echo $this->get_field_name( 'ad_type' ); ?>[]" value="<?php echo $ad['type']; ?>"/>
			</div>
		</li>

		<?php
	}

}


add_action( 'plugins_loaded', 'mks_load_ads_widget_text_domain' );

/* Load text domain */
function mks_load_ads_widget_text_domain() {
  load_plugin_textdomain( 'meks-easy-ads-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}


?>