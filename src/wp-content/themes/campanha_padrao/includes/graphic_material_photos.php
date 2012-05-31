<?php

require_once(TEMPLATEPATH . '/includes/graphic_material/CandidatePhoto.php');

$candidatePhoto = new CandidatePhoto;
$error = '';

if (isset($_POST["graphic_material_upload_photo"])) {
    try {
        $candidatePhoto->handleUpload();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$position = get_option('photo-position-' . CandidatePhoto::FILE_NAME);

if (!$position) {
    $position = array('left' => 0, 'top' => 0, 'width' => 'auto');
}

?>

<div class="wrapper">
    <h2>FOTO</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div><br/>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="graphic_material_upload_photo" value="1" />
        <?php wp_nonce_field('graphic_material_upload_photo', 'graphic_material_upload_photo_nonce'); ?>
        <input type="file" name="photo" />
        <input type="submit" value="subir foto" />
    </form>
        
    <?php if (file_exists(GRAPHIC_MATERIAL_DIR . CandidatePhoto::FILE_NAME)): ?>
        <div id="photo-wrapper">
            <div id="zoom-plus">+</div>
            <div id="zoom-minus">-</div>
            <img src="<?php echo GRAPHIC_MATERIAL_URL . CandidatePhoto::FILE_NAME; ?>" style="left: <?php echo $position['left']; ?>; top: <?php echo $position['top']; ?>; width: <?php echo $position['width']; ?>;"/>
        </div>
        <button id="save-position">salvar posição</button>
        <span id="save-response">a posição da imagem foi salva</span>
    <?php else: ?>
        você ainda não enviou a imagem
    <?php endif; ?>
</div>