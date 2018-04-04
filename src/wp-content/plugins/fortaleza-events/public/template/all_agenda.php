<?php get_header(); ?>
<div class="container_all_agenda">
<div class="all_agenda">
    <div class="inner_agenda">
        <div class="filter_agenda">
            <div class="filter_filed">
                <div class="first_row_filed">
                    <div class="classofication">
                        <?php
                        $args = array(
                            'orderby'           => 'name', 
                            'order'             => 'ASC',
                            'hide_empty'        => 0, 
                        );
                        $classification= get_terms('classifaction', $args);?>
                        <i class="fa fa-folder-o" aria-hidden="true"></i>
                        <select data-placeholder="CLASSIFICACÃO INDICATIVA" class="chosen-select" id="classification">
                            <option value=""></option>
                            <?php 
                                foreach ($classification as $classifications) {
                                    // print_R($cities);exit;
                                   echo '<option value="'.$classifications->term_id.'">'.$classifications->name.'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="start_time">
                         <?php
                        $args = array(
                            'orderby'           => 'name', 
                            'order'             => 'ASC',
                            'hide_empty'        => 0, 
                        );
                        $start_at= get_terms('start_at', $args);
                        ?>
			            <i class="fa fa-tags" aria-hidden="true"></i>
                        <select data-placeholder="HORÁRIO" class="chosen-select" id="start_time">
                            <option value=""></option>
                            <?php 
                                foreach ($start_at as $start_ats) {
                                    // print_R($cities);exit;
                                   echo '<option value="'.$start_ats->term_id.'">'.$start_ats->name.'</option>';
                                }
                            ?>
                        </select>   
                    </div>
                </div>
                <div class="second_row_filed">		  
                    <div class="start_on_date">
			            <i class="fa fa-calendar" aria-hidden="true"></i>
                        <input type="text" id="start_date" name="start_date" placeholder="DATA"/>    
                    </div>
		    <div class="event_title">
			 <i class="fa fa-search" aria-hidden="true"></i>
                         <input type="text" id="post_title" name="post_title" placeholder="TÍTULO"/>
                         <input type="hidden" id="ajax_url" value="<?php echo admin_url( 'admin-ajax.php' ); ?>">
                    </div>
                </div>
                
            </div>    
       </div>
        <?php 
            // The Query
                $args = array(
                     'post_type' => 'agenda',
                     'post_status' => 'publish',
                     'orderby'           => 'meta_value',
                     'meta_key'          => '_start_date',
                     'meta_type'         => 'DATE',
                     'order'             => 'DESC',
                     'posts_per_page' => -1
                );

                $the_query = new WP_Query( $args );
                $listing_layout=get_option('listing_layout');
                if($listing_layout=='three_colums'){
                    $class='one_Third';
                    $value=3;
                }elseif ($listing_layout=='two_colums') {
                    $class='one_half';
                    $value=2;
                }else{
                    $class='one_Third';
                    $value='3';
                }
            // The Loop
                echo '<div class="inner_rows">';
                echo '<div class="row">';
                $i = 0;
                if ( $the_query->have_posts() ) {
                        while ($the_query->have_posts() ) { $the_query->the_post(); ?>
                          <div class='<?php echo $class; ?> <?php if ($i % $value == 0){echo 'first';}?>' >
                                <div class="agenda_image" id="container_hover">
                                    <?php  $agenda_img=get_the_post_thumbnail_url(); 
                                    if(!empty($agenda_img)){?>
                                       <img src="<?php echo $agenda_img; ?>" class="image"/>
                                    <?php }else{?>
                                        <img src="<?php echo CWEB_WS_PATH1;?>/public/assets/img/dummy.jpg" class="image"/>
                                    <?php } ?>
				                    <div class="overlay_custom">
					               <div class="text_hover"><a href="<?php echo get_the_permalink(); ?>"><i class="fa fa-link" aria-hidden="true"></i></a></div>
				            </div>
                                </div>
                                <div class="agenda_title">
                                    <h1><a style="color:#c0392b;" href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></h1>                            
                                </div>
                                <div class="agenda_details">
                                    <div class="lang_details">
                                        <span class="lang_label">Data : </span>
                                        <?php
                                            $euroTimestamp=strtotime(get_post_meta(get_the_ID(),'_start_date',true));
                                            $start_date= date("d/m/Y", $euroTimestamp)
                                        ?>
                                        <span class="lang_value"><?php echo $start_date; ?></span>
                                    </div>
                                    <div class="classification_details">
                                        <span class="class_label"><?php echo get_post_meta(get_the_ID(),'_start_time', true); ?></span>
                                    </div>
                                </div>
                            </div> 
                            
                        <?php
                               $i++;
                                  if ($i % $value == 0) {
                                      echo '</div><div class="row">';}
                                } 
                    echo '</div>';
                    /* Restore original Post Data */
                    wp_reset_postdata();
                     } else {
                          echo 'No Agenda Available.';
                    }
            ?>
        </div>
         <div id="inner_ajax_rows"></div>  
    </div>    
</div>
</div>
<div class="clearfix" style="clear:both"></div>
<style>
    .container_all_agenda {
    width: 80%;
    margin: auto;
    max-width: 1080px;
    position: relative;
}
</style>   
<?php get_footer();?>
