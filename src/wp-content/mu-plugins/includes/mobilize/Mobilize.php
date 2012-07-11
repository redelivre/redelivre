<?php

require_once(WPMU_PLUGIN_DIR . '/includes/wideimage/WideImage.php');

/**
 * Description of Mobilize
 *
 * @author rafael
 */
class Mobilize {

    const SETTINGS_NONCE = 'save-mobilize';
    const ADESIVE_NONCE = 'adesivenonce';
    const ENVIE_NONCE = 'envienonce';
    const OPTION_NAME = 'mobilize';
    const TEXTO_DESCRITIVO_PADRAO_PAGINA = 'Ajude-nos em nossa campanha.';
    const TEXTO_DESCRITIVO_PADRAO_REDES = 'Acompanhe a campanha nas redes sociais abaixo.';
    const TEXTO_DESCRITIVO_PADRAO_BANNERS = 'Copie o código abaixo e insira no seu blog ou site os banners da campanha.';
    const TEXTO_DESCRITIVO_PADRAO_ADESIVE = 'Coloque sua foto em "Escolher arquivo" e depois clique em "Adesivar foto", agora é só aguardar!';
    const TEXTO_DESCRITIVO_PADRAO_ENVIE = 'Coloque seu nome e seu e-mail. Depois coloque o e-mail de seus amigos separados por vírgulas e agora é só colocar sua mensagem pessoal e enviar!.';

    static $errors = array('banners' => array(), 'adesive' => array(), 'redes' => array(), 'envie' => array());

    static function isActive($section) {
        $option = self::getOption($section);

        return isset($option['active']);
    }

    static function getErrors($section = null) {
        if ($section)
            return self::$errors[$section];
        else
            return self::$errors;
    }

    static function printErrors($section) {
        $errors = self::getErrors($section);
        if ($errors) {
            $msg = implode('<br/>', $errors);
            echo "<div class='error'>$msg</div>";
        }
    }

    static function addError($section, $error) {
        self::$errors[$section][] = $error;
    }

    static function printSettingsNonce() {
        wp_nonce_field(self::SETTINGS_NONCE, self::SETTINGS_NONCE);
    }
    
    static function saveSettings() {
        if ($_POST && isset($_POST[self::SETTINGS_NONCE]) && wp_verify_nonce($_POST[self::SETTINGS_NONCE], self::SETTINGS_NONCE)) {
            $option = self::getOption();

            // para não perder as imagens quando salvar o post sem enviar outras imagens.
            // se for implementar mais de uma imagem por seção, tem que pensar num modo de deletar imagens
            if (isset($options['banners']['files'])) {
                $_POST['mobilize']['banners']['files'] = $option['banners']['files'];
            }
            if (isset($options['adesive']['files'])) {
                $_POST['mobilize']['adesive']['files'] = $option['adesive']['files'];
            }

            self::handleBannerUploads();
            self::handleAdesiveUploads();
            self::toggleMenuItem();

            array_walk_recursive($_POST['mobilize'], create_function('&$val', '$val = stripslashes($val);'));
            self::updateOption($_POST['mobilize']);
        }
    }

