<?php

require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CampanhaSVGDocument.php');

/**
 * Class to generate different graphic material
 * to the candidates.
 */
class SmallFlyer {
    /*
     * Return all available shapes for the image.
     * @return array a list of shapes
     */
    public static function getShapes() {
        $shapes = array();
        $files = glob(TEMPLATEPATH . '/img/graphic_material/shape*');
        
        foreach ($files as $file) {
            $shape = new stdClass;
            $shape->name = basename($file, '.svg');
            $shape->uri = get_template_directory_uri() . '/img/graphic_material/' . basename($file);
            $shape->path = $file;
            
            $shapes[] = $shape;
        }
        
        return $shapes;
    }

    /**
     * Build a candidate flyer based on the information
     * provided via AJAX request.
     */    
    public function getImage() {
        $candidateImage = TEMPLATEPATH . '/img/delme/mahatma-gandhi.jpg';
        $shapeName = filter_input(INPUT_GET, 'shapeName', FILTER_SANITIZE_STRING);
        $shapeColor = filter_input(INPUT_GET, 'shapeColor', FILTER_SANITIZE_STRING);
        
        $finalImage = SVGDocument::getInstance(null, 'CampanhaSVGDocument');
        $finalImage->setWidth(266);
        $finalImage->setHeight(354);
        
        $candidateImage = SVGImage::getInstance(0, 0, 'candidateImage', $candidateImage);
        $finalImage->addShape($candidateImage);
 
        $shapePath = TEMPLATEPATH . "/img/graphic_material/$shapeName.svg";
        
        if (file_exists($shapePath)) {
            // TODO: check if there is a better way to change element style
            $svg = SVGDocument::getInstance($shapePath);
            $element = $svg->getElementByAttribute('fill-rule', 'evenodd');
            $shape = new SVGPath($element->asXML());
            
            if ($shapeColor) {
                $style = new SVGStyle;
                $style->setFill($shapeColor);
                $shape->setStyle($style);
            }
            
            $finalImage->addShape($shape);
        }
        
        die($finalImage->asXML(null, false));
    }
}
