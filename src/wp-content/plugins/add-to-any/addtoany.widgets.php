<?php

/**
 * AddToAny WordPress Widgets
 */

/**
 * AddToAny Share widget class
 *
 * @since add-to-any .9.9.8
 */
class A2A_SHARE_SAVE_Widget extends WP_Widget {
	
	/** constructor */
	function __construct() {
		$widget_ops = array( 
			'description' => 'Share buttons for sharing your content.',
			'customize_selective_refresh' => true,
		);
		parent::__construct( '', 'AddToAny Share', $widget_ops );
		
		// Enqueue script if widget is active (appears in a sidebar) or if in Customizer preview.
		// is_customize_preview() @since 4.0.0
		if ( is_active_widget( false, false, $this->id_base ) || ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}
	
    public function enqueue_scripts() {
        wp_enqueue_script( 'addtoany-widget-init', plugins_url( 'addtoany.admin.js', __FILE__ ), array(), '0.1', true );
    }
	
	/** Backwards compatibility for A2A_SHARE_SAVE_Widget::display(); usage */
	public function display( $args = false ) {
		self::widget( $args, NULL );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args = array(), $instance ) {
	
		global $A2A_SHARE_SAVE_plugin_url_path;
		
		$defaults = array(
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '',
			'after_title' => '',
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		echo $before_widget;
		
		if ( isset( $instance ) && ! empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
			echo $before_title . $title . $after_title;
		}
		
		ADDTOANY_SHARE_SAVE_KIT( array(
			"use_current_page" => true,
		) );

		echo $after_widget;
	}
	
	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
	
	/**
	 * @param array $instance
	 */
	public function form( $instance ) {
		$title = isset( $instance ) && ! empty( $instance['title'] ) ? __( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<a href="options-general.php?page=addtoany"><?php _e('AddToAny Settings', 'add-to-any'); ?>...</a>
		</p>
		<?php
	}
	
}

/**
 * AddToAny Follow widget class
 *
 * @since add-to-any 1.6
 */
class A2A_Follow_Widget extends WP_Widget {
	
	/** constructor */
	function __construct() {
		$widget_ops = array( 
			'description' => 'Follow buttons link to your social media.',
			'customize_selective_refresh' => true,
		);
		parent::__construct( '', 'AddToAny Follow', $widget_ops );	
		
		// Enqueue script if widget is active (appears in a sidebar) or if in Customizer preview.
		// is_customize_preview() @since 4.0.0
		if ( is_active_widget( false, false, $this->id_base ) || ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}
	
	/**
	 * Enqueue a script with jQuery as a dependency.
	 */
    public function enqueue_scripts() {
        wp_enqueue_script( 'addtoany-widget-init', plugins_url( 'addtoany.admin.js', __FILE__ ), array('jquery'), '0.1', true );
    }

	/**
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args = array(), $instance ) {
	
		global $A2A_SHARE_SAVE_plugin_url_path;
		
		$defaults = array(
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '',
			'after_title' => '',
		);
		
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		echo $before_widget;
		
		if ( isset( $instance ) && ! empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
			echo $before_title . $title . $after_title;
		}
		
		$active_services = array();
		
		// See which services have IDs set
		$services = $this->get_follow_services();
		foreach ( $services as $code => $service ) {
			$code_id = $code . '_id';
			if ( ! empty( $instance[ $code_id ] ) ) {
				// Set ID value
				$active_services[ $code ] = array( 'id' => $instance[ $code_id ] );
			}
		}
		
		ADDTOANY_FOLLOW_KIT( array(
			'buttons' => $active_services,
		) );

		echo $after_widget;
	}
	
	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		// Accept service IDs
		$services = $this->get_follow_services();
		foreach ( $services as $code => $service ) {
			$code_id = $code . '_id';
			if ( isset( $new_instance[ $code_id ] ) ) {
				$instance[ $code_id ] = strip_tags( $new_instance[ $code_id ] );
			}
		}
		
		return $instance;
	}
	
	/**
	 * @param array $instance
	 */
	public function form( $instance ) {
		$services = $this->get_follow_services();
		
		$title = isset( $instance ) && ! empty( $instance['title'] ) ? __( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
<?php foreach ( $services as $code => $service ) : 
		$code_id = $code . '_id';
		$id_value = ! empty( $instance[ $code_id ] ) ? $instance[ $code_id ] : '';
		$label_text = 'feed' == $code ? sprintf( __('%s URL:'), $service['name'] ) : sprintf( __('%s ID:'), $service['name'] );
?>
		<p>
			<label for="<?php echo $this->get_field_id( $code_id ); ?>"><?php echo $label_text; ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( $code_id ); ?>" name="<?php echo $this->get_field_name( $code_id ); ?>" type="text" value="<?php echo esc_attr( $id_value ); ?>">
			<br>
			<small><?php echo str_replace( '${id}', '<u>ID</u>', $service['href'] ); ?></small>
		</p>
<?php endforeach; ?>
		<p>
			<a href="options-general.php?page=addtoany"><?php _e('AddToAny Settings', 'add-to-any'); ?>...</a>
		</p>
<?php
	}
	
	private function get_follow_services() {
		global $A2A_FOLLOW_services;
		
		// Make available services extensible via plugins, themes (functions.php), etc.
		$services = apply_filters( 'A2A_FOLLOW_services', $A2A_FOLLOW_services );
		$services = ( is_array( $services ) ) ? $services : array();
		
		return $services;
	}
	
}
