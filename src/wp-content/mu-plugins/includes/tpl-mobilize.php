<?php
$options = get_option('mobilize');

get_header();
?>
<div class="container clearfix">
    <?php get_sidebar('left'); ?>
    <section id="main-section" class="span-12 colborder">
        
        <?php if (isset($options['banners']['active'])): ?>
            <article id="mobilize-banners">
                <h3>Banners</h3>
                
            </article>
        <?php endif; ?>
        
        
        <?php if (isset($options['adesive']['active'])): ?>
            <article id="mobilize-adesive">
                <h3>Adesive sua foto</h3>
                
            </article>
        <?php endif; ?>
        
        
        <?php if (isset($options['redes']['active'])): ?>
            <article id="mobilize-redes">
                <h3>Redes sociais</h3>
                
            </article>
        <?php endif; ?>
        
            
        <?php if (isset($options['envie']['active'])): ?>
            <article id="mobilize-envie">
                <h3>Envie para um amigo</h3>
                <div id="mobilize-envie-subject"><?php echo $options['envie']['message'] ?></div>
                <div id="mobilize-envie-message"><?php echo nl2br(htmlentities(utf8_decode($options['envie']['message']))) ?></div>
            </article>
        <?php endif; ?>

    </section>
    <?php get_sidebar('right'); ?>
</div>

<?php get_footer(); ?>