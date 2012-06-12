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
    
    public function __construct() {
        parent::__construct();
        
        $this->fileName = 'santinho.svg';
        $this->filePath = $this->dir . $this->fileName;
        
        $this->data = $this->getData();
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
        
        // generate a PDF copy of the SVG file
        $this->export();
        
        // if necessary change the publicity of the page that list
        // the links to all graphic materials        
        $this->maybeChangePublicity();
        
        // store SVG file information in the database to be able
        // to regenerate it
        $this->saveData();
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
        $candidateImage = GRAPHIC_MATERIAL_DIR . '/smallflyer_candidate_croped.png';
        
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
        $this->data->shapeName = isset($_REQUEST['data']['shapeName']) ? filter_var($_REQUEST['data']['shapeName'], FILTER_SANITIZE_STRING) : null;
        $this->data->shapeColor1 = isset($_REQUEST['data']['shapeColor1']) ? filter_var($_REQUEST['data']['shapeColor1'], FILTER_SANITIZE_STRING) : null;
        $this->data->shapeColor2 = isset($_REQUEST['data']['shapeColor2']) ? filter_var($_REQUEST['data']['shapeColor2'], FILTER_SANITIZE_STRING) : null;

        $shapePath = TEMPLATEPATH . "/img/graphic_material/{$this->data->shapeName}.svg";
        
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
