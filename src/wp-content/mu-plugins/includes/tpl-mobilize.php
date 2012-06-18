<?php
$options = get_option('mobilize');
wp_enqueue_style('mobilize', WPMU_PLUGIN_URL . '/css/mobilize.css');
get_header();
?>
<section id="main-section" class="wrap clearfix">
    
    <div id="mobilize-content">
		<h1>Apoie esta campanha</h1>
		 <?php if (Mobilize::isActive('redes')): ?>
            <section id="mobilize-redes" class="mobilize-widget clearfix">
                <?php $redes = Mobilize::getOption('redes'); ?>
                <h6>Redes sociais</h6>
                <p>Texto explicativo sobre os botões de compartilhamento nas redes.</p>
                <?php if (isset($redes['facebook']) && !empty($redes['facebook'])): ?>
                    <a class="mobilize-button mobilize-facebook" href="<?php echo $redes['facebook']; ?>">Facebook</a>
                <?php endif; ?>
                
                <?php if (isset($redes['twitter']) && !empty($redes['twitter'])): ?>
                    <a class="mobilize-button mobilize-twitter" href="<?php echo $redes['twitter']; ?>">Twitter</a>
                <?php endif; ?>
                
                <?php if (isset($redes['google']) && !empty($redes['google'])): ?>
                    <a class="mobilize-button mobilize-google" href="<?php echo $redes['google']; ?>">Google +</a>
                <?php endif; ?>
                
            </section>
        <?php endif; ?>
        
        <?php if (Mobilize::isActive('banners')): ?>
            
            <section id="mobilize-banners" class="mobilize-widget clearfix">
                <h6>Banners</h6>
                <p>Texto explicativo sobre os banners.</p>
                <?php for($i=0; $i < Mobilize::getNumBanners(); $i++): ?>
					<div class="mobilize-banners">
						<!-- banner de 250x250 -->
						<div class="banner-250"><img width="250" height="250" src="<?php echo Mobilize::getBannerURL($i) ?>" alt="" /></div>
						<textarea class="code"><?php echo htmlentities('<a href="'.get_bloginfo('url').'"><img src="'.Mobilize::getBannerURL($i).'" /></a>')?></textarea>					
					</div>
					<div class="mobilize-banners">
						<!-- banner de 200x200 -->
						<div class="banner-200"><img width="200" height="200" src="<?php echo Mobilize::getBannerURL($i) ?>" alt="" /></div>
						<textarea class="code"><?php echo htmlentities('<a href="'.get_bloginfo('url').'"><img src="'.Mobilize::getBannerURL($i).'" /></a>')?></textarea>					
					</div>
					<div class="mobilize-banners">
						<!-- banner de 125x125 -->
						<div class="banner-125"><img width="125" height="125" src="<?php echo Mobilize::getBannerURL($i) ?>" alt="" /></div>
						<textarea class="code"><?php echo htmlentities('<a href="'.get_bloginfo('url').'"><img src="'.Mobilize::getBannerURL($i).'" /></a>')?></textarea>	
					</div>
                <?php endfor; ?>
            </section>

            <script type="text/javascript">

            jQuery('.code').click( function() { jQuery(this).select(); } );

            </script>

        <?php endif; ?>
        
        
        <?php if (Mobilize::isActive('adesive')): ?>
        
            <section id="mobilize-sticker" class="mobilize-widget clearfix">
                <h6>Adesive sua foto!</h6>
                <div class="sticked-avatar"><img class="sticker"src="<?php echo Mobilize::getAdesiveURL(); ?>" alt="" /><img width="80" height="80" src="<?php echo WPMU_PLUGIN_URL; ?>/img/mistery_man.jpg" /></div>
                <p>Faça upload de uma foto sua e adicione o adesivo ao lado para ajudar na divulgação da minha candidatura!</p>                
                <form method="post" enctype="multipart/form-data" target="_blank">
                    <?php Mobilize::printAdesiveNonce() ?>
                    <p>Envie sua foto: <input type="file" name="photo" /> <input class="mobilize-button" type="submit" value="Adesivar foto" /></p>
                </form>
            </section>
        
        <?php endif; ?>
        
        
       
        
            
        <?php if (Mobilize::isActive('envie')): ?>
        
            <?php $success = Mobilize::enviarEmails(); ?>
        
            <section id="mobilize-sendto" class="mobilize-widget clearfix">
                <a name="send-to"></a>
                <h6>Envie para um amigo!</h6>
                <div id="standard-message">
                    <div><p><?php echo nl2br(htmlentities(utf8_decode($options['envie']['message']))) ?></p></div>
                </div>
                <!-- #standard-message -->
                <div id="mobilize-sendto-form">
                    <?php if (true === $success): ?>
                        <p class="success">Mensagem Enviada!</p>
                    <?php elseif (false === $success): ?>
                        <p class="error">Houve um erro ao enviar sua mensagem, tente novamente!</p>
                    <?php endif; ?>
						<p>Texto explicativo sobre o envie para um amigo.</p>
                    <form method="post" action="#send-to">
                        
                        <?php Mobilize::printEnvieNonce() ?>
                        
                        <input id="sender-name" type="text" value="<?php echo isset($_POST['sender-name']) ? $_POST['sender-name'] : __('name', 'intervozes'); ?>" name="sender-name" onfocus="if (this.value == '<?php _e('name', 'intervozes'); ?>') this.value = '';" onblur="if (this.value == '') {this.value = '<?php _e('name', 'intervozes'); ?>';}" /><br />
                        <input id="sender-email" type="text" value="<?php echo isset($_POST['sender-email']) ? $_POST['sender-email'] : 'email'; ?>" name="sender-email" onfocus="if (this.value == 'email') this.value = '';" onblur="if (this.value == '') {this.value = 'email';}" /><br />
                        
                        <input id="recipient-email" type="text" value="<?php echo isset($_POST['recipient-email']) ? $_POST['recipient-email'] :'Adicione até 10 endereços de email separados por vírgula'; ?>" name="recipient-email" onfocus="if (this.value == 'Adicione até 10 endereços de email separados por vírugla') this.value = '';" onblur="if (this.value == '') {this.value = 'Adicione até 10 endereços de email separados por vírugla';}" /><br />
                        
                        
                        <textarea id="sender-message" name="sender-message" onfocus="if (this.value == 'Adicione sua própria mensagem ou deixe em branco') this.value = '';" onblur="if (this.value == '') {this.value = 'Adicione sua própria mensagem ou deixe em branco';}"><?php echo isset($_POST['sender-message']) ? $_POST['sender-message'] : 'Adicione sua própria mensagem ou deixe em branco'; ?></textarea><br />
       
                        <input id="submit" class="mobilize-button" type="submit" value="Enviar" name="submit" />
                    </form>
                </div>
            </section>


        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>
