<?php
class Widget_Jaiminho extends WP_Widget {
	function Widget_Jaiminho() {
		$widget_ops = array( 'classname' => 'widget_jaiminho', 'description' => __('O formulário para cadastro no Jaiminho', 'jaiminho') );
		$this->__construct( 'jaiminho', 'Jaiminho', $widget_ops );
		
	}
 
	function widget( $args, $instance ) {
		
		extract( $args );

		$title = array_key_exists('title', $instance)?
			apply_filters( 'widget_title',
					empty($instance['title']) ? __('Jaiminho') : $instance['title'] ) :
			'';
		$jaiminho_text = array_key_exists('jaiminho_text', $instance)?
			apply_filters( 'widget_text', $instance['jaiminho_text'], $instance ) :
			'';
		
		echo $before_widget;
		
		if ( $title ) echo $before_title . $title . $after_title;
		
		
		if ( array_key_exists('jaiminho_id', $instance)
				&& $jaiminho_id = (int) $instance['jaiminho_id'] ) { ?>
		
			<div class="jaiminho">
				<div class="jaiminho-excerpt"><?php echo wpautop($jaiminho_text); ?></div>
			
				<?php jaiminho( $jaiminho_id ); ?>
			</div><!-- .jaiminho -->
			
		<?php
		}
		else if ( current_user_can('manage_options') ){
			echo '<em>'.__('Não foi informado o ID da lista do Jaiminho. Preencha corretamente o campo do ID dentro do
			widget. Você pode encontrar o ID dentro do campo <code>value</code>, na primeira linha do
			formulário.', 'jaiminho').'</em>';
		}
		
		
		echo $after_widget;
	}
 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['jaiminho_id'] = (int) $new_instance['jaiminho_id'];
		if ( current_user_can('unfiltered_html') )
			$instance['jaiminho_text'] =  $new_instance['jaiminho_text'];
		else
			$instance['jaiminho_text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['jaiminho_text']) ) ); // wp_filter_post_kses() expects slashed
		$instance['filter'] = isset($new_instance['filter']);

		return $instance;
	}
 
	function form( $instance ) {
		$title = (array_key_exists('title', $instance) ?
			esc_attr($instance['title']) : '');
		$jaiminho_id = (array_key_exists('jaiminho_id', $instance) ?
				(int) $instance['jaiminho_id'] : 0);
		$jaiminho_text = (array_key_exists('jaiminho_text', $instance) ?
				format_to_edit($instance['jaiminho_text']) : '');
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		
		<p>
		<textarea class="widefat" rows="10" cols="15" id="<?php echo $this->get_field_id('jaiminho_text'); ?>" name="<?php echo $this->get_field_name('jaiminho_text'); ?>"><?php echo $jaiminho_text; ?></textarea>
		<small><?php _e('(Um texto para apresentação da ferramenta. Não é obrigatório)', 'jaiminho'); ?></small></p>

		<p><label for="<?php echo $this->get_field_id('jaiminho_id'); ?>"><?php _e('ID da lista:'); ?></label>
		<input id="<?php echo $this->get_field_id('jaiminho_id'); ?>" name="<?php echo $this->get_field_name('jaiminho_id'); ?>" type="text" value="<?php echo $jaiminho_id; ?>" size="3" /><br />
		<small><?php _e('(O valor presente em <code>value</code>, dentro do formulário gerado pelo Jaiminho)', 'jaiminho'); ?></small></p>
<?php
	}
}
?>
