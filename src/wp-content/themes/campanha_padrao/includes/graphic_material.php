<?php

echo 'Tem que impedir o cara de gerar material gráfico se ele ainda não tiver pago :-)';

require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');

echo '<br><br><br>Usuário sobe a foto:<br>';

echo '<img src="../wp-content/themes/campanha_padrao/img/delme/mahatma-gandhi.jpg"><br><br>';

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

$image = SVGDocument::getInstance();
$image->setWidth(266);
$image->setHeight(354);

$image->addShape(SVGImage::getInstance(0, 0, 'myImage', TEMPLATEPATH . '/img/delme/mahatma-gandhi.jpg'));
$image->addShape($path);

echo $image->asXML();

echo '<br><br>E por fim adiciona texto:';

$style = new SVGStyle(array('font-size' => '30px'));
$style->setFill('red');
$style->setStroke('red');
$style->setStrokeWidth(1);
$image->addShape(SVGText::getInstance(15, 290, 'candidateName', 'Mahatma Gandhi', $style));

$style = new SVGStyle(array('font-size' => '20px', 'align' => 'right'));
$style->setFill('red');
$image->addShape(SVGText::getInstance(15, 320, 'candidateNumber', 'Vereador 50501', $style));

echo $image->asXML();
