<?php
/*
 * Plugin Name: Widget de Lista da Agenda
 */

class WidgetAgenda extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => __CLASS__, 'description' => __('adiciona uma lista dos eventos', 'sbc'));
        parent::__construct('widget_agenda', __('Agenda - Lista', 'sbc'), $widget_ops);
    }

    function widget($args, $instance) {
    
    	extract( $args, EXTR_SKIP );
    	
        $num_posts = array_key_exists('num_posts', $instance) ? $instance['num_posts'] : 0;
        
        $qargs = array(
            'posts_per_page' => $num_posts,
            'post_type' => 'agenda',
            'orderby' => 'meta_value',
            'meta_key' => '_data_inicial',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_data_final',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATETIME'
                )
            )
        );

        remove_filter('pre_get_posts', array('Agenda','pre_get_posts'));
        
        $events_query = new WP_Query($qargs);

        $events = $events_query->posts;
        
        echo $before_widget;
	            
        echo $before_title;
        echo empty( $instance['title'] ) ? _e( 'Agenda', 'sbc' ) : $instance['title'];
        echo $after_title;
        
        foreach ($events as $event):
            $data_inicial = get_post_meta($event->ID, '_data_inicial', true);
            if ($data_inicial)
                $data_inicial = mysql2date(get_option('date_format'), $data_inicial, true);

            $data_final = get_post_meta($event->ID, '_data_final', true);
            if ($data_final)
                $data_final = mysql2date(get_option('date_format'), $data_final, true);
            ?>
            <p>
                <span class="date">
                    <?php echo $data_inicial; ?> 
                    <?php if ($data_inicial != $data_final): ?>
                        a <?php echo $data_final; ?>
                    <?php endif; ?>
                </span><br/>
                <a href="<?php echo get_permalink($event->ID); ?>" title="<?php echo esc_attr($event->post_title); ?>"><?php echo $event->post_title; ?></a>
            </p>
            <?php
        endforeach;
        ?>
        <p class="textright"><a href="<?php echo get_post_type_archive_link('agenda') ?>" class="all"><?php _e('veja o calendário completo', 'sbc'); ?></a></p>
        
        <?php
        echo $after_widget;
        
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['num_posts'] = $new_instance['num_posts'];
        return $instance;
    }

    function form($instance) {
        $title = isset($instance['title']) ? $instance['title'] : '';
        $num_posts = isset($instance['num_posts']) ? $instance['num_posts'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php _e('Título'); ?><br/>
                <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo isset($title) ? $title : __('Agenda', 'sbc'); ?>"/>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('post_name'); ?>">
                <?php _e('Número de eventos na lista', 'sbc'); ?> 
                <input class="widefat" id="<?php echo $this->get_field_id('num_posts'); ?>" name="<?php echo $this->get_field_name('num_posts'); ?>" type="text" value="<?php echo $num_posts; ?>" />
            </label>
        </p>
        <?php
    }

}

function registerWidgetAgendaLista() {
    register_widget("WidgetAgenda");
}

add_action('widgets_init', 'registerWidgetAgendaLista');
