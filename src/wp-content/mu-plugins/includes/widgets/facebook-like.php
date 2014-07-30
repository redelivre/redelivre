<?php
/*
Plugin Name: Widget Facebook Like
Description: Adciona uma caixa de Likes do Facebook
Version: 1.0
Author: HackLab
*/

class WidgetFacebookLikeBox extends WP_Widget {
    function WidgetFacebookLikeBox() {
        $widget_ops = array('classname' => 'FacebookLikeBox', 'description' => 'Adciona uma caixa de likes de sua página no Facebook' );
        wp_register_script('facebook_like_form', network_site_url() . 'wp-content/mu-plugins/includes/widgets/js/facebook-like.js', array('jquery'));
        wp_enqueue_script('facebook_like_form');
        parent::WP_Widget('facebookLikeBox', 'Facebook LikeBox', $widget_ops);
    }
    
    function widget($args, $instance) {
        extract($args);
        $options = get_option('campanha_social_networks');
        if(!isset($options['facebook-page']) || !$options['facebook-page'])
            return;
        
        echo $before_widget;
        $show_faces = (isset($instance['fb-show-faces'])) ? $instance['fb-show-faces'] : 'true';
        
        $altura = ( array_key_exists('fb-height', $instance) && intval($instance['fb-height']) > 0 ) ? intval($instance['fb-height']) : (($show_faces == 'true') ? '285' : '80');
        $alturapx = $altura.'px';
        ?>
        <iframe src="//www.facebook.com/plugins/likebox.php?href=<?php echo urlencode($options['facebook-page']) ?>&amp;width="100%"&amp;height=<?php echo $altura; ?>&amp;colorscheme=light&amp;show_faces=<?php echo $show_faces; ?>&amp;border_color=white&amp;stream=false&amp;show_border=false&amp;header=false&amp;appId=" scrolling="no" frameborder="0" allowTransparency="true" style="width: 100%; height: <?php echo $alturapx; ?>; overflow:hidden;" ></iframe>
        <?php
        echo $after_widget;
    }
 
    function update($new_instance, $old_instance) {
        $instance = array();
		$instance['fb-show-faces'] = $new_instance['fb-show-faces'];
		$instance['fb-height'] = $new_instance['fb-height'];

		return $instance;
        //return $old_instance;
    }
    
    function form($instance) {
    	
    	$fb_height = array_key_exists('fb-height', $instance) ? intval($instance['fb-height']) : 290;
        ?>
        <p>
        Este Widget utiliza a configuração de Página do Facebook do menu <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=campaign_social_networks">Redes Sociais</a> e para funcionar corretamente, você deve ter uma página no Facebook.
        </p>

        <strong>Opções do widget</strong>
        <p>
        	<label for="<?php $this->get_field_id('fb-show-faces'); ?>">Exibir fotos</label>
        	<select name="<?php echo $this->get_field_name('fb-show-faces'); ?>" id="<?php echo $this->get_field_id('fb-show-faces'); ?>">
        		<option value="true" <?php echo ( array_key_exists('fb-show-faces', $instance) && $instance['fb-show-faces'] == 'true') ? "selected=1" : "selected=1"; ?>>Sim</option>
        		<option value="false" <?php echo ( array_key_exists('fb-show-faces', $instance) && $instance['fb-show-faces'] == 'true') ? "" : ""; ?>>Não</option>
        	</select>
        </p>
        <p>
        	<label for="<?php $this->get_field_id('fb-height'); ?>"><?php _e('Altura do Widget', 'redelivre'); ?></label>
        	<input id="<?php echo $this->get_field_id( 'fb-height' ); ?>" name="<?php echo $this->get_field_name( 'fb-height' ); ?>" type="text" value="<?php echo esc_attr( $fb_height ); ?>" />
        	<label for="<?php $this->get_field_id('fb-height'); ?>"><small><?php _e('Obs.: use 0 para configuração padrão', 'redelivre'); ?></small></label>
        </p>
        <script type="text/javascript">
		<!--
			jQuery(document).ready(function() {
				facebook_like_form_auto_height_init("<?php echo $this->get_field_id('fb-show-faces'); ?>", "<?php echo $this->get_field_id( 'fb-height' ); ?>");
			});
		//-->
		</script>
        <?php 
    }
 
}

function registerWidgetFacebookLikeBox() {
    register_widget("WidgetFacebookLikeBox");
}

add_action('widgets_init', 'registerWidgetFacebookLikeBox');
    
?>
