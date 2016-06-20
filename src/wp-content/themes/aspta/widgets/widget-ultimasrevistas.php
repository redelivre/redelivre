<?php
class Widget_UltimasRevistas extends WP_Widget {
	function Widget_UltimasRevistas() {
		$widget_ops = array('classname' => 'widget_ultimasrevistas', 'description' => 'Mostra as últimas edições da Revista.' );
		$this->WP_Widget('ultimasrevistas', 'Revista Agriculturas', $widget_ops);
		
	}
 
	function widget($args, $instance) {
		extract($args);
		
		global $post;
		
		
		$title = apply_filters( 'widget_title', empty($instance['title']) ? 'Últimas edições' : $instance['title'] );
		
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 4;
		
		$ultimas = new WP_Query( array( 
			'caller_get_posts' => 1,
			'post_type'	=> 'revista',
			'posts_per_page' => $number,
			'post_parent'	=> 0,
			'post__not_in'  => array( $post->ID )
		) );
			
		if ( ! is_page( 'revista-agriculturas' ) ) {
			if ( $ultimas->have_posts() ) :
				echo $before_widget;
				if ( $title ) echo $before_title . $title . $after_title; ?>
				<ul>
					<?php
					while ( $ultimas->have_posts() ) : $ultimas->the_post(); ?>
					<li><a href="<?php echo get_permalink( $revista->ID ); ?> "><?php the_revista_thumbnail( 'medium' ); ?></a></li>			
					<?php
					endwhile; ?>
				</ul>
				<p>
					<a class="veja-mais" href="<?php echo get_page_link( get_page_by_title( 'Revista Agriculturas' )->ID ); ?>" title="Revista Agriculturas">Ver todas</a>
				</p>
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
		$number = isset($instance['number']) ? absint($instance['number']) : 4;
		
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p><label for="<?php echo $this->get_field_id('number'); ?>">Número de edições:</label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}
register_widget( 'Widget_UltimasRevistas' );
?>