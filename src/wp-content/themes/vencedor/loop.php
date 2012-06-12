
    <?php while ( have_posts()) : the_post(); ?>		
        <div id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>		  
            <h2 class="post-title"><a href="<?php the_permalink();?>" title="<?php the_title();?>"><?php the_title();?></a></h2>
            <p class="post-meta">
            <?php global $authordata;
            $authorlink = sprintf(
                '<a href="%1$s" title="%2$s">%3$s</a>',
                get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
                esc_attr( sprintf( __( 'Posts by %s', 'temauolhost' ), get_the_author() ) ),
                get_the_author()
            ); ?>
                <?php printf(__('By %s on %s at %s.', 'temauolhost'), $authorlink, get_the_time( get_option( 'date_format') ), get_the_time() );  ?>
            </p>
            <div class="post-content clearfix">    
                <?php if ( has_post_thumbnail() ) : ?> 
                  <?php the_post_thumbnail(); ?>				 
                <?php endif; ?>
                <?php if (is_search() || is_archive()): ?>
                    <?php the_excerpt(); ?>
                <?php else: ?>
                    <?php the_content(__('Continue reading &raquo;', 'temauolhost')); ?>
                <?php endif; ?>
                
            </div>
            <!-- .post-content -->
            <div class="post-footer clearfix">
            <p class="post-meta alignleft"><?php _e('Categories:', 'temauolhost'); ?> <?php the_category(', '); ?><br /><?php the_tags(); ?></p>
            <p class="comments-number alignright"><a href="<?php comments_link(); ?>"><?php comments_number('0','1','%'); ?></a></p>
            </div>		        
            <?php comments_template(); ?>
            <!-- comentários -->
        </div>
        <!-- .post -->
    <?php endwhile; ?>
    <?php if ( $wp_query->max_num_pages > 1 ) : ?>
        <div class="navigation">
        <div class="alignleft"><?php next_posts_link(__('&laquo; Previous posts', 'temauolhost')); ?></div>
        <div class="alignright"><?php previous_posts_link(__('Next posts &raquo;', 'temauolhost')); ?></div>
        </div>
        <!-- #navigation -->
    <?php endif; ?>			
