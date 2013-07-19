<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php if ( ! empty( $preamble ) ) : ?><p class="tt-preamble"><?php echo $preamble; ?></p><?php endif; ?>
<ol class="tweets">
<?php foreach( $tweets AS $tweet ) : ?>
	<li class="<?php echo esc_attr( $tweet->twit_uid ); ?>">
		<div class="avatar">
			<a target="_blank" href="<?php echo esc_url( $tweet->twit_link ); ?>"><img src="<?php echo esc_url( $tweet->twit_pic ); ?>" alt="<?php echo esc_attr( $tweet->twit_name ); ?>"/></a>
		</div>
		<div class="msg">
			<span class="twit"><a target="_blank" href="<?php echo esc_url( $tweet->twit_link ); ?>"><?php echo esc_html( $tweet->twit_name ); ?></a>:</span>
			<span class="msgtxt"><?php echo $tweet->content; ?></span>
		</div>
	    <div class="info">
			<a target="_blank" class="tweet-link" href="<?php echo esc_url( $tweet->link ); ?>" title="<?php __( 'View tweet', 'twitter-tracker' ); ?>"><?php echo esc_html( $tweet->time_since() );  ?></a>
		</div>
	    <p class="clearleft"></p>
	</li>
<?php endforeach; ?>
</ol>
<?php echo $html_after; ?>