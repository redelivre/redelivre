<?php

class Widget_Banner extends WP_Widget {

	private $path;
	public function __construct() {
		parent::__construct(
	 		'widget-banner',
			'Widget Banner',
			array('description' => __( 'widget de banner na sidebar', '_mobilize' )) // Args
		);
		
		$this->path = get_template_directory() . '/widgets/banner';
	}

	public function widget( $args, $instance ) {
		extract($args);
		include $this->path . "/view.php";
	}

	public function form( $instance ) {
		include $this->path . '/form.php';
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['banner'] = $new_instance['banner'];
		
		return $instance;
	}

}

function widget_banner_register(){
	register_widget('Widget_Banner');
}

add_action('widgets_init', 'widget_banner_register');
?>