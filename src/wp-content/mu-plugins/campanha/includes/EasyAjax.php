<?php
/**
 * Description of EasyAjax
 *
 * @author rafael
 */

class EasyAjax {
    static $admin = array('teste1');
    
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
    
    static function teste1(){
        
    }
}

EasyAjax::init();

?>
