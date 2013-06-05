<?php
/*
    Plugin Name: Mobilize
    Plugin URI: http://www.ethymos.com.br
    Description: 
    Author: Ethymos
    Version: 1.0
    Author URI: 
    Text Domain:
    Domain Path:
 */

define('INC_MOBILIZE', dirname(__FILE__));
define('MOBILIZE_MATERIAL_DIR', INC_MOBILIZE.'/../../mu-plugins/includes/graphic_material/');
define('MOBILIZE_MATERIAL_URL', get_bloginfo('url').'/files/graphic_material/');

if (class_exists('WideImage')) {
    require INC_MOBILIZE.'/includes/wideimage/WideImage.php';
}

class Mobilize {

    const SETTINGS_NONCE                  = 'save-mobilize';
    const ADESIVE_NONCE                   = 'adesivenonce';
    const ENVIE_NONCE                     = 'envienonce';
    const OPTION_NAME                     = 'mobilize';
    const TEXTO_DESCRITIVO_PADRAO_PAGINA  = 'Mobilize e demonstre seu apoio';
    const TEXTO_DESCRITIVO_PADRAO_REDES   = 'Acompanhe as redes sociais abaixo.';
    const TEXTO_DESCRITIVO_PADRAO_BANNERS = 'Utilize o código da primeira caixa abaixo para inserir o banner em seu site ou blog, ou, então, utilize o link da segunda caixa para compartilhá-lo nas redes sociais.';
    const TEXTO_DESCRITIVO_PADRAO_ADESIVE = 'Coloque sua foto em "Escolher arquivo" e depois clique em "Adesivar foto", agora é só aguardar!';
    const TEXTO_DESCRITIVO_PADRAO_ENVIE   = 'Coloque seu nome e seu e-mail. Depois coloque o e-mail de seus amigos separados por vírgulas e agora é só colocar sua mensagem pessoal e enviar. As pessoas indicadas por você irão receber o texto abaixo junto com a sua mensagem.';

    public static $errors = array('banners' => array(), 'adesive' => array(), 'redes' => array(), 'envie' => array());

