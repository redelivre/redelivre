<?php

require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/GraphicMaterialFactory.php');

$header = GraphicMaterialFactory::build('Header');

if (isset($_POST['save'])) {
    check_admin_referer('graphic_material');
    
    try {
        $header->save();
    } catch (Exception $e) {
        echo "<div class='error'><p>{$e->getMessage()}</p></div>";
    }
}

?>

<div>
    <h1>Imagem do topo do site</h1>
    <div id="graphic_material_content" style="width: 60%; float: left;">
        <h3>1. Selecione uma foto ou envie uma nova:</h3>
        <?php $header->candidatePhoto->printHtml(); ?>
        
        <form id="graphic_material_form" method="post">
            <?php wp_nonce_field('graphic_material'); ?>
            <input type='hidden' name='action' value='campanhaPreviewFlyer'>
            <input type='hidden' name='type' value='header'>
            <div id="graphic_material_wizard">
                <h3>2. Escolha uma forma:</h3>
                <?php
                $shapes = Header::getShapes();
                
                foreach ($shapes as $shape) {
                    $checked = (isset($header->data->shapeName) && $shape->name == $header->data->shapeName) ? ' checked ' : '';
                    echo "<input type='radio' name='data[shapeName]' value='{$shape->name}' $checked><img src='{$shape->url}'>";
                }
                ?>
                
                <p>Cor 1: <input type="color" name="data[shapeColor1]" value="<?php echo (isset($header->data->shapeColor1) && !empty($header->data->shapeColor1)) ? $header->data->shapeColor1 : '#ff0000'; ?>" data-text="hidden" style="height:20px;width:20px;" /></p>
                <p>Cor 2: <input type="color" name="data[shapeColor2]" value="<?php echo (isset($header->data->shapeColor2) && !empty($header->data->shapeColor2)) ? $header->data->shapeColor2 : '#00ff00'; ?>" data-text="hidden" style="height:20px;width:20px;" /></p>
                <br>
                
                <h3>3. Textos:</h3>
                Nome: <input type="text" name="data[candidateName]" value="<?php echo (isset($header->data->shapeName)) ? $header->data->shapeName : ''; ?>" /><br>
                Cor: <input type="color" name="data[candidateColor]" value="<?php echo (isset($header->data->candidateColor) && !empty($header->data->candidateColor)) ? $header->data->candidateColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                Tamanho:
                <select name="data[candidateSize]">
                    <option value="" selected="selected"></option>
                    <?php
                    foreach (range(8, 30) as $number) {
                        $selected = (isset($header->data->candidateSize) && $header->data->candidateSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                <br><br>
                
                Slogan: <input type="text" name="data[slogan]" value="<?php echo (isset($header->data->slogan)) ? $header->data->slogan : ''; ?>" /><br>
                Cor: <input type="color" name="data[sloganColor]" value="<?php echo (isset($header->data->sloganColor) && !empty($header->data->sloganColor)) ? $header->data->sloganColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                Tamanho:
                <select name="data[sloganSize]">
                    <option value="" selected="selected"></option>
                    <?php
                    foreach (range(8, 30) as $number) {
                        $selected = (isset($header->data->sloganSize) && $header->data->sloganSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
            </div>
            
            </p><input type="submit" class="button-primary" name="save" value="Salvar"></p>
        </form>
    </div>
    
    <div id="graphic_material_preview" style="min-height: 400px;">
        <h3>Pré-visualização</h3>
    </div>
    <div id="graphic_material_saved">
        <?php
        if ($header->hasImage()) {
            echo '<h3>Imagem salva</h3>';
            // add random number as parameter to skip browser cache
            $rand = rand();
            echo "<img src='{$header->getImage('png')}?rand=$rand'>";
        }
        ?>
    </div>
</div>
    
