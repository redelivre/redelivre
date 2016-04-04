<?php

add_action('admin_menu', 'exportador_objetos_sugeridos_menu');

function exportador_objetos_sugeridos_menu() {
    if (get_theme_option('allow_suggested')) {
        add_submenu_page('theme_options', 'Exportar objetos sugeridos', 'Exportar objetos sugeridos', 'manage_options', 'exportador_objetos_sugeridos', 'exportador_objetos_sugeridos_page_callback_function');
    }
}

function exportador_objetos_sugeridos_page_callback_function() {
?>
    <div class="wrap span-20">
        <h2><?php echo __('Exportar objetos sugeridos', 'consulta'); ?></h2>
        
        <p><?php _e('Utilize está página para exportar uma tabela do Excel com informações de todos os objetos sugeridos pelos usuários. Se desejar, é possível filtrar a lista para exportar apenas objetos criados num determinado período de tempo.', 'consulta'); ?></p>
        
        <form method="post" action="<?php echo get_template_directory_uri(); ?>/includes/exportador-objetos-sugeridos-xls.php" class="clear prepend-top">
            <div class="span-20 ">
                <div class="span-6 last">
                    <br/>
                    <input type="checkbox" name="periodo" id="period">
                    <label for="period">Exportar por período</label>
                    <div id="select_period" style="display: none;">
                        <br/>

                        <label for="data_inicial"><strong>Data inicial</strong></label><br/>
                        <input type="text" id="data_inicial" class="text select_date" name="data_inicial" />
                        <br/><br/>
                        <label for="data_final"><strong>Data final</strong></label><br/>
                        <input type="text" id="data_final" class="text select_date" name="data_final" />
                    </div>
                </div>
            </div>
              
            <p class="clear prepend-top">
                <input type="submit" class="button-primary" value="Exportar" />
            </p>
        </form>
    </div>
    <?php 
}