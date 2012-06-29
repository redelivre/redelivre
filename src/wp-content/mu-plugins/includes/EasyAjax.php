<?php

require_once(__DIR__ . '/graphic_material/GraphicMaterialFactory.php');

/**
 * Description of EasyAjax
 *
 * @author rafael
 */
class MuEasyAjax {
    static $admin = array('savePhotoPosition', 'campanhaPreviewFlyer');
    
    static function init(){
        $methods = get_class_methods(__CLASS__);
        
        foreach($methods as $method){
            if($method != 'init')
                if(in_array($method, self::$admin)){
                    add_action("wp_ajax_$method", array(__CLASS__,$method));
                }else{
                    add_action("wp_ajax_$method", array(__CLASS__,$method));
                    add_action("wp_ajax_nopriv_$method", array(__CLASS__,$method));
                }
        }
    }
    
    static function savePhotoPosition(){
        $class = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
        
        $graphicMaterial = GraphicMaterialFactory::build($class);
        $graphicMaterial->candidatePhoto->crop();
    }
    
    static function campanhaPreviewFlyer() {
        $class = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
        
        if (class_exists($class)) {
            $graphicMaterial = GraphicMaterialFactory::build($class);
            $graphicMaterial->preview();
        }
    }
}

MuEasyAjax::init();

?>
