<?php

abstract class GraphicMaterial
{
    /**
     * The final SVG file
     * @var CampanhaSVGDocument
     */
    protected $finalImage;
    
    /**
     * Graphic material file name
     * @var string
     */
    protected $fileName;
    
    /**
     * Path to file
     * @var string
     */
    protected $filePath;
    
    /**
     * Url to the directory where
     * flyers should be stored.
     * @var string
     */
    protected $baseUrl;
    
    /**
     * Path to the directory where
     * flyers should be stored.
     * @var string
     */
    protected $dir;
    
    /**
     * Data used to generate the 
     * SVG file.
     * @var array
     */
    public $data;
    
    /*
     * Return all available shapes for a graphic material type.
     * 
     * @param string $type
     * @return array a list of shapes
     */
    public static function getShapes() {
        $shapes = array();
        $type = strtolower(get_called_class());
        $files = glob(WPMU_PLUGIN_DIR . "/img/graphic_material/$type*.svg");

        foreach ($files as $file) {
            $shape = new stdClass;
            $shape->name = basename($file, '.svg');
            
            if (!file_exists(GRAPHIC_MATERIAL_DIR . $shape->name . '.png')) {
                $image = SVGDocument::getInstance($file, 'CampanhaSVGDocument');
                $image->setWidth(150);
                $image->setHeight(150);
                $image->export(GRAPHIC_MATERIAL_DIR . $shape->name . '.png');
            }
            
            $shape->url = GRAPHIC_MATERIAL_URL . $shape->name . '.png';
            
            $shapes[] = $shape;
        }
        
        return $shapes;
    }

    public function __construct(CandidatePhoto $candidatePhoto, DpiConverter $converter)
    {
        $this->converter = $converter;
        $this->candidatePhoto = $candidatePhoto;
        
        $info = wp_upload_dir();
        
        if ($info['error']) {
            throw new Exception($info['error']);
        }
        
        $this->dir = $info['basedir'] . '/graphic_material/';
        $this->baseUrl = $info['baseurl'] . '/graphic_material/';
        
        if (!file_exists($this->dir)) {
            mkdir($this->dir);
        }
        
        $this->optionName = strtolower(get_called_class());
        
        $this->fileName = strtolower(get_called_class()) . '.svg';
        $this->filePath = $this->dir . $this->fileName;
        
        $this->data = $this->getData();
    }
    
    /**
     * Do the actual processing to generate the SVG
     * image based on the user input. Used both when 
     * displaying the image to the browser and when
     * saving the image to the disk.
     * 
     * @return bool
     */
    protected function processImage() {
        $candidateImage = GRAPHIC_MATERIAL_DIR . '/' . strtolower(get_called_class()). '_candidate_croped.png';
        $this->data->shapeName = isset($_REQUEST['data']['shapeName']) ? filter_var($_REQUEST['data']['shapeName'], FILTER_SANITIZE_STRING) : null;
        $path = WPMU_PLUGIN_DIR . "/img/graphic_material/{$this->data->shapeName}.svg";
        
        if (!empty($this->data->shapeName)) {
            if (file_exists($path)) {
                $this->finalImage = SVGDocument::getInstance($path, 'CampanhaSVGDocument');
                
                $candidateImage = SVGImage::getInstance(0, 0, 'candidateImage', $candidateImage);
                $this->finalImage->prependImage($candidateImage);
         
                $this->formatShape();
                $this->formatText();
                
                return true;
            } else {
                throw new Exception('Não foi possível encontrar o arquivo com a forma.');
            }
        }
        
        return false;
    }

    /**
     * Change all the texts and its fonts and colors.
     * 
     * @return null
     */
    abstract protected function formatText();
    
    /**
     * Change the color of the parts of shape file.
     * 
     * @return null
     */    
    abstract protected function formatShape();

    /**
     * Get from the database data used to
     * generated the SVG file.
     * 
     * @return stdClass data to generate SVG file
     */
    public function getData()
    {
        // the option is stored using the name of one of this class childs
        $data = get_option($this->optionName);
        
        if ($data) {
            return $data;
        } else {
            return new stdClass;
        }
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
        
        if ($this->processImage()) {
            $this->finalImage->export($path);
            
            // resize image to browser size (75dpi)
            $img = WideImage::load($path);
            $img->resize($this->converter->maybeConvertTo75Dpi(static::width), $this->converter->maybeConvertTo75Dpi(static::height), 'outside')->saveToFile($path);
            
            // add random number as parameter to skip browser cache
            $rand = rand();
            
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-type: application/json');
            
            echo json_encode(array('image' => "<img src='$url?rand=$rand'>", 'candidateSize' => intval($this->data->candidateSize)));
        }
        
        die;
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
                
                // resize image to browser size (75dpi)
                $img = WideImage::load($filePath);
                $img->resize($this->converter->maybeConvertTo75Dpi(static::width), $this->converter->maybeConvertTo75Dpi(static::height), 'outside')->saveToFile($filePath);
                        
                return $url;
            }
        }
    }

    /**
     * Store the data used to generate the SVG
     * file in the database.
     * 
     * @return null
     */
    public function saveData()
    {
        update_option($this->optionName, $this->data);
    }
}


/**
 * Template tag to print the graphical material list
 * 
 * @return null
 */

function the_graphic_material() {

    $manager = new GraphicMaterialManager;

    if ($manager->isPublic() || is_user_logged_in()) {
        $links = $manager->getLinks();
    }

    if (isset($links) && !empty($links)) {
        ?>
        <p>Veja abaixo a lista de todos os materiais gráficos disponíveis para download:</p>
        <ul>
        <?php
        foreach ($links as $name => $url):
            ?>
            <li><a href="<?php echo $url; ?>"><?php echo $name; ?></a></li>
            <?php
        endforeach;
        echo '</ul>';
    } else if (isset($links) && empty($links)) {
        ?>
        <p>Nenhum material disponível.</p>
        <?php
    } else {
        ?>
        <p>Você não tem permissão para ver esta página.</p>
        <?php
    }

}
