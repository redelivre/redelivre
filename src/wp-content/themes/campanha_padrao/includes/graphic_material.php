<?php


require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');
require_once(TEMPLATEPATH . '/includes/wideimage/WideImage.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CampanhaSVGDocument.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/SmallFlyer.php');

$smallFlyer = new SmallFlyer;

if (isset($_POST['save'])) {
    check_admin_referer('graphic_material');
    
    try {
        $smallFlyer->save();
    } catch (Exception $e) {
        echo "<div class='error'><p>{$e->getMessage()}</p></div>";
    }
}

echo 'Tem que impedir o cara de gerar material gráfico se ele ainda não tiver pago :-)';

?>

<div id="graphic_material_content">
    <?php echo '<br><br><br>Usuário sobe a foto:<br>'; ?>
    <?php echo '<img src="../wp-content/themes/campanha_padrao/img/delme/mahatma-gandhi.jpg"><br><br>'; ?>
    
    <form id="graphic_material_form" method="post">
        <?php wp_nonce_field('graphic_material'); ?>
        <input type='hidden' name='action' value='campanha_preview_flyer'>
        <input type='hidden' name='page' value='graphic_material'>
        <div id="graphic_material_wizard">
            <p>Escolha uma forma:</p>
            <?php
            $shapes = SmallFlyer::getShapes();
            
            foreach ($shapes as $shape) {
                $image = SVGDocument::getInstance($shape->path, 'CampanhaSVGDocument');
                $image->setWidth(70);
                $image->setHeight(70);
                echo "<input type='radio' name='shapeName' value='{$shape->name}'>" . $image->asXML(null, false);
            }
            ?>
            
            <p>Escolha uma cor para a forma: <input type="color" name="shapeColor" value="#ff0000" data-text="hidden" style="height:20px;width:20px;" /></p>
            
            <p>Digite o seu nome como quer que apareço no santinho: <input type="text" name="candidateName" value="" /> <input type="color" name="candidateColor" value="#000000" data-text="hidden" style="height:20px;width:20px;" /> Tamanho da fonte (em pixels): <input type="text" name="candidateSize" value="" /></p>
        </div>
        
        <input type="submit" name="save" value="Salvar">
        <input type="submit" name="export" value="Exportar">
    </form>
    
    <div id="graphic_material_preview"></div>
    
    <div id="graphic_material_saved">
        <?php
        if ($smallFlyer->hasImage()) {
            echo '<h2>Imagem salva</h2>';
            echo $smallFlyer->getImage();
        }
        ?>
    </div>
</div>