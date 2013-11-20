
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br/></div>
    <h2>Configurações da Página de Mobilização</h2>

    <form method="post" enctype="multipart/form-data">        
        <?php Mobilize::printSettingsNonce(); ?>
        
        <div id="mobiliza-general" class="section">
            <h3>Configurações gerais</h3>
            
            <?php if (is_plugin_active('contribua/contribua.php')){ ?>
            <p><label><input type="checkbox" name="mobilize[general][contribua]" data-section="mobilize-general" <?php if (isset($option['general']['contribua'])){ echo 'checked="checked"'; }  ?> /> Habilitar contribua</label></p>            
            <?php } ?>

            <p><label><input type="checkbox" name="mobilize[general][ocultarexplicacao]" data-section="mobilize-general" <?php if (isset($option['general']['ocultarexplicacao'])){ echo 'checked="checked"'; }  ?> /> Ocultar texto explicativo</label></p>
            <p class="section-description">
                <label>
                    Título desta página:<br>
                    <input size="40" name="mobilize[general][title]" type="text" value="<?php echo isset($option['general']['title']) ? $option['general']['title'] : ''; ?>">
                </label><br><br>
                <label>
                    Espaçamento lateral:<br>
                    <input size="5" name="mobilize[general][espacamento_lateral]" type="text" value="<?php echo isset($option['general']['espacamento_lateral']) ? $option['general']['espacamento_lateral'] : ''; ?>">
                </label><br><br>
                <label>Texto explicativo geral para o usuário:<br/>
                    <textarea name="mobilize[general][description]" rows="5" cols="80"><?php echo isset($option['general']['description']) ? htmlentities(utf8_decode($option['general']['description'])) : ''; ?></textarea>
                </label>
            </p>
        </div>

        <ul id="sortable">        
            <li>
                <div id="mobilize-redes" class="section">
                    <!--<div class="mobilize-clear"><a href="#" title="mover"><img width="15" src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize/img/move.png'; ?>" alt="mover"></a></div>-->
                    <h3><label><input type="checkbox" name="mobilize[redes][active]" <?php if (Mobilize::isActive('redes')) echo 'checked="checked"' ?> data-section="mobilize-redes" value="1"/> Redes Sociais</label></h3>          

                   <div class="section-content">
                        <?php Mobilize::printErrors('redes'); ?>
                        <p class="section-description">
                            <label>Texto explicativo desta seção para o usuário:<br>
                                <textarea name="mobilize[redes][description]" rows="5" cols="80"><?php echo htmlentities(utf8_decode($option['redes']['description'])) ?></textarea>
                            </label>
                        </p>

                        <p class="section-description">
                            <label>
                                Facebook Página <br>
                                <input type="text" size="50" placeholder="https://" name="redes-facebook-page" value="<?php echo $optionsRedesSociais['redes_facebook_page']; ?>">
                            </label>
                        </p>
                        <p class="section-label">
                            <description>
                                Twitter <br>
                                <input type="text" size="50" placeholder="https://" name="redes-twitter" value="<?php echo $optionsRedesSociais['redes_twitter']; ?>">
                            </label>
                        </p>

                        <p class="section-description">
                            <label>
                                Youtube <br>
                                <input type="text" size="50" placeholder="https://" name="redes-youtube" value="<?php echo $optionsRedesSociais['redes_youtube']; ?>">
                            </label>
                        </p>

                        <p class="section-description">
                            <label>
                                Google+ <br>
                                <input type="text" size="50" placeholder="https://" name="redes-google" value="<?php echo $optionsRedesSociais['redes_google']; ?>">
                            </label>
                        </p>
                        
                    </div>
                </div>
            </li>

            <li>
                <div id="mobilize-banners" class="section">
                    <!--<div class="mobilize-clear"><a href="#" title="mover"><img width="15" src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize/img/move.png'; ?>" alt="mover"></a></div>-->
                    <h3><label><input type="checkbox" name="mobilize[banners][active]" <?php if (Mobilize::isActive('banners')) echo 'checked="checked"' ?> data-section="mobilize-banners" value="1"/> Banners</label></h3>
                    <p class="mobilize-description">Carregue aqui um banner quadrado de 250x250 pixels que o sistema vai preparar automaticamente 3 tamanhos que seus apoiadores poderão colocar em sites, blogs ou mesmo nas redes sociais.</p>

                    <div class="section-content">
                        <?php Mobilize::printErrors('banners'); ?>
                        <p class="section-description">
                            <label>Texto explicativo desta seção para o usuário:<br/>
                                <textarea  rows="5" cols="80" name="mobilize[banners][description]"><?php echo htmlentities(utf8_decode($option['banners']['description'])) ?></textarea>
                            </label>
                        </p>

                        <label>
                            Subir banner:
                            <input type="file" name="banner[]" />
                        </label><br/>
                        <?php if ($banner_url = Mobilize::getBannerURL(125)) { ?>
                            <img src="<?php echo $banner_url; ?>" style="max-width:780px;"/>
                        <?php } ?>
                    </div>
                </div>
            </li>

            <li>
                <div id="mobilize-adesive" class="section">
                    <!--<div class="mobilize-clear"><a href="#" title="mover"><img width="15" src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize/img/move.png'; ?>" alt="mover"></a></div>-->
                    <h3><label><input type="checkbox" name="mobilize[adesive][active]" <?php if(Mobilize::isActive('adesive')) echo 'checked="checked"' ?> data-section="mobilize-adesive" value="1"/> Adesive sua Foto</label></h3>
                    <p class="mobilize-description">Carregue uma imagem pequena com seu nome e número, com isto seus apoiadores poderão adesivar suas fotos para colocar nas redes sociais. Suba uma imagem horizontal com, no mínimo, 150 pixels.</p>

                    <div class="section-content">
                        <?php Mobilize::printErrors('adesive'); ?>
                        <p class="section-description">
                            <label>Texto explicativo desta seção para o usuário:<br/>
                                <textarea  rows="5" cols="80" name="mobilize[adesive][description]"><?php echo htmlentities(utf8_decode($option['adesive']['description'])) ?></textarea>
                            </label>
                        </p>

                        <label>
                            Subir máscara:
                            <input type="file" name="adesive[]" />
                        </label><br/>
                        <?php if ($adesive_url = Mobilize::getAdesiveURL()): ?>
                            <img src="<?php echo $adesive_url; ?>" style="max-width:780px;"/>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
            
            <li>
                <div id="mobilize-enviar" class="section">
                    <!--<div class="mobilize-clear"><a href="#" title="mover"><img width="15" src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize/img/move.png'; ?>" alt="mover"></a></div>-->
                    <h3><label><input type="checkbox" name="mobilize[envie][active]" <?php if(Mobilize::isActive('envie')) echo 'checked="checked"' ?> data-section="mobilize-enviar" value="1"/> Enviar para um amigo</label></h3>
                    <p class="mobilize-description">Esta é uma mensagem padrão que seus apoiadores poderão enviar para várias pessoas. Insira o assunto da mensagem (ex: Eu apoio “Candidato X”) e um texto para a mensagem. Seja sucinto, mas passe sua mensagem.</p>
                    
                    <div class="section-content">
                        <?php Mobilize::printErrors('enviar'); ?>
                        <p class="section-description">
                            <label>Texto explicativo desta seção para o usuário:<br/>
                                <textarea  rows="5" cols="80" name="mobilize[envie][description]"><?php echo htmlentities(utf8_decode($option['envie']['description'])) ?></textarea>
                            </label>
                        </p>

                        <p>
                            <label>
                                Usuário de e-mail:<br>
                                <input size="40" name="mobilize[general][from_email]" type="text" value="<?php echo !empty($option['general']['from_email']) ? $option['general']['from_email'] : 'noreply@seudominio.com.br'; ?>">
                            </label>
                        </p>

                        <p>
                            <label>Assunto da mensagem que será enviada por e-mail:<br/>
                                <input size="60" type="text" name="mobilize[envie][subject]" value="<?php echo isset($option['envie']['subject']) ? htmlentities(utf8_decode($option['envie']['subject'])) : ''; ?>"/>
                            </label>
                        </p>
                        
                        <p>
                            <style>#mobilize-editor_iframe { height: 200px; }</style>
                            <label>Mensagem que será enviada por e-mail:<br/>
                                <?php wp_editor(html_entity_decode(isset($option['envie']['message']) ? $option['envie']['message'] : '', ENT_QUOTES, 'UTF-8'), 'editor_mobilize', array('textarea_name' => 'mobilize[envie][message]')); ?>
                            </label>
                        </p>
                    </div>
                </div>
            </li>
        </ul>
    
        <input type="submit" name="submit" class="button-primary" value="Salvar configurações" />
    </form> 
</div>

<script src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize/js/admin-mobilize.js'; ?>"></script>
