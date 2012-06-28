<?php

require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/DpiConverter.php');
require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/SmallFlyer.php');
require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/CandidatePhoto.php');

/**
 * Class to build graphic material related
 * classes.
 */
class GraphicMaterialFactory {
    public static function build($className)
    {
        switch (strtolower($className)) {
            case 'smallflyer':
                $dpiConverter = new DpiConverter;
                $candidatePhoto = new CandidatePhoto('smallflyer_candidate.png', SmallFlyer::width, SmallFlyer::height, $dpiConverter);
                $smallFlyer = new SmallFlyer($candidatePhoto, $dpiConverter);
                
                return $smallFlyer;
            default:
                break;
        }
    }
}
