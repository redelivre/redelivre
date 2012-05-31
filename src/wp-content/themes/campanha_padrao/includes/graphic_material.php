<?php

require_once(TEMPLATEPATH . '/includes/svglib/svglib.php');
require_once(TEMPLATEPATH . '/includes/wideimage/WideImage.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CampanhaSVGDocument.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/SmallFlyer.php');
require_once(TEMPLATEPATH . '/includes/graphic_material/CandidatePhoto.php');

$smallFlyer = new SmallFlyer;
$candidatePhoto = new CandidatePhoto('smallflyer_candidate.png');
$url = site_url() . '/materialgrafico';

if (isset($_POST['save'])) {
    check_admin_referer('graphic_material');
    
    try {
        $smallFlyer->save();
    } catch (Exception $e) {
        echo "<div class='error'><p>{$e->getMessage()}</p></div>";
    }
}

?>

<div>
    <h1>Geração de material gráfico</h1>
    <div id="graphic_material_content" style="width: 60%; float: left;">
        <h3>1. Selecione uma foto ou envie uma nova:</h3>
        <?php $candidatePhoto->printHtml(); ?>
        
        <form id="graphic_material_form" method="post">
            <?php wp_nonce_field('graphic_material'); ?>
            <input type='hidden' name='action' value='campanhaPreviewFlyer'>
            <input type='hidden' name='page' value='graphic_material'>
            <div id="graphic_material_wizard">
                <h3>2. Escolha uma forma:</h3>
                <?php
                $shapes = SmallFlyer::getShapes();
                
                foreach ($shapes as $shape) {
                    $checked = (isset($smallFlyer->data->shapeName) && $shape->name == $smallFlyer->data->shapeName) ? ' checked ' : '';
                    echo "<input type='radio' name='data[shapeName]' value='{$shape->name}' $checked><img src='{$shape->url}'>";
                }
                ?>
                
                <p>Cor 1: <input type="color" name="data[shapeColor1]" value="<?php echo (isset($smallFlyer->data->shapeColor1) && !empty($smallFlyer->data->shapeColor1)) ? $smallFlyer->data->shapeColor1 : '#ff0000'; ?>" data-text="hidden" style="height:20px;width:20px;" /></p>
                <p>Cor 2: <input type="color" name="data[shapeColor2]" value="<?php echo (isset($smallFlyer->data->shapeColor2) && !empty($smallFlyer->data->shapeColor2)) ? $smallFlyer->data->shapeColor2 : '#00ff00'; ?>" data-text="hidden" style="height:20px;width:20px;" /></p>
                <br>
                
                <h3>3. Textos:</h3>
                Nome: <input type="text" name="data[candidateName]" value="<?php echo (isset($smallFlyer->data->shapeName)) ? $smallFlyer->data->shapeName : ''; ?>" /><br>
                Cor: <input type="color" name="data[candidateColor]" value="<?php echo (isset($smallFlyer->data->candidateColor) && !empty($smallFlyer->data->candidateColor)) ? $smallFlyer->data->candidateColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                Tamanho:
                <select name="data[candidateSize]">
                    <option value="" selected="selected"></option>
                    <?php
                    foreach (range(8, 30) as $number) {
                        $selected = (isset($smallFlyer->data->candidateSize) && $smallFlyer->data->candidateSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                <br><br>
                
                Slogan: <input type="text" name="data[slogan]" value="<?php echo (isset($smallFlyer->data->slogan)) ? $smallFlyer->data->slogan : ''; ?>" /><br>
                Cor: <input type="color" name="data[sloganColor]" value="<?php echo (isset($smallFlyer->data->sloganColor) && !empty($smallFlyer->data->sloganColor)) ? $smallFlyer->data->sloganColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                Tamanho:
                <select name="data[sloganSize]">
                    <option value="" selected="selected"></option>
                    <?php
                    foreach (range(8, 30) as $number) {
                        $selected = (isset($smallFlyer->data->sloganSize) && $smallFlyer->data->sloganSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                
                <h3>4. Envie o link para a gráfica:</h3>
                <p>Utilize o link <a href="<?php echo $url; ?>" target="_blank"><?php echo $url; ?></a> para compartilhar o material gráfico gerado. O checkbox abaixo precisa estar selecionado para que o conteúdo do link seja público.</p>
                <input type='checkbox' name='graphic_material_public' <?php if ($smallFlyer->isPublic()) echo ' checked="checked" '; ?>> Link público?
            </div>
            
            <input type="submit" name="save" value="Salvar">
        </form>
    </div>
    
    <div id="graphic_material_preview" style="min-height: 400px;">
        <h3>Pré-visualização</h3>
    </div>
    <div id="graphic_material_saved">
        <?php
        if ($smallFlyer->hasImage()) {
            echo '<h3>Imagem salva</h3>';
            // add random number as parameter to skip browser cache
            $rand = rand();
            echo "<img src='{$smallFlyer->getImage('png')}?rand=$rand'>";
        }
        ?>
    </div>
</div>
    
