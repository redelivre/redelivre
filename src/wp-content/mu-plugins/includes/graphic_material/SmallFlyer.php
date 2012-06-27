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
        
        if (file_exists($path)) {
            $this->finalImage = SVGDocument::getInstance($path, 'CampanhaSVGDocument');
            
            $candidateImage = SVGImage::getInstance(0, 0, 'candidateImage', $candidateImage);
            $this->finalImage->prependImage($candidateImage);
     
            $this->formatShape();
            $this->formatText();
        } else {
            throw new Exception('Não foi possível encontrar o arquivo com a forma.');
        }
    }
    
    /**
     * Format a SVG shape image to be include in the flyer
     */
    protected function formatShape() {
        $this->data->shapeColor1 = isset($_REQUEST['data']['shapeColor1']) ? filter_var($_REQUEST['data']['shapeColor1'], FILTER_SANITIZE_STRING) : null;
        $this->data->shapeColor2 = isset($_REQUEST['data']['shapeColor2']) ? filter_var($_REQUEST['data']['shapeColor2'], FILTER_SANITIZE_STRING) : null;

        $this->finalImage->getElementById('fundo')->setAttribute('fill', $this->data->shapeColor1);
        $this->finalImage->getElementById('borda')->setAttribute('fill', $this->data->shapeColor2);
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
        
        $this->data->slogan = isset($_REQUEST['data']['slogan']) ? filter_var($_REQUEST['data']['slogan'], FILTER_SANITIZE_STRING) : null;
        $this->data->sloganSize = (isset($_REQUEST['data']['sloganSize']) && !empty($_REQUEST['data']['sloganSize'])) ? filter_var($_REQUEST['data']['sloganSize'], FILTER_SANITIZE_NUMBER_INT) : 30;
        $this->data->sloganColor = isset($_REQUEST['data']['sloganColor']) ? filter_var($_REQUEST['data']['sloganColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $this->data->numberSize = (isset($_REQUEST['data']['numberSize']) && !empty($_REQUEST['data']['numberSize'])) ? filter_var($_REQUEST['data']['numberSize'], FILTER_SANITIZE_NUMBER_INT) : 30;
        $this->data->numberColor = isset($_REQUEST['data']['numberColor']) ? filter_var($_REQUEST['data']['numberColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $this->data->roleSize = (isset($_REQUEST['data']['roleSize']) && !empty($_REQUEST['data']['roleSize'])) ? filter_var($_REQUEST['data']['roleSize'], FILTER_SANITIZE_NUMBER_INT) : 30;
        $this->data->roleColor = isset($_REQUEST['data']['roleColor']) ? filter_var($_REQUEST['data']['roleColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $this->data->coalition = isset($_REQUEST['data']['coalition']) ? filter_var($_REQUEST['data']['coalition'], FILTER_SANITIZE_STRING) : null;
        $this->data->coalitionSize = (isset($_REQUEST['data']['coalitionSize']) && !empty($_REQUEST['data']['coalitionSize'])) ? filter_var($_REQUEST['data']['coalitionSize'], FILTER_SANITIZE_NUMBER_INT) : 30;
        $this->data->coalitionColor = isset($_REQUEST['data']['coalitionColor']) ? filter_var($_REQUEST['data']['coalitionColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $this->data->candidateNumber = $campaign->candidate_number;
        
        $role = $this->finalImage->getElementById('cargo');
        
        if (strlen($this->data->candidateNumber) == 2) {
            $role[0] = 'Prefeito';
        } else {
            $role[0] = 'Vereador';
        }
        
        $role->setAttribute('fill', $this->data->roleColor);
        $role->setAttribute('font-size', $this->data->roleSize);
        
        $candidateName = $this->finalImage->getElementById('nome-do-candidato');
        $candidateName[0] = $this->data->candidateName;
        $candidateName->setAttribute('fill', $this->data->candidateColor);
        $candidateName->setAttribute('font-size', $this->data->candidateSize);

        $slogan = $this->finalImage->getElementById('slogan');
        $slogan[0] = $this->data->slogan;
        $slogan->setAttribute('fill', $this->data->sloganColor);
        $slogan->setAttribute('font-size', $this->data->sloganSize);
        
        $number = $this->finalImage->getElementById('numero');
        $number[0] = $this->data->candidateNumber;
        $number->setAttribute('fill', $this->data->numberColor);
        $number->setAttribute('font-size', $this->data->numberSize);
        
        $coalition = $this->finalImage->getElementById('coligação');
        $coalition[0] = $this->data->coalition;
        $coalition->setAttribute('fill', $this->data->coalitionColor);
        $coalition->setAttribute('font-size', $this->data->coalitionSize);
    }
}
