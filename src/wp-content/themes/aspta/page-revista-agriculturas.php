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

<div <?php thematic_post_class(); ?>>
					
					<?php 
	                
	                // creating the post header
	                thematic_postheader();
	                
	                ?>
	                
					<div class="entry-content">
	
	                    <?php
	                    
	                    the_content();
	                    
	                    
	                    /*
	                     * Lista as revistas
	                     */
	                    query_posts( array (
	                    	'post_type'		=> 'revista',
	                    	'post_parent'	=> 0,
	                    	'posts_per_page'=> -1,
	                    	'paged'			=> $paged
	                    ));
	                    
	                    if ( have_posts() ) :
	                    
	                    	$i = 1; ?> 
	                    	<div id="revistas">
	                    
	                    	<?php
	                    	
	                    	while ( have_posts() ) : the_post();
	                    
	                    		if ( $i == 1 ) :
	                    		
	                    			if ( $paged == 0 ) : ?>
	                    		
		                    		<div id="ultima-revista">
		                    			<h4>Última edição</h4>
		                    			<div class="entry-thumbnail">
		                    				<a href="<?php echo get_permalink( $revista->ID ); ?> ">
		                    					<?php the_revista_thumbnail( 'large' ); ?>
		                    				</a>
		                    			</div>
		                    			<div class="entry-revista">
		                    				<h2 class="entry-title"><a href="<?php echo get_permalink( $revista->ID ); ?>"><?php echo strip_tags(get_the_title()); ?></a></h2>
		                    				<div class="entry-summary">
		                    					<?php the_excerpt(); ?>
		                    				</div>
		                    			</div><!-- .entry-revista -->
		                    		</div>
		                    		<?php endif; ?>
	                    		
                                        <h4>Edições anteriores</h4>
	                    		<div id="revistas-anteriores">
	                    		
	                    		<?php else : ?>
	                    		
                    				<a href="<?php echo get_permalink( $revista->ID ); ?> "><?php the_revista_thumbnail( 'medium' ); ?></a>
                    			
	                    		<?php
	                    		
	                    		endif;
	                    		$i++;
	                    		
	                    	endwhile; ?>
	                    	
	                    	<?php thematic_navigation_below(); ?>
	                    	
	                    		</div><!-- #revistas-anteriores -->
	                    		
	                    <?php endif; ?>
	                    
	                    	</div><!-- #revistas -->
	                    
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


    
    // calling footer.php
    get_footer();

?>
