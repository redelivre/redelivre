<?php
class Widget_NestaRevista extends WP_Widget {
	function Widget_NestaRevista() {
		$widget_ops = array('classname' => 'widget_nestarevista', 'description' => 'Mostra outros artigos da mesma revista.' );
		$this->WP_Widget('nestarevista', 'Revista Agriculturas - Outros artigos', $widget_ops);
		
	}
 
	function widget($args, $instance) {
		extract($args);
		
		global $post;
		
		
		$title = apply_filters( 'widget_title', empty($instance['title']) ? 'Também nesta Revista' : $instance['title'] );
		
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 5;
		
		if ( $post->post_parent != 0 ) {
			
	 		$artigos = new WP_Query( array( 
				'caller_get_posts'	=> 1,
				'post_type'			=> 'revista',
				'posts_per_page'	=> $number,
	 			'post_parent'		=> $post->post_parent,
				'post__not_in'		=> array( $post->ID )
			) );
				
			if ( $artigos->have_posts() ) :
				echo $before_widget;
				if ( $title ) echo $before_title . $title . $after_title; ?>
				<ul>
					<?php
					while ( $artigos->have_posts() ) : $artigos->the_post(); ?>
					<li><a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php echo strip_tags(get_the_title()); ?></a></li>			
					<?php
					endwhile; ?>
				</ul>
				<?php
				echo $after_widget;
			endif;
		}
	}
 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
 
		return $instance;
		
	}
 
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('number'); ?>">Número de artigos:</label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}
register_widget( 'Widget_NestaRevista' );
?>