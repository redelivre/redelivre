<?php

require_once(TEMPLATEPATH . '/includes/wideimage/WideImage.php');

/**
 * Methods to deal with the candidate
 * photos.
 */
class CandidatePhoto {
    const FILE_NAME = 'foto1.png';
    
    /**
     * Handle candidate photo uploads
     * 
     * @throws Exception when an error occurs
     * @return null
     */
    public function handleUpload()
    {
        $mimeTypes = array('image/jpeg', 'image/png');
        
        if (wp_verify_nonce($_POST['graphic_material_upload_photo_nonce'], 'graphic_material_upload_photo')
            && isset($_FILES['photo']))
        {
            if (!$_FILES['photo']['error'] && in_array($_FILES['photo']['type'], $mimeTypes)) {
                $fname = GRAPHIC_MATERIAL_DIR . self::FILE_NAME;
                move_uploaded_file($_FILES['photo']['tmp_name'], $fname);
                
            } else if (!$_FILES['photo']['error'] && !in_array($_FILES['photo']['type'], $mimeTypes)) {
                throw new Exception("Tipo de arquivo inválido, o arquivo deve ser dos tipos .png ou .jpg");
            } else {
                throw new Exception("Algum erro inesperado aconteceu."); 
            }
        }
    }
    
    /**
     * Crop candidate image based on user selecion
     * in the browser.
     * 
     * @return null
     */
    public function crop()
    {
        update_option('photo-position-' . self::FILE_NAME, array('left' => $_POST['left'], 'top' => $_POST['top'], 'width' => $_POST['width']));
        
        list($left, $top) = preg_replace('/-?(\d+?)px/', '$1', array($_POST['left'], $_POST['top']));
        
        $image = WideImage::load(GRAPHIC_MATERIAL_DIR . self::FILE_NAME);
        $croped = $image->crop($left, $top, 200, 300);
        $croped->saveToFile(GRAPHIC_MATERIAL_DIR . self::FILE_NAME . '_croped.png');
    }
}
