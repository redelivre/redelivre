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
    
    /**
     * Enqueue scripts and styles used for
     * generating graphic materials.
     */
    public static function scriptsAndStyles()
    {
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('crop_photo', get_stylesheet_directory_uri() . '/js/crop_photo.js', array('jquery-ui-draggable'));
        wp_enqueue_script('graphic_material', get_stylesheet_directory_uri() . '/js/graphic_material.js', array('jquery', 'mColorPicker'));
        wp_enqueue_script('mColorPicker', get_stylesheet_directory_uri() . '/js/mColorPicker.min.js', array('jquery'));
        //TODO: updates to mColorPicker plugin will break it. Is there a way to change the images_dir without changing the plugin code?        
        wp_localize_script('mColorPicker', 'mCP', array('images_dir' => get_stylesheet_directory_uri() . '/img/mColorPicker/'));
        
        wp_enqueue_style('graphic_material', get_stylesheet_directory_uri() . '/css/graphic_material.css');
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