<?php
/**
 * @package Guarani
 * @since Guarani 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<h1 class="entry-title">
			<a href="<?php the_permalink(); ?>" title="<?php printf( __('Read, comment and share &ldquo;%s&rdquo;', 'f451'), the_title_attribute('echo=0') ); ?>" rel="bookmark">
	        	<?php the_title(); ?>
	        </a>
		</h1>
		<div class="entry-meta">
			<?php guarani_posted_on(); ?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'guarani' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->
	
	<footer class="entry-meta">
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
	    </div><!-- .entry-share -->
	    
	    <div class="entry-taxonomies">
		<?php
			/* translators: used between list items, there is a space after the comma */
			$category_list = get_the_category_list( __( ', ', 'guarani' ) );

			/* translators: used between list items, there is a space after the comma */
			$tag_list = get_the_tag_list( '', ', ' );

			if ( ! guarani_categorized_blog() ) {
				// This blog only has 1 category so we just need to worry about tags in the meta text
				if ( '' != $tag_list ) {
					$meta_text = __( 'This entry was tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'guarani' );
				} else {
					$meta_text = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'guarani' );
				}

			} else {
				// But this blog has loads of categories so we should probably display them here
				if ( '' != $tag_list ) {
					$meta_text = __( 'This entry was posted in %1$s and tagged %2$s.', 'guarani' );
				} else {
					$meta_text = __( 'This entry was posted in %1$s.', 'guarani' );
				}

			} // end check for categories on this blog

			printf(
				$meta_text,
				$category_list,
				$tag_list
			);
		?>
	    </div><!-- .entry-taxonomies -->
	</footer><!-- .entry-meta -->
</article><!-- #post-<?php the_ID(); ?> -->
