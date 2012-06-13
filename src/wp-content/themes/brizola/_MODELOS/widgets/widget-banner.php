<?php
/*
Plugin Name: Widget de banner
Description: Adds a banner to the sidebar
Version: 1.0
Author: HackLab
*/

class WidgetBanner extends WP_Widget {
    function WidgetBanner() {
        $widget_ops = array('classname' => 'Banner', 'description' => 'Adds a banner to the sidebar' );
        parent::WP_Widget('banner', 'Banner', $widget_ops);

    }
 
    function widget($args, $instance) {
        extract($args);
        echo $before_widget;
            $blank = ($instance['target'] == 'sim') ? '_blank' : '';
            if($instance['title']) echo $before_title, $instance['title'], $after_title;
            
            if($instance['imageFileURL']) {
                if($instance['linkURL']) echo '<a href="',$instance['linkURL'],'" title="',$instance['title'],'" target="',$blank,'">';
                echo '<img src="',$instance['imageFileURL'],'" />';
                if($instance['linkURL']) echo '</a><div class="hr"></div>';
            }            
        echo $after_widget;
    }
 
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        
        $instance['imageFileURL'] = strip_tags($new_instance['imageFileURL']);
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['linkURL'] = strip_tags($new_instance['linkURL']);
        $instance['target'] = $new_instance['target'];
        $instance['width'] = strip_tags($new_instance['width']);
        $instance['height'] = strip_tags($new_instance['height']);
        return $instance;
    }
    
    function form($instance) {
        $imageFileURL = esc_attr($instance['imageFileURL']);
        $title = esc_attr($instance['title']);
        $linkURL = esc_attr($instance['linkURL']);
        $target = $instance['target'];
        $width = esc_attr($instance['width']);
        $height = esc_attr($instance['height']);
        
        ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>">
                    <?php _e('Title:'); ?> 
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('imageFileURL'); ?>">
                    <?php _e('URL to the image or swf:'); ?> 
                    <input class="widefat" id="<?php echo $this->get_field_id('imageFileURL'); ?>" name="<?php echo $this->get_field_name('imageFileURL'); ?>" type="text" value="<?php echo $imageFileURL; ?>" />
                </label>
            </p>
            <p>
                <label>
                    <?php _e('Dimensions (needed for swf)'); ?> 
                    <input size="3" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo $width; ?>" />
                    x
                    <input size="3" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo $height; ?>" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('linkURL'); ?>">
                    <?php _e('Link:'); ?> 
                    <input class="widefat" id="<?php echo $this->get_field_id('linkURL'); ?>" name="<?php echo $this->get_field_name('linkURL'); ?>" type="text" value="<?php echo $linkURL; ?>" />
                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id('target'); ?>">
                    <?php _e('Abrir em nova Janela?'); ?> 
                    <?php $blank = ($instance['target'] == 'sim') ? 'checked' : ''; ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('target'); ?>" name="<?php echo $this->get_field_name('target'); ?>" type="checkbox" value="sim" <?php echo $blank; ?> />
                </label>
            </p>
        <?php 

    }
 
}

function registerWidgetBanner() {
    register_widget("WidgetBanner");
}

add_action('widgets_init', 'registerWidgetBanner');
    
?>
