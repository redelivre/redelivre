<?php
class Widget_Ultimos extends WP_Widget {
	function Widget_Ultimos() {
		$widget_ops = array('classname' => 'widget_ultimos', 'description' => 'Mostra os últimos posts.' );
		$this->WP_Widget('ultimos', 'Tópicos recentes, volume II', $widget_ops);
		
	}
 
	function widget($args, $instance) {
		extract($args);
		
		global $post;
		
		if ( !is_home() ) {
		
			$title = apply_filters( 'widget_title', empty($instance['title']) ? 'Últimos posts' : $instance['title'] );
			
			if ( is_page( 'campanha' ) || is_tax( 'itens-de-campanha' ) )
				$post_type = 'campanha';
			else
				$post_type = '';
			
			$ultimos = new WP_Query( array( 
				'caller_get_posts'	=> 1,
				'posts_per_page'	=> 5,
				'post__not_in'		=> array( $post->ID ),
				'post_type'			=> $post_type
			) );
				
			if ( $ultimos->have_posts() ) :
				echo $before_widget;
				if ( $title ) echo $before_title . $title . $after_title; ?>
				<ul>
					<?php
					while ( $ultimos->have_posts() ) : $ultimos->the_post(); ?>
					<li><a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php echo strip_tags(get_the_title()); ?></a></li>			
					<?php
					endwhile; ?>
				</ul>
				<?php
				echo $after_widget;
			endif;
		} //is_home
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
register_widget( 'Widget_Ultimos' );
?>