    static function toggleMenuItem() {
        $menu = wp_get_nav_menu_object('main');
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
                    'menu-item-title' => 'Mobilização',
                    'menu-item-url' => home_url('/mobilizacao'),
                    'menu-item-status' => 'publish')
                );
            } else if (!isset($_POST['mobilize']['general']['menuItem']) && $menuItem) {
                wp_delete_post($menuItem->ID, true);
            }
        }
    }

    static function getOption($index = null) {
        $option = get_option(self::OPTION_NAME);
        $option['redes']['description'] = isset($option['redes']['description']) ? $option['redes']['description'] : self::TEXTO_DESCRITIVO_PADRAO_REDES;
        $option['banners']['description'] = isset($option['banners']['description']) ? $option['banners']['description'] : self::TEXTO_DESCRITIVO_PADRAO_BANNERS;
        $option['adesive']['description'] = isset($option['adesive']['description']) ? $option['adesive']['description'] : self::TEXTO_DESCRITIVO_PADRAO_ADESIVE;
        $option['envie']['description'] = isset($option['envie']['description']) ? $option['envie']['description'] : self::TEXTO_DESCRITIVO_PADRAO_ENVIE;
        if ($index)
            $result = @$option[$index];
        else
            $result = $option;
        return is_array($result) ? $result : array();
    }

    static function updateOption($option) {
        update_option(self::OPTION_NAME, $option);
    }

    static function deleteBanner($index = 0) {
        $option = self::getOption('banners');

        $filename250 = self::getBannerFilename(250, $index);
        $filename200 = self::getBannerFilename(200, $index);
        $filename125 = self::getBannerFilename(125, $index);

        if ($filename250 && file_exists($filename250))
            unlink($filename250);

        if ($filename200 && file_exists($filename200))
            unlink($filename200);

        if ($filename125 && file_exists($filename125))
            unlink($filename125);
    }

    static function deleteAdesive($index = 0) {
        $option = self::getOption('adesive');

        $filename = self::getAdesiveFilename($index);

        if ($filename && file_exists($filename))
            unlink($filename);
    }

    static function getNumBanners() {
        $option = self::getOption('banners');
        
        if (isset($option['files'])) {
            return count($option['files']);
        }
    }

    static function getBannerURL($size, $index = 0) {
        $option = self::getOption('banners');
        if (isset($option['files'][$index]) && is_numeric($size))
            return GRAPHIC_MATERIAL_URL . 'banners/' . preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-{$size}$2", $option['files'][$index]);
        else
            return '';
    }

    static function getAdesiveURL($index = 0) {
        $option = self::getOption('adesive');

        if (isset($option['files'][$index]))
            return GRAPHIC_MATERIAL_URL . 'adesives/' . $option['files'][$index];
        else
            return '';
    }

    static function getBannersPath() {
        return GRAPHIC_MATERIAL_DIR . 'banners/';
    }

    static function getAdesivesPath() {
        return GRAPHIC_MATERIAL_DIR . 'adesives/';
    }

    static function getBannerFilename($size, $index = 0) {
        $option = self::getOption('banners');
        $path = self::getBannersPath();
        if (isset($option['files'][$index]) && is_numeric($size))
            return $path . preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-{$size}$2", $option['files'][$index]);
        else
            return '';
    }

    static function getAdesiveFilename($index = 0) {
        $option = self::getOption('adesive');
        $path = self::getAdesivesPath();
        if (isset($option['files'][$index]))
            return $path . $option['files'][$index];
        else
            return '';
    }

    static function handleBannerUploads() {
        if (isset($_FILES['banner']) && is_array($_FILES['banner']['name'])) {
            $path = self::getBannersPath();
            if (!file_exists($path) && !is_dir($path))
                mkdir($path);

            foreach ($_FILES['banner']['tmp_name'] as $index => $tmp_name) {
                if (self::validateBanner($index)) {
                    self::deleteBanner($index);

                    $fname250 = preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-250$2", $_FILES['banner']['name'][$index]);
                    $fname200 = preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-200$2", $_FILES['banner']['name'][$index]);
                    $fname125 = preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-125$2", $_FILES['banner']['name'][$index]);

                    $tmp = WideImage::load($tmp_name);
                    $tmp->resize(250)->saveToFile($path . $fname250);
                    $tmp->resize(200)->saveToFile($path . $fname200);
                    $tmp->resize(125)->saveToFile($path . $fname125);



                    // adciona o arquivo no array do _POST para ser salvo posteriormente no update_option
                    $_POST['mobilize']['banners']['files'][$index] = $_FILES['banner']['name'][$index];
                }
            }
        }
    }

    static function handleAdesiveUploads() {
        if (isset($_FILES['adesive']) && is_array($_FILES['adesive']['name'])) {
            $path = self::getAdesivesPath();
            if (!file_exists($path) && !is_dir($path))
                mkdir($path);

            foreach ($_FILES['adesive']['tmp_name'] as $index => $tmp_name) {
                if (self::validateAdesive($index)) {
                    self::deleteAdesive($index);

                    $fname = $_FILES['adesive']['name'][$index];

                    move_uploaded_file($tmp_name, $path . $fname);

                    // adciona o arquivo no array do _POST para ser salvo posteriormente no update_option
                    $_POST['mobilize']['adesive']['files'][$index] = $fname;

                    //resize if image is too big
                    $adesivo = WideImage::load($path . $fname);
                    $w = $adesivo->getWidth();

                    $maxWidth = 150;

                    if ($w > $maxWidth) {
                        $adesivo = $adesivo->resize($maxWidth, null);
                    }
                    
                    /*
                    $h = $adesivo->getHeight();

                    if ($h > $maxSize)
                        $adesivo = $adesivo->resize(null, $maxSize);
                    */
                    
                    $adesivo->saveToFile($path . $fname);
                }
            }
        }
    }

    static function validateBanner($index = 0) {
        if (!$_FILES['banner']['name'][$index])
            return;

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

    static function validateAdesive($index = 0) {
        if (!$_FILES['adesive']['name'][$index])
            return;

        $ok = $_FILES['adesive']['error'][$index] == UPLOAD_ERR_OK;

        $ok = self::validadeImageUpload('adesive', $index);
        if (!$ok)
            self::addError('adesive', "O upload do adesivo falhou.");
            
        $file = WideImage::load($_FILES['adesive']['tmp_name'][$index]);
        
        if ($file->getWidth() < 150) {
            self::addError('adesive', "O banner deve ter no mínimo 150 pixels de largura.");
            $ok = false;
        }

        return $ok;
    }

    static function validadeImageUpload($image, $index = 0) {

        if (empty($image) || is_null($image) || !isset($_FILES[$image]))
            return false;

        $acceptedFormats = array('image/gif', 'image/png', 'image/jpeg', 'image/pjpeg', 'image/x-png');

        return in_array($_FILES[$image]['type'][$index], $acceptedFormats);
    }

    static function printAdesiveNonce() {
        wp_nonce_field(self::ADESIVE_NONCE, self::ADESIVE_NONCE);
    }

    static function adesivar() {
        if ($_POST && isset($_POST[self::ADESIVE_NONCE]) && wp_verify_nonce($_POST[self::ADESIVE_NONCE], self::ADESIVE_NONCE) && isset($_FILES['photo']['error']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
            $option = self::getOption('adesive');

            // se um dia tiver mais de um adesivo é só enviar o $index pelo post
            $index = 0;
            $adesive_filename = self::getAdesiveFilename($index);
            if ($adesive_filename) {

                $adesivo = WideImage::load($adesive_filename);
                $uploaded = WideImage::loadFromUpload('photo');
                
                $uploaded = $uploaded->resize(150, null);


                $new = $uploaded->merge($adesivo, 'right', 'bottom');
                $new->output('jpg', 100);
                die;
            }
        }
    }

    static function printEnvieNonce() {
        wp_nonce_field(self::ENVIE_NONCE, self::ENVIE_NONCE);
    }

    static function enviarEmails() {
        if ($_POST && isset($_POST[self::ENVIE_NONCE]) && wp_verify_nonce($_POST[self::ENVIE_NONCE], self::ENVIE_NONCE)) {
            $option = self::getOption('envie');

            // TODO: ENVIAR EMAIL

            $success = null;

            if ($_POST['sender-name'] && $_POST['sender-email']) {

                $recipients = explode(',', $_POST['recipient-email']);

                $msg = $_POST['sender-message'] ? stripslashes($_POST['sender-message']) . "\n\n" . $option['message'] : $option['message'];

                $success = false;

                if (is_array($recipients) && sizeof($recipients) > 0) {

                    foreach ($recipients as $r) {

                        if ($x = wp_mail($r, $option['subject'], $msg, "From: 'Carteiro Campanha Completa' <noreply@campanhacompleta.com.br>"))
                            $success = true;
                    }
                }
            }

            return $success;
        }
    }

}

function do_mobilize_action() {
    Mobilize::adesivar();
}

add_action('init', 'do_mobilize_action', 100);
?>