    public static function saveRedesSociais()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            update_option('mobilize_redes_sociais', array(
                'redes_facebook_page' => trim($_POST['redes-facebook-page']),
                'redes_twitter'       => trim($_POST['redes-twitter']),
                'redes_youtube'       => trim($_POST['redes-youtube']),
                'redes_google'        => trim($_POST['redes-google']),
            ));
        }
    }

    public static function optionRedesSociais($index = NULL)
    {
        $options = get_option('mobilize_redes_sociais');
        
        if (!is_null($index)) {
            return isset($options[$index]) ? $options[$index] : NULL;
        }
        else {
            return $options;
        }
    }

    public static function isActive($section) {
        $option = self::getOption($section);
        return isset($option['active']);
    }

    public static function getErrors($section = null) {
        return $section ? self::$errors[$section] : self::$errors;
    }

    public static function printErrors($section) {
        $errors = self::getErrors($section);

        if ($errors) {
            $msg = implode('<br />', $errors);
            echo "<div class='error'>$msg</div>";
        }
    }

    public static function addError($section, $error) {
        self::$errors[$section][] = $error;
    }

    public static function printSettingsNonce() {
        wp_nonce_field(self::SETTINGS_NONCE, self::SETTINGS_NONCE);
    }
    
    public static function saveSettings() {
        if ($_POST && isset($_POST[self::SETTINGS_NONCE]) && wp_verify_nonce($_POST[self::SETTINGS_NONCE], self::SETTINGS_NONCE)) {
            $option = self::getOption();

            // para não perder as imagens quando salvar o post sem enviar outras imagens.
            // se for implementar mais de uma imagem por seção, tem que pensar num modo de deletar imagens
            $_POST['mobilize']['banners']['files'] = $option['banners']['files'];
            $_POST['mobilize']['adesive']['files'] = $option['adesive']['files'];

            self::handleBannerUploads();
            self::handleAdesiveUploads();
            self::toggleMenuItem();

            array_walk_recursive($_POST['mobilize'], create_function('&$val', '$val = stripslashes($val);'));
            self::updateOption($_POST['mobilize']);
        }
    }

    public static function toggleMenuItem() {
        $menu  = wp_get_nav_menu_object('main');
        $items = wp_get_nav_menu_items('main');
        $menuItem = null;

        if ($menu) {
            foreach ($items as $item) {
                if ($item->url == home_url('/mobilizacao')) {
                    $menuItem = $item;
                }
            }

            if (isset($_POST['mobilize']['general']['menuItem']) && !$menuItem) {
                wp_update_nav_menu_item($menu->term_taxonomy_id, 0, array(
                    'menu-item-title'  => 'Mobilização',
                    'menu-item-url'    => home_url('/mobilizacao'),
                    'menu-item-status' => 'publish')
                );
            } else if (!isset($_POST['mobilize']['general']['menuItem']) && $menuItem) {
                wp_delete_post($menuItem->ID, true);
            }
        }
    }
 
    public static function getOption($index = null) {
        $option = get_option(self::OPTION_NAME);
        $option['redes']['description']   = isset($option['redes']['description'])   ? $option['redes']['description']   : self::TEXTO_DESCRITIVO_PADRAO_REDES;
        $option['banners']['description'] = isset($option['banners']['description']) ? $option['banners']['description'] : self::TEXTO_DESCRITIVO_PADRAO_BANNERS;
        $option['adesive']['description'] = isset($option['adesive']['description']) ? $option['adesive']['description'] : self::TEXTO_DESCRITIVO_PADRAO_ADESIVE;
        $option['envie']['description']   = isset($option['envie']['description'])   ? $option['envie']['description']   : self::TEXTO_DESCRITIVO_PADRAO_ENVIE;

        $result = $index ? @$option[$index] : $option;

        return is_array($result) ? $result : array();
    }

    public static function updateOption($option) {
        update_option(self::OPTION_NAME, $option);
    }

    public static function deleteBanner($index = 0) {
        $option = self::getOption('banners');

        $filename250 = self::getBannerFilename(250, $index);
        $filename200 = self::getBannerFilename(200, $index);
        $filename125 = self::getBannerFilename(125, $index);

        if ($filename250 && file_exists($filename250)) {
            unlink($filename250);
        }

        if ($filename200 && file_exists($filename200)) {
            unlink($filename200);
        }

        if ($filename125 && file_exists($filename125)) {
            unlink($filename125);
        }
    }

    public static function deleteAdesive($index = 0) {
        $option = self::getOption('adesive');

        $filename = self::getAdesiveFilename($index);

        if ($filename && file_exists($filename)) {
            unlink($filename);
        }
    }

    public static function getNumBanners() {
        $option = self::getOption('banners');
        
        if (isset($option['files'])) {
            return count($option['files']);
        }
    }

    public static function getBannerURL($size, $index = 0) {
        $option = self::getOption('banners');

        if (isset($option['files'][$index]) && is_numeric($size)) {
            return MOBILIZE_MATERIAL_URL.'banners/'.preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-{$size}$2", $option['files'][$index]);
        }
        else {
            return '';
        }
    }

    public static function getAdesiveURL($index = 0) {
        $option = self::getOption('adesive');
        return isset($option['files'][$index]) ? MOBILIZE_MATERIAL_URL.'adesives/'.$option['files'][$index] : '';
    }

    public static function getBannersPath() {
        return MOBILIZE_MATERIAL_DIR.'banners/';
    }

    public static function getAdesivesPath() {
        return MOBILIZE_MATERIAL_DIR.'adesives/';
    }

    public static function getBannerFilename($size, $index = 0) {
        $option = self::getOption('banners');
        $path   = self::getBannersPath();

        if (isset($option['files'][$index]) && is_numeric($size)) {
            return $path.preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-{$size}$2", $option['files'][$index]);
        }
        else {
            return '';
        }
    }

    public static function getAdesiveFilename($index = 0) {
        $option = self::getOption('adesive');
        $path   = self::getAdesivesPath();
        
        return isset($option['files'][$index]) ? $path.$option['files'][$index] : '';
    }

    public static function handleBannerUploads() {
        if (isset($_FILES['banner']) && is_array($_FILES['banner']['name'])) {
            $path = self::getBannersPath();

            if (!file_exists($path) && !is_dir($path)) {
                mkdir($path);
            }

            foreach ($_FILES['banner']['tmp_name'] as $index => $tmp_name) {
                if (self::validateBanner($index)) {
                    self::deleteBanner($index);

                    $fname250 = preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-250$2", $_FILES['banner']['name'][$index]);
                    $fname200 = preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-200$2", $_FILES['banner']['name'][$index]);
                    $fname125 = preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-125$2", $_FILES['banner']['name'][$index]);

                    $tmp = WideImage::load($tmp_name);
                    $tmp->resize(250)->saveToFile($path.$fname250);
                    $tmp->resize(200)->saveToFile($path.$fname200);
                    $tmp->resize(125)->saveToFile($path.$fname125);

                    // adciona o arquivo no array do _POST para ser salvo posteriormente no update_option
                    $_POST['mobilize']['banners']['files'][$index] = $_FILES['banner']['name'][$index];
                }
            }
        }
    }

    public static function handleAdesiveUploads() {
        if (isset($_FILES['adesive']) && is_array($_FILES['adesive']['name'])) {
            $path = self::getAdesivesPath();

            if (!file_exists($path) && !is_dir($path)) {
                mkdir($path);
            }

            foreach ($_FILES['adesive']['tmp_name'] as $index => $tmp_name) {
                if (self::validateAdesive($index)) {
                    self::deleteAdesive($index);

                    $fname = $_FILES['adesive']['name'][$index];

                    move_uploaded_file($tmp_name, $path.$fname);

                    // adiciona o arquivo no array do _POST para ser salvo posteriormente no update_option
                    $_POST['mobilize']['adesive']['files'][$index] = $fname;

                    // resize if image is too big
                    $adesivo = WideImage::load($path.$fname);
                    $w = $adesivo->getWidth();

                    $maxWidth = 250;

                    if ($w > $maxWidth) {
                        $adesivo = $adesivo->resize($maxWidth, null);
                    }
                    
                    /*
                    $h = $adesivo->getHeight();

                    if ($h > $maxSize)
                        $adesivo = $adesivo->resize(null, $maxSize);
                    */
                    
                    $adesivo->saveToFile($path.$fname);
                }
            }
        }
    }

    public static function validateBanner($index = 0) {
        if (!$_FILES['banner']['name'][$index]) {
            return;
        }

        if ($_FILES['banner']['error'][$index] != UPLOAD_ERR_OK) {
            self::addError('banners', "O upload do banner falhou.");
            return false;
        }

        if (!self::validadeImageUpload('banner', $index)) {
            self::addError('banners', "O formato do arquivo é inválido.");
            return false;
        }

        $file = WideImage::load($_FILES['banner']['tmp_name'][$index]);

        if ($file->getWidth() != $file->getHeight() && ($file->getWidth() < 250 || $file->getHeight() < 250)) {
            self::addError('banners', "O banner deve ser quadrado e ter no mínimo 250x250 pixels.");
            return false;
        } elseif ($file->getWidth() != $file->getHeight()) {
            self::addError('banners', "O banner deve ser quadrado.");
            return false;
        } elseif ($file->getWidth() < 250) {
            self::addError('banners', "O banner deve ter no mínimo 250x250 pixels.");
            return false;
        }

        return true;
    }

    public static function validateAdesive($index = 0) {
        if (!$_FILES['adesive']['name'][$index]) {
            return;
        }

        $ok = $_FILES['adesive']['error'][$index] == UPLOAD_ERR_OK;
        $ok = self::validadeImageUpload('adesive', $index);

        if (!$ok)
            self::addError('adesive', "O upload do adesivo falhou.");
            
        $file = WideImage::load($_FILES['adesive']['tmp_name'][$index]);
        
        if ($file->getWidth() < 250) {
            self::addError('adesive', "O banner deve ter no mínimo 250 pixels de largura.");
            $ok = false;
        }

        return $ok;
    }

    public static function validadeImageUpload($image, $index = 0) {
        if (empty($image) || is_null($image) || !isset($_FILES[$image])) {
            return false;
        } 
        else {
            $acceptedFormats = array('image/gif', 'image/png', 'image/jpeg', 'image/pjpeg', 'image/x-png');
            return in_array($_FILES[$image]['type'][$index], $acceptedFormats);
        }
    }

    public static function printAdesiveNonce() {
        wp_nonce_field(self::ADESIVE_NONCE, self::ADESIVE_NONCE);
    }

    public static function adesivar() {
        if ($_POST && isset($_POST[self::ADESIVE_NONCE]) && wp_verify_nonce($_POST[self::ADESIVE_NONCE], self::ADESIVE_NONCE) && isset($_FILES['photo']['error']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $option = self::getOption('adesive');

            // se um dia tiver mais de um adesivo é só enviar o $index pelo post
            $index = 0;
            $adesive_filename = self::getAdesiveFilename($index);

            if ($adesive_filename) {
                $adesivo  = WideImage::load($adesive_filename);
                $uploaded = WideImage::loadFromUpload('photo');
                
                $uploaded = $uploaded->resize(250, null);

                $new = $uploaded->merge($adesivo, 'right', 'bottom');
                header('Content-disposition: attachment; filename=foto.jpg');
                header('Content-type: image/jpeg');
                $new->output('jpg', 100);
                die;
            }
        }
    }

    public static function printEnvieNonce() {
        wp_nonce_field(self::ENVIE_NONCE, self::ENVIE_NONCE);
    }

    public static function enviarEmails() {
        if ($_POST && isset($_POST[self::ENVIE_NONCE]) && wp_verify_nonce($_POST[self::ENVIE_NONCE], self::ENVIE_NONCE)) {
            $option = self::getOption('envie');

            // TODO: ENVIAR EMAIL
            $success = null;

            if ($_POST['sender-name'] && $_POST['sender-email']) {
                // Headers
                $sender      = filter_input(INPUT_POST, 'sender-name', FILTER_SANITIZE_STRING);
                $senderEmail = filter_input(INPUT_POST, 'sender-email', FILTER_SANITIZE_EMAIL);
                $recipients  = explode(',', $_POST['recipient-email']);
                $from        = "From: '$sender' <noreply@campanhacompleta.com.br>";

                // Mensagem
                $msg  = "$sender ($senderEmail) lhe enviou a mensagem que segue abaixo.\n\n";
                $msg .= $_POST['sender-message'] ? stripslashes($_POST['sender-message'])."\n\n".$option['message'] : $option['message'];

                $success = false;

                if (is_array($recipients) && sizeof($recipients) > 0) {
                    foreach ($recipients as $r) {
                        if ($x = wp_mail($r, $option['subject'], $msg, $from)) {
                            $success = true;
                        }
                    }
                }
            }

            return $success;
        }
    }

    public static function tiracento($texto)
    {
        $trocarIsso =   array('à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ü','ú','ÿ','À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ù','Ü','Ú','Ÿ',);
        $porIsso =      array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','y','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','U','U','U','Y',);
        $titletext = str_replace($trocarIsso, $porIsso, $texto);
        return $titletext;
    }

}

// Functions, filters and actions
require INC_MOBILIZE.'/includes/functions-mobilize.php';
?>