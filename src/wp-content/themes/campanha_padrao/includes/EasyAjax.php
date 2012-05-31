<?php
/**
 * Description of EasyAjax
 *
 * @author rafael
 */

class EasyAjax {
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
        update_option('photo-position-'.$_POST['filename'], array('left' => $_POST['left'], 'top' => $_POST['top'], 'width' => $_POST['width']));
    }
    
    static function campanhaPreviewFlyer() {
        require_once(TEMPLATEPATH . '/includes/graphic_material/SmallFlyer.php');
        $smallFlyer = new SmallFlyer;
        $smallFlyer->preview();
    }
}

EasyAjax::init();

?>
