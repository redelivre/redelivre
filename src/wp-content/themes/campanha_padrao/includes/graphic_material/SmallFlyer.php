<?php

require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CampanhaSVGDocument.php');

/**
 * Class to generate different graphic material
 * to the candidates.
 */
class SmallFlyer {
    /**
     * The final SVG flyer
     * @var CampanhaSVGDocument
     */
    protected $finalImage;
    
    /**
     * Path to the directory where
     * flyers should be stored.
     * @var string
     */
    protected $dir;
    
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

    public function __construct() {
        $dir = wp_upload_dir();
        $dir = $dir['basedir'] . '/graphic_material/';
        
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        
        $this->dir = $dir;
        $this->fileName = 'smallflyer.svg';
        $this->filePath = $this->dir . $this->fileName;
    }

    /**
     * Build a candidate flyer based on the information
     * provided via AJAX request and print it to the browser.
     * 
     * @return null
     */    
    public function previewImage() {
        $this->processImage();
        die($this->finalImage->asXML(null, false));
    }
    
    /**
     * Save the SVG flyer to the hard disk for future use.
     * 
     * @return null
     */
    public function saveImage() {
        $this->processImage();
        $this->finalImage->asXML($this->filePath);
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
     * @return string SVG image
     */
    public function getImage() {
        if (file_exists($this->filePath)) {
            $svg = SVGDocument::getInstance($this->filePath, 'CampanhaSVGDocument');
            return $svg->asXML(null, false);
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
        
        // using filter_var instead of filter_input because INPUT_REQUEST is not implemented yet
        $shapeName = filter_var($_REQUEST['shapeName'], FILTER_SANITIZE_STRING);
        $shapeColor = filter_var($_REQUEST['shapeColor'], FILTER_SANITIZE_STRING);
        $candidateName = filter_var($_REQUEST['candidateName'], FILTER_SANITIZE_STRING);
        $candidateSize = filter_var($_REQUEST['candidateSize'], FILTER_SANITIZE_NUMBER_INT);
        $candidateColor = filter_var($_REQUEST['candidateColor'], FILTER_SANITIZE_STRING);
        
        $this->finalImage = SVGDocument::getInstance(null, 'CampanhaSVGDocument');
        $this->finalImage->setWidth(266);
        $this->finalImage->setHeight(354);
        
        $candidateImage = SVGImage::getInstance(0, 0, 'candidateImage', $candidateImage);
        $this->finalImage->addShape($candidateImage);
 
        $this->formatShape($shapeName, $shapeColor);
 
        $this->formatText($candidateName, $candidateColor, $candidateSize);
    }
    
    /**
     * Format a SVG shape image to be include in the flyer
     * 
     * @param $shapeName string name of the file that should be used
     * @param $shapeColor string
     */
    protected function formatShape($shapeName, $shapeColor) {
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
            
            $this->finalImage->addShape($shape);
        } else {
            throw new Exception('Você precisa selecionar uma forma.');
        }
    }
    
    /**
     * Create a SVGText object with the texts that
     * should be include in the final SVG of the flyer
     * 
     * @param string $candidateName
     * @param string $candidateColor
     * @param int $candidateSize
     */
    protected function formatText($candidateName, $candidateColor, $candidateSize) {
        global $campaign;
                
        if (empty($candidateColor)) {
            $candidateColor = 'red';
        }
        
        if (empty($candidateSize)) {
            $candidateSize = '30';
        }

        $style = new SVGStyle(array('font-size' => "{$candidateSize}px"));
        $style->setFill($candidateColor);
        $style->setStroke($candidateColor);
        $style->setStrokeWidth(1);
        
        $this->finalImage->addShape(SVGText::getInstance(15, 290, 'candidateName', $candidateName, $style));
        
        $string = $campaign->candidate_number;
        
        if (strlen($string) == 2) {
            $string = 'Prefeito ' . $string;
        } else {
            $string = 'Vereador ' . $string;
        }
        
        $this->finalImage->addShape(SVGText::getInstance(15, 320, 'candidateNumber', $string, $style));
    }
}
