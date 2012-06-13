<?php 
if($_POST && isset($_POST['save-mobilize']) && wp_verify_nonce($_POST['save-mobilize'], 'save-mobilize')){
    update_option('mobilize', $_POST['mobilize']);
}


$option = get_option('mobilize');
$option = is_array($option) ? $option : array();
?>
<style>
    div.section { min-height:100px; }
</style>

<script type="text/javascript">
(function($){
    var $doc = $(document);
    
    $doc.ready(function(){
        $('div.section').each(function(){
            if($(this).find('h3 input').is(':checked'))
                $(this).find('.section-content').show();
            else
                $(this).find('.section-content').hide();
        });
        
        $('h3 input').click(function(){
            if($(this).is(':checked'))
                $('#'+$(this).data('section')+' .section-content').slideDown();
            else
                $('#'+$(this).data('section')+' .section-content').slideUp();
        });
    });
    
})(jQuery)
</script>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2>Configurações da Página de Mobilização</h2>
    <p class="description">Texto descritivo deste menu.</p>
    
    <form method="post">
        <?php wp_nonce_field('save-mobilize','save-mobilize') ?>
        <div id="mobilize-banners" class="section">
            <h3><label><input type="checkbox" name="mobilize[banner][active]" <?php if(isset($option['banner']['active'])) echo 'checked="checked"' ?> data-section="mobilize-banners" value="1"/> Banners</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                BANNERS
            </div>
        </div>

        <div id="mobilize-adesive" class="section">
            <h3><label><input type="checkbox" name="mobilize[adesive][active]" <?php if(isset($option['adesive']['active'])) echo 'checked="checked"' ?> data-section="mobilize-adesive" value="1"/> Adesive sua Foto</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                ADESIVE
            </div>
        </div>

        <div id="mobilize-redes" class="section">
            <h3><label><input type="checkbox" name="mobilize[redes][active]" <?php if(isset($option['redes']['active'])) echo 'checked="checked"' ?> data-section="mobilize-redes" value="1"/> Redes Sociais</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                REDES SOCIAIS
            </div>
        </div>

        <div id="mobilize-enviar" class="section">
            <h3><label><input type="checkbox" name="mobilize[envie][active]" <?php if(isset($option['envie']['active'])) echo 'checked="checked"' ?> data-section="mobilize-enviar" value="1"/> Enviar para um amigo</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                ENVIAR PARA UM AMIGO
            </div>
        </div>
        
        <input type="submit" name="submit" value="salvar configurações" />
    </form>
</div>