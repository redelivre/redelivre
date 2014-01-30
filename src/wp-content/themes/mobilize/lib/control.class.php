<?php

class MobilizeControl extends WP_Customize_Control {
	protected function render_content() {
		if ($this->type === 'textarea') {
			?>
				<label>
					<span class="customize-control-title"><?php
						echo esc_html($this->label);
					?></span>
					<textarea <?php echo $this->link(); ?>><?php
						echo esc_attr($this->value());
					?></textarea>
				</label>
			<?php
		}
		else {
			parent::render_content();
		}
	}
}

?>
