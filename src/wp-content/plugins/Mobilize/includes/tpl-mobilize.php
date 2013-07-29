<?php
    $optionsPluginRedesSociais = get_option('campanha_social_networks');
    $optionsRedesSociais = Mobilize::optionRedesSociais();

    // variables
    $blogurl = urlencode(get_bloginfo('url'));
    $options = Mobilize::getOption();

    // add css style
    wp_enqueue_style('mobilize', get_bloginfo('url').'/wp-content/plugins/Mobilize/css/mobilize.css');
    
    // header insertion
    get_header();

    // global variables
    global $user_ID;

    function hex_color_type($type){
        switch ($type) {
            case '1':
                return Mobilize_moip::getOption('mm_color_institucional');
                break;
            case '2':
                return Mobilize_moip::getOption('mm_color_projeto');
                break;
            case '3':
                return Mobilize_moip::getOption('mm_color_outros');
                break;
        }
    }

    function contribuicao_type($type) {
        switch ($type) {
            case '1':
                return ' - Institucional - ';
                break;
            case '2':
                return ' - Projeto - ';
                break;
            case '3':
                return ' - Outro - ';
                break;
            default:
                return ' - ';
                break;
        }
    }

    function mount_desc($type, $descricao)
    {
        return 'Contribuicao'.contribuicao_type($type).Mobilize::tiracento($descricao);
    }

    if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa1') == 'true') {
        $color1 = 'style="color: '.hex_color_type(Mobilize_moip::getOption('mm_tipo_contribuicaofixa1')).';"';
    }

    if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa2') == 'true') {
        $color2 = 'style="color: '.hex_color_type(Mobilize_moip::getOption('mm_tipo_contribuicaofixa2')).';"';
    }

    if (Mobilize_moip::getOption('mm_checkbox_contribuicaofixa3') == 'true') {
        $color3 = 'style="color: '.hex_color_type(Mobilize_moip::getOption('mm_tipo_contribuicaofixa3')).';"';
    }
?>

