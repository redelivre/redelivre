<?php
Mobilize::saveSettings();

$option = Mobilize::getOption();
?>
<style>
    div.section { min-height:100px; }
    
    #mobilize-redes .section-content label { display:inline-block; width: 80px;}
    #mobilize-redes .section-content input { width:400px; }
    #mobilize-enviar .section-content input { width:400px; }
    #mobilize-enviar .section-content textarea { width:400px; height:300px; }
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
    <p class="description">Texto descritivo deste menu. <b>MELHORAR TODOS OS TEXTOS</b></p>

    <form method="post" enctype="multipart/form-data">
        <?php Mobilize::printSettingsNonce() ?>
        <div id="mobiliza-general" class="section">
            <h3>Configurações gerais</h3>
            <p><label><input type="checkbox" name="mobilize[general][active]" <?php echo Mobilize::isActive('general') ? 'checked="checked"' : ''; ?> data-section="mobilize-general" /> Habilitar a página de mobilização</label></p>
            <p><label><input type="checkbox" name="mobilize[general][menuItem]" <?php echo (isset($option['general']) && isset($option['general']['menuItem']) && $option['general']['menuItem']) ? 'checked="checked"' : ''; ?> data-section="mobilize-general" /> Exibir link para a página de mobilização no menu</label></p>
        </div>
        <div id="mobilize-banners" class="section">
            <h3><label><input type="checkbox" name="mobilize[banners][active]" <?php if(Mobilize::isActive('banners'))  echo 'checked="checked"' ?> data-section="mobilize-banners" value="1"/> Banners</label></h3>
            <p class="description">Texto descritivo desta seção. Explicar tamanho e formato da imagem?</p>
            <div class="section-content">
                <?php Mobilize::printErrors('banners'); ?>
                <label>
                    Subir banner:
                    <input type="file" name="banner[]" />
                </label><br/>
                <?php if($banner_url = Mobilize::getBannerURL(125)):?>
                    <img src="<?php echo $banner_url; ?>" style="max-width:780px;"/>
                <?php endif; ?>
            </div>
        </div>

        <div id="mobilize-adesive" class="section">
            <h3><label><input type="checkbox" name="mobilize[adesive][active]" <?php if(Mobilize::isActive('adesive')) echo 'checked="checked"' ?> data-section="mobilize-adesive" value="1"/> Adesive sua Foto</label></h3>
            <p class="description">Texto descritivo desta seção. Explicar sobre ser uma máscara etc.</p>

            <div class="section-content">
                <?php Mobilize::printErrors('adesive'); ?>
                <label>
                    Subir máscara:
                    <input type="file" name="adesive[]" />
                </label><br/>
                <?php if($adesive_url = Mobilize::getAdesiveURL()):?>
                    <img src="<?php echo $adesive_url; ?>" style="max-width:780px;"/>
                <?php endif; ?>
            </div>
        </div>

        <div id="mobilize-redes" class="section">
            <h3><label><input type="checkbox" name="mobilize[redes][active]" <?php if(Mobilize::isActive('redes'))  echo 'checked="checked"' ?> data-section="mobilize-redes" value="1"/> Redes Sociais</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                <?php Mobilize::printErrors('redes'); ?>
                <small>Entre com os endereços do seu perfil nas redes sociais.</small><br/>
                
                <label for="rede-1">Facebook:</label> <input id="rede-1" type="text" name="mobilize[redes][facebook]" value="<?php echo @htmlentities($option['redes']['facebook']) ?>"/><br/>
                
                <label for="rede-2">Twitter:</label> <input id="rede-2" type="text" name="mobilize[redes][twitter]" value="<?php echo @htmlentities($option['redes']['twitter']) ?>"/><br/>
                
                <label for="rede-3">Google+:</label> <input id="rede-3" type="text" name="mobilize[redes][google]" value="<?php echo @htmlentities($option['redes']['google']) ?>"/><br/>
                
            </div>
        </div>

        <div id="mobilize-enviar" class="section">
            <h3><label><input type="checkbox" name="mobilize[envie][active]" <?php if(Mobilize::isActive('envie')) echo 'checked="checked"' ?> data-section="mobilize-enviar" value="1"/> Enviar para um amigo</label></h3>
            <p class="description">Texto descritivo desta seção.</p>

            <div class="section-content">
                <?php Mobilize::printErrors('enviar'); ?>
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

        <input type="submit" name="submit" class="button-primary" value="Salvar configurações" />
    </form>
</div>