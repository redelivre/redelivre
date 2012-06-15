<?php
$options = get_option('mobilize');

get_header();
?>
<div class="container clearfix">
    <?php get_sidebar('left'); ?>
    <section id="main-section" class="span-12 colborder">
        
        <?php if (Mobilize::isActive('banners')): ?>
            <article id="mobilize-banners">
                <h3>Banners</h3>
                <?php for($i=0; $i < Mobilize::getNumBanners(); $i++): ?>
                <img src="<?php echo Mobilize::getBannerURL($i) ?>" style="max-width:480px"/>
                <?php echo htmlentities('<img src="'.Mobilize::getBannerURL($i).'" />')?>
                <?php endfor; ?>
            </article>
        <?php endif; ?>
        
        
        <?php if (Mobilize::isActive('adesive')): ?>
            <article id="mobilize-adesive">
                <h3>Adesive sua foto</h3>
                <form method="post" enctype="multipart/form-data">
                    <?php Mobilize::printAdesiveNonce() ?>
                    sua foto: <input type="file" name="photo" />
                    <input type="submit" value="adesivar foto" />
                </form>
            </article>
        <?php endif; ?>
        
        
        <?php if (Mobilize::isActive('redes')): ?>
            <article id="mobilize-redes">
                <h3>Redes sociais</h3>
                <?php _pr(Mobilize::getOption('redes')) ?>
            </article>
        <?php endif; ?>
        
            
        <?php if (Mobilize::isActive('envie')): ?>
            <article id="mobilize-envie">
                <h3>Envie para um amigo</h3>
                <div id="mobilize-envie-message">
                    Esta é a mensagem que será enviada:</br>
                    <p>
                    <?php echo nl2br(htmlentities(utf8_decode($options['envie']['message']))) ?>
                    </p>
                </div>
                <form method="post">
                    <?php Mobilize::printEnvieNonce() ?>
                    <label>
                        Seu nome:
                        <input type="text" name="nome" />
                    </label><br/>
                    
                    <label>
                        Seu e-mail:
                        <input type="text" name="email" />
                    </label><br/>
                    
                    <label>
                        Enviar para:
                        <input type="text" name="destinos" />
                    </label><br/>
                    
                    <label>
                        Envie uma mensagem extra:<br/>
                        <textarea name="message"></textarea>
                    </label><br/>
                    <input type="submit" name="enviar" value="enviar emails" />
                </form>
            </article>
        <?php endif; ?>

    </section>
    <?php get_sidebar('right'); ?>
</div>

<?php get_footer(); ?>