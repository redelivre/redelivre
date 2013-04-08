<?php
/**
 * Custom template tags for Guarani
 *
 * @package Guarani
 * @since Guarani 1.0
 */
 
if ( ! function_exists( 'guarani_user_social' ) ) :
/**
 * Create social links plus feed & author links
 * 
 * @param object $user Os dados do usuário
 *
 * @since Guarani 1.0
 */
function guarani_user_social( $user ) {

	$userdata = get_userdata( $user );

	$output = '';

	$social_data = array(
		array(
			'name'			=> 'facebook',
			'url'			=> esc_url( $userdata->facebook ),
			'title'			=> __( 'Profile on Facebook', 'guarani' )
		),
		array(
			'name'			=> 'twitter',
			'url'			=> esc_url( $userdata->twitter ),
			'title'			=> __( 'Profile on Twitter', 'guarani' ),
			'icon-class'	=> 'icon-twitter'
		),
		array(
			'name'			=> 'googleplus',
			'url'			=> esc_url( $userdata->googleplus ),
			'title'			=> __( 'Profile on Google+', 'guarani' )
		),
		array(
			'name'			=> 'globe',
			'url'			=> esc_url( $userdata->user_url ),
			'title'			=> __( 'Personal website', 'guarani' )
		)
	);
	
	$output = '<div class="author-contact member-contact">';
	
	// We only show author link when not in author page
	if ( ! is_author() && count_user_posts( $userdata->ID ) > 0 )
		$output .= '<a href="' . get_author_posts_url( $userdata->ID ) . '" title="' . sprintf( __( 'All posts by %s', 'f451' ), $userdata->display_name ) . '" class="icon-archive"><span class="assistive-text">Posts</span></a>';
	
	foreach ( $social_data as $social ) {
		if ( ! empty( $social['url'] ) ) {
			$output .= '<a title="' . $social['title'] . '" href="' . $social['url'] . '" class="icon-' . $social['name'] . '">';
			$output .= '<span class="assistive-text">' . $social['name'] . '</span>';
			$output .= '</a>';	
		}
	}
	
	$output .= '</div><!-- .author-contact -->';			

	echo $output;

}
endif; //guarani_user_social

if ( ! function_exists( 'guarani_content_nav' ) ) :
/**
 * Display navigation to next/previous pages when applicable
 *
 * @since Guarani 1.0
 */
function guarani_content_nav( $nav_id ) {
	global $wp_query, $post;

	// Don't print empty markup on single pages if there's nowhere to navigate.
	if ( is_single() ) {
		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous )
			return;
	}

	// Don't print empty markup in archives if there's only one page.
	if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
		return;

	$nav_class = 'site-navigation paging-navigation';
	if ( is_single() )
		$nav_class = 'site-navigation post-navigation';

	?>
	<nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
		<h1 class="assistive-text"><?php _e( 'Post navigation', 'guarani' ); ?></h1>

	<?php if ( is_single() ) : // navigation links for single posts ?>

		<?php previous_post_link( '<div class="previous">%link</div>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'guarani' ) . '</span> %title' ); ?>
		<?php next_post_link( '<div class="next">%link</div>', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'guarani' ) . '</span>' ); ?>

	<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

		<?php if ( get_next_posts_link() ) : ?>
		<div class="previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'guarani' ) ); ?></div>
		<?php endif; ?>

		<?php if ( get_previous_posts_link() ) : ?>
		<div class="next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'guarani' ) ); ?></div>
		<?php endif; ?>

	<?php endif; ?>

	</nav><!-- #<?php echo $nav_id; ?> -->
	<?php
}
endif; // guarani_content_nav

if ( ! function_exists( 'guarani_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Guarani 1.0
 */
function guarani_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'guarani' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'guarani' ), ' ' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment-container">
			<footer class="comment-meta">
				<div class="comment-avatar">
					<?php echo get_avatar( $comment, 96 ); ?>
				</div>
				<div class="comment-author vcard">
					<cite class="fn"><?php echo get_comment_author_link(); ?></cite>
					<?php if ( $comment->comment_approved == '0' ) : ?>
						<span class="comment-awaiting"><?php _e( 'Your comment is awaiting moderation.', 'guarani' ); ?></span>
					<?php endif; ?>
					<time pubdate datetime="<?php comment_time( 'c' ); ?>">
					<?php
						/* translators: 1: date, 2: time */
						printf( __( '<span>on</span> %1$s at %2$s', 'guarani' ), get_comment_date(), get_comment_time() ); ?>
					</time>
				</div><!-- .comment-author .vcard -->

				<div class="comment-extras">
					<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>" class="icon-link"><span class="assistive-text"><?php _e( 'Copy the permalink', 'guarani' ); ?></span></a>
					<?php edit_comment_link( __( '<span class="icon-edit">(Edit)</span>', 'guarani' ), '' ); ?>
				</div><!-- .comment-meta-data -->
			</footer><!-- .comment-meta -->

			<div class="comment-content"><?php comment_text(); ?></div>

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => '<span aria-hidden="true" class="icon-reply"></span><span class="assistive-text">Reply</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->

	<?php
			break;
	endswitch;
}
endif; // ends check for guarani_comment()

if ( ! function_exists( 'guarani_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @since Guarani 1.0
 */
function guarani_posted_on() {
	echo '<span aria-hidden="true" class="icon-clock"></span>';
	printf( __( 'Posted on <a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a>', 'guarani' ),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);
	// Comments link
	if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
	<span class="comments-link"><span aria-hidden="true" class="icon-comment"></span><?php comments_popup_link( __( 'Leave a comment', 'guarani' ), __( '1 Comment', 'guarani' ), __( '% Comments', 'guarani' ) ); ?></span>
	<?php
	endif;
	// Display the 'Last updated' time
	/*
	if ( get_the_date() != get_the_modified_date() ) {
		echo '<time class="entry-update">';
		printf( __( 'Updated on %s', 'guarani' ), get_the_modified_date() );
		echo '</time>';
	}
	*/
	
}
endif;

/**
 * Display video as a post feature, above the title
 * 
 * @since Guarani 1.0
 */
function guarani_featured_video() {
	
	global $post; ?>
	
	<figure class="entry-image">
		<?php
		if ( $featured_video = get_post_meta( $post->ID, '_guarani_featured_video', true ) ) {
			$featured_video_embed = wp_oembed_get( $featured_video, array( 'width' => 680 ) );
			echo '<div class="featured-video">' . $featured_video_embed . '</div>';		
		}
		?>
	</figure><!-- .entry-image -->
	
	<?php
}

/**
 * Returns true if a blog has more than 1 category
 *
 * @since Guarani 1.0
 */
function guarani_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so guarani_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so guarani_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in guarani_categorized_blog
 *
 * @since Guarani 1.0
 */
function guarani_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'guarani_category_transient_flusher' );
add_action( 'save_post', 'guarani_category_transient_flusher' );