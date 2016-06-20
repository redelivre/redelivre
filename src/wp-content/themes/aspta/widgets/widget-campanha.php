<?php
class Widget_Campanha extends WP_Widget {
	function Widget_Campanha() {
		$widget_ops = array('classname' => 'widget_campanha', 'description' => 'Lista nas páginas os itens relacionados com a Campanha' );
		$this->WP_Widget('campanha', 'Ítens de Campanha', $widget_ops);
		
	}
 
	function widget($args, $instance) {
		extract($args);
		
		global $post, $taxonomy, $term;
		
		
		if ( is_tax ( 'itens-de-campanha' ) ) {
			
			$title = 'Campanha do ' . $post->post_title;
			
			echo $before_widget;
			
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? 'Ítens de Campanha' : $instance['title'] );
			
			$itens = get_terms( 'itens-de-campanha', array( 'hide_empty' => 0 ) );
			
			if ( $itens ) {
				echo $before_title . $title . $after_title; ?>
				<ul>
					<?php
					foreach ( $itens as $item ) { ?>
					
						<li<?php if ( $term == $item->slug ) echo ' class="current_term_item"'; ?>><a href="<?php echo get_term_link ( $item->slug, 'itens-de-campanha' ); ?>"><?php echo $item->name; ?></a></li>
						<?php 	
					}
					?>
				</ul>
				<?php
			}
			
		}
		
		echo $after_widget;
		
	}
 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
 
		return $instance;
		
	}
 
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
	}
}
register_widget( 'Widget_Campanha' );
?>