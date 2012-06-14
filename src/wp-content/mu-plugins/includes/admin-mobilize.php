<?php
if ($_POST && isset($_POST['save-mobilize']) && wp_verify_nonce($_POST['save-mobilize'], 'save-mobilize')) {
    update_option('mobilize', $_POST['mobilize']);
    
    if($_FILES)
        die(var_dump($_FILES));
}


$option = get_option('mobilize');
$option = is_array($option) ? $option : array();
?>
<style>
    div.section { min-height:100px; }
    
    #mobilize-redes .section-content label { display:inline-block; width: 80px;}
    #mobilize-redes .section-content input { width:400px; }
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

    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('save-mobilize', 'save-mobilize') ?>
        <div id="mobilize-banners" class="section">
            <h3><label><input type="checkbox" name="mobilize[banners][active]" <?php if (isset($option['banners']['active'])) echo 'checked="checked"' ?> data-section="mobilize-banners" value="1"/> Banners</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                subir um banner: <input type="file" name="banner" />
            </div>
        </div>

        <div id="mobilize-adesive" class="section">
            <h3><label><input type="checkbox" name="mobilize[adesive][active]" <?php if (isset($option['adesive']['active'])) echo 'checked="checked"' ?> data-section="mobilize-adesive" value="1"/> Adesive sua Foto</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                
            </div>
        </div>

        <div id="mobilize-redes" class="section">
            <h3><label><input type="checkbox" name="mobilize[redes][active]" <?php if (isset($option['redes']['active'])) echo 'checked="checked"' ?> data-section="mobilize-redes" value="1"/> Redes Sociais</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                <small>Selecione as Redes Sociais que deseja mostrar na seção mobilização e informe os endereços das mesmas.</small><br/>
                
                <label for="rede-1">Facebook:</label> <input id="rede-1" type="text" name="mobilize[redes][facebook]"/><br/>
                
                <label for="rede-2">Twitter:</label> <input id="rede-2" type="text" name="mobilize[redes][twitter]"/><br/>
                
                <label for="rede-3">Google+:</label> <input id="rede-3" type="text" name="mobilize[redes][google]"/><br/>
                
            </div>
        </div>

        <div id="mobilize-enviar" class="section">
            <h3><label><input type="checkbox" name="mobilize[envie][active]" <?php if (isset($option['envie']['active'])) echo 'checked="checked"' ?> data-section="mobilize-enviar" value="1"/> Enviar para um amigo</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">

                <p>
                    <label>Título da mensagem que será enviada por e-mail:<br/>
                        <input type="text" name="mobilize[envie][subject]" value="<?php echo @htmlentities(utf8_decode($option['envie']['subject'])); ?>"/>
                    </label>
                </p>
                <p>
                    <label>Mensagem que será enviada por e-mail:<br/>
                        <textarea name="mobilize[envie][message]" ><?php echo @htmlentities(utf8_decode($option['envie']['message'])); ?></textarea>
                    </label>
                </p>


            </div>
        </div>

        <input type="submit" name="submit" value="salvar configurações" />
    </form>
</div>