<?php if (get_theme_option('use_evaluation') && consulta_get_number_alternatives() > 0) :
    $postId = get_the_ID();
    $evaluationOptions = get_theme_option('evaluation_labels');
    $userVote = str_replace('_label_', 'label_', get_user_vote($postId));
    $votes = get_votes($postId);
    $evaluation_type = get_theme_option('evaluation_type');
    ?>

    <div class="evaluation clearfix">
        <?php if (is_single()) : ?>
            <h3 class="subtitulo"><?php _e('Avaliação', 'consulta'); ?></h3>
        <?php endif; ?>

        <p><?php echo get_theme_option('evaluation_text'); ?></p>

        <?php if (get_theme_option('evaluation_public_results') || current_user_can('manage_options')) : ?>
            <?php if ($evaluation_type == 'percentage'): ?>
                <?php $perceVotes = consulta_get_votes_percentage($votes); ?>
                
                <div id="evaluation_bars" class="clear">
                    <h5><?php _e('Resultado até o momento', 'consulta'); ?></h5>
                    <?php
                    $ii = 0;
                    foreach ($evaluationOptions as $key => $value) :
                        if (empty($value)) {
                            break;
                        }
                        ?>
    
                        <div class="clear">
                            
                            <label><?php echo $value; ?>: <?php echo $votes[$ii]; ?> (<?php echo $perceVotes[$ii]; ?>%)</label>
                            <div id="evaluation_bar_bg" >
                                <div class="evaluation_bar" style="width: <?php echo $perceVotes[$ii]; ?>%;"></div>
                            </div>
                        </div>
                
                        <?php $ii ++;
                    endforeach; ?>
                </div>
            <?php elseif($evaluation_type == 'average'): ?>
                <?php $widthItem = consulta_get_width_item(); ?>
                <?php $numAlternatives = consulta_get_number_alternatives(); ?>
                <?php $average = consulta_get_votes_average($votes); ?>
                <?php $averageWidth =  ($average  * 100) / $numAlternatives; ?>
                
                <div id="evaluation_scale" class="clear">
                    <h5>Média de <?php echo array_sum($votes); ?> votos: <?php echo $average; ?></h5>
                    
                    <div id="evaluation_bar_bg" >
                        <div class="evaluation_bar" style="width: <?php echo $averageWidth; ?>%;"></div>
                    </div>
                    
                    <?php
                    $ii = 1;
                    foreach ($evaluationOptions as $key => $value) :
                        if (empty($value)) {
                            break;
                        }
                        ?>
                        
                        <div class="evaluation_average_label" style="width: <?php echo $widthItem; ?>%;">
                            <div class="evaluation_average_marker"></div>
                            <p><?php echo $ii, '. ', $value; ?></p>
                        </div>
                        <?php $ii++;
                    endforeach; ?>
                    
                    <div class="clear"></div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    
        <?php if (is_user_logged_in()): ?>
            <div id="user_evaluation">            
                <h5><?php _e('Minha avaliação', 'consulta'); ?></h5>
                
                <form id="object_evaluation">
                    <input type="hidden" id="post_id" name="post_id" value="<?php the_ID(); ?>" />    	
                    <?php foreach ($evaluationOptions as $key => $value) : ?>
                        <?php if (empty($value)) break; ?>
                        <input type="radio" id="<?php echo $key; ?>" name="object_evaluation" <?php checked($userVote === $key); ?> />
                        <label for="<?php echo $key; ?>"><?php echo $value; ?></label>
                        <br />
                    <?php endforeach; ?>
                </form>
            </div>
        <?php else: ?>
            <p><?php _e('Para avaliar é necessário se <a href="' . wp_login_url() . '?action=register">cadastrar</a> e efetuar o <a href="' . wp_login_url() . '">login</a>.', 'consulta'); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>