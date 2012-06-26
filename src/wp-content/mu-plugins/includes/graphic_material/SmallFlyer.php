<?php

require_once(WPMU_PLUGIN_DIR . '/includes/svglib/svglib.php');
require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/GraphicMaterial.php');
require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/CampanhaSVGDocument.php');

/**
 * Class to generate small flyer (santinho e colinha)
 * to the candidates.
 */
class SmallFlyer extends GraphicMaterial {
    /**
     * Small flyer width
     * @var int
     */
    public $width = 992;
    
    /**
     * Small flyer height
     * @var int
     */
    public $height = 1358;
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * @see GraphicMaterial::processImage()
     */
    protected function processImage() {
        $candidateImage = GRAPHIC_MATERIAL_DIR . '/smallflyer_candidate_croped.png';
        $this->data->shapeName = isset($_REQUEST['data']['shapeName']) ? filter_var($_REQUEST['data']['shapeName'], FILTER_SANITIZE_STRING) : null;
        $path = WPMU_PLUGIN_DIR . "/img/graphic_material/{$this->data->shapeName}.svg";
        
        $this->finalImage = SVGDocument::getInstance($path, 'CampanhaSVGDocument');
        
        $candidateImage = SVGImage::getInstance(0, 0, 'candidateImage', $candidateImage);
        $this->finalImage->prependImage($candidateImage);
 
        //$this->formatShape();
 
        //$this->formatText();
    }
    
    /**
     * Format a SVG shape image to be include in the flyer
     */
    protected function formatShape() {
        $this->data->shapeColor1 = isset($_REQUEST['data']['shapeColor1']) ? filter_var($_REQUEST['data']['shapeColor1'], FILTER_SANITIZE_STRING) : null;
        $this->data->shapeColor2 = isset($_REQUEST['data']['shapeColor2']) ? filter_var($_REQUEST['data']['shapeColor2'], FILTER_SANITIZE_STRING) : null;

        $shapePath = GRAPHIC_MATERIAL_DIR . "/{$this->data->shapeName}.svg";
        
        if (file_exists($shapePath)) {
            // TODO: check if there is a better way to change element style
            $svg = SVGDocument::getInstance($shapePath);
            
            $element1 = $svg->getElementById('cor1');
            $shape1 = new SVGPath($element1->asXML());
            
            $element2 = $svg->getElementById('cor2');
            $shape2 = new SVGPath($element2->asXML());
            
            if ($this->data->shapeColor1) {
                $style = new SVGStyle;
                $style->setFill($this->data->shapeColor1);
                $shape1->setStyle($style);
            }
            
            if ($this->data->shapeColor2) {
                $style = new SVGStyle;
                $style->setFill($this->data->shapeColor2);
                $shape2->setStyle($style);
            }
            
            $this->finalImage->addShape($shape1);
            $this->finalImage->addShape($shape2);
        }
    }
    
    /**
     * Create a SVGText object with the texts that
     * should be include in the final SVG of the flyer
     * 
     * @return null
     */
    protected function formatText() {
        global $campaign;

        $this->data->candidateName = isset($_REQUEST['data']['candidateName']) ? filter_var($_REQUEST['data']['candidateName'], FILTER_SANITIZE_STRING) : null;
        $this->data->candidateSize = (isset($_REQUEST['data']['candidateSize']) && !empty($_REQUEST['data']['candidateSize'])) ? filter_var($_REQUEST['data']['candidateSize'], FILTER_SANITIZE_NUMBER_INT) : 30;
        $this->data->candidateColor = isset($_REQUEST['data']['candidateColor']) ? filter_var($_REQUEST['data']['candidateColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $candidateStyle = $this->createStyle($this->data->candidateSize, $this->data->candidateColor);
        $this->finalImage->addShape(SVGText::getInstance(15, 270, 'candidateName', $this->data->candidateName, $candidateStyle));

        $this->data->slogan = isset($_REQUEST['data']['slogan']) ? filter_var($_REQUEST['data']['slogan'], FILTER_SANITIZE_STRING) : null;
        $this->data->sloganSize = (isset($_REQUEST['data']['sloganSize']) && !empty($_REQUEST['data']['sloganSize'])) ? filter_var($_REQUEST['data']['sloganSize'], FILTER_SANITIZE_NUMBER_INT) : 30;
        $this->data->sloganColor = isset($_REQUEST['data']['sloganColor']) ? filter_var($_REQUEST['data']['sloganColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $sloganStyle = $this->createStyle($this->data->sloganSize, $this->data->sloganColor);
        $this->finalImage->addShape(SVGText::getInstance(15, 330, 'slogan', $this->data->slogan, $sloganStyle));
        
        $this->data->candidateNumber = $campaign->candidate_number;
        
        if (strlen($this->data->candidateNumber) == 2) {
            $this->data->candidateNumber = 'Prefeito ' . $this->data->candidateNumber;
        } else {
            $this->data->candidateNumber = 'Vereador ' . $this->data->candidateNumber;
        }
        
        $this->finalImage->addShape(SVGText::getInstance(15, 300, 'candidateNumber', $this->data->candidateNumber, $candidateStyle));
    }

    /**
     * Return a SVGText object with the specified font
     * size and color
     * 
     * @param int $fontSize
     * @param string $fontColor
     * @return SVGText
     */
    protected function createStyle($fontSize, $fontColor) {
        $style = new SVGStyle(array('font-size' => "{$fontSize}px"));
        $style->setFill($fontColor);
        $style->setStroke($fontColor);
        $style->setStrokeWidth(1);
        
        return $style;
    }
}
