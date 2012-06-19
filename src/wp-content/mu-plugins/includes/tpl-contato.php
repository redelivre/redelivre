<?php

get_header();

?>

<section id="main-section" class="wrap clearfix">
    <?php if (get_option('campanha_contact_enabled')) : ?>
        Conteúdo da página de contato
    <?php else: ?>
        <p>O recurso está desabilitado.</p>
    <?php endif; ?>
</section>

<?php

get_footer();
