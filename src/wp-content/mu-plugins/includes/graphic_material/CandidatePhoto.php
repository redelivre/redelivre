<?php

require_once(WPMU_PLUGIN_DIR . '/includes/wideimage/WideImage.php');

/**
 * Methods to deal with the candidate
 * photos.
 */
class CandidatePhoto {
    /**
     * Uploaded photo name
     * @var string
     */
    protected $fileName;
    
    /**
     * Upload error string
     * @var string
     */
    protected $error = '';
    
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }
    
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
                $fname = GRAPHIC_MATERIAL_DIR . $this->fileName;
                move_uploaded_file($_FILES['photo']['tmp_name'], $fname);
                delete_option('photo-position-' . $this->fileName);
            } else if (!$_FILES['photo']['error'] && !in_array($_FILES['photo']['type'], $mimeTypes)) {
                $this->error = "Tipo de arquivo inválido, o arquivo deve ser dos tipos .png ou .jpg";
            } else {
                $this->error = "Algum erro inesperado aconteceu."; 
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
        update_option('photo-position-' . $this->fileName, array('left' => $_POST['left'], 'top' => $_POST['top'], 'width' => $_POST['width']));
        
        list($left, $top) = preg_replace('/-?(\d+?)px/', '$1', array($_POST['left'], $_POST['top']));
        
        $image = WideImage::load(GRAPHIC_MATERIAL_DIR . $this->fileName);
        $croped = $image->crop($left, $top, 200, 300);
        $baseName = basename($this->fileName, '.png');
        $croped->saveToFile(GRAPHIC_MATERIAL_DIR . $baseName . '_croped.png');
    }
    
    /**
     * Print form to upload and crop candidate
     * photo.
     * 
     * @return null
     */
    public function printHtml()
    {
        if (isset($_POST["graphic_material_upload_photo"])) {
            $this->handleUpload();
        }
        
        $position = get_option('photo-position-' . $this->fileName);

        if (!$position) {
            $position = array('left' => 0, 'top' => 0, 'width' => 'auto');
        }
        
        ?>
        <div class="wrapper">
            <?php if ($this->error): ?>
                <div class="error"><?php echo $this->error; ?></div><br/>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="graphic_material_upload_photo" value="1" />
                <input type="hidden" name="graphic_material_filename" value="<?php echo $this->fileName ?>" />
                <?php wp_nonce_field('graphic_material_upload_photo', 'graphic_material_upload_photo_nonce'); ?>
                <input type="file" name="photo" />
                <input type="submit" value="subir foto" />
            </form>
                
            <?php if (file_exists(GRAPHIC_MATERIAL_DIR . $this->fileName)): ?>
                <div id="photo-wrapper">
                    <div id="zoom-plus">+</div>
                    <div id="zoom-minus">-</div>
                    <img src="<?php echo GRAPHIC_MATERIAL_URL . $this->fileName; ?>" style="left: <?php echo $position['left']; ?>; top: <?php echo $position['top']; ?>; width: <?php echo $position['width']; ?>;"/>
                </div>
                <button id="save-position">salvar posição</button>
                <span id="save-response">a posição da imagem foi salva</span>
            <?php else: ?>
                você ainda não enviou a imagem
            <?php endif; ?>
        </div>
        <?php
    }
}
