<?php

echo 'Tem que impedir o cara de gerar material gráfico se ele ainda não tiver pago :-)';

require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');
require_once(TEMPLATEPATH . '/includes/wideimage/WideImage.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CampanhaSVGDocument.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/SmallFlyer.php');

echo '<br><br><br>Usuário sobe a foto:<br>';

echo '<img src="../wp-content/themes/campanha_padrao/img/delme/mahatma-gandhi.jpg"><br><br>';


?>

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
        
        <p>Escolha uma cor para a forma:</p>
        
    </div>
    <div id="image_preview"></div>
</form>
<?php

/*

echo '<br><br><br>Escolhe o formato e define num input a cor:<br>';

$foreground = SVGDocument::getInstance(TEMPLATEPATH . '/img/delme/foreground.svg');

echo 'Imagem original:' . $foreground->asXML();

$element = $foreground->getElementByAttribute('fill-rule', 'evenodd');
$path = new SVGPath($element->asXML());

$style = new SVGStyle;
$style->setFill('orange');
$path->setStyle($style);

$foreground->addShape($path);
echo 'SVG alterado em função da escolha do usuário:' . $foreground->asXML();

echo '<br><br>E então o sistema junta os dois:';

$finalImage = SVGDocument::getInstance(null, 'CampanhaSVGDocument');
$finalImage->setWidth(266);
$finalImage->setHeight(354);

$tmpImage = WideImage::load(TEMPLATEPATH . '/img/delme/mahatma-gandhi.jpg');
$tmpImage->resize(266, 240, 'outside')->saveToFile('/tmp/output.jpg');
$candidateImage = SVGImage::getInstance(0, 0, 'myImage', '/tmp/output.jpg');

$finalImage->addShape($candidateImage);
$finalImage->addShape($path);

echo $finalImage->asXML();

echo '<br><br>E por fim adiciona texto:';

$style = new SVGStyle(array('font-size' => '30px'));
$style->setFill('red');
$style->setStroke('red');
$style->setStrokeWidth(1);
$finalImage->addShape(SVGText::getInstance(15, 290, 'candidateName', 'Mahatma Gandhi', $style));

$style = new SVGStyle(array('font-size' => '20px', 'align' => 'right'));
$style->setFill('red');
$finalImage->addShape(SVGText::getInstance(15, 320, 'candidateNumber', 'Vereador 50501', $style));

echo $finalImage->asXML();

$finalImage->export('/tmp/gandhi.png');
*/