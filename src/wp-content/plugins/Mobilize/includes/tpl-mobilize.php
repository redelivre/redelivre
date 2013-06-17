<?php
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
?>

<section id="mobilize-content">

	<?php /*if (Mobilize::isActive('general')):*/ ?>
        <h1>Apoie este projeto</h1>
        <div class="section-description">
            <p><?php echo isset($options['general']['description']) && !empty($options['general']['description']) ? $options['general']['description'] : 'Nesta página, você encontra diferentes formas de mobilização e apoio.'; ?></p>
        </div>

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
                    <?php } ?>

                    <?php if (!is_null($optionsRedesSociais['redes_twitter']) && !empty($optionsRedesSociais['redes_twitter'])) { ?>
                        <a class="mobilize-button mobilize-twitter" href="<?php echo $optionsRedesSociais['redes_twitter']; ?>">Twitter</a>
                    <?php } ?>

                    <?php if (!is_null($optionsRedesSociais['redes_google']) && !empty($optionsRedesSociais['redes_google'])) { ?>
                        <a class="mobilize-button mobilize-google" href="<?php echo $optionsRedesSociais['redes_google']; ?>">Google +</a>
                    <?php } ?>
                        
                    <?php if (!is_null($optionsRedesSociais['redes_youtube']) && !empty($optionsRedesSociais['redes_youtube'])) { ?>
                        <a class="mobilize-button mobilize-youtube" href="<?php echo $optionsRedesSociais['redes_youtube']; ?>">Youtube</a>
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
                        <textarea class="code"><?php echo htmlentities('<a href="' . get_bloginfo('url') . '"><img src="' . Mobilize::getBannerURL(250, $i) . '" /></a>') ?></textarea>
                        <input class="code" type="text" readonly="readonly" value="<?php the_permalink(); ?>" />                   
                    </div>
                    <div class="mobilize-banners">
                        <!-- banner de 200x200 -->
                        <div class="image-banner">
                            <img class="image-banner-2" src="<?php echo Mobilize::getBannerURL(200); ?>" alt="">
                        </div>
                        <textarea class="code"><?php echo htmlentities('<a href="' . get_bloginfo('url') . '"><img src="' . Mobilize::getBannerURL(200, $i) . '" /></a>') ?></textarea>
                        <input class="code" type="text" readonly="readonly" value="<?php the_permalink(); ?>" />                   
                    </div>
                    <div class="mobilize-banners">
                        <!-- banner de 125x125 -->
                        <div class="image-banner">
                            <img class="image-banner-3" src="<?php echo Mobilize::getBannerURL(125); ?>" alt="">
                        </div>
                        <textarea class="code"><?php echo htmlentities('<a href="' . get_bloginfo('url') . '"><img src="' . Mobilize::getBannerURL(125, $i) . '" /></a>') ?></textarea>
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
                <div id="standard-message">
                    <div class="message-container"><?php echo nl2br($options['envie']['message']); ?></div>
                </div>
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

                        <input id="submit" class="mobilize-button" type="submit" value="Enviar" name="submit" />
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