<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix');?>>    
    <header>                       
        <h1 class="bottom"><?php the_title();?></h1>
        <p>
            <?php _e('By', 'SLUG'); ?> <?php the_author_posts_link(); ?> <?php _e('on', 'SLUG'); ?> 
            <time class="post-time" datetime="<?php the_time('Y-m-d'); ?>" pubdate><?php the_time( get_option('date_format') ); ?></time>
            <?php edit_post_link( __( 'Edit', 'SLUG' ), '| ', '' ); ?>
        </p>
    </header>
    <div class="post-content clearfix">
        <?php the_content(); ?>
        <?php wp_link_pages( array( 'before' => '<nav class="page-link">' . __( 'Pages:', 'SLUG' ), 'after' => '</nav>' ) ); ?>
    </div>
    <!-- .post-content -->
    <footer class="clearfix">
        <p class="taxonomies">
            <span><?php _e('Categories', 'SLUG'); ?>:</span> <?php the_category(', ');?><br />
            <?php the_tags('<span>Tags:</span> ', ', '); ?>
        </p>
        <div class='clearfix'>
            <div style='float:left; margin-right:10px;'>
                <div id="fb-root"></div><script src="http://connect.facebook.net/pt_BR/all.js#appId=111490612284967&amp;xfbml=1"></script><fb:like href="<?php the_permalink();?>" send="false" layout="button_count"  show_faces="false" action="like" font=""></fb:like>
            </div>
            <div style='float:left; margin-right:10px;'>
                <a href="http://twitter.com/share" class="twitter-share-button" data-text="" data-url="<?php the_permalink()?>" data-count="horizontal" data-via="catracalivre">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script> 
            </div>
            <div style='float:left;'>
                <g:plusone size="medium" href="<?php the_permalink(); ?>"></g:plusone>
                <!-- Place this tag after the last plusone tag -->
                <script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

            </div>
        </div>
    </footer>               
    <?php comments_template(); ?>
    <!-- comentários -->
</article>
<!-- .post -->
