<?php get_header(); ?>
    <section id="main-section" class="wrap clearfix">
	    <div id="content" class="col-8">		
	    <?php if ( have_posts()) : the_post(); ?>		
		<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>
			<header>
				<h2 class="post-title"><a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php the_title();?></a></h2>
				<p class="post-meta">
					<?php global $authordata;
					$authorlink = sprintf(
						'<a href="%1$s" title="%2$s">%3$s</a>',
						get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
						esc_attr( sprintf( __( 'Posts by %s', 'blog01' ), get_the_author() ) ),
						get_the_author()
					); ?>
					<?php printf(__('By %s on %s at %s.', 'blog01'), $authorlink, get_the_time( get_option( 'date_format') ), get_the_time() );  ?>
				</p>
            </header>
		    <div class="post-content">			    
			<?php the_content(); ?>			
			<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'blog01' ), 'after' => '</div>' ) ); ?>
		    </div>
		    <!-- .post-content -->
		    <footer class="post-footer clearfix">
				<?php get_template_part('interaction'); ?>
				<p class="post-meta alignleft"><?php _e('Categories:', 'blog01'); ?> <?php the_category(', '); ?><br /><?php the_tags('Tags: ',', '); ?></p>
				<p class="comments-number alignright"><a href="<?php comments_link(); ?>"><?php comments_number('0','1','%'); ?></a></p>
				<?php if (is_attachment()):?>
					<p class="clear"><a class="voltar" href="<?php echo get_permalink( $post->post_parent ); ?>"><?php _e('&laquo; Go back to ', 'blog01'); ?>"<?php echo get_the_title( $post->post_parent ); ?>"</a>.</p>
				<?php endif; ?>
		    </footer>    
		    <?php comments_template(); ?>
		    <!-- comentários -->
		</article>
		<!-- .post -->
		<nav class="navigation">
		    <div class="alignleft"><?php next_post_link('%link',__('&laquo; Previous post', 'blog01')); ?></div>
		    <div class="alignright"><?php previous_post_link('%link',__('Next post &raquo;', 'blog01')); ?></div>
		</nav>
		<!-- #navigation -->				
		<?php else : ?>		
		    <p class="post"><?php _e('No results found.', 'blog01'); ?></p>
	    <?php endif; ?>
	    </div>
	    <!-- #content -->
	    <aside id="sidebar" class="col-4 clearfix">
			<?php get_sidebar(); ?>
	    </aside>
	    <!-- #sidebar -->      
    </section>
    <!-- #main-section -->
<?php get_footer(); ?>
