<?php

/**
 * Register widgets
 *
 * @since Guarani 1.0 
 */
function guarani_register_widgets() {

	register_widget( 'Guarani_Custom_Posts_Widget' );
	register_widget( 'Guarani_Featured_Page_Widget' );
	register_widget( 'Guarani_Tag_Cloud_Reloaded_Widget' );
	
	unregister_widget( 'WP_Widget_Tag_Cloud' );
	
}

add_action( 'widgets_init', 'guarani_register_widgets' );


/**
 * Custom Posts Widget
 * Display the latest posts from a certain category
 *
 * @since Guarani 1.0
 */
class Guarani_Custom_Posts_Widget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function Guarani_Custom_Posts_Widget() {
		$widget_ops = array( 'classname' => 'widget_guarani_custom_posts', 'description' => __( 'Displays the latest posts from a certain category', 'guarani' ) );
		$this->WP_Widget( 'widget_guarani_custom_posts', __( 'Posts from a Category', 'guarani' ), $widget_ops );
		$this->alt_option_name = 'widget_guarani_custom_posts';

		add_action( 'save_post', array(&$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache' ) );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array An array of standard parameters for widgets in this theme
	 * @param array An array of settings for this widget instance
	 * @return void Echoes it's output
	 **/
	function widget( $args, $instance ) {

		if ( isset( $cache ) && !is_array( $cache ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = null;

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		extract( $args, EXTR_SKIP );

		// ID da categoria
		$category = (int) $instance['category'];
		
		// Não havendo título, usamos o nome da categoria
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? get_category( $category )->name : $instance['title'], $instance, $this->id_base );

		// Número de posts
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 5;
 			
 		// Is a feature?
 		$is_feature = isset( $instance['feature'] ) ? $instance['feature'] : false;
		
		$category_args = array(
			'cat'					=> $category,
			'posts_per_page'		=> ( $is_feature ) ? 1 : $number, // If is a feature, override the previous number of posts
			'post_status' 			=> 'publish',
			'ignore_sticky_posts'	=> true
		);
		
		$category_posts = new WP_Query( $category_args );

		if ( $category_posts->have_posts() ) :
			echo $before_widget;
			
			echo $before_title;
			echo '<a href="' . get_category_link( $category ) . '" title="' . sprintf( __( 'View all posts filed under %s', 'guarani' ), get_category( $category )->name ) . '">' . $title . '</a>';
			echo $after_title;
			
			if ( $is_feature ) :
			
				while ( $category_posts->have_posts() ) : $category_posts->the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>			
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="entry-image">
							<?php the_post_thumbnail( 'small-feature' ); ?>
						</figure>
					<?php endif; ?>
					<h2 class="entry-title">
						<?php the_title(); ?>
					</h2>
					</a>
					<div class="entry-content">
						<?php the_excerpt(); ?>
						<a href="<?php the_permalink(); ?>" class="more-link"><?php _e( 'Continue reading', 'guarani' ); ?></a>
					</div>
				</article>
				<?php
				endwhile;
				
			else : ?>
			
				<ul>
					<?php while ( $category_posts->have_posts() ) : $category_posts->the_post(); ?>
					<li>
						<a href="<?php the_permalink() ?>" title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a>
					</li>
					<?php endwhile; ?>
				</ul>
				
			<?php
			endif; // is_feature
			
			// Final do widget
			echo $after_widget;

			// Reinicia o postdata
			wp_reset_postdata();

		
		endif; // if ( have_posts() )

	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 **/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['category'] = (int)( $new_instance['category'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['feature'] = (bool) $new_instance['feature'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_guarani_custom_posts'] ) )
			delete_option( 'widget_guarani_custom_posts' );

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_guarani_custom_posts', 'widget' );
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 **/
	function form( $instance ) {
		$title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : '';
		$category = isset( $instance['category'] ) ? (int) $instance['category'] : 0;
		$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'guarani' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:', 'guarani' ); ?></label>
			<?php wp_dropdown_categories( 'name='.$this->get_field_name( 'category' ) . '&id=' . $this->get_field_id( 'category' ) . '&show_count=1&class=widefat&selected=' . $category );?>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _e( 'Number of posts to show:', 'guarani' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3" />
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['feature'], true ) ?> id="<?php echo $this->get_field_id( 'feature' ); ?>" name="<?php echo $this->get_field_name( 'feature' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'feature' ); ?>"><?php _ex( 'Show as a featured post (will override the number of posts option above to show only the last one)', 'guarani' ); ?></label><br />
        </p>
		
	<?php
	}
}

/**
 * Featured Page Widget
 * Feature a page, showing its excerpt and thumbnail
 *
 * @since Guarani 1.0
 */
class Guarani_Featured_Page_Widget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @return void
	 **/
	function Guarani_Featured_Page_Widget() {
		$widget_ops = array( 'classname' => 'widget_guarani_featured_page', 'description' => __( 'Feature a page, showing its excerpt and thumbnail', 'guarani' ) );
		$this->WP_Widget( 'widget_guarani_featured_page', __( 'Featured Page', 'guarani' ), $widget_ops );
		$this->alt_option_name = 'widget_guarani_featured_page';

		add_action( 'save_post', array(&$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache' ) );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array An array of standard parameters for widgets in this theme
	 * @param array An array of settings for this widget instance
	 * @return void Echoes it's output
	 **/
	function widget( $args, $instance ) {
	
		global $post;

		if ( isset( $cache ) && !is_array( $cache ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = null;

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		extract( $args, EXTR_SKIP );

		// Page ID
		if ( isset( $instance['page'] ) )
		{
			$page = (int) $instance['page'];
			$post = get_page( $page );
		}
		
		if ( $post ) {
		
			setup_postdata( $post );
			
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? get_the_title() : $instance['title'], $instance, $this->id_base );
			
			echo $before_widget;
			echo $before_title;
			echo '<a href="' . get_permalink() . '">' . $title . '</a>';
			echo $after_title;
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>			
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="entry-image">
						<?php the_post_thumbnail( 'small-feature' ); ?>
					</figure>
				<?php endif; ?>
				</a>
				<div class="entry-content">
					<?php the_excerpt(); ?>
					<a href="<?php the_permalink(); ?>" class="more-link"><?php _e( 'Continue reading', 'guarani' ); ?></a>
				</div>
			</article>
			
			<?php
			
			// Final do widget
			echo $after_widget;

			// Reinicia o postdata
			wp_reset_postdata();

		
		} // if ( have_posts() )

	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 **/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['page'] = (int)( $new_instance['page'] );
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_guarani_featured_page'] ) )
			delete_option( 'widget_guarani_featured_page' );

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_guarani_featured_page', 'widget' );
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 **/
	function form( $instance ) {
		$title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : '';
		$page = isset( $instance['page'] ) ? (int) $instance['page'] : 0;
		?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'guarani' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'page' ); ?>"><?php _e( 'Page:', 'guarani' ); ?></label>
			<?php
			wp_dropdown_pages( array(
				'id' => $this->get_field_id('page'),
				'name' => $this->get_field_name('page'),
				'selected' => $page,
			) );
			?>
		</p>
		
	<?php
	}
}

/**
 * Tag cloud widget class with extra option for number os taxonomies to show
 *
 * @since Guarani 1.0
 */
class Guarani_Tag_Cloud_Reloaded_Widget extends WP_Widget {

        function __construct() {
                $widget_ops = array( 'description' => __( "Your most used tags in cloud format") );
                parent::__construct('tag_cloud', __('Tag Cloud'), $widget_ops);
        }

        function widget( $args, $instance ) {
                extract($args);
                $current_taxonomy = $this->_get_current_taxonomy($instance);
                if ( !empty($instance['title']) ) {
                        $title = $instance['title'];
                } else {
                        if ( 'post_tag' == $current_taxonomy ) {
                                $title = __('Tags');
                        } else {
                                $tax = get_taxonomy($current_taxonomy);
                                $title = $tax->labels->name;
                        }
                }
                
                // Número de posts
                if ( !array_key_exists('number', $instance) && ! $number = absint( $instance['number'] ) )
                	$number = 45;
                
                $title = apply_filters('widget_title', $title, $instance, $this->id_base);

                echo $before_widget;
                if ( $title )
                        echo $before_title . $title . $after_title;
                echo '<div class="tagcloud">';
                wp_tag_cloud( apply_filters('widget_tag_cloud_args', array('taxonomy' => $current_taxonomy, 'number' => $number) ) );
                echo "</div>\n";
                echo $after_widget;
        }

        function update( $new_instance, $old_instance ) {
                $instance['title'] = strip_tags(stripslashes($new_instance['title']));
                $instance['taxonomy'] = stripslashes($new_instance['taxonomy']);
                $instance['number'] = (int) $new_instance['number'];
                return $instance;
        }

        function form( $instance ) {
                $current_taxonomy = $this->_get_current_taxonomy($instance);
                $number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 45;
?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
        <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>
        
        <p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _e( 'Number of taxonomies to show:', 'guarani' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3" />
		</p>
        
        <p><label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy:') ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>">
        <?php foreach ( get_taxonomies() as $taxonomy ) :
                                $tax = get_taxonomy($taxonomy);
                                if ( !$tax->show_tagcloud || empty($tax->labels->name) )
                                        continue;
        ?>
                <option value="<?php echo esc_attr($taxonomy) ?>" <?php selected($taxonomy, $current_taxonomy) ?>><?php echo $tax->labels->name; ?></option>
        <?php endforeach; ?>
        </select></p><?php
        }

        function _get_current_taxonomy($instance) {
                if ( !empty($instance['taxonomy']) && taxonomy_exists($instance['taxonomy']) )
                        return $instance['taxonomy'];

                return 'post_tag';
        }
}
?>