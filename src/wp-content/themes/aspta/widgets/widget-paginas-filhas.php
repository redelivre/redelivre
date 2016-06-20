<?php
class Widget_PaginasFilhas extends WP_Widget {
	function Widget_PaginasFilhas() {
		$widget_ops = array('classname' => 'widget_paginasfilhas', 'description' => 'Lista as páginas filhas, se houver' );
		$this->WP_Widget('paginas_filhas', 'Páginas Filhas', $widget_ops);
		
	}
 
	function widget($args, $instance) {
		global $post;
		
		wp_reset_query();
		
		extract( $args );
		
		if ( is_page() || is_singular( 'revista' ) ) {
		
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? 'Páginas Filhas' : $instance['title'] );
			
			if ( is_singular( 'revista' ) )
				$filhas = get_pages( 'child_of=' . get_page_by_title( 'Revista Agriculturas' )->ID . '&sort_column=menu_order' );
			else
				$filhas = ( $post->post_parent == 0 ) ? get_pages( 'child_of=' . $post->ID . '&sort_column=menu_order' ) : get_pages( 'child_of=' . $post->post_parent . '&sort_column=menu_order' );
			
			if( !empty( $filhas ) ) {
				echo $before_widget;
				
				echo $before_title . $title . $after_title; ?>
				
				<ul>
				<?php
				foreach ( $filhas as $filha ) {
				?>
				<li class="page_item page-item-<?php echo $filha->ID; ?> <?php if ( $post->ID == $filha->ID ) echo 'current_page_item'; ?>"><a href="<?php echo get_page_link($filha->ID); ?>" title="<?php echo $filha->post_title; ?>"><?php echo $filha->post_title; ?></a></li>
				<?php
				} ?>
				</ul>
				
				<?php
				echo $after_widget;	
			}
		}
		
	}
 
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
 
		return $instance;
		
	}
 
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']); ?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
	<?php
	}
}

register_widget( 'Widget_PaginasFilhas' );
?>