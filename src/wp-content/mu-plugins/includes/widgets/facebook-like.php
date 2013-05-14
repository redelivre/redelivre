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
        parent::WP_Widget('facebookLikeBox', 'Facebook LikeBox', $widget_ops);

    }
    
    function widget($args, $instance) {
        extract($args);
        $options = get_option('campanha_social_networks');
        if(!isset($options['facebook-page']) || !$options['facebook-page'])
            return;
        
        echo $before_widget;
        $show_faces = (isset($instance['fb-show-faces'])) ? $instance['fb-show-faces'] : 'true';
        $altura = ($show_faces == 'true') ? '285px' : '80px';
        ?>
        <iframe src="//www.facebook.com/plugins/likebox.php?href=<?php echo urlencode($options['facebook-page']) ?>&amp;width=292&amp;height=290&amp;colorscheme=light&amp;show_faces=<?php echo $show_faces; ?>&amp;border_color=white&amp;stream=false&amp;header=false&amp;appId=" scrolling="no" frameborder="0" allowTransparency="true" style="width: 280px; height: <?php echo $altura; ?>; overflow:hidden;" ></iframe>
        <?php
        echo $after_widget;
    }
 
    function update($new_instance, $old_instance) {
        $instance = array();
		$instance['fb-show-faces'] = $new_instance['fb-show-faces'];

		return $instance;
        //return $old_instance;
    }
    
    function form($instance) {
        ?>
        <p>
        Este Widget utiliza a configuração de Página do Facebook do menu <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=campaign_social_networks">Redes Sociais</a> e para funcionar corretamente, você deve ter uma página no Facebook.
        </p>

        <strong>Opções do widget</strong>
        <p>
        	<label for="<?php $this->get_field_id('fb-show-faces'); ?>">Exibir fotos</label>
        	<select name="<?php echo $this->get_field_name('fb-show-faces'); ?>" id="<?php echo $this->get_field_id('fb-show-faces'); ?>">
        		<option value="true" <?php echo ($instance['fb-show-faces'] == 'true') ? "selected=1" : ""; ?>>Sim</option>
        		<option value="false" <?php echo ($instance['fb-show-faces'] == 'false') ? "selected=1" : ""; ?>>Não</option>
        	</select>
        </p>
        <?php 
    }
 
}

function registerWidgetFacebookLikeBox() {
    register_widget("WidgetFacebookLikeBox");
}

add_action('widgets_init', 'registerWidgetFacebookLikeBox');
    
?>
