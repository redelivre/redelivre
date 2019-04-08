<?php

add_action( 'add_meta_boxes', 'meta_post_add_custom_box' );

/* Do something with the data entered */
add_action( 'save_post', 'meta_post_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function meta_post_add_custom_box() {
    add_meta_box( 
        'meta_post',
        'Metas relacionadas',
        'meta_post_inner_custom_box_callback_function',
        'post', // em que post type eles entram?
        'normal', // onde? side, normal, advanced
        'high' //, 'core', 'default' or 'low'
        //,array('variáve' => 'valor') // variaveis que serão passadas para o callback
    );

}

/* Prints the box content */
function meta_post_inner_custom_box_callback_function() {
    global $post;
    // Use nonce for verification
    wp_nonce_field( 'save_meta_post', 'meta_post_noncename' );
    
    $meta_name = '_meta_relacionada';
    
    $metas_atuais = get_post_meta($post->ID, $meta_name);
    
    $metas = get_posts('post_type=meta&showposts=-1');
    
    if (!is_array($metas))
        return;
    
    
    ?>
    <b><label for="<?php echo $meta_name; ?>">
       Selecione uma ou mais metas
    </label></b>
       
       <br/>
       
       
           <?php foreach ($metas as $meta): ?>
                
                <input type="checkbox" name="<?php echo $meta_name; ?>[]" value="<?php echo $meta->ID; ?>" <?php if (in_array($meta->ID, $metas_atuais)) echo 'checked'; ?> > 
                    <?php echo $meta->post_title; ?> 
                <br/><br/>
           
           <?php endforeach; ?>
       
       
       <?php
    
    ?>
    
    <?php
    
}

/* When the post is saved, saves our custom data */
function meta_post_save_postdata( $post_id ) {
    
    $meta_name = '_meta_relacionada';
    
    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times

    if (isset($_POST['meta_post_noncename']) && !wp_verify_nonce( $_POST['meta_post_noncename'], 'save_meta_post' ))
        return;


    // Check permissions
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) 
    {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
    }
    else
    {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
    }

    // OK, we're authenticated: we need to find and save the data
    
    delete_post_meta($post_id, $meta_name);
    
    if (isset($_POST[$meta_name]) && is_array($_POST[$meta_name])) {
    
        foreach ($_POST[$meta_name] as $meta) {
        
            add_post_meta($post_id, $meta_name, $meta);
        
        }
    
    }
    
    wp_cache_delete('widget_metas_relacionadas', 'widget');
    
}



/**
 * Metas relacionadas widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Posts_Relacionados extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_posts_relacionadas', 'description' => 'Exibe os posts relacionados a uma meta. Esse widget só irá aparecer quando estivermos vendo uma meta' );
		parent::__construct('posts-relacionadas', 'Posts relacionados a meta', $widget_ops);
		$this->alt_option_name = 'widget_posts_relacionadas';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_posts_relacionadas', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? 'Posts relacionados' : $instance['title'], $instance, $this->id_base);
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 10;
        
        if (!is_single()) {
            // esse widget só aparece na single
            return;
        
        } else {
        
            $obj = get_queried_object();
            $post_id = $obj->ID;
        
        }
        
        
		$r = new WP_Query(array(
            'posts_per_page' => $number, 
            'no_found_rows' => true, 
            'post_status' => 'publish', 
            'ignore_sticky_posts' => true,
            'meta_key' => '_meta_relacionada',
            'meta_value' => $post_id
            ));
            
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
		<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_posts_relacionadas', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_posts_relacionadas']) )
			delete_option('widget_posts_relacionadas');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_posts_relacionadas', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}


/**
 * Metas relacionadas widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Metas_Relacionados extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_metas_relacionadas', 'description' => 'Exibe as metas relacionadas a um post ou a outra meta. Esse widget só irá aparecer quando estivermos vendo um post ou uma meta' );
		parent::__construct('metas-relacionadas', 'Metas relacionados ao post/meta', $widget_ops);
		$this->alt_option_name = 'widget_metas_relacionadas';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_metas_relacionadas', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? 'Metas relacionadas' : $instance['title'], $instance, $this->id_base);
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 10;
        
        if (!is_single()) {
            // esse widget só aparece na single
            return;
        
        } else {
        
            $obj = get_queried_object();
            $post_id = $obj->ID;
        
        }
        
        if ($obj->post_type == 'post') {
            
            $ids = get_post_meta($post_id, '_meta_relacionada');
            
            if (!sizeof($ids))
                return;
                
            $r = new WP_Query(array(
                'posts_per_page' => $number, 
                'no_found_rows' => true, 
                'post_status' => 'publish', 
                'ignore_sticky_posts' => true,
                'post_type' => 'meta',
                'post__in' => $ids
                ));
        
        } elseif ($obj->post_type == 'meta') {
        
            $terms = get_the_terms( $obj->ID, 'tema' );

            //O primeiro tema é o tema da meta
            foreach ($terms as $term) {
                $TemaDaMeta = $term->slug;
                break;
            }
            
            $r = new WP_Query(array(
                'posts_per_page' => $number, 
                'no_found_rows' => true, 
                'post_status' => 'publish', 
                'ignore_sticky_posts' => true,
                'post_type' => 'meta',
                'post__not_in' => array($obj->ID),
                'tema' => $TemaDaMeta
                ));
        
        } else {
        
            return;
        
        }
        
        
        
        
		
            
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
		<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_posts', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_metas_relacionadas', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}


function register_relacao_meta_post_widgets() {
	
    register_widget('WP_Widget_Posts_Relacionados');
    register_widget('WP_Widget_Metas_Relacionados');
    
}

add_action('widgets_init', 'register_relacao_meta_post_widgets');

?>
