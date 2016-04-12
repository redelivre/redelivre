<?php

add_action('admin_menu', 'exportador_avaliacoes_menu');

function exportador_avaliacoes_menu() {
    if (get_theme_option('use_evaluation')) {
        add_submenu_page('theme_options', 'Exportar votos', 'Exportar avaliações', 'manage_options', 'exportador_avaliacoes', 'exportador_avaliacoes_page_callback_function');
    }
}

function exportador_avaliacoes_page_callback_function() {
?>
    <div class="wrap span-20">
        <h2><?php echo __('Exportar avaliações', 'consulta'); ?></h2>
        
        <p><?php _e('Utilize esta página para exportar uma tabela do Excel com todas as avaliações feitas pelos usuários nos objetos da consulta.', 'consulta'); ?></p>
        
        <form method="post" action="<?php echo get_template_directory_uri(); ?>/includes/exportador-avaliacoes-xls.php" class="clear prepend-top">
            <p class="clear prepend-top">
                <input type="submit" class="button-primary" value="Exportar" />
            </p>
        </form>
    </div>
    <?php 
}