<?php
$options = get_option('mobilize');
wp_enqueue_style('mobilize', WPMU_PLUGIN_URL . '/css/mobilize.css');
get_header();
?>
<section id="main-section" class="wrap clearfix">
    
    <div id="content" class="col-8">
        
        <?php if (Mobilize::isActive('banners')): ?>
            
            <section class="banners clearfix">
                <h6>Banners</h6>
                <?php for($i=0; $i < Mobilize::getNumBanners(); $i++): ?>
                    <p class="bottom">
                        <small><a href="<?php echo Mobilize::getBannerURL($i); ?>" target="_blank" title="Visualizar tamanho real">Visualizar tamanho real</a></small>
                        <br/>
                        <img src="<?php echo Mobilize::getBannerURL($i) ?>"/>
                        <br/>
                        <textarea class="code"><?php echo htmlentities('<a href="'.get_bloginfo('siteurl').'"><img src="'.Mobilize::getBannerURL($i).'" /></a>')?></textarea>
                    </p>
                <?php endfor; ?>
            </section>

            <script type="text/javascript">

            jQuery('.code').click( function() { jQuery(this).select(); } );

            </script>

        <?php endif; ?>
        
        
        <?php if (Mobilize::isActive('adesive')): ?>
        
            <section class="sticker clearfix">
                <h6>Adesive sua foto!</h6>
                <div class="sticked-avatar"><img class="sticker" src="<?php echo Mobilize::getAdesiveURL(); ?>" alt="" /><img src="<?php echo WPMU_PLUGIN_URL; ?>/img/mistery_man.jpg" /></div>
                <p>Faça upload de uma foto sua e adicione o adesivo ao lado para ajudar na divulgação da minha candidatura!</p>
                <form method="post" enctype="multipart/form-data" target="_blank">
                    <?php Mobilize::printAdesiveNonce() ?>
                    Envie sua foto: <input type="file" name="photo" /> <input type="submit" value="adesivar foto" />
                </form>
            </section>
        
        <?php endif; ?>
        
        
        <?php if (Mobilize::isActive('redes')): ?>
            <section id="mobilize-redes">
                <?php $redes = Mobilize::getOption('redes'); ?>
                <h6>Redes sociais</h6>
                
                <?php if (isset($redes['facebook']) && !empty($redes['facebook'])): ?>
                    <a href="<?php echo $redes['facebook']; ?>">Facebook</a>
                <?php endif; ?>
                
                <?php if (isset($redes['twitter']) && !empty($redes['twitter'])): ?>
                    <a href="<?php echo $redes['twitter']; ?>">twitter</a>
                <?php endif; ?>
                
                <?php if (isset($redes['google']) && !empty($redes['google'])): ?>
                    <a href="<?php echo $redes['google']; ?>">Google +</a>
                <?php endif; ?>
                
            </section>
        <?php endif; ?>
        
            
        <?php if (Mobilize::isActive('envie')): ?>
        
            <?php $success = Mobilize::enviarEmails(); ?>
        
            <section class="send-to clearfix">
                <a  name="send-to"></a>
                <h6 class="col-12">Envie para um amigo!</h6>
                <div id="standard-message">
                    <p><?php echo nl2br(htmlentities(utf8_decode($options['envie']['message']))) ?></p>
                </div>
                <!-- #standard-message -->
                <div class="col-5">
                    <?php if (true === $success): ?>
                        <div class="success">Mensagem Enviada!</div>
                    <?php elseif (false === $success): ?>
                        <div class="error">Houve um erro ao enviar sua mensagem, tente novamente!</div>
                    <?php endif; ?>
                    <p><?php echo nl2br($send_options['text']); ?></p>
                    <form method="post" action="#send-to">
                        
                        <?php Mobilize::printEnvieNonce() ?>
                        
                        <input id="sender-name" type="text" value="<?php echo $_POST['sender-name'] ? $_POST['sender-name'] : __('name', 'intervozes'); ?>" name="sender-name" onfocus="if (this.value == '<?php _e('name', 'intervozes'); ?>') this.value = '';" onblur="if (this.value == '') {this.value = '<?php _e('name', 'intervozes'); ?>';}" /><br />
                        <input id="sender-email" type="email" value="<?php echo $_POST['sender-email'] ? $_POST['sender-email'] : 'email'; ?>" name="sender-email" onfocus="if (this.value == 'email') this.value = '';" onblur="if (this.value == '') {this.value = 'email';}" /><br />
                        
                        <input id="recipient-email" type="text" value="<?php echo $_POST['recipient-email'] ? $_POST['recipient-email'] :'Adicione até 10 endereços de email separados por vírugla'; ?>" name="recipient-email" onfocus="if (this.value == 'Adicione até 10 endereços de email separados por vírugla') this.value = '';" onblur="if (this.value == '') {this.value = 'Adicione até 10 endereços de email separados por vírugla';}" /><br />
                        
                        
                        <textarea id="sender-message" name="sender-message" onfocus="if (this.value == 'Adicione sua própria mensagem ou deixe em branco') this.value = '';" onblur="if (this.value == '') {this.value = 'Adicione sua própria mensagem ou deixe em branco';}"><?php echo $_POST['sender-message'] ? $_POST['sender-message'] : 'Adicione sua própria mensagem ou deixe em branco'; ?></textarea><br />
       
                        <input id="submit" class="button" type="submit" value="Enviar" name="submit" />
                    </form>
                </div>
            </section>


        <?php endif; ?>

    </div>
    <aside id="sidebar" class="col-4 clearfix">
        <?php get_sidebar(); ?>
    </aside>
</section>

<?php get_footer(); ?>
