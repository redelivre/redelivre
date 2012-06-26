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
     * Image minimum width
     * @var int
     */
    protected $minWidth;
    
    /**
     * Image minimum height
     * @var int
     */
    protected $minHeight;

    /**
     * Name of the resized file used to be 
     * dispĺayed in the browser.
     * @var string
     */
    protected $screenFileName;
    
    /**
     * Width used to display the image to the 
     * browser.
     * @var int
     */
    protected $screenWidth;
    
    /**
     * Height used to display the image to the 
     * browser.
     * @var int
     */
    protected $screenHeight;
    
    /**
     * Upload error string
     * @var string
     */
    protected $error = '';
    
    /**
     * Image object
     * @var WideImage
     */
    protected $image;
    
    public function __construct($fileName, $minWidth, $minHeight)
    {
        $this->fileName = $fileName;
        $this->screenFileName = basename($this->fileName, '.png') . '_resized.png';
        $this->minWidth = $minWidth;
        $this->minHeight = $minHeight;
        
        if (file_exists(GRAPHIC_MATERIAL_DIR . $this->fileName)) {
            $this->loadImage();
        }
        
        $this->screenWidth = $this->convertTo75Dpi($this->minWidth);
        $this->screenHeight = $this->convertTo75Dpi($this->minHeight);
    }
    
    /**
     * Create a WideImage object with the candidate photo.
     */
    protected function loadImage() {
        $this->image = WideImage::load(GRAPHIC_MATERIAL_DIR . $this->fileName);
    }
    
    /**
     * Receives a value in pixels assuming it is 300 dpi and convert it
     * to the corresponding pixel value in 75 dpi.
     * 
     * @param int $value
     * @return int
     */
    protected function convertTo75Dpi($value)
    {
        return $value / 4;
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
                $img = WideImage::loadFromUpload('photo');
                
                if ($img->getWidth() < $this->minWidth || $img->getHeight() < $this->minHeight) {
                    $this->error = "Atenção: a imagem deve ter no mínimo {$this->minWidth}x{$this->minHeight} pixels para garantir a qualidade da impressão. A imagem enviada possui {$img->getWidth()}x{$img->getHeight()} pixels. Por favor envie outra imagem maior.";
                } else {
                    delete_option('photo-position-' . $this->fileName);
                    $filePath = GRAPHIC_MATERIAL_DIR . $this->fileName;
                    
                    // override uploaded image with resized version with dimensions close to minWidth and minHeight (300 dpi)
                    $img = $img->resize($this->minWidth, $this->minHeight, 'outside');
                    $img->saveToFile($filePath);
                    
                    // generate low resolution image to send to the browser (75 dpi)
                    $lowRes = $img->resize($this->screenWidth, $this->screenHeight, 'outside');
                    $lowRes->saveToFile(GRAPHIC_MATERIAL_DIR . $this->screenFileName);
                    
                    $this->loadImage();
                    $this->crop();
                }
            } else if (!$_FILES['photo']['error'] && !in_array($_FILES['photo']['type'], $mimeTypes)) {
                $this->error = "Tipo de arquivo inválido, o arquivo deve ser dos tipos .png ou .jpg";
            } else {
                $this->error = $this->handleUploadError($_FILES['photo']['error']);
            }
        }
    }

    protected function handleUploadError($error)
    {
        $uploadErrorStrings = array(false,
            __("The uploaded file exceeds the <code>upload_max_filesize</code> directive in <code>php.ini</code>."),
            __("The uploaded file exceeds the <em>MAX_FILE_SIZE</em> directive that was specified in the HTML form."),
            __("The uploaded file was only partially uploaded."),
            __("No file was uploaded."),
            '',
            __("Missing a temporary folder."),
            __("Failed to write file to disk."),
            __("File upload stopped by extension."));
            
        return $uploadErrorStrings[$error];
    } 
    
    /**
     * Crop candidate image based on user selecion
     * in the browser.
     * 
     * @return null
     */
    public function crop()
    {
        $baseName = basename($this->fileName, '.png');
        $cropedFile = GRAPHIC_MATERIAL_DIR . $baseName . '_croped.png';
        
        $left = isset($_POST['left']) ? $_POST['left'] : 0;
        $top = isset($_POST['top']) ? $_POST['top'] : 0;

        update_option('photo-position-' . $this->fileName, array('left' => $left, 'top' => $top));
        
        // remove 'px' from the end of the strings
        list($left, $top) = preg_replace('/-?(\d+?)px/', '$1', array($left, $top));
        
        $croped = $this->image->crop($left, $top, $this->minWidth, $this->minHeight);
        
        if (file_exists($cropedFile)) {
            unlink($cropedFile);
        }
            
        $croped->saveToFile($cropedFile);
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
                <div class="error"><p><?php echo $this->error; ?></p></div><br/>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="graphic_material_upload_photo" value="1" />
                <input type="hidden" name="graphic_material_filename" value="<?php echo $this->fileName ?>" />
                <input type="hidden" name="minWidth" value="<?php echo $this->minWidth ?>" />
                <input type="hidden" name="minHeight" value="<?php echo $this->minHeight ?>" />
                <?php wp_nonce_field('graphic_material_upload_photo', 'graphic_material_upload_photo_nonce'); ?>
                <input type="file" name="photo" />
                <input type="submit" value="subir foto" />
            </form>
            <?php if ($this->minWidth && $this->minHeight): ?>
                <div class="warning"><p>Para garantir a qualidade da impressão a imagem enviada deve ter pelo menos <?php echo "{$this->minWidth}x{$this->minHeight}"; ?> pixels.</p></div>
            <?php endif; ?>
                
            <?php if (file_exists(GRAPHIC_MATERIAL_DIR . $this->fileName)): ?>
                <div id="photo-wrapper" style="width: <?php echo $this->screenWidth; ?>px; height: <?php echo $this->screenHeight; ?>px; overflow: hidden;">
                    <img src="<?php echo GRAPHIC_MATERIAL_URL . $this->screenFileName . '?' . rand(); ?>" style="left: <?php echo $position['left']; ?>; top: <?php echo $position['top']; ?>;"/>
                </div>
                <button id="save-position">salvar posição</button>
                <span id="save-response">a posição da imagem foi salva</span>
            <?php else: ?>
                Você ainda não enviou uma imagem.
            <?php endif; ?>
        </div>
        <?php
    }
}
