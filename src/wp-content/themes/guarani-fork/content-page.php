<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package guarani
 * @since guarani 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'guarani' ), 'after' => '</div>' ) ); ?>
		<?php edit_post_link( __( 'Edit', 'guarani' ), '<span class="edit-link">', '</span>' ); ?>
	</div><!-- .entry-content -->

	<div class="entry-share cf">
	    <ul class="share-social cf">
	    	<?php  $post_permalink = get_permalink(); ?>
	    	<li><a class="share-twitter icon-twitter" title="<?php _e( 'Share on Twitter', 'guarani' ); ?>" href="http://twitter.com/intent/tweet?original_referer=<?php echo $post_permalink; ?>&text=<?php echo $post->post_title; ?>&url=<?php echo $post_permalink; ?>" rel="nofollow" target="_blank"><span class="assistive-text"><?php _e( 'Share on Twitter', 'guarani' ); ?></span></a></li>
	    	<li><a class="share-facebook icon-facebook" title="<?php _e( 'Share on Facebook', 'guarani' ); ?>" href="https://www.facebook.com/sharer.php?u=<?php echo $post_permalink; ?>" rel="nofollow" target="_blank"><span class="assistive-text"><?php _e( 'Share on Facebook', 'guarani' ); ?></span></a></li>
	    	<li><a class="share-googleplus icon-googleplus" title="<?php _e( 'Share on Google+', 'guarani' ); ?>" href="https://plus.google.com/share?url=<?php echo $post_permalink; ?>" rel="nofollow" target="_blank"><span class="assistive-text"><?php _e( 'Share on Google+', 'guarani' ); ?></span></a></li>
		</ul>
		<div class="share-shortlink">
			<span aria-hidden="true" class="icon-link"></span>
	    	<input type="text" value="<?php if ( $shortlink = wp_get_shortlink( $post->ID ) ) echo $shortlink; else the_permalink(); ?>" onclick="this.focus(); this.select();" readonly="readonly" />
		</div>
	</div>
</article><!-- #post-<?php the_ID(); ?> -->
