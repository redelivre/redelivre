<?php
class Widget_Biblioteca extends WP_Widget {
	function Widget_Biblioteca() {
		$widget_ops = array('classname' => 'widget_biblioteca', 'description' => 'Lista nas páginas os itens relacionados com a Biblioteca' );
		$this->WP_Widget('biblioteca', 'Biblioteca', $widget_ops);
		
	}
 
	function widget($args, $instance) {
		extract($args);
		
		global $post, $taxonomy, $term;
		
		
		if ( is_child_of( 'programas' ) || is_tax( array ( 'programas', 'temas-de-intervencao' ) ) ) {
			
			$title = 'Biblioteca do ' . $post->post_title;
			
			echo $before_widget;
			
			$joiner = ( get_option( 'permalink_structure' ) == null ) ? '&' : '?';
			
			if ( is_child_of( 'programas' ) ) {
				$taxonomia = 'programas';
				$termo = $post->post_name;
				$title = 'Biblioteca para o ' . $post->post_title;
			}
			elseif ( is_tax( ) ) {
				$taxonomia = $taxonomy;
				$termo = $term;
				if ( is_tax ( 'temas-de-intervencao' ) )
					$title = 'Biblioteca para o tema ' . thematic_get_term_name();
				else
					$title = 'Biblioteca para o ' . thematic_get_term_name();
			}
			
			echo $before_title . $title . $after_title;
			
			$categorias = get_categories( 'hide_empty=1' ); ?>
			
			<p>
				Consulte a biblioteca relacionada com este <?php echo ( is_child_of ( 'programas' ) || is_tax ( 'programas' ) ) ? 'programa' : 'tema'; ?>
			</p>
			
			<ul>
				<?php
				foreach ( $categorias as $categoria ) {
					
					$args = array(
						'posts_per_page' => -1,
						'tax_query' => array(
							'relation' => 'AND',
							array(
								'taxonomy' => $taxonomia,
								'field' => 'slug',
								'terms' => array( $termo ),
							),
							array(
								'taxonomy' => 'category',
								'field' => 'id',
								'terms' => array( $categoria->term_id ),
							)
						)
					);
							
					$biblioteca = new WP_Query ( $args );
					
					if ( $biblioteca->have_posts() ) : ?>
					
						<li class="<?php echo $categoria->slug; ?>"><a href="<?php echo get_category_link( $categoria->term_id ) . $joiner . $taxonomia . '=' . $termo; ?>"><?php echo $categoria->name . ' (' . $biblioteca->post_count . ')'; ?></a></li> 
					
					<?php
					endif;
					
				}
				?>
			</ul>
			<?php
			
		}
		
		echo $after_widget;
		
	}
 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
 
		return $instance;
		
	}
 
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		
?>
		<p><em>Este widget não possui configurações. Ele apenas lista cada item da biblioteca e sua relação com o Programa que está sendo visualizado.</em></p>
		<?php /*
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		*/ ?>
<?php
	}
}
register_widget( 'Widget_Biblioteca' );
?>