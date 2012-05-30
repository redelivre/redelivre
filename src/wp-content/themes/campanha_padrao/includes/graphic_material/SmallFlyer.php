<?php

require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/GraphicMaterial.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CampanhaSVGDocument.php');

/**
 * Class to generate different graphic material
 * to the candidates.
 */
class SmallFlyer extends GraphicMaterial {
    /**
     * The final SVG flyer
     * @var CampanhaSVGDocument
     */
    protected $finalImage;
    
    /**
     * Small flyer file name
     * @var string
     */
    protected $fileName;
    
    /**
     * Path to flyer file
     * @var string
     */
    protected $filePath;
    
    /*
     * Return all available shapes for the image.
     * 
     * @return array a list of shapes
     */
    public static function getShapes() {
        $shapes = array();
        $basePath = TEMPLATEPATH . '/img/graphic_material/';
        $files = glob($basePath . 'shape*.svg');
        
        foreach ($files as $file) {
            $shape = new stdClass;
            $shape->name = basename($file, '.svg');
            
            $image = SVGDocument::getInstance($file, 'CampanhaSVGDocument');
            $image->setWidth(70);
            $image->setHeight(70);
            $image->export($basePath . $shape->name . '.png');
            
            $shape->url = get_template_directory_uri() . '/img/graphic_material/' . $shape->name . '.png';
            
            $shapes[] = $shape;
        }
        
        return $shapes;
    }

    public function __construct() {
        parent::__construct();
        
        $this->fileName = 'smallflyer.svg';
        $this->filePath = $this->dir . $this->fileName;
    }

    /**
     * Build a candidate flyer based on the information
     * provided via AJAX request and print its url to the browser.
     * 
     * @return null
     */    
    public function preview() {
        $path = preg_replace('/\.svg$/', '.png', $this->filePath);
        $url =  $this->baseUrl . basename($this->fileName, '.svg') . '.png';
        
        $this->processImage();
        $this->finalImage->export($path);
        
        // add random number as parameter to skip browser cache
        $rand = rand();
        die("<img src='$url?rand=$rand'>");
    }
    
    /**
     * Save the SVG flyer to the hard disk for future use
     * and export it to PDF.
     * 
     * @return null
     */
    public function save() {
        $this->processImage();
        $this->finalImage->asXML($this->filePath);
        
        $this->export();
    }
    
    /**
     * Export SVG flyer to PDF
     * 
     * @return null
     */
    protected function export() {
        $path = preg_replace('/\.svg$/', '.pdf', $this->filePath);
        $this->finalImage->export($path);
    }
    
    /**
     * Check whether a flyer has been created
     * already.
     * 
     * @return bool
     */
    public function hasImage() {
        return file_exists($this->filePath);
    }
    
    /**
     * Get SVG image from hard disk.
     * 
     * @param string $format
     * @return string SVG image or URL to PNG image
     */
    public function getImage($format = 'svg') {
        if (file_exists($this->filePath)) {
            $svg = SVGDocument::getInstance($this->filePath, 'CampanhaSVGDocument');
            
            if ($format == 'svg') {
                return $svg->asXML(null, false);
            } else {
                $filePath = preg_replace('/\.svg$/', '.png', $this->filePath);
                $url =  $this->baseUrl . basename($this->fileName, '.svg') . '.png';
                $svg->export($filePath);
                
                return $url;
            }
        }
    }
    
    /**
     * Do the actual processing to generate the SVG
     * image based on the user input. Used both when 
     * displaying the image to the browser and when
     * saving the image to the disk.
     * 
     * @return null
     */
    protected function processImage() {
        $candidateImage = TEMPLATEPATH . '/img/delme/mahatma-gandhi.jpg';
        
        $this->finalImage = SVGDocument::getInstance(null, 'CampanhaSVGDocument');
        $this->finalImage->setWidth(266);
        $this->finalImage->setHeight(354);
        
        $candidateImage = SVGImage::getInstance(0, 0, 'candidateImage', $candidateImage);
        $this->finalImage->addShape($candidateImage);
 
        $this->formatShape();
 
        $this->formatText();
    }
    
    /**
     * Format a SVG shape image to be include in the flyer
     */
    protected function formatShape() {
        $shapeName = isset($_REQUEST['shapeName']) ? filter_var($_REQUEST['shapeName'], FILTER_SANITIZE_STRING) : null;
        $shapeColor1 = isset($_REQUEST['shapeColor1']) ? filter_var($_REQUEST['shapeColor1'], FILTER_SANITIZE_STRING) : null;
        $shapeColor2 = isset($_REQUEST['shapeColor2']) ? filter_var($_REQUEST['shapeColor2'], FILTER_SANITIZE_STRING) : null;

        $shapePath = TEMPLATEPATH . "/img/graphic_material/$shapeName.svg";
        
        if (file_exists($shapePath)) {
            // TODO: check if there is a better way to change element style
            $svg = SVGDocument::getInstance($shapePath);
            
            $element1 = $svg->getElementById('cor1');
            $shape1 = new SVGPath($element1->asXML());
            
            $element2 = $svg->getElementById('cor2');
            $shape2 = new SVGPath($element2->asXML());
            
            if ($shapeColor1) {
                $style = new SVGStyle;
                $style->setFill($shapeColor1);
                $shape1->setStyle($style);
            }
            
            if ($shapeColor2) {
                $style = new SVGStyle;
                $style->setFill($shapeColor2);
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

        $candidateName = isset($_REQUEST['candidateName']) ? filter_var($_REQUEST['candidateName'], FILTER_SANITIZE_STRING) : null;
        $candidateSize = (isset($_REQUEST['candidateSize']) && !empty($_REQUEST['candidateSize'])) ? filter_var($_REQUEST['candidateSize'], FILTER_SANITIZE_NUMBER_INT) : 30;
        $candidateColor = isset($_REQUEST['candidateColor']) ? filter_var($_REQUEST['candidateColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $candidateStyle = $this->createStyle($candidateSize, $candidateColor);
        $this->finalImage->addShape(SVGText::getInstance(15, 270, 'candidateName', $candidateName, $candidateStyle));

        $slogan = isset($_REQUEST['slogan']) ? filter_var($_REQUEST['slogan'], FILTER_SANITIZE_STRING) : null;
        $sloganSize = (isset($_REQUEST['sloganSize']) && !empty($_REQUEST['sloganSize'])) ? filter_var($_REQUEST['sloganSize'], FILTER_SANITIZE_NUMBER_INT) : 30;
        $sloganColor = isset($_REQUEST['sloganColor']) ? filter_var($_REQUEST['sloganColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $sloganStyle = $this->createStyle($sloganSize, $sloganColor);
        $this->finalImage->addShape(SVGText::getInstance(15, 330, 'slogan', $slogan, $sloganStyle));
        
        $string = $campaign->candidate_number;
        
        if (strlen($string) == 2) {
            $string = 'Prefeito ' . $string;
        } else {
            $string = 'Vereador ' . $string;
        }
        
        $this->finalImage->addShape(SVGText::getInstance(15, 300, 'candidateNumber', $string, $candidateStyle));
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
