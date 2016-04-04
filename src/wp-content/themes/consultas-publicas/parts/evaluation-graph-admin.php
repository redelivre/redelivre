<?php

// exibe o gráfico da votação na página de relatórios no admin

if (current_user_can('manage_options')) :
    $evaluation_type = get_theme_option('evaluation_type');
    
    if ($evaluation_type == 'percentage') : ?>
        <div id="evaluation_bars" class="clear evaluation_container" style="display: none;">
            <?php evaluation_build_bars_graph($item->ID); ?>
        </div>
    <?php elseif ($evaluation_type == 'average') : ?>
        <div id="evaluation_scale" class="clear evaluation_container" style="display: none;">
            <?php evaluation_build_scale_graph($item->ID); ?>
        </div>
    <?php endif; ?>
<?php endif; ?>