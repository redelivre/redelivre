<?php

class GraphicMaterial
{
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
        $files = glob(WPMU_PLUGIN_DIR . "/img/graphic_material/shape*.svg");
        
        foreach ($files as $file) {
            $shape = new stdClass;
            $shape->name = basename($file, '.svg');
            
            $image = SVGDocument::getInstance($file, 'CampanhaSVGDocument');
            $image->setWidth(70);
            $image->setHeight(70);
            $image->export(GRAPHIC_MATERIAL_DIR . $shape->name . '.png');
            
            $shape->url = GRAPHIC_MATERIAL_URL . $shape->name . '.png';
            
            $shapes[] = $shape;
        }
        
        return $shapes;
    }

    
    
    /**
     * Enqueue scripts and styles used for
     * generating graphic materials.
     */
    public static function scriptsAndStyles()
    {
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('graphic_material', WPMU_PLUGIN_URL . '/js/graphic_material.js', array('jquery', 'mColorPicker'));
        wp_enqueue_script('crop_photo', WPMU_PLUGIN_URL . '/js/crop_photo.js', array('jquery-ui-draggable', 'graphic_material'));
        wp_enqueue_script('mColorPicker', WPMU_PLUGIN_URL . '/js/mColorPicker.min.js', array('jquery'));
        //TODO: updates to mColorPicker plugin will break it. Is there a way to change the images_dir without changing the plugin code?        
        wp_localize_script('mColorPicker', 'mCP', array('images_dir' => WPMU_PLUGIN_URL . '/img/mColorPicker/'));
        
        wp_enqueue_style('graphic_material', WPMU_PLUGIN_URL . '/css/graphic_material.css');
    }
    
    /**
     * Setup a few constants used by the
     * system.
     */
    public static function setUp()
    {
        $info = wp_upload_dir();
        
        if ($info['error']) {
            throw new Exception($info['error']);
        }
        
        define('GRAPHIC_MATERIAL_DIR', $info['basedir'] . '/graphic_material/');
        define('GRAPHIC_MATERIAL_URL', $info['baseurl'] . '/graphic_material/');
        
        if (!file_exists(GRAPHIC_MATERIAL_DIR)) {
            mkdir(GRAPHIC_MATERIAL_DIR);
        }
    }
    
    public function __construct()
    {
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
    }
    
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
     * Store the data used to generate the SVG
     * file in the database.
     * 
     * @return null
     */
    public function saveData()
    {
        update_option($this->optionName, $this->data);
    }
    
    /**
     * Return a list of links to all the graphic
     * materials created by the user.
     * 
     * @return array
     */
    public function getLinks()
    {
        $links = array();
        
        foreach (glob($this->dir . '*.pdf') as $file) {
            $name = ucfirst(basename($file, '.pdf'));
            $url = $this->baseUrl . basename($file);
              
            $links[$name] = $url;
        }

        return $links;
    }
    
    /**
     * Check whether the list of graphic materials
     * is public or not.
     * 
     * @return bool
     */
    public function isPublic()
    {
        if (get_option('graphic_material_public_links')) {
            return true;
        }
        
        return false;
    }
 
    /**
     * Change whether the graphic material 
     * is public or not.
     * 
     * @return null
     */   
    public function maybeChangePublicity()
    {
        if (isset($_REQUEST['graphic_material_public']) && $_REQUEST['graphic_material_public'] == 'on') {
            $publicity = 1;
        } else {
            $publicity = 0;
        }

        update_option('graphic_material_public_links', $publicity);
    }
}