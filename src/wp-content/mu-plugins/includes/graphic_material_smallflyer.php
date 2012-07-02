<?php

require_once(WPMU_PLUGIN_DIR . '/includes/graphic_material/GraphicMaterialFactory.php');

$smallFlyer = GraphicMaterialFactory::build('SmallFlyer');

if (isset($_POST['save'])) {
    check_admin_referer('graphic_material');
    
    try {
        $smallFlyer->save();
        $smallFlyer->export();
    } catch (Exception $e) {
        echo "<div class='error'><p>{$e->getMessage()}</p></div>";
    }
}

?>

<div id="graphic-material-smallflyer">
    <h1>Santinho e colinha</h1>
    <div id="graphic_material_content">
        <h3>1. Envie uma foto:</h3>
        <?php $smallFlyer->candidatePhoto->printHtml(); ?>
        
            <form id="graphic_material_form" method="post">
            <?php wp_nonce_field('graphic_material'); ?>
            <input type='hidden' name='action' value='campanhaPreviewFlyer'>
            <input type='hidden' name='type' value='smallflyer'>
            <div id="graphic_material_wizard">
                <h3>2. Escolha uma forma:</h3>
                <p>
                    Cores da forma: 
                    <input type="color" name="data[shapeColor1]" value="<?php echo (isset($smallFlyer->data->shapeColor1) && !empty($smallFlyer->data->shapeColor1)) ? $smallFlyer->data->shapeColor1 : '#ff0000'; ?>" data-text="hidden" style="height:20px;width:20px;" />
                    <input type="color" name="data[shapeColor2]" value="<?php echo (isset($smallFlyer->data->shapeColor2) && !empty($smallFlyer->data->shapeColor2)) ? $smallFlyer->data->shapeColor2 : '#00ff00'; ?>" data-text="hidden" style="height:20px;width:20px;" />
                </p>
                <div id="listShapes">
                <?php
                $shapes = SmallFlyer::getShapes();
                
                foreach ($shapes as $shape) {
                    $checked = (isset($smallFlyer->data->shapeName) && $shape->name == $smallFlyer->data->shapeName) ? ' checked ' : '';
                    $active = $checked ? "class='active'" : "";
                    echo "<div class='shapeItem'><label for='shapeName-{$shape->name}' $active><input type='radio' name='data[shapeName]' id='shapeName-{$shape->name}' value='{$shape->name}' $checked><img src='{$shape->url}'></label></div>";
                }
                ?>
                </div>
                
                <div class="clear"></div>
                
                
                
                
                <h3>3. Textos:</h3>
                
                <h4>Nome do candidato:</h4>
                <input type="text" name="data[candidateName]" value="<?php echo (isset($smallFlyer->data->candidateName)) ? $smallFlyer->data->candidateName : ''; ?>" />
                Cor: <input type="color" name="data[candidateColor]" value="<?php echo (isset($smallFlyer->data->candidateColor) && !empty($smallFlyer->data->candidateColor)) ? $smallFlyer->data->candidateColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" />
                <br><br>
                Tamanho da fonte:<br/>
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
                
                <h4>Slogan:</h4>
                <input type="text" name="data[slogan]" value="<?php echo (isset($smallFlyer->data->slogan)) ? $smallFlyer->data->slogan : ''; ?>" />
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
                
                <h4>Coligação:</h4>
                <input type="text" name="data[coalition]" value="<?php echo (isset($smallFlyer->data->coalition)) ? $smallFlyer->data->coalition : ''; ?>" />
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
                
                <h4>Cor do número do candidato: </h4>
                <input type="color" name="data[numberColor]" value="<?php echo (isset($smallFlyer->data->numberColor) && !empty($smallFlyer->data->numberColor)) ? $smallFlyer->data->numberColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
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
                
                <h4>Cor do cargo:</h4>
                <input type="color" name="data[roleColor]" value="<?php echo (isset($smallFlyer->data->roleColor) && !empty($smallFlyer->data->roleColor)) ? $smallFlyer->data->roleColor : '#000000'; ?>" data-text="hidden" style="height:20px;width:20px;" /><br>
                
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
                
                
            </div>
            
        
    </div>
    
    <div id="graphic_material_visualization">
        
        <div id="graphic_material_preview" >
            <h3>Pré-visualização</h3>
        </div>
        
        <div class="updated" id="save-reminder" style="display:none;"><p>Não esqueça de salvar</p></div>
        
        <p>
            <input type="submit" class="button-primary" name="save" value="Salvar e gerar PDF">
            <input type="button" value="Cancelar Edição" class="button-secondary" onClick="document.location = document.location.toString();" />
        </p>
        
    </div>
    
    </form>
    
</div>
    
