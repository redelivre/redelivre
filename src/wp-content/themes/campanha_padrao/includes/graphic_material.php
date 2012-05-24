<?php

echo 'Tem que impedir o cara de gerar material gráfico se ele ainda não tiver pago :-)';

require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');
require_once(TEMPLATEPATH . '/includes/wideimage/WideImage.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CampanhaSVGDocument.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/SmallFlyer.php');

campanha_svg_not_supported_message();

?>

<div id="graphic_material_content">
    <?php echo '<br><br><br>Usuário sobe a foto:<br>'; ?>
    <?php echo '<img src="../wp-content/themes/campanha_padrao/img/delme/mahatma-gandhi.jpg"><br><br>'; ?>
    
    <form id="graphic_material_form">
        <input type='hidden' name='action' value='campanha_preview_flyer'>
        <div>
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
        <div id="image_preview"></div>
    </form>
</div>