<?php

require_once(__DIR__ . '/graphic_material/SmallFlyer.php');
require_once(__DIR__ . '/graphic_material/CandidatePhoto.php');

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
        $fileName = filter_input(INPUT_POST, 'filename', FILTER_SANITIZE_STRING);
        $minWidth = filter_input(INPUT_POST, 'minWidth', FILTER_SANITIZE_NUMBER_INT);
        $minHeight = filter_input(INPUT_POST, 'minHeight', FILTER_SANITIZE_NUMBER_INT);
        
        $candidatePhoto = new CandidatePhoto($fileName, $minWidth, $minHeight);
        $candidatePhoto->crop();
    }
    
    static function campanhaPreviewFlyer() {
        $smallFlyer = new SmallFlyer;
        $smallFlyer->preview();
    }
}

MuEasyAjax::init();

?>
