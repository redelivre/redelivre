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
	                    
	                    // Lista os temas de intervenção
	                    $temas = get_terms( 'temas-de-intervencao', 'hide_empty=0' );
	                    if( $temas ) : ?>
	                    
	                    <div id="temas">
	                    	<?php foreach( $temas as $tema ) : ?>
	                    	<div class="box-tema">
		                    	<div class="tema">
		                    		<h2 class="<?php echo $tema->slug; ?>"><a href="<?php echo get_term_link( $tema, 'temas-de-intervencao' ); ?>" title="<?php echo $tema->name; ?>"><span><?php echo $tema->name; ?></span></a></h2>
		                    		<?php /*<img src="<?php bloginfo( 'stylesheet_directory' ); ?>/images/temas-<?php echo $tema->slug; ?>.png" alt="<?php echo $tema->name; ?>" /> 
		                    		<img src="<?php bloginfo( 'stylesheet_directory' ); ?>/images/icone-saude.png" alt="<?php echo $tema->name; ?>" />*/?>
		                    		<p><?php echo $tema->description; ?></p>
		                    	</div>
		                    </div>
	                    	<?php endforeach; ?>
	                    </div>
	                    
	                    <?php
	                    endif;
	                    
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