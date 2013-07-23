<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<input type="hidden" name="tt_query_action" value="1" />
<?php wp_nonce_field( 'tt_query', '_tt_query_nonce' ); ?>

<p class="tt_query">
	<label for="tt_query">
		Twitter Tracker widget query: <br />
		<input class="large-text" type="text" name="tt_query" value="<?php echo esc_attr( $query ); ?>" id="tt_query" />
	</label><br />
	<small><?php _e( 'This term will override, for this page or post only, any value you have entered into the widget. Enter any search term that works on <a href="http://twitter.com/" target="_blank">Twitter Search</a>, here&apos;s some <a href="http://twitter.com/operators" target="_blank">help with the syntax</a>.', 'twitter-tracker' ) ?></small>
</p>

<p class="tt_username">
	<label for="tt_username">
		Twitter Profile Tracker widget username: <br />
		<input class="large-text" type="text" name="tt_username" value="<?php echo esc_attr( $username ); ?>" id="tt_username" />
	</label><br />
	<small><?php _e( 'This will override, for this page or post only, any username you have entered into the widget.', 'twitter-tracker' ) ?></small>
</p>
