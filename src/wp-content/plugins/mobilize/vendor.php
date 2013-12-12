<?php

class Mobilize {

    const SETTINGS_NONCE                  = 'save-mobilize';
    const ADESIVE_NONCE                   = 'adesivenonce';
    const ENVIE_NONCE                     = 'envienonce';
    const OPTION_NAME                     = 'mobilize';
    const TEXTO_DESCRITIVO_PADRAO_PAGINA  = 'Mobilize e demonstre seu apoio';
    const TEXTO_DESCRITIVO_PADRAO_REDES   = 'Acompanhe as redes sociais abaixo.';
    const TEXTO_DESCRITIVO_PADRAO_BANNERS = 'Utilize o código da primeira caixa abaixo para inserir o banner em seu site ou blog, ou, então, utilize o link da segunda caixa para compartilhá-lo nas redes sociais.';
    const TEXTO_DESCRITIVO_PADRAO_ADESIVE = 'Selecione uma foto clicando no botão abaixo, aguarde o procedimento e depois é só salvar o arquivo no seu dispositivo.';
    const TEXTO_DESCRITIVO_PADRAO_ENVIE   = 'Coloque seu nome e seu e-mail. Depois coloque o e-mail de seus amigos separados por vírgulas e agora é só colocar sua mensagem pessoal e enviar. As pessoas indicadas por você irão receber o texto abaixo junto com a sua mensagem.';

    public static $errors = array('banners' => array(), 'adesive' => array(), 'redes' => array(), 'envie' => array());

	/**
	 * [createPageTemplate description]
	 * @return [type] [description]
	 */
	public static function createPageTemplate()
	{
		wp_enqueue_script('mobilize-edit',
			plugins_url('/mobilize/assets/js/edit.js', INC_MOBILIZE));
		wp_localize_script('mobilize-edit', 'templateData',
				array('slug' => get_page_template_slug()));
	}

	public static function savePage($post_ID)
	{
		if (array_key_exists('page_template', $_POST)
				&& $_POST['page_template'] == 'mobilize')
		{
			update_post_meta($post_ID, '_wp_page_template', 'mobilize');
			$_POST['page_template'] = 'default';
		}
		else if(get_post_meta($post_ID, '_wp_page_template', true) === 'mobilize')
		{
				update_post_meta($post_ID, '_wp_page_template', 'default');
		}
	}

    /**
     * [saveRedesSociais description]
     * @return [type] [description]
     */
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

    /**
     * [optionRedesSociais description]
     * @param  [type] $index [description]
     * @return [type]        [description]
     */
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

    /**
     * [isActive description]
     * @param  [type]  $section [description]
     * @return boolean          [description]
     */
    public static function isActive($section) {
        $option = self::getOption($section);
        return isset($option['active']);
    }

    /**
     * [getErrors description]
     * @param  [type] $section [description]
     * @return [type]          [description]
     */
    public static function getErrors($section = null) {
        return $section ? (isset(self::$errors[$section]) ? self::$errors[$section] : '') : self::$errors;
    }

    /**
     * [printErrors description]
     * @param  [type] $section [description]
     * @return [type]          [description]
     */
    public static function printErrors($section) {
        $errors = self::getErrors($section);

        if (is_array($errors) && count($errors)) {
            $msg = implode('<br />', $errors);
            echo "<div class='error'>$msg</div>";
        }
    }

    /**
     * [addError description]
     * @param [type] $section [description]
     * @param [type] $error   [description]
     */
    public static function addError($section, $error) {
        self::$errors[$section][] = $error;
    }

    /**
     * [printSettingsNonce description]
     * @return [type] [description]
     */
    public static function printSettingsNonce() {
        wp_nonce_field(self::SETTINGS_NONCE, self::SETTINGS_NONCE);
    }
   
