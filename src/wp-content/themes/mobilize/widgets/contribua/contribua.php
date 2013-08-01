<?php

class Widget_Contribua extends WP_Widget {

	private $path;
	public function __construct() {
		parent::__construct(
	 		'widget-contribua',
			'Widget Contribua',
			array('description' => __( 'widget de contribui&ccedil;&atilde;o', 'mobilize' )) // Args
		);
		
		$this->path = get_template_directory() . '/widgets/contribua';
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
		$instance['descricao'] = $new_instance['descricao'];
		
		return $instance;
	}

}

function widget_contribua_register(){
	register_widget('Widget_Contribua');
}

add_action('widgets_init', 'widget_contribua_register');
?>