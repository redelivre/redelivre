<?php
/*
Plugin Name: Widget Facebook Like
Description: Adciona uma caixa de Likes do Facebook
Version: 1.0
Author: HackLab
*/

class WidgetFacebookSubscribe extends WP_Widget {
    function __construct() {
        $widget_ops = array('classname' => 'FacebookSubscribe', 'description' => 'Adciona uma caixa de Subscribes do Facebook' );
        parent::__construct('facebookSubscribe', 'Facebook Assinantes', $widget_ops);

    }
    
    function widget($args, $instance) {
        extract($args);
        $options = get_option('campanha_social_networks');
        if(!isset($options['facebook']) || !$options['facebook'])
            return;
        
        echo $before_widget;
        ?>
        <iframe src="//www.facebook.com/plugins/subscribe.php?href=<?php echo urlencode($options['facebook']) ?>&amp;layout=standard&amp;show_faces=true&amp;colorscheme=light&amp;font=lucida+grande&amp;width=292&amp;appId=" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height: 80px;" allowTransparency="true"></iframe>
        <?php
        echo $after_widget;
    }
 
    function update($new_instance, $old_instance) {
        return $old_instance;
    }
    
    function form($instance) {
        ?>
        Este Widget utiliza a configuração de Perfil do Facebook do menu <a href="<?php bloginfo('url') ?>/wp-admin/admin.php?page=campaign_social_networks">Redes Sociais</a> e para funcionar corretamente, você deve ter a opção <a href="https://www.facebook.com/settings?tab=subscribers" target="_blank" >permitir assinantes </a> habilitada em seu perfil do Facebook.
        <?php
    }
 
}

function registerWidgetFacebookSubscribe() {
    register_widget("WidgetFacebookSubscribe");
}

add_action('widgets_init', 'registerWidgetFacebookSubscribe');
    
?>