    /**
     * [saveSettings description]
     * @return [type] [description]
     */
    public static function saveSettings() {
        if ($_POST && isset($_POST[self::SETTINGS_NONCE]) && wp_verify_nonce($_POST[self::SETTINGS_NONCE], self::SETTINGS_NONCE)) {
            $option = self::getOption();

            // para não perder as imagens quando salvar o post sem enviar outras imagens.
            // se for implementar mais de uma imagem por seção, tem que pensar num modo de deletar imagens
						if (array_key_exists('files', $option['banners']))
							$_POST['mobilize']['banners']['files'] = $option['banners']['files'];
						if (array_key_exists('files', $option['adesive']))
							$_POST['mobilize']['adesive']['files'] = $option['adesive']['files'];

            self::handleBannerUploads();
            self::handleAdesiveUploads();

            array_walk_recursive($_POST['mobilize'], create_function('&$val', '$val = stripslashes($val);'));
            self::updateOption($_POST['mobilize']);
            echo '<div style="margin: 15px 0; margin-right: 15px; box-sizing: border-box; -webkit-box-sizing: border-box; background-color: #f0f7fd; border-left: 5px solid #d0e3f0; padding: 10px; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 18px;">Dados atualizados com sucesso!</div>';
        }
    }

    /**
     * [getOption description]
     * @param  [type] $index [description]
     * @return [type]        [description]
     */
    public static function getOption($index = null) {
        $option = get_option(self::OPTION_NAME);
        $option['redes']['description']   = isset($option['redes']['description'])   ?  $option['redes']['description']   : __(self::TEXTO_DESCRITIVO_PADRAO_REDES, 'mobilize');
        $option['banners']['description'] = isset($option['banners']['description']) ?  $option['banners']['description'] : __(self::TEXTO_DESCRITIVO_PADRAO_BANNERS, 'mobilize');
        $option['adesive']['description'] = isset($option['adesive']['description']) ?  $option['adesive']['description'] : __(self::TEXTO_DESCRITIVO_PADRAO_ADESIVE, 'mobilize');
        $option['envie']['description']   = isset($option['envie']['description'])   ?  $option['envie']['description']   : __(self::TEXTO_DESCRITIVO_PADRAO_ENVIE, 'mobilize');

        $result = $index ? @$option[$index] : $option;

        return is_array($result) ? $result : array();
    }

    /**
     * [updateOption description]
     * @param  [type] $option [description]
     * @return [type]         [description]
     */
    public static function updateOption($option) {
        update_option(self::OPTION_NAME, $option);
    }

    /**
     * [deleteBanner description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
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

    /**
     * [deleteAdesive description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
    public static function deleteAdesive($index = 0) {
        $option = self::getOption('adesive');

        $filename = self::getAdesiveFilename($index);

        if ($filename && file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * [getNumBanners description]
     * @return [type] [description]
     */
    public static function getNumBanners() {
        $option = self::getOption('banners');
        
        if (isset($option['files'])) {
            return count($option['files']);
        }
    }

    /**
     * [getBannerURL description]
     * @param  [type]  $size  [description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
    public static function getBannerURL($size, $index = 0) {
        $option = self::getOption('banners');

        if (isset($option['files'][$index]) && is_numeric($size)) {
            return MOBILIZE_MATERIAL_URL.'banners/'.preg_replace("/^(.*)(\.[a-zA-Z]{3,4})$/", "$1-{$size}$2", $option['files'][$index]);
        }
        else {
            return '';
        }
    }

    /**
     * [getAdesiveURL description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
    public static function getAdesiveURL($index = 0) {
        $option = self::getOption('adesive');
        return isset($option['files'][$index]) ? MOBILIZE_MATERIAL_URL.'adesives/'.$option['files'][$index] : '';
    }

    /**
     * [getBannersPath description]
     * @return [type] [description]
     */
    public static function getBannersPath() {
        return MOBILIZE_MATERIAL_DIR.'banners/';
    }

    /**
     * [getAdesivesPath description]
     * @return [type] [description]
     */
    public static function getAdesivesPath() {
        return MOBILIZE_MATERIAL_DIR.'adesives/';
    }

