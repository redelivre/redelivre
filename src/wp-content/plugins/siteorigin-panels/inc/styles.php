<?php
/**
 * Code to handle the row styling
 */

/**
 * Get all the row styles.
 *
 * @return array An array defining the row fields.
 */
function siteorigin_panels_style_get_fields(){
	static $fields = false;

	if($fields === false) {
		$fields = array();
		$styles = apply_filters( 'siteorigin_panels_row_styles', array() );
		if(!empty($styles)){
			$fields['class'] = array(
				'name' => __('Class', 'siteorigin-panels'),
				'type' => 'select',
				'default' => '',
				'options' => wp_parse_args( $styles, array('' => __( 'Default', 'siteorigin-panels' ) ) ),
			);
		}

		$fields = apply_filters( 'siteorigin_panels_row_style_fields', $fields );
	}

	return (array) $fields;
}

function siteorigin_panels_style_dialog_form(){
	$fields = siteorigin_panels_style_get_fields();

	if(empty($fields)) {
		_e("Your theme doesn't provide any visual style fields. ");
		return;
	}

	foreach($fields as $name => $attr) {

		echo '<p>';
		echo '<label>' . $attr['name'] . '</label>';

		switch($attr['type']) {
			case 'select':
				?>
				<select name="panelsStyle[<?php echo esc_attr($name) ?>]" data-style-field="<?php echo esc_attr($name) ?>" data-style-field-type="<?php echo esc_attr($attr['type']) ?>">
					<?php foreach($attr['options'] as $ov => $on) : ?>
						<option value="<?php echo esc_attr($ov) ?>"><?php echo esc_html($on) ?></option>
					<?php endforeach ?>
				</select>
				<?php
				break;

			case 'checkbox' :
				?>
				<label class="siteorigin-panels-checkbox-label">
					<input type="checkbox" name="panelsStyle[<?php echo esc_attr($name) ?>]" data-style-field="<?php echo esc_attr($name) ?>" data-style-field-type="<?php echo esc_attr($attr['type']) ?>" />
					Enabled
				</label>
				<?php
				break;

			case 'number' :
				?><input type="number" name="panelsStyle[<?php echo esc_attr($name) ?>]" data-style-field="<?php echo esc_attr($name) ?>" data-style-field-type="<?php echo esc_attr($attr['type']) ?>" /> <?php
				break;

			default :
				?><input type="text" name="panelsStyle[<?php echo esc_attr($name) ?>]" data-style-field="<?php echo esc_attr($name) ?>" data-style-field-type="<?php echo esc_attr($attr['type']) ?>" /> <?php
				break;
		}

		echo '</p>';
	}
}

/**
 * Check if we're using a color in any of the style fields.
 *
 * @return bool
 */
function siteorigin_panels_style_is_using_color(){
	$fields = siteorigin_panels_style_get_fields();

	foreach($fields as $id => $attr) {
		if(isset($attr['type']) && $attr['type'] == 'color' ) {
			return true;
		}
	}
	return false;
}

/**
 * Convert the single string attribute of the grid style into an array.
 *
 * @param $panels_data
 * @return mixed
 */
function siteorigin_panels_style_update_data($panels_data){
	if(empty($panels_data['grids'])) return $panels_data;

	for($i = 0; $i < count($panels_data['grids']); $i++) {

		if( isset($panels_data['grids'][$i]['style']) && is_string($panels_data['grids'][$i]['style']) ){
			$panels_data['grids'][$i]['style'] = array('class' => $panels_data['grids'][$i]['style']);
		}
	}
	return $panels_data;
}
add_filter('siteorigin_panels_data', 'siteorigin_panels_style_update_data');
add_filter('siteorigin_panels_prebuilt_layout', 'siteorigin_panels_style_update_data');

/**
 * Sanitize all the data that's come from post data
 *
 * @param $panels_data
 */
function siteorigin_panels_style_sanitize_data($panels_data){
	$fields = siteorigin_panels_style_get_fields();

	if(empty($fields)) return $panels_data;
	if(empty($panels_data['grids']) || !is_array($panels_data['grids'])) return $panels_data;

	for( $i = 0; $i < count($panels_data['grids']); $i++ ) {

		foreach($fields as $name => $attr) {
			switch($attr['type']) {
				case 'checkbox':
					// Convert the checkbox value to true or false.
					$panels_data['grids'][$i]['style'][$name] = !empty($panels_data['grids'][$i]['style'][$name]);
					break;

				case 'number':
					$panels_data['grids'][$i]['style'][$name] = intval($panels_data['grids'][$i]['style'][$name]);
					break;

				case 'url':
					$panels_data['grids'][$i]['style'][$name] = esc_url_raw($panels_data['grids'][$i]['style'][$name]);
					break;

				case 'select' :
					// Make sure the value is in the options
					if(!in_array($panels_data['grids'][$i]['style'][$name], array_keys($attr['options']))) {
						$panels_data['grids'][$i]['style'][$name] = false;
					}
					break;
			}
		}
	}

	return $panels_data;
}
add_filter('siteorigin_panels_panels_data_from_post', 'siteorigin_panels_style_sanitize_data');