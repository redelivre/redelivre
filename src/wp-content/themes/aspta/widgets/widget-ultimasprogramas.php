<?php
class Widget_UltimosProgramas extends WP_Widget {
	function Widget_UltimosProgramas() {
		$widget_ops = array('classname' => 'widget_ultimosprogramas', 'description' => 'Mostra os últimos posts relacionados com o programa atual.' );
		$this->WP_Widget('ultimos_programas', 'Tópicos recentes dos programas', $widget_ops);
		
	}
 
	function widget($args, $instance) {
		extract($args);
		
		global $post;
		
		/*
		 * Programas
		 * Se o widget estiver sendo visto na página dos programas ou de algum deles, são mostrados
		 * os posts relacionados apenas com essas categorias (programas e/ou suas filhas)
		 */
		
		echo $before_widget;
		
		$page_parent = $post->post_parent;
		
		if ( $page_parent == 0 ) {
			$title = 'Últimas dos Programas';
			
			$args = array(
				'fields' => 'ids'
			);
			
			// Recebe a ID dos programas
			$ids_programas = get_terms( 'programas', $args );
			 
			// Recebe um array com os posts dentro dos termos da taxonomia 
			$programas_posts = get_objects_in_term( $ids_programas, 'programas');
			
			$ultimos = new WP_Query( array(
				'post__in' => $programas_posts,
				'caller_get_posts' => 1,
				'posts_per_page' => 5,
			) );
			
		}
		else {
			$title = 'Últimas deste Programa';
			
			$page_slug = $post->post_name;
			$page_title = $post->post_title; 
			
			$ultimos = new WP_Query( array(
				'programas'	=> $page_slug,
				'caller_get_posts' => 1,
				'posts_per_page' => 5,
				'post__not_in'  => array( $post->ID )
			) );
			
		}
		
		if ( $ultimos->have_posts() ) {
			echo $before_widget;
			echo $before_title . $title . $after_title; ?>
			<ul>
				<?php
				while ( $ultimos->have_posts() ) : $ultimos->the_post(); ?>
				<li><a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php echo strip_tags(get_the_title()); ?></a></li>			
				<?php
				endwhile; ?>
			</ul>
			
			<?php if ( $page_parent > 0 ) { ?>
			<p class="veja-mais">
				<a href="<?php echo get_term_link( $page_slug, 'programas' ); ?>" title="Veja todos os posts em <?php echo $page_title; ?>">Veja todos os posts deste programa</a>
			</p>
			<?php
			}
			
			
			
			echo $after_widget;
		}
		
		wp_reset_query();
		
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
register_widget( 'Widget_UltimosProgramas' );
?>