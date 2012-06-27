<?php

define('GRAPHIC_MATERIAL_PUBLIC_URL', site_url() . '/materialgrafico');

/**
 * Container class for methods that help
 * manage the graphic material functionalities. 
 */
class GraphicMaterialManager {
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
    
    /**
     * Return a list of links to all the graphic
     * materials created by the user.
     * 
     * @return array
     */
    public function getLinks()
    {
        $links = array();
        $names = array('smallflyer' => 'Santinho', 'flyer' => 'Flyer');
        
        foreach (glob(GRAPHIC_MATERIAL_DIR . '*.pdf') as $file) {
            $englishName = basename($file, '.pdf');
            if (array_key_exists($englishName, $names)) {
                $name = $names[$englishName];
                $url = GRAPHIC_MATERIAL_URL . basename($file);
                
                $links[$name] = $url;
            }
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