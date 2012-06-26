<?php

get_header();

$manager = new GraphicMaterialManager;

if ($manager->isPublic() || is_user_logged_in()) {
    $links = $manager->getLinks();
}

if (isset($links) && !empty($links)) {
    ?>
    <p>Veja abaixo a lista de todos os materiais gráficos disponíveis para download:</p>
    <ul>
    <?php
    foreach ($links as $name => $url):
        ?>
        <li><a href="<?php echo $url; ?>"><?php echo $name; ?></a></li>
        <?php
    endforeach;
    echo '</ul>';
} else if (isset($links) && empty($links)) {
    ?>
    <p>Nenhum material disponível.</p>
    <?php
} else {
    ?>
    <p>Você não tem permissão para ver esta página.</p>
    <?php
}

get_footer();