<section id="mobilize-content">

    <?php /*if (Mobilize::isActive('general')):*/ ?>
        <h1><?php echo !empty($options['general']['title']) ? $options['general']['title'] : 'Apoie este projeto'; ?></h1>
        <div class="section-description">
            <p><?php echo isset($options['general']['description']) && !empty($options['general']['description']) ? $options['general']['description'] : 'Nesta página, você encontra diferentes formas de mobilização e apoio.'; ?></p>
        </div>
        <?php if(Mobilize_moip::getOption('mm_checkbox_status') == 'true'){ ?>
        <section class="mobilize-widget clearfix">
                <h6>Contribuição</h6>
                <p><?php if(trim(Mobilize_moip::getOption('mm_descricao')) == '') { echo Mobilize_moip::TEXTO_DESCRITIVO_PADRAO_MOIP; } else { echo Mobilize_moip::getOption('mm_descricao'); } ?></p>
                
                <?php if(Mobilize_moip::getOption('mm_checkbox_contribuicaofixa1') == 'true') { ?>
                <div class="contribution-wrapper" style="padding: 0;">
                    <form target="_blank" class="form-moip1" action="https://www.moip.com.br/PagamentoMoIP.do" method="post">
                        <!-- input data -->
                        <input type="hidden" name="id_carteira" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
                        <input type="hidden" name="valor" value="<?php echo str_replace(array('R$', ',', '.', ' '), array('', '', '', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa1')); ?>">
                        <input type="hidden" name="nome" value="<?php echo mount_desc(Mobilize_moip::getOption('mm_tipo_contribuicaofixa1'), Mobilize_moip::getOption('mm_descricao_contribuicaofixa1')); ?>">
                        <!-- /input data -->

                        <div class="contribution">
                            <p class="description"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa1'); ?></p>

                            <h3 class="price" <?php echo $color1; ?>><?php echo str_replace(array('R$', ' '), array('<span>R$</span>', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa1')); ?></h3>

                            <a href="#" class="link-moip1 link-contribua">Contribuir</a>
                        </div>
                    </form>
                </div>
                <?php } ?>

                <?php if(Mobilize_moip::getOption('mm_checkbox_contribuicaofixa2') == 'true') { ?>
                <div class="contribution-wrapper">
                    <form target="_blank" class="form-moip2" action="https://www.moip.com.br/PagamentoMoIP.do" method="post">
                        <div class="contribution">
                            <!-- input data -->
                            <input type="hidden" name="id_carteira" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
                            <input type="hidden" name="valor" value="<?php echo str_replace(array('R$', ',', '.', ' '), array('', '', '', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa2')); ?>">
                            <input type="hidden" name="nome" value="<?php echo mount_desc(Mobilize_moip::getOption('mm_tipo_contribuicaofixa2'), Mobilize_moip::getOption('mm_descricao_contribuicaofixa2')); ?>">
                            <!-- /input data -->

                            <p class="description"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa2'); ?></p>

                            <h3 class="price" <?php echo $color2; ?>><?php echo str_replace(array('R$', ' '), array('<span>R$</span>', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa2')); ?></h3>

                            <a href="#" class="link-moip2 link-contribua">Contribuir</a>
                        </div>
                    </form>
                </div>
                <?php } ?>

                <?php if(Mobilize_moip::getOption('mm_checkbox_contribuicaofixa3') == 'true') { ?>
                <div class="contribution-wrapper">
                    <form target="_blank" class="form-moip3" action="https://www.moip.com.br/PagamentoMoIP.do" method="post">
                        <div class="contribution">
                            <!-- input data -->
                            <input type="hidden" name="id_carteira" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
                            <input type="hidden" name="valor" value="<?php echo str_replace(array('R$', ',', '.', ' '), array('', '', '', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa3')); ?>">
                            <input type="hidden" name="nome" value="<?php echo mount_desc(Mobilize_moip::getOption('mm_tipo_contribuicaofixa3'), Mobilize_moip::getOption('mm_descricao_contribuicaofixa3')); ?>">
                            <!-- /input data -->

                            <p class="description"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaofixa3'); ?></p>

                            <h3 class="price" <?php echo $color3; ?>><?php echo str_replace(array('R$', ' '), array('<span>R$</span>', ''), Mobilize_moip::getOption('mm_valor_contribuicaofixa3')); ?></h3>

                            <a href="#" class="link-moip3 link-contribua">Contribuir</a>
                        </div>
                    </form>
                </div>
                <?php } ?>
                
                <?php if(Mobilize_moip::getOption('mm_checkbox_contribuicaolivre') == 'true') { ?>
                <div class="contribution-wrapper">
                    <form target="_blank" class="form-moip4" action="https://www.moip.com.br/PagamentoMoIP.do" method="post">
                        <div class="contribution">
                            <!-- input data -->
                            <input type="hidden" name="id_carteira" value="<?php echo Mobilize_moip::getOption('mm_carteira'); ?>">
                            <input class="valor-livre-output" type="hidden" name="valor">
                            <input type="hidden" name="nome" value="<?php echo mount_desc('', Mobilize_moip::getOption('mm_descricao_contribuicaolivre')); ?>">
                            <!-- /input data -->

                            <p class="description"><?php echo Mobilize_moip::getOption('mm_descricao_contribuicaolivre'); ?></p>

                            <div class="price-livre">
                                <input class="valor-livre-input" type="text" placeholder="Digite seu valor" style="border: 1px solid #DDD;">
                            </div>

                            <a href="#" class="link-moip4 link-contribua">Contribuir</a>
                        </div>
                    </form>
                </div>
                <?php } ?>
        </section>
        <?php } ?>

        <?php if (Mobilize::isActive('redes')) { ?>
            <section id="mobilize-redes" class="mobilize-widget clearfix">
                <?php $redes = get_option('campanha_social_networks'); ?>
                <h6>Redes sociais</h6>
                <p class="section-description">
                    <?php echo $options['redes']['description']; ?>
                </p>
                <div class='clearfix'>
                    <?php if (!is_null($optionsRedesSociais['redes_facebook_page']) && !empty($optionsRedesSociais['redes_facebook_page'])) { ?>
                        <a class="mobilize-button mobilize-facebook" href="<?php echo $optionsRedesSociais['redes_facebook_page']; ?>">Facebook</a>
                    <?php } else if(is_array($optionsPluginRedesSociais) && isset($optionsPluginRedesSociais['facebook-page']) && !empty($optionsPluginRedesSociais['facebook-page'])) { ?>
                        <a class="mobilize-button mobilize-facebook" href="<?php echo $optionsPluginRedesSociais['facebook-page']; ?>">Facebook</a>
                    <?php } ?>

                    <?php if (!is_null($optionsRedesSociais['redes_twitter']) && !empty($optionsRedesSociais['redes_twitter'])) { ?>
                        <a class="mobilize-button mobilize-twitter" href="<?php echo $optionsRedesSociais['redes_twitter']; ?>">Twitter</a>
                    <?php } else if(is_array($optionsPluginRedesSociais) && isset($optionsPluginRedesSociais['twitter']) && !empty($optionsPluginRedesSociais['twitter'])) { ?>
                        <a class="mobilize-button mobilize-twitter" href="<?php echo $optionsPluginRedesSociais['twitter']; ?>">Twitter</a>
                    <?php } ?>

                    <?php if (!is_null($optionsRedesSociais['redes_google']) && !empty($optionsRedesSociais['redes_google'])) { ?>
                        <a class="mobilize-button mobilize-google" href="<?php echo $optionsRedesSociais['redes_google']; ?>">Google +</a>
                    <?php } else if(is_array($optionsPluginRedesSociais) && isset($optionsPluginRedesSociais['google']) && !empty($optionsPluginRedesSociais['google'])) { ?>
                        <a class="mobilize-button mobilize-google" href="<?php echo $optionsPluginRedesSociais['google']; ?>">Google +</a>
                    <?php } ?>
                        
                    <?php if (!is_null($optionsRedesSociais['redes_youtube']) && !empty($optionsRedesSociais['redes_youtube'])) { ?>
                        <a class="mobilize-button mobilize-youtube" href="<?php echo $optionsRedesSociais['redes_youtube']; ?>">Youtube</a>
                    <?php } else if(is_array($optionsPluginRedesSociais) && isset($optionsPluginRedesSociais['youtube']) && !empty($optionsPluginRedesSociais['youtube'])) { ?>
                        <a class="mobilize-button mobilize-youtube" href="<?php echo $optionsPluginRedesSociais['youtube']; ?>">Youtube</a>
                    <?php } ?>
                </div>

                <div class="clearfix"></div>
            </section>
            <!-- #mobilize-redes -->
        <?php } ?>

        <?php if (Mobilize::isActive('banners')): ?>
            <section id="mobilize-banners" class="mobilize-widget clearfix">
                <h6>Banners</h6>
                <p class="section-description">
                    <?php echo $options['banners']['description']; ?>
                </p>
                <?php for ($i = 0; $i < Mobilize::getNumBanners(); $i++): ?>
                    <?php if (Mobilize::getBannerURL(250) != '') { ?>
                    <div class="mobilize-banners" style="padding-left: 0;">
                        <!-- banner de 250x250 -->
                        <div class="image-banner">
                            <img class="image-banner-1" src="<?php echo Mobilize::getBannerURL(250); ?>" alt="">
                        </div>
                        <textarea class="code"><?php echo htmlentities('<a href="' . get_bloginfo('url') . '/mobilize"><img src="' . Mobilize::getBannerURL(250, $i) . '" /></a>') ?></textarea>
                        <input class="code" type="text" readonly="readonly" value="<?php the_permalink(); ?>" />                   
                    </div>
                    <div class="mobilize-banners">
                        <!-- banner de 200x200 -->
                        <div class="image-banner">
                            <img class="image-banner-2" src="<?php echo Mobilize::getBannerURL(200); ?>" alt="">
                        </div>
                        <textarea class="code"><?php echo htmlentities('<a href="' . get_bloginfo('url') . '/mobilize"><img src="' . Mobilize::getBannerURL(200, $i) . '" /></a>') ?></textarea>
                        <input class="code" type="text" readonly="readonly" value="<?php the_permalink(); ?>" />                   
                    </div>
                    <div class="mobilize-banners">
                        <!-- banner de 125x125 -->
                        <div class="image-banner">
                            <img class="image-banner-3" src="<?php echo Mobilize::getBannerURL(125); ?>" alt="">
                        </div>
                        <textarea class="code"><?php echo htmlentities('<a href="' . get_bloginfo('url') . '/mobilize"><img src="' . Mobilize::getBannerURL(125, $i) . '" /></a>') ?></textarea>
                        <input class="code" type="text" readonly="readonly" value="<?php the_permalink(); ?>" />   
                    </div>
                    <?php } ?>
                <?php endfor; ?>
            </section>
            <!-- #mobilize-banners -->
        <?php endif; ?>


        <?php if (Mobilize::isActive('adesive')): ?>

            <section id="mobilize-sticker" class="mobilize-widget clearfix">
                <h6>Adesive sua foto!</h6>
                <p class="section-description">
                    <?php echo $options['adesive']['description']; ?>
                </p>

                <div class="sticked-avatar"><img class="sticker"src="<?php echo Mobilize::getAdesiveURL(); ?>" alt="" /><img width="80" height="80" src="<?php echo get_bloginfo('url'); ?>/wp-content/plugins/Mobilize/img/mistery_man.jpg" /></div>
                <form method="post" enctype="multipart/form-data" target="_blank">
                    <?php Mobilize::printAdesiveNonce() ?>
                    <p>Envie sua foto: <input type="file" name="photo" /> <input class="mobilize-button" type="submit" value="Adesivar foto" /></p>
                </form>
            </section>
            <!-- #mobilize-sticker -->
        <?php endif; ?>

        <?php if (Mobilize::isActive('envie')) { ?>

            <?php $success = Mobilize::enviarEmails(); ?>

            <section id="mobilize-sendto" class="mobilize-widget clearfix">
                <a name="send-to"></a>
                <h6>Envie para um amigo!</h6>
                <p class="section-description">
                    <?php echo $options['envie']['description']; ?>
                </p>
                <!-- #standard-message -->
                <div id="mobilize-sendto-form">
                    <?php if (true === $success) { ?>
                        <p class="success">Mensagem Enviada!</p>
                    <?php } else if (false === $success) { ?>
                        <p class="error">Houve um erro ao enviar sua mensagem, tente novamente!</p>
                    <?php } ?>
                    <form method="post" action="#send-to">

                        <?php Mobilize::printEnvieNonce(); ?>

                        <input id="sender-name" type="text" value="<?php echo isset($_POST['sender-name']) ? $_POST['sender-name'] : 'Seu nome'; ?>" name="sender-name" onfocus="if (this.value == '<?php echo "Seu nome" ?>') this.value = '';" onblur="if (this.value == '') {this.value = '<?php echo "Seu nome"; ?>';}" />
                        <input id="sender-email" type="text" value="<?php echo isset($_POST['sender-email']) ? $_POST['sender-email'] : 'Seu endereço de e-mail'; ?>" name="sender-email" onfocus="if (this.value == 'Seu endereço de e-mail') this.value = '';" onblur="if (this.value == '') {this.value = 'Seu endereço de e-mail';}" />

                        <input id="recipient-email" type="text" value="<?php echo isset($_POST['recipient-email']) ? $_POST['recipient-email'] : 'Adicione até 10 endereços de email separados por vírgula'; ?>" name="recipient-email" onfocus="if (this.value == 'Adicione até 10 endereços de email separados por vírgula') this.value = '';" onblur="if (this.value == '') {this.value = 'Adicione até 10 endereços de email separados por vírgula';}" />


                        <textarea id="sender-message" name="sender-message" onfocus="if (this.value == 'Adicione sua própria mensagem ou deixe em branco') this.value = '';" onblur="if (this.value == '') {this.value = 'Adicione sua própria mensagem ou deixe em branco';}"><?php echo isset($_POST['sender-message']) ? $_POST['sender-message'] : 'Adicione sua própria mensagem ou deixe em branco'; ?></textarea>
                        
                        <div id="standard-message">
                            <div class="message-container"><?php echo nl2br($options['envie']['message']); ?></div>
                        </div>

                        <input id="submit" class="mobilize-enviar-button" type="submit" value="Enviar" name="submit" />
                    </form>
            </section>
            <!-- /mobilize-sendto -->
        <?php } ?>

</section>
<!-- #mobilize-content -->        

<!-- Javascripts -->
<script src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize/js/price.js'; ?>"></script>
<script src="<?php echo get_bloginfo('url').'/wp-content/plugins/Mobilize/js/template.js'; ?>"></script>
<!-- /Javascripts -->

<?php get_footer(); ?>
