<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WidgetUniquePost
 *
 * @author rafael
 */
class WidgetAgendaLista extends WidgetTemplate {

    protected function widget($config) {
        $num_posts = isset($config['num_events']) ? intval($config['num_events']) : 10;
        
        $query_args = array(
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

        $events_query = new WP_Query($query_args);
        
        $events = $events_query->posts;
        ?>
        <header><p class="category"><a href="<?php echo get_post_type_archive_link('agenda') ?>" class="all">agenda</a></p></header>
        <?php
        foreach ($events as $event):
            $data_inicial = get_post_meta($event->ID, '_data_inicial', true);
            if ($data_inicial)
                $data_inicial = date(get_option('date_format'), strtotime($data_inicial));
            
            $data_final = get_post_meta($event->ID, '_data_final', true);
            if ($data_final)
                $data_final = date(get_option('date_format'), strtotime($data_final));
            ?>
            <p>
                <span class="date">
                    <?php echo $data_inicial; ?> 
                    <?php if($data_inicial != $data_final): ?>
                        à <?php echo $data_final; ?>
                    <?php endif; ?>
                </span><br/>
                <a href="<?php echo get_permalink($event->ID); ?>"><?php echo $event->post_title; ?></a>
            </p>
        <?php 
        endforeach; 
        ?>
        <p class="textright"><a href="<?php echo get_post_type_archive_link('agenda') ?>" class="all">veja o calendário completo</a></p>
        <?php
    }

    protected function form($config) {
        ?>
        <p>
            <label>
                Número máximo de eventos:
                <input type="text" name="num_events" value="<?php echo $config['num_events']; ?>"/>
            </label>
        </p>
        <?php
    }

    protected function getFormTitle() {
        return 'Configurações da Agenda';
    }

}
?>
