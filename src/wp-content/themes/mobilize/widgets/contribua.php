<?php

class Widget_Contribua extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'widgetcontribua', // Base ID
			'Widget Contribua', // Name
			array( 'description' => __( 'widget de contribui&ccedil;&atilde;o', '_mobilize' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {
		include get_template_directory() . "/widgets/contribua/view.php";
	}

	public function form( $instance ) {
		// outputs the options form on admin
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}

}


?>