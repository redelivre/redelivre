<?php

    // calling the header.php
    get_header();

    // action hook for placing content above #container
    thematic_abovecontainer();

?>

		<div id="container">
			
			<?php thematic_abovecontent();
		
			echo apply_filters( 'thematic_open_id_content', '<div id="content">' . "\n" );
			
				the_post();
    	        
    	        // create the navigation above the content
				thematic_navigation_above();
		
    	        // calling the widget area 'single-top'
    	        get_sidebar('single-top');
    	        
    	        // action hook creating the single post
    	        thematic_abovepost(); ?>
			
				<div id="post-<?php the_ID();
					echo '" ';
					if (!(THEMATIC_COMPATIBLE_POST_CLASS)) {
						post_class();
						echo '>';
					} else {
						echo 'class="';
						thematic_post_class();
						echo '">';
					}
     				thematic_postheader(); ?>
					<div class="entry-content">
						<?php
						
						thematic_content();
						
						$edicoes = get_posts( array(
	                    'post_parent'	=> $post->ID,
	    	        	'post_type'		=> 'revista',
						numberposts		=>	-1,
						'posts_per_page'=> -1,
						'orderby' 		=> 'order',
	                    'order'			=> 'ASC'
	                    ));
		                    
					if ( $edicoes ) : ?>
					<div class="nesta-edicao">
		                <h2>Nesta edição</h2>
		                <ul>
		                   	<?php 
		                   	foreach ($edicoes as $post) :
		                   	setup_postdata($post); ?>
		                   	<li><a href="<?php echo get_permalink( $programa->ID ); ?> "><?php echo strip_tags(get_the_title()); ?></a>
					<?php $coauthors =  coauthors_posts_links( ', ', ' e ', ', por ', '', false ); 
						if ($coauthors != ", por ")
							echo $coauthors;
					?></li>
		                   	<?php endforeach; ?>
		                </ul>
		            </div>
					<?php endif;
					wp_reset_query();
					?>
						
						<?php wp_link_pages('before=<div class="page-link">' .__('Pages:', 'thematic') . '&after=</div>') ?>
					</div><!-- .entry-content -->
					<?php thematic_postfooter(); ?>
				</div><!-- #post -->
				<?php

				thematic_belowpost();
    	        
    	        
    	        // calling the widget area 'single-insert'
    	        get_sidebar('single-insert');
		
    	        // create the navigation below the content
				thematic_navigation_below();
		
    	        // calling the comments template
    	        thematic_comments_template();
		
    	        // calling the widget area 'single-bottom'
    	        get_sidebar('single-bottom');
    	        
    	        ?>
		
			</div><!-- #content -->
			
			<?php thematic_belowcontent(); ?> 
			
		</div><!-- #container -->
		
<?php 

    // action hook for placing content below #container
    thematic_belowcontainer();

    // calling the standard sidebar 
    thematic_sidebar();
    
    // calling footer.php
    get_footer();

?>
