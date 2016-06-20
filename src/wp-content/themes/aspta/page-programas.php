<?php

// calling the header.php
get_header();

// action hook for placing content above #container
thematic_abovecontainer();

?>

<div id="container"><?php thematic_abovecontent();

echo apply_filters( 'thematic_open_id_content', '<div id="content">' . "\n" );

// calling the widget area 'page-top'
get_sidebar('page-top');

the_post();

thematic_abovepost();

?>

<div
	id="post-<?php the_ID(); 
					echo '" ';
					if (!(THEMATIC_COMPATIBLE_POST_CLASS)) {
						post_class();
						echo '>';
					} else {
						echo 'class="';
						thematic_post_class();
						echo '">';
					}
	                
	                // creating the post header
	                thematic_postheader();
	                
	                ?>
	                
					<div class="entry-content">
	
	                    <?php
	                    
	                    the_content();
	                    
	                    $programas = get_pages( array(
	                    	'child_of'		=> $post->ID,
	                    	'sort_column'	=> 'menu_order',
	                    ));
	                    
	                    if ( $programas ) { ?>
	                    <div id="programas">
	                    	<?php
	                    	foreach ( $programas as $post ) {
	                    		setup_postdata($post);?>
	                    		<div class="programa">
	                    			<div class="post-thumbnail">
	                    				<a href="<?php the_permalink(); ?>"><?php echo the_post_thumbnail( 'programas' ); ?></a>
	                    			</div>
	                    			
	                    			<div class="post-content">
	                    				<h2><a href="<?php echo get_permalink( $programa->ID ); ?> "><?php echo strip_tags(get_the_title()); ?></a></h2>
	                    				<div class="entry-content"><?php the_excerpt();  ?></div>
	                    				<p class="veja-mais"><a href="<?php the_permalink(); ?>">Conheça o programa &rarr;</a></p>
	                    			</div><!-- .post-content -->
	                    		</div> 
	                    	<?php
	                    	}	
	                    } ?>
	                    </div><!-- #programas -->
	                    <?php
	                    
	                    wp_reset_query();
	                    
	                    wp_link_pages("\t\t\t\t\t<div class='page-link'>".__('Pages: ', 'thematic'), "</div>\n", 'number');
	                    
	                    edit_post_link(__('Edit', 'thematic'),'<span class="edit-link">','</span>') ?>
	
					</div><!-- .entry-content -->
				</div><!-- #post -->
	
	        <?php
	        
	        thematic_belowpost();
	        
	        // calling the widget area 'page-bottom'
	        get_sidebar('page-bottom');
	        
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