    /**
     * [getBannerFilename description]
     * @param  [type]  $size  [description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
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

    /**
     * [getAdesiveFilename description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
    public static function getAdesiveFilename($index = 0) {
        $option = self::getOption('adesive');
        $path   = self::getAdesivesPath();
        
        return isset($option['files'][$index]) ? $path.$option['files'][$index] : '';
    }

    /**
     * [handleBannerUploads description]
     * @return [type] [description]
     */
    public static function handleBannerUploads() {
        if (isset($_FILES['banner']) && is_array($_FILES['banner']['name'])) {
            $path = self::getBannersPath();

            if (!file_exists($path) && !is_dir($path)) {
                mkdir($path, 0777, true);
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

    /**
     * [handleAdesiveUploads description]
     * @return [type] [description]
     */
    public static function handleAdesiveUploads() {
        if (isset($_FILES['adesive']) && is_array($_FILES['adesive']['name'])) {
            $path = self::getAdesivesPath();

            if (!file_exists($path) && !is_dir($path)) {
                mkdir($path, 0777, true);
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
                                        
                    $adesivo->saveToFile($path.$fname);
                }
            }
        }
    }

    /**
     * [validateBanner description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
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

    /**
     * [validateAdesive description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
    public static function validateAdesive($index = 0) {
        if (!$_FILES['adesive']['name'][$index]) {
            return;
        }

        $ok = $_FILES['adesive']['error'][$index] == UPLOAD_ERR_OK;
        $ok = self::validadeImageUpload('adesive', $index);

        if (!$ok)
            self::addError('adesive', "O upload do adesivo falhou.");
            
        $file = WideImage::load($_FILES['adesive']['tmp_name'][$index]);
        
        if ($file->getWidth() < 150) {
            self::addError('adesive', "O adesivo deve ter no mínimo 150 pixels de largura.");
            $ok = false;
        }

        return $ok;
    }

    /**
     * [validadeImageUpload description]
     * @param  [type]  $image [description]
     * @param  integer $index [description]
     * @return [type]         [description]
     */
    public static function validadeImageUpload($image, $index = 0) {
        if (empty($image) || is_null($image) || !isset($_FILES[$image])) {
            return false;
        } 
        else {
            $acceptedFormats = array('image/gif', 'image/png', 'image/jpeg', 'image/pjpeg', 'image/x-png');
            return in_array($_FILES[$image]['type'][$index], $acceptedFormats);
        }
    }

    /**
     * [printAdesiveNonce description]
     * @return [type] [description]
     */
    public static function printAdesiveNonce() {
        wp_nonce_field(self::ADESIVE_NONCE, self::ADESIVE_NONCE);
    }

    /**
     * [adesivar description]
     * @return [type] [description]
     */
		public static function adesivar($photo) {
			$option = self::getOption('adesive');

			// se um dia tiver mais de um adesivo é só enviar o $index pelo post
			$index = 0;
			$adesive_filename = self::getAdesiveFilename($index);

			if ($adesive_filename) {
				$adesivo	= WideImage::load($adesive_filename);
				$uploaded = WideImage::loadFromUpload($photo);

				$uploaded = $uploaded->resize(250, null);

				$new = $uploaded->merge($adesivo, 'right', 'bottom');
				header('Content-disposition: attachment; filename=foto.jpg');
				header('Content-type: image/jpeg');
				$new->output('jpg', 100);
				die;
			}
		}

    /**
     * [printEnvieNonce description]
     * @return [type] [description]
     */
    public static function printEnvieNonce() {
        wp_nonce_field(self::ENVIE_NONCE, self::ENVIE_NONCE);
    }

    /**
     * [enviarEmails description]
     * @return [type] [description]
     */
		public static function enviarEmails(
				$sender, $senderEmail, $recipientList, $senderMessage)
		{
			$success = false;
			$option = self::getOption('envie');

			if (!empty($sender) && !empty($senderEmail) && !empty($recipientList))
			{
				$sender      = filter_var($sender, FILTER_SANITIZE_STRING);
				$senderEmail = filter_var($senderEmail, FILTER_SANITIZE_EMAIL);
				$headers     = "From: '{$sender}' <{}>";
				$recipients  = array();
				foreach (explode(',', $recipientList) as $recipient)
				{
					$recipients[] = filter_var(trim($recipient), FILTER_SANITIZE_EMAIL);
				}

				$msg =
					"$sender ($senderEmail) lhe enviou a mensagem que segue abaixo:\n\n";
				if (!empty($senderMessage))
					$msg .= "$senderMessage\n\n";
				$msg .= $option['message'];

				$success = wp_mail($recipients, $option['subject'], $msg, $headers);
			}

			return $success;
		}
}
