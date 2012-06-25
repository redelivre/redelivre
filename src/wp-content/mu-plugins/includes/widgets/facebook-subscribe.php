<?php
/*
Plugin Name: Widget Facebook Like
Description: Adciona uma caixa de Likes do Facebook
Version: 1.0
Author: HackLab
*/

class WidgetFacebookSubscribe extends WP_Widget {
    function WidgetFacebookSubscribe() {
        $widget_ops = array('classname' => 'FacebookSubscribe', 'description' => 'Adciona uma caixa de Subscribes do Facebook' );
        parent::WP_Widget('facebookSubscribe', 'Facebook Assinantes', $widget_ops);

    }
    
    function widget($args, $instance) {
        extract($args);
        $options = get_option('campanha_social_networks');
        if(!isset($options['facebook']) || !$options['facebook'])
            return;
        
        echo $before_widget;
        ?>
        <h3>Assine</h3>
        <iframe src="//www.facebook.com/plugins/subscribe.php?href=<?php echo urlencode($options['facebook']) ?>&amp;layout=standard&amp;show_faces=true&amp;colorscheme=light&amp;font=lucida+grande&amp;width=200&amp;appId=" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:200px;" allowTransparency="true"></iframe>
        <?php
        echo $after_widget;
    }
 
    function update($new_instance, $old_instance) {
        return $old_instance;
    }
    
    function form($instance) {
        ?>
        Para este widget funcionar, você deve ter a opção <a href="https://www.facebook.com/settings?tab=subscribers" target="_blank" >permitir assinantes </a> habilitada em seu perfil do Facebook.
        <?php
    }
 
}

function registerWidgetFacebookSubscribe() {
    register_widget("WidgetFacebookSubscribe");
}

add_action('widgets_init', 'registerWidgetFacebookSubscribe');
    
?>
