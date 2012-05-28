<?php
$error = "";
$_upload_dir = wp_upload_dir();
$FILE_NAME = 'foto1';
$MIME_TYPES = array('image/jpeg', 'image/png');
$BASE_PATH = $_upload_dir['basedir'].'/fotos/';
$BASE_URL = $_upload_dir['baseurl'].'/fotos/';

if (isset($_POST["graphic_material_upload_photo"]) && wp_verify_nonce($_POST['graphic_material_upload_photo_nonce'], 'graphic_material_upload_photo') && isset($_FILES['photo']) ){
    if(!$_FILES['photo']['error'] && in_array($_FILES['photo']['type'], $MIME_TYPES)){
        
        if(!file_exists($BASE_PATH) && !is_dir($BASE_PATH))
            mkdir($BASE_PATH);
        
        $fname = $BASE_PATH.$FILE_NAME;
        
        move_uploaded_file($_FILES['photo']['tmp_name'], $fname);
        
    }else if(!$_FILES['photo']['error'] && !in_array($_FILES['photo']['type'], $MIME_TYPES)){
        $error = "Tipo de arquivo inválido, o arquivo deve ser dos tipos .png ou .jpg";
    }else{
        $error = "Algum erro inesperado aconteceu.";
    }
    
}
?>
<script>
(function($){
    $(document).ready(function(){
        $("#photo-wrapper").css({
            width:200,
            height:300,
            overflow: 'hidden'
        });
        
        $("#photo-wrapper img").css({
            cursor: 'move',
            zIndex:1
        }).draggable();
        
        var zoom_interval;
        $(document).mouseup(function(){
            clearInterval(zoom_interval);
        });
        
        $("#zoom-plus").mousedown(function(){
            zoom_interval = setInterval(function(){
                var w = parseInt($('#photo-wrapper img').css('width'))+2
                $('#photo-wrapper img').css('width', w);
            },20);
        }).disableSelection();
        
        $("#zoom-minus").mousedown(function(){
            zoom_interval = setInterval(function(){
                if($('#photo-wrapper img').width() <= $('#photo-wrapper').width() || $('#photo-wrapper img').height() <= $('#photo-wrapper').height())
                    return;
                
                var w = parseInt($('#photo-wrapper img').css('width'))-2
                $('#photo-wrapper img').css('width', w);
            },20);
        }).disableSelection();
    });
})(jQuery);
</script>
<style>
    #zoom-plus, #zoom-minus { background:white; border:1px solid black; cursor:pointer; font-weight:bold; height:15px; margin:2px; position:absolute; text-align:center; width:15px; z-index:2; }
    #zoom-plus { left:19px; }
    #photo-wrapper { border: 1px solid transparent; }
    #photo-wrapper:hover { border: 1px dashed #999; }
</style>
<div class="wrapper">
    <h2>FOTO</h2>
    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div><br/>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="graphic_material_upload_photo" value="1" />
        <?php wp_nonce_field('graphic_material_upload_photo', 'graphic_material_upload_photo_nonce'); ?>
        <input type="file" name="photo" />
        <input type="submit" value="subir foto" />
    </form>
        
    <?php if(file_exists($BASE_PATH.$FILE_NAME)): ?>
        <div id="photo-wrapper">
            <div id="zoom-plus">+</div>
            <div id="zoom-minus">-</div>
            <img src="<?php echo $BASE_URL.$FILE_NAME; ?>" />
        </div>
    <?php else: ?>
        você ainda não enviou a imagem
    <?php endif; ?>
</div>