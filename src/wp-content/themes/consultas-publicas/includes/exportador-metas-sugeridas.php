<?php

add_action('admin_menu', 'exportador_metas_sugeridas_menu');

function exportador_metas_sugeridas_menu() {
 
    add_submenu_page('edit.php?post_type=meta-sugerida', 'Exportar', 'Exportar', 'manage_options', 'exportador_metas_sugeridas', 'exportador_metas_sugeridas_page_callback_function');
    
   
}


function exportador_metas_sugeridas_page_callback_function() {
    
?>
  <div class="wrap span-20">
    <h2><?php echo __('Exportar Comentários', 'consulta'); ?></h2>

    <form method="post" action="<?php echo get_template_directory_uri(); ?>/includes/exportador-metas-sugeridas-xls.php" class="clear prepend-top">
      
      <div class="span-20 ">
      
        <div class="span-6 last">
          
          <h3>Selecione o período</h3>
          <label for="data_inicial"><strong>Data inicial (YYYY-MM-DD)</strong></label><br/>
          <input type="text" id="data_inicial" class="text seleciona_data" name="data_inicial" value="<?php echo htmlspecialchars($options['data_inicial']); ?>"/>
          <br/><br/>
          <label for="data_final"><strong>Data final (YYYY-MM-DD)</strong></label><br/>
          <input type="text" id="data_final" class="text seleciona_data" name="data_final" value="<?php echo htmlspecialchars($options['data_final']); ?>"/>
          <br/><br/>
          
          <script>
          
          //jQuery('.seleciona_data').datepicker();
          
          </script>
          
        </div>
      </div>
      
      <p class="textright clear prepend-top">
        <input type="submit" class="button-primary" value="Exportar" />
      </p>
    </form>
  </div>

<?php 

}
