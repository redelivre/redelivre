<?php 

/*  
	Copyright 2009 Simon Wheatley

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/**
 * Extends the WP_Widget base class in ways I find handy.
 */
class TwitterTracker_SW_Widget extends WP_Widget {

	// TEMPLATING FUNCTIONS
	
	// Fields for the admin form

	function input_text( $label, $var, $value, $note = false, $class = 'widefat' )
	{
		?>
		<p>
			<label for="<?php echo $this->get_field_id( $var ); ?>"><?php echo $label; ?> 
				<input class="widefat" id="<?php echo $this->get_field_id( $var ); ?>" name="<?php echo $this->get_field_name( $var ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
			</label>
			<?php if ( $note ) { ?>
				<br /><small><?php echo $note; ?></small>
			<?php } ?>
		</p>
		<?php
	}
	
	function input_conversational_mini_text( $label, $var, $value, $note = false, $class = 'widefat' )
	{
		?>
		<p><label for="<?php echo $this->get_field_id( $var ); ?>"><?php echo $label; ?></label>
			<input size="3" id="<?php echo $this->get_field_id( $var ); ?>" name="<?php echo $this->get_field_name( $var ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" /><br/>
			<?php if ( $note ) { ?>
				<br /><small><?php echo $note; ?></small>
			<?php } ?>
		</p>
		<?php
	}
	
	function input_checkbox( $label, $var, $value, $note = false, $class = 'widefat' )
	{
		$checked = ( $value ) ? ' checked="checked" ' : '';
		?>
		<p><input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( $var ); ?>" name="<?php echo $this->get_field_name( $var ); ?>" <?php echo $checked; ?> value="1" />
			<label for="<?php echo $this->get_field_id( $var ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php if ( $note ) { ?>
				<br /><small><?php echo $note; ?></small>
			<?php } ?>
		</p>
		<?php
	}

	function input_radios( $label, $var, $options, $selected, $note = false )
	{
		?>
		<p class="widget_inputs-<?php echo esc_attr( $var ); ?>">
			<?php echo esc_html( $label ); ?><br />
			<?php foreach ( $options as $i => $option ) : ?>
				<label for="<?php echo $this->get_field_id( $var ); ?>_<?php echo esc_attr( $i ); ?>">
					<input 
					type="radio" 
					name="<?php echo $this->get_field_name( $var ); ?>" 
					id="<?php echo $this->get_field_id( $var ); ?>_<?php echo esc_attr( $i ); ?>" 
					value="<?php echo esc_attr( $i ); ?>" 
					<?php checked( $i, $selected ); ?>
					/> <?php echo esc_html( $option ); ?>
				</label><br />
			<?php endforeach; ?>
			<?php if ( $note ) : ?>
				<br /><small><?php echo $note; ?></small>
			<?php endif; ?>
		</p>
		<?php
	}

	function maybe_wp_kses( $value, $context ) {
		if ( current_user_can( 'unfiltered_html' ) )
			return $value;
		$allowed_html = apply_filters( 'tt_allowed_html', array(
			'a' => array( 'href' => true, 'title' => true, 'target' => true, 'class' => true, 'id' => true ),
			'em' => array( 'class' => true, 'id' => true ),
			'strong' => array( 'class' => true, 'id' => true ),
			'p' => array( 'class' => true, 'id' => true ),
			'br' => true,
		), $context );
		return wp_kses( $value, $allowed_html );
	}

	function maybe_strip_tags( $value ) {
		if ( current_user_can( 'unfiltered_html' ) )
			return $value;
		return strip_tags( $value );
	}

}

?>