<?php

require_once(WPMU_PLUGIN_DIR . '/includes/svglib/svglib.php');
require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/GraphicMaterial.php');
require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/CampanhaSVGDocument.php');

/**
 * Class to generate the header of the site
 */
class Header extends GraphicMaterial {
    /**
     * Small flyer width
     * @var int
     */
    const width = 960;
    
    /**
     * Small flyer height
     * @var int
     */
    const height = 198;

    /**
     * @see GraphicMaterial::formatShape()
     */
    protected function formatShape() {
        $this->data->shapeColor1 = isset($_REQUEST['data']['shapeColor1']) ? filter_var($_REQUEST['data']['shapeColor1'], FILTER_SANITIZE_STRING) : null;
        $this->data->shapeColor2 = isset($_REQUEST['data']['shapeColor2']) ? filter_var($_REQUEST['data']['shapeColor2'], FILTER_SANITIZE_STRING) : null;

        if(is_object($this->finalImage->getElementById('fundo')))
        	$this->finalImage->getElementById('fundo')->setAttribute('fill', $this->data->shapeColor1);
        if(is_object($this->finalImage->getElementById('borda')))
        	$this->finalImage->getElementById('borda')->setAttribute('fill', $this->data->shapeColor2);
    }
    
    /**
     * @see GraphicMaterial::formatText()
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
        
        $this->data->roleColor = isset($_REQUEST['data']['roleColor']) ? filter_var($_REQUEST['data']['roleColor'], FILTER_SANITIZE_STRING) : 'black';
        
        $this->data->candidateNumber = $campaign->candidate_number;
        
        $candidateName = $this->finalImage->getElementById('nome-do-candidato');
        $candidateName[0] = $this->data->candidateName;
        if(is_object($candidateName))
        {
        	$candidateName->setAttribute('fill', $this->data->candidateColor);
        	$candidateName->setAttribute('font-size', $this->data->candidateSize);
        }

        $slogan = $this->finalImage->getElementById('slogan');
        
        $role = $this->finalImage->getElementById('cargo');
        
        if($role){
            $role->setAttribute('fill', $this->data->roleColor);
        }
        
        
        if ($slogan) {
            $slogan[0] = $this->data->slogan;
            $slogan->setAttribute('fill', $this->data->sloganColor);
            $slogan->setAttribute('font-size', $this->data->sloganSize);
        }
        
        $number = $this->finalImage->getElementById('numero');
        $number[0] = $this->data->candidateNumber;
        
        if(is_object($number))
        {
        	$number->setAttribute('fill', $this->data->numberColor);
        	$number->setAttribute('font-size', $this->data->numberSize);
        }
    }
    
    
    function setAsWordPressHeader() {
        
        set_theme_mod('header_image', site_url('files/graphic_material/header.png') );
        set_theme_mod('header_textcolor', 'blank');
        
    }
}
