<?php

require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/SmallFlyer.php');
require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/CandidatePhoto.php');

$smallFlyer = new SmallFlyer;
$candidatePhoto = new CandidatePhoto('smallflyer_candidate.png', $smallFlyer->width, $smallFlyer->height);

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
    <h1>Santinho e colinha</h1>
    <div id="graphic_material_content" style="width: 60%; float: left;">
        <h3>1. Selecione uma foto ou envie uma nova:</h3>
        <?php $candidatePhoto->printHtml(); ?>
        
        <form id="graphic_material_form" method="post">
            <?php wp_nonce_field('graphic_material'); ?>
            <input type='hidden' name='action' value='campanhaPreviewFlyer'>
            <input type='hidden' name='type' value='smallflyer'>
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
                Nome: <input type="text" name="data[candidateName]" value="<?php echo (isset($smallFlyer->data->candidateName)) ? $smallFlyer->data->candidateName : ''; ?>" /><br>
                Cor: <input type="color" name="data[candidateColor]" value="<?php echo (isset($smallFlyer->data->candidateColor) && !empty($smallFlyer->data->candidateColor)) ? $smallFlyer->data->candidateColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                Tamanho:
                <select name="data[candidateSize]" id="candidateSize">
                    <option value="" selected="selected">Valor padrão</option>
                    <?php
                    foreach (range(70, 160) as $number) {
                        $selected = (isset($smallFlyer->data->candidateSize) && $smallFlyer->data->candidateSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                <br><br>
                
                Slogan: <input type="text" name="data[slogan]" value="<?php echo (isset($smallFlyer->data->slogan)) ? $smallFlyer->data->slogan : ''; ?>" /><br>
                Cor: <input type="color" name="data[sloganColor]" value="<?php echo (isset($smallFlyer->data->sloganColor) && !empty($smallFlyer->data->sloganColor)) ? $smallFlyer->data->sloganColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                <!--
                Tamanho:
                <select name="data[sloganSize]">
                    <option value="" selected="selected"></option>
                    <?php
                    foreach (range(70, 160) as $number) {
                        $selected = (isset($smallFlyer->data->sloganSize) && $smallFlyer->data->sloganSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                <br><br>
                -->
                
                Número: <br>
                Cor: <input type="color" name="data[numberColor]" value="<?php echo (isset($smallFlyer->data->numberColor) && !empty($smallFlyer->data->numberColor)) ? $smallFlyer->data->numberColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                <!--
                Tamanho:
                <select name="data[numberSize]">
                    <option value="" selected="selected"></option>
                    <?php
                    foreach (range(140, 220) as $number) {
                        $selected = (isset($smallFlyer->data->numberSize) && $smallFlyer->data->numberSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                <br><br>
                -->
                
                Cargo: <br>
                Cor: <input type="color" name="data[roleColor]" value="<?php echo (isset($smallFlyer->data->roleColor) && !empty($smallFlyer->data->roleColor)) ? $smallFlyer->data->roleColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                
                <!--
                Tamanho:
                <select name="data[roleSize]">
                    <option value="" selected="selected"></option>
                    <?php
                    foreach (range(70, 140) as $number) {
                        $selected = (isset($smallFlyer->data->roleSize) && $smallFlyer->data->roleSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                <br><br>
                -->
                
                Coligação: <input type="text" name="data[coalition]" value="<?php echo (isset($smallFlyer->data->coalition)) ? $smallFlyer->data->coalition : ''; ?>" /><br>
                Cor: <input type="color" name="data[coalitionColor]" value="<?php echo (isset($smallFlyer->data->coalitionColor) && !empty($smallFlyer->data->coalitionColor)) ? $smallFlyer->data->coalitionColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                
                <!--
                Tamanho:
                <select name="data[coalitionSize]">
                    <option value="" selected="selected"></option>
                    <?php
                    foreach (range(20, 50) as $number) {
                        $selected = (isset($smallFlyer->data->coalitionSize) && $smallFlyer->data->coalitionSize == $number) ? ' selected="selected" ' : '';
                        echo "<option value='$number' $selected>$number</option>";
                    }
                    ?>
                </select>
                <br><br>
                -->
            </div>
            <p><input type="submit" class="button-primary" name="save" value="Salvar"></p>
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
    
